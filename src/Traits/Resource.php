<?php

namespace Jlbelanger\Tapioca\Traits;

use Illuminate\Support\Str;
use Jlbelanger\Tapioca\Traits\Filterable;
use Jlbelanger\Tapioca\Traits\Sortable;
use Jlbelanger\Tapioca\Traits\Validatable;

trait Resource
{
	use Filterable, Sortable, Validatable;

	/**
	 * @return array
	 */
	public function baseData() : array
	{
		return [
			'id' => $this->dataId(),
			'type' => $this->dataType(),
		];
	}

	/**
	 * @return string
	 */
	protected function dataId() : string
	{
		return (string) $this->{$this->primaryKey};
	}

	/**
	 * @return string
	 */
	protected function dataType() : string
	{
		return str_replace('_', '-', $this->table);
	}

	/**
	 * @param  array $include
	 * @param  array $fields
	 * @return array
	 */
	public function data(array $include = [], array $fields = []) : array
	{
		$data = $this->baseData();
		$fields = array_key_exists($data['type'], $fields) ? $fields[$data['type']] : null;
		return array_merge($data, array_filter([
			'attributes' => $this->dataAttributes($fields),
			'relationships' => $this->dataRelationships($include),
			'meta' => $this->dataMeta($fields),
		]));
	}

	/**
	 * @param  array|null $fields
	 * @return array
	 */
	protected function dataAttributes($fields = null) : array
	{
		$output = [];
		$singularRelationships = array_map(function ($s) {
			return Str::snake($s) . '_id';
		}, $this->singularRelationships());
		$attributes = $this->fillable;
		$attributes = array_diff($attributes, $this->hidden);
		$attributes = array_diff($attributes, $singularRelationships);
		$attributes = array_merge($attributes, $this->additionalAttributes());
		foreach ($attributes as $attribute) {
			if ($fields !== null && !in_array($attribute, $fields)) {
				continue;
			}
			$output[$attribute] = $this->$attribute;
		}
		return $output;
	}

	/**
	 * @param  array $include
	 * @return array
	 */
	protected function dataRelationships(array $include = []) : array
	{
		$output = [];

		foreach ($include as $relationshipName) {
			$functionName = Str::camel($relationshipName);

			if (in_array($relationshipName, $this->singularRelationships())) {
				$relation = $this->$functionName;
				$output[$relationshipName] = [
					'data' => !empty($relation) && $relation->id !== 0 ? $relation->baseData() : null,
				];
			} elseif (in_array($relationshipName, $this->multiRelationships())) {
				$relation = $this->$functionName()->get();
				$output[$relationshipName] = [
					'data' => [],
				];
				foreach ($relation as $relationRecord) {
					$output[$relationshipName]['data'][] = $relationRecord->baseData();
				}
			}
		}

		return $output;
	}

	/**
	 * @param  array|null $fields
	 * @return array
	 */
	protected function dataMeta($fields) : array
	{
		return [];
	}

	/**
	 * @param  array $meta
	 * @return void
	 */
	public function updateMeta(array $meta) : void
	{
	}

	/**
	 * Returns a list of attributes that aren't in fillable that should be included in this record's data.
	 *
	 * @return array
	 */
	public function additionalAttributes() : array
	{
		return [];
	}

	/**
	 * @param  array $data
	 * @return array
	 */
	public function defaultAttributes(array $data) : array
	{
		return [];
	}

	/**
	 * @return array
	 */
	public function singularRelationships() : array
	{
		return [];
	}

	/**
	 * @return array
	 */
	public function multiRelationships() : array
	{
		return [];
	}

	/**
	 * @param  string $key
	 * @param  string $filename
	 * @return string
	 */
	public function uploadedFilename(string $key, string $filename) : string
	{
		$pathInfo = pathinfo($filename);
		return '/uploads/' . $this->getTable() . '-' . $key . '/' . Str::random(8) . '.' . $pathInfo['extension'];
	}

	/**
	 * @return array
	 */
	public function whitelistedAttributes() : array
	{
		return $this->fillable;
	}

	/**
	 * @return array
	 */
	public function whitelistedRelationships() : array
	{
		return array_merge($this->singularRelationships(), $this->multiRelationships());
	}
}
