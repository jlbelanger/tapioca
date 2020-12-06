<?php

namespace Jlbelanger\LaravelJsonApi\Controllers;

use Jlbelanger\LaravelJsonApi\Controllers\ResourceController;
use Jlbelanger\LaravelJsonApi\Middleware\AuthorizeMiddleware;

class AuthorizedResourceController extends ResourceController
{
	/**
	 * Creates a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware(AuthorizeMiddleware::class)->only(['index', 'store', 'show', 'update', 'destroy']);
		parent::__construct();
	}
}
