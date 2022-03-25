<?php

namespace Jlbelanger\Tapioca\Tests\Exceptions;

use Jlbelanger\Tapioca\Exceptions\NotFoundException;
use Jlbelanger\Tapioca\Tests\TestCase;

class NotFoundExceptionTest extends TestCase
{
	public function testGenerate()
	{
		$output = NotFoundException::generate();
		$this->assertSame('{"title":"URL does not exist.","status":"404"}', $output->getMessage());
	}
}
