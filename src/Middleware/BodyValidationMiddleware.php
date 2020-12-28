<?php

namespace Jlbelanger\LaravelJsonApi\Middleware;

use Closure;
use Illuminate\Http\Request;

class BodyValidationMiddleware
{
	/**
	 * Handles incoming requests.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		$method = $request->method();
		if (!in_array($method, ['PUT', 'POST'])) {
			return $next($request);
		}

		$errors = [];
		$input = $request->input();

		if (!array_key_exists('data', $input)) {
			$errors[] = [
				'title' => "The body must contain a 'data' key.",
				'detail' => 'eg. {"data": {"type": "foo"}}',
				'status' => '400',
			];
			return response()->json(['errors' => $errors], 400);
		}

		$bodyType = $request->input('data.type');
		if (!$bodyType) {
			$errors[] = [
				'title' => "'data' must contain a 'type' key.",
				'detail' => 'eg. {"data": {"type": "foo"}}',
				'status' => '400',
			];
			return response()->json(['errors' => $errors], 400);
		}

		$urlType = $request->segments()[0];
		if ($bodyType !== $urlType) {
			$errors[] = [
				'title' => "The type in the body ('" . $bodyType . "') does not match the type in the URL ('" . $urlType . "').",
				'status' => '400',
			];
			return response()->json(['errors' => $errors], 400);
		}

		if ($method === 'PUT') {
			$bodyId = $request->input('data.id');
			if (!$bodyId) {
				$errors[] = [
					'title' => "'data' must contain an 'id' key.",
					'detail' => 'eg. {"data": {"id": "1", "type": "foo"}}',
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}

			$urlId = $request->segments()[1];
			if ($bodyId !== $urlId) {
				$errors[] = [
					'title' => "The ID in the body ('" . $bodyId . "') does not match the ID in the URL ('" . $urlId . "').",
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}
		} else {
			if ($request->input('data.id')) {
				$errors[] = [
					'title' => "'data' cannot contain an 'id' key for POST requests.",
					'detail' => 'eg. {"data": {"type": "foo"}}',
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}
		}

		return $next($request);
	}
}
