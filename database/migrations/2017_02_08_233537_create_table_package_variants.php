<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePackageVariants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_variants', function (Blueprint $table) {
            $table->unsignedInteger('package_id');
            $table->unsignedInteger('product_variant_group_id');

            $table->index('package_id');
            $table->index('product_variant_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('package_variants');
    }
}
