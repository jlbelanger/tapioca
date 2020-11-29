<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jlbelanger\LaravelJsonApi\Middleware\BodyValidationMiddleware;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class BodyValidationMiddlewareTest extends TestCase
{
	public function handleProvider()
	{
		return [
			'with a GET request' => [[
				'uri' => '/users/',
				'method' => 'GET',
				'parameters' => [],
				'expected' => [],
			]],
			'with a DELETE request' => [[
				'uri' => '/users/1',
				'method' => 'DELETE',
				'parameters' => [],
				'expected' => [],
			]],
			'with a POST request and no data' => [[
				'uri' => '/users/',
				'method' => 'POST',
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
			]],
			'with a POST request and no type' => [[
				'uri' => '/users/',
				'method' => 'POST',
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
			]],
			'with a POST request and mismatched type' => [[
				'uri' => '/users/',
				'method' => 'POST',
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
			]],
			'with a valid POST request' => [[
				'uri' => '/users/',
				'method' => 'POST',
				'parameters' => [
					'data' => [
						'type' => 'users',
					],
				],
				'expected' => [],
			]],
			'with a PUT request and no data' => [[
				'uri' => '/users/',
				'method' => 'PUT',
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
			]],
			'with a PUT request and no type' => [[
				'uri' => '/users/',
				'method' => 'PUT',
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
			]],
			'with a PUT request and mismatched type' => [[
				'uri' => '/users/',
				'method' => 'PUT',
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
			]],
			'with a PUT request and no id' => [[
				'uri' => '/users/',
				'method' => 'PUT',
				'parameters' => [
					'data' => [
						'type' => 'users',
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
			]],
			'with a PUT request and mismatched id' => [[
				'uri' => '/users/1',
				'method' => 'PUT',
				'parameters' => [
					'data' => [
						'id' => '2',
						'type' => 'users',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "The ID in the body ('2') does not match the ID in the URL ('1').",
							'status' => '400',
						],
					],
				],
			]],
			'with a valid PUT request' => [[
				'uri' => '/users/1',
				'method' => 'PUT',
				'parameters' => [
					'data' => [
						'id' => '1',
						'type' => 'users',
					],
				],
				'expected' => [],
			]],
		];
	}

	/**
	 * @dataProvider handleProvider
	 */
	public function testHandle($args)
	{
		$request = Request::create($args['uri'], $args['method'], $args['parameters']);
		$response = (new BodyValidationMiddleware())->handle(
			$request,
			function ($request) {
				return new JsonResponse();
			}
		);
		$this->assertSame($args['expected'], $response->getData(true));
	}
}
