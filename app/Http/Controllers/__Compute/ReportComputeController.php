<?php

namespace App\Http\Controllers\__Compute;

use App\Brands;
use App\Category;
use App\Contact;
use App\Http\Controllers\Controller;
use App\Variation;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\TransactionSellLine;
class ReportComputeController extends Controller
{
    public function underStockReport(Request $request){

        if($request->ajax()){
            $supplier_id = false;
            $brand_id = false;
            $category_id = false;
            $hide_neg_qty = false;
            $hide_new_pro = false;

            if(isset($request->supplier) && $request->supplier != 'all'){
                $supplier_id = $request->supplier;
            }

            if(isset($request->brand) && $request->brand != 'all'){
                $brand_id = $request->brand;
            }

            if(isset($request->category) && $request->category != 'all'){
                $category_id = $request->category;
            }

            if(isset($request->neg_qty) && $request->neg_qty == '0'){
                $hide_neg_qty = true;
            }

            if(isset($request->new_pro) && $request->new_pro == '0'){
                $hide_new_pro = true;
            }

            $cut_off_date = new DateTime();
            $cut_off_date->modify('-60 day');

            $safetyStockData = DB::table('variation_location_details as vl')
                ->select(
                    'p.id',
                    'p.supplier_id',
                    'p.name',
                    'p.category_id',
                    'vl.qty_available',
                    'stat.sell_qty as safety',
                    DB::raw('ROUND(stat.sell_qty/2) as buffer'),
                    DB::raw('ROUND((80*(stat.sell_qty + (stat.sell_qty/2)))/100) as margin'),
                    DB::raw('ROUND((stat.sell_qty + (stat.sell_qty/2)))-vl.qty_available as to_buy')
                )
                ->leftJoin('products as p','p.id','=','vl.product_id')
                ->leftJoin('product_wise_sale_qty as stat','p.id','=','stat.product_id')
                ->where('vl.qty_available','<',DB::raw('ROUND((80*(stat.sell_qty + (stat.sell_qty/2)))/100)'))
                ->where('vl.location_id',4)
                ->when($supplier_id,function($query) use ($supplier_id){
                    $query->where('supplier_id',$supplier_id);
                })
                ->when($brand_id,function($query) use ($brand_id){
                    $query->where('brand_id',$brand_id);
                })
                ->when($category_id,function($query) use ($category_id){
                    $query->where('category_id',$category_id);
                })
                ->when($hide_neg_qty,function($query){
                    $query->where('vl.qty_available','>=',0);
                })
                ->where('p.created_at','<',$cut_off_date->format('Y-m-d'))
                ->where('p.not_for_selling',0)
            ->get();

            if(!$hide_new_pro){
                $newProductStockData = DB::table('variation_location_details as vl')
                    ->select(
                        'p.id',
                        'p.supplier_id',
                        'p.name',
                        'p.category_id',
                        'vl.qty_available',
                        'stat.safety as safety',
                        DB::raw('ROUND(stat.safety/2) as buffer'),
                        DB::raw('ROUND((80*(stat.safety + (stat.safety/2)))/100) as margin'),
                        DB::raw('ROUND((stat.safety + (stat.safety/2)))-vl.qty_available as to_buy')
                    )
                    ->leftJoin('products as p','p.id','=','vl.product_id')
                    ->leftJoin('new_product_saftey_stock as stat','p.id','=','stat.product_id')
                    ->where('vl.qty_available','<',DB::raw('ROUND((80*(stat.safety + (stat.safety/2)))/100)'))
                    ->where('vl.location_id',4)
                    ->when($supplier_id,function($query) use ($supplier_id){
                        $query->where('supplier_id',$supplier_id);
                    })
                    ->when($brand_id,function($query) use ($brand_id){
                        $query->where('brand_id',$brand_id);
                    })
                    ->when($category_id,function($query) use ($category_id){
                        $query->where('category_id',$category_id);
                    })
                    ->when($hide_neg_qty,function($query){
                        $query->where('vl.qty_available','>=',0);
                    })
                    ->where('p.created_at','>',$cut_off_date->format('Y-m-d'))
                    ->where('p.not_for_selling',0)
                ->get();

                $safetyStockData = $safetyStockData->concat($newProductStockData);
            }

            ($supplier_id && count($safetyStockData) > 0) ? $enable_payload = 0 : $enable_payload = 1;
            $payload_string = implode("_",array_column($safetyStockData->toArray(),'id'));

            return response([$safetyStockData,$enable_payload,$payload_string]);
        }

        $suppliers = Contact::select('id','name')->where('type','supplier')->orderBy('name')->get();
        $brands = Brands::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('report.safety_under_stock_report',compact('suppliers','brands','categories'));
    }

