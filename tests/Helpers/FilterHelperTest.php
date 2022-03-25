<?php

namespace Jlbelanger\Tapioca\Tests\Helpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\FilterHelper;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\TestCase;

class FilterHelperTest extends TestCase
{
	use RefreshDatabase;

	public function normalizeProvider()
	{
		return [
			'with an empty array and no default filter' => [[
				'filter' => [],
				'defaultFilter' => [],
				'expected' => [],
			]],
			'with an empty string and no default filter' => [[
				'filter' => '',
				'defaultFilter' => [],
				'expected' => [],
			]],
			'with null and no default filter' => [[
				'filter' => null,
				'defaultFilter' => [],
				'expected' => [],
			]],
			'with a valid array and no default filter' => [[
				'filter' => [
					'a' => ['eq' => 'b'],
				],
				'defaultFilter' => [],
				'expected' => [
					'a' => ['eq' => 'b'],
				],
			]],
			'with multiple valid arrays and no default filter' => [[
				'filter' => [
					'a' => ['eq' => 'b'],
					'c' => ['ne' => 'd'],
				],
				'defaultFilter' => [],
				'expected' => [
					'a' => ['eq' => 'b'],
					'c' => ['ne' => 'd'],
				],
			]],
			'with an empty array and a default filter' => [[
				'filter' => [],
				'defaultFilter' => [
					'a' => ['eq' => 'b'],
				],
				'expected' => [
					'a' => ['eq' => 'b'],
				],
			]],
			'with an empty string and a default filter' => [[
				'filter' => '',
				'defaultFilter' => [
					'a' => ['eq' => 'b'],
				],
				'expected' => [
					'a' => ['eq' => 'b'],
				],
			]],
			'with null and a default filter' => [[
				'filter' => null,
				'defaultFilter' => [
					'a' => ['eq' => 'b'],
				],
				'expected' => [
					'a' => ['eq' => 'b'],
				],
			]],
			'with a valid array and a default filter that does not conflict' => [[
				'filter' => [
					'c' => ['eq' => 'd'],
				],
				'defaultFilter' => [
					'a' => ['eq' => 'b'],
				],
				'expected' => [
					'c' => ['eq' => 'd'],
					'a' => ['eq' => 'b'],
				],
			]],
			'with a valid array and a default filter that does conflict' => [[
				'filter' => [
					'a' => ['ne' => 'c'],
				],
				'defaultFilter' => [
					'a' => ['eq' => 'b'],
				],
				'expected' => [
					'a' => ['eq' => 'b'],
				],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize($args)
	{
		$output = FilterHelper::normalize($args['filter'], $args['defaultFilter']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider()
	{
		return [
			'with a string' => [[
				'filter' => 'foo',
				'expectedMessage' => '{"title":"Parameter \'filter\' must be an array.","detail":"eg. ?filter[foo][eq]=bar"}',
			]],
			'with a missing operation' => [[
				'filter' => [
					'foo' => 'bar, baz',
				],
				'expectedMessage' => '{"title":"Parameter \'filter[foo]\' has missing operation.","detail":"eg. ?filter[foo][eq]=bar"}',
			]],
			'with an invalid operation' => [[
				'filter' => [
					'foo' => [
						'equals' => 'bar, baz',
					],
				],
				'expectedMessage' => '{"title":"Parameter \'filter[foo][equals]\' has invalid operation.","detail":"Permitted operations: eq, ge, gt, le, like, notlike, lt, ne, in, null, notnull"}',
			]],
			'with a valid array' => [[
				'filter' => [
					'foo' => [
						'eq' => 'bar',
					],
				],
				'expectedMessage' => null,
			]],
		];
	}

	/**
	 * @dataProvider validateProvider
	 */
	public function testValidate($args)
	{
		if (!empty($args['expectedMessage'])) {
			$this->expectException(JsonApiException::class);
			$this->expectExceptionMessage($args['expectedMessage']);
		} else {
			$this->expectNotToPerformAssertions();
		}
		$this->callPrivate(new FilterHelper, 'validate', [$args['filter']]);
	}

	public function performProvider()
	{
		return [
			'with no records' => [[
				'records' => [],
				'filter' => [
					'title' => [
						'eq' => 'a',
					],
				],
				'expected' => [],
			]],
			'when filtering with eq' => [[
				'records' => [
					'albums' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'filter' => [
					'title' => [
						'eq' => 'a',
					],
				],
				'expected' => ['a'],
			]],
			'when filtering with ge' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_date' => '1969'],
						['title' => 'b', 'release_date' => '1970'],
						['title' => 'c', 'release_date' => '1971'],
					],
				],
				'filter' => [
					'release_date' => [
						'ge' => '1970',
					],
				],
				'expected' => ['b', 'c'],
			]],
			'when filtering with gt' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_date' => '1969'],
						['title' => 'b', 'release_date' => '1970'],
						['title' => 'c', 'release_date' => '1971'],
					],
				],
				'filter' => [
					'release_date' => [
						'gt' => '1970',
					],
				],
				'expected' => ['c'],
			]],
			'when filtering with le' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_date' => '1969'],
						['title' => 'b', 'release_date' => '1970'],
						['title' => 'c', 'release_date' => '1971'],
					],
				],
				'filter' => [
					'release_date' => [
						'le' => '1970',
					],
				],
				'expected' => ['a', 'b'],
			]],
			'when filtering with like and leading wildcard' => [[
				'records' => [
					'albums' => [
						['title' => 'apple'],
						['title' => 'banana'],
						['title' => 'coconut'],
					],
				],
				'filter' => [
					'title' => [
						'like' => '%t',
					],
				],
				'expected' => ['coconut'],
			]],
			'when filtering with like and trailing wildcard' => [[
				'records' => [
					'albums' => [
						['title' => 'apple'],
						['title' => 'banana'],
						['title' => 'coconut'],
					],
				],
				'filter' => [
					'title' => [
						'like' => 'b%',
					],
				],
				'expected' => ['banana'],
			]],
			'when filtering with like and leading and trailing wildcards' => [[
				'records' => [
					'albums' => [
						['title' => 'apple'],
						['title' => 'banana'],
						['title' => 'coconut'],
					],
				],
				'filter' => [
					'title' => [
						'like' => '%n%',
					],
				],
				'expected' => ['banana', 'coconut'],
			]],
			'when filtering with notlike and leading wildcard' => [[
				'records' => [
					'albums' => [
						['title' => 'apple'],
						['title' => 'banana'],
						['title' => 'coconut'],
					],
				],
				'filter' => [
					'title' => [
						'notlike' => '%t',
					],
				],
				'expected' => ['apple', 'banana'],
			]],
			'when filtering with notlike and trailing wildcard' => [[
				'records' => [
					'albums' => [
						['title' => 'apple'],
						['title' => 'banana'],
						['title' => 'coconut'],
					],
				],
				'filter' => [
					'title' => [
						'notlike' => 'b%',
					],
				],
				'expected' => ['apple', 'coconut'],
			]],
			'when filtering with notlike and leading and trailing wildcards' => [[
				'records' => [
					'albums' => [
						['title' => 'apple'],
						['title' => 'banana'],
						['title' => 'coconut'],
					],
				],
				'filter' => [
					'title' => [
						'notlike' => '%n%',
					],
				],
				'expected' => ['apple'],
			]],
			'when filtering with lt' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_date' => '1969'],
						['title' => 'b', 'release_date' => '1970'],
						['title' => 'c', 'release_date' => '1971'],
					],
				],
				'filter' => [
					'release_date' => [
						'lt' => '1970',
					],
				],
				'expected' => ['a'],
			]],
			'when filtering with ne' => [[
				'records' => [
					'albums' => [
						['title' => 'a'],
						['title' => 'b'],
					],
				],
				'filter' => [
					'title' => [
						'ne' => 'a',
					],
				],
				'expected' => ['b'],
			]],
			'when filtering with in' => [[
				'records' => [
					'albums' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
					],
				],
				'filter' => [
					'title' => [
						'in' => 'a,c',
					],
				],
				'expected' => ['a', 'c'],
			]],
			'when filtering with null' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_date' => null],
						['title' => 'b', 'release_date' => '2020'],
					],
				],
				'filter' => [
					'release_date' => [
						'null' => '1',
					],
				],
				'expected' => ['a'],
			]],
			'when filtering with notnull' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_date' => null],
						['title' => 'b', 'release_date' => '2020'],
					],
				],
				'filter' => [
					'release_date' => [
						'notnull' => '1',
					],
				],
				'expected' => ['b'],
			]],
			'when filtering a relationship with eq' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'artist' => 'd'],
						['title' => 'b', 'artist' => 'e'],
						['title' => 'c', 'artist' => 'f'],
					],
					'artists' => [
						['title' => 'd'],
						['title' => 'e'],
						['title' => 'f'],
					],
				],
				'filter' => [
					'artist.title' => [
						'eq' => 'e',
					],
				],
				'expected' => ['b'],
			]],
		];
	}

	/**
	 * @dataProvider performProvider
	 */
	public function testPerform($args)
	{
		$this->createRecords($args['records']);
		$output = FilterHelper::perform((new Album())->newQuery(), $args['filter']);
		$titles = $output->get()->pluck('title')->toArray();
		$this->assertSame($args['expected'], $titles);
	}
}
