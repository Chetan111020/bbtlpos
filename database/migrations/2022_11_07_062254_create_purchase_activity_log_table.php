<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_activity_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->string('description')->nullable();
            $table->longText('message')->nullable();
            $table->integer('business_id')->unsigned()->nullable();
            $table->integer('location_id')->unsigned()->nullable();
            $table->integer('contact_id')->unsigned()->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('final_total')->nullable();
            $table->string('amount_paid')->nullable();
            $table->string('pay_term_number')->nullable();
            $table->string('pay_term_type')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('discount_amount')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('tax')->nullable();
            $table->string('shipping_details')->nullable();
            $table->string('shipping_charges')->nullable();
            $table->string('shipping_date')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->string('tracking_id')->nullable();
            $table->date('eta')->nullable();
            $table->string('box_qty')->nullable();
            $table->longText('reason')->nullable();
            $table->longText('purchase_lines')->nullable();
            $table->longText('payment_lines')->nullable();
            $table->longText('additional_notes')->nullable();
            $table->longText('extra_document')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('received_date')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('business_id');
            $table->index('contact_id');
            $table->index('transaction_date');
            $table->index('received_date');
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
        Schema::dropIfExists('purchase_activity_log');
    }
}
