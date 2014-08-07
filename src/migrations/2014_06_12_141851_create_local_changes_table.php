<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocalChangesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('local_changes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('resource_type');
			$table->integer('resource_id');
			$table->string('field');

			$table->timestamps();

			$table->index('resource_type');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('local_changes');
	}

}
