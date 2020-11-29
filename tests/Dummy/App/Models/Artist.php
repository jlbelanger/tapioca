<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Album;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\ArtistFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

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
	protected function rules() : array
	{
		return [
			'attributes.title' => 'required',
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
