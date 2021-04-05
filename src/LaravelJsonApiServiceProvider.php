<?php

namespace Jlbelanger\LaravelJsonApi;

use Illuminate\Support\ServiceProvider;

class LaravelJsonApiServiceProvider extends ServiceProvider
{
	/**
	 * @return void
	 */
	public function register() : void
	{
		$this->mergeConfigFrom(__DIR__ . '/../tests/Dummy/Config/config.php', 'laraveljsonapi');
	}

	/**
	 * @return void
	 */
	public function boot() : void
	{
		if (!empty($_SERVER['LARAVEL_JSON_API_TEST'])) {
			$this->loadRoutesFrom(__DIR__ . '/../tests/Dummy/Routes/api.php');
		}

		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../config/config.php' => config_path('laraveljsonapi.php'),
			], 'config');
		}
	}
}
