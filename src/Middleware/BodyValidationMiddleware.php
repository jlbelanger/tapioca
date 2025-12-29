<?php

namespace Jlbelanger\Tapioca\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

		if (strpos($request->header('Content-Type'), 'multipart/form-data') === 0) {
			$detail = [];
			if (!$request->has('data')) {
				$detail[] = __("'data' value is missing.");
			}
			if (!$request->has('meta.files')) {
				$detail[] = __("'meta.files' value is missing.");
			}
			if ($detail) {
				$errors[] = [
					'title' => __("Multipart requests must contain 'data' and 'meta.files' value."),
					'detail' => implode(' ', $detail),
					'status' => '400',
				];
				return response()->json(['errors' => $errors], 400);
			}

			$newData = self::decodeValues($request->all(), collect())->undot()->toArray();
			$request->replace($newData);
		}

		$input = $request->input();

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

	/**
	 * @param  array      $values
	 * @param  Collection $data
	 * @param  string     $prefix
	 * @return Collection
	 */
	protected static function decodeValues(array $values, Collection $data, string $prefix = '') : Collection
	{
		foreach ($values as $key => $value) {
			$newKey = ($prefix ? $prefix . '.' : '') . $key;
			if (is_array($value)) {
				$data = self::decodeValues($value, $data, $newKey);
			} elseif (!is_string($value)) { // Eg. Illuminate\Http\UploadedFile.
				$data[$newKey] = $value;
			} else {
				$data[$newKey] = json_decode($value, true);
			}
		}
		return $data;
	}
}
