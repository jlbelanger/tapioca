<?php

namespace Jlbelanger\Tapioca\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;

class SortHelper
{
	/**
	 * @param  string|mixed $sort        Eg. 'b, -c'.
	 * @param  array        $defaultSort Eg. ['a'].
	 * @return array                     Eg. ['b', '-c'].
	 */
	public static function normalize($sort, array $defaultSort) : array
	{
		if (empty($sort)) {
			return $defaultSort;
		}

		self::validate($sort);

		$sort = explode(',', $sort);
		$sort = array_map('trim', $sort);

		return $sort;
	}

	/**
	 * @param  string|mixed $sort
	 * @return void
	 */
	protected static function validate($sort) : void
	{
		if (!is_string($sort)) {
			throw JsonApiException::generate([
				'title' => __("Parameter ':key' must be a string.", ['key' => 'sort']),
				'detail' => __('eg. :example', ['example' => '?sort=foo,-bar']),
			], 400);
		}

		// TODO: Whitelist sort.
	}
}
