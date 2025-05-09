<?php

namespace App\Http\Controllers;

use App\BusinessLocation;

use App\Charts\CommonChart;
use App\Currency;
use App\Transaction;
use App\Utils\BusinessUtil;

use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\VariationLocationDetails;
use Datatables;
use DB;
use Illuminate\Http\Request;
use App\Utils\Util;
use App\Utils\RestaurantUtil;
use App\User;
use App\Product;
use App\Contact;
use App\Variation;

use DateTime;



use Illuminate\Notifications\DatabaseNotification;

class NewHomeController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $commonUtil;
    protected $restUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('dashboard.data')) {
            return view('home.index_new');
        }
        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $date_filters['this_fy'] = $fy;
        $date_filters['this_month']['start'] = date('Y-m-01');
        $date_filters['this_month']['end'] = date('Y-m-t');
        $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));


        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();

        //Get sell for indivisual locations
        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();

        return view('home.index_new', compact('date_filters', 'all_locations'));
    }
    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals($start = null, $end = null)
    {
        if (request()->ajax()) {
          

            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }

            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end, $location_id);

            $transaction_types = [
                'purchase_return', 'sell_return', 'expense'
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end,
                $location_id
            );
            //Total Staff
             $user = User::where('business_id', $business_id)
                    ->user()
                    ->where('is_cmmsn_agnt', 0)
                    ->select(['id', 'username',
                    DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"), 'email', 'allow_login'])
                    ->count();

            //total items
            $allproducts = DB::table('variation_location_details')
                ->count('qty_available');

            //total customer
             $allcustomer = Contact::where('type','customer')
            ->where('contact_status','active')
            ->select(DB::raw('contacts.name'))
            ->count();

            //Total Inventory Stock
               $quantity = DB::table('variation_location_details')

            ->sum('qty_available');
            

            //Total Inventory Value
             $inventory_value = Product::join('variation_location_details','variation_location_details.product_id', '=' ,'products.id' )
            ->join('variations', 'variations.product_id', '=', 'products.id')
            ->where('products.not_for_selling', '=',0)
            ->sum(DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available'));


            //top customers
            $end = date('Y-m-d', strtotime('+1 day', strtotime($end)));
            $query = Transaction::join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->Select(
                    'contacts.name as name',
                    DB::raw('SUM(final_total) as high_purchase')
                )
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.transaction_date', '>=', $start)
                ->where('transactions.transaction_date', '<=', $end)
                ->groupBy('contacts.id')->take(6)
                ->orderBy('high_purchase', 'DESC')
                ->get();

            $a = [];
            foreach ($query as $value) {
                array_push($a, $value->name);
                array_push($a, $value->high_purchase);
            }


            $end = date('Y-m-d', strtotime('+1 day', strtotime($end)));

            // gross profit
            $gross_profit =0;
            $gross_profit = $this->getAvgGpData($business_id,$start,$end);

            //total expenses
            $expenses = Transaction::leftJoin('expense_categories AS ec', 'transactions.expense_category_id', '=', 'ec.id')
                ->whereIn('transactions.type', ['expense', 'expense_refund'])
                ->select(
                    'transaction_date',
                    'final_total'
                )
                ->where('transaction_date', '>=', $start)
                ->where('transaction_date', '<=', $end)
                ->get();
                
                //total sell
               $total_sell = $this->getSaleData($business_id,$start,$end);
               
               
                //total order
                $total_order = DB::table('transactions as t')
                ->where('type', 'sell')
                ->where('status', 'final')
                ->whereDate('t.transaction_date','>=',$start)
                ->whereDate('t.transaction_date', '<=',$end)
                ->count(); 

            //invoice_due
            // $invoice_due = Transaction::where('transactions.business_id', $business_id)
            //     ->where('transactions.type', 'sell')
            //     ->where('transactions.status', 'final')
            //     ->select(
            //         'final_total',
            //         DB::raw('(SELECT SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id) as total_paid')
            //     )
            //     ->where('transaction_date', '>=', '2021-05-31')
            //     ->where('transaction_date', '<=', \Carbon::now()->format('Y-m-d'));
            //     $sell_details = $invoice_due->get();
            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id);

            $discount = Transaction::where('transactions.business_id', $business_id)
                        ->where('transactions.type', 'sell')
                        ->where('transactions.status', 'final')
                        ->select(
                            // DB::raw('SUM(discount_amount) as discount_amount')
                            DB::raw("SUM(IF(transactions.discount_type='percentage',(transactions.total_before_tax * (transactions.discount_amount / 100)),transactions.discount_amount)) as discount_amount")
                        )
                        ->where('transactions.transaction_date', '>=', $start)
                        ->where('transactions.transaction_date', '<=', $end)
                        ->get();
            
            $purchase = DB::table('transactions as t')
                        ->where('type', 'purchase')
                        ->where('status', 'received')
                        ->select(
                            DB::raw('SUM(tr.purchase_price * tr.quantity) as purchase_amt'),
                            DB::raw('SUM(tr.purchase_price * tr.quantity) - SUM(tp.amount) as purchase_paid')
                        )
                        ->leftjoin('transaction_payments as tp', 't.id', '=', 'tp.transaction_id')
                        ->leftJoin('purchase_lines as tr', 't.id', '=', 'tr.transaction_id')        
                        ->where('t.transaction_date', '>=', $start)
                        ->where('t.transaction_date', '<=', $end)
                        ->get();
            $total_purchase_inc_tax = !empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];
            $output['total_discount'] = $discount->sum('discount_amount');
            $output['total_purchase'] = $purchase->sum('purchase_amt');
            $output['purchase_due'] = $purchase->sum('purchase_paid');
            $output['total_order'] = $total_order;
            $output['total_staff'] = $user;
            $output['allproducts'] = $allproducts;
            $output['allcustomer'] = $allcustomer;
            $output['quantity'] = $quantity;
            $output['inventory_value'] = $inventory_value;
            $output['a'] = $a;
            $output['gross_profit'] = $gross_profit;
            $output['total_sell'] = $total_sell;
            // $output['invoice_due'] = $sell_details->sum('final_total') - $sell_details->sum('total_paid');
            $output['invoice_due'] = $sell_details['invoice_due'];
            $output['total_expense'] = $transaction_totals['total_expense'];

            return $output;
        }
    }


    public function getSaleData($business_id,$start_date,$end_date){
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $with = [];
        $sells = Transaction::select(DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                    TP.transaction_id=transactions.id) as total_paid'),
                    'discount_amount',
                    'discount_type',
                    'total_before_tax',
                    'final_total')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final');
        
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $sells->where('transactions.created_by', request()->session()->get('user.id'));
        }

        if (!empty($start_date) && !empty($end_date)) {
            $sells->whereDate('transactions.transaction_date', '>=', $start_date)
                        ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if ($is_woocommerce) {
            $sells->addSelect('transactions.woocommerce_order_id');
            if (request()->only_woocommerce_sells) {
                $sells->whereNotNull('transactions.woocommerce_order_id');
            }
        }
        
        $sells->groupBy('transactions.id');
        
        $result = $sells->get();
        $dueSum = $result->sum(function($row){
            $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

            if (!empty($discount) && $row->discount_type == 'percentage') {
                $discount = $row->total_before_tax * ($discount / 100);
            }
            
            return ($row->final_total + $discount);
        });
        return $dueSum;
    }


    public function getAvgGpData($business_id,$start_date,$end_date){
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
        $with = [];
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $sells = $this->transactionUtil->getListSells_New($business_id);
        $myquery = $this->transactionUtil->getListSells_New1($business_id);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $myquery .= ' AND transactions.location_id = "'.$permitted_locations.'" '; 
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $myquery .= ' AND transactions.created_by = "'.request()->session()->get('user.id').'" ';
            $sells->where('transactions.created_by', request()->session()->get('user.id'));
        }
    
        if (!empty($start_date) && !empty($end_date)) {
            $myquery .= 'AND transactions.transaction_date BETWEEN "'.$start_date.'" AND "'.$end_date.'" '; 
            $sells->whereDate('transactions.transaction_date', '>=', $start_date)
                        ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if ($is_woocommerce) {
            $addSelect = 'transactions.woocommerce_order_id'; 
            $sells->addSelect('transactions.woocommerce_order_id');
            if (request()->only_woocommerce_sells) {
                $myquery .= ' AND transactions.woocommerce_order_id IS NOT NULL '; 
                $sells->whereNotNull('transactions.woocommerce_order_id');
            }
        }

        $sells->groupBy('transactions.id');

        $with[] = 'payment_lines';
        if (!empty($with)) {
            $sells->with($with);
        }

        //$business_details = $this->businessUtil->getDetails($business_id);
        if ($this->businessUtil->isModuleEnabled('subscription')) {
            $addSelect = 'transactions.is_recurring, transactions.recur_parent_id '; 
            $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
        }
        
        $myquery .= ' GROUP BY transactions.id,SR.final_total,SR.id';
        $result = $sells->get();
        
        $dueSum = $result->sum(function($row){
            $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
            $data =  DB::table('transaction_sell_lines')
                        ->select(DB::raw("(1-((sum(purchase_price*quantity))/(sum(unit_price_inc_tax*quantity)-$discount)))*100 as GP"))
                        ->where('transaction_id',$row->id)->first();
            $gross_profit = round($data->GP,2);
                        $gp = $gross_profit.'%';
            return $gross_profit;
        });
        $avg = count($result);
        if($avg!=0){
            return ($dueSum/$avg);
        }
        return $dueSum;
           
    }

    public function SalesChart()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('dashboard.data')) {
            return view('home.index_new');
        }

        $start = request()->start;
        $end = request()->end;

        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);


        $sell = DB::table('transactions as t')
            ->where('type', 'sell')
            ->where('status', 'final')
            ->select(
                DB::raw('DATE(t.transaction_date) as trdate'),
                // DB::raw('SUM(tr.unit_price * tr.quantity) as sales')
                DB::raw('SUM(final_total) as sales')
            )
            // ->leftJoin('transaction_sell_lines as tr', 't.id', '=', 'tr.transaction_id')
            ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();
            
         $sell_paid =  DB::table('transactions as t')
            ->where('type', 'sell')
            ->where('status', 'final')
            ->select(
                DB::raw('SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) as sell_paid'),
                DB::raw('DATE(t.transaction_date) as trdate')
                )
            ->leftJoin('transaction_payments as tp', 't.id', '=', 'tp.transaction_id')
            ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();
            
        

         $dates = [];
            for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
                array_push($dates, $i->format('Y-m-d'));
            }
    
            $all_sell = [];
            $n = 0;
            $m = 0;
            $all_sell_paid = [];
            foreach ($dates as $date) {
    
                if (in_array($date, array_column($sell->toArray(), 'trdate'))) {
                    array_push($all_sell, $sell[$n]->sales ?? 0);
                    $n++;
                } else {
                    array_push($all_sell, 0);
                }
    
                if (in_array($date, array_column($sell_paid->toArray(), 'trdate'))) {
                    array_push($all_sell_paid, $sell_paid[$m]->sell_paid ?? 0);
                    $m++;
                } else {
                    array_push($all_sell_paid, 0);
                }
            }
            
            $all_sell_due = [];
            foreach ($dates as $key => $date) {
                array_push($all_sell_due, $all_sell[$key] - $all_sell_paid[$key] ?? 0);
            }
    
            $mainChart = [
                $all_sell,
                $all_sell_due,
                $dates
            ];
            return $mainChart;
    }

    public function PurchaseDueChart()
    {
        
        if (!auth()->user()->can('dashboard.data')) {
            return view('home.index_new');
        }

        $start = request()->start;
        $end = request()->end;

        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);

        $purchase = DB::table('transactions as t')
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->select(
                DB::raw('DATE(t.transaction_date) as trdate'),
                // DB::raw('SUM(tr.purchase_price * tr.quantity) as purchase_amt')
                DB::raw('SUM(final_total) as purchase_amt')
            )
            // ->leftJoin('purchase_lines as tr', 't.id', '=', 'tr.transaction_id')
            ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();

        $purchase_paid = DB::table('transactions as t')
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->select(
                DB::raw('SUM(tp.amount) as purchase_paid'),
                DB::raw('DATE(t.transaction_date) as trdate')
                // DB::raw('DATE(tp.created_at) AS trdate'),
                // DB::raw('COALESCE(SUM(tp.amount),0) AS purchase_paid')

            )
            ->leftJoin('transaction_payments as tp', 't.id', '=', 'tp.transaction_id')
            ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();

        $dates = [];
        for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
            array_push($dates, $i->format('Y-m-d'));
        }

        $all_purchase = [];
        $n = 0;
        $m = 0;
        $all_purchase_paid = [];
        foreach ($dates as $date) {

            if (in_array($date, array_column($purchase->toArray(), 'trdate'))) {
                array_push($all_purchase, $purchase[$n]->purchase_amt ?? 0);
                $n++;
            } else {
                array_push($all_purchase, 0);
            }

            if (in_array($date, array_column($purchase_paid->toArray(), 'trdate'))) {
                array_push($all_purchase_paid, $purchase_paid[$m]->purchase_paid ?? 0);
                $m++;
            } else {
                array_push($all_purchase_paid, 0);
            }
        }

        $all_purchase_due = [];
        foreach ($dates as $key => $date) {
            array_push($all_purchase_due, $all_purchase[$key] - $all_purchase_paid[$key] ?? 0);
        }

        $mainChart = [
            $all_purchase,
            $all_purchase_due,
            $dates
        ];
        return $mainChart;
    }
    
    public function GetTopCustomer(){
         $start = '2022-01-01';
        $end = '2022-12-31';

        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);

      $mainChart = DB::table('transactions as t')
            ->select(DB::raw('DATE(t.transaction_date) as trdate'),DB::raw('SUM(tr.unit_price * tr.quantity) as sales'))
            ->leftJoin('transaction_sell_lines as tr','t.id','=','tr.transaction_id')
            // ->where('product_id',$id)
            ->where('t.transaction_date','>=',$dtbefore->format('Y-m-d'))
            ->where('t.transaction_date','<=',$dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get()->toArray();
                    
            
        return $mainChart;
    }
}
