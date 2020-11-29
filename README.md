# Laravel JSON API

This packages allows you to use [JSON:API](https://jsonapi.org/) with [Laravel](https://laravel.com/).

There are other Laravel packages that already do the same thing, and they probably do it much better. I felt like the other packages had too much boilerplate though, and I wanted to re-use my Laravel models as resources. I also wanted to add some features that aren't technically JSON:API, like updating a regular record and related pivot table records at the same time. And programming's kinda my thing, so I thought writing this could be fun. (Spoiler alert: it wasn't. Okay, maybe just a little.)

## Install

``` bash
composer require jlbelanger/laravel-json-api @dev
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
]);

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

Finally, add the following to the `register` function in `app/Exceptions/Handler.php`:

``` php
$this->renderable(function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
	return response()->json(['errors' => [['title' => 'URL does not exist.', 'status' => '404', 'detail' => 'Method not allowed.']]], 404);
});

$this->renderable(function (Jlbelanger\LaravelJsonApi\Exceptions\JsonApiException $e) {
	return response()->json(['errors' => $e->getErrors()], $e->getCode());
});

$this->renderable(function (Symfony\Component\HttpKernel\Exception\HttpException $e) {
	return response()->json(['errors' => [['title' => $e->getMessage(), 'status' => $e->getStatusCode()]]], $e->getStatusCode());
});
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
GET /articles?filter[slug][eq]=null
GET /articles?filter[slug][ne]=foo
GET /articles?filter[slug][ne]=null
GET /articles?filter[slug][like]=foo%
GET /articles?filter[slug][in]=foo,bar
GET /articles?filter[word_count][gt]=50
GET /articles?filter[word_count][lt]=50
GET /articles?filter[word_count][ge]=50
GET /articles?filter[word_count][le]=50
GET /articles?filter[user.id][eq]=1
```

Note: `null` is only supported for `eq` and `ne`

### Include

```
GET /articles?include=user
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
