<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClumsyGroupsPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clumsy_groups_pivot', function (Blueprint $table) {
            $table->integer('group_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clumsy_groups_pivot', function($table)
        {
            $table->dropForeign('clumsy_groups_pivot_group_id_foreign');
            $table->dropForeign('clumsy_groups_pivot_user_id_foreign');
        });

        Schema::drop('clumsy_groups_pivot');
    }
}
