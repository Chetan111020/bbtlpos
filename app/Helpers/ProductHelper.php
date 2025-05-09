<?php

namespace App\Helpers;

use App\Product;
use DateTime;
use Illuminate\Support\Facades\DB;

class ProductHelper
{
    public static function getSalesData($product_id,$start_date,$end_date){

        $salesData = DB::table('transactions as t')
            ->select(
                DB::raw('SUM(tr.quantity) as qty'),
                DB::raw('SUM(tr.quantity_returned) as qty_rtn'),
                DB::raw('SUM(tr.unit_price_inc_tax * tr.quantity) as sales'),
                DB::raw('SUM(tr.unit_price_inc_tax * tr.quantity_returned) as sales_rtn'))
            ->leftJoin('transaction_sell_lines as tr','t.id','=','tr.transaction_id')
            ->where('tr.product_id',$product_id)
            ->where('t.status','final')
            ->whereBetween('t.transaction_date',[$start_date,$end_date." 23:59:59"])
            ->whereIn('t.type',['sell','sell_return'])
        ->get();

        return $salesData[0];
    }

    public static function getSafetyStock($product_id){

        $product = Product::where('id',$product_id)->where('not_for_selling',0)->first();
        $safety = 0;
        $buffer = 0;

        if(isset($product)){
            $start = new DateTime();
            $start->modify('-90 day');
            $end = date('Y-m-d');
            $cut_off_date = new DateTime();
            $cut_off_date->modify('-60 day');

            if($product->created_at < $cut_off_date->format('Y-m-d H:i:s')){
                $safetyStockData = DB::table('transactions as t')
                    ->select(
                        DB::raw('ROUND((SUM(tr.quantity) - SUM(tr.quantity_returned))/3) as safety')
                    )
                    ->leftJoin('transaction_sell_lines as tr','t.id','=','tr.transaction_id')
                    ->where('t.status','final')
                    ->where('tr.product_id',$product_id)
                    ->whereIn('t.type',['sell','sell_return'])
                    ->whereBetween('t.transaction_date',[$start->format('Y-m-d'),$end." 23:59:59"])
                ->get();

                $safety = $safetyStockData[0]->safety ?? 0;
                $buffer = number_format($safetyStockData[0]->safety / 2,0,'','') ?? 0;
            }
            else{
                $safetyStockData = DB::table('transactions as t')
                    ->select(
                        DB::raw('ROUND((SUM(tr.quantity - tr.quantity_returned))*0.3) as safety')
                    )
                    ->leftJoin('purchase_lines as tr','t.id','=','tr.transaction_id')
                    ->where('t.status','<>','draft')
                    ->where('tr.product_id',$product_id)
                    ->whereIn('t.type',['purchase','purchase_return','opening_stock'])
                ->get();

                $safety = $safetyStockData[0]->safety ?? 0;
                $buffer = number_format($safetyStockData[0]->safety / 2,0,'','') ?? 0;
            }

        }

        return [$safety,$buffer];
    }
}