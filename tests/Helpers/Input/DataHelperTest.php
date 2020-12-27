<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataHelper;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class DataHelperTest extends TestCase
{
	public function testNormalize()
	{
		$this->markTestIncomplete();
	}

	public function validateProvider()
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
	public function testValidate($args)
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
