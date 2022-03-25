<?php

namespace Jlbelanger\Tapioca\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class RelationshipUpdated
{
	use SerializesModels;

	public $record;

	public $result;

	/**
	 * Creates a new event instance.
	 *
	 * @param  Model $record
	 * @param  array $result
	 * @return void
	 */
	public function __construct(Model $record, array $result)
	{
		$this->record = $record;
		$this->result = $result;
	}
}
