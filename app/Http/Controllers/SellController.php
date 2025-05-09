<?php

namespace App\Http\Controllers;

use App\Models\NeatTxnLog;
use App\Models\NeatTxnProLog;
use App\Models\NeatTxnInfoLogs;

use App\Account;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Category;
use App\CustomerGroup;
use App\InvoiceScheme;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\TransactionPayment;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Warranty;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Delinvoicelog;
use App\Helpers\TransactionHelper;
use App\Models\SmartSyncValue;
use App\PickPackStartLog;
use App\Product;
use Illuminate\Support\Facades\Auth;
use App\RecalculateLog;
use Illuminate\Support\Facades\Log;

class SellController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $contactUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $productUtil;


    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;

        $this->dummyPaymentLine = ['method' => '', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();
            $sells = $this->transactionUtil->getListSells($business_id);
            //$sells = $this->transactionUtil->getListSellsOptimize($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            //Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
                //$sells->where('transactions.created_by', request()->session()->get('user.id'));
                $sells->where(function ($q) {
                    $q->where('transactions.created_by', request()->session()->get('user.id'))
                    ->orWhere('contacts.sales_rep',request()->session()->get('user.id'));
                });
            }

            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $sells->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                    ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $sells->where('transactions.is_direct_sale', 0);
                    $sells->whereNull('transactions.sub_type');
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if ($is_woocommerce) {
                $sells->addSelect('transactions.woocommerce_order_id');
                if (request()->only_woocommerce_sells) {
                    $sells->whereNotNull('transactions.woocommerce_order_id');
                }
            }

            if (request()->only_subscriptions) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.recur_parent_id')
                        ->orWhere('transactions.is_recurring', 1);
                });
            }

            if (!empty(request()->list_for) && request()->list_for == 'service_staff_report') {
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (!empty(request()->res_waiter_id)) {
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (!empty(request()->input('sub_type'))) {
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (!empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (!empty(request()->input('sales_cmsn_agnt'))) {
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (!empty(request()->input('service_staffs'))) {
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }
            $only_shipments = request()->only_shipments == 'true' ? true : false;
            if ($only_shipments && auth()->user()->can('access_shipping')) {
                $sells->whereNotNull('transactions.shipping_status');
            }

            if (!empty(request()->input('shipping_status'))) {
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            if (!empty(request()->input('order_status'))) {

                $order_status_filter = request()->input('order_status');

                if($order_status_filter == '1'){
                    $sells->where('transactions.order_picking_status', '=', '2' )
                          ->where('transactions.order_packing_status', '=', '0' );
                    // $order_status = 'Picking Completed';
                }elseif($order_status_filter == '2'){
                    $sells->where('transactions.order_packing_status', '=','2' )
                        ->where('transactions.order_picking_status', '=','2' );
                    //     $order_status = 'Packing Completed';
                }elseif($order_status_filter == '3'){
                    $sells->where('transactions.order_packing_status', '=','1' )
                            ->where('transactions.order_picking_status', '=','2' );
                }elseif($order_status_filter == '4'){
                    // $sells->where('transactions.order_picking_status', '!=' , '2')->where('transactions.order_packing_status', '!=' , '1');

                    $sells->where('transactions.order_picking_status', '=','0' )
                        ->where('transactions.order_packing_status', '=','0' );
                }elseif($order_status_filter == '5'){
                    $sells->where('transactions.order_picking_status', '=','1' );
                }elseif($order_status_filter == '6'){
                    $sells->where('transactions.order_picking_status', '=','3' );
                }

            }

            if (!empty(request()->input('tax_rates'))) {
                $sells->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
                // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
                 $tax_rates_id = request()->input('tax_rates');
                $sells->where(function ($query) use($tax_rates_id) {
                    $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                          ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
                });
            }

            $sells->groupBy('transactions.id');

            if (!empty(request()->suspended)) {
                $transaction_sub_type = request()->get('transaction_sub_type');
                if (!empty($transaction_sub_type)) {
                    $sells->where('transactions.sub_type', $transaction_sub_type);
                } else {
                    $sells->where('transactions.sub_type', null);
                }

                $with = ['sell_lines'];

                if ($is_tables_enabled) {
                    $with[] = 'table';
                }

                if ($is_service_staff_enabled) {
                    $with[] = 'service_staff';
                }

                $sales = $sells->where('transactions.is_suspend', 1)
                            ->with($with)
                            ->addSelect('transactions.is_suspend', 'transactions.res_table_id', 'transactions.res_waiter_id', 'transactions.additional_notes')
                            ->get();

                return view('sale_pos.partials.suspended_sales_modal')->with(compact('sales', 'is_tables_enabled', 'is_service_staff_enabled', 'transaction_sub_type'));
            }

            $with[] = 'payment_lines';
            if (!empty($with)) {
                $sells->with($with);
            }

            //$business_details = $this->businessUtil->getDetails($business_id);
            if ($this->businessUtil->isModuleEnabled('subscription')) {
                $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            }

            if(empty(request()->input('product_category')) || request()->input('product_category') == 'all'){
                $filtered_sells = $sells;
            } else {
                $category_id = request()->input('product_category');
                $filtered_sells = [];
                $sells_arr = $sells->get();
                foreach ($sells_arr as  $value) {
                    $transaction_id = $value->id;
                    $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                    foreach ($transaction_sells as $sell) {
                        if($sell->product_single->category_single && $sell->product_single->category_single->id == $category_id){
                            array_push($filtered_sells, $value);
                            break;
                        }
                    }
                }
            }
            if(auth()->user()->id == 6){
                // return 0;
            }
            $datatable = Datatables::of($filtered_sells)
                ->addColumn(
                    'action',
                    function ($row) use ($only_shipments) {
                        // $html = '<div class="btn-group">
                        //             <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                        //                 data-toggle="dropdown" aria-expanded="false">' .
                        //                 __("messages.actions") .
                        //                 '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        //                 </span>
                        //             </button>
                        //             <ul class="dropdown-menu dropdown-menu-left" role="menu">' ;

                        if (!$only_shipments) {
                            $html = '<div class="btn-group">
                                        <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                            data-toggle="dropdown" aria-expanded="false">' .
                                            __("messages.actions") .
                                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-left" role="menu">' ;
                        }
                        else{
                                $html = '<div class="btn-group">
                                        <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                            data-toggle="dropdown" aria-expanded="false">' .
                                            __("messages.actions") .
                                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">' ;
                        }
                        if (auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access") || auth()->user()->can("view_own_sell_only")) {
                            $html .= '<li><a href="#" data-href="' . action("SellController@show", [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
                        }
                        if (!$only_shipments) {

                            $user_role = Auth::user()->getRoleNameAttribute();
                            if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Administration#' . auth()->user()->business_id)){
                                    if ($row->is_direct_sale == 0 ) {
                                        if (auth()->user()->can("sell.update")) {
                                            $html .= '<li><a target="_blank" href="' . action('SellPosController@edit', [$row->id]) . '"><i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                                        }
                                    } else {
                                        if (auth()->user()->can("direct_sell.access")) {
                                            $html .= '<li><a target="_blank" href="' . action('SellController@edit', [$row->id]) . '"><i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                                        }
                                    }
                            }
                            elseif($row->order_packing_status == '2' && $row->order_picking_status == '2' ){

                            }else{
                                 //if($row->order_picking_status == 0 || $row->order_packing_status == 2){
                                    if ($row->is_direct_sale == 0 ) {
                                        if (auth()->user()->can("sell.update")) {
                                            $html .= '<li><a target="_blank" href="' . action('SellPosController@edit', [$row->id]) . '"><i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                                        }
                                    } else {
                                        if (auth()->user()->can("direct_sell.access")) {
                                            $html .= '<li><a target="_blank" href="' . action('SellController@edit', [$row->id]) . '"><i class="fas fa-edit"></i> ' . __("messages.edit") . '</a></li>';
                                        }
                                    }
                                //}

                            }

                            if (auth()->user()->can("direct_sell.delete") || auth()->user()->can("sell.delete")) {
                                $html .= '<li><a href="' . action('SellPosController@destroy', [$row->id]) . '" class="delete-sale"><i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                            }
                        }
                        if (auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access")) {
                            $html .= '<li><a target="_blank" href="' . route('invoice.smart', ['id' => $row->id]) . '"><i class="fas fa-file-invoice"></i> ' . __("Open Invoice") . '</a></li>';

                            $html .= ' <li><a target="_blank" href="' . action('SellPosController@packing_slip_blank_without_price', [$row->id]) . '"><i class="fas fa-file-invoice"></i> ' . __("Packing Slip") . '</a></li>';
                            $html .= ' <li><a target="_blank" href="' . action('SellPosController@packing_slip_blank', [$row->id]) . '"><i class="fas fa-file-invoice"></i> ' . __("Packing Slip w Price") . '</a></li>';

                            // $html .= '<li><a target="_blank" href="' . action('SellPosController@open_invoice', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Open Invoice old") . '</a></li> ';
                                // <li><a target="_blank" href="' . action('SellPosController@packing_slip', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Packing Slip") . '</a></li>
                                // <li><a target="_blank" href="' . action('SellPosController@blank_slip', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Blank Slip") . '</a></li>
                                // <li><a target="_blank" href="' . action('SellPosController@OBInvoice', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("OB Invoice") . '</a></li>
                            // $roles = auth()->user()->roles()->whereIn('id',[9,15])->get()->toArray();
                            // if(!empty($roles) && count($roles) > 0){
                                // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "new_sale"]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("Email Invoice") . '</a></li>';
                            // }
                            if(auth()->user()->id == 6 || auth()->user()->id == 85 || auth()->user()->id == 86){
                                // $html .= '<li><a target="_blank" href="' . route('invoice.smart', $row->id) . '">' . __("✨ Invoice v2") . '</a></li>';
                                $html .= '<li><a target="_blank" href="' . route('invoice.smart', ['id' => $row->id, 'preview' => 1]) . '"><i class="fas fa-file-invoice"></i> ' . __("Print Invoice") . '</a></li>';
                                $html .= '<li><a target="_blank" href="' . route('invoice.smart', ['id' => $row->id, 'preview' => 1, 'box' => 'no']) . '"><i class="fas fa-file-invoice"></i> ' . __("Print Invoice (No Box)") . '</a></li>';
                            }

                            // if(auth()->user()->id == 6){

                            // }

                        }
                        $html .= '<li class="divider"></li>';
                        if (auth()->user()->can("access_shipping")) {
                            $html .= '<li><a href="#" data-href="' . action('SellController@editShipping', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-truck" aria-hidden="true"></i>' . __("lang_v1.edit_shipping") . '</a></li>';
                        }
                        if (!$only_shipments) {

                            if ($row->payment_status != "paid" && (auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) && auth()->user()->can("sell.payments")) {
                                $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __("purchase.add_payment") . '</a></li>';
                            }

                            $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __("purchase.view_payments") . '</a></li>';

                            if (auth()->user()->can("sell.create")) {
                                $html .= '<li><a href="' . action('SellController@duplicateSellCreate', [$row->id]) . '"><i class="fas fa-copy"></i> ' . __("lang_v1.duplicate_sell") . '</a></li>

                                <li style="display:none;" ><a href="' . action('SellReturnController@add', [$row->id]) . '"><i class="fas fa-undo"></i> ' . __("lang_v1.sell_return") . '</a></li>

                                ';
                                // <li><a href="' . action('SellPosController@showInvoiceUrl', [$row->id]) . '" class="view_invoice_url"><i class="fas fa-eye"></i> ' . __("lang_v1.view_invoice_url") . '</a></li>

                            }

                            $html .= '';
				            $html .= ' <li><a href="'.action('SellPosController@driverinvoice', [$row->id]).'" ><i class="fa fa-file-invoice"></i> ' . __("Driver Invoice") . '</a></li>';

                            // $roles = auth()->user()->roles()->whereIn('id',[9,15])->get()->toArray();
                            // if(!empty($roles) && count($roles) > 0){
                            //     $html .= '<li><a target="_blank" href="' . action('SellPosController@invoicegen', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Print Invoice") . '</a></li>';
                            // }
                            // $html .= ' <li><a target="_blank" href="' . action('SellPosController@allInvoice') . '"><i class="fas fa-eye"></i> ' . __("All Invoice") . '</a></li>';

                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn(
                        'shipping_details',
                        function($row){
                            if($row->shipping_details == "[object Object]"){
                                return "";
                            }
                            else{
                                return $row->shipping_details;
                            }
                        }
                    )
                ->editColumn(
                    'p_status',
                    function($row){
                        if( $row->p_status  ==  'ask_for_payment_before_ship'){
                            return 'Ask For Payment Before Shipping';
                        }elseif($row->p_status  == 'ok_to_ship'){
                            return 'Okay to Deliver/Ship (Payment Confirmed)';
                        }else{
                            return 'Ask In The Office';
                        }
                    }
                )
                 ->editColumn(
                    'final_total',
                    function($row){
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        $final_total =  $row->final_total + $discount;
                        return  '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="'. $final_total.'">'. $final_total.'</span>';
                    }
                )
                ->editColumn(
                    'tax_amount',
                    '<span class="display_currency total-tax" data-currency_symbol="true" data-orig-value="{{$tax_amount}}">{{$tax_amount}}</span>'
                )
                ->addColumn(
                    'total_gp',
                     function($row){

                         $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
                         if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        try{
                            $check_data =  DB::table('transaction_sell_lines')
                                    ->select(DB::raw("sum(purchase_price*quantity) as cost"))
                                    ->where('transaction_id',$row->id)->first();

                            $divisable = $row->total_before_tax - $discount - $check_data->cost;
                            $divider = $row->total_before_tax - $discount;
                            if($divider == 0){
                                $divider = 1;
                            }
                            $new_gp = round((($divisable) * 100)/($divider),2)."%";

                            return  '<span class="total_gross_profit" data-orig-value="'.$new_gp.'">'.$new_gp.'</span>';
                        }
                        catch(Exception $e){
                            if (!empty($discount) && $row->discount_type == 'percentage') {
                                $discount = $row->total_before_tax * ($discount / 100);
                            }
                            $total_sell = $row->total_sell - $discount;
                            $total_cost = $row->total_cost;
                            if($total_cost > 0 && $total_sell > 0){
                                $gross_profit = (1 - ($total_cost / $total_sell))*100;
                                $gross_profit = round($gross_profit,2);
                                $gp = $gross_profit.'%';
                            }else{
                                $gross_profit = 0;
                                $gp = '0%';
                            }

                            return  '<span class="total_gross_profit" data-orig-value="'. $gross_profit.'">'. $gp.'</span>';
                        }
                    }
                )
                ->editColumn(
                    'total_paid',
                    '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="{{$total_paid}}">{{$total_paid}}</span>'
                )
                ->editColumn(
                    'shipping_charges',
                    '<span class="display_currency shipping-charges" data-currency_symbol="true" data-orig-value="{{$shipping_charges}}">{{$shipping_charges}}</span>'
                )
                ->editColumn(
                    'total_before_tax',
                    '<span class="display_currency total_before_tax" data-currency_symbol="true" data-orig-value="{{$total_before_tax}}">{{$total_before_tax}}</span>'
                )
                // ->editColumn(
                //     'added_by',
                //     function ($row) {
                //         return $this->getUserName($row->added_by);
                //     }
                // )
                // ->editColumn(
                //     'packed_by',
                //     function ($row) {
                //         return $this->getUserName($row->packed_by);
                //     }
                // )
                // ->editColumn(
                //     'picked_by',
                //     function ($row) {
                //         return $this->getUserName($row->picked_by);
                //     }
                // )
                // ->editColumn(
                //     'waiter',
                //     function ($row) {
                //         return $this->getUserName($row->waiter);
                //     }
                // )

                ->editColumn(
                    'discount_amount',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        return '<span class="display_currency total-discount" data-currency_symbol="true" data-orig-value="' . $discount . '">' . $discount . '</span>';
                    }
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
                    }
                )
                ->editColumn(
                    'types_of_service_name',
                    '<span class="service-type-label" data-orig-value="{{$types_of_service_name}}" data-status-name="{{$types_of_service_name}}">{{$types_of_service_name}}</span>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining =  $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $total_remaining . '">' . $total_remaining . '</span>';


                    return $total_remaining_html;
                })
                ->addColumn('return_due', function ($row) {
                    $return_due_html = '';
                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action("TransactionPaymentController@show", [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="display_currency sell_return_due" data-currency_symbol="true" data-orig-value="' . $return_due . '">' . $return_due . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->addColumn('balance_due', function ($row) use($business_id) {

                    // $contact = Contact::where('contacts.id', $row->contact_id_new)
                    //     ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                    //     ->leftjoin('users as sale','sale.id','=','contacts.sales_rep')
                    //     ->leftjoin('users as account','account.id','=','contacts.account_rep')
                    //     ->leftjoin('users as createdfor','createdfor.id','=','contacts.created_by')
                    //     ->with(['business'])
                    //     ->select(
                    //           DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    //           DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    //           DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    //           DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                    //           DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    //           DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                    //           DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                    //           DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    //           DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    //         'contacts.*'
                    //     )->first();

                    // $prev_payments_sum = TransactionPayment::leftJoin(
                    //                         'transactions as t',
                    //                         'transaction_payments.transaction_id',
                    //                         '=',
                    //                         't.id'
                    //                     )
                    //                         ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
                    //                         ->where('transaction_payments.payment_for', $row->contact_id_new)
                    //                         ->whereDate('paid_on', '<', '2021-01-01')->select(DB::raw("SUM(transaction_payments.amount) as total_paid"))
                    //                         ->first();


                    // // $this->__paymentQuery($row->contact_id_new, '2021-01-01')
                    // //     ->select(DB::raw("SUM(transaction_payments.amount) as total_paid"))
                    // //     ->first();

                    // $total_prev_invoice = number_format (($contact->total_purchase + $contact->total_invoice - $contact->invoice_received -  $contact->total_sell_return + $contact->sell_return_paid -  $contact->total_purchase_return ), 2, '.', '') - $contact->balance;

                    // $balance_due=  $contact->opening_balance - $contact->opening_balance_paid + $total_prev_invoice - $prev_payments_sum->total_paid;

                    // $open_color = 'green';
                    // if($balance_due > 0){
                    //     $open_color = 'red';
                    // }
                    // return  '<span class="display_currency " style="white-space:nowrap;font-weight:bolder;color:'.$open_color.';" data-currency_symbol="true" data-orig-value="'. $balance_due.'">'.'$ '. $balance_due.'</span>';

                    $qry = $this->contactUtil->getContactInfo($business_id, $row->contact_id_new);


                    $balance_due=  $qry->opening_balance - $qry->opening_balance_paid + $qry->total_invoice - $qry->invoice_received - $qry->balance - $qry->total_sell_return + $qry->sell_return_paid;
                    $opening = ($qry->total_invoice - $qry->invoice_received) - $qry->balance;
                    return '<span class="display_currency contact_due" data-currency_symbol="true"  data-highlight=true  data-orig-value="' .($balance_due). '" >' .($balance_due). '</span>';

                })
                ->addColumn('picked_by', function ($row) {
                    return $row->picked_by;
                })
                ->addColumn('packed_by', function ($row) {
                    return $row->packed_by;
                })
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    // $edited_log = Delinvoicelog::where('transaction_id',$row->id)
                    //             ->where('description', 'edited')->count();
                    // if(!empty($edited_log))
                    // {
                    //     $invoice_no .= ' <i class="fa fa-edit text-primary no-print">logs</i>';
                    // }
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    // if (!empty($row->return_exists)) {
                    //     $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') .'"><i class="fas fa-undo"></i></small>';
                    // }
                    // if (!empty($row->is_recurring)) {
                    //     $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    // }

                    // if (!empty($row->recur_parent_id)) {
                    //     $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    // }

                    return $invoice_no;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = !empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action('SellController@editShipping', [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color .'">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

                    return $status;
                })
                ->editColumn('order_status', function ($row) {

                    if($row->order_picking_status == '2' && $row->order_packing_status == '0'){
                        $order_status = 'Picking Completed';
                        $bg_color = 'bg-orange';
                    }elseif($row->order_packing_status == '2' && $row->order_picking_status == '2'){
                        $order_status = 'Packing Completed';
                        $bg_color = 'bg-green';
                    }elseif($row->order_packing_status == '1' && $row->order_picking_status == '2'){
                        $order_status = 'Packing Started';
                        $bg_color = 'bg-orange';
                    }elseif($row->order_picking_status == '0'){
                        $order_status = 'Received';
                        $bg_color = 'bg-blue';
                    }elseif($row->order_picking_status == '1'){
                        $order_status = 'Picking Started';
                        $bg_color = 'bg-orange';
                    }elseif($row->order_picking_status == '3'){
                        $order_status = 'Cancel';
                        $bg_color = 'bg-red';
                    }else{
                        $order_status = 'Received';
                        $bg_color = 'bg-blue';
                    }

                    $status = '<a href="#"><span class="label ' . $bg_color .'">' . $order_status . '</span></a>';

                    return $status;
                })
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]];
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

                    return $html;
                })
                ->addColumn('bulk_sms', function ($row) use ($business_id) {
                    $transaction = Transaction::where('business_id', $business_id)->with(['contact'])->find($row->id);
                    $contact_mobile  = $transaction->contact->mobile;
                    return  '<input type="checkbox"  class="row-select" value="' . $row->id .'" data-mobile = "'. $contact_mobile .'">' ;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view") || auth()->user()->can("view_own_sell_only")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }]);

            $rawColumns = ['final_total', 'p_status','bulk_sms','shipping_charges', 'action', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status','order_status', 'types_of_service_name', 'payment_methods','balance_due', 'return_due', 'picked_by','total_gp'];

            return $datatable->rawColumns($rawColumns)
                      ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        // $customers = Contact::customersDropdown($business_id, false);
        $customers = Contact::customersCompanyDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $orderStatuses =  ['All' ,'Picking Completed' , 'Packing Completed' , 'Packing Started' ,'Received' , 'Picking Started' , 'Cancel'  ];
        // $this->transactionUtil->orderStatuses();

        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $tax_rates = TaxRate::join('categories as cat', 'cat.id', '=', 'tax_rates.category')->select(DB::raw("CONCAT(tax_rates.name,' - ',cat.name) AS name"),'tax_rates.id')->orderBy('name')->pluck('name','id');
        return view('sell.index')
        ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses','categories','tax_rates','orderStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellController@index'));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        //Types of service
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $users = User::where('business_id', $business_id)->get();

        $status = request()->get('status', '');

        return view('sell.create')
            ->with(compact(
                'business_details',
                'taxes',
                'walk_in_customer',
                'business_locations',
                'bl_attributes',
                'default_location',
                'commission_agent',
                'types',
                'customer_groups',
                'payment_line',
                'payment_types',
                'price_groups',
                'default_datetime',
                'pos_settings',
                'invoice_schemes',
                'default_invoice_schemes',
                'types_of_service',
                'accounts',
                'shipping_statuses',
                'status',
                'users'
            ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $query = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(['contact', 'sell_lines' => function ($q) {
                        $q->whereNull('parent_sell_line_id');
                    },'sell_lines.product', 'sell_lines.product.unit', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'payment_lines', 'sell_lines.modifiers', 'sell_lines.lot_details', 'tax', 'sell_lines.sub_unit', 'table', 'service_staff', 'sell_lines.service_staff', 'types_of_service', 'sell_lines.warranties']);

        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }

        $sell = $query->firstOrFail();

        foreach ($sell->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }
        }

        $payment_types = $this->transactionUtil->payment_types($sell->location_id, true);
        $order_taxes = [];
        if (!empty($sell->tax)) {
            if ($sell->tax->is_tax_group) {
                $order_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->tax, $sell->tax_amount));
            } else {
                $order_taxes[$sell->tax->name] = $sell->tax_amount;
            }
        }

        $business_details = $this->businessUtil->getDetails($business_id);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $shipping_status_colors = $this->shipping_status_colors;
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;

        //added by developer 1
        $activity_logs = Delinvoicelog::join('users','users.id','=','delinvoicelogs.user_id')
              ->where('transaction_id',$id)
              ->whereIn('description', ['added', 'edited', 'send_email', 'send_whatsapp'])
                ->select('delinvoicelogs.created_at as datetime',
                    'delinvoicelogs.message as message',
                    'delinvoicelogs.description as description',
                    'users.first_name as first_name'
                )
                ->get();


        $pickpacklog = PickPackStartLog::join('users','users.id','=','pickpackstartlog.user_id')
                ->where('transaction_id',$id)
                ->select('pickpackstartlog.created_at as datetime',
                    'pickpackstartlog.type as type',
                    'users.first_name as first_name'
                )
                ->get();

                $deleted_product = new Product();
                $deleted_product->name = "Product Name Not Found";

         $recalculate_log = Recalculatelog::join('users','users.id','=','recalculatelog.user_id')
                            ->where('transaction_id',$id)
                            ->select(
                                'recalculatelog.*',
                                'users.first_name as first_name'
                            )
                            ->get();

        $custom_jadoo_popup = SmartSyncValue::getSmartValue('custom_jadoo_popup');

        // if(auth()->user()->id == 6){
        //     $custom_jadoo_popup = 0;
        // }

        if(
            TransactionSellline::where('transaction_id', $sell->id)->where('pos_line_tax_amount','>',0)->exists()
            || $sell->contact->customer_group_id == 70
        ){
            $custom_jadoo_popup = 0;
        }

        $view_name = 'sale_pos.show';

        // if(auth()->user()->id == 6){
            $view_name = 'sale_pos.show_new';
        // }

        $jadoo_products = [];
        if(!empty($custom_jadoo_popup) && empty(request()->input('downloadsell'))){
            $jadoo_products = $this->productUtil->GetJadooProductlist();
            $view_name = 'sale_pos.show_jadoo';
        }

        $user = User::find($sell->created_by);
        $picker = User::find($sell->picked_by ?? 0);
        $packer = User::find($sell->packed_by ?? 0);

        $sell->picked_by_name = $picker->first_name ?? '--';
        $sell->packed_by_name = $packer->first_name ?? '--';

        $txnLogs = NeatTxnLog::with('user')->where('txn_id', $id)->latest()->get();
        $productLogs = NeatTxnProLog::whereIn('neat_txn_id', $txnLogs->pluck('id'))->get();
        $infoLogs = NeatTxnInfoLogs::whereIn('neat_txn_id', $txnLogs->pluck('id'))->with('contact', 'newcontact')->get();
        $filteredTxnLogs = $txnLogs->filter(function($txnLog) use ($productLogs, $infoLogs) {
            return $productLogs->where('neat_txn_id', $txnLog->id)->isNotEmpty() || $infoLogs->where('neat_txn_id', $txnLog->id)->isNotEmpty();
        });
        $txnLogs = $filteredTxnLogs;
        $productLogs = $productLogs->whereIn('neat_txn_id', $txnLogs->pluck('id'));
        $infoLogs = $infoLogs->whereIn('neat_txn_id', $txnLogs->pluck('id'));

        return view($view_name)
            ->with(compact(
                'user',
                'jadoo_products',
                'deleted_product',
                'taxes',
                'sell',
                'payment_types',
                'order_taxes',
                'pos_settings',
                'shipping_statuses',
                'shipping_status_colors',
                'is_warranty_enabled',
                'activity_logs',
                'pickpacklog',
                'recalculate_log',
                'txnLogs',
                'productLogs',
                'infoLogs'
            ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                    'msg' => __('lang_v1.return_exist')]);
        }

        $business_id = request()->session()->get('user.business_id');

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
                            ->with(['price_group', 'types_of_service'])
                            ->where('type', 'sell')
                            ->findorfail($id);

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::
                        join(
                            'products AS p',
                            'transaction_sell_lines.product_id',
                            '=',
                            'p.id'
                        )
                        ->join(
                            'variations AS variations',
                            'transaction_sell_lines.variation_id',
                            '=',
                            'variations.id'
                        )
                        ->join(
                            'product_variations AS pv',
                            'variations.product_variation_id',
                            '=',
                            'pv.id'
                        )
                        ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                            $join->on('variations.id', '=', 'vld.variation_id')
                                ->where('vld.location_id', '=', $location_id);
                        })
                        ->leftjoin('units', 'units.id', '=', 'p.unit_id')
                        ->where('transaction_sell_lines.transaction_id', $id)
                        ->with(['warranties'])
                        ->select(
                            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                            'p.id as product_id',
                            'p.enable_stock',
                            'p.name as product_actual_name',
                            'pv.name as product_variation_name',
                            'pv.is_dummy as is_dummy',
                            'variations.name as variation_name',
                            'variations.sub_sku',
                            'p.barcode_type',
                            'p.enable_sr_no',
                            'variations.id as variation_id',
                            'units.short_name as unit',
                            'units.allow_decimal as unit_allow_decimal',
                            'transaction_sell_lines.tax_id as tax_id',
                            'transaction_sell_lines.item_tax as item_tax',
                            'transaction_sell_lines.unit_price as default_sell_price',
                            'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                            'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                            'transaction_sell_lines.id as transaction_sell_lines_id',
                            'transaction_sell_lines.id',
                            'transaction_sell_lines.quantity as quantity_ordered',
                            'transaction_sell_lines.sell_line_note as sell_line_note',
                            'transaction_sell_lines.box_no as box_no',
                            'transaction_sell_lines.lot_no_line_id',
                            'transaction_sell_lines.line_discount_type',
                            'transaction_sell_lines.line_discount_amount',
                            'transaction_sell_lines.res_service_staff_id',
                            'units.id as unit_id',
                            'transaction_sell_lines.sub_unit_id',
                            DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
                        )
                        ->get();
        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                if ($transaction->status != 'final') {
                    $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                    $sell_details[$key]->qty_available = $actual_qty_avlbl;
                    $value->qty_available = $actual_qty_avlbl;
                }

                $sell_details[$key]->formatted_qty_available = $this->transactionUtil->num_f($value->qty_available);
                $lot_numbers = [];
                if (request()->session()->get('business.enable_lot_number') == 1) {
                    $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                    foreach ($lot_number_obj as $lot_number) {
                        //If lot number is selected added ordered quantity to lot quantity available
                        if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                            $lot_number->qty_available += $value->quantity_ordered;
                        }

                        $lot_number->qty_formated = $this->transactionUtil->num_f($lot_number->qty_available);
                        $lot_numbers[] = $lot_number;
                    }
                }
                $sell_details[$key]->lot_numbers = $lot_numbers;

                if (!empty($value->sub_unit_id)) {
                    $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                    $sell_details[$key] = $value;
                }

                $sell_details[$key]->formatted_qty_available = $this->transactionUtil->num_f($value->qty_available);
            }
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $transaction->transaction_date = $this->transactionUtil->format_date($transaction->transaction_date, true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $waiters = null;
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        if ($transaction->status == 'draft') {
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;
        $warranties = $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];

        return view('sell.edit')
            ->with(compact('business_details', 'taxes', 'sell_details', 'transaction', 'commission_agent', 'types', 'customer_groups', 'pos_settings', 'waiters', 'invoice_schemes', 'default_invoice_schemes', 'redeem_details', 'edit_discount', 'edit_price', 'accounts', 'shipping_statuses', 'warranties'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     //
    // }

    /**
     * Display a listing sell drafts.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDrafts()
    {
        if (!auth()->user()->can('list_drafts')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);


        return view('sale_pos.draft')
            ->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Display a listing sell quotations.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuotations()
    {
        if (!auth()->user()->can('list_quotations')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sale_pos.quotations')
                ->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Send the datatable response for draft or quotations.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDraftDatables()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $is_quotation = request()->only('is_quotation', 0);

            $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'draft')
                ->where('is_quotation', $is_quotation)
                ->select(
                    'transactions.id',
                    'transactions.final_total',
                    'transaction_date',
                    'invoice_no',
                    'contacts.name',
                    'contacts.id as contact_id',
                    'bl.name as business_location',
                    'is_direct_sale',
                    'is_quotation',
                    'transactions.invoice_token'

                );

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transaction_date', '>=', $start)
                            ->whereDate('transaction_date', '<=', $end);
            }

            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }

            if ($is_woocommerce) {
                $sells->addSelect('transactions.woocommerce_order_id');
            }

            $sells->groupBy('transactions.id');

            return Datatables::of($sells)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                            data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                            <li>
                            <a href="#" data-href="{{action(\'SellController@show\', [$id])}}" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> @lang("messages.view")</a>
                            </li>
                    @if (auth()->user()->can("edit_drafts") )
                        @if($is_direct_sale == 1)
                            <li>
                            <a target="_blank" href="{{action(\'SellPosController@edit\', [$id])}}"><i class="fas fa-edit"></i>  @lang("messages.edit")</a>
                            </li>
                        @else
                            <li>
                            <a target="_blank" href="{{action(\'SellPosController@edit\', [$id])}}"><i class="fas fa-edit"></i>  @lang("messages.edit")</a>
                            </li>
                        @endif
                    @endif
                    @if($is_quotation)
                        <li>
                            <a href="#" class="print-invoice" data-href="{{route(\'sell.printQuotation\', [$id])}}"><i class="fas fa-print" aria-hidden="true"></i> @lang("messages.print")</a>
                        </li>
                    @else
                    <li><a target="_blank" href="{{ action(\'SellPosController@open_invoice\', [$id]) }}"><i class="fas fa-eye"></i> ' . __("Open Draft") . '</a></li>
                    <li><a target="_blank" href="{{ action(\'SellPosController@invoicegen\', [$id]) }}"><i class="fas fa-eye"></i> ' . __("Print Draft") . '</a></li>
                    <li><a target="_blank" href="{{ action(\'SellPosController@packing_slip_blank\', [$id]) }}"><i class="fas fa-eye"></i> ' . __("Blank Draft") . '</a></li>

                    <!--<li>

                        <a href="#" class="print-invoice" data-href="{{route(\'sell.printInvoice\', [$id])}}"><i class="fas fa-print" aria-hidden="true"></i> @lang("messages.print")</a>
                    </li>-->
                    @endif
                    <li>
                    <a href="{{action(\'SellPosController@destroy\', [$id])}}" class="delete-sale"><i class="fas fa-trash"></i>  @lang("messages.delete")</a>
                    </li>

                    <li><a href="{{action("SellPosController@showInvoiceUrl", [$id])}}" class="view_invoice_url"><i class="fas fa-eye"></i> @lang("lang_v1.view_quote_url")</a></li>
                    <li><a href="#" data-id="{{$id}}"  data-invoice_token="{{$invoice_token}}" data-href="{{ action("NotificationController@getTemplate", ["transaction_id" => $id,"template_for" => "new_quotation"])}}" class="btn-modal send_with_pdf" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("New Draft Notification") . '</a></li>
                    </ul>
                    </div>
                    '
                )
                ->removeColumn('id')
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }

                    return $invoice_no;
                })
                ->editColumn('balance_due', function ($row) {
                        $contact = Contact::where('contacts.id', $row->contact_id)
                        ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                        ->leftjoin('users as sale','sale.id','=','contacts.sales_rep')
                        ->leftjoin('users as account','account.id','=','contacts.account_rep')
                        ->leftjoin('users as createdfor','createdfor.id','=','contacts.created_by')
                        ->with(['business'])
                        ->select(
                            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                            'contacts.*'
                        )->first();
                    $balance_due= $contact->total_invoice - $contact->invoice_received;
                    return  '<span class="display_currency " data-currency_symbol="true" data-orig-value="'. $balance_due.'">'.'$ '. $balance_due.'</span>';

                })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['action', 'invoice_no','balance_due', 'transaction_date'])
                ->make(true);
        }
    }

    /**
     * Creates copy of the requested sale.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicateSell($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->findorfail($id);
            $duplicate_transaction_data = [];
            foreach ($transaction->toArray() as $key => $value) {
                if (!in_array($key, ['id', 'created_at', 'updated_at'])) {
                    $duplicate_transaction_data[$key] = $value;
                }
            }
            $duplicate_transaction_data['status'] = 'draft';
            $duplicate_transaction_data['payment_status'] = null;
            $duplicate_transaction_data['transaction_date'] =  \Carbon::now();
            $duplicate_transaction_data['created_by'] = $user_id;
            $duplicate_transaction_data['invoice_token'] = null;
             $duplicate_transaction_data['order_picking_status'] = 0;
            $duplicate_transaction_data['order_packing_status'] = 0;

            DB::beginTransaction();
            $duplicate_transaction_data['invoice_no'] = $this->transactionUtil->getInvoiceNumber($business_id, 'draft', $duplicate_transaction_data['location_id']);

            //Create duplicate transaction
            $duplicate_transaction = Transaction::create($duplicate_transaction_data);

            //Create duplicate transaction sell lines
            $duplicate_sell_lines_data = [];

            foreach ($transaction->sell_lines as $sell_line) {
                $new_sell_line = [];
                foreach ($sell_line->toArray() as $key => $value) {
                    if (!in_array($key, ['id', 'transaction_id', 'created_at', 'updated_at', 'lot_no_line_id'])) {
                        $new_sell_line[$key] = $value;
                    }
                }

                $duplicate_sell_lines_data[] = $new_sell_line;
            }

            $duplicate_transaction->sell_lines()->createMany($duplicate_sell_lines_data);
             TransactionSellLine::where('transaction_id',$duplicate_transaction->id)->update(['picking_status'=>0,'packing_status'=>0,'is_picked'=>0,'is_packed'=>0]);
            DB::commit();

            $output = ['success' => 0,
                            'msg' => trans("lang_v1.duplicate_sell_created_successfully")
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
        }

        if (!empty($duplicate_transaction)) {
            if ($duplicate_transaction->is_direct_sale == 1) {
                return redirect()->action('SellController@edit', [$duplicate_transaction->id])->with(['status', $output]);
            } else {
                return redirect()->action('SellPosController@edit', [$duplicate_transaction->id])->with(['status', $output]);
            }
        } else {
            abort(404, 'Not Found.');
        }
    }

    /**
     * Shows modal to edit shipping details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editShipping($id)
    {
        if (!auth()->user()->can('access_shipping')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $transaction = Transaction::where('business_id', $business_id)
                                ->findorfail($id);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        return view('sell.partials.edit_shipping')
               ->with(compact('transaction', 'shipping_statuses'));
    }

    /**
     * Update shipping.
     *
     * @param  Request $request, int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateShipping(Request $request, $id)
    {
        if (!auth()->user()->can('access_shipping')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'shipping_details', 'shipping_address',
                'shipping_status', 'delivered_to',
                'shipping_cost','shipping_charges'
            ]);
            $business_id = $request->session()->get('user.business_id');

            $transaction = Transaction::find($id);
            if(isset($transaction)){
                $previous_transaction_record = $transaction->toArray();
                $pro='';

                if($transaction->shipping_charges != $input['shipping_charges']){
                    $pro .= 'Shipping Cost ($'.$this->transactionUtil->num_uf($transaction->shipping_charges).' --> $'.$this->transactionUtil->num_uf($input['shipping_charges']).'), ';
                }

                if($transaction->shipping_details != $input['shipping_details']){
                    $pro .= 'Shipping Details, ';
                }

                if($transaction->shipping_address != $input['shipping_address']){
                    $pro .= 'Shipping Address, ';
                }

                if($transaction->shipping_status != $input['shipping_status']){
                    $old_status = ucwords(str_replace('_', ' ', $transaction->shipping_status));
                    $new_status = ucwords(str_replace('_', ' ', $input['shipping_status']));
                    $pro .= 'Shipping Status ('. $old_status .'  --> '. $new_status .'), ';
                }

                if($transaction->delivered_to != $input['delivered_to']){
                    $pro .= 'Delivered To, ';
                }

                $payments = TransactionPayment::where('transaction_id',$transaction->id)->get();
                $paid_amt = 0;
                foreach($payments as $payment){
                    if($payment->is_return == 0){
                        $paid_amt += $payment->amount;
                    }
                    else{
                        $paid_amt -= $payment->amount;
                    }
                }

                $new_total = $transaction->final_total - $transaction->shipping_charges + $input['shipping_charges'];
                if($new_total > $transaction->final_total && $transaction->payment_status == 'paid'){
                    if($paid_amt < $new_total){
                        $transaction->payment_status = 'partial';
                    }
                }
                if($new_total < $transaction->final_total && $transaction->payment_status == 'partial'){
                    if($paid_amt >= $new_total){
                        $transaction->payment_status = 'paid';
                    }
                }

                // transfer excess amount to advance
                $balance_transfer = false;
                if($new_total < $transaction->final_total && $transaction->payment_status != 'due'){
                    if($paid_amt > $new_total){
                        $balance_transfer = true;
                    }
                }

                $transaction->final_total = $new_total;
                $input['final_total'] = $new_total;
                $transaction->save();
                $transaction->update($input);

                $transactionId = $transaction->id;
                $ntl_id = NeatTxnLog::logActivity($transactionId, 'Edited Shipping');
                NeatTxnInfoLogs::logActivityupdateShipping($ntl_id->id, $input, $transactionId, 'Edited', $previous_transaction_record);

                if($pro!="")
                {
                    $user_id = request()->session()->get('user.id');
                    $this->transactionUtil->Delinvoicelog('edited',$user_id,$id,$pro);
                }

                if($balance_transfer){
                    $bt_status = TransactionHelper::balanceTransfer($transaction->id);
                    Log::info('Balance Transfer Processed for Invoice: ' . $transaction->invoice_no . ' -- ' . $bt_status);
                }

            }
            // $transaction = Transaction::where('business_id', $business_id)
            //                     ->where('id', $id)
            //                     ->update($input);

            $output = ['success' => 1,
                            'msg' => trans("lang_v1.updated_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Display list of shipments.
     *
     * @return \Illuminate\Http\Response
     */
    public function shipments()
    {
        if (!auth()->user()->can('access_shipping')) {
            abort(403, 'Unauthorized action.');
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        return view('sell.shipments')->with(compact('shipping_statuses'))
                ->with(compact('business_locations', 'customers', 'sales_representative', 'is_service_staff_enabled', 'service_staffs'));
    }

    /* POS Duplicate Create */
    public function duplicateSellCreate($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        $transaction = Transaction::where('business_id', $business_id)
                        ->where('type', 'sell')
                        ->findorfail($id);
        $duplicate_transaction_data = [];
        foreach ($transaction->toArray() as $key => $value) {
            if (in_array($key, ['id', 'location_id'])) {
                $duplicate_transaction_data[$key] = $value;
            }
        }
        // $duplicate_transaction_data['invoice_no'] = $this->transactionUtil->getInvoiceNumber($business_id, 'draft', $duplicate_transaction_data['location_id']);

        $output = ['success' => 0,
                            'msg' => trans("lang_v1.duplicate_sell_new_invoice_opened.")
                        ];
        if (!empty($duplicate_transaction_data)) {
            request()->session()->put('duplicate_id', $transaction['id']);
            if(!empty(request()->input('back_order'))){
                return redirect()->action('SellPosController@create',['back_order'=>request()->input('back_order')])->with(['status', $output]);
            }
            else{
                return redirect()->action('SellPosController@create')->with(['status', $output]);
            }
        } else {
            abort(404, 'Not Found.');
        }
    }

    public function customerSales(Request $request, $customer_email)
    {

        if(isset($request->code) && !empty($request->code) && $request->code == 'novx@cloud')
        {
            $customer = Contact::where('email', $customer_email)->first();

            $business = Business::findOrFail($customer->business_id);
            $currency = $business->currency;

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id')->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')->leftJoin('users as au', 'transactions.picked_by', '=', 'au.id')->leftJoin('users as pb', 'transactions.packed_by', '=', 'pb.id')->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')->join(
                'business_locations AS bl', 'transactions.location_id', '=', 'bl.id'
            )->leftJoin(
                'transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id'
            )->leftJoin(
                'types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id'
            )->where('transactions.type', 'sell')->where('transactions.status', 'final')->select(
                'transactions.id', 'transactions.transaction_date', 'transactions.is_direct_sale', 'transactions.invoice_no', 'transactions.invoice_no as invoice_no_text', 'contacts.name', 'contacts.mobile', 'contacts.contact_id', 'transactions.payment_status', 'transactions.final_total', 'transactions.tax_amount', 'transactions.discount_amount', 'transactions.discount_type', 'transactions.total_before_tax', 'transactions.rp_redeemed', 'transactions.rp_redeemed_amount', 'transactions.rp_earned', 'transactions.types_of_service_id', 'transactions.shipping_status', 'transactions.pay_term_number', 'transactions.pay_term_type', 'transactions.additional_notes', 'transactions.order_picking_status', 'transactions.order_packing_status', 'transactions.staff_note', 'transactions.shipping_details', // 'tsl.pos_line_tax_id',
                \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date'), DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"), DB::raw(
                '(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as total_paid'
            ), 'bl.name as business_location', DB::raw('COUNT(SR.id) as return_exists'), DB::raw(
                '(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE
                        TP2.transaction_id=SR.id ) as return_paid'
            ), DB::raw('COALESCE(SR.final_total, 0) as amount_return'), 'SR.id as return_transaction_id', 'tos.name as types_of_service_name', 'transactions.service_custom_field_1',

                DB::raw('sum(1) as total_cost'), DB::raw('sum(1) as total_sell'),

                DB::raw('"0" as total_items'), DB::raw("CONCAT(COALESCE(ss.surname, ''),' ',COALESCE(ss.first_name, ''),' ',COALESCE(ss.last_name,'')) as waiter"), DB::raw("CONCAT(COALESCE(au.surname, ''),' ',COALESCE(au.first_name, ''),' ',COALESCE(au.last_name,'')) as picked_by"), DB::raw("CONCAT(COALESCE(pb.surname, ''),' ',COALESCE(pb.first_name, ''),' ',COALESCE(pb.last_name,'')) as packed_by"), 'tables.name as table_name'
            );
            $sells->where('contacts.id', $customer->id)->groupBy('transactions.id') ;
            $sells = $sells->get();

            /*echo "<pre>";
            print_r($sell_return);
            echo "<pre>";*/
           $sell_return = '';
            return view('sell.customer_sale', compact('sells', 'customer', 'currency','sell_return'));
        }
        else
        {
            $output = [
                'success' => 0,
                'msg' => 'Unauthorized user',
            ];

            return $output;
        }
    }

    public function customerSalesInvoice($id)
    {

        $transaction = Transaction::where('id', $id)->with(
            [
                'business',
                'location',
            ]
        )->first();

        $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;
        $business_id       = $transaction->business_id;
        $transaction       = Transaction::where('business_id', $business_id)->findorfail($id);
        $url               = $this->transactionUtil->getInvoiceUrl($id, $business_id);
        $receipt           = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content', false, true, $invoice_layout_id);

        $title = $transaction->business->name . ' | ' . $transaction->invoice_no;

        return view('sale_pos.partials.show_invoice')->with(compact('receipt', 'title', 'url'));
    }

    public function customerCreditMemo(Request $request)
    {
        if(isset($request->customer_id) && !empty($request->customer_id))
        {
            $customer_id = $request->customer_id;

            $customer = Contact::where('id', $customer_id)->first();

            $business = Business::findOrFail($customer->business_id);
            $currency = $business->currency;
            $sell_return = Transaction::with('contact','location','sell_lines')->where('type','sell_return')->where('contact_id',$customer_id)
            ->leftJoin(
                'transaction_payments AS TP',
                'transactions.id',
                '=',
                'TP.transaction_id'
            )->select(
                'transactions.*',
                'transactions.id as parent_sale_id',
                DB::raw('SUM(TP.amount) as amount_paid')
                // DB::raw('(SUM(transaction_sell_lines.quantity_returned) + SUM(transaction_sell_lines.gar_box_return_qty)) as quantity_returned')
            )->groupBy('transactions.id')
            ->get();


           $due_payment = Transaction::where('type','sell_return')->where('contact_id',$customer_id)->where('payment_status','due')->count();
           $partial_payment =  Transaction::where('type','sell_return')->where('contact_id',$customer_id)->where('payment_status','partial')->count();
           $paid_payment =  Transaction::where('type','sell_return')->where('contact_id',$customer_id)->where('payment_status','paid')->count();

            $total_paid = Transaction::where('type','sell_return')->where('contact_id',$customer_id)->sum('final_total');
            $total_due = Transaction::where('type','sell_return')->where('contact_id',$customer_id)->where('payment_status','due')->sum('final_total');


            return view('sell.customer_creditmemo')
                 ->with(compact('sell_return','due_payment','partial_payment','paid_payment','total_paid','total_due','currency'));
                 exit;
        }
        else
        {
            $output = [
                'success' => 0,
                'msg' => 'Unauthorized user',
            ];

            return $output;
        }
    }

    public function printCreditMemoInvoice(Request $request, $id)
    {
        if (request()->ajax()) {
            try {

                $output = ['success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];

                $transaction = Transaction::where('id', $id)->with(
                    [
                        'business',
                        'location',
                    ]
                )->first();

                $business_id =  $transaction->business_id;

                $transaction = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->first();
                if (empty($transaction)) {
                    return $output;
                }

                $receipt = $this->receiptContentCreditDemo($business_id, $transaction->location_id, $id, 'browser');

                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                $output = ['success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];
            }
            return $output;
        }
    }

    private function receiptContent(
        $business_id, $location_id, $transaction_id, $printer_type = null, $is_package_slip = false, $from_pos_screen = true, $invoice_layout_id = null
    ){
        $output = [
            'is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => [],
        ];


        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        if($from_pos_screen && $location_details->print_receipt_on_invoice != 1)
        {
            return $output;
        }
        //Check if printing of invoice is enabled or not.
        //If enabled, get print type.
        $output['is_enabled'] = true;

        $invoice_layout_id = !empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;
        $invoice_layout    = $this->businessUtil->invoiceLayout($business_id, $location_id, $invoice_layout_id);

        //Check if printer setting is provided.
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details           = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);
        $tax_details               = $this->transactionUtil->getTaxDetails($transaction_id);
        $currency_details          = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator,
        ];
        $receipt_details->currency = $currency_details;

        if($is_package_slip)
        {
            $output['html_content']  = view('sale_pos.receipts.packing_slip', compact('receipt_details', 'tax_details'))->render();
            $output['html_content1'] = view('sale_pos.receipts.driver_invoice', compact('receipt_details', 'tax_details'))->render();
            $output['html_content2'] = view('sale_pos.receipts.classic', compact('receipt_details', 'tax_details'))->render();
            $output['html_content3'] = view('sale_pos.receipts.invoicegen', compact('receipt_details', 'tax_details'))->render();
            $output['html_content4'] = view('sale_pos.receipts.packing_gen', compact('receipt_details', 'tax_details'))->render();


            return $output;
        }
        //If print type browser - return the content, printer - return printer config data, and invoice format config
        if($receipt_printer_type == 'printer')
        {
            $output['print_type']     = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data']           = $receipt_details;
        }
        else
        {
            $layout = !empty($receipt_details->design) ? 'sale_pos.receipts.' . $receipt_details->design : 'sale_pos.receipts.classic';

            $output['html_content'] = view($layout, compact('receipt_details', 'tax_details'))->render();
        }

        return $output;
    }

    private function receiptContentCreditDemo(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null
    )
    {
        $output = ['is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => []
        ];

        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        //Check if printing of invoice is enabled or not.
        if ($location_details->print_receipt_on_invoice == 1) {
            //If enabled, get print type.
            $output['is_enabled'] = true;

            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_id, $location_details->invoice_layout_id);

            //Check if printer setting is provided.
            $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

            $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);

            $tax_details  = $this->transactionUtil->getTaxDetails($transaction_id);
            //If print type browser - return the content, printer - return printer config data, and invoice format config
            if ($receipt_printer_type == 'printer') {
                $output['print_type'] = 'printer';
                $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
                $output['data'] = $receipt_details;
            } else {
                $output['html_content'] = view('sell_return.receipt', compact('receipt_details', 'tax_details'))->render();
            }
        }

        return $output;
    }

    /**
     * Display a listing sell payment Verified.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPaymentVerified()
    {
        if (!auth()->user()->can('list_payment_verified')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);


        return view('sale_pos.payment-verified')
            ->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Send the datatable response for payment verified or quotations.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPaymentVerifiedDatables()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            //$is_quotation = request()->only('is_quotation', 0);

            $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'payment-verified')
                //->where('is_quotation', $is_quotation)
                ->select(
                    'transactions.id',
                    'transactions.final_total',
                    'transaction_date',
                    'invoice_no',
                    'contacts.name',
                    'contacts.id as contact_id',
                    'bl.name as business_location',
                    'is_direct_sale',
                    'is_quotation'
                );

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transaction_date', '>=', $start)
                            ->whereDate('transaction_date', '<=', $end);
            }

            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }

            if ($is_woocommerce) {
                $sells->addSelect('transactions.woocommerce_order_id');
            }

            $sells->groupBy('transactions.id');

            return Datatables::of($sells)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                            data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                            <li>
                            <a href="#" data-href="{{action(\'SellController@show\', [$id])}}" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> @lang("messages.view")</a>
                            </li>
                    @if (auth()->user()->can("edit_drafts") )
                        @if($is_direct_sale == 1)
                            <li>
                            <a target="_blank" href="{{action(\'SellController@edit\', [$id])}}"><i class="fas fa-edit"></i>  @lang("messages.edit")</a>
                            </li>
                        @else
                            <li>
                            <a target="_blank" href="{{action(\'SellPosController@edit\', [$id])}}"><i class="fas fa-edit"></i>  @lang("messages.edit")</a>
                            </li>
                        @endif
                    @endif

                    <li>
                        <a href="#" class="print-invoice" data-href="{{route(\'sell.printInvoice\', [$id])}}"><i class="fas fa-print" aria-hidden="true"></i> @lang("messages.print")</a>
                    </li>
                    <li>
                    <a href="{{action(\'SellPosController@destroy\', [$id])}}" class="delete-sale"><i class="fas fa-trash"></i>  @lang("messages.delete")</a>
                    </li>
                    </ul>
                    </div>
                    '
                )
                ->removeColumn('id')
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }

                    return $invoice_no;
                })
                ->editColumn('balance_due', function ($row) {
                        $contact = Contact::where('contacts.id', $row->contact_id)
                        ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                        ->leftjoin('users as sale','sale.id','=','contacts.sales_rep')
                        ->leftjoin('users as account','account.id','=','contacts.account_rep')
                        ->leftjoin('users as createdfor','createdfor.id','=','contacts.created_by')
                        ->with(['business'])
                        ->select(
                            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                            'contacts.*'
                        )->first();
                    $balance_due= $contact->total_invoice - $contact->invoice_received;
                    return  '<span class="display_currency " data-currency_symbol="true" data-orig-value="'. $balance_due.'">'.'$ '. $balance_due.'</span>';

                })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['action', 'invoice_no','balance_due', 'transaction_date'])
                ->make(true);
        }
    }
}
