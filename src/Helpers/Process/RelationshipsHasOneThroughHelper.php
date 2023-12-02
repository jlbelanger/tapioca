<?php

namespace Jlbelanger\Tapioca\Helpers\Process;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class RelationshipsHasOneThroughHelper
{
	/**
	 * Note: This is not in the JSON:API spec.
	 *
	 * @param  array         $relData
	 * @param  HasOneThrough $relation
	 * @param  Model         $record
	 * @return array
	 */
	public static function update(array $relData, HasOneThrough $relation, Model $record) : array
	{
		$relatedRecord = $relation->first();
		$result = [
			'deleteIds' => [],
			'addIds' => [],
			'deleted' => [],
		];
		$relationModel = $relation->getParent();
		$relationTable = $relationModel->getTable();

		if ($relatedRecord) {
			// There is an existing record.
			$relationModelKeyName = $relationModel->getQualifiedKeyName();
			$ids = DB::table($relationTable)
				->where($relation->getQualifiedFirstKeyName(), '=', $record->getKey())
				->where($relation->getQualifiedParentKeyName(), '=', $relatedRecord->getKey())
				->pluck($relationModelKeyName);

			if (empty($relData['data'])) {
				// Delete the existing record.
				$result['deleteIds'] = $ids;
				DB::table($relationTable)->whereIn($relationModelKeyName, $ids)->delete();
			} elseif ((string) $relatedRecord->getKey() !== (string) $relData['data']['id']) {
				// Update the existing record.
				DB::table($relationTable)->whereIn($relationModelKeyName, $ids)->update([
					$relation->getSecondLocalKeyName() => $relData['data']['id'],
				]);
			}
		} else {
			// There is no existing record.
			if (!empty($relData['data'])) {
				// Add a new record.
				$insertData = [
					$relation->getFirstKeyName() => $record->getKey(),
					$relation->getSecondLocalKeyName() => $relData['data']['id'],
				];
				if (!empty($relData['data']['attributes'])) {
					$insertData = array_merge($insertData, $relData['data']['attributes']);
				}
				DB::table($relationTable)
					->insert($insertData);
				$id = DB::getPdo()->lastInsertId();
				$result['addIds'] = [$id];
			}
		}

		return $result;
	}
}
