<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSalesPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_packages', function (Blueprint $table) {
            $table->unsignedInteger('sale_id');
            $table->unsignedInteger('package_id');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('price');
            $table->unsignedInteger('original_price');
            $table->unsignedInteger('discount')
                ->default(0);
            $table->unsignedInteger('subtotal');

            $table->index(['sale_id']);
            $table->index(['package_id']);
            $table->index(['quantity']);
            $table->index(['price']);
            $table->index(['original_price']);
            $table->index(['discount']);
            $table->index(['subtotal']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sale_packages');
    }
}
