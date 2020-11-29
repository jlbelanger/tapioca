<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Output;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Helpers\Output\SortHelper;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class SortHelperTest extends TestCase
{
	use RefreshDatabase;

	public function performProvider()
	{
		return [
			'with no records' => [[
				'records' => [],
				'sort' => ['title'],
				'expected' => [],
			]],
			'when sorting ascending' => [[
				'records' => [
					'articles' => [
						['title' => 'c'],
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'sort' => ['title'],
				'expected' => ['a', 'b', 'c'],
			]],
			'when sorting descending' => [[
				'records' => [
					'articles' => [
						['title' => 'c'],
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'sort' => ['-title'],
				'expected' => ['c', 'b', 'a'],
			]],
			'when sorting by a related record' => [[
				'records' => [
					'articles' => [
						['title' => '1', 'username' => 'c'],
						['title' => '2', 'username' => 'a'],
						['title' => '3', 'username' => 'b'],
					],
					'users' => [
						['username' => 'c'],
						['username' => 'a'],
						['username' => 'b'],
					],
				],
				'sort' => ['user.username'],
				'expected' => ['2', '3', '1'],
			]],
			'when sorting by multiple fields' => [[
				'records' => [
					'articles' => [
						['title' => 'a', 'content' => 'b'],
						['title' => 'b', 'content' => 'b'],
						['title' => 'c', 'content' => 'b'],
						['title' => 'd', 'content' => 'a'],
						['title' => 'e', 'content' => 'a'],
						['title' => 'f', 'content' => 'a'],
					],
				],
				'sort' => ['content', 'title'],
				'expected' => ['d', 'e', 'f', 'a', 'b', 'c'],
			]],
		];
	}

	/**
	 * @dataProvider performProvider
	 */
	public function testPerform($args)
	{
		$this->createRecords($args['records']);
		$output = SortHelper::perform((new Article())->newModelQuery(), $args['sort'], new Article());
		$titles = $output->get()->pluck('title')->toArray();
		$this->assertSame($args['expected'], $titles);
	}
}
