<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbumSongTable extends Migration
{
	/**
	 * Runs the migrations.
	 *
	 * @return void
	 */
	public function up() : void
	{
		Schema::create('album_song', function (Blueprint $table) {
			$table->id('asid');
			$table->unsignedInteger('album_id');
			$table->unsignedInteger('song_id');
			$table->integer('track');
			$table->string('length')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverses the migrations.
	 *
	 * @return void
	 */
	public function down() : void
	{
		Schema::dropIfExists('album_song');
	}
}
