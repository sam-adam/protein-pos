<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersAddCanGiveDiscount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_give_discount')
                ->default(true);
            $table->boolean('can_give_unlimited_discount')
                ->default(false);

            $table->index(['can_give_discount']);
            $table->index(['can_give_unlimited_discount']);
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
            $table->dropColumn(['can_give_discount', 'can_give_unlimited_discount']);
        });
    }
}
