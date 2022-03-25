<?php

namespace Jlbelanger\Tapioca\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;

class IncludeHelper
{
	/**
	 * @param  string|mixed $include
	 * @return array
	 */
	public static function normalize($include) : array
	{
		if (empty($include)) {
			return [];
		}

		if (!is_string($include)) {
			throw JsonApiException::generate([
				'title' => "Parameter 'include' must be a string.",
				'detail' => 'eg. ?include=foo,bar',
			], 400);
		}

		$include = explode(',', $include);
		$include = array_map('trim', $include);

		self::validate($include);

		return $include;
	}

	/**
	 * @param  array $include
	 * @return void
	 */
	protected static function validate(array $include) : void
	{
		// TODO: Whitelist includes.
	}
}
