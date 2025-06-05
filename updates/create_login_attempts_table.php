<?php namespace OfTheWildfire\LoginCaptcher\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateLoginAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('ofthewildfire_logincaptcher_attempts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->nullable()->unsigned();
            $table->string('email')->nullable();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('attempt_type'); // login or password_reset
            $table->boolean('success')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('backend_users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ofthewildfire_logincaptcher_attempts');
    }
} 