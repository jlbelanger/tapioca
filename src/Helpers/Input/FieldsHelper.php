<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;

class FieldsHelper
{
	/**
	 * @param  array|mixed $fields Eg. ['a' => 'b, c'].
	 * @return array               Eg. ['a' => ['b', 'c']].
	 */
	public static function normalize($fields) : array
	{
		if (empty($fields)) {
			return [];
		}

		if (is_array($fields)) {
			foreach ($fields as $key => $values) {
				$values = explode(',', $values);
				$values = array_map('trim', $values);
				$fields[$key] = $values;
			}
		}

		self::validate($fields);

		return $fields;
	}

	/**
	 * @param  array|mixed $fields
	 * @return void
	 */
	protected static function validate($fields) : void
	{
		if (!is_array($fields)) {
			throw JsonApiException::generate([
				'title' => "Parameter 'fields' must be an array.",
				'detail' => 'eg. ?fields[foo]=bar',
			], 400);
		}

		// TODO: Whitelist fields.
	}
}
