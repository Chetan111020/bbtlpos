<?php

namespace App\Exports;

use App\Product;
use App\VariationLocationDetails;
use Maatwebsite\Excel\Concerns\FromArray;
use App\Transaction;
use DB;
class ARExport implements FromArray
{
    
    public function array():array {
        $business_id = request()->session()->get('user.business_id');

        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->leftjoin('users AS u', 'contacts.sales_rep', '=', 'u.id')
        ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
        ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
        ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
        ->where('transactions.business_id', $business_id)
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final')
        ->where('transactions.payment_status', '!=', 'paid')
        ->select(
            'transactions.id',
            'transactions.transaction_date',
            'transactions.invoice_no',
            'transactions.invoice_no as invoice_no_text',
            'contacts.name as customer_name',
            'contacts.mobile',
            'contacts.contact_id',
            'contacts.id as contact_id_new',
             
            'transactions.payment_status',
            'transactions.final_total',
            'transactions.tax_amount',
            'transactions.discount_amount',
            'transactions.discount_type',
            'transactions.total_before_tax',
            DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date'),
            DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
            DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id) as total_paid'),
            'bl.name as business_location',
            DB::raw('COUNT(SR.id) as return_exists'),
            DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE TP2.transaction_id = SR.id ) as return_paid'),
            DB::raw('(SELECT tsr.final_total FROM transactions as tsr WHERE tsr.id = SR.id) as amount_return'),
            DB::raw('sum(1) as total_sell'),
            DB::raw('DATEDIFF(CURDATE(), transactions.transaction_date) as days_diff'),

                            DB::raw('CASE 
                WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 0 AND 15 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                ELSE 0 
            END as days_1_15'),
                            DB::raw('CASE 
                WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 16 AND 30 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                ELSE 0 
            END as days_16_30'),
                            DB::raw('CASE 
                WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 31 AND 45 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                ELSE 0 
            END as days_31_45'),
                            DB::raw('CASE 
                WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 46 AND 60 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                ELSE 0 
            END as days_46_60'),
                            DB::raw('CASE 
                WHEN DATEDIFF(CURDATE(), transactions.transaction_date) > 60 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                ELSE 0 
            END as days_61_plus')
        )
        ->groupBy('transactions.id')->orderBy('contacts.name')->get();

        //set headers
        $sells_array = [['DATE', 'CUSTOMER NAME','INVOICE NO','TOTAL AMOUNT', 'TOTAL PAID', '1-15', '16-30', '31-45', '46-60', '61+', 'BALANCE DUE', 'SALE REP']];
        
        foreach ($sells as $sell) {
            $discount = !empty($sell->discount_amount) ? $sell->discount_amount : 0;

            if (!empty($discount) && $sell->discount_type == 'percentage') {
                $discount = $sell->total_before_tax * ($discount / 100);
            }

            $final_total =  $sell->final_total + $discount;

            $total_remaining =  $sell->final_total - $sell->total_paid;
             
            $sell_arr = [
                $sell->transaction_date,
                $sell->customer_name,
                $sell->invoice_no,
                $final_total,
                $sell->total_paid
            ];

            // Append the days columns if they are not 0
            if ($sell->days_1_15 != 0) {
                $sell_arr[] = $sell->days_1_15;
            } else {
                $sell_arr[] = '';
            }

            if ($sell->days_16_30 != 0) {
                $sell_arr[] = $sell->days_16_30;
            } else {
                $sell_arr[] = '';
            }

            if ($sell->days_31_45 != 0) {
                $sell_arr[] = $sell->days_31_45;
            } else {
                $sell_arr[] = '';
            }

            if ($sell->days_46_60 != 0) {
                $sell_arr[] = $sell->days_46_60;
            } else {
                $sell_arr[] = '';
            }

            if ($sell->days_61_plus != 0) {
                $sell_arr[] = $sell->days_61_plus;
            } else {
                $sell_arr[] = '';
            }

            $sell_arr[] = $total_remaining;
            $sell_arr[] = $sell->user_name;

            $sells_array[] = $sell_arr;
        }

        return $sells_array;
    }

    public function getRawData()
    {
        return $this->array();
    }
}