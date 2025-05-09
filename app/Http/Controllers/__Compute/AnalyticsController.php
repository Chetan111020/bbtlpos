<?php

namespace App\Http\Controllers\__Compute;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function productAnalytics(Request $request,$id = 0){

        if($request->ajax()){
            $dtnow = date('Y-m-d');

            $dtcurr = new DateTime($dtnow);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($dtnow);
            $dtbefore->modify('-140 day');

            $mainChart_sales = DB::table('transactions as t')
            ->select(DB::raw('DATE(t.transaction_date) as trdate'),DB::raw('SUM(tr.unit_price * tr.quantity) as sales'))
            ->leftJoin('transaction_sell_lines as tr','t.id','=','tr.transaction_id')
            ->where('tr.product_id',$id)
            ->where('t.type','sell')
            ->where('t.status','final')
            ->where('t.transaction_date','>=',$dtbefore->format('Y-m-d'))
            ->where('t.transaction_date','<=',$dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get()->toArray();

            $mainChart_purchase = DB::table('transactions as t')
            ->select(DB::raw('DATE(t.transaction_date) as trdate'),DB::raw('SUM(pl.purchase_price * pl.quantity) as purchases'))
            ->leftJoin('purchase_lines as pl','t.id','=','pl.transaction_id')
            ->where('pl.product_id',$id)
            ->where('t.type','purchase')
            ->where('t.status','received')
            ->where('t.transaction_date','>=',$dtbefore->format('Y-m-d'))
            ->where('t.transaction_date','<=',$dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get()->toArray();

            $n = 0;
            $m = 0;
            $sales = [];
            $purchases = [];
            $dates = [];
            for($i = $dtbefore; $i < $dtcurr; $i->modify('+1 day')){

                if(in_array($i->format('Y-m-d'),array_column($mainChart_sales,'trdate'))){
                    array_push($sales,$mainChart_sales[$n]->sales ?? 0);
                    $n++;
                }
                else{
                    array_push($sales,0);
                }

                if(in_array($i->format('Y-m-d'),array_column($mainChart_purchase,'trdate'))){
                    array_push($purchases,$mainChart_purchase[$m]->purchases ?? 0);
                    $m++;
                }
                else{
                    array_push($purchases,0);
                }

                array_push($dates,$i->format('Y-m-d'));

            }

            $mainChartResult = [
                $sales,
                $purchases,
                $dates
            ];

            $mnow = new DateTime($dtnow);
            $mbefore = new DateTime($dtnow);
            $mbefore->modify('-2 month');

            $secData1 = DB::table('transactions as t')
                ->select(
                    DB::raw('MONTH(t.transaction_date) as trmonth'),
                    DB::raw('YEAR(t.transaction_date) as tryear'),
                    DB::raw('SUM(tr.quantity) as qty'),
                    DB::raw('SUM(tr.quantity_returned) as qty_rtn'),
                    DB::raw('SUM(tr.unit_price_inc_tax * tr.quantity) as sales'),
                    DB::raw('SUM(tr.unit_price_inc_tax * tr.quantity_returned) as sales_rtn'))
                ->leftJoin('transaction_sell_lines as tr','t.id','=','tr.transaction_id')
                ->where('tr.product_id',$id)
                ->whereIn('t.type',['sell','sell_return'])
                ->where('t.status','final')
                ->where('t.transaction_date','>=',$mbefore->format('Y-m-01'))
                ->where('t.transaction_date','<=',$mnow->format('Y-m-t'))
                ->groupBy('trmonth','tryear')
                ->get()
            ->toArray();

            $secData2 = DB::table('transactions as t')
                ->select(
                    DB::raw('MONTH(t.transaction_date) as trmonth'),
                    DB::raw('YEAR(t.transaction_date) as tryear'),
                    DB::raw('SUM(pr.quantity) as qty'),
                    DB::raw('SUM(pr.purchase_price * pr.quantity) as purchase'))
                ->leftJoin('purchase_lines as pr','t.id','=','pr.transaction_id')
                ->where('pr.product_id',$id)
                ->where('t.type','purchase')
                ->where('t.status','received')
                ->where('pr.quantity_returned',0)
                ->where('t.transaction_date','>=',$mbefore->format('Y-m-01'))
                ->where('t.transaction_date','<=',$mnow->format('Y-m-t'))
                ->groupBy('trmonth','tryear')
                ->get()
            ->toArray();

            $secData = [];
            for($i = 0; $i < 3;$i++){
                $results = [];
                $tmpDate = new DateTime($mbefore->format('Y-m-01'));
                $tmpDate->modify('+'.$i.' month');

                $results['month'] = $tmpDate->format('F-Y');
                $results['sales'] = $this->format_money(0);
                $results['sales_rtn'] = $this->format_money(0);
                $results['qty'] = 0;
                $results['qty_rtn'] = 0;
                $results['purchase'] = $this->format_money(0);
                $results['pur_qty'] = 0;

                if(isset($secData1)){
                    if(in_array($tmpDate->format('m'),array_column($secData1,'trmonth'))){
                        foreach($secData1 as $item){
                            if($item->trmonth == $tmpDate->format('m')){
                                $results['sales'] = $this->format_money($item->sales);
                                $results['sales_rtn'] = $this->format_money($item->sales_rtn);
                                $results['qty'] = $this->format_number($item->qty);
                                $results['qty_rtn'] = $this->format_number($item->qty_rtn);
                                break;
                            }
                        }
                    }
                }
                if(isset($secData2)){
                    if(in_array($tmpDate->format('m'),array_column($secData2,'trmonth'))){
                        foreach($secData2 as $item){
                            if($item->trmonth == $tmpDate->format('m')){
                                $results['purchase'] = $this->format_money($item->purchase);
                                $results['pur_qty'] = $this->format_number($item->qty);
                                break;
                            }
                        }
                    }
                }
                array_push($secData,$results);
            }

            $total_data1 = DB::table('transactions as t')
                ->select(
                    DB::raw('SUM(tr.quantity) as sales_qty'),
                    DB::raw('SUM(tr.unit_price * tr.quantity) as sales_amt'))
                ->leftJoin('transaction_sell_lines as tr','t.id','=','tr.transaction_id')
                ->where('tr.product_id',$id)
                ->where('t.type','sell')
                ->where('t.status','final')
                ->get();

            $total_data2 = DB::table('transactions as t')
                ->select(
                    DB::raw('SUM(pr.quantity) as purchase_qty'),
                    DB::raw('SUM(pr.purchase_price * pr.quantity) as purchase_amt'))
                ->leftJoin('purchase_lines as pr','t.id','=','pr.transaction_id')
                ->where('pr.product_id',$id)
                ->where('t.type','purchase')
                ->where('t.status','received')
                ->where('pr.quantity_returned',0)
                ->get();

            $total_data = [];
            $total_data['sales_qty'] = $this->format_number($total_data1[0]->sales_qty);
            $total_data['sales_amt'] = $this->format_money($total_data1[0]->sales_amt);
            $total_data['purchase_qty'] = $this->format_number($total_data2[0]->purchase_qty);
            $total_data['purchase_amt'] = $this->format_money($total_data2[0]->purchase_amt);


            return response([
                $mainChartResult,
                $secData,
                $total_data
            ]);
        }

        return view('__compute.product_analytics');
    }

    function format_money($value){
        $str_out = "";
        if($value < 0){
            $str_out = "".number_format(($value*-1),1, '.', '');
        }
        else{
            $str_out = "".number_format($value,1, '.', ',');
        }
        return "$ ".$str_out;
    }

    function format_number($value){
        return number_format($value,0, '', '');
    }
}