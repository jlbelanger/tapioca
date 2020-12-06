<?php

namespace Jlbelanger\LaravelJsonApi\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jlbelanger\LaravelJsonApi\Exceptions\NotFoundException;

class AuthorizeMiddleware
{
	/**
	 * Handles incoming requests.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		$method = $request->method();
		$path = $request->segment(1);
		$id = $request->segment(2);
		$model = $this->model($path);
		$user = Auth::guard('sanctum')->user();
		$action = null;

		if ($id) {
			$record = $model->find($id);
			if ($method === 'GET') {
				$action = 'view';
			} elseif ($method === 'PUT') {
				$action = 'update';
			} elseif ($method === 'DELETE') {
				$action = 'delete';
			}

			if (!$user || !$record || !$user->can($action, $record)) {
				throw NotFoundException::generate();
			}
		} else {
			if ($method === 'GET') {
				$action = 'viewAny';
			} elseif ($method === 'POST') {
				$action = 'create';
			}

			if (!$user || !$user->can($action, $model)) {
				throw NotFoundException::generate();
			}
		}

		return $next($request);
	}

	/**
	 * @param  string $path
	 * @return Model
	 */
	protected function model(string $path) : Model
	{
		$className = config('laraveljsonapi.models_path', 'App\\Models\\') . Str::studly(Str::singular($path));
		return new $className;
	}
}
