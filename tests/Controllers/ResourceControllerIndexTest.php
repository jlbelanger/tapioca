<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ResourceControllerIndexTest extends TestCase
{
	use RefreshDatabase;

	public function indexProvider()
	{
		return [
			'with no params' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums',
				'parameters' => [],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.foo%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'foo',
								'release_date' => null,
							],
						],
					],
				],
			]],
			'with fields param' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'fields' => [
						'albums' => 'title',
					],
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.foo%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'foo',
							],
						],
					],
				],
			]],
			'with filter param' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
						['title' => 'bar'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'filter' => [
						'title' => ['eq' => 'bar'],
					],
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.bar%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'bar',
								'release_date' => null,
							],
						],
					],
				],
			]],
			'with filter param for belongsTo' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'artist' => 'foo'],
						['title' => 'b', 'artist' => 'bar'],
					],
					'artists' => [
						['title' => 'foo'],
						['title' => 'bar'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'filter' => [
						'artist.title' => ['eq' => 'bar'],
					],
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.b%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'b',
								'release_date' => null,
							],
						],
					],
				],
			]],
			// TODO: with filter param for belongsToMany
			// TODO: with filter param for hasMany
			'with include param for belongsTo' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'artist' => 'c'],
						['title' => 'b', 'artist' => 'd'],
					],
					'artists' => [
						['title' => 'c'],
						['title' => 'd'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'include' => 'artist',
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.a%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'a',
								'release_date' => null,
							],
							'relationships' => [
								'artist' => [
									'data' => [
										'id' => '%artists.c%',
										'type' => 'artists',
									],
								],
							],
						],
						[
							'id' => '%albums.b%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'b',
								'release_date' => null,
							],
							'relationships' => [
								'artist' => [
									'data' => [
										'id' => '%artists.d%',
										'type' => 'artists',
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%artists.c%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'c',
								'filename' => null,
							],
						],
						[
							'id' => '%artists.d%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'd',
								'filename' => null,
							],
						],
					],
				],
			]],
			'with include param for belongsToMany' => [[
				'records' => [
					'albums' => [
						['title' => 'a'],
						['title' => 'b'],
					],
					'songs' => [
						['title' => 'c'],
						['title' => 'd'],
						['title' => 'e'],
						['title' => 'f'],
					],
					'album_songs' => [
						['album' => 'a', 'song' => 'c'],
						['album' => 'a', 'song' => 'd'],
						['album' => 'b', 'song' => 'e'],
						['album' => 'b', 'song' => 'f'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'include' => 'songs',
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.a%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'a',
								'release_date' => null,
							],
							'relationships' => [
								'songs' => [
									'data' => [
										[
											'id' => '%songs.c%',
											'type' => 'songs',
										],
										[
											'id' => '%songs.d%',
											'type' => 'songs',
										],
									],
								],
							],
						],
						[
							'id' => '%albums.b%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'b',
								'release_date' => null,
							],
							'relationships' => [
								'songs' => [
									'data' => [
										[
											'id' => '%songs.e%',
											'type' => 'songs',
										],
										[
											'id' => '%songs.f%',
											'type' => 'songs',
										],
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%songs.c%',
							'type' => 'songs',
							'attributes' => [
								'title' => 'c',
								'content' => 'Lorem ipsum dolor.',
							],
						],
						[
							'id' => '%songs.d%',
							'type' => 'songs',
							'attributes' => [
								'title' => 'd',
								'content' => 'Lorem ipsum dolor.',
							],
						],
						[
							'id' => '%songs.e%',
							'type' => 'songs',
							'attributes' => [
								'title' => 'e',
								'content' => 'Lorem ipsum dolor.',
							],
						],
						[
							'id' => '%songs.f%',
							'type' => 'songs',
							'attributes' => [
								'title' => 'f',
								'content' => 'Lorem ipsum dolor.',
							],
						],
					],
				],
			]],
			// TODO: with include param for hasMany
			'with include and fields params' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'artist' => 'c'],
						['title' => 'b', 'artist' => 'd'],
					],
					'artists' => [
						['title' => 'c'],
						['title' => 'd'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'include' => 'artist',
					'fields' => [
						'albums' => 'title',
						'artists' => 'title',
					],
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.a%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'a',
							],
							'relationships' => [
								'artist' => [
									'data' => [
										'id' => '%artists.c%',
										'type' => 'artists',
									],
								],
							],
						],
						[
							'id' => '%albums.b%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'b',
							],
							'relationships' => [
								'artist' => [
									'data' => [
										'id' => '%artists.d%',
										'type' => 'artists',
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%artists.c%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'c',
							],
						],
						[
							'id' => '%artists.d%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'd',
							],
						],
					],
				],
			]],
			'with page param' => [[
				'records' => [
					'albums' => [
						['title' => 'apple'],
						['title' => 'banana'],
						['title' => 'coconut'],
						['title' => 'date'],
						['title' => 'eggplant'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'page' => [
						'number' => '1',
						'size' => '2',
					],
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.apple%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'apple',
								'release_date' => null,
							],
						],
						[
							'id' => '%albums.banana%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'banana',
								'release_date' => null,
							],
						],
					],
					'meta' => [
						'page' => [
							'number' => 1,
							'size' => 2,
							'total' => 5,
							'total_pages' => 3,
						],
					],
				],
			]],
			'with sort param' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
						['title' => 'bar'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'sort' => 'title',
				],
				'expected' => [
					'data' => [
						[
							'id' => '%albums.bar%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'bar',
								'release_date' => null,
							],
						],
						[
							'id' => '%albums.foo%',
							'type' => 'albums',
							'attributes' => [
								'title' => 'foo',
								'release_date' => null,
							],
						],
					],
				],
			]],
		];
	}

	/**
	 * @dataProvider indexProvider
	 */
	public function testIndex($args)
	{
		$records = $this->createRecords($args['records']);
		$expected = $this->replaceIds($args['expected'], $records);

		$response = $this->call('GET', $args['path'], $args['parameters']);
		$response->assertExactJSON($expected);
		$response->assertStatus(200);
	}
}
