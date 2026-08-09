<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;

class ArticleFactory extends Factory
{
	protected $model = Article::class;

	public function definition() : array
	{
		return [
			'title' => 'Foo',
			'content' => null,
			'word_count' => null,
		];
	}
}
