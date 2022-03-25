<?php

namespace Jlbelanger\Tapioca\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Jlbelanger\Tapioca\Helpers\FilterHelper;
use Jlbelanger\Tapioca\Helpers\Input\DataHelper;
use Jlbelanger\Tapioca\Helpers\Input\FieldsHelper;
use Jlbelanger\Tapioca\Helpers\Input\FileHelper;
use Jlbelanger\Tapioca\Helpers\Input\IncludedHelper;
use Jlbelanger\Tapioca\Helpers\Input\IncludeHelper;
use Jlbelanger\Tapioca\Helpers\Input\PageHelper;
use Jlbelanger\Tapioca\Helpers\Input\SortHelper;
use Jlbelanger\Tapioca\Helpers\Output\IncludeHelper as IncludeOutputHelper;
use Jlbelanger\Tapioca\Helpers\Output\PageHelper as PageOutputHelper;
use Jlbelanger\Tapioca\Helpers\Output\SortHelper as SortOutputHelper;

class JsonApiRequest
{
	protected $type;
	protected $model;
	protected $records;
	protected $response;

	protected $data;
	protected $fields;
	protected $filter;
	protected $include;
	protected $included;
	protected $files;
	protected $page;
	protected $sort;

	/**
	 * @param  string                                                                    $type
	 * @param  Request                                                                   $request
	 * @param  Model                                                                     $model
	 * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $records
	 * @return void
	 */
	public function __construct(string $type, Request $request, Model $model, $records)
	{
		$this->type = $type;
		$this->model = $model;
		$this->records = $records;

		// Normalize/validate query parameters.
		$this->fields = FieldsHelper::normalize($request->input('fields'));
		$this->filter = FilterHelper::normalize($request->input('filter'), $model->defaultFilter());
		$this->include = IncludeHelper::normalize($request->input('include'));
		$this->page = PageHelper::normalize($request->input('page'));
		$this->sort = SortHelper::normalize($request->input('sort'), $model->defaultSort());

		// Normalize/validate body.
		if (strpos($request->header('Content-Type'), 'multipart/form-data') === 0) {
			$body = json_decode($request->input('json'), true);
			$data = !empty($body['data']) ? $body['data'] : null;
			$included = !empty($body['included']) ? $body['included'] : null;
			$files = json_decode($request->input('files'), true);
		} else {
			$data = $request->input('data');
			$included = $request->input('included');
			$files = [];
		}
		$this->data = DataHelper::normalize($data, $model->whitelistedAttributes(), $model->whitelistedRelationships());
		$this->included = IncludedHelper::normalize($included);
		$this->files = FileHelper::normalize($files, $request);

		// Create the response.
		$this->response = new JsonApiResponse($this);
	}

	/**
	 * @return array
	 */
	public function output() : array
	{
		if (!$this->isSingular()) {
			$this->prepareInclude();
			$this->performFilter();
			$this->performSort();
			$this->performPage();
			$this->records = $this->records->get();
		}

		$this->response->prepare();

		return $this->response->output();
	}

	/**
	 * @return void
	 */
	protected function prepareInclude() : void
	{
		if (empty($this->include)) {
			return;
		}
		$this->records = IncludeOutputHelper::prepare($this->model, $this->records, $this->include);
	}

	/**
	 * @return void
	 */
	protected function performFilter() : void
	{
		if (empty($this->filter)) {
			return;
		}
		$this->records = FilterHelper::perform($this->records, $this->filter);
	}

	/**
	 * @return void
	 */
	protected function performSort() : void
	{
		if (empty($this->sort)) {
			return;
		}
		$this->records = SortOutputHelper::perform($this->records, $this->sort, $this->model);
	}

	/**
	 * @return void
	 */
	protected function performPage() : void
	{
		if (empty($this->page)) {
			return;
		}
		list($this->records, $page) = PageOutputHelper::perform($this->records, $this->page);
		$this->response->setMeta(['page' => $page]);
	}

	/**
	 * @return array
	 */
	public function getData() : array
	{
		return $this->data;
	}

	/**
	 * @return array
	 */
	public function getFields() : array
	{
		return $this->fields;
	}

	/**
	 * @return array
	 */
	public function getFiles() : array
	{
		return $this->files;
	}

	/**
	 * @return array
	 */
	public function getInclude() : array
	{
		return $this->include;
	}

	/**
	 * @return array
	 */
	public function getIncluded() : array
	{
		return $this->included;
	}

	/**
	 * @return array|\Illuminate\Database\Eloquent\Builder
	 */
	public function getRecords()
	{
		return $this->records;
	}

	/**
	 * @return boolean
	 */
	public function isSingular() : bool
	{
		return in_array($this->type, ['show', 'store', 'update']);
	}

	/**
	 * @param  Model $records
	 * @return void
	 */
	public function setRecords(Model $records) : void
	{
		$this->records = $records;
	}
}
