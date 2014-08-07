<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentryAuthTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('migrate', array('--package' => 'cartalyst/sentry'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        (new MigrationCartalystSentryInstallUsersGroupsPivot)->down();
        (new MigrationCartalystSentryInstallGroups)->down();
        (new MigrationCartalystSentryInstallThrottle)->down();
        (new MigrationCartalystSentryInstallUsers)->down();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        */
    }

}