<?php

namespace Jlbelanger\Tapioca\Tests\Traits;

use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\TestCase;

class ValidatableTest extends TestCase
{
	public function validateProvider() : array
	{
		return [
			'with invalid data on create' => [[
				'data' => [],
				'method' => 'POST',
				'expected' => [
					'attributes.title' => ['The title field is required.'],
					'relationships.artist' => ['The artist field is required.'],
				],
			]],
			'with valid data on create' => [[
				'data' => [
					'attributes' => [
						'title' => 'foo',
					],
					'relationships' => [
						'artist' => [
							'id' => '123',
							'type' => 'artists',
						],
					],
				],
				'method' => 'POST',
				'expected' => [],
			]],
			'with invalid data on update' => [[
				'data' => [],
				'method' => 'PUT',
				'expected' => [],
			]],
			'with valid data on update' => [[
				'data' => [
					'attributes' => [
						'title' => 'foo',
					],
					'relationships' => [
						'artist' => [
							'id' => '123',
							'type' => 'artists',
						],
					],
				],
				'method' => 'PUT',
				'expected' => [],
			]],
			// TODO: when rules are an array vs string
		];
	}

	/**
	 * @dataProvider validateProvider
	 */
	public function testValidate($args)
	{
		$output = (new Album())->validate($args['data'], $args['method']);
		$this->assertSame($args['expected'], $output);
	}

	public function testRequiredOnCreate()
	{
		$output = $this->callPrivate(new Album(), 'requiredOnCreate', ['POST']);
		$this->assertSame('required', $output);

		$output = $this->callPrivate(new Album(), 'requiredOnCreate', ['PUT']);
		$this->assertSame('filled', $output);
	}
}
