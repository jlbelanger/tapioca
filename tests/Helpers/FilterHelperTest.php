<?php

namespace Jlbelanger\Tapioca\Tests\Helpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\FilterHelper;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FilterHelperTest extends TestCase
{
	use RefreshDatabase;

	public static function normalizeProvider() : array
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

	#[DataProvider('normalizeProvider')]
	public function testNormalize(array $args) : void
	{
		$output = FilterHelper::normalize($args['filter'], $args['defaultFilter']);
		$this->assertSame($args['expected'], $output);
	}

	public static function validateProvider() : array
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
				'expectedMessage' => '{"title":"Parameter \'filter[foo][equals]\' has invalid operation.","detail":"Permitted operations: eq, ge, gt, le, like, notlike, lt, ne, in, notin, null, notnull"}',
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

	#[DataProvider('validateProvider')]
	public function testValidate(array $args) : void
	{
		if (!empty($args['expectedMessage'])) {
			$this->expectException(JsonApiException::class);
			$this->expectExceptionMessage($args['expectedMessage']);
		} else {
			$this->expectNotToPerformAssertions();
		}
		$this->callPrivate(new FilterHelper, 'validate', [$args['filter']]);
	}

	public static function performProvider() : array
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
						['title' => 'a', 'release_year' => '1969'],
						['title' => 'b', 'release_year' => '1970'],
						['title' => 'c', 'release_year' => '1971'],
					],
				],
				'filter' => [
					'release_year' => [
						'ge' => '1970',
					],
				],
				'expected' => ['b', 'c'],
			]],
			'when filtering with gt' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_year' => '1969'],
						['title' => 'b', 'release_year' => '1970'],
						['title' => 'c', 'release_year' => '1971'],
					],
				],
				'filter' => [
					'release_year' => [
						'gt' => '1970',
					],
				],
				'expected' => ['c'],
			]],
			'when filtering with le' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_year' => '1969'],
						['title' => 'b', 'release_year' => '1970'],
						['title' => 'c', 'release_year' => '1971'],
					],
				],
				'filter' => [
					'release_year' => [
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
						['title' => 'a', 'release_year' => '1969'],
						['title' => 'b', 'release_year' => '1970'],
						['title' => 'c', 'release_year' => '1971'],
					],
				],
				'filter' => [
					'release_year' => [
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
			'when filtering with notin' => [[
				'records' => [
					'albums' => [
						['title' => 'a'],
						['title' => 'b'],
						['title' => 'c'],
					],
				],
				'filter' => [
					'title' => [
						'notin' => 'a,c',
					],
				],
				'expected' => ['b'],
			]],
			'when filtering with null' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_year' => null],
						['title' => 'b', 'release_year' => '2020'],
					],
				],
				'filter' => [
					'release_year' => [
						'null' => '1',
					],
				],
				'expected' => ['a'],
			]],
			'when filtering with notnull' => [[
				'records' => [
					'albums' => [
						['title' => 'a', 'release_year' => null],
						['title' => 'b', 'release_year' => '2020'],
					],
				],
				'filter' => [
					'release_year' => [
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

	#[DataProvider('performProvider')]
	public function testPerform(array $args) : void
	{
		$this->createRecords($args['records']);
		$output = FilterHelper::perform((new Album())->newQuery(), $args['filter']);
		$titles = $output->get()->pluck('title')->toArray();
		$this->assertSame($args['expected'], $titles);
	}
}
