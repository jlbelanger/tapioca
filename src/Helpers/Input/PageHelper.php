<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class PageHelper
{
	/**
	 * @param  array|mixed $page Eg. ['size' => '10'].
	 * @return array             Eg. ['number' => 1, 'size' => 10].
	 */
	public static function normalize($page) : array
	{
		$output = [];
		if (empty($page)) {
			return $output;
		}

		if (is_array($page)) {
			if (array_key_exists('number', $page)) {
				$output['number'] = (int) $page['number'];
			} else {
				$output['number'] = 1;
			}

			if (array_key_exists('size', $page)) {
				$output['size'] = (int) $page['size'];
			}
		}

		self::validate($output);

		return $output;
	}

	/**
	 * @param  array|mixed $page
	 * @return void
	 */
	protected static function validate($page) : void
	{
		if (!is_array($page)) {
			throw JsonApiException::generate([
				'title' => "Parameter 'page' must be an array.",
				'detail' => 'eg. ?page[number]=1&page[size]=50',
			], 400);
		}

		if (!array_key_exists('size', $page)) {
			throw JsonApiException::generate([
				'title' => "Parameter 'page[size]' is not specified.",
			], 400);
		}

		if ($page['number'] <= 0) {
			throw JsonApiException::generate([
				'title' => "Parameter 'page[number]' must be greater than 0.",
			], 400);
		}

		if ($page['size'] <= 0) {
			throw JsonApiException::generate([
				'title' => "Parameter 'page[size]' must be greater than 0.",
			], 400);
		}
	}
}
