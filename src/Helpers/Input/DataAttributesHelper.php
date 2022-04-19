<?php

namespace Jlbelanger\Tapioca\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;

class DataAttributesHelper
{
	/**
	 * @param  array|mixed $data
	 * @param  array       $whitelistedAttributes
	 * @param  string      $prefix
	 * @return array
	 */
	public static function normalize($data, array $whitelistedAttributes, string $prefix = 'data') : array
	{
		if (empty($data['attributes'])) {
			$data['attributes'] = [];
			return $data;
		}

		self::validate($data['attributes'], $whitelistedAttributes, $prefix);

		return $data;
	}

	/**
	 * @param  array|mixed $attributes
	 * @param  array       $whitelistedAttributes
	 * @param  string      $prefix
	 * @return void
	 */
	protected static function validate($attributes, array $whitelistedAttributes, string $prefix = 'data') : void
	{
		$isIncluded = strpos($prefix, 'included') !== false;

		if (!is_array($attributes)) {
			throw JsonApiException::generate([
				'title' => "'attributes' must be an object.",
				'detail' => $isIncluded ? 'eg. {"included": [{"attributes": {}}]}' : 'eg. {"data": {"attributes": {}}}',
				'source' => [
					'pointer' => '/' . $prefix . '/attributes',
				],
			], 400);
		}

		foreach ($attributes as $key => $value) {
			if (!in_array($key, $whitelistedAttributes)) {
				throw JsonApiException::generate([
					'title' => "'$key' is not a valid attribute.",
					'source' => [
						'pointer' => '/' . $prefix . '/attributes/' . $key,
					],
				], 400);
			}
		}
	}
}
