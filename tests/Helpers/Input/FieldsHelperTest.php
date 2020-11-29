<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\FieldsHelper;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class FieldsHelperTest extends TestCase
{
	public function normalizeProvider()
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
	public function testNormalize($args)
	{
		$output = FieldsHelper::normalize($args['fields']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider()
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
	public function testValidate($args)
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
