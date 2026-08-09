<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
	public function up() : void
	{
		Schema::create('songs', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('content')->nullable();
			$table->timestamps();
		});
	}

	public function down() : void
	{
		Schema::dropIfExists('songs');
	}
}
