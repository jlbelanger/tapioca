<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ResourceControllerStoreTest extends TestCase
{
	use RefreshDatabase;

	public function storeProvider()
	{
		return [
			'with no body' => [[
				'records' => [],
				'path' => '/users',
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
				'path' => '/users',
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
				'path' => '/users',
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
				'path' => '/users',
				'parameters' => [
					'data' => [
						'type' => 'foo',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "The type in the body ('foo') does not match the type in the URL ('users').",
							'status' => '400',
						],
					],
				],
				'expectedStatus' => 400,
			]],
			'with matching type and no attributes' => [[
				'records' => [],
				'path' => '/users',
				'parameters' => [
					'data' => [
						'type' => 'users',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => 'The username field is required.',
							'source' => [
								'pointer' => '/data/attributes/username',
							],
							'status' => '422',
						],
						[
							'title' => 'The email field is required.',
							'source' => [
								'pointer' => '/data/attributes/email',
							],
							'status' => '422',
						],
					],
				],
				'expectedStatus' => 422,
			]],
			'with valid attributes' => [[
				'records' => [],
				'path' => '/users',
				'parameters' => [
					'data' => [
						'type' => 'users',
						'attributes' => [
							'username' => 'foo',
							'email' => 'foo@example.com',
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'users',
						'attributes' => [
							'username' => 'foo',
							'email' => 'foo@example.com',
						],
					],
				],
				'expectedStatus' => 201,
			]],
			'with valid belongsTo relationship' => [[
				'records' => [
					'users' => [
						['username' => 'foo'],
					],
				],
				'path' => '/articles?include=user',
				'parameters' => [
					'data' => [
						'id' => '%id%',
						'type' => 'articles',
						'attributes' => [
							'title' => 'foo',
						],
						'relationships' => [
							'user' => [
								'data' => [
									'id' => '%users.foo%',
									'type' => 'users',
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
							'user' => [
								'data' => [
									'id' => '%users.foo%',
									'type' => 'users',
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%users.foo%',
							'type' => 'users',
							'attributes' => [
								'username' => 'foo',
								'email' => 'foo@example.com',
							],
						],
					],
				],
				'expectedStatus' => 201,
			]],
			'with valid belongsToMany relationship' => [[
				'records' => [
					'users' => [
						['username' => 'foo'],
					],
					'tags' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'path' => '/articles?include=tags,user',
				'parameters' => [
					'data' => [
						'id' => '%id%',
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
							'user' => [
								'data' => [
									'id' => '%users.foo%',
									'type' => 'users',
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
							'user' => [
								'data' => [
									'id' => '%users.foo%',
									'type' => 'users',
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
						[
							'id' => '%users.foo%',
							'type' => 'users',
							'attributes' => [
								'username' => 'foo',
								'email' => 'foo@example.com',
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
				'path' => '/albums?include=artist,album_songs,songs',
				'parameters' => [
					'data' => [
						'id' => '%id%',
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
							],
							'relationships' => [
								'song' => [
									'data' => [
										'id' => '%songs.a%',
										'type' => 'songs',
									],
								],
							],
						],
						[
							'id' => 'temp-2',
							'type' => 'album-song',
							'attributes' => [
								'track' => 2,
							],
							'relationships' => [
								'song' => [
									'data' => [
										'id' => '%songs.b%',
										'type' => 'songs',
									],
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
							'title' => 'foo',
							'release_date' => null,
						],
						'relationships' => [
							'album_songs' => [
								'data' => [
									[
										// TODO: This might not always be 1.
										'id' => '1',
										'type' => 'album-song',
									],
									[
										// TODO: This might not always be 2.
										'id' => '2',
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
							'id' => '%artists.foo%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'foo',
								'filename' => null,
							],
						],
						[
							// TODO: This might not always be 1.
							'id' => '1',
							'type' => 'album-song',
							'attributes' => [
								'track' => '1',
								'length' => null,
							],
						],
						[
							// TODO: This might not always be 2.
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
