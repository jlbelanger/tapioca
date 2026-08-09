<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistsTable extends Migration
{
	public function up() : void
	{
		Schema::create('artists', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('filename')->nullable();
			$table->timestamps();
		});
	}

	public function down() : void
	{
		Schema::dropIfExists('artists');
	}
}
