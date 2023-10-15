<?php

namespace Jlbelanger\Tapioca\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\ArticleController;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\TestCase;

class ResourceControllerTest extends TestCase
{
	use RefreshDatabase;

	public function testDestroy() : void
	{
		$article = Article::factory()->create();
		$this->assertDatabaseHas('articles', ['id' => $article->id]);
		$response = (new ArticleController)->destroy($article->id);
		$this->assertSame([], $response->getData(true));
		$this->assertSame(204, $response->getStatusCode());
		$this->assertDatabaseMissing('articles', ['id' => $article->id]);
	}

	public function testModel() : void
	{
		$this->markTestIncomplete();
	}
}
