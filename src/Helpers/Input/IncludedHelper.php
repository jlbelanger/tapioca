<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
			$className = self::getClassName($record['type']);
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
				'title' => "'included' must be an array.",
				'detail' => 'eg. {"included": []}',
				'source' => [
					'pointer' => '/included',
				],
			], 400);
		}

		foreach ($included as $i => $record) {
			if (empty($record['id'])) {
				throw JsonApiException::generate([
					'title' => "Included records must contain 'id' key.",
					'detail' => 'eg. {"included": [{"id": "1", "type": "foo"}]}',
					'source' => [
						'pointer' => '/included/' . $i,
					],
				], 400);
			}

			if (empty($record['type'])) {
				throw JsonApiException::generate([
					'title' => "Included records must contain 'type' key.",
					'detail' => 'eg. {"included": [{"id": "1", "type": "foo"}]}',
					'source' => [
						'pointer' => '/included/' . $i,
					],
				], 400);
			}

			$className = self::getClassName($record['type']);
			if (!class_exists($className)) {
				throw JsonApiException::generate([
					'title' => "Type '" . $record['type'] .  "' is invalid.",
					'source' => [
						'pointer' => '/included/' . $i . '/type',
					],
				], 400);
			}
		}
	}

	/**
	 * @param  string $type
	 * @return string
	 */
	protected static function getClassName(string $type) : string
	{
		return config('laraveljsonapi.models_path', 'App\\Models\\') . Str::studly(Str::singular($type));
	}
}
