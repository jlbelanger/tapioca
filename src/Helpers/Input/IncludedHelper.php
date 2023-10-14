<?php

namespace Jlbelanger\Tapioca\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\DataHelper;
use Jlbelanger\Tapioca\Helpers\Utilities;
use Illuminate\Support\Arr;

class IncludedHelper
{
	/**
	 * @param  array|mixed $included
	 * @return array
	 */
	public static function normalize($included) : array
	{
		if (empty($included)) {
			return [];
		}

		self::validate($included);

		foreach ($included as $i => $record) {
			$className = Utilities::getClassNameFromType($record['type']);
			$model = new $className();
			$whitelistedAttributes = $model->whitelistedAttributes();
			$whitelistedRelationships = $model->whitelistedRelationships();
			$included[$i] = DataHelper::normalize($record, $whitelistedAttributes, $whitelistedRelationships, 'included/' . $i);
		}

		return $included;
	}

	/**
	 * @param  array|mixed $included
	 * @return void
	 */
	protected static function validate($included) : void
	{
		if (!is_array($included) || Arr::isAssoc($included)) {
			throw JsonApiException::generate([
				'title' => __("':key' must be an array.", ['key' => 'included']),
				'detail' => __('eg. :example', ['example' => '{"included": []}']),
				'source' => [
					'pointer' => '/included',
				],
			], 400);
		}

		foreach ($included as $i => $record) {
			if (empty($record['id'])) {
				throw JsonApiException::generate([
					'title' => __("Included records must contain ':key' key.", ['key' => 'id']),
					'detail' => __('eg. :example', ['example' => '{"included": [{"id": "1", "type": "foo"}]}']),
					'source' => [
						'pointer' => '/included/' . $i,
					],
				], 400);
			}

			if (empty($record['type'])) {
				throw JsonApiException::generate([
					'title' => __("Included records must contain ':key' key.", ['key' => 'type']),
					'detail' => __('eg. :example', ['example' => '{"included": [{"id": "1", "type": "foo"}]}']),
					'source' => [
						'pointer' => '/included/' . $i,
					],
				], 400);
			}

			$className = Utilities::getClassNameFromType($record['type']);
			if (!class_exists($className)) {
				throw JsonApiException::generate([
					'title' => __("Type ':key' is invalid.", ['key' => $record['type']]),
					'source' => [
						'pointer' => '/included/' . $i . '/type',
					],
				], 400);
			}
		}
	}
}
