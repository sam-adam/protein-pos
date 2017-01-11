<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSalesDetailTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_items', function (Blueprint $table) { $table->rename('sale_items'); });
        Schema::table('sales_payments', function (Blueprint $table) { $table->rename('sale_payments'); });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) { $table->rename('sales_items'); });
        Schema::table('sale_payments', function (Blueprint $table) { $table->rename('sales_payments'); });
    }
}
