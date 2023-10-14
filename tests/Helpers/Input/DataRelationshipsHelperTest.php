<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Input;

use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Helpers\Input\DataRelationshipsHelper;
use Jlbelanger\Tapioca\Tests\TestCase;

class DataRelationshipsHelperTest extends TestCase
{
	public function normalizeProvider()
	{
		return [
			'with an empty array' => [[
				'data' => [],
				'whitelistedRelationships' => ['foo'],
				'expected' => ['relationships' => []],
			]],
			'with an empty array for relationships' => [[
				'data' => ['relationships' => []],
				'whitelistedRelationships' => ['foo'],
				'expected' => ['relationships' => []],
			]],
			'with an empty string for relationships' => [[
				'data' => ['relationships' => ''],
				'whitelistedRelationships' => ['foo'],
				'expected' => ['relationships' => []],
			]],
			'with null for relationships' => [[
				'data' => ['relationships' => null],
				'whitelistedRelationships' => ['foo'],
				'expected' => ['relationships' => []],
			]],
			'with removing a valid singular relationship' => [[
				'data' => [
					'attributes' => [],
					'relationships' => [
						'foo' => null,
					],
				],
				'whitelistedRelationships' => ['foo'],
				'expected' => [
					'attributes' => [],
					'relationships' => [
						'foo' => null,
					],
				],
			]],
			'with adding a valid singular relationship' => [[
				'data' => [
					'attributes' => [],
					'relationships' => [
						'foo' => [
							'data' => [
								'id' => '123',
								'type' => 'foos',
							],
						],
					],
				],
				'whitelistedRelationships' => ['foo'],
				'expected' => [
					'attributes' => [],
					'relationships' => [
						'foo' => [
							'data' => [
								'id' => '123',
								'type' => 'foos',
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
				'whitelistedRelationships' => ['artist'],
				'expectedMessage' => '{"title":"\'relationships\' must be an object.","detail":"eg. {\"data\": {\"relationships\": {}}}","source":{"pointer":"\/data\/relationships"}}',
			]],
			'with a relationship string' => [[
				'relationships' => [
					'artist' => 'foo',
				],
				'whitelistedRelationships' => ['artist'],
				'expectedMessage' => '{"title":"\'artist\' must be an object.","detail":"eg. {\"data\": {\"relationships\": {\"artist\": {\"data\": {\"id\": \"1\", \"type\": \"foo\"}}}}","source":{"pointer":"\/data\/relationships\/artist"}}',
			]],
			'with a relationship without data' => [[
				'relationships' => [
					'artist' => [
						'id' => '123',
						'type' => 'artists',
					],
				],
				'whitelistedRelationships' => ['artist'],
				'expectedMessage' => '{"title":"\'artist\' must contain \'data\' key.","detail":"eg. {\"data\": {\"relationships\": {\"artist\": {\"data\": {\"id\": \"1\", \"type\": \"foo\"}}}}","source":{"pointer":"\/data\/relationships\/artist"}}',
			]],
			'with missing id' => [[
				'relationships' => [
					'foo' => [
						'data' => [
							'type' => 'artists',
						],
					],
				],
				'whitelistedRelationships' => ['artist'],
				'expectedMessage' => '{"title":"\'foo\' is not a valid relationship.","source":{"pointer":"\/data\/relationships\/foo"}}',
			]],
			'with missing type' => [[
				'relationships' => [
					'foo' => [
						'data' => [
							'id' => '123',
						],
					],
				],
				'whitelistedRelationships' => ['artist'],
				'expectedMessage' => '{"title":"\'foo\' is not a valid relationship.","source":{"pointer":"\/data\/relationships\/foo"}}',
			]],
			'with a non-whitelisted relationship' => [[
				'relationships' => [
					'foo' => [
						'data' => [
							'id' => '123',
							'type' => 'artists',
						],
					],
				],
				'whitelistedRelationships' => ['artist'],
				'expectedMessage' => '{"title":"\'foo\' is not a valid relationship.","source":{"pointer":"\/data\/relationships\/foo"}}',
			]],
			'with a valid whitelisted relationship' => [[
				'relationships' => [
					'artist' => [
						'data' => [
							'id' => '123',
							'type' => 'artists',
						],
					],
				],
				'whitelistedRelationships' => ['artist'],
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
