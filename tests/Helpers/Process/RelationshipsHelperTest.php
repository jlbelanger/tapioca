<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Process;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Helpers\Process\RelationshipsHelper;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Album;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Song;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Tag;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class RelationshipsHelperTest extends TestCase
{
	use RefreshDatabase;

	public function testUpdate()
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
						'id' => (string) $tagToLeave->id,
						'type' => 'tags',
					],
					[
						'id' => (string) $tagToAdd->id,
						'type' => 'tags',
					],
				],
			],
		];
		$included = [];
		$expected = [
			'tags' => [
				'delete' => [(string) $tagToDelete->id],
				'add' => [(string) $tagToAdd->id],
				'deleted' => [],
			],
		];
		$output = RelationshipsHelper::update($article, $relationships, $included);
		$this->assertSame($expected, $output);
		$this->assertDatabaseMissing('article_tag', [
			'article_id' => $article->id,
			'tag_id' => $tagToDelete->id,
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->id,
			'tag_id' => $tagToAdd->id,
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->id,
			'tag_id' => $tagToLeave->id,
		]);

		$album = Album::factory()->create();
		$songToAdd = Song::factory()->create();
		$songToDelete = Song::factory()->create();
		$songToLeave = Song::factory()->create();
		$relToDelete = AlbumSong::factory()->create(['album_id' => $album->id, 'song_id' => $songToDelete->id, 'track' => 1]);
		$relToLeave = AlbumSong::factory()->create(['album_id' => $album->id, 'song_id' => $songToLeave->id, 'track' => 2]);
		$relationships = [
			'album_songs' => [
				'data' => [
					[
						'id' => (string) $relToLeave->id,
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
				],
				'relationships' => [
					'album' => [
						'data' => [
							'id' => (string) $album->id,
							'type' => 'songs',
						],
					],
					'song' => [
						'data' => [
							'id' => (string) $songToAdd->id,
							'type' => 'songs',
						],
					],
				],
			],
		];
		$expected = [
			'album_songs' => [
				'delete' => [(string) $relToDelete->id],
				'add' => [(string) ($relToLeave->id + 1)],
				'deleted' => [
					$relToDelete->id => [
						'id' => $relToDelete->id,
						'album_id' => (string) $album->id,
						'song_id' => (string) $songToDelete->id,
						'track' => '1',
						'length' => null,
					],
				],
			],
		];
		$output = RelationshipsHelper::update($album, $relationships, $included);
		$this->assertSame($expected, $output);
		$this->assertDatabaseMissing('album_song', [
			'album_id' => $album->id,
			'song_id' => $songToDelete->id,
		]);
		$this->assertDatabaseHas('album_song', [
			'album_id' => $album->id,
			'song_id' => $songToAdd->id,
			'track' => 1,
		]);
		$this->assertDatabaseHas('album_song', [
			'album_id' => $album->id,
			'song_id' => $songToLeave->id,
			'track' => 2,
		]);

		// TODO: With an unsupported relationship.
	}

	public function testUpdateBelongsToMany()
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
					'id' => (string) $tagToLeave->id,
					'type' => 'tags',
				],
				[
					'id' => (string) $tagToAdd->id,
					'type' => 'tags',
				],
			],
		];
		$existing = $article->tags();
		$expected = [
			'deleteIds' => [
				(string) $tagToDelete->id,
			],
			'addIds' => [
				(string) $tagToAdd->id,
			],
			'deleted' => [],
		];
		$output = $this->callPrivate(new RelationshipsHelper(), 'updateBelongsToMany', [$relData, $existing]);
		$this->assertSame($output, $expected);

		$this->assertDatabaseMissing('article_tag', [
			'article_id' => $article->id,
			'tag_id' => $tagToDelete->id,
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->id,
			'tag_id' => $tagToAdd->id,
		]);
		$this->assertDatabaseHas('article_tag', [
			'article_id' => $article->id,
			'tag_id' => $tagToLeave->id,
		]);
	}
}
