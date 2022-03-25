<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\SongFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class Song extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'content',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return SongFactory::new();
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
		];
	}

	/**
	 * @return array
	 */
	public function multiRelationships() : array
	{
		return ['album_songs'];
	}

	/**
	 * @return HasMany
	 */
	public function albumSongs() : HasMany
	{
		return $this->hasMany(AlbumSong::class);
	}
}
