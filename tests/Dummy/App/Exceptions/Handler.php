<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

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

		$this->renderable(function (Throwable $e) {
			$code = $e->getCode() ? $e->getCode() : 500;
			$error = ['title' => 'There was an error connecting to the server.', 'status' => (string) $code];
			if (config('app.debug')) {
				$error['detail'] = $e->getMessage();
				$error['meta'] = [
					'exception' => get_class($e),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTrace(),
				];
			}
			return response()->json(['errors' => [$error]], $code);
		});
	}
}
