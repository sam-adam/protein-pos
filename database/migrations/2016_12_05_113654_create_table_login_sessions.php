<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLoginSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('branch_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('logged_in_at');
            $table->dateTime('logged_out_at')
                ->nullable();
            $table->timestamps();

            $table->index(['branch_id']);
            $table->index(['user_id']);
            $table->index(['logged_in_at']);
            $table->index(['logged_out_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('login_sessions');
    }
}
