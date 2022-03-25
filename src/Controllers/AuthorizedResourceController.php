<?php

namespace Jlbelanger\Tapioca\Controllers;

use Jlbelanger\Tapioca\Controllers\ResourceController;
use Jlbelanger\Tapioca\Middleware\AuthorizeMiddleware;

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
