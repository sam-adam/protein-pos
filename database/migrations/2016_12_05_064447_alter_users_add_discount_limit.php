<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersAddDiscountLimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('minimum_discount')
                ->nullable();
            $table->enum('minimum_discount_type', ['percent', 'price'])
                ->nullable();
            $table->unsignedInteger('maximum_discount')
                ->nullable();
            $table->enum('maximum_discount_type', ['percent', 'price'])
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['minimum_discount', 'maximum_discount']);
        });
    }
}
