<?php

use Illuminate\Support\Facades\Route;
use Jlbelanger\LaravelJsonApi\Exceptions\NotFoundException;

Route::group(['middleware' => ['api']], function () {
	Route::apiResources([
		'albums' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\AlbumController',
		'articles' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\ArticleController',
		'artists' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\ArtistController',
		'comments' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\CommentController',
		'songs' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\SongController',
		'tags' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\TagController',
		'users' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\UserController',
	]);
});

Route::fallback(function () {
	throw NotFoundException::generate();
});
