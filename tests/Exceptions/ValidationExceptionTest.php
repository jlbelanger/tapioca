<?php

namespace Jlbelanger\Tapioca\Tests\Exceptions;

use Jlbelanger\Tapioca\Exceptions\ValidationException;
use Jlbelanger\Tapioca\Tests\TestCase;

class ValidationExceptionTest extends TestCase
{
	public function generateProvider()
	{
		return [
			'with no errors' => [[
				'data' => [],
				'prefix' => 'blah',
				'expected' => [],
			]],
			'with errors' => [[
				'data' => [
					'attributes.foo' => ['Foo is not valid.', 'Foo is too short.'],
					'relationships.bar' => ['Bar is required.'],
				],
				'prefix' => 'blah',
				'expected' => [
					[
						'title' => 'Foo is not valid.',
						'source' => [
							'pointer' => '/blah/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Foo is too short.',
						'source' => [
							'pointer' => '/blah/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Bar is required.',
						'source' => [
							'pointer' => '/blah/relationships/bar',
						],
						'status' => '422',
					],
				],
			]],
		];
	}

	/**
	 * @dataProvider generateProvider
	 */
	public function testGenerate($args)
	{
		$output = ValidationException::generate($args['data'], $args['prefix']);
		$this->assertSame(json_encode($args['expected']), $output->getMessage());
	}

	public function formatErrorsProvider()
	{
		return [
			'with no errors' => [[
				'data' => [],
				'prefix' => 'blah',
				'expected' => [],
			]],
			'with errors' => [[
				'data' => [
					'attributes.foo' => ['Foo is not valid.', 'Foo is too short.'],
					'relationships.bar' => ['Bar is required.'],
				],
				'prefix' => 'blah',
				'expected' => [
					[
						'title' => 'Foo is not valid.',
						'source' => [
							'pointer' => '/blah/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Foo is too short.',
						'source' => [
							'pointer' => '/blah/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Bar is required.',
						'source' => [
							'pointer' => '/blah/relationships/bar',
						],
						'status' => '422',
					],
				],
			]],
		];
	}

	/**
	 * @dataProvider formatErrorsProvider
	 */
	public function testFormatErrors($args)
	{
		$output = ValidationException::formatErrors($args['data'], $args['prefix']);
		$this->assertSame($args['expected'], $output);
	}
}
