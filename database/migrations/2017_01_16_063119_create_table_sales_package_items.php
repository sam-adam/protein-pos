<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSalesPackageItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_package_items', function (Blueprint $table) {
            $table->unsignedInteger('sale_package_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('branch_inventory_id')
                ->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('original_price');

            $table->index(['sale_package_id']);
            $table->index(['product_id']);
            $table->index(['branch_inventory_id']);
            $table->index(['quantity']);
            $table->index(['original_price']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sale_package_items');
    }
}
