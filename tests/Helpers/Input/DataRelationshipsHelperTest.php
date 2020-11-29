<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers\Input;

use Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException;
use Jlbelanger\LaravelJsonApi\Helpers\Input\DataRelationshipsHelper;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class DataRelationshipsHelperTest extends TestCase
{
	public function normalizeProvider()
	{
		return [
			'with an empty array' => [[
				'data' => [],
				'whitelistedRelationships' => ['user'],
				'expected' => ['relationships' => []],
			]],
			'with an empty array for relationships' => [[
				'data' => ['relationships' => []],
				'whitelistedRelationships' => ['user'],
				'expected' => ['relationships' => []],
			]],
			'with an empty string for relationships' => [[
				'data' => ['relationships' => ''],
				'whitelistedRelationships' => ['user'],
				'expected' => ['relationships' => []],
			]],
			'with null for relationships' => [[
				'data' => ['relationships' => null],
				'whitelistedRelationships' => ['user'],
				'expected' => ['relationships' => []],
			]],
			'with removing a valid singular relationship' => [[
				'data' => [
					'attributes' => [],
					'relationships' => [
						'user' => null,
					],
				],
				'whitelistedRelationships' => ['user'],
				'expected' => [
					'attributes' => [],
					'relationships' => [
						'user' => null,
					],
				],
			]],
			'with adding a valid singular relationship' => [[
				'data' => [
					'attributes' => [],
					'relationships' => [
						'user' => [
							'data' => [
								'id' => '123',
								'type' => 'users',
							],
						],
					],
				],
				'whitelistedRelationships' => ['user'],
				'expected' => [
					'attributes' => [],
					'relationships' => [
						'user' => [
							'data' => [
								'id' => '123',
								'type' => 'users',
							],
						],
					],
				],
			]],
			// TODO: Test multi relationships.
		];
	}

	/**
	 * @dataProvider normalizeProvider
	 */
	public function testNormalize($args)
	{
		$output = DataRelationshipsHelper::normalize($args['data'], $args['whitelistedRelationships']);
		$this->assertSame($args['expected'], $output);
	}

	public function validateProvider()
	{
		return [
			'with a string' => [[
				'relationships' => 'foo',
				'whitelistedRelationships' => ['user'],
				'expectedMessage' => '{"title":"\'relationships\' must be an object.","detail":"eg. {\"data\": {\"relationships\": {}}}","source":{"pointer":"\/data\/relationships"}}',
			]],
			'with a relationship string' => [[
				'relationships' => [
					'user' => 'foo',
				],
				'whitelistedRelationships' => ['user'],
				'expectedMessage' => '{"title":"\'user\' must be an object.","detail":"eg. {\"data\": {\"relationships\": {\"user\": {\"data\": {\"id\": \"1\", \"type\": \"foo\"}}}}","source":{"pointer":"\/data\/relationships\/user"}}',
			]],
			'with a relationship without data' => [[
				'relationships' => [
					'user' => [
						'id' => '123',
						'type' => 'user',
					],
				],
				'whitelistedRelationships' => ['user'],
				'expectedMessage' => '{"title":"\'user\' must contain a \'data\' key.","detail":"eg. {\"data\": {\"relationships\": {\"user\": {\"data\": {\"id\": \"1\", \"type\": \"foo\"}}}}","source":{"pointer":"\/data\/relationships\/user"}}',
			]],
			// TODO: Test without id/type.
			'with a non-whitelisted relationship' => [[
				'relationships' => [
					'foo' => [
						'data' => [
							'id' => '123',
							'type' => 'user',
						],
					],
				],
				'whitelistedRelationships' => ['user'],
				'expectedMessage' => '{"title":"\'foo\' is not a valid relationship.","source":{"pointer":"\/data\/relationships\/foo"}}',
			]],
			'with a valid whitelisted relationship' => [[
				'relationships' => [
					'user' => [
						'data' => [
							'id' => '123',
							'type' => 'user',
						],
					],
				],
				'whitelistedRelationships' => ['user'],
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
		$this->callPrivate(new DataRelationshipsHelper, 'validate', [$args['relationships'], $args['whitelistedRelationships']]);
	}
}
