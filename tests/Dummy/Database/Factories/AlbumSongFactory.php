<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Album;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\AlbumSong;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Song;

class AlbumSongFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = AlbumSong::class;

	/**
	 * Defines the model's default state.
	 *
	 * @return array
	 */
	public function definition() : array
	{
		return [
			'album_id' => Album::factory(),
			'song_id' => Song::factory(),
			'track' => 1,
		];
	}
}
