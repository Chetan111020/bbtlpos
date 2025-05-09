<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticJadooInvoice extends Model
{
    protected $table = 'static_jadoo_invoices';
    protected $fillable = [
        'transaction_id',
        'box_id',
        'sub_sku',
        'item_code',
        'name',
        'quantity',
        'unit_price',
        'line_total'
    ];
}
