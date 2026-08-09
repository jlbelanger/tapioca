<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\ArticleTag;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Tag;

class ArticleTagFactory extends Factory
{
	protected $model = ArticleTag::class;

	public function definition() : array
	{
		return [
			'article_id' => Article::factory(),
			'tag_id' => Tag::factory(),
		];
	}
}