     public function overStockReport(Request $request){

        if($request->ajax()){
            $supplier_id = false;
            $brand_id = false;
            $category_id = false;
            $hide_neg_qty = false;
            $hide_new_pro = false;
            $not_for_selling = 0;  

            if(isset($request->supplier) && $request->supplier != 'all'){
                $supplier_id = $request->supplier;
            }

            if(isset($request->brand) && $request->brand != 'all'){
                $brand_id = $request->brand;
            }

            if(isset($request->category) && $request->category != 'all'){
                $category_id = $request->category;
            }

            if(isset($request->neg_qty) && $request->neg_qty == '0'){
                $hide_neg_qty = true;
            }

            if(isset($request->new_pro) && $request->new_pro == '0'){
                $hide_new_pro = true;
            }
            
            if(isset($request->not_for_selling)){
                $not_for_selling = $request->not_for_selling;
            }
            
            $cut_off_date = new DateTime();
            $cut_off_date->modify('-60 day');
            
            $lastSellDates = DB::table('transaction_sell_lines as tsl')
                            ->leftjoin('transactions', 'transactions.id', '=', 'tsl.transaction_id')
                            ->select('tsl.product_id', DB::raw('DATE_FORMAT(MAX(transactions.transaction_date), "%m-%d-%Y %h:%i %p") as last_sell_date'),
                                DB::raw('DATEDIFF(CURRENT_DATE, MAX(transactions.transaction_date)) as days_since_last_sell')
                            )
                            ->where('transactions.type', 'sell')
                            ->where('transactions.status', 'final')
                            ->groupBy('tsl.product_id');

            $lastPurchaseDates = DB::table('purchase_lines as pl')
                            ->leftjoin('transactions', 'transactions.id', '=', 'pl.transaction_id')
                            ->select('pl.product_id', DB::raw('DATE_FORMAT(MAX(transactions.transaction_date), "%m-%d-%Y %h:%i %p") as last_purchase_date')
                            )
                            ->where('transactions.type', 'purchase')
                            ->where('transactions.status', 'received')
                            ->groupBy('pl.product_id');

            $safetyStockData = DB::table('variation_location_details as vl')
                ->select(
                    'p.id',
                    'p.supplier_id',
                    'p.name',
                    'p.sku',
                    'p.category_id',
                    'v.dpp_inc_tax',
                    'vl.qty_available',
                    'stat.sell_qty as safety',
                    DB::raw('ROUND(stat.sell_qty/2) as buffer'),
                    DB::raw('ROUND((120*(stat.sell_qty + (stat.sell_qty/2)))/100) as margin'),
                    DB::raw('vl.qty_available-ROUND((stat.sell_qty + (stat.sell_qty/2))) as to_buy'),
                    'last_sell_dates.last_sell_date',
                    'last_sell_dates.days_since_last_sell',
                    'last_purchase_dates.last_purchase_date'
                )
                ->leftJoin('products as p','p.id','=','vl.product_id')
                ->leftJoinSub($lastSellDates, 'last_sell_dates', function($join) {
                    $join->on('p.id', '=', 'last_sell_dates.product_id');
                })
                ->leftJoinSub($lastPurchaseDates, 'last_purchase_dates', function($join) {
                    $join->on('p.id', '=', 'last_purchase_dates.product_id');
                })
                ->leftJoin('variations as v','v.id','=','vl.variation_id')
                ->leftJoin('product_wise_sale_qty as stat','p.id','=','stat.product_id')
                ->where('vl.qty_available','>',DB::raw('ROUND((120*(stat.sell_qty + (stat.sell_qty/2)))/100)'))
                ->where('vl.location_id',4)
                ->where('p.not_for_selling', $not_for_selling) 
                ->when($supplier_id,function($query) use ($supplier_id){
                    $query->where('supplier_id',$supplier_id);
                })
                ->when($brand_id,function($query) use ($brand_id){
                    $query->where('brand_id',$brand_id);
                })
                ->when($category_id,function($query) use ($category_id){
                    $query->where('category_id',$category_id);
                })
                ->when($hide_neg_qty,function($query){
                    $query->where('vl.qty_available','>=',0);
                })
                ->where('p.created_at','<',$cut_off_date->format('Y-m-d'))
            ->get();

            if(!$hide_new_pro){
                $newProductStockData = DB::table('variation_location_details as vl')
                    ->select(
                        'p.id',
                        'p.supplier_id',
                        'p.name',
                        'p.sku',
                        'p.category_id',
                        'v.dpp_inc_tax',
                        'vl.qty_available',
                        'stat.safety as safety',
                        DB::raw('ROUND(stat.safety/2) as buffer'),
                        DB::raw('ROUND((120*(stat.safety + (stat.safety/2)))/100) as margin'),
                        DB::raw('vl.qty_available-ROUND((stat.safety + (stat.safety/2))) as to_buy'),
                        'last_sell_dates.last_sell_date',
                        'last_sell_dates.days_since_last_sell',
                        'last_purchase_dates.last_purchase_date'
                    )
                    ->leftJoin('products as p','p.id','=','vl.product_id')
                    ->leftJoinSub($lastSellDates, 'last_sell_dates', function($join) {
                        $join->on('p.id', '=', 'last_sell_dates.product_id');
                    })
                    ->leftJoinSub($lastPurchaseDates, 'last_purchase_dates', function($join) {
                        $join->on('p.id', '=', 'last_purchase_dates.product_id');
                    })
                    ->leftJoin('variations as v','v.id','=','vl.variation_id')
                    ->leftJoin('new_product_saftey_stock as stat','p.id','=','stat.product_id')
                    ->where('vl.qty_available','>',DB::raw('ROUND((120*(stat.safety + (stat.safety/2)))/100)'))
                    ->where('vl.location_id',4)
                    ->where('p.not_for_selling', $not_for_selling) 
                    ->when($supplier_id,function($query) use ($supplier_id){
                        $query->where('supplier_id',$supplier_id);
                    })
                    ->when($brand_id,function($query) use ($brand_id){
                        $query->where('brand_id',$brand_id);
                    })
                    ->when($category_id,function($query) use ($category_id){
                        $query->where('category_id',$category_id);
                    })
                    ->when($hide_neg_qty,function($query){
                        $query->where('vl.qty_available','>=',0);
                    })
                    ->where('p.created_at','>',$cut_off_date->format('Y-m-d'))
                ->get();

                $safetyStockData = $safetyStockData->concat($newProductStockData);
            }

            foreach($safetyStockData as $item){
                if($item->safety < 0){
                    $item->safety = 0;
                    $item->buffer = 0;
                    $item->to_buy = $item->qty_available;
                }
                $item->suggest_qty = $item->safety + $item->buffer;
                $item->qty_available = round($item->qty_available, 2);
                $item->to_buy = round($item->to_buy, 2);
                $item->to_buy_value = round($item->to_buy * $item->dpp_inc_tax, 2);
            }

            return response([$safetyStockData]);
        }

        $suppliers = Contact::select('id','name')->where('type','supplier')->orderBy('name')->get();
        $brands = Brands::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('report.safety_over_stock_report',compact('suppliers','brands','categories'));
    }
    public function velocityReport(Request $request){
        if($request->ajax()){

            $brand_id = false;
            $category_id = false;
            $hide_neg_qty = false;
            $hide_inactive = false;

            if(isset($request->brand) && $request->brand != 'all'){
                $brand_id = $request->brand;
            }

            if(isset($request->category) && $request->category != 'all'){
                $category_id = $request->category;
            }

            if(isset($request->neg_qty) && $request->neg_qty == '0'){
                $hide_neg_qty = true;
            }

            if(isset($request->inactive_products) && $request->inactive_products == '1'){
                $hide_inactive = true;
            }

            $velocityData = DB::table('products as p')
                ->select(
                    'p.item_code',
                    'p.name',
                    DB::raw('COALESCE(vld.qty_available, 0) AS on_hand'),
                    DB::raw('COALESCE(v1.avg_sale_qty, 0) AS v1_avg'),
                    DB::raw('COALESCE(v2.avg_sale_qty, 0) AS v2_avg'),
                    DB::raw('COALESCE(v3.avg_sale_qty, 0) AS v3_avg')
                )
                ->leftJoin('velocity_qty_1 as v1','p.id','=','v1.product_id')
                ->leftJoin('velocity_qty_2 as v2','p.id','=','v2.product_id')
                ->leftJoin('velocity_qty_3 as v3','p.id','=','v3.product_id')
                // ->leftJoin('variation_location_details as vld','p.id','=','vld.product_id')
                // ->where('vld.location_id','=',4)
                ->leftJoin('variation_location_details as vld', function ($join) {
                    $join->on('p.id','=','vld.product_id')->where('vld.location_id','=',4);
                })
                ->when($brand_id,function($query) use ($brand_id){
                    $query->where('brand_id',$brand_id);
                })
                ->when($category_id,function($query) use ($category_id){
                    $query->where('category_id',$category_id);
                })
                ->when($hide_neg_qty,function($query){
                    $query->where('vld.qty_available','>=',0);
                })
                ->when($hide_inactive,function($query){
                    $query->where('not_for_selling','=','0');
                })
            ->get();

            foreach($velocityData as $item){
                $item->on_hand = round($item->on_hand);
                $item->v_all_avg = round(($item->v1_avg + $item->v2_avg + $item->v3_avg) / 3,2);
                $item->days_on_hand = 0;
                if($item->v_all_avg != 0){
                    $item->days_on_hand = round($item->on_hand / $item->v_all_avg, 2);
                }

                $item->v1_avg = $this->format_number($item->v1_avg);
                $item->v2_avg = $this->format_number($item->v2_avg);
                $item->v3_avg = $this->format_number($item->v3_avg);
            }
            return Datatables::of($velocityData)->make(true);
        }
        $brands = Brands::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        return view('report.velocity_report',compact('brands','categories'));
    }

