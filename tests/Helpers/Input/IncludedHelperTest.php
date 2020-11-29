<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\IncludedHelper;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class IncludedHelperTest extends TestCase
{
	public function normalizeProvider()
	{
		return [
			'with an empty array' => [[
				'data' => [],
				'expected' => ['included' => []],
			]],
			'with an empty array for included' => [[
				'data' => ['included' => []],
				'expected' => ['included' => []],
			]],
			'with an empty string for included' => [[
				'data' => ['included' => ''],
				'expected' => ['included' => []],
			]],
			'with null for included' => [[
				'data' => ['included' => null],
				'expected' => ['included' => []],
			]],
			'with valid included' => [[
				'data' => ['included' => ['foo' => 'bar']],
				'expected' => ['included' => ['foo' => 'bar']],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize($args)
	{
		$output = IncludedHelper::normalize($args['data']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider()
	{
		return [
			'with a string' => [[
				'included' => 'foo',
				'expectedMessage' => '{"title":"\'included\' must be an array.","detail":"eg. {\"data\": {\"included\": []}}","source":{"pointer":"\/data\/included"}}',
			]],
			'with an array' => [[
				'included' => [],
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
		$this->callPrivate(new IncludedHelper, 'validate', [$args['included']]);
	}
}
