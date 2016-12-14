<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInventoryMovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->unsignedInteger('from_branch_id')
                ->nullable();
            $table->unsignedInteger('to_branch_id');
            $table->text('remark');

            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['from_branch_id']);
            $table->index(['to_branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('inventory_movements');
    }
}
