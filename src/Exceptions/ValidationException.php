<?php

namespace Jlbelanger\LaravelJsonApi\Exceptions;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class ValidationException
{
	/**
	 * @param  array  $data
	 * @param  string $prefix
	 * @return JsonApiException
	 */
	public static function generate(array $data, string $prefix = 'data') : JsonApiException
	{
		return new JsonApiException(json_encode(self::formatErrors($data, $prefix)), 422);
	}

	/**
	 * @param  array  $data
	 * @param  string $prefix
	 * @return array
	 */
	public static function formatErrors(array $data, string $prefix = 'data') : array
	{
		$output = [];
		foreach ($data as $key => $errors) {
			foreach ($errors as $error) {
				$output[] = [
					'title' => $error,
					'source' => [
						'pointer' => '/' . $prefix . '/' . str_replace('.', '/', $key),
					],
					'status' => '422',
				];
			}
		}
		return $output;
	}
}
