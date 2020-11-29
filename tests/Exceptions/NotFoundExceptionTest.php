<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Exceptions;

use Jlbelanger\LaravelJsonApi\Exceptions\NotFoundException;
use Jlbelanger\LaravelJsonApi\Tests\TestCase;

class NotFoundExceptionTest extends TestCase
{
	public function testGenerate()
	{
		$output = NotFoundException::generate();
		$this->assertSame('{"title":"URL does not exist.","status":"404"}', $output->getMessage());
	}
}
