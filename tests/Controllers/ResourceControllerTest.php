<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\ArticleController;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ResourceControllerTest extends TestCase
{
	use RefreshDatabase;

	public function testDestroy()
	{
		$article = Article::factory()->create();
		$this->assertDatabaseHas('articles', ['id' => $article->id]);
		$response = (new ArticleController)->destroy($article->id);
		$this->assertSame([], $response->getData(true));
		$this->assertSame(204, $response->getStatusCode());
		$this->assertDatabaseMissing('articles', ['id' => $article->id]);
	}

	public function testModel()
	{
		$this->markTestIncomplete();
	}
}
