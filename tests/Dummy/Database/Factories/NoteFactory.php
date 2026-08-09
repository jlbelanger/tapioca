<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Note;

class NoteFactory extends Factory
{
	protected $model = Note::class;

	public function definition() : array
	{
		return [
			'record_id' => Album::factory(),
			'record_type' => Album::class,
			'content' => 'Lorem ipsum dolor sit amet.',
		];
	}
}
