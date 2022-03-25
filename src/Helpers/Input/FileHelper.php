<?php

namespace Jlbelanger\Tapioca\Helpers\Input;

use Illuminate\Http\Request;

class FileHelper
{
	/**
	 * @param  array   $filenames
	 * @param  Request $request
	 * @return array
	 */
	public static function normalize(array $filenames, Request $request) : array
	{
		$output = [];
		foreach ($filenames as $filename) {
			$output[$filename] = $request->file($filename);
		}
		return $output;
	}
}
