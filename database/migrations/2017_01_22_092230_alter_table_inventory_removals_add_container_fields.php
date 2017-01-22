<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoryRemovalsAddContainerFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_removals', function (Blueprint $table) {
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_item_id')
                ->nullable();
            $table->unsignedInteger('product_item_quantity')
                ->default(0);
            $table->unsignedInteger('product_pre_adjusted_stock')
                ->default(0);
            $table->unsignedInteger('product_item_pre_adjusted_stock')
                ->nullable();

            $table->index(['product_id']);
            $table->index(['product_item_id']);
            $table->index(['product_item_quantity']);
            $table->index(['product_pre_adjusted_stock']);
            $table->index(['product_item_pre_adjusted_stock']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_removals', function (Blueprint $table) {
            $table->dropColumn('product_id');
            $table->dropColumn('product_item_id');
            $table->dropColumn('product_item_quantity');
            $table->dropColumn('product_pre_adjusted_stock');
            $table->dropColumn('product_item_pre_adjusted_stock');
        });
    }
}
