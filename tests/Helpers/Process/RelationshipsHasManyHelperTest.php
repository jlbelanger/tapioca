<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Process;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Helpers\Process\RelationshipsHasManyHelper;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Album;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Song;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class RelationshipsHasManyHelperTest extends TestCase
{
	use RefreshDatabase;

	public function testUpdateHasMany()
	{
		$album = Album::factory()->create();
		$songToAdd = Song::factory()->create();
		$songToDelete = Song::factory()->create();
		$songToLeave = Song::factory()->create();
		$relToDelete = AlbumSong::factory()->create(['album_id' => $album->id, 'song_id' => $songToDelete->id, 'track' => 1]);
		$relToLeave = AlbumSong::factory()->create(['album_id' => $album->id, 'song_id' => $songToLeave->id, 'track' => 2]);

		$relData = [
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
					'album_id' => (string) $album->id,
					'song_id' => (string) $songToAdd->id,
				],
			],
		];
		$expected = [
			'deleteIds' => [(string) $relToDelete->id],
			'addIds' => [(string) ($relToLeave->id + 1)],
			'deleted' => [
				$relToDelete->id => [
					'id' => $relToDelete->id,
					'album_id' => (string) $album->id,
					'song_id' => (string) $songToDelete->id,
					'track' => '1',
					'length' => null,
				],
			],
		];
		$output = RelationshipsHasManyHelper::update($relData, $existing, $key, $record, $included);
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
	}

	public function findProvider()
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
	public function testFind($args)
	{
		$output = $this->callPrivate(new RelationshipsHasManyHelper, 'find', [$args['included'], $args['id'], $args['type']]);
		$this->assertSame($args['expected'], $output);
	}
}
