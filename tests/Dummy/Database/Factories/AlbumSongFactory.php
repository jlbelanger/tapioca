<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Song;

class AlbumSongFactory extends Factory
{
	protected $model = AlbumSong::class;

	public function definition() : array
	{
		return [
			'album_id' => Album::factory(),
			'song_id' => Song::factory(),
			'track' => 1,
		];
	}
}
