<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProductsSetNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand_id', 'product_category_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('brand_id')
                ->nullable();
            $table->unsignedInteger('product_category_id')
                ->nullable();

            $table->index(['brand_id']);
            $table->index(['product_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand_id', 'product_category_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('brand_id');
            $table->unsignedInteger('product_category_id');

            $table->index(['brand_id']);
            $table->index(['product_category_id']);
        });
    }
}
