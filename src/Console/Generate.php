<?php

namespace Jlbelanger\Tapioca\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class Generate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'make:tapioca {name}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new Tapioca resource';

	/**
	 * Executes the console command.
	 *
	 * @return integer
	 */
	public function handle()
	{
		$name = $this->argument('name');
		$routeName = Str::kebab(Str::plural($name));

		$this->createModel($name);
		$this->createController($name);
		$this->addRoute($routeName, $name);

		$this->info('Done: ' . env('APP_URL') . '/api/' . $routeName);

		return 0;
	}

	/**
	 * @param  string $name
	 * @return void
	 */
	protected function createModel(string $name) : void
	{
		$this->info('Creating model...');
		Artisan::call('make:model ' . $name);
		$path = $this->laravel->basePath() . '/app/Models/' . $name . '.php';
		$this->replaceInFile($path, '/( |\t)use /', '$1use \Jlbelanger\Tapioca\Traits\Resource, ');
	}

	/**
	 * @param  string $name
	 * @return void
	 */
	protected function createController(string $name) : void
	{
		$this->info('Creating controller...');
		Artisan::call('make:controller ' . $name . ' --type=plain');
		$path = $this->laravel->basePath() . '/app/Http/Controllers/' . $name . '.php';
		$this->replaceInFile($path, ' extends Controller', 'Controller extends \Jlbelanger\Tapioca\Controllers\ResourceController');
		$this->replaceInFile($path, '\Controllers;', '\Controllers\Api;');
		rename($path, $this->laravel->basePath() . '/app/Http/Controllers/Api/' . $name . 'Controller.php');
	}

	/**
	 * @param  string $routeName
	 * @param  string $name
	 * @return void
	 */
	protected function addRoute(string $routeName, string $name) : void
	{
		$this->info('Adding route...');
		$line = "Route::apiResource('" . $routeName . "', '\App\Http\Controllers\Api\\" . $name . "Controller')->middleware(['api']);";
		$path = $this->laravel->basePath() . '/routes/api.php';
		$this->appendLineToFile($path, $line);
	}

	/**
	 * @param  string $path
	 * @param  string $line
	 * @return void
	 */
	protected function appendLineToFile(string $path, string $line) : void
	{
		$contents = file_get_contents($path);
		if (strpos($contents, $line) !== false) {
			return;
		}
		$contents .= "\n" . $line . "\n";
		file_put_contents($path, $contents);
	}

	/**
	 * @param  string $path
	 * @param  string $find
	 * @param  string $replace
	 * @return void
	 */
	protected function replaceInFile(string $path, string $find, string $replace) : void
	{
		$contents = file_get_contents($path);
		if (substr($find, 0, 1) === '/') {
			if (preg_match($find, $contents) === false) {
				return;
			}
			$contents = preg_replace($find, $replace, $contents);
		} else {
			if (strpos($contents, $find) === false) {
				return;
			}
			$contents = str_replace($find, $replace, $contents);
		}
		file_put_contents($path, $contents);
	}
}
