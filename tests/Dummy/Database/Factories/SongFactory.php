<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Song;

class SongFactory extends Factory
{
	protected $model = Song::class;

	public function definition() : array
	{
		return [
			'title' => 'Foo',
			'content' => 'Lorem ipsum dolor.',
		];
	}
}
