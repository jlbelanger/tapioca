<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Output;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Helpers\Output\PageHelper;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class PageHelperTest extends TestCase
{
	use RefreshDatabase;

	public function performProvider()
	{
		return [
			'with no records' => [[
				'records' => [],
				'page' => [
					'number' => 1,
					'size' => 3,
				],
				'expectedRecords' => [],
				'expectedMeta' => [
					'number' => 1,
					'size' => 3,
					'total' => 0,
					'total_pages' => 0,
				],
			]],
			'with fewer records than size' => [[
				'records' => [
					'articles' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'page' => [
					'number' => 1,
					'size' => 3,
				],
				'expectedRecords' => ['a', 'b'],
				'expectedMeta' => [
					'number' => 1,
					'size' => 3,
					'total' => 2,
					'total_pages' => 1,
				],
			]],
			'with more records than size' => [[
				'records' => [
					'articles' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
						['title' => 'd'],
						['title' => 'e'],
					],
				],
				'page' => [
					'number' => 1,
					'size' => 3,
				],
				'expectedRecords' => ['a', 'b', 'c'],
				'expectedMeta' => [
					'number' => 1,
					'size' => 3,
					'total' => 5,
					'total_pages' => 2,
				],
			]],
			'with more records than size, requesting second page' => [[
				'records' => [
					'articles' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
						['title' => 'd'],
						['title' => 'e'],
					],
				],
				'page' => [
					'number' => 2,
					'size' => 3,
				],
				'expectedRecords' => ['d', 'e'],
				'expectedMeta' => [
					'number' => 2,
					'size' => 3,
					'total' => 5,
					'total_pages' => 2,
				],
			]],
		];
	}

	/**
	 * @dataProvider performProvider
	 */
	public function testPerform($args)
	{
		$this->createRecords($args['records']);
		list($output, $meta) = PageHelper::perform((new Article())->newModelQuery(), $args['page']);
		$titles = $output->get()->pluck('title')->toArray();
		$this->assertSame($args['expectedRecords'], $titles);
		$this->assertSame($args['expectedMeta'], $meta);
	}
}
