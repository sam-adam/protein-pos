<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSaleRefundPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_refund_packages', function (Blueprint $table) {
            $table->unsignedInteger('sale_refund_id');
            $table->unsignedInteger('sale_package_id');
            $table->unsignedInteger('quantity');

            $table->index(['sale_refund_id']);
            $table->index(['sale_package_id']);
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
        Schema::drop('sale_refund_packages');
    }
}
