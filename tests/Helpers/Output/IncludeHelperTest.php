<?php

namespace Jlbelanger\Tapioca\Tests\Helpers\Output;

use Jlbelanger\Tapioca\Helpers\Output\IncludeHelper;
use Jlbelanger\Tapioca\Tests\TestCase;

class IncludeHelperTest extends TestCase
{
	public function testPrepare()
	{
		$this->markTestIncomplete();
	}

	public function testPerform()
	{
		$this->markTestIncomplete();
	}

	public function testInclude()
	{
		$this->markTestIncomplete();
	}

	public function testGetRecordFromData()
	{
		$this->markTestIncomplete();
	}

	public function isKnownProvider()
	{
		return [
			'when the record is not known' => [[
				'record' => ['id' => '123', 'type' => 'foo'],
				'knownRecords' => [],
				'expected' => false,
			]],
			'when a record with the same type but different id is known' => [[
				'record' => ['id' => '123', 'type' => 'foo'],
				'knownRecords' => ['foo' => ['456']],
				'expected' => false,
			]],
			'when a record with the same id but different type is known' => [[
				'record' => ['id' => '123', 'type' => 'foo'],
				'knownRecords' => ['bar' => ['123']],
				'expected' => false,
			]],
			'when the record is already known' => [[
				'record' => ['id' => '123', 'type' => 'foo'],
				'knownRecords' => ['foo' => ['123']],
				'expected' => true,
			]],
		];
	}

	/**
	 * @dataProvider isKnownProvider
	 */
	public function testIsKnown($data)
	{
		$output = $this->callPrivate(new IncludeHelper, 'isKnown', [$data['record'], $data['knownRecords']]);
		$this->assertSame($data['expected'], $output);
	}

	public function addKnownRecordProvider()
	{
		return [
			'when knownRecords is empty' => [[
				'knownRecords' => [],
				'data' => ['id' => '123', 'type' => 'foo'],
				'expected' => ['foo' => ['123']],
			]],
			'when there are knownRecords of a different type' => [[
				'knownRecords' => ['bar' => ['456']],
				'data' => ['id' => '123', 'type' => 'foo'],
				'expected' => ['bar' => ['456'], 'foo' => ['123']],
			]],
			'when there are knownRecords of the same type' => [[
				'knownRecords' => ['foo' => ['456']],
				'data' => ['id' => '123', 'type' => 'foo'],
				'expected' => ['foo' => ['456', '123']],
			]],
		];
	}

	/**
	 * @dataProvider addKnownRecordProvider
	 */
	public function testAddKnownRecord($data)
	{
		$output = $this->callPrivate(new IncludeHelper, 'addKnownRecord', [$data['knownRecords'], $data['data']]);
		$this->assertSame($data['expected'], $output);
	}

	public function testFilterParams()
	{
		$keys = [
			'articles.issue',
			'articles.issue.magazine',
			'tags.parent',
		];
		$type = 'articles';
		$output = IncludeHelper::filterParams($keys, $type);
		$this->assertSame([
			['issue', 'issue.magazine'],
			['tags.parent'],
		], $output);
	}
}
