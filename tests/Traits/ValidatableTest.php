<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Traits;

use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Album;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ValidatableTest extends TestCase
{
	public function prettyAttributeNamesProvider()
	{
		return [
			[[
				'rules' => [
					'attributes.contact_email_address' => '',
					'relationships.foo' => '',
				],
				'expected' => [
					'attributes.contact_email_address' => 'contact email address',
					'relationships.foo' => 'foo',
				],
			]],
		];
	}

	/**
	 * @dataProvider prettyAttributeNamesProvider
	 */
	public function testPrettyAttributeNames($args)
	{
		$output = $this->callPrivate(new Album(), 'prettyAttributeNames', [$args['rules']]);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider()
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
