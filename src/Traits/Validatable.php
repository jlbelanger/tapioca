<?php

namespace Jlbelanger\LaravelJsonApi\Traits;

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
	 * @param  array $rules
	 * @return array eg. ['attributes.email_address' => 'email address', 'relationships.user' => 'user']
	 */
	protected static function prettyAttributeNames(array $rules) : array
	{
		$output = [];
		$keys = array_keys($rules);
		foreach ($keys as $key) {
			$output[$key] = preg_replace('/^[^\.]+\./', '', str_replace('_', ' ', $key));
		}
		return $output;
	}

	/**
	 * @param  array  $data
	 * @param  string $method
	 * @return array
	 */
	public function validate(array $data, string $method) : array
	{
		$rules = $this->rules($data, $method);
		$validator = Validator::make($data, $rules, [], $this->prettyAttributeNames($rules));
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
