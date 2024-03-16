<?php

namespace Jlbelanger\Tapioca\Helpers;

use DB;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Events\RelationshipUpdated;
use Jlbelanger\Tapioca\Helpers\JsonApiRequest;
use Jlbelanger\Tapioca\Helpers\Process\AttributesHelper;
use Jlbelanger\Tapioca\Helpers\Process\RelationshipsHelper;
use Jlbelanger\Tapioca\Helpers\Utilities;
use Validator;

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

		$files = $req->getFiles();
		if (!empty($files)) {
			foreach ($files as $key => $file) {
				if (empty($file)) {
					$filename = null;
				} else {
					$filename = $record->uploadedFilename($key, $file->getClientOriginalName(), $req->getData());
					$pathInfo = pathinfo($filename);
					$file->move(public_path($pathInfo['dirname']), $pathInfo['basename']);
					$record->processFile($key, $filename);
				}
				$req->setDataAttribute($key, $filename);
			}
		}

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
						$included[$i]['attributes'][$key] = $record->getKey();
					}
				}

				foreach ($included[$i]['relationships'] as $key => $value) {
					if (!empty($value['data']['id']) && $value['data']['id'] === 'temp-this-id') {
						$included[$i]['relationships'][$key]['data']['id'] = $record->getKey();
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
			} else {
				$record = (new $className)::find($data['id']);
			}

			$rules = $record->rules();
			if (!$record) {
				throw JsonApiException::generate([
					'title' => __("Record with id ':id' and type ':type' does not exist.", ['id' => $data['id'], 'type' => $data['type']]),
					'source' => [
						'pointer' => '/included/' . $i,
					],
				], 422);
			}

			$newRules = [];
			foreach ($rules as $key => $value) {
				$newKey = preg_replace('/^data\./', 'included.' . $i . '.', $key);
				$newRules[$newKey] = $value;
			}
			Validator::make(['included' => [$i => $data]], $newRules, [], Utilities::prettyAttributeNames($newRules))->validate();
		}
	}
}
