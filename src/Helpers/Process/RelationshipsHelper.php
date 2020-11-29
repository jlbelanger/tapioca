<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Process;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Process\AttributesHelper;

class RelationshipsHelper
{
	protected static $tempIdPrefix = 'temp-';

	/**
	 * @param  Model $record
	 * @param  array $relationships
	 * @param  array $included
	 * @return array
	 */
	public static function update(Model $record, array $relationships, array $included) : array
	{
		$output = [];

		foreach ($relationships as $key => $relData) {
			$funcName = Str::camel($key);
			$existing = $record->$funcName();
			$className = class_basename($existing);

			if ($className === 'BelongsToMany') {
				$result = self::updateBelongsToMany($relData, $existing);
			} elseif ($className === 'HasMany') {
				$result = self::updateHasMany($relData, $existing, $key, $record, $included);
			} else {
				throw JsonApiException::generate([
					'title' => "Relationship type '$className' is not supported.",
					'source' => [
						'pointer' => "/data/relationships/$key",
					],
				], 400);
			}

			$output[$key] = [
				'delete' => $result['deleteIds'],
				'add' => $result['addIds'],
				'deleted' => $result['deleted'],
			];
		}

		return $output;
	}

	/**
	 * @param  array         $relData
	 * @param  BelongsToMany $existing
	 * @return array
	 */
	protected static function updateBelongsToMany(array $relData, BelongsToMany $existing) : array
	{
		$existingIds = array_map('strval', $existing->pluck($existing->getRelatedPivotKeyName())->toArray());
		$newIds = array_map('strval', Arr::pluck($relData['data'], 'id'));

		$deleteIds = array_values(array_diff($existingIds, $newIds));
		if (!empty($deleteIds)) {
			$existing->detach($deleteIds);
		}

		$addIds = array_values(array_diff($newIds, $existingIds));
		if (!empty($addIds)) {
			$existing->attach($addIds);
		}

		return [
			'deleteIds' => $deleteIds,
			'addIds' => $addIds,
			'deleted' => [],
		];
	}

	/**
	 * Note: This is not in the JSON API spec.
	 *
	 * @param  array   $relData
	 * @param  HasMany $existing
	 * @param  string  $key
	 * @param  Model   $record
	 * @param  array   $included
	 * @return array
	 */
	protected static function updateHasMany(array $relData, HasMany $existing, string $key, Model $record, array $included) : array
	{
		$existingIds = array_map('strval', $existing->pluck($existing->getLocalKeyName())->toArray());
		$newIds = array_map('strval', Arr::pluck($relData['data'], 'id'));

		$deleteIds = array_values(array_diff($existingIds, $newIds));
		$deleteData = [];
		if (!empty($deleteIds)) {
			$deletable = $existing->whereIn('id', $deleteIds);
			$attributes = array_merge(['id'], $deletable->getRelated()->getFillable());
			$deleteTerms = $deletable->select($attributes)->getResults()->toArray();
			foreach ($deleteTerms as $term) {
				$deleteData[$term['id']] = Arr::only($term, $attributes);
			}
			$deletable->delete();
		}

		$model = get_class($existing->getRelated());

		$addIds = [];
		foreach ($relData['data'] as $rel) {
			if (strpos($rel['id'], self::$tempIdPrefix) !== 0) {
				continue;
			}

			$includedData = self::findIncluded($included, $rel['id'], $rel['type']);
			if (empty($includedData)) {
				throw JsonApiException::generate([
					'title' => "Record with id '" . $rel['id'] . "' and type '" . $rel['type'] . "' not found in 'included'.",
					'source' => [
						'pointer' => "/data/relationships/$key",
					],
				], 400);
			}

			// TODO: Nested relationships won't work.
			$relRecord = new $model();
			$includedData = DataHelper::normalize($includedData, $relRecord->whitelistedAttributes(), $relRecord->whitelistedRelationships());
			$includedData['attributes'][$record->getForeignKey()] = $record->id;
			$includedData = AttributesHelper::convertSingularRelationships($includedData);
			$new = $relRecord->create($includedData['attributes']);
			$addIds[] = (string) $new->id;
		}

		return [
			'deleteIds' => $deleteIds,
			'addIds' => $addIds,
			'deleted' => $deleteData,
		];
	}

	/**
	 * @param  array  $included
	 * @param  string $id
	 * @param  string $type
	 * @return array
	 */
	protected static function findIncluded(array $included, string $id, string $type) : array
	{
		foreach ($included as $record) {
			if ((string) $record['id'] === (string) $id && $record['type'] === $type) {
				return $record;
			}
		}
		return [];
	}
}
