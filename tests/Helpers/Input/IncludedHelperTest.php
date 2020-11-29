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
				'included' => ['foo' => 'bar'],
				'expected' => ['foo' => 'bar'],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize($args)
	{
		$output = IncludedHelper::normalize($args['included']);
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
