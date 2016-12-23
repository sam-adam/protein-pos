<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoryRemovalsAddCurrentStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_removals', function (Blueprint $table) {
            $table->unsignedInteger('pre_adjusted_stock');

            $table->index(['pre_adjusted_stock']);
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
            $table->dropColumn(['pre_adjusted_stock']);
        });
    }
}
