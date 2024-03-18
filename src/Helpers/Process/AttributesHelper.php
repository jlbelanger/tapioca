<?php

namespace Jlbelanger\Tapioca\Helpers\Process;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Str;
use Jlbelanger\Tapioca\Helpers\JsonApiRequest;

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
	 * @param  array             $data
	 * @param  Model             $record
	 * @param  HasOneOrMany|null $existing
	 * @return array
	 */
	public static function convertSingularRelationships(array $data, Model $record, ?HasOneOrMany $existing = null) : array
	{
		$fillable = $record->getFillable();
		$className = $existing ? class_basename($existing) : '';
		$singularRelationships = $record->singularRelationships();
		$relToColumn = [];
		foreach ($singularRelationships as $rel) {
			$fn = Str::camel($rel);
			$relToColumn[$rel] = $record->$fn()->getForeignKeyName();
		}

		foreach ($data['relationships'] as $key => $value) {
			$cleanKey = Str::snake($key);
			if (empty($relToColumn[$cleanKey]) || !in_array($relToColumn[$cleanKey], $fillable)) {
				continue;
			}
			$attribute = $relToColumn[$cleanKey];

			$typeAttribute = $cleanKey . '_type';

			if ($value['data'] === null) {
				// This is a singular relationship that is being removed.
				unset($data['relationships'][$key]);
				$data['attributes'][$attribute] = null;
				if ($className === 'MorphMany' && in_array($typeAttribute, $fillable)) {
					$data['attributes'][$typeAttribute] = null;
				}
			} elseif (array_key_exists('id', $value['data'])) {
				// This is a singular relationship that is being updated.
				unset($data['relationships'][$key]);
				$data['attributes'][$attribute] = $value['data']['id'];
				if ($className === 'MorphMany' && in_array($typeAttribute, $fillable)) {
					$data['attributes'][$typeAttribute] = $value['data']['type'];
				}
			}
		}

		return $data;
	}
}
