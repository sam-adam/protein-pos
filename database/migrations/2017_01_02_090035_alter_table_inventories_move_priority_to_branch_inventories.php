<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInventoriesMovePriorityToBranchInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branch_inventories', function (Blueprint $table) {
            $table->unsignedInteger('priority');

            $table->index(['priority']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branch_inventories', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
}
