<?php

namespace Jlbelanger\Tapioca\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\DataAttributesHelper;
use Jlbelanger\Tapioca\Helpers\Input\DataMetaHelper;
use Jlbelanger\Tapioca\Helpers\Input\DataRelationshipsHelper;

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

	/**
	 * @param  array $data
	 * @return array
	 */
	public static function convertEmptyStringsToNull(array $data) : array
	{
		foreach ($data as $key => $value) {
			if (is_string($value) && $value === '') {
				$data[$key] = null;
			} elseif (is_array($value)) {
				$data[$key] = self::convertEmptyStringsToNull($data[$key]);
			}
		}
		return $data;
	}
}
