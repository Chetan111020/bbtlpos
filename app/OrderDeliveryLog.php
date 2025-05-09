<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryLog extends Model
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
    protected $table = 'order_delivery_log';
}
