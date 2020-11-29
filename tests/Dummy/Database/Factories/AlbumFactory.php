<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Album;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Artist;

class AlbumFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Album::class;

	/**
	 * Defines the model's default state.
	 *
	 * @return array
	 */
	public function definition() : array
	{
		return [
			'title' => 'Foo',
			'artist_id' => Artist::factory(),
		];
	}
}
