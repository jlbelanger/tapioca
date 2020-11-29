<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Illuminate\Database\Eloquent\Model;
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
	 * @return array
	 */
	public static function normalize($data, array $whitelistedAttributes, array $whitelistedRelationships) : array
	{
		if ($data === null) {
			return [];
		}
		self::validate($data);
		$data = DataAttributesHelper::normalize($data, $whitelistedAttributes);
		$data = DataRelationshipsHelper::normalize($data, $whitelistedRelationships);
		$data = DataMetaHelper::normalize($data);
		return $data;
	}

	/**
	 * @param  array|mixed $data
	 * @return void
	 */
	protected static function validate($data) : void
	{
		if (!is_array($data)) {
			throw JsonApiException::generate([
				'title' => "'data' must be an array.",
			], 400);
		}

		$allowedKeys = [
			'id',
			'type',
			'attributes',
			'relationships',
			'included',
			'meta',
		];
		$keys = array_keys($data);
		$disallowedKeys = array_diff($keys, $allowedKeys);
		if (!empty($disallowedKeys)) {
			throw JsonApiException::generate([
				'title' => "'data' contains disallowed keys: '" . implode("', '", $disallowedKeys) . "'.",
			], 400);
		}
	}
}
