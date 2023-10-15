<?php

namespace Jlbelanger\Tapioca\Traits;

use Illuminate\Validation\Rule;

trait Validatable
{
	/**
	 * @return array eg. ['data.attributes.email' => ['required', 'email'], 'data.relationships.user' => ['required']]
	 */
	public function rules() : array
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
	 * @return string
	 */
	protected function unique(string $column) : string
	{
		$unique = Rule::unique($this->getTable(), $column);
		if ($this->getKey()) {
			$unique->ignore($this->getKey());
		}
		return $unique;
	}
}
