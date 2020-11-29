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
					'attributes.email' => ['Email is not valid.', 'Email is too short.'],
					'relationships.user' => ['User is required.'],
				],
				'expected' => [
					[
						'title' => 'Email is not valid.',
						'source' => [
							'pointer' => '/data/attributes/email',
						],
						'status' => '422',
					],
					[
						'title' => 'Email is too short.',
						'source' => [
							'pointer' => '/data/attributes/email',
						],
						'status' => '422',
					],
					[
						'title' => 'User is required.',
						'source' => [
							'pointer' => '/data/relationships/user',
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
					'attributes.email' => ['Email is not valid.', 'Email is too short.'],
					'relationships.user' => ['User is required.'],
				],
				'expected' => [
					[
						'title' => 'Email is not valid.',
						'source' => [
							'pointer' => '/data/attributes/email',
						],
						'status' => '422',
					],
					[
						'title' => 'Email is too short.',
						'source' => [
							'pointer' => '/data/attributes/email',
						],
						'status' => '422',
					],
					[
						'title' => 'User is required.',
						'source' => [
							'pointer' => '/data/relationships/user',
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
