<?php

use Illuminate\Database\Schema\Blueprint;
use ViKon\Auth\Database\Migration\Migration;

/**
 * Class CreateLogsTable
 *
 * @author Kovács Vince<vincekovacs@hotmail.com>
 */
class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        static::$schema->create('logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->text('message');
            $table->text('context');
            $table->string('level');
            $table->string('channel');
            $table->dateTime('created_at');
            $table->text('extra');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        static::$schema->drop('logs');
    }
}
