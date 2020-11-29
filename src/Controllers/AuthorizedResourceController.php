<?php

namespace Jlbelanger\LaravelJsonApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jlbelanger\LaravelJsonApi\Controllers\ResourceController;
use Jlbelanger\LaravelJsonApi\Exceptions\NotFoundException;

class AuthorizedResourceController extends ResourceController
{
	/**
	 * Displays a listing of the resource.
	 *
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request) : JsonResponse
	{
		$records = $this->model();
		$user = Auth::guard('sanctum')->user();
		if (!$user || !$user->can('viewAny', $records)) {
			throw NotFoundException::generate();
		}

		return parent::index($request);
	}

	/**
	 * Stores a newly created resource in storage.
	 *
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function store(Request $request) : JsonResponse
	{
		$record = $this->model();
		$user = Auth::guard('sanctum')->user();
		if (!$user || !$user->can('create', $record)) {
			throw NotFoundException::generate();
		}

		return parent::store($request);
	}

	/**
	 * Displays the specified resource.
	 *
	 * @param  Request $request
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function show(Request $request, string $id) : JsonResponse
	{
		$record = $this->model()->find($id);
		$user = Auth::guard('sanctum')->user();
		if (!$user || !$record || !$user->can('view', $record)) {
			throw NotFoundException::generate();
		}

		return parent::show($request, $id);
	}

	/**
	 * Updates the specified resource in storage.
	 *
	 * @param  Request $request
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function update(Request $request, string $id) : JsonResponse
	{
		$record = $this->model()->find($id);
		$user = Auth::guard('sanctum')->user();
		if (!$user || !$record || !$user->can('update', $record)) {
			throw NotFoundException::generate();
		}

		return parent::update($request, $id);
	}

	/**
	 * Removes the specified resource from storage.
	 *
	 * @param  string $id
	 * @return JsonResponse
	 */
	public function destroy(string $id) : JsonResponse
	{
		$record = $this->model()->find($id);
		$user = Auth::guard('sanctum')->user();
		if (!$user || !$record || !$user->can('delete', $record)) {
			throw NotFoundException::generate();
		}

		return parent::destroy($id);
	}
}
