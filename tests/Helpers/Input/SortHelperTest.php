<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\SortHelper;
use Jlbelanger\Tapioca\Tests\TestCase;

class SortHelperTest extends TestCase
{
	public function normalizeProvider() : array
	{
		return [
			'with an empty array and no default sort' => [[
				'sort' => [],
				'defaultSort' => [],
				'expected' => [],
			]],
			'with an empty string and no default sort' => [[
				'sort' => '',
				'defaultSort' => [],
				'expected' => [],
			]],
			'with null and no default sort' => [[
				'sort' => null,
				'defaultSort' => [],
				'expected' => [],
			]],
			'with a valid string and no default sort' => [[
				'sort' => 'foo, -bar',
				'defaultSort' => [],
				'expected' => ['foo', '-bar'],
			]],
			'with an empty array and a default sort' => [[
				'sort' => [],
				'defaultSort' => ['a', 'b', 'c'],
				'expected' => ['a', 'b', 'c'],
			]],
			'with an empty string and a default sort' => [[
				'sort' => '',
				'defaultSort' => ['a', 'b', 'c'],
				'expected' => ['a', 'b', 'c'],
			]],
			'with null and a default sort' => [[
				'sort' => null,
				'defaultSort' => ['a', 'b', 'c'],
				'expected' => ['a', 'b', 'c'],
			]],
			'with a valid string and a default sort' => [[
				'sort' => 'foo, -bar',
				'defaultSort' => ['a', 'b', 'c'],
				'expected' => ['foo', '-bar'],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize(array $args) : void
	{
		$output = SortHelper::normalize($args['sort'], $args['defaultSort']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider() : array
	{
		return [
			'with an array' => [[
				'sort' => ['foo' => 'bar'],
				'expectedMessage' => '{"title":"Parameter \'sort\' must be a string.","detail":"eg. ?sort=foo,-bar"}',
			]],
			'with a string' => [[
				'sort' => 'foo',
				'expectedMessage' => null,
			]],
		];
	}

	/**
	 * @dataProvider validateProvider
	 */
	public function testValidate(array $args) : void
	{
		if (!empty($args['expectedMessage'])) {
			$this->expectException(JsonApiException::class);
			$this->expectExceptionMessage($args['expectedMessage']);
		} else {
			$this->expectNotToPerformAssertions();
		}
		$this->callPrivate(new SortHelper, 'validate', [$args['sort']]);
	}
}
