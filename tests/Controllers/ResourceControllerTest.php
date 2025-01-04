<?php

namespace Jlbelanger\Tapioca\Tests\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\TestCase;

class ResourceControllerTest extends TestCase
{
	use RefreshDatabase;

	public function testDestroy() : void
	{
		$article = Article::factory()->create();
		$this->assertDatabaseHas('articles', ['id' => $article->getKey()]);

		$response = $this->call('DELETE', '/articles/' . $article->getKey());
		$response->assertNoContent(204);
		$this->assertDatabaseMissing('articles', ['id' => $article->getKey()]);
	}

	public function testModel() : void
	{
		$this->markTestIncomplete();
	}
}
