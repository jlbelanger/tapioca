<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jlbelanger\LaravelJsonApi\Middleware\ContentTypeMiddleware;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ContentTypeMiddlewareTest extends TestCase
{
	public function testHandle()
	{
		$request = new Request();
		$response = (new ContentTypeMiddleware())->handle(
			$request,
			function ($request) {
				return new JsonResponse();
			}
		);
		$this->assertSame('application/vnd.api+json', $response->headers->get('Content-Type'));
	}
}
