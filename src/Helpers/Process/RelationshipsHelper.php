<?php

namespace Jlbelanger\Tapioca\Helpers\Process;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Process\RelationshipsHasManyHelper;

class RelationshipsHelper
{
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
				$result = RelationshipsHasManyHelper::update($relData, $existing, $key, $record, $included);
			} elseif ($className === 'HasOneThrough') {
				$result = RelationshipsHasOneThroughHelper::update($relData, $existing, $record);
			} else {
				throw JsonApiException::generate([
					'title' => __("Relationship type ':key' is not supported.", ['key' => $className]),
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
			$attributes = [];
			if ($existing->createdAt()) {
				$attributes[$existing->createdAt()] = Date::now();
			}
			$existing->attach($addIds, $attributes);
		}

		return [
			'deleteIds' => $deleteIds,
			'addIds' => $addIds,
			'deleted' => [],
		];
	}
}
