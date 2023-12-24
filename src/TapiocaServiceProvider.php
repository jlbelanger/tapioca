<?php

namespace Jlbelanger\Tapioca;

use Illuminate\Support\ServiceProvider;
use Jlbelanger\Tapioca\Console\Generate;

class TapiocaServiceProvider extends ServiceProvider
{
	/**
	 * @return void
	 */
	public function register() : void
	{
		$this->mergeConfigFrom(__DIR__ . '/../tests/Dummy/Config/config.php', 'tapioca');
	}

	/**
	 * @return void
	 */
	public function boot() : void
	{
		if (!empty($_ENV['LARAVEL_JSON_API_TEST'])) {
			$this->loadRoutesFrom(__DIR__ . '/../tests/Dummy/Routes/api.php');
		}

		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../config/config.php' => config_path('tapioca.php'),
			], 'config');

			$this->commands([Generate::class]);
		}
	}
}
