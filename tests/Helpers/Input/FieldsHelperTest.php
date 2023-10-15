<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\FieldsHelper;
use Jlbelanger\Tapioca\Tests\TestCase;

class FieldsHelperTest extends TestCase
{
	public function normalizeProvider() : array
	{
		return [
			'with an empty array' => [[
				'fields' => [],
				'expected' => [],
			]],
			'with an empty string' => [[
				'fields' => '',
				'expected' => [],
			]],
			'with null' => [[
				'fields' => null,
				'expected' => [],
			]],
			'with a valid array' => [[
				'fields' => [
					'a' => 'b, c',
				],
				'expected' => [
					'a' => ['b', 'c'],
				],
			]],
			'with multiple valid arrays' => [[
				'fields' => [
					'a' => 'b, c',
					'd' => 'e',
				],
				'expected' => [
					'a' => ['b', 'c'],
					'd' => ['e'],
				],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize(array $args) : void
	{
		$output = FieldsHelper::normalize($args['fields']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider() : array
	{
		return [
			'with a string' => [[
				'fields' => 'foo',
				'expectedMessage' => '{"title":"Parameter \'fields\' must be an array.","detail":"eg. ?fields[foo]=bar"}',
			]],
			'with an array' => [[
				'fields' => [
					'a' => 'b',
				],
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
		$this->callPrivate(new FieldsHelper, 'validate', [$args['fields']]);
	}
}
