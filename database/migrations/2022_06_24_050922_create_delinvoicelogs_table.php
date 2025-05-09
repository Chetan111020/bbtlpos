<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDelinvoicelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delinvoicelogs', function (Blueprint $table) {
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
            $table->dateTime('transaction_date')->nullable();
            $table->longText('transaction_line')->nullable();
            $table->longText('sell_lines')->nullable();
            $table->longText('payment_lines')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->binary('invoice_pdf')->nullable();
            $table->longText('reason')->nullable();
            //$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('business_id');
            $table->index('type');
            $table->index('contact_id');
            $table->index('transaction_date');
            $table->index('deleted_by');
        });

        DB::statement("ALTER TABLE delinvoicelogs CHANGE invoice_pdf invoice_pdf LONGBLOB NULL DEFAULT NULL");
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delinvoicelogs');
    }
}
