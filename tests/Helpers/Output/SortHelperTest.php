<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Output;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Helpers\Output\SortHelper;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Album;
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
					'albums' => [
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
					'albums' => [
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
					'albums' => [
						['title' => '1', 'artist' => 'c'],
						['title' => '2', 'artist' => 'a'],
						['title' => '3', 'artist' => 'b'],
					],
					'artists' => [
						['title' => 'c'],
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'sort' => ['artist.title'],
				'expected' => ['2', '3', '1'],
			]],
			'when sorting by multiple fields' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_date' => '1968'],
						['title' => 'b', 'release_date' => '1968'],
						['title' => 'c', 'release_date' => '1968'],
						['title' => 'd', 'release_date' => '1967'],
						['title' => 'e', 'release_date' => '1967'],
						['title' => 'f', 'release_date' => '1967'],
					],
				],
				'sort' => ['release_date', 'title'],
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
		$output = SortHelper::perform((new Album())->newModelQuery(), $args['sort'], new Album());
		$titles = $output->get()->pluck('title')->toArray();
		$this->assertSame($args['expected'], $titles);
	}
}
