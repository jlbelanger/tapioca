<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ResourceControllerUpdateTest extends TestCase
{
	use RefreshDatabase;

	public function updateProvider()
	{
		return [
			'with no body' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
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
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
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
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
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
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
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
			'with matching type and no ID' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
				'parameters' => [
					'data' => [
						'type' => 'albums',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "'data' must contain an 'id' key.",
							'detail' => 'eg. {"data": {"id": "1", "type": "foo"}}',
							'status' => '400',
						],
					],
				],
				'expectedStatus' => 400,
			]],
			'with matching type and mismatching ID' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
				'parameters' => [
					'data' => [
						'id' => 'foo',
						'type' => 'albums',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "The ID in the body ('foo') does not match the ID in the URL ('%albums.foo%').",
							'status' => '400',
						],
					],
				],
				'expectedStatus' => 400,
			]],
			'with no attributes' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
				'parameters' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
					],
				],
				'expected' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'foo',
							'release_date' => null,
						],
					],
				],
				'expectedStatus' => 200,
			]],
			'with valid attributes' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
				],
				'path' => '/albums/%albums.foo%',
				'parameters' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'bar',
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'bar',
							'release_date' => null,
						],
					],
				],
				'expectedStatus' => 200,
			]],
			'when changing a belongsTo relationship' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
					'artists' => [
						['title' => 'bar'],
					],
				],
				'path' => '/albums/%albums.foo%?include=artist',
				'parameters' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'relationships' => [
							'artist' => [
								'data' => [
									'id' => '%artists.bar%',
									'type' => 'artists',
								],
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'foo',
							'release_date' => null,
						],
						'relationships' => [
							'artist' => [
								'data' => [
									'id' => '%artists.bar%',
									'type' => 'artists',
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%artists.bar%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'bar',
								'filename' => null,
							],
						],
					],
				],
				'expectedStatus' => 200,
			]],
			'when removing a belongsTo relationship' => [[
				'records' => [
					'tags' => [
						['title' => 'foo'],
						['title' => 'bar', 'parent' => 'foo'],
					],
				],
				'path' => '/tags/%tags.bar%?include=parent',
				'parameters' => [
					'data' => [
						'id' => '%tags.bar%',
						'type' => 'tags',
						'relationships' => [
							'parent' => [
								'data' => null,
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%tags.bar%',
						'type' => 'tags',
						'attributes' => [
							'title' => 'bar',
						],
						'relationships' => [
							'parent' => [
								'data' => null,
							],
						],
					],
				],
				'expectedStatus' => 200,
			]],
			'when adding a belongsToMany relationship' => [[
				'records' => [
					'articles' => [
						['title' => 'foo'],
					],
					'tags' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'path' => '/articles/%articles.foo%?include=tags',
				'parameters' => [
					'data' => [
						'id' => '%articles.foo%',
						'type' => 'articles',
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
						'id' => '%articles.foo%',
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
				'expectedStatus' => 200,
			]],
			'when removing a belongsToMany relationship' => [[
				'records' => [
					'articles' => [
						['title' => 'foo'],
					],
					'tags' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
					],
					'article_tags' => [
						['article' => 'foo', 'tag' => 'a'],
						['article' => 'foo', 'tag' => 'b'],
						['article' => 'foo', 'tag' => 'c'],
					],
				],
				'path' => '/articles/%articles.foo%?include=tags',
				'parameters' => [
					'data' => [
						'id' => '%articles.foo%',
						'type' => 'articles',
						'relationships' => [
							'tags' => [
								'data' => [
									[
										'id' => '%tags.a%',
										'type' => 'tags',
									],
									[
										'id' => '%tags.c%',
										'type' => 'tags',
									],
								],
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%articles.foo%',
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
										'id' => '%tags.c%',
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
							'id' => '%tags.c%',
							'type' => 'tags',
							'attributes' => [
								'title' => 'c',
							],
						],
					],
				],
				'expectedStatus' => 200,
			]],
			'when adding a hasMany relationship' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
					'songs' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'path' => '/albums/%albums.foo%?include=album_songs,songs',
				'parameters' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
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
						'id' => '%albums.foo%',
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
				'expectedStatus' => 200,
			]],
			'when removing a hasMany relationship' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
					'songs' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
					],
					'album_songs' => [
						['album' => 'foo', 'song' => 'a', 'track' => 1],
						['album' => 'foo', 'song' => 'b', 'track' => 2],
						['album' => 'foo', 'song' => 'c', 'track' => 3],
					],
				],
				'path' => '/albums/%albums.foo%?include=album_songs',
				'parameters' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'relationships' => [
							'album_songs' => [
								'data' => [
									[
										'id' => '%album_songs.0%',
										'type' => 'album-song',
									],
									[
										'id' => '%album_songs.2%',
										'type' => 'album-song',
									],
								],
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'foo',
							'release_date' => null,
						],
						'relationships' => [
							'album_songs' => [
								'data' => [
									[
										'id' => '%album_songs.0%',
										'type' => 'album-song',
									],
									[
										'id' => '%album_songs.2%',
										'type' => 'album-song',
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%album_songs.0%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '1',
								'length' => null,
							],
						],
						[
							'id' => '%album_songs.2%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '3',
								'length' => null,
							],
						],
					],
				],
				'expectedStatus' => 200,
			]],
			'when changing a hasMany relationship' => [[
				'records' => [
					'albums' => [
						['title' => 'foo'],
					],
					'songs' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
					],
					'album_songs' => [
						['album' => 'foo', 'song' => 'a', 'track' => 1],
						['album' => 'foo', 'song' => 'b', 'track' => 2],
						['album' => 'foo', 'song' => 'c', 'track' => 3],
					],
				],
				'path' => '/albums/%albums.foo%?include=album_songs',
				'parameters' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'relationships' => [
							'album_songs' => [
								'data' => [
									[
										'id' => '%album_songs.0%',
										'type' => 'album-song',
									],
									[
										'id' => '%album_songs.1%',
										'type' => 'album-song',
									],
									[
										'id' => '%album_songs.2%',
										'type' => 'album-song',
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%album_songs.1%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '4',
							],
						],
					],
				],
				'expected' => [
					'data' => [
						'id' => '%albums.foo%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'foo',
							'release_date' => null,
						],
						'relationships' => [
							'album_songs' => [
								'data' => [
									[
										'id' => '%album_songs.0%',
										'type' => 'album-song',
									],
									[
										'id' => '%album_songs.1%',
										'type' => 'album-song',
									],
									[
										'id' => '%album_songs.2%',
										'type' => 'album-song',
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%album_songs.0%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '1',
								'length' => null,
							],
						],
						[
							'id' => '%album_songs.1%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '4',
								'length' => null,
							],
						],
						[
							'id' => '%album_songs.2%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '3',
								'length' => null,
							],
						],
					],
				],
				'expectedStatus' => 200,
			]],
		];
	}

	/**
	 * @dataProvider updateProvider
	 */
	public function testUpdate($args = [])
	{
		$records = $this->createRecords($args['records']);
		$args['parameters'] = $this->replaceIds($args['parameters'], $records);
		$args['expected'] = $this->replaceIds($args['expected'], $records);
		list($args['path']) = $this->replaceIds([$args['path']], $records);

		$response = $this->call('PUT', $args['path'], $args['parameters']);
		$response->assertExactJSON($args['expected']);
		$response->assertStatus($args['expectedStatus']);
	}
}
