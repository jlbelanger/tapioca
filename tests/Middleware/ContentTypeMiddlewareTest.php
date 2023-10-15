<?php

namespace Jlbelanger\Tapioca\Tests\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jlbelanger\Tapioca\Middleware\ContentTypeMiddleware;
use Jlbelanger\Tapioca\Tests\TestCase;

class ContentTypeMiddlewareTest extends TestCase
{
	public function testHandle() : void
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
