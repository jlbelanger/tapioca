<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class DataAttributesHelper
{
	/**
	 * @param  array|mixed $data
	 * @param  array       $whitelistedAttributes
	 * @return array
	 */
	public static function normalize($data, array $whitelistedAttributes) : array
	{
		if (empty($data['attributes'])) {
			$data['attributes'] = [];
			return $data;
		}

		self::validate($data['attributes'], $whitelistedAttributes);

		return $data;
	}

	/**
	 * @param  array|mixed $attributes
	 * @param  array       $whitelistedAttributes
	 * @return void
	 */
	protected static function validate($attributes, array $whitelistedAttributes) : void
	{
		if (!is_array($attributes)) {
			throw JsonApiException::generate([
				'title' => "'attributes' must be an object.",
				'detail' => 'eg. {"data": {"attributes": {}}}',
				'source' => [
					'pointer' => '/data/attributes',
				],
			], 400);
		}

		foreach ($attributes as $key => $value) {
			if (!in_array($key, $whitelistedAttributes)) {
				throw JsonApiException::generate([
					'title' => "'$key' is not a valid attribute.",
					'source' => [
						'pointer' => "/data/attributes/$key",
					],
				], 400);
			}
		}
	}
}
