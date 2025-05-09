<?php

namespace App\Http\Controllers;

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
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\Warranty;
use App\Brands;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SellNewController extends Controller
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
            $sells = $this->transactionUtil->getListSells_New($business_id);
            $myquery = $this->transactionUtil->getListSells_New1($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $myquery .= ' AND transactions.location_id = "'.$permitted_locations.'" ';
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            //Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $myquery .= ' AND transactions.created_by = "'.$created_by.'" ';
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
                $myquery .= ' AND transactions.created_by = "'.request()->session()->get('user.id').'" ';
                $sells->where('transactions.created_by', request()->session()->get('user.id'));
            }

            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $myquery .= ' AND transactions.payment_status = "'.request()->input('payment_status').'" ';
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                // $myquery .= ' AND transactions.payment_status IN ("due","partial") AND transactions.pay_term_number IS NOT NULL AND transactions.pay_term_type IS NOT NULL   ';
                $sells->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $myquery .= ' AND transactions.location_id = "'.$location_id.'" ';
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                    ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }


            if (!empty(request()->br_account_rep)) {
                $br_account_rep = request()->br_account_rep;
                $myquery .= ' AND contacts.account_rep = "'.$br_account_rep.'" ';
                $sells->where('contacts.account_rep', $br_account_rep);
            }
             if (!empty(request()->br_sales_rep)) {
                $br_sales_rep = request()->br_sales_rep;
                $myquery .= ' AND contacts.sales_rep = "'.$br_sales_rep.'" ';
                $sells->where('contacts.sales_rep', $br_sales_rep);
            }
            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $myquery .= ' AND contacts.id = "'.$customer_id.'" ';
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $myquery .= 'AND transactions.transaction_date BETWEEN "'.$start.'" AND "'.$end.'" ';
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $myquery .= ' AND transactions.is_direct_sale = 0 AND transactions.sub_type IS NULL ';
                    $sells->where('transactions.is_direct_sale', 0);
                    $sells->whereNull('transactions.sub_type');
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $myquery .= ' AND transactions.commission_agent "'.$commission_agent.'" ';
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if ($is_woocommerce) {
                $addSelect = 'transactions.woocommerce_order_id';
                $sells->addSelect('transactions.woocommerce_order_id');
                if (request()->only_woocommerce_sells) {
                    $myquery .= ' AND transactions.woocommerce_order_id IS NOT NULL ';
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
                $myquery .= ' AND transactions.res_waiter_id IS NOT NULL ';
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (!empty(request()->res_waiter_id)) {
                $res_waiter_id = request()->res_waiter_id;
                $myquery .= ' AND transactions.res_waiter_id "'.$res_waiter_id.'" ';
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (!empty(request()->input('sub_type'))) {
                $sub_type = request()->input('sub_type');
                $myquery .= ' AND transactions.sub_type "'.$sub_type.'" ';
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (!empty(request()->input('created_by'))) {
                $myquery .= ' AND transactions.created_by "'.request()->input('created_by').'" ';
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (!empty(request()->input('sales_cmsn_agnt'))) {
                $myquery .= ' AND transactions.commission_agent "'.request()->input('sales_cmsn_agnt').'" ';
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (!empty(request()->input('service_staffs'))) {
                $myquery .= ' AND transactions.res_waiter_id "'.request()->input('service_staffs').'" ';
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }
            $only_shipments = request()->only_shipments == 'true' ? true : false;
            if ($only_shipments && auth()->user()->can('access_shipping')) {
                $myquery .= ' AND transactions.shipping_status IS NOT NULL ';
                $sells->whereNotNull('transactions.shipping_status');
            }

            if (!empty(request()->input('shipping_status'))) {
                $myquery .= ' AND transactions.shipping_status "'.request()->input('shipping_status').'" ';
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
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
                    $myquery .= ' AND transactions.sub_type "'.$transaction_sub_type.'" ';
                    $sells->where('transactions.sub_type', $transaction_sub_type);
                } else {
                    $myquery .= ' AND transactions.sub_type IS NULL ';
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

                $addSelect = 'transactions.is_recurring, transactions.recur_parent_id ';
                $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            }


            $myquery .= ' GROUP BY transactions.id,SR.final_total,SR.id';

            // dd($myquery);
            // echo $myquery . '<br>';

            $filtered_sells = $sells->get();

            if(empty(request()->input('product_category')) || request()->input('product_category') == 'all'){
                // $filtered_sells = $sells;
                // $filtered_sells = DB::raw($myquery);
            } else {
                $category_id = request()->input('product_category');
                $sells_arr = $filtered_sells;
                $filtered_sells = [];
                // $sells_arr = $sells->get();
                // $sells_arr = DB::raw($myquery);
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

            if(empty(request()->input('brands')) || request()->input('brands') == 'all'){
                // $filtered_sells = $sells;
                // $filtered_sells = DB::raw($myquery);
            } else {
                $brands = request()->input('brands');
                $sells_arr = $filtered_sells;
                $filtered_sells = [];
                // $sells_arr = $sells->get();
                // $sells_arr = DB::raw($myquery);
                foreach ($sells_arr as  $value) {
                    $transaction_id = $value->id;
                    $transaction_sells = TransactionSellLine::leftJoin('products', 'transaction_sell_lines.product_id', '=','products.id' )
                            // ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                            ->where('products.brand_id',$brands)
                            ->where('transaction_id',$transaction_id)
                            ->get();

                    foreach ($transaction_sells as $sell) {
                        // if(!empty($sell)){
                            array_push($filtered_sells, $value);
                            break;
                        // }
                    }

                }
                //return response($filtered_sells);

            }

// return response($sells->get());


            // dd($filtered_sells);

            $datatable = Datatables::of($filtered_sells)
                ->addColumn(
                    'action',
                    function ($row) use ($only_shipments) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                        data-toggle="dropdown" aria-expanded="false">' .
                                        __("messages.actions") .
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-left" role="menu">' ;

                        if (auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access") || auth()->user()->can("view_own_sell_only")) {
                            $html .= '<li><a href="#" data-href="' . action("SellController@show", [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
                        }
                        if (!$only_shipments) {
                            //if($row->order_packing_status == '2' && $row->order_picking_status == '2'){
                            //}else{
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

                            if (auth()->user()->can("direct_sell.delete") || auth()->user()->can("sell.delete")) {
                                $html .= '<li><a href="' . action('SellPosController@destroy', [$row->id]) . '" class="delete-sale"><i class="fas fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                            }
                        }
                        if (auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access")) {
                            $html .= '<li><a target="_blank" href="' . action('SellPosController@open_invoice', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Open Invoice") . '</a></li>
			                       	<li><a target="_blank" href="' . action('SellPosController@packing_slip', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Packing Slip") . '</a></li>
                                <li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "new_sale"]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("Email Invoice") . '</a></li>';
                        }
                        if (auth()->user()->can("access_shipping")) {
                            $html .= '<li><a href="#" data-href="' . action('SellController@editShipping', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-truck" aria-hidden="true"></i>' . __("lang_v1.edit_shipping") . '</a></li>';
                        }
                        if (!$only_shipments) {
                            $html .= '<li class="divider"></li>';

                            if ($row->payment_status != "paid" && (auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) && auth()->user()->can("sell.payments")) {
                                $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __("purchase.add_payment") . '</a></li>';
                            }

                            $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __("purchase.view_payments") . '</a></li>';

                            if (auth()->user()->can("sell.create")) {
                                $html .= '<li><a href="' . action('SellController@duplicateSellCreate', [$row->id]) . '"><i class="fas fa-copy"></i> ' . __("lang_v1.duplicate_sell") . '</a></li>

                                <li style="display:none;" ><a href="' . action('SellReturnController@add', [$row->id]) . '"><i class="fas fa-undo"></i> ' . __("lang_v1.sell_return") . '</a></li>

                                <li><a href="' . action('SellPosController@showInvoiceUrl', [$row->id]) . '" class="view_invoice_url"><i class="fas fa-eye"></i> ' . __("lang_v1.view_invoice_url") . '</a></li>

                                ';
                            }

                            $html .= '';
				            $html .= ' <li><a href="'.action('SellPosController@driverinvoice', [$row->id]).'" ><i class="fa fa-file-invoice"></i> ' . __("Driver Invoice") . '</a></li>';
				            $html .= '<li><a target="_blank" href="' . action('SellPosController@invoicegen', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Print Invoice") . '</a></li>';
				            $html .= ' <li><a target="_blank" href="' . action('SellPosController@allInvoice') . '"><i class="fas fa-eye"></i> ' . __("All Invoice") . '</a></li>';

                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')
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
                    'total_cost',
                    function($row){
                        $costData = TransactionSellLine::select(DB::raw("SUM(purchase_price*quantity) as total_cost"))
                            ->where('transaction_id',$row->id)->first();

                        $cost = number_format($costData->total_cost,2,'.','') ?? "";
                        return  '<span class="display_currency total_cost" data-currency_symbol="true" data-orig-value="'.$cost.'">'. $cost.'</span>';
                    }
                )
                ->addColumn(
                    'total_gp',
                    function($row){
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
                        // if (!empty($discount) && $row->discount_type == 'percentage') {
                        //     $discount = $row->total_before_tax * ($discount / 100);
                        // }


                        // $data =  DB::table('transaction_sell_lines')
                        //             ->select(
                        //                 DB::raw("(1-
                        //                 (
                        //                     (sum(purchase_price*quantity))
                        //                     /
                        //                     (sum(unit_price_inc_tax*quantity)-$discount)
                        //                 )
                        //                 )*100 as GP")
                        //             )
                        //             ->where('transaction_id',$row->id)->first();
                        // $gross_profit = round($data->GP,2);
                        // $gp = $gross_profit.'%';

                        $check_data =  DB::table('transaction_sell_lines')
                                    ->select(DB::raw("sum(purchase_price*quantity) as cost"))
                                    ->where('transaction_id',$row->id)->first();

                        $divisable = $row->total_before_tax - $discount - $check_data->cost;
                        $divider = $row->total_before_tax - $discount;
                        if($divider == 0){
                            $divider = 1;
                        }
                        $new_gp = round((($divisable) * 100)/($divider),2)."%";
                        // return $new_gp;
                        return  '<span class="total_gross_profit" data-orig-value="'.$new_gp.'">'.$new_gp.'</span>';
                    }
                )

                // ->addColumn(
                //     'total_gp',
                //      function($row){
                //          $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                //         if (!empty($discount) && $row->discount_type == 'percentage') {
                //             $discount = $row->total_before_tax * ($discount / 100);
                //         }
                //         $total_sell = $row->total_sell - $discount;
                //         $total_cost = $row->total_cost;
                //         if($total_cost > 0 && $total_sell > 0){
                //             $gross_profit = (1 - ($total_cost / $total_sell))*100;
                //             $gross_profit = round($gross_profit,2);
                //             $gp = $gross_profit.'%';
                //         }else{
                //             $gross_profit = 0;
                //             $gp = '110%';
                //         }

                //         return  '<span class="total_gross_profit" data-orig-value="'. $gross_profit.'">'. $gp.'</span>';
                //     }
                // )
                ->editColumn(
                    'total_paid',
                    '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="{{$total_paid}}">{{$total_paid}}</span>'
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
                //         // return $row;
                //     }
                // )
                ->addColumn('picked_by', function ($row) {
                    return $row->picked_by;
                })
                ->addColumn('packed_by', function ($row) {
                    return $row->packed_by;
                })
                ->addColumn('added_by', function ($row) {
                    return $row->added_by;
                })
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
                    'account_rep',
                    function ($row) {
                        $account_rep = User::select('first_name')->find($row->account_rep);
                        if(!empty($account_rep->first_name)){
                            return $account_rep->first_name;
                        }
                            return null;
                    }
                )
                 ->editColumn(
                    'sales_rep',
                    function ($row) {
                        $sales_rep = User::select('first_name')->find($row->sales_rep);
                        if(!empty($sales_rep->first_name)){
                            return $sales_rep->first_name;
                        }
                            return null;
                    }
                )


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
                // ->addColumn('picked_by', function ($row) {
                //     return $row->picked_by;
                //     // return $row;
                // })
                // ->addColumn('packed_by', function ($row) {
                //     return $row->packed_by;
                // })
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') .'"><i class="fas fa-undo"></i></small>';
                    }
                    if (!empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

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
                        $bg_color = 'bg-green';
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
                // ->orderColumn('total_gp', function ($query, $order) {
                //     $query->orderBy('total_gp', $order);
                // })
                // ->orderColumn('total_gp', function ($query, $order) use ($filtered_sells) {
                //     $yourArrayData = $filtered_sells->get()->toArray();
                //     usort($yourArrayData, function ($a, $b) use ($order) {
                //         $valueA = floatval(str_replace('%', '', $a['total_gp']));
                //         $valueB = floatval(str_replace('%', '', $b['total_gp']));

                //         return $order === 'asc' ? $valueA - $valueB : $valueB - $valueA;
                //     });
                // })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view") || auth()->user()->can("view_own_sell_only")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }]);

            $rawColumns = ['final_total', 'account_rep', 'action', 'total_cost', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status','order_status', 'types_of_service_name', 'payment_methods', 'return_due', 'picked_by','total_gp'];

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
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $tax_rates = TaxRate::join('categories as cat', 'cat.id', '=', 'tax_rates.category')->select(DB::raw("CONCAT(tax_rates.name,' - ',cat.name) AS name"),'tax_rates.id')->orderBy('name')->pluck('name','id');

        $account_rep = User::forDropdown($business_id, false);
        $sales_rep = User::forDropdown($business_id, false);

        $brands = Brands::forDropdown($business_id);

        return view('sell.index-new')
        ->with(compact('business_locations','brands','sales_rep','account_rep', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses','categories','tax_rates'));
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
        $activity_logs = DB::table('activity_log')->join('users AS u', 'activity_log.causer_id', '=', 'u.id' )->where('username', 'not like', "%deleted%")->where('subject_id',$id)->select('activity_log.description','activity_log.updated_at','u.username')->get();
        return view('sale_pos.show')
            ->with(compact(
                'taxes',
                'sell',
                'payment_types',
                'order_taxes',
                'pos_settings',
                'shipping_statuses',
                'shipping_status_colors',
                'is_warranty_enabled',
                'activity_logs'
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
                    @if($is_direct_sale == 1)
                        <li>
                        <a target="_blank" href="{{action(\'SellController@edit\', [$id])}}"><i class="fas fa-edit"></i>  @lang("messages.edit")</a>
                        </li>
                    @else
                    <li>
                    <a target="_blank" href="{{action(\'SellPosController@edit\', [$id])}}"><i class="fas fa-edit"></i>  @lang("messages.edit")</a>
                    </li>
                    @endif
                    <li>
                        <a href="#" class="print-invoice" data-href="{{route(\'sell.printInvoice\', [$id])}}"><i class="fas fa-print" aria-hidden="true"></i> @lang("messages.print")</a>
                    </li>
                    <li>
                    <a href="{{action(\'SellPosController@destroy\', [$id])}}" class="delete-sale"><i class="fas fa-trash"></i>  @lang("messages.delete")</a>
                    </li>
                    @if($is_quotation)
                    <li><a href="{{action("SellPosController@showInvoiceUrl", [$id])}}" class="view_invoice_url"><i class="fas fa-eye"></i> @lang("lang_v1.view_quote_url")</a></li>
                    <li><a href="#" data-href="{{ action("NotificationController@getTemplate", ["transaction_id" => $id,"template_for" => "new_quotation"])}}" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("lang_v1.new_quotation_notification") . '</a></li>
                    @endif
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
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['action', 'invoice_no', 'transaction_date'])
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
                    'shipping_status', 'delivered_to'
                ]);
            $business_id = $request->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->update($input);

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
            return redirect()->action('SellPosController@create')->with(['status', $output]);
        } else {
            abort(404, 'Not Found.');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function gp_report()
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
            $sells = $this->transactionUtil->getListSells_New($business_id);
            $myquery = $this->transactionUtil->getListSells_New1($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $myquery .= ' AND transactions.location_id = "'.$permitted_locations.'" ';
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            //Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $myquery .= ' AND transactions.created_by = "'.$created_by.'" ';
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
                $myquery .= ' AND transactions.created_by = "'.request()->session()->get('user.id').'" ';
                $sells->where('transactions.created_by', request()->session()->get('user.id'));
            }

            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $myquery .= ' AND transactions.payment_status = "'.request()->input('payment_status').'" ';
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                // $myquery .= ' AND transactions.payment_status IN ("due","partial") AND transactions.pay_term_number IS NOT NULL AND transactions.pay_term_type IS NOT NULL   ';
                $sells->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $myquery .= ' AND transactions.location_id = "'.$location_id.'" ';
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
                $myquery .= ' AND contacts.id = "'.$customer_id.'" ';
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $myquery .= 'AND transactions.transaction_date BETWEEN "'.$start.'" AND "'.$end.'" ';
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $myquery .= ' AND transactions.is_direct_sale = 0 AND transactions.sub_type IS NULL ';
                    $sells->where('transactions.is_direct_sale', 0);
                    $sells->whereNull('transactions.sub_type');
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $myquery .= ' AND transactions.commission_agent "'.$commission_agent.'" ';
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if ($is_woocommerce) {
                $addSelect = 'transactions.woocommerce_order_id';
                $sells->addSelect('transactions.woocommerce_order_id');
                if (request()->only_woocommerce_sells) {
                    $myquery .= ' AND transactions.woocommerce_order_id IS NOT NULL ';
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
                $myquery .= ' AND transactions.res_waiter_id IS NOT NULL ';
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (!empty(request()->res_waiter_id)) {
                $res_waiter_id = request()->res_waiter_id;
                $myquery .= ' AND transactions.res_waiter_id "'.$res_waiter_id.'" ';
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (!empty(request()->input('sub_type'))) {
                $sub_type = request()->input('sub_type');
                $myquery .= ' AND transactions.sub_type "'.$sub_type.'" ';
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (!empty(request()->input('created_by'))) {
                $myquery .= ' AND transactions.created_by "'.request()->input('created_by').'" ';
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (!empty(request()->input('sales_cmsn_agnt'))) {
                $myquery .= ' AND transactions.commission_agent "'.request()->input('sales_cmsn_agnt').'" ';
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (!empty(request()->input('service_staffs'))) {
                $myquery .= ' AND transactions.res_waiter_id "'.request()->input('service_staffs').'" ';
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }
            $only_shipments = request()->only_shipments == 'true' ? true : false;
            if ($only_shipments && auth()->user()->can('access_shipping')) {
                $myquery .= ' AND transactions.shipping_status IS NOT NULL ';
                $sells->whereNotNull('transactions.shipping_status');
            }

            if (!empty(request()->input('shipping_status'))) {
                $myquery .= ' AND transactions.shipping_status "'.request()->input('shipping_status').'" ';
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
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
                    $myquery .= ' AND transactions.sub_type "'.$transaction_sub_type.'" ';
                    $sells->where('transactions.sub_type', $transaction_sub_type);
                } else {
                    $myquery .= ' AND transactions.sub_type IS NULL ';
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

                $addSelect = 'transactions.is_recurring, transactions.recur_parent_id ';
                $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            }


            $myquery .= ' GROUP BY transactions.id,SR.final_total,SR.id';

            // dd($myquery);
            // echo $myquery . '<br>';

            if(empty(request()->input('product_category')) || request()->input('product_category') == 'all'){
                $filtered_sells = $sells;
                // $filtered_sells = DB::raw($myquery);
            } else {
                $category_id = request()->input('product_category');
                $filtered_sells = [];
                $sells_arr = $sells->get();
                // $sells_arr = DB::raw($myquery);
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

            // return response($sells->get());
            // $test = 'SELECT c.id,c.contact_id,c.name,count(t.id) as noofinvoice,sum(t.final_total) as totalinvoice ,
            // sum(t.discount_amount), (select SUM(tsl.quantity * tsl.unit_price) FROM transaction_sell_lines as tsl WHERE t.id = tsl.transaction_id AND t.contact_id = c.id) as total_sell
            // FROM transactions as t, contacts as c WHERE c.id = t.contact_id AND t.type like 'sell' AND t.status LIKE 'final' AND t.business_id = 4 AND t.transaction_date BETWEEN '2022-01-01 00:00:00' AND '2022-12-31 23:59:59' GROUP BY c.id,t.id';
            // // foreach($test as $data){
            //     echo "<pre>";
            //     print_r($test);
                // die;
            // }
            // dd($filtered_sells);
            $datatable = Datatables::of($sells)
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') .'"><i class="fas fa-undo"></i></small>';
                    }
                    if (!empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'final_total',
                    // function($row){
                    //     $final_total =  $row->final_total;
                    //     return  '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="'. $final_total.'">'. $final_total.'</span>';
                    // }
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
                    'discount_amount',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        return '<span class="display_currency total-discount" data-currency_symbol="true" data-orig-value="' . $discount . '">' . $discount . '</span>';
                    }
                )
                ->addColumn(
                    'total_cost',
                    function($row){
                        $costData = TransactionSellLine::select(DB::raw("SUM(purchase_price*quantity) as total_cost"))
                            ->where('transaction_id',$row->id)->first();

                        $cost = number_format($costData->total_cost,2,'.','') ?? "";
                        return  '<span class="display_currency total_cost" data-currency_symbol="true" data-orig-value="'.$cost.'">'. $cost.'</span>';
                    }
                )
                ->addColumn(
                    'sell_total',
                    function($row){
                        $sellTotal = TransactionSellLine::select(DB::raw("SUM(unit_price*quantity) as sell"))
                            ->where('transaction_id',$row->id)->first();

                        $sell = number_format($sellTotal->sell,2,'.','') ?? "";

                        return  '<span class="display_currency sell-total" data-currency_symbol="true" data-orig-value="'. $sell.'">'. $sell.'</span>';
                    }
                )
                ->addColumn(
                    'total_gp',
                    function($row){
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

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
                )
                ->addColumn(
                    'profit_amt',
                    function($row){
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        $check_data =  DB::table('transaction_sell_lines')
                            ->select(DB::raw("sum(purchase_price*quantity) as cost"))
                            ->where('transaction_id',$row->id)
                        ->first();

                        $profit_amt = $row->total_before_tax - $discount - $check_data->cost;

                        return  '<span class="profit_amt display_currency" data-currency_symbol="true" data-orig-value="'.$profit_amt.'">'.$profit_amt.'</span>';
                    }
                )
                ->removeColumn('id');

            $rawColumns = ['profit_amt','final_total', 'action', 'sell_total', 'total_cost', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status','order_status', 'types_of_service_name', 'payment_methods', 'return_due', 'picked_by','total_gp'];

            return $datatable->rawColumns($rawColumns)->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
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
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $tax_rates = TaxRate::join('categories as cat', 'cat.id', '=', 'tax_rates.category')->select(DB::raw("CONCAT(tax_rates.name,' - ',cat.name) AS name"),'tax_rates.id')->orderBy('name')->pluck('name','id');
        return view('report.gp_report')
        ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses','categories','tax_rates'));
    }
}
