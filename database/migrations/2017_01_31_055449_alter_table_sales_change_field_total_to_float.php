<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSalesChangeFieldTotalToFloat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->float('total');
            $table->index(['total']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('total')
                ->default(false);
        });
    }
}
