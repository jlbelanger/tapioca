<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Helpers;

use Jlbelanger\LaravelJsonApi\Helpers\Utilities;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class UtilitiesTest extends TestCase
{
	public function testGetClassNameFromType()
	{
		$this->assertSame('Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\EventType', Utilities::getClassNameFromType('event-types'));
		$this->assertSame('Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Quiz', Utilities::getClassNameFromType('quizzes'));
		$this->assertSame('Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\User', Utilities::getClassNameFromType('users'));
	}

	public function testIsTempId()
	{
		$this->assertSame(true, Utilities::isTempId('temp-1'));
		$this->assertSame(false, Utilities::isTempId('1'));
	}

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
		$output = Utilities::prettyAttributeNames($args['rules']);
		$this->assertSame($args['expected'], $output);
	}
}
