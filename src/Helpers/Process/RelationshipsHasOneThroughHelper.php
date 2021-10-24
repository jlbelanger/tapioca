<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Process;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class RelationshipsHasOneThroughHelper
{
	/**
	 * Note: This is not in the JSON API spec.
	 *
	 * @param  array         $relData
	 * @param  HasOneThrough $existing
	 * @param  Model         $record
	 * @return array
	 */
	public static function update(array $relData, HasOneThrough $existing, Model $record) : array
	{
		$hasOneThrough = $existing->first();
		$relTable = explode('.', $existing->getQualifiedFirstKeyName())[0];
		$result = [
			'deleteIds' => [],
			'addIds' => [],
			'deleted' => [],
		];

		if ($hasOneThrough) {
			// There is an existing record.
			$ids = DB::table($relTable)
				->where($existing->getQualifiedFirstKeyName(), '=', $record->id)
				->where($existing->getQualifiedParentKeyName(), '=', $hasOneThrough->id)
				->pluck('id');

			if (empty($relData['data'])) {
				// Delete the existing record.
				$result['deleteIds'] = $ids;
				DB::table($relTable)->whereIn('id', $ids)->delete();
			} elseif ((string) $hasOneThrough->id !== (string) $relData['data']['id']) {
				// Update the existing record.
				DB::table($relTable)->whereIn('id', $ids)->update([
					$existing->getSecondLocalKeyName() => $relData['data']['id'],
				]);
			}
		} else {
			// There is no existing record.
			if (!empty($relData['data'])) {
				// Add a new record.
				$insertData = [
					$existing->getFirstKeyName() => $record->id,
					$existing->getSecondLocalKeyName() => $relData['data']['id'],
				];
				if (!empty($relData['data']['attributes'])) {
					$insertData = array_merge($insertData, $relData['data']['attributes']);
				}
				DB::table($relTable)
					->insert($insertData);
				$id = DB::getPdo()->lastInsertId();
				$result['addIds'] = [$id];
			}
		}

		return $result;
	}
}
