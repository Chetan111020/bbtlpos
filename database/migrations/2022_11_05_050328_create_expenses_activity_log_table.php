<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses_activity_log', function (Blueprint $table) {
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

            $table->string('expense_category')->nullable();
            $table->string('expense_for')->nullable();

            $table->string('subscription_no')->nullable();
            $table->string('subscription_repeat_on')->nullable();

            $table->string('is_recurring')->nullable();
            $table->string('recur_interval')->nullable();
            $table->string('recur_interval_type')->nullable();
            $table->string('recur_repetitions')->nullable();
            $table->string('recur_stopped_on')->nullable();
            $table->string('recur_parent_id')->nullable();

            $table->string('final_total')->nullable();
            $table->string('amount_paid')->nullable();
            $table->string('discount_amount')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('tax')->nullable();
            $table->string('shipping_charges')->nullable();
            $table->string('box_qty')->nullable();
            $table->longText('reason')->nullable();
            $table->longText('payment_lines')->nullable();
            $table->longText('additional_notes')->nullable();
            $table->longText('document')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('business_id');
            $table->index('contact_id');
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
        Schema::dropIfExists('expenses_activity_log');
    }
}
