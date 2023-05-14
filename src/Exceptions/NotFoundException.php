<?php

namespace Jlbelanger\Tapioca\Exceptions;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;

class NotFoundException
{
	/**
	 * @param  string $title
	 * @return JsonApiException
	 */
	public static function generate(string $title = 'URL does not exist.') : JsonApiException
	{
		$data = [
			'title' => $title,
			'status' => '404',
		];
		return new JsonApiException(json_encode($data), 404);
	}
}
