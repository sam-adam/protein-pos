<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProductsAddBulkContainer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('product_item_id')
                ->nullable();
            $table->unsignedInteger('product_item_quantity')
                ->default(1);

            $table->index(['product_item_id']);
            $table->index(['product_item_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_item_id']);
            $table->dropColumn(['product_item_quantity']);
        });
    }
}
