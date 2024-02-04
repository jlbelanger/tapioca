<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['api']], function () {
	Route::apiResources([
		'albums' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\AlbumController',
		'articles' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\ArticleController',
		'artists' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\ArtistController',
		'notes' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\NoteController',
		'songs' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\SongController',
		'tags' => '\Jlbelanger\Tapioca\Tests\Dummy\App\Controllers\TagController',
	]);
});
