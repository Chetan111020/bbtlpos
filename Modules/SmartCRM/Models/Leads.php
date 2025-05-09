<?php

namespace Modules\SmartCRM\Models;

use Illuminate\Database\Eloquent\Model;

class Leads extends Model
{
    protected $table = 'leads';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
    // public function getCreatedByAttribute()
    // {
    //     return $this->user->first_name . " " . $this->user->last_name;
    // }
    
}
