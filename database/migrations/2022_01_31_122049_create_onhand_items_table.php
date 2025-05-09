<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnhandItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onhand_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id')->default(0);
            $table->integer('year')->default(0);
            $table->integer('week_01')->default(0);
            $table->integer('week_02')->default(0);
            $table->integer('week_03')->default(0);
            $table->integer('week_04')->default(0);
            $table->integer('week_05')->default(0);
            $table->integer('week_06')->default(0);
            $table->integer('week_07')->default(0);
            $table->integer('week_08')->default(0);
            $table->integer('week_09')->default(0);
            $table->integer('week_10')->default(0);
            $table->integer('week_11')->default(0);
            $table->integer('week_12')->default(0);
            $table->integer('week_13')->default(0);
            $table->integer('week_14')->default(0);
            $table->integer('week_15')->default(0);
            $table->integer('week_16')->default(0);
            $table->integer('week_17')->default(0);
            $table->integer('week_18')->default(0);
            $table->integer('week_19')->default(0);
            $table->integer('week_20')->default(0);
            $table->integer('week_21')->default(0);
            $table->integer('week_22')->default(0);
            $table->integer('week_23')->default(0);
            $table->integer('week_24')->default(0);
            $table->integer('week_25')->default(0);
            $table->integer('week_26')->default(0);
            $table->integer('week_27')->default(0);
            $table->integer('week_28')->default(0);
            $table->integer('week_29')->default(0);
            $table->integer('week_30')->default(0);
            $table->integer('week_31')->default(0);
            $table->integer('week_32')->default(0);
            $table->integer('week_33')->default(0);
            $table->integer('week_34')->default(0);
            $table->integer('week_35')->default(0);
            $table->integer('week_36')->default(0);
            $table->integer('week_37')->default(0);
            $table->integer('week_38')->default(0);
            $table->integer('week_39')->default(0);
            $table->integer('week_40')->default(0);
            $table->integer('week_41')->default(0);
            $table->integer('week_42')->default(0);
            $table->integer('week_43')->default(0);
            $table->integer('week_44')->default(0);
            $table->integer('week_45')->default(0);
            $table->integer('week_46')->default(0);
            $table->integer('week_47')->default(0);
            $table->integer('week_48')->default(0);
            $table->integer('week_49')->default(0);
            $table->integer('week_50')->default(0);
            $table->integer('week_51')->default(0);
            $table->integer('week_52')->default(0);
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
        Schema::dropIfExists('onhand_items');
    }
}
