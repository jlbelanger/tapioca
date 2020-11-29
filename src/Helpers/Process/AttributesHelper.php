<?php

namespace Jlbelanger\LaravelJsonApi\Helpers\Process;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Jlbelanger\LaravelJsonApi\Helpers\JsonApiRequest;

class AttributesHelper
{
	/**
	 * @param  Model          $record
	 * @param  JsonApiRequest $req
	 * @param  boolean        $isUpdate
	 * @return Model
	 */
	public static function process(Model $record, JsonApiRequest $req, bool $isUpdate = false) : array
	{
		$data = $req->getData();
		$data = self::convertSingularRelationships($data);

		if ($isUpdate) {
			if (!empty($data['attributes'])) {
				$record->update($data['attributes']);
			}
		} else {
			if (!empty($data['attributes'])) {
				$record = $record->create($data['attributes']);
			} else {
				$record->create();
			}
		}

		return [$record, $data];
	}

	/**
	 * @param  array $data
	 * @return array
	 */
	public static function convertSingularRelationships(array $data) : array
	{
		foreach ($data['relationships'] as $key => $value) {
			if ($value['data'] === null) {
				// This is a singular relationship that is being removed.
				unset($data['relationships'][$key]);
				$data['attributes'][Str::snake($key) . '_id'] = null;
			} elseif (array_key_exists('id', $value['data'])) {
				// This is a singular relationship that is being updated.
				unset($data['relationships'][$key]);
				$data['attributes'][Str::snake($key) . '_id'] = $value['data']['id'];
			}
		}

		return $data;
	}
}
