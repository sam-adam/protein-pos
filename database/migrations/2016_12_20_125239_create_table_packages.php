<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('deleted_at')
                ->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->boolean('is_customizable')
                ->default(false);

            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['deleted_at']);
            $table->index(['name']);
            $table->index(['price']);
            $table->index(['is_customizable']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('packages');
    }
}
