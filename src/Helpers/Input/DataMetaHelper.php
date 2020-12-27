<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class DataMetaHelper
{
	/**
	 * @param  array|mixed $data
	 * @param  string      $prefix
	 * @return array
	 */
	public static function normalize($data, string $prefix = 'data') : array
	{
		if (empty($data['meta'])) {
			$data['meta'] = [];
			return $data;
		}
		self::validate($data['meta'], $prefix);
		return $data;
	}

	/**
	 * @param  array|mixed $meta
	 * @param  string      $prefix
	 * @return void
	 */
	protected static function validate($meta, string $prefix = 'data') : void
	{
		$isIncluded = strpos($prefix, 'included') !== false;

		if (!is_array($meta)) {
			throw JsonApiException::generate([
				'title' => "'meta' must be an object.",
				'detail' => $isIncluded
					? 'eg. {"included": [{"meta": {}}]}'
					: 'eg. {"data": {"meta": {}}}',
				'source' => [
					'pointer' => '/' . $prefix . '/meta',
				],
			], 400);
		}
	}
}
