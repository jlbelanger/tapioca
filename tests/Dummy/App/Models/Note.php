<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\NoteFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class Note extends Model
{
	use HasFactory, Resource;

	protected $primaryKey = 'nid';

	protected $fillable = [
		'record_id',
		'record_type',
		'content',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return NoteFactory::new();
	}

	/**
	 * @return MorphTo
	 */
	public function record() : MorphTo
	{
		return $this->morphTo();
	}

	/**
	 * @return array
	 */
	public function rules(array $data) : array
	{
		return [
			'data.attributes.content' => [$this->requiredOnCreate()],
			'data.relationships.record' => [$this->requiredOnCreate()],
		];
	}

	/**
	 * @return array
	 */
	public function singularRelationships() : array
	{
		return ['record'];
	}
}
