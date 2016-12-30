<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('branch_id');
            $table->unsignedInteger('opened_by_user_id');
            $table->unsignedInteger('closed_by_user_id')
                ->nullable();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')
                ->nullable();
            $table->double('opened_cash_balance');
            $table->double('closed_cash_balance')
                ->nullable();
            $table->text('remark')
                ->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')
                ->nullable();
            $table->timestamps();

            $table->index(['branch_id']);
            $table->index(['opened_by_user_id']);
            $table->index(['closed_by_user_id']);
            $table->index(['opened_at']);
            $table->index(['closed_at']);
            $table->index(['opened_cash_balance']);
            $table->index(['closed_cash_balance']);
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
        Schema::drop('shifts');
    }
}
