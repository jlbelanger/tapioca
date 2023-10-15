<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Artist;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\AlbumFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class Album extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'release_year',
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
	 * @return array
	 */
	public function rules() : array
	{
		return [
			'data.attributes.title' => [$this->requiredOnCreate()],
			'data.attributes.release_year' => ['integer'],
			'data.relationships.artist' => [$this->requiredOnCreate()],
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
