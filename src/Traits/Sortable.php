<?php

namespace Jlbelanger\Tapioca\Traits;

trait Sortable
{
	/**
	 * Returns default sort for index requests. If there is a user-supplied sort, this will not be used.
	 *
	 * @return array eg. ['user.username', '-created_at']
	 */
	public static function defaultSort() : array
	{
		return [];
	}
}
