<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\DataAttributesHelper;
use Jlbelanger\Tapioca\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DataAttributesHelperTest extends TestCase
{
	public static function normalizeProvider() : array
	{
		return [
			'with an empty array' => [[
				'data' => [],
				'whitelistedAttributes' => ['title'],
				'expected' => ['attributes' => []],
			]],
			'with an empty array for attributes' => [[
				'data' => ['attributes' => []],
				'whitelistedAttributes' => ['title'],
				'expected' => ['attributes' => []],
			]],
			'with an empty string for attributes' => [[
				'data' => ['attributes' => ''],
				'whitelistedAttributes' => ['title'],
				'expected' => ['attributes' => []],
			]],
			'with null for attributes' => [[
				'data' => ['attributes' => null],
				'whitelistedAttributes' => ['title'],
				'expected' => ['attributes' => []],
			]],
			'with valid attributes' => [[
				'data' => ['attributes' => ['title' => 'foo']],
				'whitelistedAttributes' => ['title'],
				'expected' => ['attributes' => ['title' => 'foo']],
			]],
		];
	}

	#[DataProvider('normalizeProvider')]
	public function testNormalize(array $args) : void
	{
		$output = DataAttributesHelper::normalize($args['data'], $args['whitelistedAttributes']);
		$this->assertSame($args['expected'], $output);
	}

	public static function validateProvider() : array
	{
		return [
			'with a string' => [[
				'attributes' => 'foo',
				'whitelistedAttributes' => ['title'],
				'expectedMessage' => '{"title":"\'attributes\' must be an object.","detail":"eg. {\"data\": {\"attributes\": {}}}","source":{"pointer":"\/data\/attributes"}}',
			]],
			'with non-whitelisted attributes' => [[
				'attributes' => ['foo' => 'bar'],
				'whitelistedAttributes' => ['title'],
				'expectedMessage' => '{"title":"\'foo\' is not a valid attribute.","source":{"pointer":"\/data\/attributes\/foo"}}',
			]],
			'with whitelisted attributes' => [[
				'attributes' => ['title' => 'foo'],
				'whitelistedAttributes' => ['title'],
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
		$this->callPrivate(new DataAttributesHelper, 'validate', [$args['attributes'], $args['whitelistedAttributes']]);
	}
}
