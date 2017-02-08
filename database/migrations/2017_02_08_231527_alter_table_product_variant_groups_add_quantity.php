<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProductVariantGroupsAddQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variant_groups', function (Blueprint $table) {
            $table->unsignedInteger('quantity')
                ->default(1);

            $table->index('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_variant_groups', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}
