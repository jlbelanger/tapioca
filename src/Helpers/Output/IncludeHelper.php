<?php

namespace Jlbelanger\Tapioca\Helpers\Output;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Jlbelanger\Tapioca\Helpers\Utilities;

class IncludeHelper
{
	/**
	 * @param  Model   $model
	 * @param  Builder $records
	 * @param  array   $include
	 * @return Builder
	 */
	public static function prepare(Model $model, Builder $records, array $include) : Builder
	{
		$relationships = array_merge($model->singularRelationships(), $model->multiRelationships());
		foreach ($include as $name) {
			if (in_array($name, $relationships)) {
				$records = $records->with(Str::camel($name));
			}
		}
		$records = $model->prepareInclude($records, $include);
		return $records;
	}

	/**
	 * @param  Collection $collection
	 * @param  array      $records
	 * @param  array      $include
	 * @param  array      $fields
	 * @return array
	 */
	public static function perform(Collection $collection, array $records, array $include, array $fields) : array
	{
		$output = [];
		if (empty($include)) {
			return $output;
		}

		// Mark all the previously fetched records as known, so that we don't fetch them again.
		$knownRecords = [];
		foreach ($records as $recordData) {
			$knownRecords = self::addKnownRecord($knownRecords, $recordData);
		}

		// TODO: This probably needs to be recursive.
		$eagerRecords = [];
		foreach ($collection as $model) {
			foreach ($model->getRelations() as $relations) {
				if (empty($relations)) {
					continue;
				}
				if (class_basename(get_class($relations)) !== 'Collection' && !is_array($relations)) {
					$relations = collect([$relations]);
				}
				foreach ($relations as $relatedRecord) {
					$data = $relatedRecord->baseData();
					$eagerRecords[$data['type'] . '-' . $data['id']] = $relatedRecord;
				}
			}
		}

		foreach ($records as $recordData) {
			$new = self::include($recordData, $include, $fields, $knownRecords, $eagerRecords);
			$knownRecords = $new['knownRecords'];
			$output = array_merge($output, $new['included']);
		}

		return $output;
	}

	/**
	 * @param  array $recordData
	 * @param  array $include
	 * @param  array $fields
	 * @param  array $knownRecords
	 * @param  array $eagerRecords
	 * @return array
	 */
	protected static function include(array $recordData, array $include, array $fields, array $knownRecords, array $eagerRecords) : array
	{
		$output = [
			'included' => [],
			'knownRecords' => $knownRecords,
		];

		if (empty($recordData['relationships'])) {
			return $output;
		}

		foreach ($recordData['relationships'] as $relName => $relatedDatas) {
			if (empty($relatedDatas['data'])) {
				continue;
			}

			$include = array_diff($include, [$relName]);
			list($filteredInclude, $include) = self::filterParams($include, $relName);

			if (array_key_exists('id', $relatedDatas['data'])) {
				$relatedDatas = [$relatedDatas['data']];
			} else {
				$relatedDatas = $relatedDatas['data'];
			}

			foreach ($relatedDatas as $relatedData) {
				if (self::isKnown($relatedData, $output['knownRecords'])) {
					continue;
				}
				$output['knownRecords'] = self::addKnownRecord($output['knownRecords'], $relatedData);
				$record = self::getRecordFromData($relatedData, $eagerRecords);
				$relatedData = $record->data($filteredInclude, $fields);
				$output['included'][] = $relatedData;

				if (!empty($filteredInclude)) {
					$new = self::include($relatedData, $filteredInclude, $fields, $output['knownRecords'], $eagerRecords);
					$output['knownRecords'] = $new['knownRecords'];
					$output['included'] = array_merge($output['included'], $new['included']);
				}
			}
		}

		return $output;
	}

	/**
	 * @param  array $data
	 * @param  array $eagerRecords
	 * @return Model
	 */
	protected static function getRecordFromData(array $data, array $eagerRecords) : Model
	{
		$key = $data['type'] . '-' . $data['id'];
		if (!empty($eagerRecords[$key])) {
			return $eagerRecords[$key];
		}
		$className = Utilities::getClassNameFromType($data['type']);
		return (new $className)->find($data['id']);
	}

	/**
	 * @param  array $record
	 * @param  array $knownRecords
	 * @return boolean
	 */
	protected static function isKnown(array $record, array $knownRecords) : bool
	{
		$type = $record['type'];
		$id = $record['id'];
		return array_key_exists($type, $knownRecords) && in_array($id, $knownRecords[$type]);
	}

	/**
	 * @param  array $knownRecords
	 * @param  array $data
	 * @return array
	 */
	protected static function addKnownRecord(array $knownRecords, array $data) : array
	{
		$type = $data['type'];
		$id = $data['id'];
		if (!array_key_exists($type, $knownRecords)) {
			$knownRecords[$type] = [];
		}
		$knownRecords[$type][] = $id;
		return $knownRecords;
	}

	/**
	 * @param  array  $keys
	 * @param  string $type
	 * @return array
	 */
	public static function filterParams(array $keys, string $type) : array
	{
		$new = [];
		$old = [];
		$type = $type . '.';
		$pos = strlen($type);
		foreach ($keys as $key) {
			if (strpos($key, $type) === 0) {
				$new[] = substr($key, $pos);
			} else {
				$old[] = $key;
			}
		}
		return [$new, $old];
	}
}
