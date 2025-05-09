<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\SellingPriceGroup;
use App\Notifications\CustomerNotification;
use App\PurchaseLine;
use App\Transaction;
use App\User;
use App\TaxRate;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\productUtil;
use App\Utils\Util;
use DB;
use Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\TransactionPayment;
use App\Variation;
use App\VariationLocationDetails;
use Modules\Woocommerce\Utils\WoocommerceUtil;
use Illuminate\Support\Facades\Auth;
use App\Customerlog;
use Exception;
use Twilio\Http\GuzzleClient;
use GuzzleHttp\Client;
use Modules\SmartCRM\Models\FollowUp;

class NewContactController extends Controller
{
    protected $woocommerceUtil;
    protected $commonUtil;
    protected $contactUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(
        WoocommerceUtil $woocommerceUtil,
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
    ) {
        $this->woocommerceUtil = $woocommerceUtil;
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = 'customer';

        $types = ['supplier', 'customer'];
        $business_id = request()->session()->get('user.business_id');

        if (empty($type) || !in_array($type, $types)) {
            return redirect()->back();
        }
        $state = request()->get('contact_state', null);
        $city = request()->get('contact_city', null);
        $customer_id = request()->get('contact_filter_customer_id', null);
        $acc_rep = request()->get('contact_account_rep',null);
        $sale_rep = request()->get('contact_sales_rep',null);
        // $users = User::select('id','first_name','last_name')->get();
        $users = User::select('id','first_name','last_name')
            ->where('business_id',4)
            ->where('status','active')
            ->where('allow_login',1)
            ->where('sales_rep',1)
            ->orderBy('first_name')
        ->get();


        if (request()->ajax()) {
            if ($type == 'supplier') {
                return $this->indexSupplier($state,$city,$customer_id,$acc_rep,$sale_rep);
            } elseif ($type == 'customer') {
                return $this->indexCustomer($state,$city,$customer_id,$acc_rep,$sale_rep);
            } else {
                die("Not Found");
            }
        }
        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($type, ['customer'])) ? true : false;
          if ($type == 'supplier') {
            $customers = Contact::suppliersDropdown($business_id, false);
        } else{
            $customers = Contact::customersDropdown($business_id, false);
        }
       return view('contact.index2')
            ->with(compact('type', 'reward_enabled', 'customers','users'));
    }

    /**
     * Returns the database object for supplier
     *
     * @return \Illuminate\Http\Response
     */
    private function indexSupplier($state,$city,$customer_id,$acc_rep,$sale_rep)
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $contact = $this->contactUtil->getContactQuery($business_id, 'supplier', $state,$city,$customer_id,$acc_rep,$sale_rep);

          if (empty(request()->get('inactive_supplier'))) {
            $contact->Active();
        }

        return Datatables::of($contact)
            ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
            ->addColumn(
                'due', function ($row) {

                $ledger_details = $this->transactionUtil->getLedgerDetails($row->id,'2021-01-01 00:00:00',date('Y-m-d H:i:s'),$row->balance);
                return '<span class="display_currency total_due text-success aaa" data-orig-value="' . $ledger_details['balance_due'] . '" data-currency_symbol=true >'.  $ledger_details['balance_due']   .'</span>';

                $due =  $row->total_purchase + $row->total_expense  - $row->purchase_paid    - $row->balance  - $row->total_purchase_return - $row->purchase_return_received ; //- $total_discount;

                // $row->total_purchase +  $row->total_expense - $row->purchase_paid  + $row->balance  - $row->total_purchase_return - $row->purchase_return_received;
// $contact_details->total_purchase + $contact_details->total_expense  - $contact_details->total_paid    - $contact_details->balance  - $contact_details->total_purchase_return; //- $total_discount;

                if ($row->contact_type == 'supplier') {
                    $due -= $row->opening_balance - $row->opening_balance_paid;
                }
                //else {
                //     $due += $row->opening_balance - $row->opening_balance_paid;
                // }
                    $total_final_invoice = 0;
                    $balance_due = 0;
                    $total_final_invoice = number_format (($row->total_purchase - $row->purchase_return_received -  $row->total_purchase_return), 2, '.', '') - $row->balance;

                    $balance_due=  ($row->opening_balance - $row->opening_balance_paid) + $total_final_invoice;

                // if($due > 0){

                    return '<span class="display_currency total_due text-success abc" data-orig-value="' . $due . '" data-currency_symbol=true >'.  $due   .'</span>';
                // }
                // return '<span class="display_currency contact_due" data-orig-value="{{$row->total_purchase - $row->purchase_paid}}" data-currency_symbol=true data-highlight=false>{{ $row->purchase_paid }}</span>';
            })
            ->addColumn(
                'return_due',
                '<span class="display_currency return_due" data-orig-value="{{$total_purchase_return - $purchase_return_paid}}" data-currency_symbol=true data-highlight=false>{{$total_purchase_return - $purchase_return_paid }}</span>'
            )
            ->addColumn(
                'action',
                function ($row) {
                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=purchase" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.pay") . '</a></li>';

                    $return_due = $row->total_purchase_return - $row->purchase_return_paid;
                    // if ($return_due > 0) {
                        $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=purchase_return" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.receive_purchase_return_due") . '</a></li>';
                    // }

                    if (auth()->user()->can('supplier.view')) {
                        $html .= '<li><a href="' . action('ContactController@show', [$row->id]) . '"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                    }
                    if (auth()->user()->can('supplier.update')) {
                        $html .= '<li><a href="' . action('ContactController@edit', [$row->id]) . '" class="edit_contact_button"><i class="glyphicon glyphicon-edit"></i>' .  __("messages.edit") . '</a></li>';
                    }
                    if (auth()->user()->can('supplier.delete')) {
                        $html .= '<li><a href="' . action('ContactController@destroy', [$row->id]) . '" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    if (auth()->user()->can('customer.update')) {
                        $html .= '<li><a href="' . action('ContactController@updateStatus', [$row->id]) . '"class="update_contact_status"><i class="fas fa-power-off"></i>';

                        if ($row->contact_status == "active") {
                            $html .= __("messages.deactivate");
                        } else {
                            $html .= __("messages.activate");
                        }

                        $html .= "</a></li>";
                    }

                    $html .= '<li class="divider"></li>';
                    if (auth()->user()->can('supplier.view')) {
                        $html .= '
                                <li>
                                    <a href="' . action('ContactController@show', [$row->id]). '?view=ledger">
                                        <i class="fas fa-scroll" aria-hidden="true"></i>
                                        ' . __("lang_v1.ledger") . '
                                    </a>
                                </li>';

                        if (in_array($row->type, ["both", "supplier"])) {
                            $html .= '<li>
                                <a href="' . action('ContactController@show', [$row->id]) . '?view=purchase">
                                    <i class="fas fa-arrow-circle-down" aria-hidden="true"></i>
                                    ' . __("purchase.purchases") . '
                                </a>
                            </li>
                            <li>
                                <a href="' . action('ContactController@show', [$row->id]) . '?view=stock_report">
                                    <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                                    ' . __("report.stock_report") . '
                                </a>
                            </li>';
                        }

                        if (in_array($row->type, ["both", "customer"])) {
                            $html .=  '<li>
                                <a href="' . action('ContactController@show', [$row->id]). '?view=sales">
                                    <i class="fas fa-arrow-circle-up" aria-hidden="true"></i>
                                    ' . __("sale.sells") . '
                                </a>
                            </li>';
                        }

                        $html .= '<li>
                                <a href="' . action('ContactController@show', [$row->id]) . '?view=documents_and_notes">
                                    <i class="fas fa-paperclip" aria-hidden="true"></i>
                                     ' . __("lang_v1.documents_and_notes") . '
                                </a>
                            </li>';
                    }
                    $html .= '</ul></div>';

                    return $html;
                }
            )
            ->editColumn('opening_balance', function ($row) {
                $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->opening_balance . '">' . $row->opening_balance . '</span>';

                return $html;
            })
            ->editColumn('balance', function ($row) {
                $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->balance . '">' . $row->balance . '</span>';

                return $html;
            })
            ->editColumn('pay_term', '
                @if(!empty($pay_term_type) && !empty($pay_term_number))
                    {{$pay_term_number}}
                    @lang("lang_v1.".$pay_term_type)
                @endif
            ')
            ->editColumn('name', function ($row) {
                if ($row->contact_status == 'inactive') {
                    return $row->name . ' <small class="label pull-right bg-red no-print">' . __("lang_v1.inactive") . '</small>';
                } else {
                    return $row->name;
                }
            })


            ->editColumn('created_at', '{{@format_date($created_at)}}')
            ->removeColumn('opening_balance_paid')
            ->removeColumn('type')
            ->removeColumn('id')
            ->removeColumn('total_purchase')
            ->removeColumn('purchase_paid')
            ->removeColumn('total_purchase_return')
            ->removeColumn('purchase_return_paid')
            ->filterColumn('address', function ($query, $keyword) {
                $query->where( function($q) use ($keyword){
                    $q->where('address_line_1', 'like', "%{$keyword}%")
                    ->orWhere('address_line_2', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('state', 'like', "%{$keyword}%")
                    ->orWhere('country', 'like', "%{$keyword}%")
                    ->orWhere('zip_code', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(COALESCE(address_line_1, ''), ', ', COALESCE(address_line_2, ''), ', ', COALESCE(city, ''), ', ', COALESCE(state, ''), ', ', COALESCE(country, '') ) like ?", ["%{$keyword}%"]);
                });
            })
            ->rawColumns(['action', 'opening_balance', 'pay_term', 'due', 'return_due', 'name', 'balance'])
            ->make(true);

    }

    /**
     * Returns the database object for customer
     *
     * @return \Illuminate\Http\Response
     */
    private function indexCustomer($state,$city, $customer_id,$acc_rep,$sale_rep)
    {
        if (!auth()->user()->can('customer.view') && !auth()->user()->can('customer.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $query = $this->contactUtil->getContactQuery($business_id, 'customer',$state,$city, $customer_id,$acc_rep,$sale_rep);

//        dd($query->get());

        // $id_balance_array = [];
        // $query_result = $query->get();
        // foreach($query_result as $result){
        //     $balance_due = 0;
        //     $qry = $this->contactUtil->getContactInfo($business_id, $result->id);
        //     $balance_due=  $qry->opening_balance - $qry->opening_balance_paid + $qry->total_invoice - $qry->invoice_received - $qry->balance - $qry->total_sell_return + $qry->sell_return_paid;
        //     $id_balance_array[$result->id] = $balance_due;
        // }
        // $ids_order = array_keys($id_balance_array);
        // $ids_order_str = implode(',', $ids_order);

        $search = request()->get('search', []);
        if (!empty(request()->get('exact_search')) && !empty($search['value'])) {
            $searchable = $search['value'];
            $query->where(function($q) use($searchable){
                $q->where("contacts.contact_id", $searchable)
                ->orWhere("contacts.supplier_business_name", $searchable)
                ->orWhere("contacts.name", $searchable)
                ->orWhere("contacts.email", $searchable)
                ->orWhere("contacts.mobile", $searchable);
            });
        }
        if (empty(request()->get('inactive_contact'))) {
            $query->Active();
        }

        
        $contacts = Datatables::of($query->get())
            ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
            ->addColumn(
                'due', function ($row ) use($business_id){
                    $total_final_invoice = 0;
                    $balance_due = 0;
                    // $total_final_invoice = number_format (($row->total_invoice - $row->invoice_received -  $row->total_sell_return + $row->sell_return_paid), 2, '.', '') - $row->balance;

                    // $balance_due=  ($row->opening_balance - $row->opening_balance_paid) + $total_final_invoice;
                    // // return $balance_due;
                    // '<span class="display_currency contact_due" data-currency_symbol="true"  data-highlight=true  data-orig-value="' .($balance_due). '" >' .($balance_due). '</span>';


                    $qry = $this->contactUtil->getContactInfo($business_id, $row->id);


                     $balance_due=  $qry->opening_balance - $qry->opening_balance_paid + $qry->total_invoice - $qry->invoice_received - $qry->balance - $qry->total_sell_return + $qry->sell_return_paid;
                     $opening = ($qry->total_invoice - $qry->invoice_received) - $qry->balance;
                    return '<span class="display_currency contact_due" data-currency_symbol="true"  data-highlight=true  data-orig-value="' .($balance_due). '" >' .($balance_due). '</span>';

                // '<span class="display_currency contact_due" data-orig-value="{{$row->total_invoice - $row->invoice_received}}" data-currency_symbol=true data-highlight=true>{{( $row->total_invoice - $rowinvoice_received)}}</span>';
            })
            ->addColumn('due_raw', function ($row) use($business_id){
                $balance_due = 0;
                // $qry = $this->contactUtil->getContactInfo($business_id, $row->id);
                // $balance_due=  $qry->opening_balance - $qry->opening_balance_paid + $qry->total_invoice - $qry->invoice_received - $qry->balance - $qry->total_sell_return + $qry->sell_return_paid;
                return $balance_due;
            })
            ->addColumn('check_box', function ($row) {
                 return  '<input type="checkbox" class="row-select" value="' . $row->id .'">' ;
            })
            ->addColumn(
                'return_due',
                '<span class="display_currency return_due" data-orig-value="{{$total_sell_return - $sell_return_paid}}" data-currency_symbol=true data-highlight=false>{{$total_sell_return - $sell_return_paid }}</span>'
            )
            ->addColumn(
                'action',
                function ($row) {
                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if(auth()->user()->can('sell.payments')){
                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=sell" class="pay_sale_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.pay") . '</a></li>';
                    }
                    $return_due = $row->total_sell_return - $row->sell_return_paid;
                    if ($return_due > 0) {
                        $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=sell_return" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.pay_sell_return_due") . '</a></li>';
                    }

                    if (auth()->user()->can('customer.view')) {
                        $html .= '<li><a href="' . action('ContactController@show', [$row->id]) . '"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                    }
                    if (auth()->user()->can('customer.update')) {
                        $html .= '<li><a href="' . action('ContactController@edit', [$row->id]) . '" class="edit_contact_button"><i class="glyphicon glyphicon-edit"></i>' .  __("messages.edit") . '</a></li>';
                    }
                    if (!$row->is_default && auth()->user()->can('customer.delete')) {
                        $html .= '<li><a href="' . action('ContactController@destroy', [$row->id]) . '" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    if (auth()->user()->can('customer.update')) {
                        $html .= '<li><a href="' . action('ContactController@updateStatus', [$row->id]) . '"class="update_contact_status"><i class="fas fa-power-off"></i>';

                        if ($row->contact_status == "active") {
                            $html .= __("messages.deactivate");
                        } else {
                            $html .= __("messages.activate");
                        }

                        $html .= "</a></li>";

                        $html .='<li><a href="' . action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@checkWooCustomerExistOrNot', [$row->id]) . '"class="woocomerce_customer_verify"><i class="fas fa-eye"></i>';
                        $html .= __("Woocommerce verify Customer");
                        $html .= "</a></li>";

                        $html .='<li><a href="' . action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@syncCustomer', [$row->id]) . '"class="woocomerce_customer_sync"><i class="fas fa-sync"></i>';
                        $html .= __("Woocommerce Sync");
                        $html .= "</a></li>";

                        if($row->woocommerce_user_id) {
                            $html .='<li><a href="' . action('\Modules\Woocommerce\Http\Controllers\WoocommerceController@customerResetPassword', [$row->id]) . '"class="woocomerce_customer_reset_password"><i class="fas fa-sync"></i>';
                            $html .= __("Woocommerce Reset Password");
                            $html .= "</a></li>";
                        }
                    }

                    $html .= '<li class="divider"></li>';
                    if (auth()->user()->can('customer.view')) {
                        $html .= '
                                <li>
                                    <a href="' . action('ContactController@show', [$row->id]). '?view=ledger">
                                        <i class="fas fa-scroll" aria-hidden="true"></i>
                                        ' . __("lang_v1.ledger") . '
                                    </a>
                                </li>';

                        if (in_array($row->type, ["both", "supplier"])) {
                            $html .= '<li>
                                <a href="' . action('ContactController@show', [$row->id]) . '?view=purchase">
                                    <i class="fas fa-arrow-circle-down" aria-hidden="true"></i>
                                    ' . __("purchase.purchases") . '
                                </a>
                            </li>
                            <li>
                                <a href="' . action('ContactController@show', [$row->id]) . '?view=stock_report">
                                    <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                                    ' . __("report.stock_report") . '
                                </a>
                            </li>';
                        }

                        if (in_array($row->type, ["both", "customer"])) {
                            $html .=  '<li>
                                <a href="' . action('ContactController@show', [$row->id]). '?view=sales">
                                    <i class="fas fa-arrow-circle-up" aria-hidden="true"></i>
                                    ' . __("sale.sells") . '
                                </a>
                            </li>';
                        }

                        $html .= '<li>
                                <a href="' . action('ContactController@show', [$row->id]) . '?view=documents_and_notes">
                                    <i class="fas fa-paperclip" aria-hidden="true"></i>
                                     ' . __("lang_v1.documents_and_notes") . '
                                </a>
                            </li>';
                    }
                    $html .= '</ul></div>';

                    return $html;
                }
            )
            ->editColumn('opening_balance', function ($row) {
                $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->opening_balance . '">' . $row->opening_balance . '</span>';

                return $html;
            })
            ->editColumn('sales_rep', function ($row) {
                $html = '<span data-orig-value="' . $row->sales_firstname . ' '.$row->sales_lastname. '">' . $row->sales_firstname . ' '.$row->sales_lastname . '</span>';
                return $html;
            })
            ->editColumn('account_rep', function ($row) {
                $html = '<span data-orig-value="' . $row->acc_firstname . ' '.$row->acc_lastname. '">' . $row->acc_firstname . ' '.$row->acc_lastname . '</span>';
                return $html;
            })
            ->editColumn('balance', function ($row) {
                $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->balance . '">' . $row->balance . '</span>';

                return $html;
            })
            ->editColumn('credit_limit', function ($row) {
                $html = __('lang_v1.no_limit');
                if (!is_null($row->credit_limit)) {
                    $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->credit_limit . '">' . $row->credit_limit . '</span>';
                }

                return $html;
            })
            ->editColumn('pay_term', function ($row){
                return $row->pay_term_number;
            })
            ->editColumn('name', function ($row) {
                if ($row->contact_status == 'inactive') {
                    return $row->name . ' <small class="label pull-right bg-red no-print">' . __("lang_v1.inactive") . '</small>';
                } else {
                    return $row->name;
                }
            })
            ->addColumn('is_nyc', function ($row) {
               $isNyc = ($row->is_nyc == "1")? "Yes" : "No";
                return  $isNyc ;
            })
            ->editColumn('total_rp', '{{$total_rp ?? 0}}')
            ->editColumn('created_at', '{{@format_date($created_at)}}')
            ->removeColumn('total_invoice')
            ->removeColumn('opening_balance_paid')
            ->removeColumn('invoice_received')
            ->removeColumn('state')
            ->removeColumn('country')
            ->removeColumn('city')
            ->removeColumn('type')
            ->removeColumn('id')
            ->removeColumn('is_default')
            ->removeColumn('total_sell_return')
            ->removeColumn('sell_return_paid');
            // ->filterColumn('address', function ($query, $keyword) {
            //     $query->where( function($q) use ($keyword){
            //         $q->where('address_line_1', 'like', "%{$keyword}%")
            //         ->orWhere('address_line_2', 'like', "%{$keyword}%")
            //         ->orWhere('city', 'like', "%{$keyword}%")
            //         ->orWhere('state', 'like', "%{$keyword}%")
            //         ->orWhere('country', 'like', "%{$keyword}%")
            //         ->orWhere('zip_code', 'like', "%{$keyword}%")
            //         ->orWhereRaw("CONCAT(COALESCE(address_line_1, ''), ', ', COALESCE(address_line_2, ''), ', ', COALESCE(city, ''), ', ', COALESCE(state, ''), ', ', COALESCE(country, '') ) like ?", ["%{$keyword}%"]);
            //     });
            // });
            // ->orderColumn('due', function ($query, $order)  use ($ids_order_str) {
            //     // $ids = array_keys($id_balance_array);
            //     $query->orderByRaw("FIELD(id, " . $ids_order_str . ") $order");
            // });
        $reward_enabled = (request()->session()->get('business.enable_rp') == 1) ? true : false;
        if (!$reward_enabled) {
            $contacts->removeColumn('total_rp');
        }
        return $contacts->rawColumns(['check_box','action', 'opening_balance', 'credit_limit', 'pay_term', 'due', 'return_due', 'name', 'balance','sales_rep','account_rep'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create') && !auth()->user()->can('customer.view_own') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $types = [];
        if (auth()->user()->can('supplier.create') || auth()->user()->can('supplier.view_own')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create') || auth()->user()->can('customer.view_own')) {
            $types['customer'] = __('report.customer');
        }
        // if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
        //     $types['both'] = __('lang_v1.both_supplier_customer');
        // }

        $customer_groups = SellingPriceGroup::forDropdown($business_id);

        $users = User::select('id','first_name','last_name')
            ->where('business_id',4)
            ->where('status','active')
            ->where('allow_login',1)
            ->where('sales_rep',1)
            ->orderBy('first_name')
        ->get();

        $selected_type = request()->type;

        return view('contact.create', ['types' => $types, 'customer_groups' => $customer_groups, 'selected_type' => $selected_type, 'users' => $users]);
            //->with(compact('types', 'customer_groups', 'selected_type', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();

        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create') && !auth()->user()->can('customer.view_own') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }
            if($request->type == 'supplier'){
                // return 123;
                $input = $request->only(['type', 'contact_id','business_name1',
                    'prefix', 'first_name', 'middle_name', 'last_name','email', 'pay_term_number',
                    'pay_term_type', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country',
                    'address_line_1', 'address_line_2', 'zip_code' ,'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4',
                    'custom_field5', 'custom_field6', 'custom_field7', 'custom_field8',
                    'custom_field9', 'custom_field10', 'shipping_address','licnumber','balance']);

            }else{

                $input = $request->only(['type', 'supplier_business_name',
                'prefix', 'first_name', 'middle_name', 'last_name', 'tax_number', 'pay_term_number',
                'pay_term_type', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country',
                'address_line_1', 'address_line_2', 'customer_group_id', 'zip_code', 'contact_id',
                'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4',
                'custom_field5', 'custom_field6', 'custom_field7', 'custom_field8',
                'custom_field9', 'custom_field10', 'email', 'shipping_address',
                'position', 'dob','contact_person_1', 'contact_person_2',
                'tobacco_license_no', 'expiry_date', 'referal_code','fax',
                'note','tax','is_nyc','cigar_customer','whatsapp','payment_status']);

            }





                if($request->type == 'supplier'){
                    $input['contact_id'] = strtoupper($request->supplier_contact_id);
                    $input['supplier_business_name'] = strtoupper($request->supplier_business_name1);
                    $input['prefix'] = $request->supplier_prefix;
                    $input['first_name'] = strtoupper($request->supplier_first_name);
                    $input['middle_name'] = strtoupper($request->supplier_middle_name);
                    $input['last_name'] = strtoupper($request->supplier_last_name);
                    $input['email'] = strtoupper($request->supplier_email);
                    $input['mobile'] = $request->supplier_mobile;
                    $input['alternate_number'] = $request->supplier_alternate_number;
                    $input['landline'] = $request->supplier_landline;
                    $input['licnumber'] = strtoupper($request->supplier_licnumber);
                    $input['balance'] = $request->supplier_openbalance;
                    $input['pay_term_number'] = $request->supplier_pay_term_number;
                    $input['pay_term_type'] = $request->supplier_pay_term_type;
                    $input['address_line_1'] = strtoupper($request->supplier_address_line_1);
                    $input['address_line_2'] = strtoupper($request->supplier_address_line_2);
                    $input['city'] = strtoupper($request->supplier_city);
                    $input['state'] = strtoupper($request->supplier_state);
                    $input['country'] = strtoupper($request->supplier_country);
                    $input['zip_code'] = $request->supplier_zip_code;
                    $input['shipping_address'] = strtoupper($request->supplier_shipping_address);
                    $input['business_id'] = $business_id;
                    $input['created_by'] = $request->session()->get('user.id');
                    $input['name'] = $input['first_name'];
                    // return $input;
                }else{
                    $input['name'] = strtoupper($input['first_name']);

                    if($request->input('sync') == "on"){
                        $input['sync'] = null;
                    }
                    else{
                       $input['sync'] =  1;
                    }
                    $input['is_nyc'] = $request->input('is_nyc');
                    $input['whatsapp'] = $request->input('whatsapp');
                    $input['tax_number'] = strtoupper($request->input('tax'));
                    $input['supplier_business_name'] = strtoupper($request->supplier_business_name);
                    $input['address_line_1'] = strtoupper($request->address_line_1);

                    $input['contact_person_1'] = strtoupper($request->contact_person_1);
                    $input['contact_person_2'] = strtoupper($request->contact_person_2);
                    $input['email'] = strtoupper($request->email);
                    $input['address_line_2'] = strtoupper($request->address_line_2);
                    $input['city'] = strtoupper($request->city);
                    $input['state'] = $request->input('state');
                    $input['first_name'] = strtoupper($request->first_name);
                    $input['name'] = strtoupper($request->first_name);
                    $input['business_id'] = $business_id;
                    // $input['mobile'] = $request->whatsapp;
                    $input['created_by'] = $request->session()->get('user.id');
                    $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;

                    $input['sales_rep'] = $request->input('sales_rep');
                    $input['account_rep'] = $request->input('account_rep', 6);
                    // exit;
                    $input['opening_balance'] = $this->commonUtil->num_uf($request->input('opening_balance'));
                    // echo "<pre>";
                    // print_r($input);
                    // die;

                }


                // return $request->supplier_first_name;


                // echo ($input['whatsapp']);exit;

            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }
        //  dd($request);
        //     if($request->file()) {
        //     $fileName = time().'_'.$request->file->getClientOriginalName();
        //     // echo $fileName;exit;
        //     $filePath = $request->file('file')->storeAs('uploads', $fileName, 'public');
        //     $path = $image->store('uploads');
        //     //$input['file'] = $this->productUtil->uploadFile($request, 'file', config('constants.product_img_path'), 'image');

        //     $fileModel->name = time().'_'.$request->file->getClientOriginalName();
        //     $fileModel->file_path = '/public/' . $filePath;
        //      $input['file']=$fileModel;

        // }
        if ($request->hasFile('docfile')) {
        $image = $request->file('docfile');
        $name = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/storage/galeryImages/');
        $image->move($destinationPath, $name);
        // $this->save();
        $input['file']=$name;
        }




            if (!empty($input['address_line_1'])) {
                $input['shipping_address'] = strtoupper($input['address_line_1']);
            }

            // if (!empty($input['expiry_date'])) {
            //     $input['expiry_date'] = $this->commonUtil->uf_date($input['expiry_date']);
            // }

            // store lat long
            try{
                $map_location =  $input['address_line_1'] . ',' . $input['city'] . ',' . $input['state'] . ',' . $input['country'] . ',' .$input['zip_code'];

                $lat_long = json_encode($map_location);

                $guzzle = new \GuzzleHttp\Client();

                $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($lat_long) . "+&sensor=false+CA&key=AIzaSyC8Jc4HBUsp9w_I9-rUTBS3t7v0atcBzWc";
                $geo = $guzzle->request('post', $url);
                $geo_result = json_decode($geo->getBody());


                $latitude = $geo_result->results[0]->geometry->location->lat;

                $longitude = $geo_result->results[0]->geometry->location->lng;

                $input['lat'] = $latitude;
                $input['lgn'] = $longitude;
            }
            catch(Exception $e){
                $input['lat'] = null;
                $input['lgn'] = null;
            }




            // return $input;
            $output = $this->contactUtil->createNewContact($input);
            // return $output;
            // return redirect()->back();
            $response = array(
                'status' => 'success',
                'success' => true,
                'msg' => 'Added successfully'
            );
            return response()->json($response);
            // return redirect()->back();

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                            'msg' =>$e->getMessage()
                        ];
                        return $output;
        }


    }

    public function showReferral($name){
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view') && !auth()->user()->can('customer.view_own') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact = $this->contactUtil->getContactInfoByName($business_id, $name);

        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($contact->type, ['customer', 'both'])) ? true : false;

        // $contact_dropdown = Contact::contactDropdown($business_id, false, false);
        $contact_dropdown = Contact::where('business_id',$business_id)->where('type',$contact->type)->get();

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        //get contact view type : ledger, notes etc.
        $view_type = request()->get('view');
        if (is_null($view_type)) {
            $view_type = 'ledger';
        }

        $contact_view_tabs = $this->moduleUtil->getModuleData('get_contact_view_tabs');
//        dd($contact);
        return view('contact.show')
            ->with(compact('contact', 'reward_enabled', 'contact_dropdown', 'business_locations', 'view_type', 'contact_view_tabs'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view') && !auth()->user()->can('customer.view_own') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact = $this->contactUtil->getContactInfo($business_id, $id);

        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($contact->type, ['customer', 'both'])) ? true : false;

        // $contact_dropdown = Contact::contactDropdown($business_id, false, false);
         $contact_dropdown = Contact::where('business_id',$business_id)->where('type',$contact->type)->active()->get();

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        //get contact view type : ledger, notes etc.
        $view_type = request()->get('view');
        if (is_null($view_type)) {
            $view_type = 'ledger';
        }

        $contact_view_tabs = $this->moduleUtil->getModuleData('get_contact_view_tabs');
//        dd($contact);

        $roles = Auth::user()->getRoleNameAttribute();

        return view('contact.show')
             ->with(compact('contact', 'reward_enabled', 'contact_dropdown', 'business_locations', 'view_type', 'contact_view_tabs','roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update') && !auth()->user()->can('customer.view_own') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::where('business_id', $business_id)->find($id);


            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $types = [];
            if (auth()->user()->can('supplier.create')) {
                $types['supplier'] = __('report.supplier');
            }
            if (auth()->user()->can('customer.create')) {
                $types['customer'] = __('report.customer');
            }
            // if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            //     $types['both'] = __('lang_v1.both_supplier_customer');
            // }

            $customer_groups = SellingPriceGroup::forDropdown($business_id);

            $ob_transaction =  Transaction::where('contact_id', $id)
                                            ->where('type', 'opening_balance')
                                            ->first();
            $opening_balance = !empty($ob_transaction->final_total) ? $ob_transaction->final_total : 0;

            //Deduct paid amount from opening balance.
            if (!empty($opening_balance)) {
                $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                if (!empty($opening_balance_paid)) {
                    $opening_balance = $opening_balance - $opening_balance_paid;
                }

                $opening_balance = $this->commonUtil->num_f($opening_balance);
            }

            $users = User::select('id','first_name','last_name')
                ->where('business_id',4)
                ->where('status','active')
                ->where('allow_login',1)
                ->where('sales_rep',1)
                ->orderBy('first_name')
            ->get();

            return view('contact.edit')
                ->with(compact('contact', 'types', 'customer_groups', 'opening_balance', 'users'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update') && !auth()->user()->can('customer.view_own') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        //if (request()->ajax()) {
            try {

                if($request->type == 'supplier'){
                    $input = $request->only(['type', 'contact_id','business_name1',
                        'prefix', 'first_name', 'middle_name', 'last_name','email', 'pay_term_number',
                        'pay_term_type', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country',
                        'address_line_1', 'address_line_2', 'zip_code' ,'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4',
                        'custom_field5', 'custom_field6', 'custom_field7', 'custom_field8',
                        'custom_field9', 'custom_field10', 'shipping_address','licnumber','balance']);

                }else{
                    $input = $request->only(['type', 'supplier_business_name', 'first_name',
                    'tax_number', 'pay_term_number', 'pay_term_type', 'mobile', 'address_line_1', '
                    address_line_2', 'zip_code', 'dob', 'alternate_number', 'city', 'state', 'country',
                    'landline', 'customer_group_id', 'contact_id', 'custom_field1', 'custom_field2',
                    'custom_field3', 'custom_field4', 'custom_field5', 'custom_field6', 'custom_field7',
                    'custom_field8', 'custom_field9', 'custom_field10', 'email', 'notify_email',
                    'shipping_address','tobacco_license_no', 'expiry_date',
                    'note','tax','file','is_nyc', 'position','cigar_customer','referal_code','payment_status']);
                }





                if($request->type == 'supplier'){

                    $business_id = $request->session()->get('user.business_id');

                    $input['contact_id'] = strtoupper($request->supplier_contact_id);
                    $input['supplier_business_name'] = strtoupper($request->supplier_business_name1);
                    $input['prefix'] = $request->supplier_prefix;
                    $input['first_name'] = strtoupper($request->supplier_first_name);
                    $input['middle_name'] = strtoupper($request->supplier_middle_name);
                    $input['last_name'] = strtoupper($request->supplier_last_name);
                    $input['email'] = strtoupper($request->supplier_email);
                    $input['mobile'] = $request->supplier_mobile;
                    $input['alternate_number'] = $request->supplier_alternate_number;
                    $input['landline'] = $request->supplier_landline;
                    $input['licnumber'] = strtoupper($request->supplier_licnumber);
                    $input['balance'] = $request->supplier_openbalance;
                    $input['pay_term_number'] = $request->supplier_pay_term_number;
                    $input['pay_term_type'] = $request->supplier_pay_term_type;
                    $input['address_line_1'] = strtoupper($request->supplier_address_line_1);
                    $input['address_line_2'] = strtoupper($request->supplier_address_line_2);
                    $input['city'] = strtoupper($request->supplier_city);
                    $input['state'] = strtoupper($request->supplier_state);
                    $input['country'] = strtoupper($request->supplier_country);
                    $input['zip_code'] = $request->supplier_zip_code;
                    $input['shipping_address'] = strtoupper($request->supplier_shipping_address);
                    $input['business_id'] = $business_id;
                    $input['created_by'] = $request->session()->get('user.id');
                    $input['name'] = $input['first_name'];
                }else{
                    $input['name'] = strtoupper($input['first_name']);

                    if($request->input('sync') == "on"){
                        $input['sync'] = null;
                    }
                    else{
                       $input['sync'] =  1;
                    }


                    $input['is_nyc'] = $request->input('is_nyc');
                    $input['whatsapp'] = $request->input('whatsapp');
                    $input['tax_number'] = strtoupper($request->input('tax'));
                    $input['supplier_business_name'] = strtoupper($request->supplier_business_name);
                    $input['address_line_1'] = strtoupper($request->address_line_1);
                    $input['tobacco_license_no'] = strtoupper($request->tobacco_license_no);
                    $input['expiry_date'] = $request->input('expiry_date');
                    $input['contact_person_1'] = strtoupper($request->contact_person_1);
                    $input['contact_person_2'] = strtoupper($request->contact_person_2);
                    $input['email'] = strtoupper($request->email);
                    $input['address_line_2'] = strtoupper($request->address_line_2);
                    $input['city'] = strtoupper($request->city);
                    $input['state'] = $request->input('state');
                    $input['first_name'] = strtoupper($request->first_name);
                    $input['name'] = strtoupper($request->first_name);

                    if(auth()->user()->can('customer.sales_rep')){
                        $input['sales_rep'] = $request->input('sales_rep');
                        $input['account_rep'] = $request->input('account_rep');
                    }

                    // $input['mobile'] = $request->whatsapp;
                    $input['created_by'] = $request->session()->get('user.id');
                    $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;


                    $business_id = $request->session()->get('user.business_id');
                    $input['opening_balance'] = $this->commonUtil->num_uf($request->input('opening_balance'));
                }


                // update lat long
                try{
                    $map_location =  $input['address_line_1'] . ',' . $input['city'] . ',' . $input['state'] . ',' . $input['country'] . ',' .$input['zip_code'];

                    $lat_long = json_encode($map_location);

                    $guzzle = new \GuzzleHttp\Client();

                    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($lat_long) . "+&sensor=false+CA&key=AIzaSyC8Jc4HBUsp9w_I9-rUTBS3t7v0atcBzWc";
                    $geo = $guzzle->request('post', $url);
                    $geo_result = json_decode($geo->getBody());


                    $latitude = $geo_result->results[0]->geometry->location->lat;

                    $longitude = $geo_result->results[0]->geometry->location->lng;

                    $input['lat'] = $latitude;
                    $input['lgn'] = $longitude;
                }
                catch(Exception $e){
                    $input['lat'] = null;
                    $input['lgn'] = null;
                }
                //end lat long
                if (!empty($input['dob'])) {
                    $input['dob'] = $this->commonUtil->uf_date($input['dob']);
                }

                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                }


                 $user_id = request()->session()->get('user.id');
                $log = $this->contactUtil->UpdateCustomerLog($input, $business_id, $user_id, $id);

                $output = $this->contactUtil->updateContact($input, $id, $business_id);

                Contact::where('id',$id)->update(["state"=>$request->input('state')]);

                $response = array(
                    'status' => 'success',
                    'success' => true,
                    'msg' => 'Updated successfully'
                );
                return response()->json($response);
                // return redirect()->back();

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                            'msg' => $e->getMessage()
                        ];
            }

            return $output;
        //}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('supplier.delete') && !auth()->user()->can('customer.delete') && !auth()->user()->can('customer.view_own') && !auth()->user()->can('supplier.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                //Check if any transaction related to this contact exists
                $count = Transaction::where('business_id', $business_id)
                                    ->where('contact_id', $id)
                                    ->count();
                if ($count == 0) {
                    $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                    if (!$contact->is_default) {
                        $contact->delete();
                    }
                    $output = ['success' => true,
                                'msg' => __("contact.deleted_success")
                                ];
                } else {
                    $output = ['success' => false,
                                'msg' => __("lang_v1.you_cannot_delete_this_contact")
                                ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    /**
     * Retrieves list of customers, if filter is passed then filter it accordingly.
     *
     * @param  string  $q
     * @return JSON
     */
    /*public function getCustomers()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $contacts = Contact::where('contacts.business_id', $business_id)
                            ->active();

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                ->where('uca.user_id', $user_id);
            }

            if (!empty($term)) {
                $contacts->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term .'%')
                            ->orWhere('supplier_business_name', 'like', '%' . $term .'%')
                            ->orWhere('mobile', 'like', '%' . $term .'%')
                            ->orWhere('contacts.contact_id', 'like', '%' . $term .'%')
                            ->orWhere('contacts.city', 'like', '%' . $term .'%')
                            ->orWhere('contacts.address_line_1', 'like', '%' . $term .'%')
                            ->orWhere('contacts.zip_code', 'like', '%' . $term .'%')
                            ;
                });
            }

            $contacts->select(
                'contacts.id',
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', name, CONCAT(name, ' (', contacts.contact_id, ')')) AS text"),
                'mobile',
                'address_line_1',
                'address_line_2',
                'zip_code',
                'email',
                'contact_person_1',
                'tax',
                'city',
                'state',
                'pay_term_number',
                'pay_term_type',
                'balance',
                'supplier_business_name',
                'customer_group_id'
            )
            ->onlyCustomers();

            if (request()->session()->get('business.enable_rp') == 1) {
                $contacts->addSelect('total_rp');
            }
            $contacts = $contacts->get();


            return json_encode($contacts);
        }
    }*/
    public function getCustomers()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $contacts = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')->where('contacts.business_id', $business_id)->whereIn('contacts.type', ['customer', 'both'])
                            ->active()->groupBy('contacts.id');

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                // added left join instead of simple join
                $contacts->leftJoin('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                ->where('uca.user_id', $user_id);
            }

            if (!empty($term)) {
                $contacts->where(function ($query) use ($term) {
                    $query->where('contacts.name', 'like', '%' . $term .'%')
                            ->orWhere('contacts.supplier_business_name', 'like', '%' . $term .'%')
                            ->orWhere('contacts.mobile', 'like', '%' . $term .'%')
                            ->orWhere('contacts.contact_id', 'like', '%' . $term .'%')
                            ->orWhere('contacts.city', 'like', '%' . $term .'%')
                            ->orWhere('contacts.address_line_1', 'like', '%' . $term .'%')
                            ->orWhere('contacts.zip_code', 'like', '%' . $term .'%')
                            ;
                });
            }

            $contacts->select(
                'contacts.id',
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', contacts.name, CONCAT(contacts.name, ' (', contacts.contact_id, ')')) AS text"),
                'contacts.mobile',
                'contacts.address_line_1',
                'contacts.address_line_2',
                'contacts.zip_code',
                'contacts.email',
                'contacts.contact_person_1',
                'contacts.tax',
                'contacts.city',
                'contacts.state',
                'contacts.pay_term_number',
                'contacts.pay_term_type',
                'contacts.balance',
                'supplier_business_name',
                'contacts.customer_group_id',
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return")
            );

            if (request()->session()->get('business.enable_rp') == 1) {
                $contacts->addSelect('total_rp');
            }
            $contacts = $contacts->get();

            foreach($contacts as $contact){
                $qry = $this->contactUtil->getContactInfo($business_id, $contact->id);
                $contact->new_calc_balance =  $qry->opening_balance - $qry->opening_balance_paid + $qry->total_invoice - $qry->invoice_received - $qry->balance - $qry->total_sell_return + $qry->sell_return_paid;
            }


            return json_encode($contacts);
        }
    }

    /**
     * Checks if the given contact id already exist for the current business.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkContactId(Request $request)
    {
        $contact_id = $request->input('contact_id');

        $valid = 'true';
        if (!empty($contact_id)) {
            $business_id = $request->session()->get('user.business_id');
            $hidden_id = $request->input('hidden_id');

            $query = Contact::where('business_id', $business_id)
                            ->where('contact_id', $contact_id);
            if (!empty($hidden_id)) {
                $query->where('id', '!=', $hidden_id);
            }
            $count = $query->count();
            if ($count > 0) {
                $valid = 'false';
            }
        }
        // echo $valid;
        // exit;
    }

    /**
     * Shows import option for contacts
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getImportContacts()
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        $zip_loaded = extension_loaded('zip') ? true : false;

        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = ['success' => 0,
                            'msg' => 'Please install/enable PHP Zip archive for import'
                        ];

            return view('contact.import')
                ->with('notification', $output);
        } else {
            return view('contact.import');
        }
    }

    /**
     * Imports contacts
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function postImportContacts(Request $request)
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }

            //Set maximum php execution time
            ini_set('max_execution_time', 0);

            if ($request->hasFile('contacts_csv')) {
                $file = $request->file('contacts_csv');
                $parsed_array = Excel::toArray([], $file);
                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                $formated_data = [];

                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    //Check if 27 no. of columns exists
                    if (count($value) != 27) {
                        $is_valid =  false;
                        $error_msg = "Number of columns mismatch";
                        break;
                    }

                    $row_no = $key + 1;
                    $contact_array = [];

                    //Check contact type
                    $contact_type = '';
                    $contact_types = [
                        1 => 'customer',
                        2 => 'supplier',
                        3 => 'both'
                    ];
                    if (!empty($value[0])) {
                        $contact_type = strtolower(trim($value[0]));
                        if (in_array($contact_type, [1, 2, 3])) {
                            $contact_array['type'] = $contact_types[$contact_type];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Invalid contact type $contact_type in row no. $row_no";
                            break;
                        }
                    } else {
                        $is_valid =  false;
                        $error_msg = "Contact type is required in row no. $row_no";
                        break;
                    }

                    $contact_array['prefix'] = $value[1];
                    //Check contact name
                    if (!empty($value[2])) {
                        $contact_array['first_name'] = $value[2];
                    } else {
                        $is_valid =  false;
                        $error_msg = "First name is required in row no. $row_no";
                        break;
                    }
                    $contact_array['middle_name'] = $value[3];
                    $contact_array['last_name'] = $value[4];
                    $contact_array['name'] = implode(' ', [$contact_array['prefix'], $contact_array['first_name'], $contact_array['middle_name'], $contact_array['last_name']]);

                    //Check supplier fields
                    if (in_array($contact_type, ['supplier', 'both'])) {
                        //Check business name
                        if (!empty(trim($value[5]))) {
                            $contact_array['supplier_business_name'] = $value[5];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Business name is required in row no. $row_no";
                            break;
                        }

                        //Check pay term
                        if (trim($value[9]) != '') {
                            $contact_array['pay_term_number'] = trim($value[9]);
                        } else {
                            $is_valid =  false;
                            $error_msg = "Pay term is required in row no. $row_no";
                            break;
                        }

                        //Check pay period
                        $pay_term_type = strtolower(trim($value[10]));
                        if (in_array($pay_term_type, ['days', 'months'])) {
                            $contact_array['pay_term_type'] = $pay_term_type;
                        } else {
                            $is_valid =  false;
                            $error_msg = "Pay term period is required in row no. $row_no";
                            break;
                        }
                    }

                    //Check contact ID
                    if (!empty(trim($value[6]))) {
                        $count = Contact::where('business_id', $business_id)
                                    ->where('contact_id', $value[6])
                                    ->count();


                        if ($count == 0) {
                            $contact_array['contact_id'] = $value[6];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Contact ID already exists in row no. $row_no";
                            break;
                        }
                    }

                    //Tax number
                    if (!empty(trim($value[7]))) {
                        $contact_array['tax_number'] = $value[7];
                    }

                    //Check opening balance
                    if (!empty(trim($value[8])) && $value[8] != 0) {
                        $contact_array['opening_balance'] = trim($value[8]);
                    }

                    //Check credit limit
                    if (trim($value[11]) != '' && in_array($contact_type, ['customer', 'both'])) {
                        $contact_array['credit_limit'] = trim($value[11]);
                    }

                    //Check email
                    if (!empty(trim($value[12]))) {
                        if (filter_var(trim($value[12]), FILTER_VALIDATE_EMAIL)) {
                            $contact_array['email'] = $value[12];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Invalid email id in row no. $row_no";
                            break;
                        }
                    }

                    //Mobile number
                    if (!empty(trim($value[13]))) {
                        $contact_array['mobile'] = $value[13];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Mobile number is required in row no. $row_no";
                        break;
                    }

                    //Alt contact number
                    $contact_array['alternate_number'] = $value[14];

                    //Landline
                    $contact_array['landline'] = $value[15];

                    //City
                    $contact_array['city'] = $value[16];

                    //State
                    $contact_array['state'] = $value[17];

                    //Country
                    $contact_array['country'] = $value[18];

                    //address_line_1
                    $contact_array['address_line_1'] = $value[19];
                    //address_line_2
                    $contact_array['address_line_2'] = $value[20];
                    $contact_array['zip_code'] = $value[21];
                    $contact_array['dob'] = $value[22];

                    //Cust fields
                    $contact_array['custom_field1'] = $value[23];
                    $contact_array['custom_field2'] = $value[24];
                    $contact_array['custom_field3'] = $value[25];
                    $contact_array['custom_field4'] = $value[26];

                    $formated_data[] = $contact_array;
                }
                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                if (!empty($formated_data)) {
                    foreach ($formated_data as $contact_data) {
                        $ref_count = $this->transactionUtil->setAndGetReferenceCount('contacts');
                        //Set contact id if empty
                        if (empty($contact_data['contact_id'])) {
                            $contact_data['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count);
                        }

                        $opening_balance = 0;
                        if (isset($contact_data['opening_balance'])) {
                            $opening_balance = $contact_data['opening_balance'];
                            unset($contact_data['opening_balance']);
                        }

                        $contact_data['business_id'] = $business_id;
                        $contact_data['created_by'] = $user_id;

                        $contact = Contact::create($contact_data);

                        if (!empty($opening_balance)) {
                            $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $opening_balance, $user_id);
                        }
                    }
                }

                $output = ['success' => 1,
                            'msg' => __('product.file_imported_successfully')
                        ];

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return redirect()->route('contacts.import')->with('notification', $output);
        }
        $type = !empty($contact->type) && $contact->type != 'both' ? $contact->type : 'supplier';
        return redirect()->action('ContactController@index', ['type' => $type])->with('status', $output);
    }

    /**
     * Shows ledger for contacts
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getLedger()
    {

        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }
        $advance_balance  =  0;
        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->input('contact_id');

        $start_date = request()->start_date;
        $end_date =  request()->end_date;

        $contact = Contact::find($contact_id);
        // return $contact;
        if(!empty($contact)) $advance_balance  =  $contact->balance;


        $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start_date, $end_date, $advance_balance);

        // return $ledger_details;
        if (request()->input('action') == 'pdf') {
            $for_pdf = true;
            $html = view('contact.ledger')
             ->with(compact('ledger_details', 'contact', 'for_pdf'))->render();
            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);
            $filename = trim($contact->supplier_business_name) . ' - Ledger.pdf';
            if(!empty($contact->name)){
                $filename = trim($contact->name) . ' - Ledger.pdf';
            }
            $mpdf->Output($filename, 'I');
        }

        return view('contact.ledger')
             ->with(compact('ledger_details', 'contact'));
    }

    public function postCustomersApi(Request $request)
    {
        try {
            $api_token = $request->header('API-TOKEN');

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $business = Business::find($api_settings->business_id);

            $data = $request->only(['name', 'email']);

            $customer = Contact::where('business_id', $api_settings->business_id)
                                ->where('email', $data['email'])
                                ->whereIn('type', ['customer', 'both'])
                                ->first();

            if (empty($customer)) {
                $data['type'] = 'customer';
                $data['business_id'] = $api_settings->business_id;
                $data['created_by'] = $business->owner_id;
                $data['mobile'] = 0;

                $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts', $business->id);

                $data['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count, $business->id);

                $customer = Contact::create($data);
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($customer);
    }

    /**
     * Function to send ledger notification
     *
     */
    public function sendLedger(Request $request)
    {
        $notAllowed = $this->notificationUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $data = $request->only(['to_email', 'subject', 'email_body', 'cc', 'bcc']);
            $emails_array = array_map('trim', explode(',', $data['to_email']));

            $contact_id = $request->input('contact_id');
            $business_id = request()->session()->get('business.id');

            $start_date = request()->input('start_date');
            $end_date =  request()->input('end_date');

            $contact = Contact::find($contact_id);

            $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start_date, $end_date,null);


            $orig_data = [
                'email_body' => $data['email_body'],
                'subject' => $data['subject']
            ];

            $tag_replaced_data = $this->notificationUtil->replaceTags($business_id, $orig_data, null, $contact);
            $data['email_body'] = $tag_replaced_data['email_body'];
            $data['subject'] = $tag_replaced_data['subject'];

            //replace balance_due
            $data['email_body'] = str_replace('{balance_due}', $this->notificationUtil->num_f($ledger_details['balance_due']), $data['email_body']);

            $data['email_settings'] = request()->session()->get('business.email_settings');


            $for_pdf = true;
            $html = view('contact.ledger')
             ->with(compact('ledger_details', 'contact', 'for_pdf'))->render();
            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);

            $file = config('constants.mpdf_temp_path') . '/' . time() . '_ledger.pdf';
            $mpdf->Output($file, 'F');

            $data['attachment'] =  $file;
            $data['attachment_name'] =  'ledger.pdf';
            \Notification::route('mail', $emails_array)
                    ->notify(new CustomerNotification($data));

            if (file_exists($file)) {
                unlink($file);
            }

            $output = ['success' => 1, 'msg' => __('lang_v1.notification_sent_successfully')];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => "File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage()
                        ];
        }

        return $output;
    }

    /**
     * Function to get product stock details for a supplier
     *
     */
    public function getSupplierStockReport($supplier_id)
    {
        $pl_query_string = $this->commonUtil->get_pl_quantity_sum_string();
        $query = PurchaseLine::join('transactions as t', 't.id', '=', 'purchase_lines.transaction_id')
                        ->join('products as p', 'p.id', '=', 'purchase_lines.product_id')
                        ->join('variations as v', 'v.id', '=', 'purchase_lines.variation_id')
                        ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                        ->join('units as u', 'p.unit_id', '=', 'u.id')
                        ->where('t.type', 'purchase')
                        ->where('t.contact_id', $supplier_id)
                        ->select(
                            'p.name as product_name',
                            'v.name as variation_name',
                            'pv.name as product_variation_name',
                            'p.type as product_type',
                            'u.short_name as product_unit',
                            'v.sub_sku',
                            'v.id as vid',
                            DB::raw('SUM(quantity) as purchase_quantity'),
                            DB::raw('SUM(quantity_returned) as total_quantity_returned'),
                            DB::raw('SUM(quantity_sold) as total_quantity_sold'),
                            DB::raw("SUM( COALESCE(quantity - ($pl_query_string), 0) * purchase_price_inc_tax) as stock_price"),
                            DB::raw("SUM( COALESCE(quantity - ($pl_query_string), 0)) as current_stock")
                        )->groupBy('purchase_lines.variation_id');

        if (!empty(request()->location_id)) {
            $query->where('t.location_id', request()->location_id);
        }

        $product_stocks =  Datatables::of($query)
                            ->editColumn('product_name', function ($row) {
                                $name = $row->product_name;
                                if ($row->product_type == 'variable') {
                                    $name .= ' - ' . $row->product_variation_name . '-' . $row->variation_name;
                                }
                                return $name . ' (' . $row->sub_sku . ')';
                            })
                            ->editColumn('purchase_quantity', function ($row) {
                                $purchase_quantity = 0;
                                if ($row->purchase_quantity) {
                                    $purchase_quantity =  (float)$row->purchase_quantity;
                                }

                                return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false  data-orig-value="' . $purchase_quantity . '" data-unit="' . $row->product_unit . '" >' . $purchase_quantity . '</span> ' . $row->product_unit;
                            })
                            ->editColumn('total_quantity_sold', function ($row) {
                                $total_quantity_sold = 0;
                                if ($row->total_quantity_sold) {
                                    $total_quantity_sold =  (float)$row->total_quantity_sold;
                                }

                                return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false  data-orig-value="' . $total_quantity_sold . '" data-unit="' . $row->product_unit . '" >' . $total_quantity_sold . '</span> ' . $row->product_unit;
                            })
                            ->editColumn('stock_price', function ($row) {
                                $stock_price = 0;
                                if ($row->stock_price) {
                                    $stock_price =  (float)$row->stock_price;
                                }
                                $current_stock = VariationLocationDetails::where('variation_id',$row->vid)->first()->qty_available;
                                $pp = Variation::find($row->vid)->dpp_inc_tax;
                                $stock_price = number_format($current_stock * $pp,2);

                                return '<span class="display_currency" data-currency_symbol=true >' . $stock_price . '</span> ';
                            })
                            ->editColumn('current_stock', function ($row) {
                                $current_stock = 0;
                                if ($row->current_stock) {
                                    $current_stock =  (float)$row->current_stock;
                                }
                                $current_stock = VariationLocationDetails::where('variation_id',$row->vid)->first()->qty_available;

                                return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false  data-orig-value="' . $current_stock . '" data-unit="' . $row->product_unit . '" >' . $current_stock . '</span> ' . $row->product_unit;
                            });

        return $product_stocks->rawColumns(['current_stock', 'stock_price', 'total_quantity_sold', 'purchase_quantity'])->make(true);
    }

    public function updateStatus($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::where('business_id', $business_id)->find($id);
            $contact->contact_status = $contact->contact_status == 'active' ? 'inactive' : 'active';
            $contact->save();

            $output = ['success' => true,
                                'msg' => __("contact.updated_success")
                                ];
            return $output;
        }
    }

    /**
     * Display contact locations on map
     *
     */
    public function contactMap()
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $query = Contact::where('business_id', $business_id)
                        ->active()
                        ->whereNotNull('position');

        if (!empty(request()->input('contacts'))) {
            $query->whereIn('id', request()->input('contacts'));
        }
        $contacts = $query->get();

        $all_contacts = Contact::where('business_id', $business_id)
                        ->active()
                        ->get();

        return view('contact.contact_map')
             ->with(compact('contacts', 'all_contacts'));
    }

    public function getContactPayments($contact_id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {

            $payments = TransactionPayment::leftjoin('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')
            ->leftjoin('transaction_payments as parent_payment', 'transaction_payments.parent_id', '=', 'parent_payment.id')
            ->leftJoin('users AS usr', 'transaction_payments.created_by', '=', 'usr.id')
            ->where('transaction_payments.business_id', $business_id)
            ->whereNull('transaction_payments.parent_id')
            ->with(['child_payments', 'child_payments.transaction'])
            ->where('transaction_payments.payment_for', $contact_id)
                ->select(
                    'transaction_payments.id',
                    'transaction_payments.amount',
                    'transaction_payments.is_return',
                    'transaction_payments.method',
                    'transaction_payments.paid_on',
                    'transaction_payments.payment_ref_no',
                    'transaction_payments.parent_id',
                    'transaction_payments.transaction_no',
                    'transaction_payments.payment_location',
                    't.invoice_no',
                    't.ref_no',
                    't.type as transaction_type',
                    't.return_parent_id',
                    't.id as transaction_id',
                    'transaction_payments.cash_note',
                    'transaction_payments.cheque_number',
                    'transaction_payments.card_transaction_number',
                    'transaction_payments.bank_account_number',
                    'transaction_payments.id as DT_RowId',
                    'parent_payment.payment_ref_no as parent_payment_ref_no',
                    DB::raw("CONCAT(COALESCE(usr.surname, ''),' ',COALESCE(usr.first_name, ''),' ',COALESCE(usr.last_name,'')) as created_by")
                )
                ->groupBy('transaction_payments.id')
                ->orderByDesc('transaction_payments.paid_on')
                ->paginate();

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

            return view('contact.partials.contact_payments_tab')
                    ->with(compact('payments', 'payment_types'));
        }
    }

    public function getReferralCompany(Request $request){
        $searchKey = $request->keys;
        $companyList = \Illuminate\Support\Facades\DB::table('contacts')
            ->where('supplier_business_name', 'LIKE', "%{$searchKey}%")
            ->select('supplier_business_name')
            ->get();
        return response()->json($companyList);
    }

    // public function showpaymentdetail($id){
    //     $business_id = request()->session()->get('user.business_id');
    //     $taxes = TaxRate::where('business_id', $business_id)
    //                         ->pluck('name', 'id');
    //     $purchase = Transaction::where('business_id', $business_id)
    //                             ->where('id', $id)
    //                             ->with(
    //                                 'contact',
    //                                 'purchase_lines',
    //                                 'purchase_lines.product',
    //                                 'purchase_lines.product.unit',
    //                                 'purchase_lines.variations',
    //                                 'purchase_lines.variations.product_variation',
    //                                 'purchase_lines.sub_unit',
    //                                 'location',
    //                                 'payment_lines',
    //                                 'tax'
    //                             )
    //                             ->firstOrFail();

    //     foreach ($purchase->purchase_lines as $key => $value) {
    //         if (!empty($value->sub_unit_id)) {
    //             $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
    //             $purchase->purchase_lines[$key] = $formated_purchase_line;
    //         }
    //     }

    //     $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

    //     $purchase_taxes = [];
    //     if (!empty($purchase->tax)) {
    //         if ($purchase->tax->is_tax_group) {
    //             $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
    //         } else {
    //             $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
    //         }
    //     }

    //     return view('purchase.show')
    //             ->with(compact('taxes', 'purchase', 'payment_methods', 'purchase_taxes'));
    // }
    public function checkemail(Request $request){
        $email = $request->email;
        $type = $request->type;
        if($type == 'supplier'){
            $contact = Contact::where('email',$email)->where('type','supplier')->first();
        }
        if($type == 'customer'){
            $contact = Contact::where('email',$email)->where('type','customer')->first();
        }
           if($contact){
               $data = [
                   'success' => true,
                   'message'=> 'Email Already Exists'
               ];
           }
           else{
               $data = [
                   'success' =>false,
                   'message'=> ''
               ];
           }
        return response()->json($data);
    }

    public function checkemailedit(Request $request){
        $email = $request->email;
        $contact_id = $request->contact_id;
        $type = $request->type;
        if($type == 'supplier'){
            $contact = Contact::where('id','!=',$contact_id)->where('email',$email)->where('type','supplier')->first();
        }
        if($type == 'customer'){
            $contact = Contact::where('id','!=',$contact_id)->where('email',$email)->where('type','customer')->first();
        }

        if($contact){
            $data = [
                'success' => true,
                'message'=> 'Email Already Exists'
              ];
         }
         else{
            $data = [
                'success' =>false,
                'message'=> ''
            ];
         }
         return response()->json($data);
    }

    public function bulkeditcustomer(Request $request){

        $customer_account_rep = $request->customer_account_rep;
        $customer_sales_rep = $request->customer_sales_rep;
        $customer_id = $request->customer_id;

        $cust_id = explode(',',$customer_id);

        $contacts = Contact::find($cust_id);

        if(count($contacts) > 0){

            foreach($contacts as $contact){
                $customer = Contact::findOrFail($contact->id);
                $customer->account_rep = $customer_account_rep;
                $customer->sales_rep = $customer_sales_rep;
                $customer->save();
            }
        }


        $output = ['success' => 1,
            'msg' => __('Updated Successfully')
        ];
        return $output;
    }
    public function getContactLogs($contact_id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {

            $activity_logs = Customerlog::join('users','users.id','=','customer_log.user_id')
              ->where('contact_id',$contact_id)
              ->whereIn('description', ['added', 'edited'])
                ->select('customer_log.created_at as datetime',
                    'customer_log.message as message',
                    'customer_log.description as description',
                    'users.first_name as first_name'
                )
                ->get();

            return view('contact.partials.customer_log_tab')
                    ->with(compact('activity_logs'));
        }
    }
     public function getFollowUpDetails($contact_id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {

            $followup = FollowUp::with(['contact', 'agent'])->where('contact_id', $contact_id)->orderBy('created_at', 'DESC')->get();

            $customers = Contact::customersDropdown($business_id, false);
            $users = User::forDropdown($business_id);
            $status = FollowUp::keyValueMap(FollowUp::STATUS);
            $priorities = FollowUp::keyValueMap(FollowUp::PRIORITIES);
            $channel = FollowUp::keyValueMap(FollowUp::CHANNEL);

            return view('contact.partials.followup_tab', compact('followup', 'customers','users','status','priorities','channel'));
        }
    }
}
