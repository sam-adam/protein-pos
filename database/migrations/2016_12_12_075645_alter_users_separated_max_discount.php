<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersSeparatedMaxDiscount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['maximum_discount', 'maximum_discount_type']);
            $table->unsignedInteger('max_price_discount')
                ->nullable();
            $table->tinyInteger('max_percentage_discount')
                ->nullable();

            $table->index(['max_price_discount']);
            $table->index(['max_percentage_discount']);
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
            $table->dropColumn(['max_price_discount', 'max_percentage_discount']);

            $table->unsignedInteger('maximum_discount')
                ->nullable();
            $table->enum('maximum_discount_type', ['percent', 'price'])
                ->nullable();
        });
    }
}
