<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultSellPriceToPurchaseLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->decimal('default_purchase_price', 22, 4)->nullable()->after('purchase_price_inc_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_lines', function (Blueprint $table) {
            //
        });
    }
}
