<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Process;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Helpers\Process\RelationshipsHasManyHelper;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Note;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Song;
use Jlbelanger\Tapioca\Tests\TestCase;

class RelationshipsHasManyHelperTest extends TestCase
{
	use RefreshDatabase;

	public function testUpdateHasMany() : void
	{
		$album = Album::factory()->create();
		$songToAdd = Song::factory()->create();
		$songToDelete = Song::factory()->create();
		$songToLeave = Song::factory()->create();
		$relToDelete = AlbumSong::factory()->create(['album_id' => $album->getKey(), 'song_id' => $songToDelete->getKey(), 'track' => 1]);
		$relToLeave = AlbumSong::factory()->create(['album_id' => $album->getKey(), 'song_id' => $songToLeave->getKey(), 'track' => 2]);

		$relData = [
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
		];
		$existing = $album->albumSongs();
		$key = 'album_songs';
		$record = $album;
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
			'deleteIds' => [(string) $relToDelete->getKey()],
			'addIds' => [(string) ($relToLeave->getKey() + 1)],
			'deleted' => [
				$relToDelete->getKey() => [
					$relToDelete->getKeyName() => $relToDelete->getKey(),
					'album_id' => $album->getKey(),
					'song_id' => $songToDelete->getKey(),
					'track' => 1,
					'length' => null,
				],
			],
		];
		$output = RelationshipsHasManyHelper::update($relData, $existing, $key, $record, $included);
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
	}

	public function testUpdateMorphMany() : void
	{
		$album = Album::factory()->create();
		$noteToDelete = Note::factory()->create(['record_id' => $album->getKey(), 'content' => 'Note to delete']);
		$noteToLeave = Note::factory()->create(['record_id' => $album->getKey(), 'content' => 'Note to leave']);

		$relData = [
			'data' => [
				[
					'id' => (string) $noteToLeave->getKey(),
					'type' => 'notes',
				],
				[
					'id' => 'temp-1',
					'type' => 'notes',
				],
			],
		];
		$existing = $album->notes();
		$key = 'notes';
		$record = $album;
		$included = [
			[
				'id' => 'temp-1',
				'type' => 'notes',
				'attributes' => [
					'content' => 'Note to add',
				],
				'relationships' => [
					'record' => [
						'data' => [
							'id' => (string) $album->getKey(),
							'type' => Album::class,
						],
					],
				],
			],
		];
		$expected = [
			'deleteIds' => [(string) $noteToDelete->getKey()],
			'addIds' => [(string) ($noteToLeave->getKey() + 1)],
			'deleted' => [
				$noteToDelete->getKey() => [
					$noteToDelete->getKeyName() => $noteToDelete->getKey(),
					'record_id' => $album->getKey(),
					'record_type' => Album::class,
					'content' => 'Note to delete',
				],
			],
		];
		$output = RelationshipsHasManyHelper::update($relData, $existing, $key, $record, $included);
		$this->assertSame($expected, $output);

		$this->assertDatabaseMissing('notes', [
			'content' => 'Note to delete',
		]);
		$this->assertDatabaseHas('notes', [
			'content' => 'Note to add',
		]);
		$this->assertDatabaseHas('notes', [
			'content' => 'Note to leave',
		]);
	}

	public static function findProvider() : array
	{
		return [
			'when there is no matching record' => [[
				'included' => [],
				'id' => '123',
				'type' => 'foo',
				'expected' => [],
			]],
			'when there is a record with the same ID but different type' => [[
				'included' => [
					[
						'id' => '123',
						'type' => 'bar',
					],
				],
				'id' => '123',
				'type' => 'foo',
				'expected' => [],
			]],
			'when there is a record with the same type but different ID' => [[
				'included' => [
					[
						'id' => '456',
						'type' => 'foo',
					],
				],
				'id' => '123',
				'type' => 'foo',
				'expected' => [],
			]],
			'when there is a record with the same ID and type' => [[
				'included' => [
					[
						'id' => '123',
						'type' => 'foo',
					],
				],
				'id' => '123',
				'type' => 'foo',
				'expected' => [
					'id' => '123',
					'type' => 'foo',
				],
			]],
		];
	}

	/**
	 * @dataProvider findProvider
	 */
	public function testFind(array $args) : void
	{
		$output = $this->callPrivate(new RelationshipsHasManyHelper, 'find', [$args['included'], $args['id'], $args['type']]);
		$this->assertSame($args['expected'], $output);
	}
}
