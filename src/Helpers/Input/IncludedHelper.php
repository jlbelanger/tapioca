<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class IncludedHelper
{
	/**
	 * @param  array|mixed $included
	 * @return array
	 */
	public static function normalize($included) : array
	{
		if (empty($included)) {
			return [];
		}
		self::validate($included);
		return $included;
	}

	/**
	 * @param  array|mixed $included
	 * @return void
	 */
	protected static function validate($included) : void
	{
		if (!is_array($included)) {
			throw JsonApiException::generate([
				'title' => "'included' must be an array.",
				'detail' => 'eg. {"data": {"included": []}}',
				'source' => [
					'pointer' => '/data/included',
				],
			], 400);
		}

		// TODO: Validate children.
	}
}
