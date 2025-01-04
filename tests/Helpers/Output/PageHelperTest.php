<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Output;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Helpers\Output\PageHelper;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PageHelperTest extends TestCase
{
	use RefreshDatabase;

	public static function performProvider() : array
	{
		return [
			'with no records' => [[
				'records' => [],
				'page' => [
					'number' => 1,
					'size' => 3,
				],
				'expectedRecords' => [],
				'expectedMeta' => [
					'number' => 1,
					'size' => 3,
					'total' => 0,
					'total_pages' => 0,
				],
			]],
			'with fewer records than size' => [[
				'records' => [
					'articles' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'page' => [
					'number' => 1,
					'size' => 3,
				],
				'expectedRecords' => ['a', 'b'],
				'expectedMeta' => [
					'number' => 1,
					'size' => 3,
					'total' => 2,
					'total_pages' => 1,
				],
			]],
			'with more records than size' => [[
				'records' => [
					'articles' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
						['title' => 'd'],
						['title' => 'e'],
					],
				],
				'page' => [
					'number' => 1,
					'size' => 3,
				],
				'expectedRecords' => ['a', 'b', 'c'],
				'expectedMeta' => [
					'number' => 1,
					'size' => 3,
					'total' => 5,
					'total_pages' => 2,
				],
			]],
			'with more records than size, requesting second page' => [[
				'records' => [
					'articles' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
						['title' => 'd'],
						['title' => 'e'],
					],
				],
				'page' => [
					'number' => 2,
					'size' => 3,
				],
				'expectedRecords' => ['d', 'e'],
				'expectedMeta' => [
					'number' => 2,
					'size' => 3,
					'total' => 5,
					'total_pages' => 2,
				],
			]],
		];
	}

	#[DataProvider('performProvider')]
	public function testPerform(array $args) : void
	{
		$this->createRecords($args['records']);
		list($output, $meta) = PageHelper::perform((new Article())->newQuery(), $args['page']);
		$titles = $output->get()->pluck('title')->toArray();
		$this->assertSame($args['expectedRecords'], $titles);
		$this->assertSame($args['expectedMeta'], $meta);
	}
}
