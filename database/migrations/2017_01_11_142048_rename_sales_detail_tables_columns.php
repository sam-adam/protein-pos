<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSalesDetailTablesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('sale_items', function (Blueprint $table) { $table->renameColumn('sales_id', 'sale_id'); });
        Schema::table('sale_payments', function (Blueprint $table) { $table->renameColumn('sales_id', 'sale_id'); });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('sale_items', function (Blueprint $table) { $table->renameColumn('sale_id', 'sales_id'); });
        Schema::table('sale_payments', function (Blueprint $table) { $table->renameColumn('sale_id', 'sales_id'); });
    }
}