    public function skuReport(Request $request){
        if($request->ajax()){

            $brand_id = false;
            $category_id = false;
            $hide_neg_qty = false;
            $hide_inactive = false;

            if(isset($request->brand) && $request->brand != 'all'){
                $brand_id = $request->brand;
            }

            if(isset($request->category) && $request->category != 'all'){
                $category_id = $request->category;
            }

            if(isset($request->neg_qty) && $request->neg_qty == '0'){
                $hide_neg_qty = true;
            }

            if(isset($request->inactive_products) && $request->inactive_products == '1'){
                $hide_inactive = true;
            }


            $baseDate = new DateTime();
            $startDate = $baseDate->modify('-90 day')->format('Y-m-d');
            $endDate = date('Y-m-d');
            if(isset($request->dates)){
                $date_arr = explode(' - ',$request->dates);
                $startDate = date('Y-m-d',strtotime($date_arr[0]));
                $endDate = date('Y-m-d',strtotime($date_arr[1]));;
            }

            $skuTransactionData = DB::table('transactions as t')
                ->select(
                    'tsl.product_id',
                    DB::raw('ROUND(AVG(tsl.unit_price_inc_tax),2) as avg_sale_price'),
                    DB::raw('ROUND(AVG(100-((tsl.purchase_price/tsl.unit_price_inc_tax)*100)),2) as avg_gp')
                )
                ->join('transaction_sell_lines as tsl','t.id','=','tsl.transaction_id')
                ->whereBetween('t.transaction_date',[$startDate,$endDate." 23:59:59"])
                ->where('t.status','final')
            ->groupBy('tsl.product_id');

            $velocityData = DB::table('products as p')
                ->select(
                    'p.item_code',
                    'p.name',
                    'v.default_purchase_price',
                    DB::raw('COALESCE(vsku.avg_sale_price, 0) AS avg_sale_price'),
                    DB::raw('COALESCE(vsku.avg_gp, 0) AS avg_gp'),
                    DB::raw('COALESCE(vld.qty_available, 0) AS on_hand'),
                    DB::raw('COALESCE(v1.avg_sale_qty, 0) AS v1_avg'),
                    DB::raw('COALESCE(v2.avg_sale_qty, 0) AS v2_avg'),
                    DB::raw('COALESCE(v3.avg_sale_qty, 0) AS v3_avg')
                )
                ->leftJoin('velocity_qty_1 as v1','p.id','=','v1.product_id')
                ->leftJoin('velocity_qty_2 as v2','p.id','=','v2.product_id')
                ->leftJoin('velocity_qty_3 as v3','p.id','=','v3.product_id')
                ->leftJoin('variations as v','p.id','=','v.product_id')
                // ->leftJoin('velocity_sku as vsku','p.id','=','vsku.product_id')
                ->leftJoinSub($skuTransactionData, 'vsku', function ($join) {
                    $join->on('p.id','=','vsku.product_id');
                })
                ->leftJoin('variation_location_details as vld', function ($join) {
                    $join->on('p.id','=','vld.product_id')->where('vld.location_id','=',4);
                })
                ->when($brand_id,function($query) use ($brand_id){
                    $query->where('brand_id',$brand_id);
                })
                ->when($category_id,function($query) use ($category_id){
                    $query->where('category_id',$category_id);
                })
                ->when($hide_neg_qty,function($query){
                    $query->where('vld.qty_available','>=',0);
                })
                ->when($hide_inactive,function($query){
                    $query->where('not_for_selling','=','0');
                })
            ->get();

            foreach($velocityData as $item){
                $item->on_hand = round($item->on_hand);
                $item->v_all_avg = round(($item->v1_avg + $item->v2_avg + $item->v3_avg) / 3,2);
                $item->days_on_hand = 0;
                if($item->v_all_avg != 0){
                    $item->days_on_hand = round($item->on_hand / $item->v_all_avg, 2);
                }
                $item->default_purchase_price = $this->format_money($item->default_purchase_price);
                $item->gp_doller = $this->format_money($item->avg_gp * $item->v_all_avg * $item->avg_sale_price);
                $item->avg_sale_price = $this->format_money($item->avg_sale_price);
                $item->avg_gp .= "%";
            }
            return Datatables::of($velocityData)->make(true);
        }
        $brands = Brands::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        return view('report.velocity_sku_report',compact('brands','categories'));
    }


