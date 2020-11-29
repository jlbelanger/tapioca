<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\ArticleTag;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Tag;

class ArticleTagFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = ArticleTag::class;

	/**
	 * Defines the model's default state.
	 *
	 * @return array
	 */
	public function definition() : array
	{
		return [
			'article_id' => Article::factory(),
			'tag_id' => Tag::factory(),
		];
	}
}
