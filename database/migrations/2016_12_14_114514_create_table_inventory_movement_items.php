<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInventoryMovementItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_movement_items', function (Blueprint $table) {
            $table->unsignedInteger('inventory_movement_id');
            $table->unsignedInteger('product_id');
            $table->dateTime('expired_at');
            $table->unsignedInteger('cost');
            $table->unsignedInteger('quantity');

            $table->index(['inventory_movement_id']);
            $table->index(['product_id']);
            $table->index(['expired_at']);
            $table->index(['cost']);
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
        Schema::drop('inventory_movement_items');
    }
}
