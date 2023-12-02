<?php

namespace Jlbelanger\Tapioca\Traits;

trait Filterable
{
	/**
	 * Returns default filters for index requests. If the user-supplied filter conflicts, this filter will be used.
	 *
	 * @return array eg. ['user_id' => ['eq' => Auth::user()->getKey()]]
	 */
	public static function defaultFilter() : array
	{
		return [];
	}
}
