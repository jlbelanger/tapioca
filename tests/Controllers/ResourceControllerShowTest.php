<?php

namespace Jlbelanger\Tapioca\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Artist;
use Jlbelanger\Tapioca\Tests\TestCase;

class ResourceControllerShowTest extends TestCase
{
	use RefreshDatabase;

	public function showProvider()
	{
		return [
			'with no params' => [[
				'parameters' => [],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'Foo',
							'release_date' => null,
						],
					],
				],
			]],
			'with fields param' => [[
				'parameters' => [
					'fields' => [
						'albums' => 'title',
					],
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'Foo',
						],
					],
				],
			]],
			'with include param' => [[
				'parameters' => [
					'include' => 'artist',
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'Foo',
							'release_date' => null,
						],
						'relationships' => [
							'artist' => [
								'data' => [
									'id' => '%artist_id%',
									'type' => 'artists',
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%artist_id%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'Foo',
								'filename' => null,
							],
						],
					],
				],
			]],
			'with include and fields params' => [[
				'parameters' => [
					'include' => 'artist',
					'fields' => [
						'albums' => 'title',
						'artist' => 'title',
					],
				],
				'expected' => [
					'data' => [
						'id' => '%id%',
						'type' => 'albums',
						'attributes' => [
							'title' => 'Foo',
						],
						'relationships' => [
							'artist' => [
								'data' => [
									'id' => '%artist_id%',
									'type' => 'artists',
								],
							],
						],
					],
					'included' => [
						[
							'id' => '%artist_id%',
							'type' => 'artists',
							'attributes' => [
								'title' => 'Foo',
								'filename' => null,
							],
						],
					],
				],
			]],
		];
	}

	/**
	 * @dataProvider showProvider
	 */
	public function testShow($args)
	{
		$artist = Artist::factory()->create();
		$album = Album::factory()->create(['artist_id' => $artist->id]);
		$args['expected'] = $this->replaceToken($args['expected'], '%artist_id%', $artist->id);
		$args['expected'] = $this->replaceToken($args['expected'], '%id%', $album->id);

		$response = $this->call('GET', '/albums/' . $album->id, $args['parameters']);
		$response->assertExactJSON($args['expected']);
		$response->assertStatus(200);
	}
}
