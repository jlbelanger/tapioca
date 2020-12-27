<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataAttributesHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataMetaHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataRelationshipsHelper;

class DataHelper
{
	/**
	 * @param  array|mixed $data
	 * @param  array       $whitelistedAttributes
	 * @param  array       $whitelistedRelationships
	 * @param  string      $prefix
	 * @return array
	 */
	public static function normalize($data, array $whitelistedAttributes, array $whitelistedRelationships, string $prefix = 'data') : array
	{
		if ($data === null) {
			return [];
		}
		self::validate($data, $prefix);
		$data = DataAttributesHelper::normalize($data, $whitelistedAttributes, $prefix);
		$data = DataRelationshipsHelper::normalize($data, $whitelistedRelationships, $prefix);
		$data = DataMetaHelper::normalize($data, $prefix);
		return $data;
	}

	/**
	 * @param  array|mixed $data
	 * @param  string      $prefix
	 * @return void
	 */
	protected static function validate($data, string $prefix = 'data') : void
	{
		if (!is_array($data)) {
			throw JsonApiException::generate([
				'title' => "'data' must be an object.",
				'detail' => 'eg. {"data": {}}',
			], 400);
		}

		$allowedKeys = [
			'id',
			'type',
			'attributes',
			'relationships',
			'meta',
		];
		if (strpos($prefix, 'included') === false) {
			$allowedKeys[] = 'included';
		}
		$keys = array_keys($data);
		$disallowedKeys = array_diff($keys, $allowedKeys);
		if (!empty($disallowedKeys)) {
			throw JsonApiException::generate([
				'title' => "'data' contains disallowed keys: '" . implode("', '", $disallowedKeys) . "'.",
			], 400);
		}
	}
}
