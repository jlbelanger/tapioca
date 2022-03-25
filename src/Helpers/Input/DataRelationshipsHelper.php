<?php

namespace Jlbelanger\Tapioca\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;

class DataRelationshipsHelper
{
	/**
	 * @param  array|mixed $data
	 * @param  array       $whitelistedRelationships
	 * @param  string      $prefix
	 * @return array
	 */
	public static function normalize($data, array $whitelistedRelationships, string $prefix = 'data') : array
	{
		if (empty($data['relationships'])) {
			$data['relationships'] = [];
			return $data;
		}

		self::validate($data['relationships'], $whitelistedRelationships, $prefix);

		// TODO: Validate relationship records exist?
		return $data;
	}

	/**
	 * @param  array|mixed $relationships
	 * @param  array       $whitelistedRelationships
	 * @param  string      $prefix
	 * @return void
	 */
	protected static function validate($relationships, array $whitelistedRelationships, string $prefix = 'data') : void
	{
		$isIncluded = strpos($prefix, 'included') !== false;

		if (!is_array($relationships)) {
			throw JsonApiException::generate([
				'title' => "'relationships' must be an object.",
				'detail' => $isIncluded
					? 'eg. {"included": [{"relationships": {}}]}'
					: 'eg. {"data": {"relationships": {}}}',
				'source' => [
					'pointer' => '/' . $prefix . '/relationships',
				],
			], 400);
		}

		foreach ($relationships as $key => $value) {
			if (empty($value)) {
				continue;
			}

			if (!is_array($value)) {
				throw JsonApiException::generate([
					'title' => "'$key' must be an object.",
					'detail' => $isIncluded
						? 'eg. {"included": [{"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}}]'
						: 'eg. {"data": {"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}}',
					'source' => [
						'pointer' => '/' . $prefix . '/relationships/' . $key,
					],
				], 400);
			}

			if (!array_key_exists('data', $value)) {
				throw JsonApiException::generate([
					'title' => "'$key' must contain a 'data' key.",
					'detail' => $isIncluded
						? 'eg. {"included": [{"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}]}'
						: 'eg. {"data": {"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}}',
					'source' => [
						'pointer' => '/' . $prefix . '/relationships/' . $key,
					],
				], 400);
			}

			/*
				// TODO: This won't work for to-many relationships.
				if (!array_key_exists('id', $value['data'])) {
					throw JsonApiException::generate([
						'title' => "'$key' must contain an 'id' key.",
						'detail' => $isIncluded
							? 'eg. {"included": [{"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}]}'
							: 'eg. {"data": {"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}}',
						'source' => [
							'pointer' => '/' . $prefix . '/relationships/' . $key . '/data,
						],
					], 400);
				}

				if (!array_key_exists('type', $value['data'])) {
					throw JsonApiException::generate([
						'title' => "'$key' must contain a 'type' key.",
						'detail' => $isIncluded
							? 'eg. {"included": [{"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}]}'
							: 'eg. {"data": {"relationships": {"' . $key . '": {"data": {"id": "1", "type": "foo"}}}}',
						'source' => [
							'pointer' => '/' . $prefix . '/relationships/' . $key . '/data,
						],
					], 400);
				}
			*/

			if (!in_array($key, $whitelistedRelationships)) {
				throw JsonApiException::generate([
					'title' => "'$key' is not a valid relationship.",
					'source' => [
						'pointer' => '/' . $prefix . '/relationships/' . $key,
					],
				], 400);
			}
		}
	}
}
