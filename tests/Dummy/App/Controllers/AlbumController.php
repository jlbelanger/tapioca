<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Controllers;

use Jlbelanger\Tapioca\Controllers\ResourceController;

class AlbumController extends ResourceController
{
	public function __construct()
	{
		parent::__construct();
		\App::singleton(
			\Illuminate\Contracts\Debug\ExceptionHandler::class,
			\Jlbelanger\Tapioca\Tests\Dummy\App\Exceptions\Handler::class
		);
	}
}
