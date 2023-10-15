<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\ArtistFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class Artist extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'filename',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return ArtistFactory::new();
	}

	/**
	 * @return array
	 */
	public function rules() : array
	{
		return [
			'data.attributes.title' => [$this->requiredOnCreate()],
		];
	}

	/**
	 * @return array
	 */
	public function multiRelationships() : array
	{
		return ['albums'];
	}

	/**
	 * @return HasMany
	 */
	public function albums() : HasMany
	{
		return $this->hasMany(Album::class);
	}
}
