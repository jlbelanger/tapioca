<?php

namespace Jlbelanger\Tapioca\Exceptions;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;

class NotFoundException
{
	/**
	 * @return JsonApiException
	 */
	public static function generate() : JsonApiException
	{
		$data = [
			'title' => 'URL does not exist.',
			'status' => '404',
		];
		return new JsonApiException(json_encode($data), 404);
	}
}
