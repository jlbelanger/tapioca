<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\PageHelper;
use Jlbelanger\Tapioca\Tests\TestCase;

class PageHelperTest extends TestCase
{
	public function normalizeProvider()
	{
		return [
			'with an empty array' => [[
				'page' => [],
				'expected' => [],
			]],
			'with an empty string' => [[
				'page' => '',
				'expected' => [],
			]],
			'with null' => [[
				'page' => null,
				'expected' => [],
			]],
			'with a valid size and number' => [[
				'page' => [
					'number' => '2',
					'size' => '10',
				],
				'expected' => [
					'number' => 2,
					'size' => 10,
				],
			]],
			'with a valid size, missing number' => [[
				'page' => [
					'size' => '10',
				],
				'expected' => [
					'number' => 1,
					'size' => 10,
				],
			]],
			'with valid non-integers' => [[
				'page' => [
					'number' => '2.5',
					'size' => '10.5',
				],
				'expected' => [
					'number' => 2,
					'size' => 10,
				],
			]],
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize($args)
	{
		$output = PageHelper::normalize($args['page']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider()
	{
		return [
			'with a string' => [[
				'page' => 'foo',
				'expectedMessage' => '{"title":"Parameter \'page\' must be an array.","detail":"eg. ?page[number]=1&page[size]=50"}',
			]],
			'with missing size' => [[
				'page' => [
					'number' => 10,
				],
				'expectedMessage' => '{"title":"Parameter \'page[size]\' is not specified."}',
			]],
			'with a negative number' => [[
				'page' => [
					'number' => -10,
					'size' => 10,
				],
				'expectedMessage' => '{"title":"Parameter \'page[number]\' must be greater than 0."}',
			]],
			'with a zero number' => [[
				'page' => [
					'number' => 0,
					'size' => 10,
				],
				'expectedMessage' => '{"title":"Parameter \'page[number]\' must be greater than 0."}',
			]],
			'with a negative size' => [[
				'page' => [
					'number' => 10,
					'size' => -10,
				],
				'expectedMessage' => '{"title":"Parameter \'page[size]\' must be greater than 0."}',
			]],
			'with a zero size' => [[
				'page' => [
					'number' => 10,
					'size' => 0,
				],
				'expectedMessage' => '{"title":"Parameter \'page[size]\' must be greater than 0."}',
			]],
			'with a valid array' => [[
				'page' => [
					'number' => 2,
					'size' => 10,
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
		$this->callPrivate(new PageHelper, 'validate', [$args['page']]);
	}
}
