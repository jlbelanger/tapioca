<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
	/**
	 * Registers the exception handling callbacks for the application.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->renderable(function (MethodNotAllowedHttpException $e) {
			return response()->json(['errors' => [['title' => 'URL does not exist.', 'status' => '404', 'detail' => 'Method not allowed.']]], 404);
		});
		$this->renderable(function (JsonApiException $e) {
			return response()->json(['errors' => $e->getErrors()], $e->getCode());
		});
		$this->renderable(function (HttpException $e) {
			return response()->json(['errors' => [['title' => $e->getMessage(), 'status' => $e->getStatusCode()]]], $e->getStatusCode());
		});
	}
}
