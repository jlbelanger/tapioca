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

	protected static function newFactory() : Factory
	{
		return SongFactory::new();
	}

	public function rules(array $data) : array
	{
		return [
			'data.attributes.title' => [$this->requiredOnCreate()],
		];
	}

	public function multiRelationships() : array
	{
		return ['album_songs'];
	}

	public function albumSongs() : HasMany
	{
		return $this->hasMany(AlbumSong::class);
	}
}
