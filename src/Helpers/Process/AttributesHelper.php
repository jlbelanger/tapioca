<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Process;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Jlbelanger\LaravelJsonApi\Helpers\JsonApiRequest;

class AttributesHelper
{
	/**
	 * @param  Model          $record
	 * @param  JsonApiRequest $req
	 * @param  boolean        $isUpdate
	 * @return Model
	 */
	public static function process(Model $record, JsonApiRequest $req, bool $isUpdate = false) : array
	{
		$data = $req->getData();
		$data = self::convertSingularRelationships($data, $record);

		if ($isUpdate) {
			if (!empty($data['attributes'])) {
				$record->update($data['attributes']);
			}
		} else {
			$attributes = [];
			if (!empty($data['attributes'])) {
				$attributes = $data['attributes'];
			}
			foreach ($attributes as $key => $value) {
				$record->$key = $value;
			}
			$defaultAttributes = $record->defaultAttributes($data);
			$attributes = array_merge($attributes, $defaultAttributes);
			$record = $record->create($attributes);
		}

		return [$record, $data];
	}

	/**
	 * @param  array $data
	 * @param  Model $record
	 * @return array
	 */
	public static function convertSingularRelationships(array $data, Model $record) : array
	{
		$fillable = $record->getFillable();

		foreach ($data['relationships'] as $key => $value) {
			$attribute = Str::snake($key) . '_id';
			if (!in_array($attribute, $fillable)) {
				continue;
			}

			if ($value['data'] === null) {
				// This is a singular relationship that is being removed.
				unset($data['relationships'][$key]);
				$data['attributes'][$attribute] = null;
			} elseif (array_key_exists('id', $value['data'])) {
				// This is a singular relationship that is being updated.
				unset($data['relationships'][$key]);
				$data['attributes'][$attribute] = $value['data']['id'];
			}
		}

		return $data;
	}
}
