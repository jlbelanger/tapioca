<?php

namespace Jlbelanger\Tapioca\Tests;

use Jlbelanger\Tapioca\TapiocaServiceProvider;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\ArticleTag;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Artist;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Song;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Tag;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
	protected function setUp() : void
	{
		parent::setUp();
		$this->loadMigrationsFrom(__DIR__ . '/Dummy/Database/Migrations');
	}

	protected function getPackageProviders($app) : array
	{
		return [
			TapiocaServiceProvider::class,
		];
	}

	protected function callPrivate($obj, string $name, array $args = [])
	{
		$class = new ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

	protected function createRecords(array $records) : array // phpcs:ignore Generic.Metrics.NestingLevel.TooHigh
	{
		$output = [];

		$types = [
			'artists' => [
				'class' => Artist::class,
				'slug' => 'title',
			],
			'albums' => [
				'class' => Album::class,
				'slug' => 'title',
				'replace' => [
					[
						'old' => 'artist',
						'new' => 'artist_id',
						'table' => 'artists',
					],
				],
			],
			'songs' => [
				'class' => Song::class,
				'slug' => 'title',
			],
			'album_songs' => [
				'class' => AlbumSong::class,
				'slug' => null,
				'replace' => [
					[
						'old' => 'album',
						'new' => 'album_id',
						'table' => 'albums',
					],
					[
						'old' => 'song',
						'new' => 'song_id',
						'table' => 'songs',
					],
				],
			],
			'tags' => [
				'class' => Tag::class,
				'slug' => 'title',
				'replace' => [
					[
						'old' => 'parent',
						'new' => 'parent_id',
						'table' => 'tags',
					],
				],
			],
			'articles' => [
				'class' => Article::class,
				'slug' => 'title',
			],
			'article_tags' => [
				'class' => ArticleTag::class,
				'slug' => null,
				'replace' => [
					[
						'old' => 'article',
						'new' => 'article_id',
						'table' => 'articles',
					],
					[
						'old' => 'tag',
						'new' => 'tag_id',
						'table' => 'tags',
					],
				],
			],
		];

		foreach ($types as $key => $data) {
			if (!empty($records[$key])) {
				$output[$key] = [];

				foreach ($records[$key] as $i => $rowData) {
					if (!empty($data['replace'])) {
						foreach ($data['replace'] as $replaceData) {
							if (!empty($rowData[$replaceData['old']])) {
								$rowData[$replaceData['new']] = $output[$replaceData['table']][$rowData[$replaceData['old']]];
								unset($rowData[$replaceData['old']]);
							}
						}
					}

					$row = ($data['class'])::factory()->create($rowData);
					if (!empty($data['slug'])) {
						$slug = $rowData[$data['slug']];
					} else {
						$slug = $i;
					}
					$output[$key][$slug] = (string) $row->id;
				}
			}
		}

		return $output;
	}

	protected function replaceIds(array $response, array $records) : array
	{
		if (empty($records)) {
			return $response;
		}

		foreach ($response as $key => $value) {
			if (is_array($value)) {
				$response[$key] = $this->replaceIds($value, $records);
			} elseif (is_string($value) && preg_match('/%.+\..+%/', $value, $matches)) {
				list($type, $slug) = explode('.', $matches[0]);
				$type = ltrim($type, '%');
				$slug = rtrim($slug, '%');
				$replaceWith = $records[$type][$slug];
				$response[$key] = str_replace($matches[0], $replaceWith, $value);
			}
		}
		return $response;
	}

	protected function replaceToken(array $rows, string $token, string $value) : array
	{
		foreach ($rows as $key => $row) {
			if (is_array($row)) {
				$rows[$key] = $this->replaceToken($row, $token, $value);
			} elseif (strpos($row, $token) !== false) {
				$rows[$key] = str_replace($token, $value, $row);
			}
		}
		return $rows;
	}
}
