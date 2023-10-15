<?php

namespace Jlbelanger\Tapioca\Helpers;

use Illuminate\Support\Str;

class Utilities
{
	/**
	 * @param  string $type
	 * @return string
	 */
	public static function getClassNameFromType(string $type) : string
	{
		return config('tapioca.models_path', 'App\\Models\\') . Str::studly(Str::singular($type));
	}

	/**
	 * @param  string $id
	 * @return boolean
	 */
	public static function isTempId(string $id) : bool
	{
		return strpos($id, 'temp-') === 0;
	}

	/**
	 * @param  array $rules
	 * @return array eg. ['data.attributes.email_address' => 'email address', 'data.relationships.user' => 'user']
	 */
	public static function prettyAttributeNames(array $rules) : array
	{
		$output = [];
		$keys = array_keys($rules);
		foreach ($keys as $key) {
			$output[$key] = preg_replace('/^.+\.([^\.]+)$/', '$1', str_replace('_', ' ', $key));
		}
		return $output;
	}
}
