# Laravel JSON API

This packages allows you to use [JSON:API](https://jsonapi.org/) with [Laravel](https://laravel.com/).

There are other Laravel packages that already do the same thing, and they probably do it much better. I felt like the other packages had too much boilerplate though, and I wanted to re-use my Laravel models as resources. I also wanted to add some features that aren't technically JSON:API, like creating/updating a regular record and related/included records at the same time. And programming's kinda my thing, so I thought writing this could be fun. (Spoiler alert: it wasn't. Okay, maybe just a little.)

## Install

Add to `composer.json`:

``` js
	"repositories": [
		{
			"type": "vcs",
			"url": "git@github.com:jlbelanger/laravel-json-api.git"
		}
	],
```

Run:

``` bash
composer require jlbelanger/laravel-json-api @dev
php artisan vendor:publish --provider="Jlbelanger\LaravelJsonApi\LaravelJsonApiServiceProvider" --tag="config"
```

## Setup

Each resource needs a controller. The controller needs to extend `ResourceController` (or `AuthorizedResourceController` if you are using Sanctum):

``` php
<?php

namespace App\Http\Controllers;

use Jlbelanger\LaravelJsonApi\Controllers\ResourceController;

class UserController extends ResourceController
{
}
```

Link the routes to the controllers in `routes/api.php`:

``` php
<?php

use Illuminate\Support\Facades\Route;
use Jlbelanger\LaravelJsonApi\Exceptions\NotFoundException;

Route::group(['middleware' => ['api']], function () {
	Route::apiResources([
		'users' => '\App\Http\Controllers\UserController',
	]);
});

Route::fallback(function () {
	throw NotFoundException::generate();
});
```

Add the `Resource` trait to each model:

``` php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

class User extends Model
{
	use Resource;
}
```

Add the following to the `dontReport` property in `app/Exceptions/Handler.php`:

``` php
protected $dontReport = [
	Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException::class,
];
```

Add the following to the `register` function in the same file (`app/Exceptions/Handler.php`):

``` php
public function register()
{
	$this->renderable(function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
		return response()->json(['errors' => [['title' => 'URL does not exist.', 'status' => '404', 'detail' => 'Method not allowed.']]], 404);
	});

	$this->renderable(function (Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException $e) {
		return response()->json(['errors' => $e->getErrors()], $e->getCode());
	});

	$this->renderable(function (Symfony\Component\HttpKernel\Exception\HttpException $e) {
		return response()->json(['errors' => [['title' => $e->getMessage(), 'status' => $e->getStatusCode()]]], $e->getStatusCode());
	});
}
```

## Usage

### Fields

```
GET /articles?fields[articles]=title
GET /articles/1?fields[articles]=title
GET /articles/1?include=user&fields[articles]=title&fields[users]=username
```

### Filter

```
GET /articles?filter[slug][eq]=foo
GET /articles?filter[slug][ne]=foo
GET /articles?filter[slug][like]=foo%
GET /articles?filter[slug][like]=%foo
GET /articles?filter[slug][like]=%foo%
GET /articles?filter[slug][in]=foo,bar
GET /articles?filter[slug][null]=1
GET /articles?filter[slug][notnull]=1
GET /articles?filter[word_count][gt]=50
GET /articles?filter[word_count][lt]=50
GET /articles?filter[word_count][ge]=50
GET /articles?filter[word_count][le]=50
GET /articles?filter[user.id][eq]=1
```

### Include

```
GET /articles?include=user
GET /articles?include=tags,user
GET /articles/1?include=user
```

### Pagination

```
GET /users?page[size]=10
GET /users?page[size]=10&page[number]=1
```

### Sort

```
GET /users?sort=username
GET /users?sort=-username
GET /users?sort=username,email
GET /articles?sort=user.username
```

## Development

Lint:

``` bash
./vendor/bin/phpcs
```

Test:

``` bash
./vendor/bin/phpunit
```
