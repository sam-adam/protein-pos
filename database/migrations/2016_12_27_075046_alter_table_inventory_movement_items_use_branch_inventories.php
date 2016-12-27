<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoryMovementItemsUseBranchInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_movement_items', function (Blueprint $table) {
            $table->dropColumn(['source_inventory_id']);

            $table->unsignedInteger('source_branch_inventory_id')
                ->nullable();

            $table->index(['source_branch_inventory_id']);
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
            $table->dropColumn(['source_branch_inventory_id']);

            $table->unsignedInteger(['source_inventory_id'])
                ->nullable();

            $table->index(['source_inventory_id']);
        });
    }
}
