<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBranchInventoriesAddContainerFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branch_inventories', function (Blueprint $table) {
            $table->unsignedInteger('container_id')
                ->nullable();
            $table->unsignedInteger('content_quantity')
                ->default(1);

            $table->index(['container_id']);
            $table->index(['content_quantity']);
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
            $table->dropColumn('container_id');
            $table->dropColumn('content_quantity');
        });
    }
}
