<?php

namespace Jlbelanger\Tapioca\Tests\Exceptions;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Tests\TestCase;

class JsonApiExceptionTest extends TestCase
{
	public function generateProvider()
	{
		return [
			'with a single error' => [[
				'data' => [
					'title' => 'This is the title.',
					'source' => [
						'pointer' => 'This is the pointer.',
					],
					'detail' => 'This is the detail.',
				],
				'code' => 404,
			]],
			'with a multiple errors' => [[
				'data' => [
					[
						'title' => 'One.',
					],
					[
						'title' => 'Two.',
					],
				],
				'code' => 404,
			]],
		];
	}

	/**
	 * @dataProvider generateProvider
	 */
	public function testGenerate($args)
	{
		$output = JsonApiException::generate($args['data'], $args['code']);
		$this->assertSame(json_encode($args['data']), $output->getMessage());
		$this->assertSame($args['code'], $output->getCode());
	}

	public function getErrorsProvider()
	{
		return [
			'with a single error' => [[
				'message' => [
					'title' => 'This is the title.',
					'source' => [
						'pointer' => 'This is the pointer.',
					],
					'detail' => 'This is the detail.',
				],
				'code' => 404,
				'expected' => [
					[
						'title' => 'This is the title.',
						'source' => [
							'pointer' => 'This is the pointer.',
						],
						'status' => '404',
						'detail' => 'This is the detail.',
					],
				],
			]],
			'with a multiple errors' => [[
				'message' => [
					[
						'title' => 'One.',
					],
					[
						'title' => 'Two.',
					],
				],
				'code' => 404,
				'expected' => [
					[
						'title' => 'One.',
						'status' => '404',
					],
					[
						'title' => 'Two.',
						'status' => '404',
					],
				],
			]],
		];
	}

	/**
	 * @dataProvider getErrorsProvider
	 */
	public function testGetErrors($args)
	{
		$exception = JsonApiException::generate($args['message'], $args['code']);
		$this->assertSame($args['expected'], $exception->getErrors());
	}

	public function formatErrorProvider()
	{
		return [
			'with all values' => [[
				'error' => (object) [
					'title' => 'This is the title.',
					'source' => [
						'pointer' => 'This is the pointer.',
					],
					'detail' => 'This is the detail.',
				],
				'code' => 404,
				'expected' => [
					'title' => 'This is the title.',
					'source' => [
						'pointer' => 'This is the pointer.',
					],
					'status' => '404',
					'detail' => 'This is the detail.',
				],
			]],
			'with no values' => [[
				'error' => (object) [],
				'code' => 404,
				'expected' => [
					'status' => '404',
				],
			]],
		];
	}

	/**
	 * @dataProvider formatErrorProvider
	 */
	public function testFormatError($args)
	{
		$exception = JsonApiException::generate([], $args['code']);
		$output = $this->callPrivate($exception, 'formatError', [$args['error']]);
		$this->assertSame($args['expected'], $output);
	}
}
