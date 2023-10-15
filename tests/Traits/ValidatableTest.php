<?php

namespace Jlbelanger\Tapioca\Tests\Traits;

use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\TestCase;

class ValidatableTest extends TestCase
{
	public function testRequiredOnCreate() : void
	{
		$output = $this->callPrivate(new Album(), 'requiredOnCreate');
		$this->assertSame('required', $output);

		$output = $this->callPrivate(Album::factory()->create(), 'requiredOnCreate');
		$this->assertSame('filled', $output);
	}
}
