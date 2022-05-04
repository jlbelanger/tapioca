<?php

namespace Jlbelanger\Tapioca\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;
use Jlbelanger\Tapioca\Exceptions\NotFoundException;
use Jlbelanger\Tapioca\Helpers\Utilities;

class AuthorizeMiddleware
{
	/**
	 * Handles incoming requests.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 */
	// phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
	public function handle(Request $request, Closure $next)
	{
		$method = $request->method();
		$i = 1;
		$path = $request->segment($i);
		if ($path === 'api') {
			$i = 2;
			$path = $request->segment($i);
		}
		$id = $request->segment($i + 1);
		$className = Utilities::getClassNameFromType($path);
		$model = new $className;
		$action = null;

		$user = Auth::guard('sanctum')->user();
		if (!$user) {
			throw NotFoundException::generate();
		}

		if ($id) {
			$record = $model->find($id);
			if (!$record) {
				throw NotFoundException::generate();
			}

			if ($method === 'GET') {
				$action = 'view';
			} elseif ($method === 'PUT') {
				$action = 'update';
			} elseif ($method === 'DELETE') {
				$action = 'delete';
			}

			if (!$user->can('view', $record)) {
				throw NotFoundException::generate();
			}

			if ($action !== 'view' && !$user->can($action, $record)) {
				throw JsonApiException::generate([
					'title' => 'You do not have permission to ' . $action . ' this record.',
				], 403);
			}
		} else {
			if ($method === 'GET') {
				$action = 'viewAny';
			} elseif ($method === 'POST') {
				$action = 'create';
			}

			if (!$user->can($action, $model)) {
				throw NotFoundException::generate();
			}
		}

		return $next($request);
	}
}
