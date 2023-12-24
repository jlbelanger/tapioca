<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\DataHelper;
use Jlbelanger\Tapioca\Tests\TestCase;

class DataHelperTest extends TestCase
{
	public function testNormalize() : void
	{
		$this->markTestIncomplete();
	}

	public static function validateProvider() : array
	{
		return [
			'with a string' => [[
				'data' => 'foo',
				'expectedMessage' => '{"title":"\'data\' must be an object.","detail":"eg. {\"data\": {}}"}',
			]],
			'with an array with an invalid key' => [[
				'data' => [
					'foo' => null,
				],
				'expectedMessage' => '{"title":"\'data\' contains disallowed keys: \'foo\'."}',
			]],
			'with an array with invalid keys' => [[
				'data' => [
					'foo' => null,
					'bar' => null,
				],
				'expectedMessage' => '{"title":"\'data\' contains disallowed keys: \'foo\', \'bar\'."}',
			]],
			'with a valid array' => [[
				'data' => [
					'id' => null,
					'type' => null,
					'attributes' => null,
					'relationships' => null,
					'included' => null,
					'meta' => null,
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
		$this->callPrivate(new DataHelper, 'validate', [$args['data']]);
	}
}
