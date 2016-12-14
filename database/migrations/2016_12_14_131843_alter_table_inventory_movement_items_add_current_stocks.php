<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoryMovementItemsAddCurrentStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_movement_items', function (Blueprint $table) {
            $table->unsignedInteger('source_current_stock')
                ->nullable();
            $table->unsignedInteger('destination_current_stock');

            $table->index(['source_current_stock']);
            $table->index(['destination_current_stock']);
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
            $table->dropColumn(['source_current_stock', 'destination_current_stock']);
        });
    }
}
