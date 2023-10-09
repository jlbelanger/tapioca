<?php

namespace Jlbelanger\Tapioca\Controllers;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jlbelanger\Tapioca\Exceptions\ValidationException;
use Jlbelanger\Tapioca\Helpers\JsonApiRequest;
use Jlbelanger\Tapioca\Helpers\ProcessHelper;
use Jlbelanger\Tapioca\Middleware\BodyValidationMiddleware;
use Jlbelanger\Tapioca\Middleware\ContentTypeMiddleware;

class ResourceController extends Controller
{
	/**
	 * Creates a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware(ContentTypeMiddleware::class);
		$this->middleware(BodyValidationMiddleware::class)->only(['store', 'update']);
	}

	/**
	 * Displays a listing of the resource.
	 *
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request) : JsonResponse
	{
		$records = $this->model()->newQuery();
		$req = new JsonApiRequest('index', $request, $this->model(), $records);
		return response()->json($req->output());
	}

	/**
	 * Stores a newly created resource in storage.
	 *
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function store(Request $request) : JsonResponse
	{
		$record = $this->model($request);

		// Validate the record.
		$req = new JsonApiRequest('store', $request, $this->model($request), $record);
		$errors = $record->validate($req->getData(), $request->method());
		if ($errors) {
			throw ValidationException::generate($errors);
		}

		// Store the record.
		$record = ProcessHelper::create($record, $req);
		$record = $record->fresh();
		$req->setRecords($record);

		return response()->json($req->output(), 201);
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
		if (!$record) {
			abort(404, 'This record does not exist.');
		}
		$req = new JsonApiRequest('show', $request, $this->model(), $record);
		return response()->json($req->output());
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
		// Fetch the record.
		$record = $this->model()->find($id);
		if (!$record) {
			abort(404, 'This record does not exist.');
		}

		// Validate the record.
		$req = new JsonApiRequest('update', $request, $this->model(), $record);
		$errors = $record->validate($req->getData(), $request->method());
		if ($errors) {
			throw ValidationException::generate($errors);
		}

		// Update the record.
		ProcessHelper::update($record, $req);

		return response()->json($req->output());
	}

	/**
	 * Removes the specified resource from storage.
	 *
	 * @param  string $id
	 * @return JsonResponse
	 */
	public function destroy(string $id) : JsonResponse
	{
		// Fetch the record.
		$record = $this->model()->find($id);
		if (!$record) {
			abort(404, 'This record does not exist.');
		}

		// Destroy the record.
		DB::beginTransaction();
		$record->delete();
		DB::commit();

		return response()->json(null, 204);
	}

	/**
	 * @param  Request $request
	 * @return Model
	 */
	protected function model(Request $request = null) : Model
	{
		$className = get_class($this);
		$className = explode('\\', $className);
		$className = array_pop($className);
		$className = preg_replace('/Controller$/', '', $className);
		$className = config('tapioca.models_path', 'App\\Models\\') . $className;
		return new $className;
	}
}
