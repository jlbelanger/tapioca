<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Traits;

use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class ValidatableTest extends TestCase
{
	public function prettyAttributeNamesProvider()
	{
		return [
			[[
				'rules' => [
					'attributes.contact_email_address' => '',
					'relationships.user' => '',
				],
				'expected' => [
					'attributes.contact_email_address' => 'contact email address',
					'relationships.user' => 'user',
				],
			]],
		];
	}

	/**
	 * @dataProvider prettyAttributeNamesProvider
	 */
	public function testPrettyAttributeNames($args)
	{
		$article = new Article();
		$output = $this->callPrivate($article, 'prettyAttributeNames', [$args['rules']]);
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
					'relationships.user' => ['The user field is required.'],
				],
			]],
			'with valid data on create' => [[
				'data' => [
					'attributes' => [
						'title' => 'foo',
					],
					'relationships' => [
						'user' => [
							'id' => '1',
							'type' => 'users',
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
						'user' => [
							'id' => '1',
							'type' => 'users',
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
		$article = new Article();
		$output = $article->validate($args['data'], $args['isUpdate']);
		$this->assertSame($args['expected'], $output);
	}
}
