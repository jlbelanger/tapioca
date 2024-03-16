<?php

namespace Jlbelanger\Tapioca\Traits;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

trait Validatable
{
	/**
	 * @param  array $data
	 * @return array eg. ['data.attributes.email' => ['required', 'email'], 'data.relationships.user' => ['required']]
	 */
	public function rules(array $data) : array
	{
		return [];
	}

	/**
	 * @return string
	 */
	protected function requiredOnCreate() : string
	{
		return $this->getKey() ? 'filled' : 'required';
	}

	/**
	 * @param  string $column
	 * @return Unique
	 */
	protected function unique(string $column) : Unique
	{
		$unique = Rule::unique($this->getTable(), $column);
		if ($this->getKey()) {
			$unique->ignore($this->getKey());
		}
		return $unique;
	}
}
