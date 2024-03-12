<?php

namespace Jlbelanger\Tapioca\Tests\Helpers;

use Jlbelanger\Tapioca\Helpers\Utilities;
use Jlbelanger\Tapioca\Tests\TestCase;

class UtilitiesTest extends TestCase
{
	public function testGetClassNameFromType() : void
	{
		$this->assertSame('Jlbelanger\Tapioca\Tests\Dummy\App\Models\EventType', Utilities::getClassNameFromType('event-types'));
		$this->assertSame('Jlbelanger\Tapioca\Tests\Dummy\App\Models\Quiz', Utilities::getClassNameFromType('quizzes'));
		$this->assertSame('Jlbelanger\Tapioca\Tests\Dummy\App\Models\User', Utilities::getClassNameFromType('users'));
	}

	public function testIsTempId() : void
	{
		$this->assertSame(true, Utilities::isTempId('temp-1'));
		$this->assertSame(false, Utilities::isTempId('1'));
	}

	public static function prettyAttributeNamesProvider() : array
	{
		return [
			[[
				'rules' => [
					'data.attributes.contact_email_address' => '',
					'data.relationships.foo' => '',
					'data.relationships.foo.data' => '',
					'data.relationships.foo.data.id' => '',
				],
				'expected' => [
					'data.attributes.contact_email_address' => 'contact email address',
					'data.relationships.foo' => 'foo',
					'data.relationships.foo.data' => 'foo',
					'data.relationships.foo.data.id' => 'id',
				],
			]],
		];
	}

	/**
	 * @dataProvider prettyAttributeNamesProvider
	 */
	public function testPrettyAttributeNames(array $args) : void
	{
		$output = Utilities::prettyAttributeNames($args['rules']);
		$this->assertSame($args['expected'], $output);
	}
}
