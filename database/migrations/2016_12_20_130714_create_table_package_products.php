<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePackageProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_products', function (Blueprint $table) {
            $table->unsignedInteger('package_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('quantity');

            $table->index(['package_id']);
            $table->index(['product_id']);
            $table->index(['quantity']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('package_products');
    }
}
