<?php

use Illuminate\Support\Facades\Route;
use Jlbelanger\Tapioca\Exceptions\NotFoundException;

Route::group(['middleware' => ['api']], function () {
	Route::apiResources([
		'albums' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\AlbumController',
		'articles' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\ArticleController',
		'artists' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\ArtistController',
		'songs' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\SongController',
		'tags' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\TagController',
	]);
});

Route::fallback(function () {
	throw NotFoundException::generate();
});