    public function priceUpdateReport(Request $request){
        if($request->ajax()){

            $brand_id = false;
            $category_id = false;
            $hide_inactive = false;

            if(isset($request->brand) && $request->brand != 'all'){
                $brand_id = $request->brand;
            }

            if(isset($request->category) && $request->category != 'all'){
                $category_id = $request->category;
            }

            if(isset($request->inactive_products) && $request->inactive_products == '1'){
                $hide_inactive = true;
            }

            $baseDate = new DateTime();
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
            if(isset($request->dates)){
                $date_arr = explode(' - ',$request->dates);
                $startDate = date('Y-m-d',strtotime($date_arr[0]));
                $endDate = date('Y-m-d',strtotime($date_arr[1]));;
            }
            // SELECT p.id,p.name,MAX(pal.created_at) FROM products p, product_activity_log pal
            // where p.id = pal.product_id and pal.message like '%Selling Price%'
            // and DATE(pal.created_at) = CURRENT_DATE GROUP by p.id,p.name
            $reportData = DB::table('products as p')
                ->select(
                    'p.id',
                    'p.name',
                    'p.sku',
                    'p.brand_id',
                    'p.category_id',
                    DB::raw('MAX(pal.created_at) as price_updated_at')
                )
                ->join('product_activity_log as pal','p.id','=','pal.product_id')
                ->whereBetween('pal.created_at',[$startDate,$endDate." 23:59:59"])
                ->where('pal.message','like','%Selling Price%')
                ->when($brand_id,function($query) use ($brand_id){
                    $query->where('p.brand_id',$brand_id);
                })
                ->when($category_id,function($query) use ($category_id){
                    $query->where('p.category_id',$category_id);
                })
                ->when($hide_inactive,function($query){
                    $query->where('p.not_for_selling','=','0');
                })
                ->groupBy('p.id','p.name','p.sku','p.brand_id','p.category_id')
            ->get();

            foreach($reportData as $item){
                $variation = Variation::where('product_id', $item->id)->first();
                if(!empty($variation->sell_price_inc_tax)){
                    $item->sell_price_inc_tax = $this->format_money($variation->sell_price_inc_tax);
                }
                else{
                    $item->sell_price_inc_tax = '';
                }

                $category = Category::find($item->category_id);
                if(!empty($category->name)){
                    $item->cat_name = $category->name;
                }
                else{
                    $item->cat_name = '--';
                }

                $brand = Brands::find($item->brand_id);
                if(!empty($brand->name)){
                    $item->brand_name = $brand->name;
                }
                else{
                    $item->brand_name = '--';
                }

                $item->price_updated_at = date('m/d/Y H:i',strtotime($item->price_updated_at));
            }

            return Datatables::of($reportData)->make(true);
        }
        $brands = Brands::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        return view('report.price_update_report',compact('brands','categories'));
    }

