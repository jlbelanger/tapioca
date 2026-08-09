<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Artist;

class ArtistFactory extends Factory
{
	protected $model = Artist::class;

	public function definition() : array
	{
		return [
			'title' => 'Foo',
		];
	}
}
