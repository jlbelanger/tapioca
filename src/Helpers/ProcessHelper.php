<?php

namespace Jlbelanger\LaravelJsonApi\Helpers;

use DB;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\LaravelJsonApi\Events\RelationshipUpdated;
use Jlbelanger\LaravelJsonApi\Helpers\JsonApiRequest;
use Jlbelanger\LaravelJsonApi\Helpers\Process\AttributesHelper;
use Jlbelanger\LaravelJsonApi\Helpers\Process\RelationshipsHelper;

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

		if (!empty($data['relationships'])) {
			$result = RelationshipsHelper::update($record, $data['relationships'], $req->getIncluded());
			event(new RelationshipUpdated($record, $result));
		}

		if (!empty($data['meta'])) {
			$record->updateMeta($data['meta']);
		}

		DB::commit();

		return $record;
	}
}
