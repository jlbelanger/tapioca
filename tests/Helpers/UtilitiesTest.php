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
}
