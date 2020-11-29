<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\User;

class ArticleFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Article::class;

	/**
	 * Defines the model's default state.
	 *
	 * @return array
	 */
	public function definition() : array
	{
		return [
			'title' => 'Foo',
			'content' => null,
			'user_id' => User::factory(),
			'word_count' => null,
		];
	}
}
