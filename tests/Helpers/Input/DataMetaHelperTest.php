<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataMetaHelper;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class DataMetaHelperTest extends TestCase
{
	public function normalizeProvider()
	{
		return [
			'with an empty array' => [[
				'data' => [],
				'expected' => ['meta' => []],
			]],
			'with an empty array for meta' => [[
				'data' => ['meta' => []],
				'expected' => ['meta' => []],
			]],
			'with an empty string for meta' => [[
				'data' => ['meta' => ''],
				'expected' => ['meta' => []],
			]],
			'with null for meta' => [[
				'data' => ['meta' => null],
				'expected' => ['meta' => []],
			]],
			'with valid meta' => [[
				'data' => ['meta' => ['foo' => 'bar']],
				'expected' => ['meta' => ['foo' => 'bar']],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize($args)
	{
		$output = DataMetaHelper::normalize($args['data']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider()
	{
		return [
			'with a string' => [[
				'meta' => 'foo',
				'expectedMessage' => '{"title":"\'meta\' must be an object.","detail":"eg. {\"data\": {\"meta\": {}}}","source":{"pointer":"\/data\/meta"}}',
			]],
			'with an array' => [[
				'meta' => [],
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
		$this->callPrivate(new DataMetaHelper, 'validate', [$args['meta']]);
	}
}
