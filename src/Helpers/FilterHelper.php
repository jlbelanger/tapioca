<?php

namespace Jlbelanger\LaravelJsonApi\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class FilterHelper
{
	protected static $filterOps = [
		'eq' => '=',
		'ge' => '>=',
		'gt' => '>',
		'le' => '<=',
		'like' => 'LIKE',
		'lt' => '<',
		'ne' => '!=',
		'in' => 'IN',
	];

	/**
	 * @param  array|mixed $filter        Eg. ['c' => ['eq' => 'd']].
	 * @param  array       $defaultFilter Eg. ['a' => ['eq' => 'b']].
	 * @return array                      Eg. ['a' => ['eq' => 'b'], 'c' => ['eq' => 'd']].
	 */
	public static function normalize($filter, array $defaultFilter) : array
	{
		if (empty($filter)) {
			return $defaultFilter;
		}

		self::validate($filter);

		return array_merge($filter, $defaultFilter);
	}

	/**
	 * @param  array|mixed $filter
	 * @return void
	 */
	protected static function validate($filter) : void
	{
		if (!is_array($filter)) {
			throw JsonApiException::generate([
				'title' => "Parameter 'filter' must be an array.",
				'detail' => 'eg. ?filter[foo][eq]=bar',
			], 400);
		}

		foreach ($filter as $key => $data) {
			if (!is_array($data)) {
				throw JsonApiException::generate([
					'title' => "Parameter 'filter[$key]' has missing operation.",
					'detail' => 'eg. ?filter[foo][eq]=bar',
				], 400);
			}

			foreach ($data as $op => $value) {
				if (!array_key_exists($op, self::$filterOps)) {
					throw JsonApiException::generate([
						'title' => "Parameter 'filter[$key][$op]' has invalid operation.",
						'detail' => 'Permitted operations: ' . implode(', ', array_keys(self::$filterOps)),
					], 400);
				}

				if ($value === 'null' && $op !== 'eq' && $op !== 'ne') {
					throw JsonApiException::generate([
						'title' => "Parameter 'filter[$key][$op]' has invalid operation for 'null' value.",
						'detail' => "Permitted operations for 'null': eq, ne",
					], 400);
				}
			}
		}

		// TODO: Whitelist filters.
	}

	/**
	 * @param  Builder $records
	 * @param  array   $filter
	 * @return Builder
	 */
	public static function perform(Builder $records, array $filter) : Builder
	{
		foreach ($filter as $key => $data) {
			foreach ($data as $op => $value) {
				$operator = self::$filterOps[$op];
				$pos = strpos($key, '.');

				if ($pos !== false) {
					$relationship = Str::camel(substr($key, 0, $pos));
					$foreignKey = substr($key, $pos + 1);
					$records = $records->whereHas($relationship, function ($q) use ($foreignKey, $value) {
						// TODO: Should also handle other operators.
						$q->where($foreignKey, '=', $value);
					});
				} elseif ($value === 'null') {
					// TODO: Maybe make these into separate operators so you can still filter by the string 'null'.
					if ($op === 'ne') {
						$records = $records->whereNotNull($key);
					} else {
						$records = $records->whereNull($key);
					}
				} elseif ($op === 'in') {
					$value = array_map('trim', explode(',', $value));
					$records = $records->whereIn($key, $value);
				} else {
					$records = $records->where($key, $operator, $value);
				}
			}
		}

		return $records;
	}
}
