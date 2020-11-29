<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class IncludedHelper
{
	/**
	 * @param  array|mixed $data
	 * @return array
	 */
	public static function normalize($data) : array
	{
		if (empty($data['included'])) {
			$data['included'] = [];
			return $data;
		}
		self::validate($data['included']);
		return $data;
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
