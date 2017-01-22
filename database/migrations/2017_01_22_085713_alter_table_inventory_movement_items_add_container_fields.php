<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoryMovementItemsAddContainerFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_movement_items', function (Blueprint $table) {
            $table->unsignedInteger('product_item_id')
                ->nullable();
            $table->unsignedInteger('product_item_quantity')
                ->default(0);
            $table->unsignedInteger('source_item_current_stock')
                ->nullable();
            $table->unsignedInteger('destination_item_current_stock')
                ->nullable();

            $table->index(['product_item_id']);
            $table->index(['product_item_quantity']);
            $table->index(['source_item_current_stock']);
            $table->index(['destination_item_current_stock']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_movement_items', function (Blueprint $table) {
            $table->dropColumn('product_item_id');
            $table->dropColumn('product_item_quantity');
            $table->dropColumn('source_item_current_stock');
            $table->dropColumn('destination_item_current_stock');
        });
    }
}
