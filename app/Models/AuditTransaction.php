<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTransaction extends Model
{
    //
    protected $fillable = [
        'transaction_id',
        'old_value',
        'new_value',
        'old_contact_id',
        'new_contact_id',
        'old_selling_group_id',
        'new_selling_group_id',
        'user_id',
    ];
}