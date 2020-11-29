<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Output;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SortHelper
{
	/**
	 * @param  Builder $records
	 * @param  array   $sort
	 * @param  Model   $model
	 * @return Builder
	 */
	public static function perform(Builder $records, array $sort, Model $model) : Builder
	{
		$joinedRelations = [];
		foreach ($sort as $i => $key) {
			$pos = strpos($key, '.');
			if ($pos === false) {
				continue;
			}

			$relName = substr($key, 0, $pos);
			if (!empty($joinedRelations[$relName])) {
				// We've already joined this model.
				$sort[$i] = str_replace($relName . '.', $joinedRelations[$relName] . '.', $key);
				continue;
			}

			$rel = $model->$relName();
			$relatedModel = $rel->getRelated();
			$relatedTable = $relatedModel->getTable();
			$joinedRelations[$relName] = $relatedTable;
			$sort[$i] = str_replace($relName . '.', $joinedRelations[$relName] . '.', $key);
			$records = $records->join($relatedTable, $relatedTable . '.' . $relatedModel->getKeyName(), '=', $model->getTable() . '.' . $rel->getForeignKeyName());
		}
		if (!empty($joinedRelations)) {
			$records = $records->select($model->getTable() . '.*');
		}

		foreach ($sort as $key) {
			$direction = $key[0] === '-' ? 'DESC' : 'ASC';
			$key = ltrim($key, '-');
			$records = $records->orderBy($key, $direction);
		}

		return $records;
	}
}
