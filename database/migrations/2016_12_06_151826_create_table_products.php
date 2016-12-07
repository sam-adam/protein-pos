<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->unsignedInteger('product_category_id');
            $table->unsignedInteger('product_variant_group_id')
                ->nullable();
            $table->unsignedInteger('brand_id');
            $table->string('name');
            $table->double('price');
            $table->string('code')
                ->nullable();
            $table->string('barcode')
                ->nullable();

            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['product_category_id']);
            $table->index(['product_variant_group_id']);
            $table->index(['brand_id']);
            $table->index(['name']);
            $table->index(['price']);
            $table->index(['code']);
            $table->index(['barcode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }
}
