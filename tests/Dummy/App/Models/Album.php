<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Artist;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\AlbumFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

class Album extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'release_date',
		'artist_id',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return AlbumFactory::new();
	}

	/**
	 * @param  array  $data
	 * @param  string $method
	 * @return array
	 */
	protected function rules(array $data, string $method) : array
	{
		return [
			'attributes.title' => $this->requiredOnCreate($method),
			'relationships.artist' => $this->requiredOnCreate($method),
		];
	}

	/**
	 * @return array
	 */
	public function multiRelationships() : array
	{
		return ['album_songs', 'songs'];
	}

	/**
	 * @return array
	 */
	public function singularRelationships() : array
	{
		return ['artist'];
	}

	/**
	 * @return HasMany
	 */
	public function albumSongs() : HasMany
	{
		return $this->hasMany(AlbumSong::class);
	}

	/**
	 * @return BelongsTo
	 */
	public function artist() : BelongsTo
	{
		return $this->belongsTo(Artist::class);
	}

	/**
	 * @return BelongsToMany
	 */
	public function songs() : BelongsToMany
	{
		return $this->belongsToMany(Song::class);
	}
}
