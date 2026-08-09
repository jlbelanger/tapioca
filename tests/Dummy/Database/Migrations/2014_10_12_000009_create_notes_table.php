<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::create('notes', function (Blueprint $table) {
			$table->id('nid');
			$table->morphs('record');
			$table->text('content')->nullable();
			$table->timestamps();
		});
	}

	public function down() : void
	{
		Schema::dropIfExists('notes');
	}
};
