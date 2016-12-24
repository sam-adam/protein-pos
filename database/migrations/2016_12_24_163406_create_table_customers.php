<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->timestamp('deleted_at')
                ->nullable();
            $table->string('name');
            $table->string('phone')
                ->nullable();
            $table->string('email')
                ->nullable();
            $table->string('address')
                ->nullable();
            $table->unsignedInteger('registered_branch_id');
            $table->unsignedInteger('customer_group_id')
                ->nullable();

            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['deleted_at']);
            $table->index(['name']);
            $table->index(['phone']);
            $table->index(['email']);
            $table->index(['address']);
            $table->index(['registered_branch_id']);
            $table->index(['customer_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('customers');
    }
}