    function format_money($value){
        return "$ ".number_format($value,2,'.','');
    }
    function format_number($value){
        return number_format($value,2,'.','');
    }

    public function PickPackMismatch($id)
    {
    $business_id = request()->session()->get('user.business_id');

    $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
        ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
        ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
        ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
        ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
        ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
        ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
        ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
        ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
        ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')
        ->leftjoin('business_locations AS bl', 't.location_id', '=', 'bl.id')
        ->leftjoin('res_tables AS rt', 't.res_table_id', '=', 'rt.id')
        ->where('t.id', $id)
        ->where('t.business_id', $business_id)
        ->where('t.type', 'sell')
        ->where('t.status', 'final');

    $products = $query->select(
        'p.name as product_name',
        'categories.name as category_name',
        'p.sku as sku',
        'transaction_sell_lines.picking_status',
        'transaction_sell_lines.packing_status',
        DB::raw('ROUND(vld.qty_available) as stock_qty'),
        DB::raw('ROUND(transaction_sell_lines.quantity) as order_quantity'),
        DB::raw('ROUND(transaction_sell_lines.unit_price, 2) as unit_price'),
        DB::raw('ROUND((transaction_sell_lines.quantity * transaction_sell_lines.unit_price), 2) as total')
    )
        ->groupBy('p.name')
        ->get();

    // Check if both picking_status and packing_status are 2
    $bothStatusTwo = $products->every(function ($product) {
        return $product->picking_status == 2 && $product->packing_status == 2;
    });

    // If both picking_status and packing_status are 2 for all products, return "No data found"
    if ($bothStatusTwo) {
        return response()->json(['message' => 'No data found'], 404);
    }

    // Format total value
    $formattedProducts = $products->map(function ($product) {
        unset($product->picking_status);
        unset($product->packing_status);
        $product->total = number_format($product->total, 2);
        return $product;
    });

    return response()->json($formattedProducts);
}
}