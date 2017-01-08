<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('opened_by_user_id');
            $table->unsignedInteger('closed_by_user_id')
                ->nullable();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')
                ->nullable();
            $table->unsignedInteger('branch_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('customer_discount')
                ->default(0);
            $table->unsignedInteger('sales_discount')
                ->default(0);;
            $table->boolean('is_delivery')
                ->default(false);
            $table->boolean('total')
                ->default(false);
            $table->dateTime('delivered_at')
                ->nullable();
            $table->dateTime('paid_at')
                ->nullable();
            $table->text('remark')
                ->nullable();
            $table->unsignedInteger('cancelled_at');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->timestamps();

            $table->index(['branch_id']);
            $table->index(['customer_id']);
            $table->index(['customer_discount']);
            $table->index(['sales_discount']);
            $table->index(['is_delivery']);
            $table->index(['delivered_at']);
            $table->index(['paid_at']);
            $table->index(['opened_by_user_id']);
            $table->index(['closed_by_user_id']);
            $table->index(['opened_at']);
            $table->index(['closed_at']);
            $table->index(['cancelled_at']);
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
        Schema::drop('sales');
    }
}
