<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Traits;

use Illuminate\Http\Request;
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
				'isUpdate' => false,
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
				'isUpdate' => false,
				'expected' => [],
			]],
			'with invalid data on update' => [[
				'data' => [],
				'isUpdate' => true,
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
				'isUpdate' => true,
				'expected' => [],
			]],
		];
	}

	/**
	 * @dataProvider validateProvider
	 */
	public function testValidate($args)
	{
		$output = (new Album())->validate($args['data'], new Request(), $args['isUpdate']);
		$this->assertSame($args['expected'], $output);
	}
}
