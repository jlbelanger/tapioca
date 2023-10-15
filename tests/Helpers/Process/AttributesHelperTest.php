<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Process;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jlbelanger\Tapioca\Helpers\Process\AttributesHelper;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Album;
use Jlbelanger\Tapioca\Tests\TestCase;

class AttributesHelperTest extends TestCase
{
	use RefreshDatabase;

	public function convertSingularRelationshipsProvider() : array
	{
		return [
			'when removing a valid singular relationship' => [[
				'data' => [
					'attributes' => [],
					'relationships' => [
						'artist' => [
							'data' => null,
						],
					],
				],
				'expected' => [
					'attributes' => [
						'artist_id' => null,
					],
					'relationships' => [],
				],
			]],
			'when adding a valid singular relationship' => [[
				'data' => [
					'attributes' => [],
					'relationships' => [
						'artist' => [
							'data' => [
								'id' => '123',
								'type' => 'artists',
							],
						],
					],
				],
				'expected' => [
					'attributes' => [
						'artist_id' => '123',
					],
					'relationships' => [],
				],
			]],
		];
	}

	/**
	 * @dataProvider convertSingularRelationshipsProvider
	 */
	public function testConvertSingularRelationships(array $args) : void
	{
		$record = Album::factory()->create();
		$output = $this->callPrivate(new AttributesHelper, 'convertSingularRelationships', [$args['data'], $record]);
		$this->assertSame($args['expected'], $output);
	}
}
