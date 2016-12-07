<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->unsignedInteger('branch_id');
            $table->unsignedInteger('product_id');
            $table->double('cost');
            $table->unsignedInteger('stock');
            $table->dateTime('expired_at');

            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['branch_id']);
            $table->index(['product_id']);
            $table->index(['cost']);
            $table->index(['stock']);
            $table->index(['expired_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('inventories');
    }
}
