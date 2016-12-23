<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInventoryRemovals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_removals', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->unsignedInteger('inventory_id');
            $table->unsignedInteger('quantity');
            $table->text('remark');

            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['inventory_id']);
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
        Schema::drop('inventory_removals');
    }
}
