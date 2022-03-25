<?php

namespace Jlbelanger\Tapioca\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Tests\TestCase;

class ResourceControllerStoreTest extends TestCase
{
	use RefreshDatabase;

	public function storeProvider()
	{
		return [
			'with no body' => [[
				'records' => [],
				'path' => '/albums',
				'parameters' => [],
				'expected' => [
					'errors' => [
						[
							'title' => "The body must contain a 'data' key.",
							'detail' => 'eg. {"data": {"type": "foo"}}',
							'status' => '400',
						],
					],
				],
				'expectedStatus' => 400,
			]],
			'with data param only' => [[
				'records' => [],
				'path' => '/albums',
				'parameters' => [
					'data' => [],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "'data' must contain a 'type' key.",
							'detail' => 'eg. {"data": {"type": "foo"}}',
							'status' => '400',
						],
					],
				],
				'expectedStatus' => 400,
			]],
			'with data string' => [[
				'records' => [],
				'path' => '/albums',
				'parameters' => [
					'data' => 'foo',
				],
				'expected' => [
					'errors' => [
						[
							'title' => "'data' must contain a 'type' key.",
							'detail' => 'eg. {"data": {"type": "foo"}}',
							'status' => '400',
						],
					],
				],
				'expectedStatus' => 400,
			]],
			'with mismatching type' => [[
				'records' => [],
				'path' => '/albums',
				'parameters' => [
					'data' => [
						'type' => 'foo',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "The type in the body ('foo') does not match the type in the URL ('albums').",
							'status' => '400',
						],
					],
				],
				'expectedStatus' => 400,
			]],
			'with matching type and no attributes' => [[
				'records' => [],
				'path' => '/tags',
				'parameters' => [
					'data' => [
						'type' => 'tags',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => 'The title field is required.',
							'source' => [
								'pointer' => '/data/attributes/title',
							],
							'status' => '422',
						],
					],
				],
				'expectedStatus' => 422,
			]],
			'with valid attributes' => [[
				'records' => [],
				'path' => '/tags',
				'parameters' => [
					'data' => [
						'type' => 'tags',
						'attributes' => [
							'title' => 'foo',
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'tags',
						'attributes' => [
							'title' => 'foo',
						],
					],
				],
				'expectedStatus' => 201,
			]],
			'with valid belongsTo relationship' => [[
				'records' => [
					'artists' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums?include=artist',
				'parameters' => [
					'data' => [
						'type' => 'albums',
						'attributes' => [
							'title' => 'bar',
						],
						'relationships' => [
							'artist' => [
								'data' => [
									'id' => '%artists.foo%',
									'type' => 'artists',
								],
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'bar',
							'release_date' => null,
						],
						'relationships' => [
							'artist' => [
								'data' => [
									'id' => '%artists.foo%',
									'type' => 'artists',
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%artists.foo%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'foo',
								'filename' => null,
							],
						],
					],
				],
				'expectedStatus' => 201,
			]],
			'with valid belongsToMany relationship' => [[
				'records' => [
					'tags' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'path' => '/articles?include=tags',
				'parameters' => [
					'data' => [
						'type' => 'articles',
						'attributes' => [
							'title' => 'foo',
						],
						'relationships' => [
							'tags' => [
								'data' => [
									[
										'id' => '%tags.a%',
										'type' => 'tags',
									],
									[
										'id' => '%tags.b%',
										'type' => 'tags',
									],
								],
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'articles',
						'attributes' => [
							'title' => 'foo',
							'content' => null,
							'word_count' => null,
						],
						'relationships' => [
							'tags' => [
								'data' => [
									[
										'id' => '%tags.a%',
										'type' => 'tags',
									],
									[
										'id' => '%tags.b%',
										'type' => 'tags',
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%tags.a%',
							'type' => 'tags',
							'attributes' => [
								'title' => 'a',
							],
						],
						[
							'id' => '%tags.b%',
							'type' => 'tags',
							'attributes' => [
								'title' => 'b',
							],
						],
					],
				],
				'expectedStatus' => 201,
			]],
			'with valid hasMany relationship' => [[
				'records' => [
					'artists' => [
						['title' => 'foo'],
					],
					'songs' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'path' => '/albums?include=album_songs,songs',
				'parameters' => [
					'data' => [
						'type' => 'albums',
						'attributes' => [
							'title' => 'foo',
						],
						'relationships' => [
							'album_songs' => [
								'data' => [
									[
										'id' => 'temp-1',
										'type' => 'album-song',
									],
									[
										'id' => 'temp-2',
										'type' => 'album-song',
									],
								],
							],
							'artist' => [
								'data' => [
									'id' => '%artists.foo%',
									'type' => 'artists',
								],
							],
						],
					],
					'included' => [
						[
							'id' => 'temp-1',
							'type' => 'album-song',
							'attributes' => [
								'track' => 1,
								'album_id' => 'temp-this-id',
								'song_id' => '%songs.a%',
							],
						],
						[
							'id' => 'temp-2',
							'type' => 'album-song',
							'attributes' => [
								'track' => 2,
								'album_id' => 'temp-this-id',
								'song_id' => '%songs.b%',
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'foo',
							'release_date' => null,
						],
						'relationships' => [
							'album_songs' => [
								'data' => [
									[
										'id' => '1',
										'type' => 'album-song',
									],
									[
										'id' => '2',
										'type' => 'album-song',
									],
								],
							],
							'songs' => [
								'data' => [
									[
										'id' => '%songs.a%',
										'type' => 'songs',
									],
									[
										'id' => '%songs.b%',
										'type' => 'songs',
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '1',
							'type' => 'album-song',
							'attributes' => [
								'track' => '1',
								'length' => null,
							],
						],
						[
							'id' => '2',
							'type' => 'album-song',
							'attributes' => [
								'track' => '2',
								'length' => null,
							],
						],
						[
							'id' => '%songs.a%',
							'type' => 'songs',
							'attributes' => [
								'title' => 'a',
								'content' => 'Lorem ipsum dolor.',
							],
						],
						[
							'id' => '%songs.b%',
							'type' => 'songs',
							'attributes' => [
								'title' => 'b',
								'content' => 'Lorem ipsum dolor.',
							],
						],
					],
				],
				'expectedStatus' => 201,
			]],
		];
	}

	/**
	 * @dataProvider storeProvider
	 */
	public function testStore($args = [])
	{
		$records = $this->createRecords($args['records']);
		$args['parameters'] = $this->replaceIds($args['parameters'], $records);
		$args['expected'] = $this->replaceIds($args['expected'], $records);

		$response = $this->call('POST', $args['path'], $args['parameters']);
		if (!empty($response['data']['id'])) {
			$args['expected'] = $this->replaceToken($args['expected'], '%id%', $response['data']['id']);
		}
		$response->assertExactJSON($args['expected']);
		$response->assertStatus($args['expectedStatus']);
	}
}
