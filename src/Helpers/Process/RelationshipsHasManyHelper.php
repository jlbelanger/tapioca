<?php

namespace Jlbelanger\Tapioca\Helpers\Process;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Arr;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\DataHelper;
use Jlbelanger\Tapioca\Helpers\Process\AttributesHelper;
use Jlbelanger\Tapioca\Helpers\Utilities;

class RelationshipsHasManyHelper
{
	/**
	 * Note: This is not in the JSON:API spec.
	 *
	 * @param  array        $relData
	 * @param  HasOneOrMany $existing
	 * @param  string       $key
	 * @param  Model        $record
	 * @param  array        $included
	 * @return array
	 */
	public static function update(array $relData, HasOneOrMany $existing, string $key, Model $record, array $included) : array
	{
		$relIdName = $existing->getRelated()->getKeyName();
		$existingIds = array_map('strval', $existing->pluck($relIdName)->toArray());
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
	 * @param  HasOneOrMany $existing
	 * @param  array        $existingIds
	 * @param  array        $newIds
	 * @return array
	 */
	protected static function delete(HasOneOrMany $existing, array $existingIds, array $newIds) : array
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
		$relModel = $existing->getRelated();
		$relIdName = $relModel->getQualifiedKeyName();
		$table = $relModel->getTable();
		$recordsToDelete = $existing->whereIn($relModel->getQualifiedKeyName(), $deleteIds);

		// Get the attributes for the models we are deleting; we might want to do something with this data in an event, and once the models are deleted, we won't be able to access this data.
		$attributeNames = array_merge([$relModel->getKeyName()], $relModel->getFillable());
		$selectAttributeNames = [];
		foreach ($attributeNames as $attributeName) {
			$selectAttributeNames[] = $table . '.' . $attributeName;
		}
		$dataToDelete = $recordsToDelete->select($selectAttributeNames)->getResults()->toArray();
		$deletedData = [];
		foreach ($dataToDelete as $data) {
			$deletedData[$data[$relModel->getKeyName()]] = Arr::only($data, $attributeNames);
		}

		// Delete the models.
		$recordsToDelete->delete();

		return [
			'ids' => $deleteIds,
			'data' => $deletedData,
		];
	}

	/**
	 * @param  array        $relData
	 * @param  HasOneOrMany $existing
	 * @param  string       $key
	 * @param  Model        $record
	 * @param  array        $included
	 * @return array
	 */
	protected static function addOrUpdate(array $relData, HasOneOrMany $existing, string $key, Model $record, array $included) : array
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
					'title' => __("Record with id ':id' and type ':type' not found in 'included'.", ['id' => $rel['id'], 'type' => $rel['type']]),
					'source' => [
						'pointer' => "/data/relationships/$key",
					],
				], 400);
			}

			$relRecord = new $pivotModel();
			$includedData = DataHelper::normalize($includedData, $relRecord->whitelistedAttributes(), $relRecord->whitelistedRelationships());
			$includedData = AttributesHelper::convertSingularRelationships($includedData, $relRecord, $existing);

			if ($isAdd) {
				// Create the new pivot model.
				$new = $relRecord->create($includedData['attributes']);
				if (!empty($includedData['relationships'])) {
					RelationshipsHelper::update($new, $includedData['relationships'], $included);
				}
				if (!empty($includedData['meta'])) {
					$new->updateMeta($includedData['meta']);
				}
				$output['ids'][] = (string) $new->getKey();
			} else {
				// Update the existing pivot model.
				$relRecord = $relRecord->find($rel['id']);
				$relRecord->update($includedData['attributes']);
				if (!empty($includedData['relationships'])) {
					RelationshipsHelper::update($relRecord, $includedData['relationships'], $included);
				}
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
