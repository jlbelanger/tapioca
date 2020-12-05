<?php

namespace Jlbelanger\LaravelJsonApi\Traits;

use Illuminate\Http\Request;
use Validator;

trait Validatable
{
	/**
	 * @param  Request $request
	 * @return array eg. ['attributes.email' => 'required|email', 'relationships.user' => 'required']
	 */
	protected function rules(Request $request) : array
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
	 * @param  array   $data
	 * @param  Request $request
	 * @param  boolean $isUpdate
	 * @return array
	 */
	public function validate(array $data, Request $request, bool $isUpdate = false) : array
	{
		$rules = $this->rules($request);
		foreach ($rules as $key => $value) {
			if ($isUpdate && is_string($value) && strpos($value, 'sometimes|') === false) {
				// Don't validate all fields on update, because we may only be sending changed fields.
				$rules[$key] = 'sometimes|' . $value;
			}
		}

		$validator = Validator::make($data, $rules, [], $this->prettyAttributeNames($rules));
		if ($validator->fails()) {
			return $validator->errors()->toArray();
		}

		return [];
	}
}
