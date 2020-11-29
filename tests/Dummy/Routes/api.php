<?php

use Illuminate\Support\Facades\Route;
use Jlbelanger\LaravelJsonApi\Exceptions\NotFoundException;

Route::group(['middleware' => ['api']], function () {
	Route::apiResources([
		'albums' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\AlbumController',
		'articles' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\ArticleController',
		'artists' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\ArtistController',
		'songs' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\SongController',
		'tags' => '\Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Controllers\TagController',
	]);
});

Route::fallback(function () {
	throw NotFoundException::generate();
});
