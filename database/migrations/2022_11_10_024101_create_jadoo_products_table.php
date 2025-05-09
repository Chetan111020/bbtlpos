<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJadooProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadoo_products', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('name')->nullable();
            $table->longText('jadoo_name')->nullable();
            $table->string('barcode')->nullable();
            $table->string('itemcode')->nullable();
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jadoo_products');
    }
}
