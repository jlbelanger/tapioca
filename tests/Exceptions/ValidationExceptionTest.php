<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Exceptions;

use Jlbelanger\LaravelJsonApi\Exceptions\ValidationException;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ValidationExceptionTest extends TestCase
{
	public function generateProvider()
	{
		return [
			'with no errors' => [[
				'data' => [],
				'expected' => [],
			]],
			'with errors' => [[
				'data' => [
					'attributes.foo' => ['Foo is not valid.', 'Foo is too short.'],
					'relationships.bar' => ['Bar is required.'],
				],
				'expected' => [
					[
						'title' => 'Foo is not valid.',
						'source' => [
							'pointer' => '/data/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Foo is too short.',
						'source' => [
							'pointer' => '/data/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Bar is required.',
						'source' => [
							'pointer' => '/data/relationships/bar',
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
		$output = ValidationException::generate($args['data']);
		$this->assertSame(json_encode($args['expected']), $output->getMessage());
	}

	public function formatErrorsProvider()
	{
		return [
			'with no errors' => [[
				'data' => [],
				'expected' => [],
			]],
			'with errors' => [[
				'data' => [
					'attributes.foo' => ['Foo is not valid.', 'Foo is too short.'],
					'relationships.bar' => ['Bar is required.'],
				],
				'expected' => [
					[
						'title' => 'Foo is not valid.',
						'source' => [
							'pointer' => '/data/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Foo is too short.',
						'source' => [
							'pointer' => '/data/attributes/foo',
						],
						'status' => '422',
					],
					[
						'title' => 'Bar is required.',
						'source' => [
							'pointer' => '/data/relationships/bar',
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
		$output = $this->callPrivate(new ValidationException, 'formatErrors', [$args['data']]);
		$this->assertSame($args['expected'], $output);
	}
}
