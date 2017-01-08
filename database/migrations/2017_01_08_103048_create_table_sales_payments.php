<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSalesPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_id');
            $table->enum('payment_method', ['CASH', 'CREDIT_CARD']);
            $table->float('amount');
            $table->string('card_number')
                ->nullable();
            $table->float('card_tax')
                ->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();

            $table->index(['sales_id']);
            $table->index(['payment_method']);
            $table->index(['amount']);
            $table->index(['card_number']);
            $table->index(['card_tax']);
            $table->index(['created_by']);
            $table->index(['updated_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sales_payments');
    }
}
