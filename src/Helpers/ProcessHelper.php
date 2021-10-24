<?php

namespace Jlbelanger\LaravelJsonApi\Helpers;

use DB;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\LaravelJsonApi\Events\RelationshipUpdated;
use Jlbelanger\LaravelJsonApi\Exceptions\ValidationException;
use Jlbelanger\LaravelJsonApi\Helpers\JsonApiRequest;
use Jlbelanger\LaravelJsonApi\Helpers\Process\AttributesHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Process\RelationshipsHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Utilities;

class ProcessHelper
{
	/**
	 * @param  Model          $record
	 * @param  JsonApiRequest $req
	 * @return Model
	 */
	public static function create(Model $record, JsonApiRequest $req) : Model
	{
		return self::process($record, $req);
	}

	/**
	 * @param  Model          $record
	 * @param  JsonApiRequest $req
	 * @return Model
	 */
	public static function update(Model $record, JsonApiRequest $req) : Model
	{
		return self::process($record, $req, true);
	}

	/**
	 * @param  Model          $record
	 * @param  JsonApiRequest $req
	 * @param  boolean        $isUpdate
	 * @return Model
	 */
	protected static function process(Model $record, JsonApiRequest $req, bool $isUpdate = false) : Model
	{
		DB::beginTransaction();

		list($record, $data) = AttributesHelper::process($record, $req, $isUpdate);

		$included = self::normalizeIncludedRecords($req->getIncluded(), $record);
		self::validateIncludedRecords($included);

		if (!empty($data['relationships'])) {
			$result = RelationshipsHelper::update($record, $data['relationships'], $included);
			event(new RelationshipUpdated($record, $result));
		}

		if (!empty($data['meta'])) {
			$record->updateMeta($data['meta']);
		}

		DB::commit();

		return $record;
	}

	/**
	 * @param  array $included
	 * @param  Model $record
	 * @return array
	 */
	protected static function normalizeIncludedRecords(array $included, Model $record) : array
	{
		foreach ($included as $i => $includedData) {
			if (Utilities::isTempId($includedData['id'])) {
				foreach ($included[$i]['attributes'] as $key => $value) {
					if ($value === 'temp-this-id') {
						$included[$i]['attributes'][$key] = $record->id;
					}
				}

				foreach ($included[$i]['relationships'] as $key => $value) {
					if (!empty($value['data']['id']) && $value['data']['id'] === 'temp-this-id') {
						$included[$i]['relationships'][$key]['data']['id'] = $record->id;
					}
				}
			}
		}
		return $included;
	}

	/**
	 * @param  array $included
	 * @return void
	 */
	protected static function validateIncludedRecords(array $included) : void
	{
		foreach ($included as $i => $data) {
			$className = Utilities::getClassNameFromType($data['type']);
			if (Utilities::isTempId($data['id'])) {
				$record = new $className();
				$method = 'POST';
			} else {
				$record = (new $className)::find($data['id']);
				$method = 'PUT';
			}

			$errors = $record->validate($data, $method);
			if (!empty($errors)) {
				throw ValidationException::generate($errors, 'included/' . $i);
			}
		}
	}
}
