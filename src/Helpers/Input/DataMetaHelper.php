<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class DataMetaHelper
{
	/**
	 * @param  array|mixed $data
	 * @return array
	 */
	public static function normalize($data) : array
	{
		if (empty($data['meta'])) {
			$data['meta'] = [];
			return $data;
		}
		self::validate($data['meta']);
		return $data;
	}

	/**
	 * @param  array|mixed $meta
	 * @return void
	 */
	protected static function validate($meta) : void
	{
		if (!is_array($meta)) {
			throw JsonApiException::generate([
				'title' => "'meta' must be an object.",
				'detail' => 'eg. {"data": {"meta": {}}}',
				'source' => [
					'pointer' => '/data/meta',
				],
			], 400);
		}
	}
}
