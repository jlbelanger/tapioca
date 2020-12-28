<?php

namespace Jlbelanger\LaravelJsonApi\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jlbelanger\LaravelJsonApi\Exceptions\NotFoundException;
use Jlbelanger\LaravelJsonApi\Helpers\Utilities;

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
		$className = Utilities::getClassNameFromType($path);
		$model = new $className;
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
}
