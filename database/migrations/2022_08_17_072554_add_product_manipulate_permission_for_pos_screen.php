<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddProductManipulatePermissionForPosScreen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Permission::create(['name' => 'edit_product_qty_from_pos_screen']);
        Permission::create(['name' => 'delete_product_from_pos_screen']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::table('permissions', function (Blueprint $table) {
            //
        });*/
    }
}
