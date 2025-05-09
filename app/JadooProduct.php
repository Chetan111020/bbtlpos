<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JadooProduct extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jadoo_products';

    public static function jaddoProductsDropdown($prepend_none = false)
    {
        $all = JadooProduct::get();

        $jadoo_products = $all->pluck('name', 'id');

        //Prepend none
        if ($prepend_none) {
            $jadoo_products = $jadoo_products->prepend(__('lang_v1.none'), '');
        }

        return $jadoo_products;
    }
}
