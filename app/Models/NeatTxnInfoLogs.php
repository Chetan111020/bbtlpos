<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Contact;
use Carbon\Carbon;

class NeatTxnInfoLogs extends Model
{
    use SoftDeletes;

    protected $table = 'neat_txn_info_logs';

    protected $fillable = [
        
        'neat_txn_id',
        'txn_id',
        'activity_by',
        'activity_title',
        
        'contact_id',       'new_contact_id',
        'transaction_date', 'new_transaction_date',
        'price_group',      'new_price_group',
        'tax_status',       'new_tax_status',
        'order_status',     'new_order_status',
        'delivery_method',  'new_delivery_method',
        'final_total',      'new_final_total',
        'discount_amount',  'new_discount_amount',
        'discount_type',    'new_discount_type', 
        'shipping_status',  'new_shipping_status',
        'shipping_charges', 'new_shipping_charges',
        'shipping_details', 'new_shipping_details',
        'shipping_address', 'new_shipping_address',
        'sale_note',        'new_sale_note', 
        'staff_note',       'new_staff_note',

        'tax_applicable',
        'notpickpack',
        
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    
public static function logActivity($neat_txn_id, $input, $txn_id = null, $activity_title = 'unknown', $previous_transaction_record = null)
{
    try {
        $log = new static();
        
        $log->neat_txn_id = $neat_txn_id ?? null;
        $log->txn_id = $txn_id;
        $log->activity_by = Auth::id();
        $log->activity_title = $activity_title;

        $isChanged = false;

        switch ($activity_title) {
            case 'Created':
                $log->contact_id = $input['contact_id'] ?? null;
                $log->transaction_date = $input['transaction_date'] ?? null;
                $log->price_group = $input['selling_price_group_id'] ?? null;
                $log->tax_status = $input['tax_deal'] ?? 'off';
                $log->order_status = $input['status'] ?? null;
                $log->shipping_status = $input['shipping_status'] ?? null;
                $log->delivery_method = $input['delivery_method'] ?? null;
                $log->discount_type = $input['discount_type'] ?? null;
                $log->sale_note = $input['sale_note'] ?? null;
                $log->staff_note = $input['staff_note'] ?? null;
                $log->notpickpack = $input['notpickpack'] ?? 0;
                $log->tax_applicable = $input['tax_applicable'] ?? 0;
                $log->final_total = round((float)str_replace(',', '', $input['final_total']), 2) ?? null;
                $log->discount_amount = round((float)str_replace(',', '', $input['discount_amount']), 2) ?? null;
                $log->shipping_charges = round((float)str_replace(',', '', $input['shipping_charges']), 2) ?? null;

                $isChanged = true;
                break;

            case 'Edited':
                if ($previous_transaction_record) {
                    $previous = $previous_transaction_record[0];
                    
                    if (isset($previous['contact_id']) && isset($input['contact_id']) && $previous['contact_id'] != $input['contact_id']) {
                        $log->contact_id = $previous['contact_id'];
                        $log->new_contact_id = $input['contact_id'];
                        $isChanged = true;
                    }
                    if (isset($previous['transaction_date']) && isset($input['transaction_date']) && Carbon::parse($previous['transaction_date'])->format('Y-m-d H') != Carbon::parse($input['transaction_date'])->format('Y-m-d H')) {
                        $log->transaction_date = $previous['transaction_date'];
                        $log->new_transaction_date = $input['transaction_date'];
                        $isChanged = true;
                    }
                    if (isset($previous['selling_price_group_id']) && isset($input['selling_price_group_id']) && $previous['selling_price_group_id'] != $input['selling_price_group_id']) {
                        $log->price_group = $previous['selling_price_group_id'];
                        $log->new_price_group = $input['selling_price_group_id'];
                        $isChanged = true;
                    }
                    if (isset($previous['final_total']) && isset($input['final_total']) && round((float)str_replace(',', '', $previous['final_total']), 2) != round((float)str_replace(',', '', $input['final_total']), 2)) {
                        $log->final_total = round((float)str_replace(',', '', $previous['final_total']), 2);
                        $log->new_final_total = round((float)str_replace(',', '', $input['final_total']), 2);
                        $isChanged = true;
                    }
                    if (isset($previous['discount_amount']) && isset($input['discount_amount']) && round((float)str_replace(',', '', $previous['discount_amount']), 2) != round((float)str_replace(',', '', $input['discount_amount']), 2)) {
                        $log->discount_amount = round((float)str_replace(',', '', $previous['discount_amount']), 2);
                        $log->new_discount_amount = round((float)str_replace(',', '', $input['discount_amount']), 2);
                        $isChanged = true;
                    }
                    if (isset($previous['shipping_charges']) && isset($input['shipping_charges']) && round((float)str_replace(',', '', $previous['shipping_charges']), 2) != round((float)str_replace(',', '', $input['shipping_charges']), 2)) {
                        $log->shipping_charges = round((float)str_replace(',', '', $previous['shipping_charges']), 2);
                        $log->new_shipping_charges = round((float)str_replace(',', '', $input['shipping_charges']), 2);
                        $isChanged = true;
                    }
                    if (isset($previous['discount_type']) && isset($input['discount_type']) && $previous['discount_type'] != $input['discount_type']) {
                        $log->discount_type = $previous['discount_type'];
                        $log->new_discount_type = $input['discount_type'];
                        $isChanged = true;
                    }
                    if (isset($previous['additional_notes']) && isset($input['sale_note']) && $previous['additional_notes'] != $input['sale_note']) {
                        $log->sale_note = $previous['additional_notes'];
                        $log->new_sale_note = $input['sale_note'];
                        $isChanged = true;
                    }
                    if (isset($previous['staff_note']) && isset($input['staff_note']) && $previous['staff_note'] != $input['staff_note']) {
                        $log->staff_note = $previous['staff_note'];
                        $log->new_staff_note = $input['staff_note'];
                        $isChanged = true;
                    }
                    if (isset($previous['status']) && isset($input['status']) && $previous['status'] != $input['status']) {
                        $log->order_status = $previous['status'];
                        $log->new_order_status = $input['status'];
                        $isChanged = true;
                    }
                    if (isset($previous['shipping_status']) && isset($input['shipping_status']) && $previous['shipping_status'] != $input['shipping_status']) {
                        $log->shipping_status = $previous['shipping_status'];
                        $log->new_shipping_status = $input['shipping_status'];
                        $isChanged = true;
                    }
                     if (isset($previous['tax_deal']) && isset($input['tax_deal']) && $previous['tax_deal'] != $input['tax_deal']) {
                        $log->tax_status = $previous['tax_deal'];
                        $log->new_tax_status = $input['tax_deal'];
                        $isChanged = true;
                    }
                    if (isset($previous['delivery_method']) && isset($input['delivery_method']) && $previous['delivery_method'] != $input['delivery_method']) {
                        $log->delivery_method = $previous['delivery_method'];
                        $log->new_delivery_method = $input['delivery_method'];
                        $isChanged = true;
                    }
     
                    //if (isset($input['notpickpack']) && $input['notpickpack'] != null) {
                    //    $log->notpickpack = $input['notpickpack'] ?? null;
                    //    $isChanged = true;
                    //}
                    //if (isset($input['tax_applicable']) && $input['tax_applicable'] != null) {
                    //    $log->tax_applicable = $input['tax_applicable'] ?? null;
                    //    $isChanged = true;
                    //}
                }
                break;

            default:
                break;
        }

        if ($isChanged) {
            $log->created_at = now();
            $log->save();
        }
    } catch (\Exception $e) {
        \Log::error('Error in logActivity method: ' . $e->getMessage());
    }

    return $log ?? null;
}


public static function logActivityupdateShipping($neat_txn_id, $input, $txn_id = null, $activity_title = 'unknown', $previous_transaction_record = null)
{
    try {
        $log = new static();
        
        $log->neat_txn_id = $neat_txn_id ?? null;
        $log->txn_id = $txn_id;
        $log->activity_by = Auth::id();
        $log->activity_title = $activity_title;

        $isChanged = false;

        switch ($activity_title) {
            case 'Created':
                $isChanged = true;
                break;

            case 'Edited':
                if ($previous_transaction_record) {
                    $previous = $previous_transaction_record;
                    if (isset($previous['shipping_charges']) && isset($input['shipping_charges']) && round((float)str_replace(',', '', $previous['shipping_charges']), 2) != round((float)str_replace(',', '', $input['shipping_charges']), 2)) {
                        $log->shipping_charges = round((float)str_replace(',', '', $previous['shipping_charges']), 2);
                        $log->new_shipping_charges = round((float)str_replace(',', '', $input['shipping_charges']), 2);
                        $isChanged = true;
                    }
                    if (isset($previous['shipping_status']) && isset($input['shipping_status']) && $previous['shipping_status'] != $input['shipping_status']) {
                        $log->shipping_status = $previous['shipping_status'];
                        $log->new_shipping_status = $input['shipping_status'];
                        $isChanged = true;
                    }
                    if (isset($previous['shipping_details']) && isset($input['shipping_details']) && $previous['shipping_details'] != $input['shipping_details']) {
                        $log->shipping_details = $previous['shipping_details'];
                        $log->new_shipping_details = $input['shipping_details'];
                        $isChanged = true;
                    }
                    if (isset($previous['shipping_address']) && isset($input['shipping_address']) && $previous['shipping_address'] != $input['shipping_address']) {
                        $log->shipping_address = $previous['shipping_address'];
                        $log->new_shipping_address = $input['shipping_address'];
                        $isChanged = true;
                    }
                    if (isset($previous['final_total']) && isset($input['final_total']) && round((float)str_replace(',', '', $previous['final_total']), 2) != round((float)str_replace(',', '', $input['final_total']), 2)) {
                        $log->final_total = round((float)str_replace(',', '', $previous['final_total']), 2);
                        $log->new_final_total = round((float)str_replace(',', '', $input['final_total']), 2);
                        $isChanged = true;
                    }
                }
                break;

            default:
                break;
        }

        if ($isChanged) {
            $log->created_at = now();
            $log->save();
        }
    } catch (\Exception $e) {
        \Log::error('Error in logActivityupdateShipping method: ' . $e->getMessage());
    }

    return $log ?? null;
}
                    
// NeatTxnInfoLogs.php

public function contact()
{
    return $this->belongsTo(Contact::class, 'contact_id');
}

public function newcontact()
{
    return $this->belongsTo(Contact::class, 'new_contact_id');
}

}
