<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Song;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\AlbumSongFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class AlbumSong extends Model
{
	use HasFactory, Resource;

	protected $primaryKey = 'asid';

	protected $table = 'album_song';

	protected $fillable = [
		'album_id',
		'song_id',
		'track',
		'length',
	];

	protected static function newFactory() : Factory
	{
		return AlbumSongFactory::new();
	}

	public function rules(array $data) : array
	{
		return [
			'data.attributes.track' => [$this->requiredOnCreate()],
			'data.relationships.album' => [$this->requiredOnCreate()],
			'data.relationships.song' => [$this->requiredOnCreate()],
		];
	}

	public function singularRelationships() : array
	{
		return ['album', 'song'];
	}

	public function album() : BelongsTo
	{
		return $this->belongsTo(Album::class);
	}

	public function song() : BelongsTo
	{
		return $this->belongsTo(Song::class);
	}
}
