<?php

namespace Jlbelanger\LaravelJsonApi\Exceptions;

use Exception;

class JsonApiException extends Exception
{
	/**
	 * @param  array   $data
	 * @param  integer $code
	 * @return JsonApiException
	 */
	public static function generate(array $data, int $code = 422) : JsonApiException
	{
		return new self(json_encode($data), $code);
	}

	/**
	 * @return array
	 */
	public function getErrors() : array
	{
		$json = json_decode($this->message);
		if (is_array($json)) {
			return array_map([$this, 'formatError'], $json);
		}
		return [$this->formatError($json)];
	}

	/**
	 * @param  object $error
	 * @return array
	 */
	protected function formatError(object $error) : array
	{
		return array_filter([
			'title' => !empty($error->title) ? $error->title : null,
			'source' => !empty($error->source) ? (array) $error->source : null,
			'status' => strval($this->code),
			'detail' => !empty($error->detail) ? $error->detail : null,
		]);
	}
}
