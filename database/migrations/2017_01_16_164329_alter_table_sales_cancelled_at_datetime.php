<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSalesCancelledAtDatetime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dateTime('cancelled_at')
                ->nullable();

            $table->index(['cancelled_at']);
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
            $table->dropColumn('cancelled_at');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedInteger('cancelled_at');

            $table->index(['cancelled_at']);
        });
    }
}
