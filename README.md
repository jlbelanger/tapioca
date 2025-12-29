# Tapioca

Tapioca is a Composer package that allows you to use [JSON:API](https://jsonapi.org/) with [Laravel](https://laravel.com/).

There are other Laravel packages that already do the same thing, and they probably do it much better. I felt like the other packages had too much boilerplate though, and I wanted to re-use my Laravel models as resources. I also wanted to add some features that aren't technically JSON:API, like creating/updating a regular record and related/included records at the same time. And programming's kinda my thing, so I thought writing this could be fun. (Spoiler alert: it wasn't. Okay, maybe just a little.)

## Features

- CRUD operations: Create/read/update/delete
- Sparse fieldsets: `?fields[articles]=title`
- Filter (with operation) `?filter[slug][eq]=foo` `?filter[slug][like]=%foo%` `?filter[slug][in]=foo,bar`
- Include relationships: `?include=user` `?include=user.roles`
- Pagination: `?page[size]=10&page[number]=1`
- Sort: `?sort=-created_at,user.username`

## Requirements

- PHP 8.4+
- [Laravel](https://laravel.com/) 10+

## Install

**Warning: This package is still a work-in-progress. Use at your own risk.**

Run:

``` bash
composer config repositories.tapioca vcs git@github.com:jlbelanger/tapioca.git
composer require jlbelanger/tapioca @dev
php artisan vendor:publish --provider="Jlbelanger\Tapioca\TapiocaServiceProvider" --tag="config"
```

## Setup

Create or update `app/Http/Middleware/Authenticate.php`:

``` php
<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Jlbelanger\Tapioca\Exceptions\JsonApiException;

class Authenticate extends Middleware
{
	/**
	 * Handles an unauthenticated user.
	 *
	 * @param  Request $request
	 * @param  array   $guards
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed, Squiz.Commenting.FunctionComment.TypeHintMissing
	protected function unauthenticated($request, array $guards)
	{
		throw JsonApiException::generate([['title' => 'You are not logged in.', 'status' => '401']], 401);
	}
}
```

### Setup: Laravel 11 and later

Add the following to `bootstrap/app.php`:

``` php
	->withMiddleware(function (Middleware $middleware) {
		$middleware->alias([
			'auth' => \App\Http\Middleware\Authenticate::class,
		]);
	})
	->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions) {
		$exceptions->dontReport([
			\Jlbelanger\Tapioca\Exceptions\JsonApiException::class,
		]);

		$exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
			return response()->json(['errors' => [['title' => 'URL does not exist.', 'status' => '404', 'detail' => 'Method not allowed.']]], 404);
		});

		$exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
			return response()->json(['errors' => [['title' => $e->getMessage() ? $e->getMessage() : 'URL does not exist.', 'status' => '404']]], 404);
		});

		$exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
			return response()->json(['errors' => [['title' => 'Please wait before retrying.', 'status' => '429']]], 429);
		});

		$exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
			$output = [];
			$errors = $e->validator->errors()->toArray();
			foreach ($errors as $pointer => $titles) {
				foreach ($titles as $title) {
					$output[] = [
						'title' => $title,
						'source' => [
							'pointer' => '/' . str_replace('.', '/', $pointer),
						],
						'status' => '422',
					];
				}
			}
			return response()->json(['errors' => $output], 422);
		});

		$exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
			return response()->json(['errors' => [['title' => $e->getMessage(), 'status' => (string) $e->getStatusCode()]]], $e->getStatusCode());
		});

		$exceptions->render(function (\Jlbelanger\Tapioca\Exceptions\JsonApiException $e) {
			return response()->json(['errors' => $e->getErrors()], $e->getCode());
		});

		$exceptions->render(function (Throwable $e) {
			$error = ['title' => 'There was an error connecting to the server.', 'status' => '500'];
			if (config('app.debug')) {
				$error['detail'] = $e->getMessage();
				$error['meta'] = [
					'exception' => get_class($e),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				];
				if (config('app.env') !== 'testing') {
					$error['meta']['trace'] = $e->getTrace();
				}
			}
			return response()->json(['errors' => [$error]], 500);
		});
	});
```

### Setup: Laravel 10 and earlier

Add the following to the `dontReport` property in `app/Exceptions/Handler.php`:

``` php
protected $dontReport = [
	\Jlbelanger\Tapioca\Exceptions\JsonApiException::class,
];
```

Add the following to the `register` function in the same file (`app/Exceptions/Handler.php`):

``` php
public function register() : void
{
	$this->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
		return response()->json(['errors' => [['title' => 'URL does not exist.', 'status' => '404', 'detail' => 'Method not allowed.']]], 404);
	});

	$this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
		return response()->json(['errors' => [['title' => $e->getMessage() ? $e->getMessage() : 'URL does not exist.', 'status' => '404']]], 404);
	});

	$this->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
		return response()->json(['errors' => [['title' => 'Please wait before retrying.', 'status' => '429']]], 429);
	});

	$this->renderable(function (\Illuminate\Validation\ValidationException $e) {
		$output = [];
		$errors = $e->validator->errors()->toArray();
		foreach ($errors as $pointer => $titles) {
			foreach ($titles as $title) {
				$output[] = [
					'title' => $title,
					'source' => [
						'pointer' => '/' . str_replace('.', '/', $pointer),
					],
					'status' => '422',
				];
			}
		}
		return response()->json(['errors' => $output], 422);
	});

	$this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
		return response()->json(['errors' => [['title' => $e->getMessage(), 'status' => (string) $e->getStatusCode()]]], $e->getStatusCode());
	});

	$this->renderable(function (\Jlbelanger\Tapioca\Exceptions\JsonApiException $e) {
		return response()->json(['errors' => $e->getErrors()], $e->getCode());
	});

	$this->renderable(function (\Throwable $e) {
		$error = ['title' => 'There was an error connecting to the server.', 'status' => '500'];
		if (config('app.debug')) {
			$error['detail'] = $e->getMessage();
			$error['meta'] = [
				'exception' => get_class($e),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			];
			if (config('app.env') !== 'testing') {
				$error['meta']['trace'] = $e->getTrace();
			}
		}
		return response()->json(['errors' => [$error]], 500);
	});
}
```

## Creating resources

You can create resources automatically or manually.

### Option A: Automatically

To automatically generate a controller, model, and route, run the following command (replacing "User" with the name of the resource):

``` bash
php artisan make:tapioca User
```

### Option B: Manually

The controller must extend `ResourceController` (or `AuthorizedResourceController` if you are using Sanctum):

``` php
<?php

namespace App\Http\Controllers\Api;

use Jlbelanger\Tapioca\Controllers\ResourceController;

class UserController extends ResourceController
{
	// Additional routes can be defined here. Otherwise, the controller should be empty.
}
```

The model must include the `Resource` trait:

``` php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jlbelanger\Tapioca\Traits\Resource;

class User extends Model
{
	use Resource;
}
```

The route must be defined in `routes/api.php` (optionally including `'auth'` or `'auth:sanctum'` or similar in the middleware):

``` php
<?php

use Illuminate\Support\Facades\Route;

Route::apiResource('users', \App\Http\Controllers\Api\UserController::class)->middleware(['api']);
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
GET /articles?filter[slug][notlike]=foo%
GET /articles?filter[slug][notlike]=%foo
GET /articles?filter[slug][notlike]=%foo%
GET /articles?filter[slug][in]=foo,bar
GET /articles?filter[slug][notin]=foo,bar
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

## Examples

- [Corrieography API](https://github.com/jlbelanger/corrie)
- [Food Tracker API](https://github.com/jlbelanger/food)
- [Glick API](https://github.com/jlbelanger/glick)
- [Jenny's Wardrobe API](https://github.com/jlbelanger/wardrobe)

## Development

### Requirements

- [Composer](https://getcomposer.org/)
- [Git](https://git-scm.com/)
- Web server with PHP

### Setup

``` bash
git clone https://github.com/jlbelanger/tapioca.git
cd tapioca
composer install
```

### Lint

``` bash
./vendor/bin/phpcs
```

### Test

``` bash
./vendor/bin/phpunit
```

## Notes

### Multipart PUT requests

PHP < 8.4 and Laravel < 11.40 [do not support multipart PUT requests](https://bugs.php.net/bug.php?id=55815).

As a workaround, you can install the [apfd PECL extension.](https://pecl.php.net/package/apfd).

To install the extension on Ubuntu (replace 8.4 with your PHP version):

``` bash
apt-get install php-pear
apt-get install php8.4-dev
pecl install apfd
echo "extension=apfd.so" >> /etc/php/8.4/fpm/php.ini
```

Then restart PHP (eg. `service php8.4-fpm restart`)
