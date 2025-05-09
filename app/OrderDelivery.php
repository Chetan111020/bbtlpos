<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    protected $table = 'order_delivery';

    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
