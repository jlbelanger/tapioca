<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\FilterHelper;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

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
				'expectedMessage' => '{"title":"Parameter \'filter[foo][equals]\' has invalid operation.","detail":"Permitted operations: eq, ge, gt, le, like, lt, ne, in, null, notnull"}',
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
					'articles' => [
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
					'articles' => [
						['title' => 'a', 'word_count' => '9'],
						['title' => 'b', 'word_count' => '10'],
						['title' => 'c', 'word_count' => '11'],
					],
				],
				'filter' => [
					'word_count' => [
						'ge' => '10',
					],
				],
				'expected' => ['b', 'c'],
			]],
			'when filtering with gt' => [[
				'records' => [
					'articles' => [
						['title' => 'a', 'word_count' => '9'],
						['title' => 'b', 'word_count' => '10'],
						['title' => 'c', 'word_count' => '11'],
					],
				],
				'filter' => [
					'word_count' => [
						'gt' => '10',
					],
				],
				'expected' => ['c'],
			]],
			'when filtering with le' => [[
				'records' => [
					'articles' => [
						['title' => 'a', 'word_count' => '9'],
						['title' => 'b', 'word_count' => '10'],
						['title' => 'c', 'word_count' => '11'],
					],
				],
				'filter' => [
					'word_count' => [
						'le' => '10',
					],
				],
				'expected' => ['a', 'b'],
			]],
			'when filtering with like and leading wildcard' => [[
				'records' => [
					'articles' => [
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
					'articles' => [
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
					'articles' => [
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
			'when filtering with lt' => [[
				'records' => [
					'articles' => [
						['title' => 'a', 'word_count' => '9'],
						['title' => 'b', 'word_count' => '10'],
						['title' => 'c', 'word_count' => '11'],
					],
				],
				'filter' => [
					'word_count' => [
						'lt' => '10',
					],
				],
				'expected' => ['a'],
			]],
			'when filtering with ne' => [[
				'records' => [
					'articles' => [
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
					'articles' => [
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
					'articles' => [
						['title' => 'a', 'content' => null],
						['title' => 'b', 'content' => 'b'],
					],
				],
				'filter' => [
					'content' => [
						'null' => '1',
					],
				],
				'expected' => ['a'],
			]],
			'when filtering with notnull' => [[
				'records' => [
					'articles' => [
						['title' => 'a', 'content' => null],
						['title' => 'b', 'content' => 'b'],
					],
				],
				'filter' => [
					'content' => [
						'notnull' => '1',
					],
				],
				'expected' => ['b'],
			]],
			'when filtering a relationship with eq' => [[
				'records' => [
					'articles' => [
						['title' => 'a', 'username' => 'd'],
						['title' => 'b', 'username' => 'e'],
						['title' => 'c', 'username' => 'f'],
					],
					'users' => [
						['username' => 'd'],
						['username' => 'e'],
						['username' => 'f'],
					],
				],
				'filter' => [
					'user.username' => [
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
		$output = FilterHelper::perform((new Article())->newModelQuery(), $args['filter']);
		$titles = $output->get()->pluck('title')->toArray();
		$this->assertSame($args['expected'], $titles);
	}
}
