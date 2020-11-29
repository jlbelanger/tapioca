<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

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
						'id' => '%articles.Foo%',
						'type' => 'articles',
						'attributes' => [
							'title' => 'Foo',
							'content' => null,
							'word_count' => null,
						],
					],
				],
			]],
			'with fields param' => [[
				'parameters' => [
					'fields' => [
						'articles' => 'title',
					],
				],
				'expected' => [
					'data' => [
						'id' => '%articles.Foo%',
						'type' => 'articles',
						'attributes' => [
							'title' => 'Foo',
						],
					],
				],
			]],
			'with include param' => [[
				'parameters' => [
					'include' => 'user',
				],
				'expected' => [
					'data' => [
						'id' => '%articles.Foo%',
						'type' => 'articles',
						'attributes' => [
							'title' => 'Foo',
							'content' => null,
							'word_count' => null,
						],
						'relationships' => [
							'user' => [
								'data' => [
									'id' => '1',
									'type' => 'users',
								],
							],
						],
					],
					'included' => [
						[
							'id' => '1',
							'type' => 'users',
							'attributes' => [
								'username' => 'foo',
								'email' => 'foo@example.com',
							],
						],
					],
				],
			]],
			'with include and fields params' => [[
				'parameters' => [
					'include' => 'user',
					'fields' => [
						'articles' => 'title',
						'users' => 'username',
					],
				],
				'expected' => [
					'data' => [
						'id' => '%articles.Foo%',
						'type' => 'articles',
						'attributes' => [
							'title' => 'Foo',
						],
						'relationships' => [
							'user' => [
								'data' => [
									'id' => '1',
									'type' => 'users',
								],
							],
						],
					],
					'included' => [
						[
							'id' => '1',
							'type' => 'users',
							'attributes' => [
								'username' => 'foo',
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
		$article = Article::factory()->create();
		$records = [
			'articles' => [
				'Foo' => (string) $article->id,
			],
		];
		$expected = $this->replaceIds($args['expected'], $records);

		$response = $this->call('GET', '/articles/' . $article->id, $args['parameters']);
		$response->assertExactJSON($expected);
		$response->assertStatus(200);
	}
}
