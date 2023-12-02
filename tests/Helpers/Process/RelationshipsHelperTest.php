<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Process;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Helpers\Process\RelationshipsHelper;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Song;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Tag;
use Jlbelanger\Tapioca\Tests\TestCase;

class RelationshipsHelperTest extends TestCase
{
	use RefreshDatabase;

	public function testUpdate() : void
	{
		$article = Article::factory()->create();
		$tagToAdd = Tag::factory()->create();
		$tagToDelete = Tag::factory()->create();
		$tagToLeave = Tag::factory()->create();
		$article->tags()->attach($tagToDelete);
		$article->tags()->attach($tagToLeave);
		$relationships = [
			'tags' => [
				'data' => [
					[
						'id' => (string) $tagToLeave->getKey(),
						'type' => 'tags',
					],
					[
						'id' => (string) $tagToAdd->getKey(),
						'type' => 'tags',
					],
				],
			],
		];
		$included = [];
		$expected = [
			'tags' => [
				'delete' => [(string) $tagToDelete->getKey()],
				'add' => [(string) $tagToAdd->getKey()],
				'deleted' => [],
			],
		];
		$output = RelationshipsHelper::update($article, $relationships, $included);
		$this->assertSame($expected, $output);
		$this->assertDatabaseMissing('article_tag', [
			'article_id' => $article->getKey(),
			'tag_id' => $tagToDelete->getKey(),
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->getKey(),
			'tag_id' => $tagToAdd->getKey(),
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->getKey(),
			'tag_id' => $tagToLeave->getKey(),
		]);

		$album = Album::factory()->create();
		$songToAdd = Song::factory()->create();
		$songToDelete = Song::factory()->create();
		$songToLeave = Song::factory()->create();
		$relToDelete = AlbumSong::factory()->create(['album_id' => $album->getKey(), 'song_id' => $songToDelete->getKey(), 'track' => 1]);
		$relToLeave = AlbumSong::factory()->create(['album_id' => $album->getKey(), 'song_id' => $songToLeave->getKey(), 'track' => 2]);
		$relationships = [
			'album_songs' => [
				'data' => [
					[
						'id' => (string) $relToLeave->getKey(),
						'type' => 'album-song',
					],
					[
						'id' => 'temp-1',
						'type' => 'album-song',
					],
				],
			],
		];
		$included = [
			[
				'id' => 'temp-1',
				'type' => 'album-song',
				'attributes' => [
					'track' => 1,
					'album_id' => (string) $album->getKey(),
					'song_id' => (string) $songToAdd->getKey(),
				],
			],
		];
		$expected = [
			'album_songs' => [
				'delete' => [(string) $relToDelete->getKey()],
				'add' => [(string) ($relToLeave->getKey() + 1)],
				'deleted' => [
					$relToDelete->getKey() => [
						$relToDelete->getKeyName() => $relToDelete->getKey(),
						'album_id' => (string) $album->getKey(),
						'song_id' => (string) $songToDelete->getKey(),
						'track' => '1',
						'length' => null,
					],
				],
			],
		];
		$output = RelationshipsHelper::update($album, $relationships, $included);
		$this->assertSame($expected, $output);
		$this->assertDatabaseMissing('album_song', [
			'album_id' => $album->getKey(),
			'song_id' => $songToDelete->getKey(),
		]);
		$this->assertDatabaseHas('album_song', [
			'album_id' => $album->getKey(),
			'song_id' => $songToAdd->getKey(),
			'track' => 1,
		]);
		$this->assertDatabaseHas('album_song', [
			'album_id' => $album->getKey(),
			'song_id' => $songToLeave->getKey(),
			'track' => 2,
		]);

		// TODO: With an unsupported relationship.
	}

	public function testUpdateBelongsToMany() : void
	{
		$article = Article::factory()->create();
		$tagToAdd = Tag::factory()->create();
		$tagToDelete = Tag::factory()->create();
		$tagToLeave = Tag::factory()->create();
		$article->tags()->attach($tagToDelete);
		$article->tags()->attach($tagToLeave);

		$relData = [
			'data' => [
				[
					'id' => (string) $tagToLeave->getKey(),
					'type' => 'tags',
				],
				[
					'id' => (string) $tagToAdd->getKey(),
					'type' => 'tags',
				],
			],
		];
		$existing = $article->tags();
		$expected = [
			'deleteIds' => [
				(string) $tagToDelete->getKey(),
			],
			'addIds' => [
				(string) $tagToAdd->getKey(),
			],
			'deleted' => [],
		];
		$output = $this->callPrivate(new RelationshipsHelper(), 'updateBelongsToMany', [$relData, $existing]);
		$this->assertSame($output, $expected);

		$this->assertDatabaseMissing('article_tag', [
			'article_id' => $article->getKey(),
			'tag_id' => $tagToDelete->getKey(),
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->getKey(),
			'tag_id' => $tagToAdd->getKey(),
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->getKey(),
			'tag_id' => $tagToLeave->getKey(),
		]);
	}
}
