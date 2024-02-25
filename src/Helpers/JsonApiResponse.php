<?php

namespace Jlbelanger\Tapioca\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Jlbelanger\Tapioca\Helpers\JsonApiRequest;
use Jlbelanger\Tapioca\Helpers\Output\IncludeHelper;

class JsonApiResponse
{
	protected $request;

	protected $data = [];
	protected $included = [];
	protected $meta = [];

	/**
	 * @param  JsonApiRequest $request
	 * @return void
	 */
	public function __construct(JsonApiRequest $request)
	{
		$this->request = $request;
	}

	/**
	 * @return void
	 */
	public function prepare() : void
	{
		// Convert singular records to an array so we don't have to deal with both cases.
		$records = $this->request->getRecords();

		$this->data = $this->fetch($records);
		$this->included = IncludeHelper::perform($records, $this->data, $this->request->getInclude(), $this->request->getFields());

		// Convert singular records back.
		if ($this->request->isSingular()) {
			$this->data = $this->data[0];
		}
	}

	/**
	 * @return array
	 */
	public function output() : array
	{
		$output = [];
		if ($this->data) {
			$output['data'] = $this->data;
		}
		if ($this->included) {
			$output['included'] = $this->included;
		}
		if ($this->meta) {
			$output['meta'] = $this->meta;
		}
		return $output;
	}

	/**
	 * @param  array $meta
	 * @return void
	 */
	public function setMeta(array $meta) : void
	{
		$this->meta = array_merge($this->meta, $meta);
	}

	/**
	 * @param  Builder|Collection $records
	 * @return array
	 */
	protected function fetch(Builder|Collection $records) : array
	{
		$output = [];
		foreach ($records as $record) {
			$recordData = $record->data($this->request->getInclude(), $this->request->getFields());
			$output[] = $recordData;
		}
		return $output;
	}
}
