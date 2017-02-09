<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSalePackageItemsAddProductVariantGroupId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_package_items', function (Blueprint $table) {
            $table->unsignedInteger('product_variant_group_id')
                ->nullable();

            $table->index(['product_variant_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_package_items', function (Blueprint $table) {
            $table->dropColumn('product_variant_group_id');
        });
    }
}
