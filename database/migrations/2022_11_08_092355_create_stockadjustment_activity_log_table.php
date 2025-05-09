<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockadjustmentActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stockadjustment_activity_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->string('description')->nullable();
            $table->longText('message')->nullable();
            $table->integer('business_id')->unsigned()->nullable();
            $table->integer('location_id')->unsigned()->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('adjustment_type')->nullable();
            $table->string('final_total')->nullable();
            $table->string('total_amount_recovered')->nullable();
            $table->longText('reason')->nullable();
            $table->longText('stock_adjustment_lines')->nullable();
            $table->longText('additional_notes')->nullable();
            $table->longText('extra_document')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('business_id');
            $table->index('transaction_date');
            $table->index('invoice_no');
            $table->index('final_total');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stockadjustment_activity_log');
    }
}
