<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Comment;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\User;

class CommentFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Comment::class;

	/**
	 * Defines the model's default state.
	 *
	 * @return array
	 */
	public function definition() : array
	{
		return [
			'content' => 'Lorem ipsum dolor.',
			'article_id' => Article::factory(),
			'user_id' => User::factory(),
		];
	}
}
