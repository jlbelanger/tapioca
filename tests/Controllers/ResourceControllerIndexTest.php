<?php

namespace Jlbelanger\Tapioca\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Tests\TestCase;

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
					'articles' => [
						['title' => 'a'],
						['title' => 'b'],
					],
					'tags' => [
						['title' => 'c'],
						['title' => 'd'],
						['title' => 'e'],
						['title' => 'f'],
					],
					'article_tags' => [
						['article' => 'a', 'tag' => 'c'],
						['article' => 'a', 'tag' => 'd'],
						['article' => 'b', 'tag' => 'e'],
						['article' => 'b', 'tag' => 'f'],
					],
				],
				'path' => '/articles',
				'parameters' => [
					'include' => 'tags',
				],
				'expected' => [
					'data' => [
						[
							'id' => '%articles.a%',
							'type' => 'articles',
							'attributes' => [
								'title' => 'a',
								'content' => null,
								'word_count' => null,
							],
							'relationships' => [
								'tags' => [
									'data' => [
										[
											'id' => '%tags.c%',
											'type' => 'tags',
										],
										[
											'id' => '%tags.d%',
											'type' => 'tags',
										],
									],
								],
							],
						],
						[
							'id' => '%articles.b%',
							'type' => 'articles',
							'attributes' => [
								'title' => 'b',
								'content' => null,
								'word_count' => null,
							],
							'relationships' => [
								'tags' => [
									'data' => [
										[
											'id' => '%tags.e%',
											'type' => 'tags',
										],
										[
											'id' => '%tags.f%',
											'type' => 'tags',
										],
									],
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%tags.c%',
							'type' => 'tags',
							'attributes' => [
								'title' => 'c',
							],
						],
						[
							'id' => '%tags.d%',
							'type' => 'tags',
							'attributes' => [
								'title' => 'd',
							],
						],
						[
							'id' => '%tags.e%',
							'type' => 'tags',
							'attributes' => [
								'title' => 'e',
							],
						],
						[
							'id' => '%tags.f%',
							'type' => 'tags',
							'attributes' => [
								'title' => 'f',
							],
						],
					],
				],
			]],
			'with include param for hasMany' => [[
				'records' => [
					'albums' => [
						['title' => 'a'],
						['title' => 'b'],
					],
					'album_songs' => [
						['album' => 'a'],
						['album' => 'a'],
						['album' => 'b'],
						['album' => 'b'],
					],
				],
				'path' => '/albums',
				'parameters' => [
					'include' => 'album_songs',
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
								'album_songs' => [
									'data' => [
										[
											'id' => '%album_songs.2%',
											'type' => 'album-song',
										],
										[
											'id' => '%album_songs.3%',
											'type' => 'album-song',
										],
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
								'track' => '1',
								'length' => null,
							],
						],
						[
							'id' => '%album_songs.2%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '1',
								'length' => null,
							],
						],
						[
							'id' => '%album_songs.3%',
							'type' => 'album-song',
							'attributes' => [
								'track' => '1',
								'length' => null,
							],
						],
					],
				],
			]],
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
