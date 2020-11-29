<?php

namespace Jlbelanger\LaravelJsonApi\Exceptions;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class ValidationException
{
	/**
	 * @param  array $data
	 * @return JsonApiException
	 */
	public static function generate(array $data) : JsonApiException
	{
		return new JsonApiException(json_encode(self::formatErrors($data)), 422);
	}

	/**
	 * @param  array $data
	 * @return array
	 */
	protected static function formatErrors(array $data) : array
	{
		$output = [];
		foreach ($data as $key => $errors) {
			foreach ($errors as $error) {
				$output[] = [
					'title' => $error,
					'source' => [
						'pointer' => '/data/' . str_replace('.', '/', $key),
					],
					'status' => '422',
				];
			}
		}
		return $output;
	}
}
