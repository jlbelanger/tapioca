<?php

namespace Jlbelanger\LaravelJsonApi\Helpers;

use Illuminate\Support\Str;

class Utilities
{
	/**
	 * @param  string $type
	 * @return string
	 */
	public static function getClassNameFromType(string $type) : string
	{
		return config('laraveljsonapi.models_path', 'App\\Models\\') . Str::studly(Str::singular($type));
	}

	/**
	 * @param  string $id
	 * @return boolean
	 */
	public static function isTempId(string $id) : bool
	{
		return strpos($id, 'temp-') === 0;
	}
}
