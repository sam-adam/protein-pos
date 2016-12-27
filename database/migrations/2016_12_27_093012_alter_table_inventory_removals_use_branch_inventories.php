<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoryRemovalsUseBranchInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_removals', function (Blueprint $table) {
            $table->dropColumn(['inventory_id']);

            $table->unsignedInteger('branch_inventory_id')
                ->nullable();

            $table->index(['branch_inventory_id']);
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
            $table->dropColumn(['branch_inventory_id']);

            $table->unsignedInteger(['inventory_id']);

            $table->index(['inventory_id']);
        });
    }
}
