<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Artist;

class AlbumFactory extends Factory
{
	protected $model = Album::class;

	public function definition() : array
	{
		return [
			'title' => 'Foo',
			'artist_id' => Artist::factory(),
		];
	}
}
