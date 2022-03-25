<?php

namespace Jlbelanger\Tapioca\Traits;

use Jlbelanger\Tapioca\Helpers\Utilities;
use Validator;

trait Validatable
{
	/**
	 * @param  array  $data
	 * @param  string $method
	 * @return array eg. ['attributes.email' => 'required|email', 'relationships.user' => 'required']
	 */
	protected function rules(array $data, string $method) : array
	{
		return [];
	}

	/**
	 * @param  array  $data
	 * @param  string $method
	 * @return array
	 */
	public function validate(array $data, string $method) : array
	{
		$rules = $this->rules($data, $method);
		$validator = Validator::make($data, $rules, [], Utilities::prettyAttributeNames($rules));
		if ($validator->fails()) {
			return $validator->errors()->toArray();
		}
		return [];
	}

	/**
	 * @param  string $method
	 * @return string
	 */
	protected function requiredOnCreate(string $method) : string
	{
		return $method === 'POST' ? 'required' : 'filled';
	}
}
