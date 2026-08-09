<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbumsTable extends Migration
{
	public function up() : void
	{
		Schema::create('albums', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('release_year')->nullable();
			$table->unsignedInteger('artist_id');
			$table->timestamps();
		});
	}

	public function down() : void
	{
		Schema::dropIfExists('albums');
	}
}
