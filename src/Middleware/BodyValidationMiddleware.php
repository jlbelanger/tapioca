<?php

namespace Jlbelanger\Tapioca\Middleware;

use Closure;
use Illuminate\Http\Request;

class BodyValidationMiddleware
{
	/**
	 * Handles incoming requests.
	 * TODO: Fix cyclomatic complexity.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next) // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
	{
		$method = $request->method();
		if (!in_array($method, ['PUT', 'POST'])) {
			return $next($request);
		}

		$errors = [];
		$input = $request->input();

		if (strpos($request->header('Content-Type'), 'multipart/form-data') === 0) {
			if (!$request->has('json') || !$request->has('files')) {
				$errors[] = [
					'title' => __("Multipart requests must contain a 'json' and a 'files' value."),
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}

			$input = json_decode($request->input('json'), true);
			if (!is_array($input)) {
				$errors[] = [
					'title' => __("':key' must be an object.", ['key' => 'json']),
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}
		}

		if (!array_key_exists('data', $input)) {
			$errors[] = [
				'title' => __("The body must contain a 'data' key."),
				'detail' => __('eg. :example', ['example' => '{"data": {"type": "foo"}}']),
				'status' => '400',
			];
			return response()->json(['errors' => $errors], 400);
		}

		$bodyType = !empty($input['data']['type']) ? $input['data']['type'] : null;
		if (!$bodyType) {
			$errors[] = [
				'title' => __("':key1' must contain ':key2' key.", ['key1' => 'data', 'key2' => 'type']),
				'detail' => __('eg. :example', ['example' => '{"data": {"type": "foo"}}']),
				'status' => '400',
			];
			return response()->json(['errors' => $errors], 400);
		}

		$segments = $request->segments();
		$route = $request->route();
		if (!empty($route) && !empty($route->parameters())) {
			$urlId = array_pop($segments);
		} else {
			$urlId = null;
		}
		$urlType = array_pop($segments);
		if ($bodyType !== $urlType) {
			$errors[] = [
				'title' => __("The type in the body (':key1') does not match the type in the URL (':key2').", ['key1' => $bodyType, 'key2' => $urlType]),
				'status' => '400',
			];
			return response()->json(['errors' => $errors], 400);
		}

		if ($method === 'PUT') {
			$bodyId = !empty($input['data']['id']) ? $input['data']['id'] : null;
			if (!$bodyId) {
				$errors[] = [
					'title' => __("':key1' must contain ':key2' key.", ['key1' => 'data', 'key2' => 'id']),
					'detail' => __('eg. :example', ['example' => '{"data": {"id": "1", "type": "foo"}}']),
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}

			if ($bodyId !== $urlId) {
				$errors[] = [
					'title' => __("The ID in the body (':key1') does not match the ID in the URL (':key2').", ['key1' => $bodyId, 'key2' => $urlId]),
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}
		} else {
			$bodyId = !empty($input['data']['id']) ? $input['data']['id'] : null;
			if ($bodyId) {
				$errors[] = [
					'title' => __("'data' cannot contain an 'id' key for POST requests."),
					'detail' => __('eg. :example', ['example' => '{"data": {"type": "foo"}}']),
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}
		}

		return $next($request);
	}
}
