<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddPaymentVerifiedListPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Permission::create(['name' => 'list_payment_verified']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
