<?php

namespace Jlbelanger\Tapioca\Tests\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Jlbelanger\Tapioca\Middleware\BodyValidationMiddleware;
use Jlbelanger\Tapioca\Tests\TestCase;

class BodyValidationMiddlewareTest extends TestCase
{
	public function handleProvider()
	{
		return [
			'with a GET request' => [[
				'uri' => '/albums/',
				'method' => 'GET',
				'parameters' => [],
				'expected' => [],
			]],
			'with a DELETE request' => [[
				'uri' => '/albums/123',
				'method' => 'DELETE',
				'parameters' => [],
				'expected' => [],
			]],
			'with a POST request and no data' => [[
				'uri' => '/albums/',
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
				'uri' => '/albums/',
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
				'uri' => '/albums/',
				'method' => 'POST',
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
			]],
			'with a POST request and id' => [[
				'uri' => '/albums/',
				'method' => 'POST',
				'parameters' => [
					'data' => [
						'id' => '1',
						'type' => 'albums',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "'data' cannot contain an 'id' key for POST requests.",
							'detail' => 'eg. {"data": {"type": "foo"}}',
							'status' => '400',
						],
					],
				],
			]],
			'with a valid POST request' => [[
				'uri' => '/albums/',
				'method' => 'POST',
				'parameters' => [
					'data' => [
						'type' => 'albums',
					],
				],
				'expected' => [],
			]],
			'with a PUT request and no data' => [[
				'uri' => '/albums/',
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
				'uri' => '/albums/',
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
				'uri' => '/albums/',
				'method' => 'PUT',
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
			]],
			'with a PUT request and no id' => [[
				'uri' => '/albums/',
				'method' => 'PUT',
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
			]],
			'with a PUT request and mismatched id' => [[
				'uri' => '/albums/123',
				'method' => 'PUT',
				'parameters' => [
					'data' => [
						'id' => '456',
						'type' => 'albums',
					],
				],
				'expected' => [
					'errors' => [
						[
							'title' => "The ID in the body ('456') does not match the ID in the URL ('123').",
							'status' => '400',
						],
					],
				],
			]],
			'with a valid PUT request' => [[
				'uri' => '/albums/123',
				'method' => 'PUT',
				'parameters' => [
					'data' => [
						'id' => '123',
						'type' => 'albums',
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
		$request->setRouteResolver(function () use ($args, $request) {
			$uri = str_replace('123', '{id}', $args['uri']);
			$route = new Route($args['method'], $uri, []);
			return $route->bind($request);
		});
		$response = (new BodyValidationMiddleware())->handle(
			$request,
			function ($request) {
				return new JsonResponse();
			}
		);
		$this->assertSame($args['expected'], $response->getData(true));
	}
}
