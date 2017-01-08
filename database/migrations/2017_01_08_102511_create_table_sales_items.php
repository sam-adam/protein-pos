<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSalesItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_items', function (Blueprint $table) {
            $table->unsignedInteger('sales_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('branch_inventory_id')
                ->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price');
            $table->unsignedInteger('original_price');
            $table->unsignedInteger('discount')
                ->default(0);
            $table->unsignedInteger('subtotal');

            $table->index(['sales_id']);
            $table->index(['product_id']);
            $table->index(['branch_inventory_id']);
            $table->index(['quantity']);
            $table->index(['price']);
            $table->index(['original_price']);
            $table->index(['discount']);
            $table->index(['subtotal']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sales_items');
    }
}
