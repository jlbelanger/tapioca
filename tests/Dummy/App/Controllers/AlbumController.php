<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers;

use Jlbelanger\LaravelJsonApi\Controllers\ResourceController;

class AlbumController extends ResourceController
{
	public function __construct()
	{
		parent::__construct();
		\App::singleton(
			\Illuminate\Contracts\Debug\ExceptionHandler::class,
			\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Exceptions\Handler::class
		);
	}
}
