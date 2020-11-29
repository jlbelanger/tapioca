<?php

namespace Jlbelanger\LaravelJsonApi\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentTypeMiddleware
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
		$response = $next($request);
		$response->header('Content-Type', 'application/vnd.api+json');
		return $response;
	}
}
