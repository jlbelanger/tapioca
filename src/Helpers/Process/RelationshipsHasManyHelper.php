<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Process;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Process\AttributesHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Utilities;

class RelationshipsHasManyHelper
{
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
	public static function update(array $relData, HasMany $existing, string $key, Model $record, array $included) : array
	{
		$existingIds = array_map('strval', $existing->pluck($existing->getLocalKeyName())->toArray());
		$newIds = array_map('strval', Arr::pluck($relData['data'], 'id'));

		$deleteData = self::delete($existing, $existingIds, $newIds);
		$addData = self::addOrUpdate($relData, $existing, $key, $record, $included);

		return [
			'deleteIds' => $deleteData['ids'],
			'addIds' => $addData['ids'],
			'deleted' => $deleteData['data'],
		];
	}

	/**
	 * Deletes any records that are in existingIds but not newIds.
	 *
	 * @param  HasMany $existing
	 * @param  array   $existingIds
	 * @param  array   $newIds
	 * @return array
	 */
	protected static function delete(HasMany $existing, array $existingIds, array $newIds) : array
	{
		$output = [
			'ids' => [],
			'data' => [],
		];

		$deleteIds = array_values(array_diff($existingIds, $newIds));
		if (empty($deleteIds)) {
			return $output;
		}

		// Get the models to delete.
		$recordsToDelete = $existing->whereIn('id', $deleteIds);

		// Get the attributes for the models we are deleting; we might want to do something with this data in an event, and once the models are deleted, we won't be able to access this data.
		$attributeNames = array_merge(['id'], $recordsToDelete->getRelated()->getFillable());
		$dataToDelete = $recordsToDelete->select($attributeNames)->getResults()->toArray();
		$deletedData = [];
		foreach ($dataToDelete as $data) {
			$deletedData[$data['id']] = Arr::only($data, $attributeNames);
		}

		// Delete the models.
		$recordsToDelete->delete();

		return [
			'ids' => $deleteIds,
			'data' => $deletedData,
		];
	}

	/**
	 * @param  array   $relData
	 * @param  HasMany $existing
	 * @param  string  $key
	 * @param  Model   $record
	 * @param  array   $included
	 * @return array
	 */
	protected static function addOrUpdate(array $relData, HasMany $existing, string $key, Model $record, array $included) : array
	{
		// Get the pivot model (eg. AlbumSong).
		$pivotModel = get_class($existing->getRelated());
		$output = [
			'ids' => [],
		];

		foreach ($relData['data'] as $rel) {
			$isAdd = Utilities::isTempId($rel['id']);

			// Find the corresponding record in 'included'.
			$includedData = self::find($included, $rel['id'], $rel['type']);
			if ($isAdd && empty($includedData)) {
				throw JsonApiException::generate([
					'title' => "Record with id '" . $rel['id'] . "' and type '" . $rel['type'] . "' not found in 'included'.",
					'source' => [
						'pointer' => "/data/relationships/$key",
					],
				], 400);
			}

			$relRecord = new $pivotModel();
			$includedData = DataHelper::normalize($includedData, $relRecord->whitelistedAttributes(), $relRecord->whitelistedRelationships());
			$includedData = AttributesHelper::convertSingularRelationships($includedData);

			if ($isAdd) {
				// Create the new pivot model.
				$new = $relRecord->create($includedData['attributes']);
				if (!empty($includedData['meta'])) {
					$new->updateMeta($includedData['meta']);
				}
				$output['ids'][] = (string) $new->id;
			} else {
				// Update the existing pivot model.
				$relRecord = $relRecord->find($rel['id']);
				$relRecord->update($includedData['attributes']);
				if (!empty($includedData['meta'])) {
					$relRecord->updateMeta($includedData['meta']);
				}
			}
		}

		return $output;
	}

	/**
	 * @param  array  $included
	 * @param  string $id
	 * @param  string $type
	 * @return array
	 */
	protected static function find(array $included, string $id, string $type) : array
	{
		foreach ($included as $record) {
			if ((string) $record['id'] === (string) $id && $record['type'] === $type) {
				return $record;
			}
		}
		return [];
	}
}
