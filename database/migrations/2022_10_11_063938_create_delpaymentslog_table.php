<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDelpaymentslogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delpaymentslog', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payment_id')->unsigned()->nullable();
            $table->integer('transaction_id')->unsigned()->nullable();
            $table->integer('business_id')->unsigned()->nullable();
            $table->integer('location_id')->unsigned()->nullable();
            $table->integer('contact_id')->unsigned()->nullable();
            $table->string('contact_type')->nullable();
            $table->longText('reason')->nullable(); 

            $table->tinyInteger('is_return')->nullable(); 
            $table->string('amount')->nullable();
            $table->string('method')->nullable();
            
            $table->string('transaction_no')->nullable();
            $table->string('card_transaction_number')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_month')->nullable();
            $table->string('card_year')->nullable();
            $table->string('card_security', 5)->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('bank_account_number')->nullable();

            $table->dateTime('paid_on')->nullable();
            $table->tinyInteger('is_advance')->nullable();
            $table->string('advance_amt')->nullable();
            $table->string('invoice_no')->nullable();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->longText('note')->nullable();
            $table->string('document')->nullable();
            $table->string('payment_ref_no')->nullable();
            $table->integer('account_id')->unsigned()->nullable();
            $table->string('discount_type')->nullable();
            $table->string('discount_amount')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            
            $table->timestamps();

            $table->index('payment_id');
            $table->index('transaction_id');
            $table->index('business_id');
            $table->index('method');
            $table->index('contact_id');
            $table->index('contact_type');
            $table->index('paid_on');
            $table->index('invoice_no');
            $table->index('payment_ref_no');
            $table->index('created_by');
            $table->index('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delpaymentslog');
    }
}
