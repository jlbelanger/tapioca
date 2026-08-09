<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Artist;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Note;
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

	protected static function newFactory() : Factory
	{
		return AlbumFactory::new();
	}

	public function rules(array $data) : array
	{
		return [
			'data.attributes.title' => [$this->requiredOnCreate()],
			'data.attributes.release_year' => ['integer'],
			'data.relationships.artist' => [$this->requiredOnCreate()],
		];
	}

	public function multiRelationships() : array
	{
		return ['album_songs', 'notes', 'songs'];
	}

	public function singularRelationships() : array
	{
		return ['artist'];
	}

	public function albumSongs() : HasMany
	{
		return $this->hasMany(AlbumSong::class);
	}

	public function artist() : BelongsTo
	{
		return $this->belongsTo(Artist::class);
	}

	public function notes() : MorphMany
	{
		return $this->morphMany(Note::class, 'record');
	}

	public function songs() : BelongsToMany
	{
		return $this->belongsToMany(Song::class);
	}
}
