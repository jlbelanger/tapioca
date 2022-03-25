<?php

namespace Jlbelanger\Tapioca\Helpers\Output;

use Illuminate\Database\Eloquent\Builder;

class PageHelper
{
	/**
	 * @param  Builder $records
	 * @param  array   $page
	 * @return array
	 */
	public static function perform(Builder $records, array $page) : array
	{
		$total = $records->count();
		$totalPages = (int) ceil($total / $page['size']);

		if ($page['number']) {
			$records = $records->skip(($page['number'] - 1) * $page['size']);
		}

		$records = $records->take($page['size']);

		$meta = [
			'number' => $page['number'],
			'size' => $page['size'],
			'total' => $total,
			'total_pages' => $totalPages,
		];

		return [$records, $meta];
	}
}
