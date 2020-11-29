<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Helpers\Input\IncludeHelper;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class IncludeHelperTest extends TestCase
{
	public function normalizeProvider()
	{
		return [
			'with an empty array' => [[
				'include' => [],
				'expected' => [],
			]],
			'with an empty string' => [[
				'include' => '',
				'expected' => [],
			]],
			'with null' => [[
				'include' => null,
				'expected' => [],
			]],
			'with a valid string' => [[
				'include' => 'foo, bar',
				'expected' => ['foo', 'bar'],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize($args)
	{
		$output = IncludeHelper::normalize($args['include']);
		$this->assertSame($args['expected'], $output);
	}
}
