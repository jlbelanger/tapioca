<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Helpers\Input\IncludeHelper;
use Jlbelanger\Tapioca\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class IncludeHelperTest extends TestCase
{
	public static function normalizeProvider() : array
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

	#[DataProvider('normalizeProvider')]
	public function testNormalize(array $args) : void
	{
		$output = IncludeHelper::normalize($args['include']);
		$this->assertSame($args['expected'], $output);
	}
}
