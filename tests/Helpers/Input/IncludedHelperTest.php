<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\IncludedHelper;
use Jlbelanger\Tapioca\Tests\TestCase;

class IncludedHelperTest extends TestCase
{
	public static function normalizeProvider() : array
	{
		return [
			'with an empty array' => [[
				'included' => [],
				'expected' => [],
			]],
			'with an empty string' => [[
				'included' => '',
				'expected' => [],
			]],
			'with null' => [[
				'included' => null,
				'expected' => [],
			]],
			'with valid included' => [[
				'included' => [
					[
						'id' => '1',
						'type' => 'albums',
					],
				],
				'expected' => [
					[
						'id' => '1',
						'type' => 'albums',
						'attributes' => [],
						'relationships' => [],
						'meta' => [],
					],
				],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize(array $args) : void
	{
		$output = IncludedHelper::normalize($args['included']);
		$this->assertSame($args['expected'], $output);
	}

	public static function validateProvider() : array
	{
		return [
			'with a string' => [[
				'included' => 'foo',
				'expectedMessage' => '{"title":"\'included\' must be an array.","detail":"eg. {\"included\": []}","source":{"pointer":"\/included"}}',
			]],
			'with an empty array' => [[
				'included' => [],
				'expectedMessage' => null,
			]],
			'with an associative array' => [[
				'included' => ['foo' => 'bar'],
				'expectedMessage' => '{"title":"\'included\' must be an array.","detail":"eg. {\"included\": []}","source":{"pointer":"\/included"}}',
			]],
			'with missing id and type' => [[
				'included' => [[]],
				'expectedMessage' => '{"title":"Included records must contain \'id\' key.","detail":"eg. {\"included\": [{\"id\": \"1\", \"type\": \"foo\"}]}","source":{"pointer":"\/included\/0"}}',
			]],
			'with missing id' => [[
				'included' => [
					[
						'type' => 'albums',
					],
				],
				'expectedMessage' => '{"title":"Included records must contain \'id\' key.","detail":"eg. {\"included\": [{\"id\": \"1\", \"type\": \"foo\"}]}","source":{"pointer":"\/included\/0"}}',
			]],
			'with missing type' => [[
				'included' => [
					[
						'id' => '1',
					],
				],
				'expectedMessage' => '{"title":"Included records must contain \'type\' key.","detail":"eg. {\"included\": [{\"id\": \"1\", \"type\": \"foo\"}]}","source":{"pointer":"\/included\/0"}}',
			]],
			'with invalid type' => [[
				'included' => [
					[
						'id' => '1',
						'type' => 'foo',
					],
				],
				'expectedMessage' => '{"title":"Type \'foo\' is invalid.","source":{"pointer":"\/included\/0\/type"}}',
			]],
			// TODO: with invalid attributes/relationships/meta/etc
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
		$this->callPrivate(new IncludedHelper, 'validate', [$args['included']]);
	}
}
