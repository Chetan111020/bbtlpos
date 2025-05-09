<?php
/* LICENSE: This source file belongs to The Web Fosters. The customer
 * is provided a licence to use it.
 * Permission is hereby granted, to any person obtaining the licence of this
 * software and associated documentation files (the "Software"), to use the
 * Software for personal or business purpose ONLY. The Software cannot be
 * copied, published, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. THE AUTHOR CAN FIX
 * ISSUES ON INTIMATION. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     The Web Fosters <thewebfosters@gmail.com>
 * @owner      The Web Fosters <thewebfosters@gmail.com>
 * @copyright  2018 The Web Fosters
 * @license    As attached in zip file.
 */
namespace App\Http\Controllers;

use App\Models\NeatTxnLog;
use App\Models\NeatTxnProLog;
use App\Models\NeatTxnInfoLogs;

use App\Account;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\Media;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\ProductVariation;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\Warranty;
use App\InvoiceLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\InvoiceScheme;
use PDF;
use ZipArchive;
use File;
use QrCode;
use App\Delinvoicelog;
use App\Helpers\RequestLogHelper;
use App\Http\Controllers\__Compute\InvoiceController;
use App\VariationGroupPrice;
use Dompdf\Dompdf;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File as FacadesFile;
use App\Notifications\InvoiceRepairReportNotification;
use Illuminate\Support\Facades\Notification;

class SellPosController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $contactUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $cashRegisterUtil;
    protected $moduleUtil;
    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
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
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }


        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');

        //Service staff filter
        $service_staffs = null;
        if ($is_service_staff_enabled) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        // $items = DB::table('products')
        //     ->join('variations', 'items.id', '=', 'variations.id')
        //     ->select('products.id,', 'variation.id','tax_rates.id')
        //     ->get();
        //     dd($items);
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        return view('sale_pos.index')->with(compact('business_locations', 'customers', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses','categories'));
        // ,'items'

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $transaction = [];
        // $trans_id = request()->get('duplicate_id');
        $trans_id = request()->session()->get('duplicate_id');
        request()->session()->forget('duplicate_id');

        $invoice_no = null;
        $sell_details = [];
        $back_order_customer = null;
        if($trans_id)
        {
            $transaction = Transaction::where('business_id', $business_id)
                                ->where('type', 'sell')
                                ->select('id','contact_id')
                                ->findorfail($trans_id);
            if($transaction)
            {
                $sell_details = $this->sell($transaction->id, $business_id);
                $invoice_no = $transaction->id;
            }

            if(!empty(request()->input('back_order'))){
                foreach($sell_details as $index=>$sells){
                    $sells->box_no = '';
                    if($sells->quantity_ordered > $sells->qty_available){
                        $sell_details->forget($index);
                    }
                }
                $back_order_customer = Contact::find($transaction->contact_id);
            }
            else{
                foreach($sell_details as $index=>$sells){
                    $sells->box_no = '';
                }
            }
        }

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('sell.create') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('repair.create')))) {
            abort(403, 'Unauthorized action.');
        }

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('HomeController@index'));
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
        }

        //like:repair
        $sub_type = request()->get('sub_type');

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create', ['sub_type' => $sub_type]);
        }

        $register_details = $this->cashRegisterUtil->getCurrentCashRegister(auth()->user()->id);

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $payment_lines[] = $this->dummyPaymentLine;

        $default_location = !empty($register_details->location_id) ? BusinessLocation::findOrFail($register_details->location_id) : null;

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        //set first location as default locaton
        if (empty($default_location)) {
            foreach ($business_locations as $id => $name) {
                $default_location = BusinessLocation::findOrFail($id);
                break;
            }
        }

        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        //Shortcuts
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_price_group_id = !empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        //Types of service
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $default_datetime = $this->businessUtil->format_date('now', true);

        $featured_products = !empty($default_location) ? $default_location->getFeaturedProducts() : [];

        //pos screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type, 'job_sheet_id' => request()->get('job_sheet_id')]);
        $invoice_layouts = InvoiceLayout::forDropdown($business_id);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        $search_categories = Category::forDropdown($business_id, 'product')->toArray();
        $packed = null;
        return view('sale_pos.create')
            ->with(compact(
                'business_locations',
                'bl_attributes',
                'business_details',
                'taxes',
                'payment_types',
                'walk_in_customer',
                'payment_lines',
                'default_location',
                'shortcuts',
                'commission_agent',
                'categories',
                'brands',
                'pos_settings',
                'change_return',
                'types',
                'customer_groups',
                'accounts',
                'price_groups',
                'types_of_service',
                'default_price_group_id',
                'shipping_statuses',
                'default_datetime',
                'featured_products',
                'sub_type',
                'pos_module_data',
                'invoice_schemes',
                'default_invoice_schemes',
                'invoice_layouts',
                'search_categories',
                'packed',
                'invoice_no',
                'sell_details',
                'back_order_customer'
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
        if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $is_direct_sale = false;
        if (!empty($request->input('is_direct_sale'))) {
            $is_direct_sale = true;
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        try {
            $input = $request->except('_token');
            $input['status'] = $request->status;
            $input['final_total'] = $request->final_total;
            $input['discount_type'] = $request->discount_type;
            $input['discount_amount'] = $request->discount_amount;
            if (isset($request->tax_deal)) {
                $input['tax_deal'] = $request->tax_deal;
            }
            // if($request->discount_type != ''){
            //     $discount_amount = $request->discount_amount;
            //     if($request->discount_type == 'percentage'){
            //         $discount_amount = ($request->amount * $request->discount_amount)/100;
            //     }
            //     $input['discount_amount'] = $discount_amount;
            //     $input['discount_type'] = 'fixed';
            // }
            $input['tax_rate_id'] = $request->tax_rate_id;
            $input['change_return'] = $request->change_return;
            // Check Customer credit limit
            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);

            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = ['success' => 0,
                            'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                        ];
                if (!$is_direct_sale) {
                    return $output;
                } else {
                    return redirect()
                        ->action('SellController@index')
                        ->with('status', $output);
                }
            }

            $input['is_quotation'] = 0;
            //status is send as quotation from Add sales screen.
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
            }

            if ($input['status'] == 'payment-verified') {
                $input['status'] = 'payment-verified';
            }


            if (!empty($input['products'])) {

                //check for defauly sell price
                $CheckForDefaultSellPrice = $this->transactionUtil->CheckForDefaultSellPrice($input['products']);

                if($CheckForDefaultSellPrice != "")
                {
                    return $this->respond(
                    ['success' => 0,
                            'msg' => $CheckForDefaultSellPrice
                        ]);
                }
                //check for defauly sell price

                $business_id = $request->session()->get('user.business_id');

                //Check if subscribed or not, then check for users quota
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                    return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
                }

                $user_id = $request->session()->get('user.id');

                $discount = ['discount_type' => $input['discount_type'],
                                'discount_amount' => $input['discount_amount']
                            ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                DB::beginTransaction();

                if (empty($request->input('transaction_date'))) {
                    $input['transaction_date'] =  \Carbon::now();
                } else {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }
                if ($is_direct_sale) {
                    $input['is_direct_sale'] = 1;
                }

                //Set commission agent
                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                $price_group_id = $request->has('price_group') ? $request->input('price_group') : null;

                //If default price group for the location exists
                $price_group_id = $price_group_id == 0 && $request->has('default_price_group') ? $request->input('default_price_group') : $price_group_id;

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend']  ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                //Generate reference number
                if (!empty($input['is_recurring'])) {
                    //Update reference count
                    $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
                    $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);
                }

                if (!empty($request->input('invoice_scheme_id'))) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                if (!empty($request->input('priority_order'))) {
                    $input['priority_order'] = 1;
                }else{
                    $input['priority_order'] = 0;
                }
                //Types of service
                if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
                    $input['types_of_service_id'] = $request->input('types_of_service_id');
                    $price_group_id = !empty($request->input('types_of_service_price_group')) ? $request->input('types_of_service_price_group') : $price_group_id;
                    $input['packing_charge'] = !empty($request->input('packing_charge')) ?
                    $this->transactionUtil->num_uf($request->input('packing_charge')) : 0;
                    $input['packing_charge_type'] = $request->input('packing_charge_type');
                    $input['service_custom_field_1'] = !empty($request->input('service_custom_field_1')) ?
                    $request->input('service_custom_field_1') : null;
                    $input['service_custom_field_2'] = !empty($request->input('service_custom_field_2')) ?
                    $request->input('service_custom_field_2') : null;
                    $input['service_custom_field_3'] = !empty($request->input('service_custom_field_3')) ?
                    $request->input('service_custom_field_3') : null;
                    $input['service_custom_field_4'] = !empty($request->input('service_custom_field_4')) ?
                    $request->input('service_custom_field_4') : null;
                }

                $input['selling_price_group_id'] = $price_group_id;

                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $input['res_table_id'] = request()->get('res_table_id');
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $input['res_waiter_id'] = request()->get('res_waiter_id');
                }

                $itemQTY = count($input['products']);
                $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id,$itemQTY, true, $input['is_suspend'] );

                RequestLogHelper::LogTransaction($request, $transaction, RequestLogHelper::LOG_TYPE_TRANSACTION_CREATE);

                //added by developer1 POS unit price log
                $price_group_details = VariationGroupPrice::where('price_group_id', $price_group_id)->get();
                //added by developer1 POS unit price log

                // $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);
                $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], false, null, [], true, $price_group_id);

                //added by developer1
                $transaction_id = $transaction->id;
                //$this->transactionUtil->Delinvoicelog('added',$user_id,$transaction_id);
                //added by developer1

                //added by developer1 POS unit price log
                //$this->transactionUtil->SellLinesSalePricelog($user_id , '' ,$transaction_id, $price_group_details);
                $this->transactionUtil->CreatedSellLinesSalePricelog($user_id , '' ,$transaction_id, $price_group_details);
                //added by developer1 POS unit price log

                if (!$is_direct_sale) {
                    //Add change return
                    $change_return = $this->dummyPaymentLine;
                    $change_return['amount'] = $input['change_return'];
                    $change_return['is_return'] = 1;
                    $input['payment'][] = $change_return;
                }

                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;

                if (!$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                }

                //Check for final and do some processing.
                if ($input['status'] == 'final') {
                    //update product stock
                    foreach ($input['products'] as $product) {
                        $decrease_qty = $this->productUtil
                                    ->num_uf($product['quantity']);
                        if (!empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        if ($product['enable_stock']) {
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $input['location_id'],
                                $decrease_qty
                            );
                        }

                        if ($product['product_type'] == 'combo') {
                            //Decrease quantity of combo as well.
                            $this->productUtil
                                ->decreaseProductQuantityCombo(
                                    $product['combo'],
                                    $input['location_id']
                                );
                        }
                    }

                    //Add payments to Cash Register
                    if (!$is_direct_sale && !$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                        $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
                    }

                    //Update payment status
                    $this->transactionUtil->updatePaymentStatus($transaction->id, null, 0, 0, $transaction->final_total);

                    if ($request->session()->get('business.enable_rp') == 1) {
                        $redeemed = !empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0;
                        $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, 0, $redeemed);
                    }

                    //Allocate the quantity from purchase and add mapping of
                    //purchase & sell lines in
                    //transaction_sell_lines_purchase_lines table
                    $business_details = $this->businessUtil->getDetails($business_id);
                    $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                    $business = ['id' => $business_id,
                                    'accounting_method' => $request->session()->get('business.accounting_method'),
                                    'location_id' => $input['location_id'],
                                    'pos_settings' => $pos_settings
                                ];
                    $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

                    //Auto send notification
                    $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                //Set Module fields
                if (!empty($input['has_module_data'])) {
                    $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
                }

                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                activity()
                ->performedOn($transaction)
                ->log('added');

                //if(auth()->user()->id == 6){
                //    dd($input);
                //}

                DB::commit();

                //New Organized Log
                $transactionId = $transaction->id;
                $ntl_id = NeatTxnLog::logActivity($transactionId, 'Created');
                NeatTxnInfoLogs::logActivity($ntl_id->id, $input, $transactionId, 'Created');
                NeatTxnProLog::logActivity($ntl_id->id, $transactionId, $input['products']);

                if ($request->input('is_save_and_print') == 1) {
                    $url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);
                    return redirect()->to($url . '?print_on_load=true');
                }

                $msg = trans("sale.pos_sale_added");
                $receipt = '';
                $invoice_layout_id = $request->input('invoice_layout_id');
                $print_invoice = false;
                if (!$is_direct_sale) {
                    if ($input['status'] == 'draft') {
                        $msg = trans("sale.draft_added");

                        if ($input['is_quotation'] == 1) {
                            $msg = trans("lang_v1.quotation_added");
                            $print_invoice = true;
                        }
                    } elseif ($input['status'] == 'payment-verified') {
                        $msg = trans("Payment Verified Sucessfully");
                        $print_invoice = true;
                    } elseif ($input['status'] == 'final') {
                        $print_invoice = true;
                    }
                }

                if ($transaction->is_suspend == 1 && empty($pos_settings['print_on_suspend'])) {
                    $print_invoice = false;
                }

                if(isset($input['finalize_no_print']) && $input['finalize_no_print']){
                    $print_invoice = false;
                }

                if ($print_invoice) {
                    self::theInvoiceRepairer();
                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, 'html_content', true, $invoice_layout_id);
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt , 'autosave_id' => $transaction->id];
                self::theInvoiceRepairer();
            } else {
                $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
            }
        } catch (\Exception $e) {
            return $e;
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $msg = trans("messages.something_went_wrong");

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }

            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }

        if (!$is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            } elseif ($input['status'] == 'quotation') {
                return redirect()
                    ->action('SellController@getQuotations')
                    ->with('status', $output);
            } elseif ($input['status'] == 'payment-verified') {
                return redirect()
                    ->action('SellController@getPaymentVerified')
                    ->with('status', $output);
            } else {
                if (!empty($input['sub_type']) && $input['sub_type'] == 'repair') {
                    $redirect_url = $input['print_label'] == 1 ? action('\Modules\Repair\Http\Controllers\RepairController@printLabel', [$transaction->id]) : action('\Modules\Repair\Http\Controllers\RepairController@index');
                    return redirect($redirect_url)
                        ->with('status', $output);
                }
                return redirect()
                    ->action('SellController@index')
                    ->with('status', $output);
            }
        }
    }

    /**
     * Returns the content for the receipt
     *
     * @param  int  $business_id
     * @param  int  $location_id
     * @param  int  $transaction_id
     * @param string $printer_type = null
     *
     * @return array
     */
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null,
        $is_package_slip = false,
        $from_pos_screen = true,
        $invoice_layout_id = null
    ) {
        $output = ['is_enabled' => false,
                    'print_type' => 'browser',
                    'html_content' => null,
                    'printer_config' => [],
                    'data' => []
                ];
        // $contact = Contact::find($customer_id);

        $business_details = $this->businessUtil->getDetails($business_id);

        $location_details = BusinessLocation::find($location_id);

        if ($from_pos_screen && $location_details->print_receipt_on_invoice != 1) {
            return $output;
        }
        //Check if printing of invoice is enabled or not.
        //If enabled, get print type.
        $output['is_enabled'] = true;

        $invoice_layout_id = !empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_id, $invoice_layout_id);

        //Check if printer setting is provided.
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);
        $tax_details = $this->transactionUtil->getTaxDetails($transaction_id);
        $currency_details = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator,
        ];
        // $credit_memo = $this->driverinvoice('107054');
        // echo "<pre>";
        // print_r($receipt_details);
        // die;
        $transaction = Transaction::where('id', $transaction_id)->with(['business', 'location'])->first();

        // $contact_id = $receipt_details->contact_id;
        $start_date = '2021-01-01';
        $end_date = date('Y-m-d H:i:s');;
        $advance_balance = 0;
        $contact = Contact::find($transaction->contact_id);
        if(!empty($contact)) $advance_balance  =  $contact->balance;
        if(!empty($contact)) $receipt_details->contact_person_1  = !empty($contact->contact_person_1) ?  $contact->contact_person_1 : '';


        $ledger_details = null;
        $ledger_details = $this->transactionUtil->getLedgerDetails($transaction->contact_id, $start_date, $end_date, $advance_balance);

        if(!empty($contact->sales_rep)){
            $sales_rep  = User::select('first_name','last_name')->where('id',$contact->sales_rep )->first();
            $receipt_details->sales_rep = $sales_rep->first_name ." ". $sales_rep->last_name;
        }else{
            $receipt_details->sales_rep = '';
        }

        $receipt_details->currency = $currency_details;

        $last_invoices = Transaction::select('id','final_total','invoice_no','transaction_date','payment_status','type')
            ->where('contact_id', $transaction->contact_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->where('id','!=', $transaction_id)
            ->latest()
            ->take(5)
            ->get();

        $jadoo_products = $this->productUtil->GetJadooProductlist();
        /*echo "<pre>";
        print_r($jadoo_products);
        exit;*/

        // $blank_slip_number = $transaction->invoice_no ?? '0';
        $blank_slip_number = '1';
        $blank_slip_number .= $transaction->contact->zip_code ?? '0';
        // $blank_slip_number .= $transaction->contact->mobile ?? '0';
        $blank_slip_number .= preg_replace('/[^0-9]/', '', $transaction->contact->mobile ?? '0');

        if ($is_package_slip) {
            $output['html_content'] = view('sale_pos.receipts.packing_slip', compact('receipt_details','tax_details','jadoo_products'))->render();
            $output['html_content1'] = view('sale_pos.receipts.driver_invoice', compact('receipt_details','tax_details','ledger_details','last_invoices'))->render();
            $output['html_content2'] = view('sale_pos.receipts.classic', compact('receipt_details','tax_details','jadoo_products'))->render();
            $output['html_content3'] = view('sale_pos.receipts.invoicegen', compact('receipt_details','tax_details'))->render();
            $output['html_content4'] = view('sale_pos.receipts.packing_gen', compact('receipt_details','tax_details'))->render();
            $output['html_content5'] = view('sale_pos.receipts.packing_slip_blank', compact('blank_slip_number','receipt_details','tax_details','jadoo_products'))->render();
            $output['html_content6'] = view('sale_pos.receipts.ob_invoice', compact('receipt_details','tax_details'))->render();
            $output['html_content7'] = view('sale_pos.receipts.packing_slip_blank_dl', compact('blank_slip_number','receipt_details','tax_details','jadoo_products'))->render();
            $output['html_content8'] = view('sale_pos.receipts.blank_slip', compact('blank_slip_number','receipt_details','tax_details','jadoo_products'))->render();
            $output['html_content9'] = view('sale_pos.receipts.packing_slip_blank_without_price', compact('receipt_details','tax_details','jadoo_products'))->render();

            if($is_package_slip=='html_content_print')
            {
                $print = "print";
                $output['html_content'] = view('sale_pos.receipts.packing_slip', compact('receipt_details','tax_details','print'))->render();
            }
            // export PDF qrcode start
            if($is_package_slip=='html_content_pdf')
            {
                $img="";
                $qrcode = "";
                $url = $this->transactionUtil->getInvoiceUrl($transaction_id, $business_id);
                if($url!="")
                {
                    //$qrcode = QrCode::size(90)->generate($url);
                    $img = base64_encode(QrCode::format('png')->size(90)->generate($url));
                    $qrcode = 'data:image/png;base64,'.$img;
                }
                $url = $this->transactionUtil->getInvoiceUrl($transaction_id, $business_id);
                $output['html_content_pdf'] = view('sale_pos.receipts.export_pdf', compact('receipt_details','tax_details','jadoo_products','qrcode'))->render();

                $output['html_content_pdf2'] = view('sale_pos.receipts_new.open_invoice_pdf', compact('url','receipt_details','tax_details','jadoo_products','qrcode'))->render();
            }
            $output['html_content_pdf1'] = view('sale_pos.receipts.export_pdf_reg', compact('receipt_details','tax_details','jadoo_products'))->render();

            // export PDF qrcode end

            // delete export PDF start
            if($is_package_slip=='delete_html_content_pdf')
            {
                $img="";
                $qrcode = "";
                $url = $this->transactionUtil->getInvoiceUrl($transaction_id, $business_id);
                if($url!="")
                {
                    //$qrcode = QrCode::size(90)->generate($url);
                    $img = base64_encode(QrCode::format('png')->size(90)->generate($url));
                    $qrcode = 'data:image/png;base64,'.$img;
                }
                $output['html_content_pdf'] = view('sale_pos.receipts.delete_export_pdf', compact('receipt_details','tax_details','qrcode'))->render();
            }
            // delete export PDF end

            return $output;
        }

        //If print type browser - return the content, printer - return printer config data, and invoice format config
        if ($receipt_printer_type == 'printer') {
            $output['print_type'] = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data'] = $receipt_details;
        } else {
            $layout = !empty($receipt_details->design) ? 'sale_pos.receipts.' . $receipt_details->design : 'sale_pos.receipts.classic';

            $output['html_content'] = view($layout, compact('receipt_details','tax_details','jadoo_products'))->render();
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        // if (!(auth()->user()->can('superadmin') || auth()->user()->can('sell.update') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('repair.update')))) {
        //     abort(403, 'Unauthorized action.');
        // }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                    'msg' => __('lang_v1.return_exist')]);
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->with(['price_group', 'types_of_service'])
                            ->findorfail($id);

        $open_blance = $this->contactUtil->getContactInfo($business_id, $transaction->contact_id);


        $location_id = $transaction->location_id;
        $business_location = BusinessLocation::find($location_id);
        $payment_types = $this->productUtil->payment_types($business_location, true);
        $location_printer_type = $business_location->receipt_printer_type;
        $sell_details = TransactionSellLine::
                        join(
                            'products AS p',
                            'transaction_sell_lines.product_id',
                            '=',
                            'p.id'
                        )
                        // ->leftjoin('tax_rates', 'p.sub_category_id', '=', 'tax_rates.sub_category')
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
                        ->join(
                            'transactions AS t',
                            't.id',
                            '=',
                            'transaction_sell_lines.transaction_id'
                        )
                        ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                            $join->on('variations.id', '=', 'vld.variation_id')
                                ->where('vld.location_id', '=', $location_id);
                        })
                        ->leftjoin('units', 'units.id', '=', 'p.unit_id')
                        ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                        ->leftjoin('categories as sub_cat', 'p.sub_category_id', '=', 'sub_cat.id')
                        ->where('transaction_sell_lines.transaction_id', $id)
                        ->with(['warranties'])
                        ->select(
                            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                            'p.id as product_id',
                            'p.enable_stock',
                            'p.qty_box',
                            'p.ml',
                            'p.main_image as product_image',
                            'p.name as product_actual_name',
                            'p.sales_price as web_sale_price',
                            'p.srp',
                            'p.type as product_type',
                            'pv.name as product_variation_name',
                            'pv.is_dummy as is_dummy',
                            'variations.name as variation_name',
                            'variations.sub_sku',
                            'variations.default_purchase_price',
                            'variations.dpp_inc_tax',
                            't.contact_id',
                            'p.barcode_type',
                            'p.category_id',
                            'p.item_code as icode',
                            // 'tax_rates.taxvalue',
                            // 'tax_rates.tax',
                            // 'tax_rates.tax_percent',
                            // 'tax_rates.city_tax_value',
                            'p.enable_sr_no',
                            'variations.id as variation_id',
                            'variations.default_sell_price as original_sell_price',
                            'units.short_name as unit',
                            'units.allow_decimal as unit_allow_decimal',
                            'transaction_sell_lines.tax_id as tax_id',
                            'transaction_sell_lines.item_tax as item_tax',
                            'transaction_sell_lines.unit_price as default_sell_price',
                            'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                            'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                            'transaction_sell_lines.id as transaction_sell_lines_id',
                            'transaction_sell_lines.id',
                            'transaction_sell_lines.purchase_price',
                            'transaction_sell_lines.quantity as quantity_ordered',
                            'transaction_sell_lines.sell_line_note as sell_line_note',
                            'transaction_sell_lines.box_no as box_no',
                            'transaction_sell_lines.parent_sell_line_id',
                            'transaction_sell_lines.lot_no_line_id',
                            'transaction_sell_lines.line_discount_type',
                            'transaction_sell_lines.line_discount_amount',
                            'transaction_sell_lines.res_service_staff_id',
                            'units.id as unit_id',
                            'transaction_sell_lines.sub_unit_id',
                            'transaction_sell_lines.pos_line_tax_id',
                            'transaction_sell_lines.city_tax_id',
                            'transaction_sell_lines.pos_line_tax_amount',
                            'categories.name as catName',
                            'sub_cat.name as subCatName',
                            DB::raw('vld.qty_available AS display_qty_available'),
                            DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
                        )
                        ->get();
        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                $product_deatails = ProductVariation::where('product_id', $sell_details[$key]->product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();
                $sell_details[$key]['cost'] = $product_deatails->variations[0]->dpp_inc_tax;
                //If modifier or combo sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'modifier')
                            ->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }

                    //Get details of combo items
                    if ($sell_details[$key]->product_type == 'combo') {
                        $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'combo')
                            ->get()
                            ->toArray();
                        if (!empty($sell_line_combos)) {
                            $sell_details[$key]->combo_products = $sell_line_combos;
                        }

                        //calculate quantity available if combo product
                        $combo_variations = [];
                        foreach ($sell_line_combos as $combo_line) {
                            $combo_variations[] = [
                                'variation_id' => $combo_line['variation_id'],
                                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                                'unit_id' => null
                            ];
                        }
                        $sell_details[$key]->qty_available =
                        $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

                        if ($transaction->status == 'final') {
                            $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
                        }

                        $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                    }
                }

                $sell_details[$key]->stock_history = [];//$this->productUtil->getVariationStockHistory($business_id, $value->variation_id, $location_id , null);

                //calculate tax details
                $sell_details[$key]['tax'] = $this->productUtil->calculateRuleTax($value->pos_line_tax_id,$value->city_tax_id, $value->dpp_inc_tax, $value->default_sell_price,$value->contact_id, $sell_details[$key]->product_id);
            }
        }

        $featured_products = $business_location->getFeaturedProducts();

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

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

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }
        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $warranties = $this->__getwarranties();
        $sub_type = request()->get('sub_type');

        //pos screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type]);

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        $default_datetime = "";
        if ($transaction->status == 'draft') {
            $default_datetime = $this->businessUtil->format_date('now', true);
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        if ($transaction->status == 'payment-verified') {
            $default_datetime = $this->businessUtil->format_date('now', true);
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $invoice_layouts = InvoiceLayout::forDropdown($business_id);
        $search_categories = Category::forDropdown($business_id, 'product')->toArray();
         $tax  = [];

        $packed = null;
        foreach ($sell_details as $key => $value) {
           $packed =  TransactionSellLine::where('id', $value->id)->whereNotNull('packing_completed_time')->first();
           break;
        }

        $autosave = 0;
        if(!empty(request()->input('autosave')) && request()->input('autosave') == 1){
            $autosave = 1;
        }

        return view('sale_pos.edit')
            ->with(compact('autosave','business_details', 'taxes', 'open_blance' , 'payment_types', 'walk_in_customer', 'sell_details', 'transaction', 'payment_lines', 'location_printer_type', 'shortcuts', 'commission_agent', 'categories', 'pos_settings', 'change_return', 'types', 'customer_groups', 'brands', 'accounts', 'waiters', 'redeem_details', 'edit_price', 'edit_discount', 'shipping_statuses', 'warranties', 'sub_type', 'pos_module_data', 'invoice_schemes', 'default_invoice_schemes', 'invoice_layouts', 'featured_products','tax','search_categories', 'packed','default_datetime'));
    }

    /**
     * Update the specified resource in storage.
     * TODO: Add edit log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('sell.update') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        $is_direct_sale = false;
        try {
            $input = $request->except('_token');
            if(!isset($request->status)){
                $input['status'] = 'final';
            }
            else{
                $input['status'] = $request->status;
            }
            $input['final_total'] = $request->final_total;
            $input['discount_type'] = $request->discount_type;
            $input['discount_amount'] = $request->discount_amount;
            if (isset($request->tax_deal)) {
                $input['tax_deal'] = $request->tax_deal;
            }
            // return $input['discount_amount'];
            $input['tax_rate_id'] = $request->tax_rate_id;
            $input['change_return'] = $request->change_return;
            if(isset($request->notpickpack)) $input['notpickpack'] = $request->notpickpack;
            //status is send as quotation from edit sales screen.
            $input['is_quotation'] = 0;
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
            }

            if ($input['status'] == 'payment-verified') {
                $input['status'] = 'payment-verified';
            }

            if (!empty($request->input('priority_order'))) {
                    $input['priority_order'] = 1;
                }else{
                    $input['priority_order'] = 0;
                }
            if (!empty($input['products'])) {

                //check for defauly sell price
                $CheckForDefaultSellPrice = $this->transactionUtil->CheckForDefaultSellPrice($input['products']);

                if($CheckForDefaultSellPrice != "")
                {
                    return $this->respond(
                    ['success' => 0,
                            'msg' => $CheckForDefaultSellPrice
                        ]);
                }
                //check for defauly sell price

                //Get transaction value before updating.
                $transaction_before = Transaction::find($id);
                $status_before =  $transaction_before->status;
                $rp_earned_before = $transaction_before->rp_earned;
                $rp_redeemed_before = $transaction_before->rp_redeemed;
                // if($request->discount_type != ''){
                //     $discount_amount = $request->discount_amount;
                //     if($request->discount_type == 'percentage'){
                //         $discount_amount = ($request->amount * $request->discount_amount)/100;
                //     }
                //     // $input['discount_amount'] = $transaction_before->discount_amount + $discount_amount;
                //     // return $discount_amount;
                //     $input['discount_amount'] =  $discount_amount;
                //     $input['discount_type'] = 'fixed';
                // }

                if ($transaction_before->is_direct_sale == 1) {
                    $is_direct_sale = true;
                }

                //Check Customer credit limit
                $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input, $id);

                if ($is_credit_limit_exeeded !== false) {
                    $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                    $output = ['success' => 0,
                                'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                            ];
                    if (!$is_direct_sale) {
                        return $output;
                    } else {
                        return redirect()
                            ->action('SellController@index')
                            ->with('status', $output);
                    }
                }

                //Check if there is a open register, if no then redirect to Create Register screen.
                if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                    return redirect()->action('CashRegisterController@create');
                }

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                $discount = ['discount_type' => $input['discount_type'],
                                'discount_amount' => $input['discount_amount']
                            ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                if (!empty($request->input('transaction_date'))) {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }

                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                $price_group_id = $request->has('price_group') ? $request->input('price_group') : null;

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend']  ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                if ($status_before == 'draft' && !empty($request->input('invoice_scheme_id'))) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                if ($status_before == 'payment-verified' && !empty($request->input('invoice_scheme_id'))) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                //Types of service
                if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
                    $input['types_of_service_id'] = $request->input('types_of_service_id');
                    $price_group_id = !empty($request->input('types_of_service_price_group')) ? $request->input('types_of_service_price_group') : $price_group_id;
                    $input['packing_charge'] = !empty($request->input('packing_charge')) ?
                    $this->transactionUtil->num_uf($request->input('packing_charge')) : 0;
                    $input['packing_charge_type'] = $request->input('packing_charge_type');
                    $input['service_custom_field_1'] = !empty($request->input('service_custom_field_1')) ?
                    $request->input('service_custom_field_1') : null;
                    $input['service_custom_field_2'] = !empty($request->input('service_custom_field_2')) ?
                    $request->input('service_custom_field_2') : null;
                    $input['service_custom_field_3'] = !empty($request->input('service_custom_field_3')) ?
                    $request->input('service_custom_field_3') : null;
                    $input['service_custom_field_4'] = !empty($request->input('service_custom_field_4')) ?
                    $request->input('service_custom_field_4') : null;
                }

                $input['selling_price_group_id'] = $price_group_id;

                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $input['res_table_id'] = request()->get('res_table_id');
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $input['res_waiter_id'] = request()->get('res_waiter_id');
                }

                $previous_products_record = TransactionSellLine::where('transaction_id', $id)->get()->toArray();
                $previous_transaction_record = Transaction::where('id', $id)->get()->toArray();

                //Begin transaction
                DB::beginTransaction();
                //added by developer1 POS unit price log
                $price_group_details = VariationGroupPrice::where('price_group_id', $price_group_id)->get();
                //added by developer1 POS unit price log

                if(!empty(request()->input('autosave')) && request()->input('autosave') == 1){
                    // skip logs on autosave
                }
                else{
                    //added by developer1
                    $this->transactionUtil->UpdatePOSLog($input, $business_id, $user_id,'',$id,$price_group_details);
                }

                $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);

                // RequestLogHelper::LogTransaction($request, $transaction, RequestLogHelper::LOG_TYPE_TRANSACTION_UPDATE);

                //Update Sell lines
                $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before, [], true, $price_group_id);
                // return 0;
                //Update update lines
                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;

                if (!$is_direct_sale && !$transaction->is_suspend && !$is_credit_sale) {
                    //Add change return
                    $input['change_return'] =  (float)str_replace(',', '', $input['change_return']);
                    $change_return = $this->dummyPaymentLine;
                    $change_return['amount'] = $input['change_return'];
                    $change_return['is_advance'] = 1;
                    $change_return['is_return'] = 1;
                    $change_return['note'] = "$".$input['change_return']."  created as advance payment for order ".$transaction->invoice_no."  due to low order amount than applied payment";
                    if (!empty($input['change_return_id'])) {
                        $change_return['id'] = $input['change_return_id'];
                    }
                    $input['payment'][] = $change_return;

                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                    $this->transactionUtil->updateContactBalance($contact_id, $input['change_return'], $type = 'add');
                    //Update cash register
                    $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
                }

                if ($request->session()->get('business.enable_rp') == 1) {
                    $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, $rp_earned_before, $transaction->rp_redeemed, $rp_redeemed_before);
                }

                //Update payment status
                $this->transactionUtil->updatePaymentStatus($transaction->id,null, 0, 0, $transaction->final_total);

                //Update product stock
                $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);

                //Allocate the quantity from purchase and add mapping of
                //purchase & sell lines in
                //transaction_sell_lines_purchase_lines table
                $business_details = $this->businessUtil->getDetails($business_id);
                $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                $business = ['id' => $business_id,
                                'accounting_method' => $request->session()->get('business.accounting_method'),
                                'location_id' => $input['location_id'],
                                'pos_settings' => $pos_settings
                            ];
                $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);

                $log_properties = [];
                if (isset($input['repair_completed_on'])) {
                    $completed_on = !empty($input['repair_completed_on']) ? $this->transactionUtil->uf_date($input['repair_completed_on'], true) : null;
                    if ($transaction->repair_completed_on != $completed_on) {
                        $log_properties['completed_on_from'] = $transaction->repair_completed_on;
                        $log_properties['completed_on_to'] = $completed_on;
                    }
                }

                //Set Module fields
                if (!empty($input['has_module_data'])) {
                    $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
                }

                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                activity()
                ->performedOn($transaction)
                ->log('edited');

                DB::commit();

                //New Organized Log
                $transactionId = $transaction->id;
                $ntl_id = NeatTxnLog::logActivity($transactionId, 'Edited');
                NeatTxnInfoLogs::logActivity($ntl_id->id, $input, $transactionId, 'Edited', $previous_transaction_record);
                NeatTxnProLog::logActivity($ntl_id->id, $transactionId, $input['products'], $previous_products_record);

                if ($request->input('is_save_and_print') == 1) {
                    $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);
                    return redirect()->to($url . '?print_on_load=true');
                }

                $msg = '';
                $receipt = '';

                $invoice_layout_id = $request->input('invoice_layout_id');

                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans("sale.draft_added");
                } elseif ($input['status'] == 'payment-verified') {
                    $msg = trans("Payment Verified Successfully");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, 'html_content', true, $invoice_layout_id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans("lang_v1.quotation_updated");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, 'html_content', true, $invoice_layout_id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'final') {
                    $msg = trans("sale.pos_sale_updated");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, 'html_content', true, $invoice_layout_id);
                    } else {
                        $receipt = '';
                    }
                }

                if(isset($input['finalize_no_print']) && $input['finalize_no_print']){
                    $receipt = '';
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt, 'autosave_id' => $transaction->id];
                self::theInvoiceRepairer();
            } else {
                $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
            }
        } catch (\Exception $e) {
            return $e;
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        if (!$is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            } elseif ($input['status'] == 'payment-verified') {
                return redirect()
                        ->action('SellController@getPaymentVerified')
                        ->with('status', $output);
            } else {
                if (!empty($transaction->sub_type) && $transaction->sub_type == 'repair') {
                    return redirect()
                        ->action('\Modules\Repair\Http\Controllers\RepairController@index')
                        ->with('status', $output);
                }

                return redirect()
                    ->action('SellController@index')
                    ->with('status', $output);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $reason = !empty(request()->get('reason'))?request()->get('reason'):"";
                $business_id = request()->session()->get('user.business_id');
                //Begin transaction
                DB::beginTransaction();

                $this->del_invoice_pdf_log($id,$reason);
                $output = $this->transactionUtil->deleteSale($business_id, $id);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans("messages.something_went_wrong");
            }

            return $output;
        }
    }

    /**
     * Returns the HTML row for a product in POS
     *
     * @param  int  $variation_id
     * @param  int  $location_id
     * @return \Illuminate\Http\Response
     */
    public function getProductRow(Request $request, $variation_id, $location_id)
    {
        $output = [];
        // dd($variation_id);
        try {
            $row_count = request()->get('product_row');
            $row_count = $row_count + 1;
            $is_direct_sell = false;
            if (request()->get('is_direct_sell') == 'true') {
                $is_direct_sell = true;
            }
            $business_id = request()->session()->get('user.business_id');
            $business_details = $this->businessUtil->getDetails($business_id);
            $quantity = request()->get('quantity', 1);
            //Check for weighing scale barcode
            $weighing_barcode = request()->get('weighing_scale_barcode');
            if ($variation_id == 'null' && !empty($weighing_barcode)) {
                $product_details = $this->__parseWeighingBarcode($weighing_barcode);
                if ($product_details['success']) {
                    $variation_id = $product_details['variation_id'];
                    $quantity = $product_details['qty'];
                } else {
                    $output['success'] = false;
                    $output['msg'] = $product_details['msg'];
                    return $output;
                }
            }
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
            $check_qty = !empty($pos_settings['allow_overselling']) ? false : true;
            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, $check_qty);
            $tax=[];
            // $tax = $this->productUtil->getProductTax($product->product_id, $variation_id, $request->customer_id, $product->dpp_inc_tax, $product->sell_price_inc_tax);
            if (!isset($product->quantity_ordered)) {
                $product->quantity_ordered = $quantity;
            }
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available, false, null, true);
            $product->display_qty_available = $product->qty_available;
            $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product->product_id);
            //Get customer group and change the price accordingly
            $customer_id = request()->get('customer_id', null);

            $last_sell_price = 0;
            if(isset($customer_id) && isset($variation_id)){

                $comparable_date = $product->last_variation_update;
                $customer_record = Contact::find($customer_id);
                if(!empty($customer_record->customer_group_id)){
                    if($customer_record->customer_group_id == 68){
                        $comparable_date = $product->t1_updated_at;
                    }
                    elseif($customer_record->customer_group_id == 69){
                        $comparable_date = $product->t2_updated_at;
                    }
                    elseif($customer_record->customer_group_id == 70){
                        $comparable_date = $product->t3_updated_at;
                    }
                }

                $last_sell = DB::table('transaction_sell_lines as tsl')
                    ->join('transactions as t','t.id','=','tsl.transaction_id')
                    ->where('t.type','sell')
                    ->where('t.status','final')
                    ->where('t.contact_id',$customer_id)
                    ->where('tsl.variation_id',$variation_id)
                    ->when(!empty($comparable_date), function($query) use($comparable_date){
                        $query->where('t.transaction_date','>=',$comparable_date);
                    })
                    ->orderBy('tsl.id','desc')
                ->first();

                // ->where('tsl.updated_at','>',$product->last_variation_update)

                if(isset($last_sell) && !empty($last_sell->unit_price)){
                    $last_sell_price = $last_sell->unit_price;
                }

            }

            $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
            $percent = (empty($cg) || empty($cg->amount)) ? 0 : $cg->amount;
            $product->is_original_sell_price = 0;
            $product->original_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
            $product->default_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
            $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);
            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
            $enabled_modules = $this->transactionUtil->allModulesEnabled();
            $product_deatails = ProductVariation::where('product_id', $product->product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();
            $cost = $product_deatails->variations[0]->dpp_inc_tax;
            //Get lot number dropdown if enabled
            $lot_numbers = [];
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;
            $purchase_line_id = request()->get('purchase_line_id');
            $price_group = request()->input('price_group');

            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $variation_id, $location_id , null);


            if (!empty($price_group)) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);

                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->is_original_sell_price = 1;
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }
            $warranties = $this->__getwarranties();
            $output['success'] = true;
            $waiters = [];
            if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
                $waiters_enabled = true;
                $waiters = $this->productUtil->serviceStaffDropdown($business_id, $location_id);
            }
            if (request()->get('type') == 'sell-return') {
                $output['html_content'] =  view('sell_return.partials.product_row')
                            ->with(compact('product', 'stock_history', 'row_count', 'tax_dropdown', 'enabled_modules', 'sub_units'))
                            ->render();
            } else {
                $is_cg = !empty($cg->id) ? true : false;
                $is_pg = !empty($price_group) ? true : false;
                $discount = $this->productUtil->getProductDiscount($product, $business_id, $location_id, $is_cg, $is_pg, $variation_id);

                if ($is_direct_sell) {
                    $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
                    $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');
                } else {
                    $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
                    $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
                }
                // $output['html_content'] =  view('sale_pos.product_row')
                //             ->with(compact('product', 'business_details','row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'discount', 'waiters', 'edit_discount', 'edit_price', 'purchase_line_id', 'warranties', 'quantity', 'cost', 'tax'))
                //             ->render();
               $variation =  Variation::join('products','variations.product_id','=','products.id')->where('variations.id',$variation_id)->first();
               $category_id = $variation->category_id;

               $cat = Category::where('id',$category_id)->first();
                if($cat && ($cat->name == 'CIGAR' || $cat->parent_id == $category_id))
                {
                    $customer = Contact::where('id',$customer_id)->first();
                    if(empty($customer->tobacco_license_no) || empty($customer->tax_number)){
                        $data = [
                            'success' => false,
                            'message'=> 'You cannot sell cigars to this customer, tax id or tobaco license is missing'
                        ];
                        return $data;
                    }else{
                        $html_content =  view('sale_pos.product_row')
                        ->with(compact('product', 'stock_history', 'last_sell_price','business_details','row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'discount', 'waiters', 'edit_discount', 'edit_price', 'purchase_line_id', 'warranties', 'quantity', 'cost', 'tax'))
                        ->render();
                        $data = [
                            'success' => true,
                            'message'=> '',
                            'html_content' => $html_content,
                        ];

                        return $data;
                    }
                } else{
                    $output['html_content'] =  view('sale_pos.product_row')
                                    ->with(compact('product', 'stock_history', 'last_sell_price', 'business_details','row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'discount', 'waiters', 'edit_discount', 'edit_price', 'purchase_line_id', 'warranties', 'quantity', 'cost', 'tax'))
                                    ->render();
                }


            }



            $output['enable_sr_no'] = $product->enable_sr_no;
            if ($this->transactionUtil->isModuleEnabled('modifiers')  && !$is_direct_sell) {
                $this_product = Product::where('business_id', $business_id)
                                        ->find($product->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] =  view('restaurant.product_modifier_set.modifier_for_product')
                    ->with(compact('product_ms', 'row_count'))->render();
                }
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            return $e;
            $output['success'] = false;
            $output['msg'] = __('lang_v1.item_out_of_stock');
        }
        if($customer_id){
             $customer = Contact::find($customer_id);
            if(($customer && ($customer->state=='Massachusetts' || $customer->state=='New Hampshire' || $customer->state=='Connecticut')) && $product->catName == 'CIGAR' && $product->subCatName == 'Mini')
            return 0;
        }
        return $output;
    }

    /**
     * Returns the HTML row for a payment in POS
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $row_index = $request->input('row_index');
        $location_id = $request->input('location_id');
        $removable = true;
        $payment_types = $this->productUtil->payment_types($location_id, true);

        $payment_line = $this->dummyPaymentLine;

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('sale_pos.partials.payment_row')
            ->with(compact('payment_types', 'row_index', 'removable', 'payment_line', 'accounts'));
    }

    /**
     * Returns recent transactions
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRecentTransactions(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $transaction_status = $request->get('status');
        $customer_id = $request->get('customer_id');

        $register = $this->cashRegisterUtil->getCurrentCashRegister($user_id);

        $query = Transaction::where('business_id', $business_id)
                        ->where('transactions.created_by', $user_id)
                        ->where('transactions.type', 'sell')
                        ->where('is_direct_sale', 0);

        if ($transaction_status == 'final') {
            //Commented as credit sales not showing
            // if (!empty($register->id)) {
            //     $query->leftjoin('cash_register_transactions as crt', 'transactions.id', '=', 'crt.transaction_id')
            //     ->where('crt.cash_register_id', $register->id);
            // }
        }

        if (isset($customer_id) && $customer_id) {
            $query->where('transactions.contact_id', $customer_id);
        }

        if ($transaction_status == 'quotation') {
            $query->where('transactions.status', 'draft')
                ->where('is_quotation', 1);
        } elseif ($transaction_status == 'draft') {
            $query->where('transactions.status', 'draft')
                ->where('is_quotation', 0);
        } elseif ($transaction_status == 'payment-verified') {
            $query->where('transactions.status', 'payment-verified');
        } else {
            $query->where('transactions.status', $transaction_status);
        }

        $transaction_sub_type = $request->get('transaction_sub_type');
        if (!empty($transaction_sub_type)) {
            $query->where('transactions.sub_type', $transaction_sub_type);
        } else {
            $query->where('transactions.sub_type', null);
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')
                            ->groupBy('transactions.id')
                            ->select('transactions.*')
                            ->with(['contact', 'table'])
                            ->limit(10)
                            ->get();

        return view('sale_pos.partials.recent_transactions')
            ->with(compact('transactions', 'transaction_sub_type', 'customer_id'));
    }

    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = ['success' => 0,
                        'msg' => trans("messages.something_went_wrong")
                        ];

                $business_id = $request->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                                ->where('id', $transaction_id)
                                ->with(['location'])
                                ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $printer_type = 'browser';
                if (!empty(request()->input('check_location')) && request()->input('check_location') == true) {
                    $printer_type = $transaction->location->receipt_printer_type;
                }

                $is_package_slip = false;
                $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;
                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, $printer_type, $is_package_slip, false, $invoice_layout_id);

                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => 0,
                        'msg' => trans("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage())
                        ];
            }

            return $output;
        }
    }

    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function printQuotation(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = ['success' => 0,
                        'msg' => trans("messages.something_went_wrong")
                        ];

                $business_id = $request->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                                ->where('id', $transaction_id)
                                ->with(['location'])
                                ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $printer_type = 'browser';
                if (!empty(request()->input('check_location')) && request()->input('check_location') == true) {
                    $printer_type = $transaction->location->receipt_printer_type;
                }

                // $is_package_slip = !empty($request->input('package_slip')) ? true : false;
                $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;
                $receipt =  $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);

                // $this->receiptContent($business_id, $transaction->location_id, $transaction_id, $printer_type, 'html_content2', false, $invoice_layout_id);

//$this->receiptContent($business_id, $transaction->location_id, $transaction_id, $printer_type, false, false, $invoice_layout_id);

                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => 0,
                        'msg' => trans("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage())
                        ];
            }

            return $output;
        }
    }

    /**
     * Gives suggetion for product based on category
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProductSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $category_id = $request->get('category_id');
            $brand_id = $request->get('brand_id');
            $location_id = $request->get('location_id');
            $term = $request->get('term');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');

            $products = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
                ->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                ->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'VLD.variation_id');

                        //Include Location
                        if (!empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                //Check null to show products even if no quantity is available in a location.
                                //TODO: Maybe add a settings to show product not available at a location or not.
                                $query->orWhereNull('VLD.location_id');
                            });
                            ;
                        }
                    }
                )
                        ->where('p.business_id', $business_id)
                        ->where('p.type', '!=', 'modifier')
                        ->where('p.is_inactive', 0)
                        ->where('p.not_for_selling', 0)
                        //Hide products not available in the selected location
                        ->where(function ($q) use ($location_id) {
                            $q->where('pl.location_id', $location_id);
                        });

            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('p.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                });
            }

            //Include check for quantity
            if ($check_qty) {
                $products->where('VLD.qty_available', '>', 0);
            }

            if (!empty($category_id) && ($category_id != 'all')) {
                $products->where(function ($query) use ($category_id) {
                    $query->where('p.category_id', $category_id);
                    $query->orWhere('p.sub_category_id', $category_id);
                });
            }
            if (!empty($brand_id) && ($brand_id != 'all')) {
                $products->where('p.brand_id', $brand_id);
            }

            if (!empty($request->get('is_enabled_stock'))) {
                $is_enabled_stock = 0;
                if ($request->get('is_enabled_stock') == 'product') {
                    $is_enabled_stock = 1;
                }

                $products->where('p.enable_stock', $is_enabled_stock);
            }

            if (!empty($request->get('repair_model_id'))) {
                $products->where('p.repair_model_id', $request->get('repair_model_id'));
            }

            $products = $products->select(
                'p.id as product_id',
                'p.name',
                'p.type',
                'p.enable_stock',
                'p.main_image as product_image',
                'variations.id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as selling_price',
                'variations.sub_sku'
            )
            ->with(['media'])
            ->orderBy('p.name', 'asc')
            ->paginate(50);
            return view('sale_pos.partials.product_list')
                    ->with(compact('products'));
        }
    }

    /**
     * Shows invoice url.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function search_Invoice(Request $request)
    {

     if($request->ajax())
     {
        $output = '';
        $query = $request->get('query');
        $ranges = $request->get('ranges');
        $rates = $request->get('rates');
        $state = $request->get('state');
        $product_category = $request->get('product_category');


      if($ranges!='' || $query!='' || $rates!='' || $state!='' || $product_category !='')
      {
        $data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name','contacts.contact_id as accountid','contacts.state as state')
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final');


        // return $data->get();

        if(isset($query)){
             $data->where('invoice_no', 'like', '%'.$query.'%');
        }
        if (!empty($rates)) {
            $data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
            // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
            $tax_rates_id = $request->get('rates');

            $data->where(function ($query) use($tax_rates_id) {
                $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                      ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
            });
        }
        $data->groupBy('transactions.id');

        if(!empty($state)){
            $data->where('contacts.state', 'like', '%'.$state.'%');
        }

        if(isset($ranges)){

            $myArray = explode('-', $ranges);
            // return $myArray;
            $fr1 = $myArray[0];
            $to2 = $myArray[1];
            $from = str_replace(' ', '', $fr1);
            $to = str_replace(' ', '', $to2);
            $new_fromdate = date('Y-m-d',strtotime($from));
            $new_todate = date('Y-m-d',strtotime('+23 hour +59 minutes +59 seconds',strtotime($to)));


            $data->whereDate('transactions.transaction_date','>=',$new_fromdate)->whereDate('transactions.transaction_date','<=',$new_todate);
        }
            $data = $data->orderBy('transactions.transaction_date','desc')->get();

            if(empty($product_category)){
               $filtered_sells = $data;
            }else{
                $filtered_sells = [];
                foreach($data as $row){

                    $transaction_id = $row->id;
                     $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                    foreach($product_category as $category){
                        foreach($transaction_sells as $sell){
                            if($sell->product_single->category_id == $category){
                                array_push($filtered_sells,$row);
                                break;
                            }
                        }
                    }
                }

            }
      }

       $total_row = count($filtered_sells);
      if($total_row > 0)
      {
        foreach($filtered_sells as $row)
        {
            $output .= '
            <h5><a href="open_invoice/'.$row->id.'" target="_blank">'.$row->invoice_no.'</a> <b>['.date('m/d/Y',strtotime($row->transaction_date)).']</b> - $'.round($row->final_total,2).' - '.$row->first_name.' ('.$row->accountid.')</h5>';
        }
      }
      else
      {
        $output = '
            <td align="center" colspan="5">No Data Found<td>';
      }
      $data = array(
       'table_data'  => $output,
       'total_data'  => $total_row
      );
      echo json_encode($data);
     }
    }




    // export invoice tab start
    public function search_export_Invoice(Request $request)
    {

     if($request->ajax())
     {
        $ranges = $request->post('ranges');
        $contact_id = $request->post('contact_id');
        $query = $request->get('query');
        $rates = $request->get('rates');
        $state = $request->get('state');
        $jadoo_invoice = $request->get('jadoo_invoice');
        // dd($jadoo_invoice);die;

        $product_category = $request->get('product_category');
        $order_note = $request->post('order_note');
        $regular_invoice1 = $request->get('regular_invoice');

        $regular_invoice = $request->post('regular_invoice');

        $regular_invoice_pdf = $request->get('regular_invoice_pdf');

        $start = $request->post('start');
        $limit = $request->post('limit');
        $set_upload_dir_path = $request->get('upload_dir_path');

        if($ranges!='' && $start!='' && $limit!='')
        {
            $output = '';

        $data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name','contacts.contact_id as accountid','contacts.state as state')
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final');
        // return $data->get();

            if(isset($ranges))
            {
                $myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $from = str_replace(' ', '', $fr1);
                $to = str_replace(' ', '', $to2);
                $new_fromdate = date('Y-m-d',strtotime($from));
                $new_todate = date('Y-m-d',strtotime('+23 hour +59 minutes +59 seconds',strtotime($to)));


                $data->whereDate('transactions.transaction_date','>=',$new_fromdate)->whereDate('transactions.transaction_date','<=',$new_todate);

                /*$myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $month = str_replace(' ', '', $fr1);
                $year = str_replace(' ', '', $to2);

                $data->whereMonth('transactions.transaction_date','=',$month)->whereYear('transactions.transaction_date','=',$year);*/
            }

            if(isset($contact_id)){
             $data->where('contacts.id','=', $contact_id);
            }

            if(isset($query)){
             $data->where('invoice_no', 'like', '%'.$query.'%');
            }
            if (!empty($rates)) {
                $data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
                // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
                $tax_rates_id = $request->get('rates');

                $data->where(function ($query) use($tax_rates_id) {
                    $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                          ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
                });
            }
            if(!empty($state)){
                $data->where('contacts.state', 'like', '%'.$state.'%');
            }

            $data = $data->groupBy('transactions.id')
            ->orderBy('transactions.transaction_date','desc')->get();
            //echo "sdf"; exit;

            $jadoo_array = [];
            $jadoo_array1 = [];
            $new_array = [];
            $regular_invoice = [];
            $filter_array = [];

            if($jadoo_invoice == 1){
                foreach($data as $row){

                    $transaction_id = $row->id;
                    $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                    foreach($transaction_sells as $sell){

                        $jadoo_products = $this->productUtil->GetJadooProductlist();

                        if(isset($jadoo_products['product']) && !empty($jadoo_products['product'])){
                            foreach($jadoo_products['product'] as $match => $value){
                                    // echo '<pre>';
                                    // print_r($match);
                                if(strpos(strtolower($sell->product_single->name),strtolower($match))!== false){
                                        if(!in_array( $row,$jadoo_array, true)){
                                            array_push($jadoo_array,$row);
                                            break;
                                        }
                                }

                            }
                            // die;
                            $data = $jadoo_array;

                        }
                    }
                }

            }
            elseif($regular_invoice1 == 1){

                    foreach($data as $row){

                        $transaction_id = $row->id;
                        $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                        $jadoo_products = $this->productUtil->GetJadooProductlist();

                        foreach($transaction_sells as $sell){


                            if(isset($jadoo_products['product']) && !empty($jadoo_products['product'])){
                                foreach($jadoo_products['product'] as $match => $value){
                                        // echo '<pre>';
                                        // print_r($match);
                                    if(strpos(strtolower($sell->product_single->name),strtolower($match))!== false){
                                            if(!in_array( $row,$jadoo_array, true)){
                                                array_push($jadoo_array,$row);
                                                break;
                                            }
                                    }

                                }
                                // die;
                                $data1 = $jadoo_array;

                            }
                        }

                        foreach($transaction_sells as $sell){

                            if(isset($sell->product_single->name))
                            {
                                $mathch_cat_id = $sell->product_single->name;
                                if(!in_array( $row,$data1, true)){
                                    array_push($jadoo_array1,$row);
                                    break;
                                }

                            }
                        }

                    }
                    $data = $jadoo_array1;

            }

            // print_r($jadoo_array);



                if(empty($product_category))
                {
                   $filtered_sells_for_count = $data;
                }
                else
                {
                    $filtered_sells_for_count = [];
                    foreach($data as $row){

                        $transaction_id = $row->id;
                        $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                        if(count($transaction_sells)>0)
                        {
                            foreach($product_category as $category){
                                foreach($transaction_sells as $sell){
                                    if(isset($sell->product_single->category_id))
                                    {
                                        $mathch_cat_id = $sell->product_single->category_id;
                                        if($mathch_cat_id == $category){
                                            array_push($filtered_sells_for_count,$row);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            //$total_row = count($total_result);
            //echo $total_row; exit;
            $total_row = count($filtered_sells_for_count);
            $result = 'next';
            if( ($start + $limit ) >= $total_row)
            {
                    $result = 'finished';
            }

        }

      if($total_row > 0)
      {

        if(isset($set_upload_dir_path) && $set_upload_dir_path!="")
            {
                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;
            }
            else
            {
                //$set_upload_dir_path = 'invoices_'.$ranges;
                $set_upload_dir_path = time();

                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;

                if(is_dir($upload_pdf_path))
                {
                    //rmdir($upload_pdf_path);
                    //Storage::deleteDirectory($upload_pdf_path);
                    if(File::deleteDirectory($upload_pdf_path))
                    {
                        mkdir($upload_pdf_path, 0777, true);
                    }
                }
                else
                {
                    mkdir($upload_pdf_path, 0777, true);
                }
                /*if (!is_dir($upload_pdf_path))
                {
                        if(mkdir($upload_pdf_path, 0777, true))
                        {
                            //$output .= '<button class="btn btn-sm btn-primary"><a href="download_zip/'.$set_upload_dir_path.'" target="_blank" style="color:white; ">Download Invoices</a></button> <br>';
                        }
                }*/
            }

             if(!empty($product_category))
            {
                $filtered_sells = (array)$filtered_sells_for_count;
            }
            else
            {
                if(!empty($jadoo_array)){
                    $filtered_sells = (array)$filtered_sells_for_count;

                }elseif(!empty($regular_invoice1)){
                    $filtered_sells = (array)$filtered_sells_for_count;

                }else{
                    $filtered_sells = $filtered_sells_for_count->toArray();

                    // $filtered_sells = (array)$filtered_sells_for_count;
                }
            }


            if( ($start + $limit ) >= $total_row)
            {
                $limit_show = $total_row-1;
            }
            else
            {
                $limit_show = $start+($limit-1);
            }

            for($i=$start;$i<=$limit_show;$i++)
            {
                    $dis_id = empty($filtered_sells[$i]['id']) ? '' : $filtered_sells[$i]['id'];
                    $dis_invoice_no = empty($filtered_sells[$i]['invoice_no']) ? '' : $filtered_sells[$i]['invoice_no'];
                    $dis_transaction_date = empty($filtered_sells[$i]['transaction_date']) ? '' : $filtered_sells[$i]['transaction_date'];
                    $dis_final_total = empty($filtered_sells[$i]['final_total']) ? '' : $filtered_sells[$i]['final_total'];
                    $dis_first_name = empty($filtered_sells[$i]['first_name']) ? '' : $filtered_sells[$i]['first_name'];
                    $dis_accountid = empty($filtered_sells[$i]['accountid']) ? '' : $filtered_sells[$i]['accountid'];

                    $output .= '
                    <h5><a href="open_invoice/'.$dis_id.'" target="_blank">'.$dis_invoice_no.'</a> <b>['.date('m/d/Y',strtotime($dis_transaction_date)).']</b> - $'.round($dis_final_total,2).' - '.$dis_first_name.' ('.$dis_accountid.')</h5>';
                    if($regular_invoice_pdf == 1){
                        $this->export_regular_invoice_pdf($dis_id,$upload_pdf_path,$order_note,$regular_invoice);
                    }else{
                        $this->export_invoice_pdf($dis_id,$upload_pdf_path,$order_note,$regular_invoice);
                    }
            }
      }
      else
      {
        $output = '
            <td align="center" colspan="5">No Data Found<td>';
      }

      $data = array(
       'start' => $start,
       'limit' => $limit,
       'upload_dir_path' => $set_upload_dir_path,
       'result' => $result,
       'table_data'  => $output,
       'total_data'  => $total_row
      );
      echo json_encode($data);
     }
    }

    // export invoice tab start
    public function search_export_InvoiceOld(Request $request)
    {

     if($request->ajax())
     {
        $ranges = $request->post('ranges');
        $contact_id = $request->post('contact_id');
        $query = $request->get('query');
        $rates = $request->get('rates');
        $state = $request->get('state');
        $product_category = $request->get('product_category');

        $start = $request->post('start');
        $limit = $request->post('limit');
        $set_upload_dir_path = $request->get('upload_dir_path');

        if($ranges!='' && $start!='' && $limit!='')
        {
            $output = '';

        $data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name','contacts.contact_id as accountid','contacts.state as state')
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final');
        // return $data->get();

            if(isset($ranges))
            {
                $myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $from = str_replace(' ', '', $fr1);
                $to = str_replace(' ', '', $to2);
                $new_fromdate = date('Y-m-d',strtotime($from));
                $new_todate = date('Y-m-d',strtotime('+23 hour +59 minutes +59 seconds',strtotime($to)));


                $data->whereDate('transactions.transaction_date','>=',$new_fromdate)->whereDate('transactions.transaction_date','<=',$new_todate);

                /*$myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $month = str_replace(' ', '', $fr1);
                $year = str_replace(' ', '', $to2);

                $data->whereMonth('transactions.transaction_date','=',$month)->whereYear('transactions.transaction_date','=',$year);*/
            }

            if(!empty($contact_id)){
                // dd($contact_id);
             $data->where('contacts.id','=', $contact_id);
            }
            if(isset($query)){
             $data->where('invoice_no', 'like', '%'.$query.'%');
            }
            if (!empty($rates)) {
                $data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
                // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
                $tax_rates_id = $request->get('rates');

                $data->where(function ($query) use($tax_rates_id) {
                    $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                          ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
                });
            }
            if(!empty($state)){
                $data->where('contacts.state', 'like', '%'.$state.'%');
            }

            $data = $data->groupBy('transactions.id')
            ->orderBy('transactions.transaction_date','desc')->get();
            //echo "sdf"; exit;

                if(empty($product_category))
                {
                   $filtered_sells_for_count = $data;
                }
                else
                {
                    $filtered_sells_for_count = [];
                    foreach($data as $row){

                        $transaction_id = $row->id;
                        $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                        if(count($transaction_sells)>0)
                        {
                            foreach($product_category as $category){
                                foreach($transaction_sells as $sell){
                                    if(isset($sell->product_single->category_id))
                                    {
                                        $mathch_cat_id = $sell->product_single->category_id;
                                        if($mathch_cat_id == $category){
                                            array_push($filtered_sells_for_count,$row);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            //$total_row = count($total_result);
            //echo $total_row; exit;
            $total_row = count($filtered_sells_for_count);
            $result = 'next';
            if( ($start + $limit ) >= $total_row)
            {
                    $result = 'finished';
            }

        }

      if($total_row > 0)
      {

        if(isset($set_upload_dir_path) && $set_upload_dir_path!="")
            {
                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;
            }
            else
            {
                //$set_upload_dir_path = 'invoices_'.$ranges;
                $set_upload_dir_path = time();

                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;

                if(is_dir($upload_pdf_path))
                {
                    //rmdir($upload_pdf_path);
                    //Storage::deleteDirectory($upload_pdf_path);
                    if(File::deleteDirectory($upload_pdf_path))
                    {
                        mkdir($upload_pdf_path, 0777, true);
                    }
                }
                else
                {
                    mkdir($upload_pdf_path, 0777, true);
                }
                /*if (!is_dir($upload_pdf_path))
                {
                        if(mkdir($upload_pdf_path, 0777, true))
                        {
                            //$output .= '<button class="btn btn-sm btn-primary"><a href="download_zip/'.$set_upload_dir_path.'" target="_blank" style="color:white; ">Download Invoices</a></button> <br>';
                        }
                }*/
            }

        $print_data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name','contacts.contact_id as accountid','contacts.state as state')
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final')
        ->whereDate('transactions.transaction_date','>=',$new_fromdate)
        ->whereDate('transactions.transaction_date','<=',$new_todate);
        //->whereMonth('transactions.transaction_date','=',$month)
        //->whereYear('transactions.transaction_date','=',$year)
        if(isset($query)){
             $print_data->where('invoice_no', 'like', '%'.$query.'%');
        }
        if (!empty($rates)) {
            $print_data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
            // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
            $tax_rates_id = $request->get('rates');

            $print_data->where(function ($query) use($tax_rates_id) {
                $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                      ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
            });
        }
        if(!empty($state)){
            $print_data->where('contacts.state', 'like', '%'.$state.'%');
        }

        $print_data = $print_data->groupBy('transactions.id')
        ->orderBy('transactions.transaction_date','desc')->offset($start)->limit($limit)->get();
        //echo "sdf"; exit;

            if(empty($product_category))
            {
               $filtered_sells = $print_data;
            }
            else
            {
                $filtered_sells = [];
                foreach($print_data as $row){

                    $transaction_id = $row->id;
                    $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                    if(count($transaction_sells)>0)
                    {
                        foreach($product_category as $category){
                            foreach($transaction_sells as $sell){
                                if(isset($sell->product_single->category_id))
                                {
                                    $mathch_cat_id = $sell->product_single->category_id;
                                    if($mathch_cat_id == $category){
                                        array_push($filtered_sells,$row);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

            }
            foreach($filtered_sells as $row)
            {
                $output .= '
                <h5><a href="open_invoice/'.$row->id.'" target="_blank">'.$row->invoice_no.'</a> <b>['.date('m/d/Y',strtotime($row->transaction_date)).']</b> - $'.round($row->final_total,2).' - '.$row->first_name.' ('.$row->accountid.')</h5>';
                $this->export_invoice_pdf($row->id,$upload_pdf_path);
            }
      }
      else
      {
        $output = '
            <td align="center" colspan="5">No Data Found<td>';
      }

      $data = array(
       'start' => $start,
       'limit' => $limit,
       'upload_dir_path' => $set_upload_dir_path,
       'result' => $result,
       'table_data'  => $output,
       'total_data'  => $total_row
      );
      echo json_encode($data);
     }
    }



    // export invoice tab start
    public function search_cigar_invoice(Request $request)
    {

     if($request->ajax())
     {

        $ranges = $request->post('ranges');
        $contact_id = $request->post('contact_id');
        $query = $request->get('query');
        $rates = $request->get('rates');
        $state = $request->get('state');
        $product_category = $request->get('product_category');
        $order_note = $request->post('order_note');
        $regular_invoice = $request->post('regular_invoice');

        $start = $request->post('start');
        $limit = $request->post('limit');
        $set_upload_dir_path = $request->get('upload_dir_path');



        if($ranges!='' && $start!='' && $limit!='')
        {
            $output = '';


        $data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name','contacts.tax_number','contacts.tobacco_license_no'  ,'contacts.address_line_1','contacts.address_line_2','contacts.state','contacts.state','contacts.country','contacts.zip_code','contacts.contact_id as accountid','contacts.state as state')
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final');
        // return $data->get();

            if(isset($ranges))
            {
                $myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $from = str_replace(' ', '', $fr1);
                $to = str_replace(' ', '', $to2);
                $new_fromdate = date('Y-m-d',strtotime($from));
                $new_todate = date('Y-m-d',strtotime('+23 hour +59 minutes +59 seconds',strtotime($to)));


                $data->whereDate('transactions.transaction_date','>=',$new_fromdate)->whereDate('transactions.transaction_date','<=',$new_todate);

                /*$myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $month = str_replace(' ', '', $fr1);
                $year = str_replace(' ', '', $to2);

                $data->whereMonth('transactions.transaction_date','=',$month)->whereYear('transactions.transaction_date','=',$year);*/
            }

            if(isset($contact_id)){
             $data->where('contacts.id','=', $contact_id);
            }

            if(isset($query)){
             $data->where('invoice_no', 'like', '%'.$query.'%');
            }
            if (!empty($rates)) {
                $data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
                // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
                $tax_rates_id = $request->get('rates');

                $data->where(function ($query) use($tax_rates_id) {
                    $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                          ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
                });
            }
            if(!empty($state)){
                $data->where('contacts.state', 'like', '%'.$state.'%');
            }

            $data = $data->groupBy('transactions.id')
            ->orderBy('transactions.transaction_date','desc')->get();
            //echo "sdf"; exit;

                if(empty($product_category))
                {
                   $filtered_sells_for_count = $data;
                }
                else
                {
                    $filtered_sells_for_count = [];
                    foreach($data as $row){
                                //  echo '<pre>'; print_r($row);die;


                        $transaction_id = $row->id;
                        $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                        if(count($transaction_sells)>0)
                        {
                            foreach($product_category as $category){
                                foreach($transaction_sells as $sell){
                                    if(isset($sell->product_single->category_id))
                                    {
                                        $mathch_cat_id = $sell->product_single->category_id;
                                        if($mathch_cat_id == $category){
                                            array_push($filtered_sells_for_count,$row);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            //$total_row = count($total_result);
            //echo $total_row; exit;
            $total_row = count($filtered_sells_for_count);
            $result = 'next';
            if( ($start + $limit ) >= $total_row)
            {
                    $result = 'finished';
            }

        }

      if($total_row > 0)
      {

            $output .= '<div class=""><table class="table table-bordered table-striped"  style="padding:10px;margin:10px;border:1px solid #coco;overflow-x:auto;" >';
            $output .= '<tr style="padding:10px;"><th style="padding:10px;">Invoice No</th><th style="padding:10px; width:18%;">Customer Name</th><th style="padding:10px;width:18%;">Address</th><th style="padding:10px;width:7%;">Ein of supplier</th><th  style="padding:10px;width:7%;"> Regular qty</th><th  style="padding:10px;width:7%;">Mini qty </th><th  style="padding:10px;width:7%;"> Sticks for Regular</th><th  style="padding:10px;width:7%;"> Sticks for Mini</th><th style="padding:10px;width:11%;"> Regular Price</th><th style="padding:10px;width:11%;"> Mini Price</th><th style="padding:10px;width:11%;">Regular Cost</th><th style="padding:10px;width:11%;"> Mini Cost</th></tr>';

        if(isset($set_upload_dir_path) && $set_upload_dir_path!="")
            {
                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;
            }
            else
            {
                //$set_upload_dir_path = 'invoices_'.$ranges;
                $set_upload_dir_path = time();

                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;

                if(is_dir($upload_pdf_path))
                {
                    //rmdir($upload_pdf_path);
                    //Storage::deleteDirectory($upload_pdf_path);
                    if(File::deleteDirectory($upload_pdf_path))
                    {
                        mkdir($upload_pdf_path, 0777, true);
                    }
                }
                else
                {
                    mkdir($upload_pdf_path, 0777, true);
                }
                /*if (!is_dir($upload_pdf_path))
                {
                        if(mkdir($upload_pdf_path, 0777, true))
                        {
                            //$output .= '<button class="btn btn-sm btn-primary"><a href="download_zip/'.$set_upload_dir_path.'" target="_blank" style="color:white; ">Download Invoices</a></button> <br>';
                        }
                }*/
            }

             if(!empty($product_category))
            {
                $filtered_sells = (array)$filtered_sells_for_count;
            }
            else
            {
                $filtered_sells = $filtered_sells_for_count->toArray();
            }


            if( ($start + $limit ) >= $total_row)
            {
                $limit_show = $total_row-1;
            }
            else
            {
                $limit_show = $start+($limit-1);
            }

                    $regular_count_footertotal = 0;
                     $mini_count_footertotal = 0;

                     $regular_total_footertotal = 0;
                     $mini_total_footertotal = 0;

                     $regular_dis_price_footertotal = 0;
                     $mini_total_cost_footertotal = 0;

                     $regular_total_cost_footertotal = 0;
                     $mini_dis_price_footertotal = 0;

            for($i=$start;$i<=$limit_show;$i++)
            {
                    $dis_id = empty($filtered_sells[$i]['id']) ? '' : $filtered_sells[$i]['id'] ;

                        $sells_lines = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$dis_id)->get();
                        // TransactionSellLine::select('*')->where('transaction_id',$dis_id)->get();

                        // $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$dis_id)->get();

                         $total_cost = 0;
                         $dis_quantity= 0;
                         $qty_box = 0;




                         $regular_count = 0;
                         $mini_count = 0;
                         $regular_total = 0;
                         $mini_total = 0;
                         $regular_total_cost = 0;
                         $regular_dis_price= 0;
                         $mini_total_cost = 0;
                         $mini_dis_price = 0;
                         foreach($sells_lines as $sell_line){


                            // $product_deatails = ProductVariation::where('product_id',$sell_line->product_id )
                            // ->with(['variations', 'variations.media'])
                            // ->first();
                            $products = Product::where('id',$sell_line->product_id)->select('qty_box')->first();
                            $sub_category_id = $sell_line->product_single->sub_category_id;

                           //   Regular
                            if(!empty($sub_category_id) && $sub_category_id == 368){
                                $regular_count += $sell_line->quantity ;
                                $regular_total += $products->qty_box * $sell_line->quantity;
                                $regular_total_cost += $sell_line->quantity * $sell_line->purchase_price ;
                                $regular_dis_price += $sell_line->quantity * $sell_line->unit_price_inc_tax ;

                                // $qty_box += $products->qty_box;
                                $regular_count_footertotal += $products->qty_box * $sell_line->quantity;
                                 $regular_total_footertotal +=  $sell_line->quantity;
                                 $regular_total_cost_footertotal += $sell_line->quantity * $sell_line->purchase_price;
                                 $regular_dis_price_footertotal += $sell_line->quantity * $sell_line->unit_price_inc_tax;
                            }



                            //   mini
                             if(!empty($sub_category_id) && $sub_category_id == 381){
                                $mini_count += $sell_line->quantity ;
                                $mini_total += $products->qty_box *$sell_line->quantity;
                                $mini_total_cost += $sell_line->quantity * $sell_line->purchase_price ;
                                $mini_dis_price += $sell_line->quantity * $sell_line->unit_price_inc_tax ;
                                // $qty_box += $products->qty_box;

                                 $mini_count_footertotal += $products->qty_box * $sell_line->quantity;
                                 $mini_total_footertotal +=  $sell_line->quantity;
                                 $mini_total_cost_footertotal += $sell_line->quantity * $sell_line->purchase_price;
                                 $mini_dis_price_footertotal += $sell_line->quantity * $sell_line->unit_price_inc_tax;
                            }


                            //  $total_cost += $sell_line->quantity * $sell_line->purchase_price ;//+ $product_deatails->variations[0]->dpp_inc_tax;
                            //  echo '<pre>'; print_r($sell_line->product_single->sub_category_id);
                            // $dis_quantity += $sell_line->quantity;

                         }
                        //  die;
                    $dis_invoice_no = empty($filtered_sells[$i]['invoice_no']) ? '' : $filtered_sells[$i]['invoice_no'];
                    $dis_transaction_date = empty($filtered_sells[$i]['transaction_date']) ? '' : $filtered_sells[$i]['transaction_date'];
                    $dis_final_total = empty($filtered_sells[$i]['final_total']) ? '' : $filtered_sells[$i]['final_total'];
                    $dis_first_name = empty($filtered_sells[$i]['first_name']) ? '' : $filtered_sells[$i]['first_name'];
                    $dis_address_line_1 = empty($filtered_sells[$i]['address_line_1']) ? '' : $filtered_sells[$i]['address_line_1'] .',';
                    $dis_address_line_2 = empty($filtered_sells[$i]['address_line_2']) ? '' : $filtered_sells[$i]['address_line_2'] .',';
                    $dis_city = empty($filtered_sells[$i]['city']) ? '' : $filtered_sells[$i]['city'].',';
                    $dis_state = empty($filtered_sells[$i]['state']) ? '' : $filtered_sells[$i]['state'].',';
                    $dis_country = empty($filtered_sells[$i]['country']) ? '' : $filtered_sells[$i]['country'].',';
                    $dis_zip_code = empty($filtered_sells[$i]['zip_code']) ? '' : $filtered_sells[$i]['zip_code'];


                    $dis_box_qty = empty($filtered_sells[$i]['box_qty']) ? '' : $filtered_sells[$i]['box_qty'];
                    $dis_tax_number = empty($filtered_sells[$i]['tobacco_license_no']) ? '' : $filtered_sells[$i]['tobacco_license_no'];

                    $dis_accountid = empty($filtered_sells[$i]['accountid']) ? '' : $filtered_sells[$i]['accountid'];

                    $output .= '<tr><td><a href="open_invoice/'.$dis_id.'" target="_blank">'.$dis_invoice_no.'</td><td>'.$dis_first_name.' ('.$dis_accountid.')</td><td>'.$dis_address_line_1 . $dis_address_line_2 . $dis_city .$dis_state.$dis_country.$dis_zip_code.'</td><td>'.$dis_tax_number.'</td><td style ="text-align:right !important;">'. $regular_count .'</td> <td style ="text-align:right !important;">'. $mini_count .'</td>
                                <td style ="text-align:right !important;width:9%;">'. $regular_total .'</td><td style ="text-align:right !important;width:9%;">'. $mini_total  .'</td>
                                <td style ="text-align:right !important;width:11%;">$ '.round($regular_dis_price,2).'</td> <td style ="text-align:right !important;width:11%;">$ '. round($mini_dis_price ,2) .'</td>
                                <td style ="text-align:right !important;width:11%;">$ '.round($regular_total_cost,2) .'</td><td style ="text-align:right !important;width:11%;">$ '.round($mini_total_cost,2).'</td></tr>';
                    // '<h5><a href="open_invoice/'.$dis_id.'" target="_blank">'.$dis_invoice_no.'</a> <b>['.date('m/d/Y',strtotime($dis_transaction_date)).']</b> - $'.round($dis_final_total,2).' - '.$dis_first_name.' ('.$dis_accountid.')</h5>';
                    // $this->export_invoice_pdf($dis_id,$upload_pdf_path,$order_note,$regular_invoice);
            }
            $output .=  '<tfoot><tr style ="text-align:center !important;font-size:18px;font-weight: bold;" ><td colspan ="4" >Total </td>
                            <td>  '.$regular_total_footertotal.'</td><td>'.$mini_total_footertotal.'</td>
                            <td> '.$regular_count_footertotal.'</td><td>'.$mini_count_footertotal.'</td>
                            <td>$ '. round($regular_dis_price_footertotal,2).'</td><td>$ '.round($mini_dis_price_footertotal,2).'</td>
                            <td>$ '. round($regular_total_cost_footertotal,2).'</td><td>$ '.round($mini_total_cost_footertotal,2).'</td></tr>
                            </tfoot>';
            $output .=  '</table></div>';

      }
      else
      {
        $output = '
            <td align="center" colspan="5">No Data Found<td>';
      }

      $data = array(
       'start' => $start,
       'limit' => $limit,
       'upload_dir_path' => $set_upload_dir_path,
       'result' => $result,
       'table_data'  => $output,
       'total_data'  => $total_row
      );
      echo json_encode($data);
     }
    }




    // export invoice tab start
    public function search_tax_invoice(Request $request)
    {

     if($request->ajax())
     {

        $ranges = $request->post('ranges');
        $contact_id = $request->post('contact_id');
        $query = $request->get('query');
        $rates = $request->get('rates');
        $state = $request->get('state');
        $product_category = $request->get('product_category');
        $order_note = $request->post('order_note');
        $regular_invoice = $request->post('regular_invoice');

        $start = $request->post('start');
        $limit = $request->post('limit');
        $set_upload_dir_path = $request->get('upload_dir_path');



        if($ranges!='' && $start!='' && $limit!='')
        {
            $output = '';


        $data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name', 'contacts.tobacco_license_no' , 'contacts.tax_number'  ,'contacts.address_line_1','contacts.address_line_2','contacts.state','contacts.state','contacts.country','contacts.zip_code','contacts.contact_id as accountid','contacts.state as state')
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final');
        // return $data->get();

            if(isset($ranges))
            {
                $myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $from = str_replace(' ', '', $fr1);
                $to = str_replace(' ', '', $to2);
                $new_fromdate = date('Y-m-d',strtotime($from));
                $new_todate = date('Y-m-d',strtotime('+23 hour +59 minutes +59 seconds',strtotime($to)));


                $data->whereDate('transactions.transaction_date','>=',$new_fromdate)->whereDate('transactions.transaction_date','<=',$new_todate);

                /*$myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $month = str_replace(' ', '', $fr1);
                $year = str_replace(' ', '', $to2);

                $data->whereMonth('transactions.transaction_date','=',$month)->whereYear('transactions.transaction_date','=',$year);*/
            }

            if(isset($contact_id)){
             $data->where('contacts.id','=', $contact_id);
            }

            if(isset($query)){
             $data->where('invoice_no', 'like', '%'.$query.'%');
            }
            if (!empty($rates)) {
                $data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
                // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
                $tax_rates_id = $request->get('rates');

                $data->where(function ($query) use($tax_rates_id) {
                    $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                          ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
                });
            }
            if(!empty($state)){
                $data->where('contacts.state', 'like', '%'.$state.'%');
            }

            $data = $data->groupBy('transactions.id')
            ->orderBy('transactions.transaction_date','desc')->get();
            //echo "sdf"; exit;

                if(empty($product_category))
                {
                   $filtered_sells_for_count = $data;
                }
                else
                {
                    $filtered_sells_for_count = [];
                    foreach($data as $row){
                        //  echo '<pre>'; print_r($row);die;

                        // $tax_amount= $row->tax_amount;


                        $transaction_id = $row->id;
                        $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                        if(count($transaction_sells)>0)
                        {
                            foreach($product_category as $category){
                                foreach($transaction_sells as $sell){
                                    if(isset($sell->product_single->category_id))
                                    {
                                        $mathch_cat_id = $sell->product_single->category_id;
                                        if($mathch_cat_id == $category){
                                            array_push($filtered_sells_for_count,$row);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
            //$total_row = count($total_result);
            //echo $total_row; exit;
            $total_row = count($filtered_sells_for_count);
            $result = 'next';
            if( ($start + $limit ) >= $total_row)
            {
                    $result = 'finished';
            }

        }

      if($total_row > 0)
      {

            $output .= '<div class=""><table class="table table-bordered table-striped"  style="padding:10px;margin:10px;border:1px solid #coco;overflow-x:auto;" >';
            $output .= '<tr style="padding:10px;"><th style="padding:10px;width:13%;">Transaction Date</th><th style="padding:10px;width:4%;">Invoice No</th><th style="padding:10px; width:18%;">Customer Name</th><th style="padding:10px;width:18%;">Address</th><th style="padding:10px;width:7%;">Ein of supplier</th><th  style="padding:10px;width:7%;"> Tax Amount</th><th  style="padding:10px;width:7%;">Invoice Total </th></tr>';

        if(isset($set_upload_dir_path) && $set_upload_dir_path!="")
            {
                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;
            }
            else
            {
                //$set_upload_dir_path = 'invoices_'.$ranges;
                $set_upload_dir_path = time();

                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;

                if(is_dir($upload_pdf_path))
                {
                    //rmdir($upload_pdf_path);
                    //Storage::deleteDirectory($upload_pdf_path);
                    if(File::deleteDirectory($upload_pdf_path))
                    {
                        mkdir($upload_pdf_path, 0777, true);
                    }
                }
                else
                {
                    mkdir($upload_pdf_path, 0777, true);
                }
                /*if (!is_dir($upload_pdf_path))
                {
                        if(mkdir($upload_pdf_path, 0777, true))
                        {
                            //$output .= '<button class="btn btn-sm btn-primary"><a href="download_zip/'.$set_upload_dir_path.'" target="_blank" style="color:white; ">Download Invoices</a></button> <br>';
                        }
                }*/
            }

             if(!empty($product_category))
            {
                $filtered_sells = (array)$filtered_sells_for_count;
            }
            else
            {
                $filtered_sells = $filtered_sells_for_count->toArray();
            }


            if( ($start + $limit ) >= $total_row)
            {
                $limit_show = $total_row-1;
            }
            else
            {
                $limit_show = $start+($limit-1);
            }

                    $regular_count_footertotal = 0;
                     $mini_count_footertotal = 0;

                     $regular_total_footertotal = 0;
                     $mini_total_footertotal = 0;

                     $regular_dis_price_footertotal = 0;
                     $mini_total_cost_footertotal = 0;

                     $regular_total_cost_footertotal = 0;
                     $mini_dis_price_footertotal = 0;


            $footer_tax = 0;
            $footer_sub_total = 0;
            for($i=$start;$i<=$limit_show;$i++)
            {
                    $dis_id = empty($filtered_sells[$i]['id']) ? '' : $filtered_sells[$i]['id'] ;

                        $sells_lines = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$dis_id)->get();

                         $total_cost = 0;
                         $dis_quantity= 0;
                         $qty_box = 0;


                         $regular_count = 0;
                         $mini_count = 0;
                         $regular_total = 0;
                         $mini_total = 0;
                         $regular_total_cost = 0;
                         $regular_dis_price= 0;
                         $mini_total_cost = 0;
                         $mini_dis_price = 0;

                         $total_tax = 0;

                         foreach($sells_lines as $sell_line){
                            $total_tax += $sell_line->pos_line_tax_amount;
                            $footer_tax +=  $sell_line->pos_line_tax_amount;

                            $products = Product::where('id',$sell_line->product_id)->select('qty_box')->first();
                            $sub_category_id = $sell_line->product_single->sub_category_id;

                           //   Regular
                            if(!empty($sub_category_id) && $sub_category_id == 368){
                                $regular_count += $sell_line->quantity ;
                                $regular_total += $products->qty_box * $sell_line->quantity;
                                $regular_total_cost += $sell_line->quantity * $sell_line->purchase_price ;
                                $regular_dis_price += $sell_line->quantity * $sell_line->unit_price_inc_tax ;

                                // $qty_box += $products->qty_box;
                                $regular_count_footertotal += $products->qty_box * $sell_line->quantity;
                                 $regular_total_footertotal +=  $sell_line->quantity;
                                 $regular_total_cost_footertotal += $sell_line->quantity * $sell_line->purchase_price;
                                 $regular_dis_price_footertotal += $sell_line->quantity * $sell_line->unit_price_inc_tax;
                            }



                            //   mini
                             if(!empty($sub_category_id) && $sub_category_id == 381){
                                $mini_count += $sell_line->quantity ;
                                $mini_total += $products->qty_box *$sell_line->quantity;
                                $mini_total_cost += $sell_line->quantity * $sell_line->purchase_price ;
                                $mini_dis_price += $sell_line->quantity * $sell_line->unit_price_inc_tax ;
                                // $qty_box += $products->qty_box;

                                 $mini_count_footertotal += $products->qty_box * $sell_line->quantity;
                                 $mini_total_footertotal +=  $sell_line->quantity;
                                 $mini_total_cost_footertotal += $sell_line->quantity * $sell_line->purchase_price;
                                 $mini_dis_price_footertotal += $sell_line->quantity * $sell_line->unit_price_inc_tax;
                            }


                            //  $total_cost += $sell_line->quantity * $sell_line->purchase_price ;//+ $product_deatails->variations[0]->dpp_inc_tax;
                            //  echo '<pre>'; print_r($sell_line->product_single->sub_category_id);
                            // $dis_quantity += $sell_line->quantity;

                         }
                        //  die;
                    $dis_invoice_no = empty($filtered_sells[$i]['invoice_no']) ? '' : $filtered_sells[$i]['invoice_no'];
                    $dis_transaction_date = empty($filtered_sells[$i]['transaction_date']) ? '' : $filtered_sells[$i]['transaction_date'];
                    $dis_final_total = empty($filtered_sells[$i]['final_total']) ? '' : $filtered_sells[$i]['final_total'];
                    $dis_first_name = empty($filtered_sells[$i]['first_name']) ? '' : $filtered_sells[$i]['first_name'];
                    $dis_address_line_1 = empty($filtered_sells[$i]['address_line_1']) ? '' : $filtered_sells[$i]['address_line_1'] .',';
                    $dis_address_line_2 = empty($filtered_sells[$i]['address_line_2']) ? '' : $filtered_sells[$i]['address_line_2'] .',';
                    $dis_city = empty($filtered_sells[$i]['city']) ? '' : $filtered_sells[$i]['city'].',';
                    $dis_state = empty($filtered_sells[$i]['state']) ? '' : $filtered_sells[$i]['state'].',';
                    $dis_country = empty($filtered_sells[$i]['country']) ? '' : $filtered_sells[$i]['country'].',';
                    $dis_zip_code = empty($filtered_sells[$i]['zip_code']) ? '' : $filtered_sells[$i]['zip_code'];
                    $dis_tax_amount = empty($filtered_sells[$i]['tax_amount']) ? '' : $filtered_sells[$i]['tax_amount'];

                    $footer_sub_total += $dis_final_total;
                    $dis_box_qty = empty($filtered_sells[$i]['box_qty']) ? '' : $filtered_sells[$i]['box_qty'];
                    $dis_tax_number = empty($filtered_sells[$i]['tobacco_license_no']) ? '' : $filtered_sells[$i]['tobacco_license_no'];

                    $dis_accountid = empty($filtered_sells[$i]['accountid']) ? '' : $filtered_sells[$i]['accountid'];

                    $output .= '<tr><td>'.date('m/d/Y h:i',strtotime($dis_transaction_date)).'<td><a href="open_invoice/'.$dis_id.'" target="_blank">'.$dis_invoice_no.'</td><td>'.$dis_first_name.' ('.$dis_accountid.')</td><td>'.$dis_address_line_1 . $dis_address_line_2 . $dis_city .$dis_state.$dis_country.$dis_zip_code.'</td><td>'.$dis_tax_number.'</td><td style ="text-align:right !important;">$ '. $total_tax .'</td> <td style ="text-align:right !important;">$ '. round($dis_final_total,2) .'</td></tr>';
                    // '<h5><a href="open_invoice/'.$dis_id.'" target="_blank">'.$dis_invoice_no.'</a> <b>['.date('m/d/Y',strtotime($dis_transaction_date)).']</b> - $'.round($dis_final_total,2).' - '.$dis_first_name.' ('.$dis_accountid.')</h5>';
                    // $this->export_invoice_pdf($dis_id,$upload_pdf_path,$order_note,$regular_invoice);
            }
            $output .=  '<tfoot><tr style ="text-align:center !important;font-size:18px;font-weight: bold;" ><td colspan ="5" >Total </td>
                            <td> $ '.$footer_tax.'</td><td> $ '.$footer_sub_total.'</td>
                            </tr>
                            </tfoot>';
            $output .=  '</table></div>';

      }
      else
      {
        $output = '
            <td align="center" colspan="5">No Data Found<td>';
      }

      $data = array(
       'start' => $start,
       'limit' => $limit,
       'upload_dir_path' => $set_upload_dir_path,
       'result' => $result,
       'table_data'  => $output,
       'total_data'  => $total_row
      );
      echo json_encode($data);
     }
    }
    public function export_invoice_pdf($id,$upload_pdf_path)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {

            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                   ->findorfail($id);
            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content_pdf',false, true, $invoice_layout_id);
            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $pdf_html= view('sale_pos.partials.export_pdf_invoice')->with(compact('receipt', 'title'))->render();
            $for_pdf = true;
            $html = $pdf_html;
            //print_r($html); exit;
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            //$file = $upload_pdf_path . '/' . time() .'_'.$id. '_invoice.pdf';
            $file = $upload_pdf_path . '/' . $transaction->invoice_no. '.pdf';
            $mpdf->Output($file, 'F');
        }
    }
     public function export_regular_invoice_pdf($id,$upload_pdf_path)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {

           $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                   ->findorfail($id);
            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content_pdf1',false, true, $invoice_layout_id);
            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $pdf_html= view('sale_pos.partials.export_reg_pdf_invoice')->with(compact('receipt', 'title'))->render();
            $for_pdf = true;
            $html = $pdf_html;
            //print_r($html); exit;
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            //$file = $upload_pdf_path . '/' . time() .'_'.$id. '_invoice.pdf';
            $file = $upload_pdf_path . '/' . $transaction->invoice_no. '.pdf';
            $mpdf->Output($file, 'F');
        }
    }

    public function download_invoice_zip($folder_name)
    {
        if (is_dir(config('constants.mpdf_temp_path').'/invoices/'.$folder_name))
        {
            $zip = new \ZipArchive();
            $fileName = $folder_name.'.zip';
            if ($zip->open(config('constants.mpdf_temp_path').'/invoices/'.$folder_name.'/'.$fileName, \ZipArchive::CREATE)== TRUE)
            {
                $files = File::files(config('constants.mpdf_temp_path').'/invoices/'.$folder_name.'/');
                foreach ($files as $key => $value){
                    $relativeName = basename($value);
                    $zip->addFile($value, $relativeName);
                }
                $zip->close();
            }

            return response()->download(config('constants.mpdf_temp_path').'/invoices/'.$folder_name.'/'.$fileName)->deleteFileAfterSend(true);
        }
        else
        {
        	return redirect()->action('SellPosController@exportInvoice')->with('success', 'No files available!!');
        }
    }
    // export invoice tab end

    public function allinvoice()
    {
        $business_id = request()->session()->get('user.business_id');

        $tran =Transaction::where('transactions.status', 'final')
        ->where('transactions.type', 'sell')
        ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name')
        ->get();


        // $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $categoriesdata = Category::forDropdown($business_id, 'product');
        $categories = Category::where('business_id',$business_id)->where('category_type','product')->get();

        $tax_rates = TaxRate::join('categories as cat', 'cat.id', '=', 'tax_rates.category')->select(DB::raw("CONCAT(tax_rates.name,' - ',cat.name) AS name"),'tax_rates.id')->orderBy('name')->pluck('name','id');


        return view('sale_pos.receipts.all_invoice')->with(compact('tran','tax_rates','categories','categoriesdata'));
    }

    //export invoice views start
    public function exportInvoice()
    {
        $business_id = request()->session()->get('user.business_id');

       /* $tran =Transaction::where('transactions.status', 'final')
        ->where('transactions.type', 'sell')
        ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name')
        ->get();*/


        // $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $categoriesdata = Category::forDropdown($business_id, 'product');
        $categories = Category::where('business_id',$business_id)->where('category_type','product')->get();

        $tax_rates = TaxRate::join('categories as cat', 'cat.id', '=', 'tax_rates.category')->select(DB::raw("CONCAT(tax_rates.name,' - ',cat.name) AS name"),'tax_rates.id')->orderBy('name')->pluck('name','id');

        $contact_dropdown = Contact::where('business_id',$business_id)->where('type','customer')->get();

        return view('sale_pos.receipts.export_invoice')->with(compact('tax_rates','categories','categoriesdata','contact_dropdown'));
    }
    //export invoice views end

    public function showInvoiceUrl($id)
    {
        // if (!auth()->user()->can('sell.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                   ->findorfail($id);
            $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

            $transaction_new = Transaction::where('business_id', $business_id)
            ->findorfail($id);

            $wa_link = "";
            $sms_number = 0;
            $sms_content = "";
            $short_msg = "";
            $contact = Contact::find($transaction_new->contact_id);
            if(!empty($contact) && !empty($transaction_new->invoice_token)){
                $wa_number = preg_replace('/[^0-9]/', '', $contact->whatsapp ?? '');
                if(strlen($wa_number) == 10){
                    $wa_number = "1".$wa_number;
                }
                if(strlen($wa_number) > 10){
                    $wa_link = "https://wa.me/" . $wa_number . "?text=Hi there, this is your invoice: https://invoicetopdf.com/psd/" . $transaction_new->invoice_token;
                    $short_msg = "Hi there, this is your invoice: https://invoicetopdf.com/psd/" . $transaction_new->invoice_token;
                }

                $sms_number = preg_replace('/[^0-9]/', '', $contact->mobile ?? '');
                $sms_content = "https://invoicetopdf.com/psd/" . $transaction_new->invoice_token;
                if(strlen($sms_number) == 10){
                    $sms_number = "1".$sms_number;
                }
                if(strlen($wa_number) < 10){
                    $sms_number = 0;
                }
                // $sms_number = '16622577055';
            }

            return view('sale_pos.partials.invoice_url_modal')
                    ->with(compact('transaction', 'url', 'wa_link', 'sms_number','sms_content','short_msg','wa_number'));
        }
    }

    /**
     * Shows invoice to guest user.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
 public function showInvoice($token,$print="")
    {
        // abort(403, 'Unauthorized action.');

        $transaction = Transaction::where('invoice_token', $token)->with(['business', 'location'])->first();

        $new = $this->showInvoiceUrl($transaction->id);
        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;
            $inv_controller = new InvoiceController();
            if($print=="")
            {
                return $inv_controller->smartInvoice($transaction->id, "", false);
                // $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            }
            else
            {
                return $inv_controller->smartInvoice($transaction->id, 1, false);
                // $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content_print',false, true, $invoice_layout_id);
            }

            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;

            // $business_id = request()->session()->get('user.business_id');
            // $transaction = Transaction::where('business_id', $business_id)
            //                       ->findorfail($transaction->id);
            // $url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);

            $url =route('show_invoice', ['token' => $transaction->invoice_token]);
            $tid = $transaction->id;
            return view('sale_pos.partials.show_invoice')
                    ->with(compact('receipt', 'title','url','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }
     public function packing_gen($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content4',false, true, $invoice_layout_id);

            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            return view('sale_pos.partials.show_packinggen')
                    ->with(compact('receipt', 'title'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    public function packing_slip($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);

            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;
            return view('sale_pos.partials.show_packingslip')
                    ->with(compact('receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    public function packing_slip_blank($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            $receipt['html_content2'] = $receipt['html_content5'];

            $contact = Contact::find($transaction->contact_id);
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;

            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;

            $wa_number = '';
            if(!empty($contact)){
                $wa_number = preg_replace('/[^0-9]/', '', $contact->whatsapp ?? '');
                if(strlen($wa_number) == 10){
                    $wa_number = "1".$wa_number;
                }
            }

            return view('sale_pos.partials.show_blank_packingslip')
                    ->with(compact('wa_number','receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    public function packing_slip_blank_without_price($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            $receipt['html_content2'] = $receipt['html_content9'];

            $contact = Contact::find($transaction->contact_id);
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;

            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;

            $wa_number = '';
            if(!empty($contact)){
                $wa_number = preg_replace('/[^0-9]/', '', $contact->whatsapp ?? '');
                if(strlen($wa_number) == 10){
                    $wa_number = "1".$wa_number;
                }
            }

            return view('sale_pos.partials.show_blank_packingslip_without_price')
                    ->with(compact('wa_number','receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }
    public function blank_slip($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            $receipt['html_content2'] = $receipt['html_content8'];

            $contact = Contact::find($transaction->contact_id);
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;


            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;
            return view('sale_pos.partials.show_blank_packingslip')
                    ->with(compact('receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    public function open_invoice($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {

            $tax_information = $this->transactionUtil->getTaxDetails($transaction->id);
            if(!empty($tax_information))
            {
                return redirect()->action('SellPosController@invoicegen', [$transaction->id]);
            }

            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                   ->findorfail($id);
            $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            $contact = Contact::find($transaction->contact_id);

            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;


            $tid = $transaction->id;
            return view('sale_pos.partials.show_invoice')
                    ->with(compact('receipt', 'title','url','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    /**
     * Shows invoice url.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function driverinvoice($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        // $query = Transaction::select('id','final_total','invoice_no','transaction_date','payment_status','type')
        //     ->where('contact_id', $transaction->contact_id)
        //     ->where('type', 'sell')
        //     ->where('status', 'final')
        //     ->latest()
        //     ->take(5)
        //     ->get();

        // echo "<pre>";
        // print_r($query);
        // die;


        if (!empty($transaction))
        {
        $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

        $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser','html_content1', false, true, $invoice_layout_id);

        $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            return view('sale_pos.partials.show_driver_invoice')->with(compact('receipt', 'title'));
        }
        else
        {
            die(__("messages.something_went_wrong"));
        }
    }

    public function invoicegen($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);

            $contact = Contact::find($transaction->contact_id);

            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;

            $tid = $transaction->id;
            return view('sale_pos.partials.show_invoicegen')
                    ->with(compact('receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    public function OBInvoice($id)
    {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);

            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;
            return view('sale_pos.partials.show_obinvoice')
                    ->with(compact('receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    /**
     * Display a listing of the recurring invoices.
     *
     * @return \Illuminate\Http\Response
     */
    public function listSubscriptions()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_recurring', 1)
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.subscription_no',
                    'bl.name as business_location',
                    'transactions.recur_parent_id',
                    'transactions.recur_stopped_on',
                    'transactions.is_recurring',
                    'transactions.recur_interval',
                    'transactions.recur_interval_type',
                    'transactions.recur_repetitions',
                    'transactions.subscription_repeat_on'
                )->with(['subscription_invoices']);



            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }
            if (!empty(request()->contact_id)) {
                $sells->where('transactions.contact_id', request()->contact_id);
            }
            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '' ;

                        if ($row->is_recurring == 1 && auth()->user()->can("sell.update")) {
                            $link_text = !empty($row->recur_stopped_on) ? __('lang_v1.start_subscription') : __('lang_v1.stop_subscription');
                            $link_class = !empty($row->recur_stopped_on) ? 'btn-success' : 'btn-danger';

                            $html .= '<a href="' . action('SellPosController@toggleRecurringInvoices', [$row->id]) . '" class="toggle_recurring_invoice btn btn-xs ' . $link_class . '"><i class="fa fa-power-off"></i> ' . $link_text . '</a>';

                            if ($row->is_direct_sale == 0) {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="' . action('SellPosController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a>';
                            } else {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="' . action('SellController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a>';
                            }

                            if (auth()->user()->can("direct_sell.delete") || auth()->user()->can("sell.delete")) {
                                $html .= '&nbsp;<a href="' . action('SellPosController@destroy', [$row->id]) . '" class="delete-sale btn btn-xs btn-danger"><i class="fas fa-trash"></i> ' . __("messages.delete") . '</a>';
                            }
                        }

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('recur_interval', function ($row) {
                    $type = $row->recur_interval == 1 ? Str::singular(__('lang_v1.' . $row->recur_interval_type)) : __('lang_v1.' . $row->recur_interval_type);
                    $recur_interval = $row->recur_interval . $type;

                    if ($row->recur_interval_type == 'months' && !empty($row->subscription_repeat_on)) {
                        $recur_interval .= '<br><small class="text-muted">' .
                        __('lang_v1.repeat_on') . ': ' . str_ordinal($row->subscription_repeat_on) ;
                    }
                    return $recur_interval;
                })
                ->editColumn('recur_repetitions', function ($row) {
                    return !empty($row->recur_repetitions) ? $row->recur_repetitions : '-';
                })
                ->addColumn('subscription_invoices', function ($row) {
                    $invoices = [];
                    if (!empty($row->subscription_invoices)) {
                        $invoices = $row->subscription_invoices->pluck('invoice_no')->toArray();
                    }

                    $html = '';
                    $count = 0;
                    if (!empty($invoices)) {
                        $imploded_invoices = '<span class="label bg-info">' . implode('</span>, <span class="label bg-info">', $invoices) . '</span>';
                        $count = count($invoices);
                        $html .= '<small>' . $imploded_invoices . '</small>';
                    }
                    if ($count > 0) {
                        $html .= '<br><small class="text-muted">' .
                    __('sale.total') . ': ' . $count . '</small>';
                    }

                    return $html;
                })
                ->addColumn('last_generated', function ($row) {
                    if (!empty($row->subscription_invoices)) {
                        $last_generated_date = $row->subscription_invoices->max('created_at');
                    }
                    return !empty($last_generated_date) ? $last_generated_date->diffForHumans() : '';
                })
                ->addColumn('upcoming_invoice', function ($row) {
                    if (empty($row->recur_stopped_on)) {
                        $last_generated = !empty(count($row->subscription_invoices)) ? \Carbon::parse($row->subscription_invoices->max('transaction_date')) : \Carbon::parse($row->transaction_date);
                        $last_generated_string = $last_generated->format('Y-m-d');
                        $last_generated = \Carbon::parse($last_generated_string);

                        if ($row->recur_interval_type == 'days') {
                            $upcoming_invoice = $last_generated->addDays($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'months') {
                            if (!empty($row->subscription_repeat_on)) {
                                $last_generated_string = $last_generated->format('Y-m');
                                $last_generated = \Carbon::parse($last_generated_string . '-' . $row->subscription_repeat_on);
                            }

                            $upcoming_invoice = $last_generated->addMonths($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'years') {
                            $upcoming_invoice = $last_generated->addYears($row->recur_interval);
                        }
                    }
                    return !empty($upcoming_invoice) ? $this->transactionUtil->format_date($upcoming_invoice) : '';
                })
                ->rawColumns(['action', 'subscription_invoices', 'recur_interval'])
                ->make(true);

            return $datatable;
        }
        return view('sale_pos.subscriptions');
    }

    /**
     * Starts or stops a recurring invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleRecurringInvoices($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->where('is_recurring', 1)
                            ->findorfail($id);

            if (empty($transaction->recur_stopped_on)) {
                $transaction->recur_stopped_on = \Carbon::now();
            } else {
                $transaction->recur_stopped_on = null;
            }
            $transaction->save();

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

    public function getRewardDetails(Request $request)
    {
        if ($request->session()->get('business.enable_rp') != 1) {
            return '';
        }

        $business_id = request()->session()->get('user.business_id');

        $customer_id = $request->input('customer_id');

        $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $customer_id);

        return json_encode($redeem_details);
    }

    public function placeOrdersApi(Request $request)
    {
        try {
            $api_token = $request->header('API-TOKEN');
            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $business_id = $api_settings->business_id;
            $location_id = $api_settings->location_id;

            $input = $request->only(['products', 'customer_id', 'addresses']);

            //check if all stocks are available
            $variation_ids = [];
            foreach ($input['products'] as $product_data) {
                $variation_ids[] = $product_data['variation_id'];
            }

            $variations_details = $this->getVariationsDetails($business_id, $location_id, $variation_ids);
            $is_valid = true;
            $error_messages = [];
            $sell_lines = [];
            $final_total = 0;
            foreach ($variations_details as $variation_details) {
                if ($variation_details->product->enable_stock == 1) {
                    if (empty($variation_details->variation_location_details[0]) || $variation_details->variation_location_details[0]->qty_available < $input['products'][$variation_details->id]['quantity']) {
                        $is_valid = false;
                        $error_messages[] = 'Only ' . $variation_details->variation_location_details[0]->qty_available . ' ' . $variation_details->product->unit->short_name . ' of '. $input['products'][$variation_details->id]['product_name'] . ' available';
                    }
                }

                //Create product line array
                $sell_lines[] = [
                    'product_id' => $variation_details->product->id,
                    'unit_price_before_discount' => $variation_details->unit_price_inc_tax,
                    'unit_price' => $variation_details->unit_price_inc_tax,
                    'unit_price_inc_tax' => $variation_details->unit_price_inc_tax,
                    'variation_id' => $variation_details->id,
                    'quantity' => $input['products'][$variation_details->id]['quantity'],
                    'item_tax' => 0,
                    'enable_stock' => $variation_details->product->enable_stock,
                    'tax_id' => null,
                ];

                $final_total += ($input['products'][$variation_details->id]['quantity'] * $variation_details->unit_price_inc_tax);
            }

            if (!$is_valid) {
                return $this->respond([
                    'success' => false,
                    'error_messages' => $error_messages
                ]);
            }

            $business = Business::find($business_id);
            $user_id = $business->owner_id;

            $business_data = [
                'id' => $business_id,
                'accounting_method' => $business->accounting_method,
                'location_id' => $location_id
            ];

            $customer = Contact::where('business_id', $business_id)
                            ->whereIn('type', ['customer', 'both'])
                            ->find($input['customer_id']);

            $order_data = [
                'business_id' => $business_id,
                'location_id' => $location_id,
                'contact_id' => $input['customer_id'],
                'final_total' => $final_total,
                'created_by' => $user_id,
                'status' => 'final',
                'payment_status' => 'due',
                'additional_notes' => '',
                'transaction_date' => \Carbon::now(),
                'customer_group_id' => $customer->customer_group_id,
                'tax_rate_id' => null,
                'sale_note' => null,
                'commission_agent' => null,
                'order_addresses' => json_encode($input['addresses']),
                'products' => $sell_lines,
                'is_created_from_api' => 1,
                'discount_type' => 'fixed',
                'discount_amount' => 0
            ];

            $invoice_total = [
                'total_before_tax' => $final_total,
                'tax' => 0,
            ];

            DB::beginTransaction();

            $transaction = $this->transactionUtil->createSellTransaction($business_id, $order_data, $invoice_total, $user_id, false);

            //Create sell lines
            $this->transactionUtil->createOrUpdateSellLines($transaction, $order_data['products'], $order_data['location_id'], false, null, [], false);

            //update product stock
            foreach ($order_data['products'] as $product) {
                if ($product['enable_stock']) {
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $order_data['location_id'],
                        $product['quantity']
                    );
                }
            }

            $this->transactionUtil->mapPurchaseSell($business_data, $transaction->sell_lines, 'purchase');
            //Auto send notification
            $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);

            DB::commit();

            $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction->id);

            $output = [
                'success' => 1,
                'transaction' => $transaction,
                'receipt' => $receipt
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $msg = trans("messages.something_went_wrong");

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }

            $output = ['success' => 0,
                        'error_messages' => [$msg]
                    ];
        }

        return $this->respond($output);
    }

    private function getVariationsDetails($business_id, $location_id, $variation_ids)
    {
        $variation_details = Variation::whereIn('id', $variation_ids)
                            ->with([
                                'product' => function ($q) use ($business_id) {
                                    $q->where('business_id', $business_id);
                                },
                                'product.unit',
                                'variation_location_details' => function ($q) use ($location_id) {
                                    $q->where('location_id', $location_id);
                                }
                            ])->get();

        return $variation_details;
    }

    public function getTypesOfServiceDetails(Request $request)
    {
        $location_id = $request->input('location_id');
        $types_of_service_id = $request->input('types_of_service_id');

        $business_id = $request->session()->get('user.business_id');

        $types_of_service = TypesOfService::where('business_id', $business_id)
                                        ->where('id', $types_of_service_id)
                                        ->first();

        $price_group_id = !empty($types_of_service->location_price_group[$location_id])
                ? $types_of_service->location_price_group[$location_id] : '';
        $price_group_name = '';

        if (!empty($price_group_id)) {
            $price_group = SellingPriceGroup::find($price_group_id);
            $price_group_name = $price_group->name;
        }

        $modal_html = view('types_of_service.pos_form_modal')
                    ->with(compact('types_of_service'))->render();

        return $this->respond([
                'price_group_id' => $price_group_id,
                'packing_charge' => $types_of_service->packing_charge,
                'packing_charge_type' => $types_of_service->packing_charge_type,
                'modal_html' => $modal_html,
                'price_group_name' => $price_group_name
            ]);
    }

    private function __getwarranties()
    {
        $business_id = session()->get('user.business_id');
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;
        $warranties = $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];
        return $warranties;
    }

    /**
     * Parse the weighing barcode.
     *
     * @return array
     */
    private function __parseWeighingBarcode($scale_barcode)
    {
        $business_id = session()->get('user.business_id');

        $scale_setting = session()->get('business.weighing_scale_setting');

        $error_msg = trans("messages.something_went_wrong");

        //Check for prefix.
        if ((strlen($scale_setting['label_prefix']) == 0) || Str::startsWith($scale_barcode, $scale_setting['label_prefix'])) {
            $scale_barcode = substr($scale_barcode, strlen($scale_setting['label_prefix']));

            //Get product sku, trim left side 0
            $sku = ltrim(substr($scale_barcode, 0, $scale_setting['product_sku_length']+1), '0');

            //Get quantity integer
            $qty_int = substr($scale_barcode, $scale_setting['product_sku_length']+1, $scale_setting['qty_length']+1);

            //Get quantity decimal
            $qty_decimal = '0.' . substr($scale_barcode, $scale_setting['product_sku_length'] + $scale_setting['qty_length'] + 2, $scale_setting['qty_length_decimal']+1);

            $qty = (float)$qty_int + (float)$qty_decimal;

            //Find the variation id
            $result = $this->productUtil->filterProduct($business_id, $sku, null, false, null, [], ['sub_sku'], false, 'exact')->first();

            if (!empty($result)) {
                return ['variation_id' => $result->variation_id,
                        'qty' => $qty,
                        'success' => true
                    ];
            } else {
                $error_msg = trans("lang_v1.sku_not_match", ['sku' => $sku]);
            }
        } else {
            $error_msg = trans("lang_v1.prefix_did_not_match");
        }

        return [
                'success' => false,
                'msg' => $error_msg
            ];
    }

    public function getFeaturedProducts($id)
    {
        $location = BusinessLocation::findOrFail($id);
        $featured_products = $location->getFeaturedProducts();

        if (!empty($featured_products)) {
            return view('sale_pos.partials.featured_products')->with(compact('featured_products'));
        } else {
            return '';
        }
    }

    public function getCustomerAddress($customer_id){
        $customer_address = $this->productUtil->getCustomerAddress($customer_id);
        return response()->json($customer_address);
    }

    public function calculateCustomerTax(Request $request){
        $customer_id = $request->customer_id;
        $products = $request->product_ids;
        $tax = $this->productUtil->getTaxData($customer_id,$products);
        return response()->json($tax);
    }

    public function getProductTax(Request $request){
        return $this->productUtil->getProductTax($request->product_id, $request->variation_id, $request->customer_id, 0, $request->selling_price);
    }

    public function duplicate_invoice_items($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $transaction = null;
        $invoice_no = null;
        $transaction = Transaction::where('business_id', $business_id)
                                ->where('type', 'sell')
                                // ->select('invoice_no')
                                ->findorfail($id);
        if($transaction)
        {
            $invoice_no = $transaction->id;

            $location_id = $transaction->location_id;
            $business_location = BusinessLocation::find($location_id);
            $payment_types = $this->productUtil->payment_types($business_location, true);
            $location_printer_type = $business_location->receipt_printer_type;

            return $sell_details = TransactionSellLine::
                    join(
                        'products AS p',
                        'transaction_sell_lines.product_id',
                        '=',
                        'p.id'
                    )
                    // ->leftjoin('tax_rates', 'p.sub_category_id', '=', 'tax_rates.sub_category')
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
                    ->join(
                        'transactions AS t',
                        't.id',
                        '=',
                        'transaction_sell_lines.transaction_id'
                    )
                    ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'vld.variation_id')
                            ->where('vld.location_id', '=', $location_id);
                    })
                    ->leftjoin('units', 'units.id', '=', 'p.unit_id')
                    ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                    ->leftjoin('categories as sub_cat', 'p.sub_category_id', '=', 'sub_cat.id')
                    ->where('transaction_sell_lines.transaction_id', $transaction->id)
                    ->select(
                        't.id as transaction_id',
                        'p.id as product_id',
                        'variations.id as variation_id',
                        'transaction_sell_lines.id as transaction_sell_lines_id',
                        DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                        't.location_id',
                        'transaction_sell_lines.parent_sell_line_id',
                        'categories.name as catName',
                        DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
                    )
                    ->get();
        }
    }

    public function sell($id, $business_id)
    {
        $business_details = $this->businessUtil->getDetails($business_id);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->with(['price_group', 'types_of_service'])
                            ->findorfail($id);

        $location_id = $transaction->location_id;
        $business_location = BusinessLocation::find($location_id);
        $payment_types = $this->productUtil->payment_types($business_location, true);
        $location_printer_type = $business_location->receipt_printer_type;
        $sell_details = TransactionSellLine::
                        join(
                            'products AS p',
                            'transaction_sell_lines.product_id',
                            '=',
                            'p.id'
                        )
                        // ->leftjoin('tax_rates', 'p.sub_category_id', '=', 'tax_rates.sub_category')
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
                        ->join(
                            'transactions AS t',
                            't.id',
                            '=',
                            'transaction_sell_lines.transaction_id'
                        )
                        ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                            $join->on('variations.id', '=', 'vld.variation_id')
                                ->where('vld.location_id', '=', $location_id);
                        })
                        ->leftjoin('units', 'units.id', '=', 'p.unit_id')
                        ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                        ->leftjoin('categories as sub_cat', 'p.sub_category_id', '=', 'sub_cat.id')
                        ->where('transaction_sell_lines.transaction_id', $id)
                        ->with(['warranties'])
                        ->select(
                            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                            'p.id as product_id',
                            'p.enable_stock',
                            'p.qty_box',
                            'p.name as product_actual_name',
                            'p.type as product_type',
                            'pv.name as product_variation_name',
                            'pv.is_dummy as is_dummy',
                            'variations.name as variation_name',
                            'variations.sub_sku',
                            'variations.default_purchase_price',
                            'variations.dpp_inc_tax',
                            't.contact_id',
                            'p.barcode_type',
                            'p.category_id',
                            // 'tax_rates.taxvalue',
                            // 'tax_rates.tax',
                            // 'tax_rates.tax_percent',
                            // 'tax_rates.city_tax_value',
                            'p.enable_sr_no',
                            'variations.id as variation_id',
                            'variations.default_sell_price as original_sell_price',
                            'units.short_name as unit',
                            'units.allow_decimal as unit_allow_decimal',
                            'transaction_sell_lines.tax_id as tax_id',
                            'transaction_sell_lines.item_tax as item_tax',
                            'transaction_sell_lines.unit_price as default_sell_price',
                            'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                            'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                            'transaction_sell_lines.id as transaction_sell_lines_id',
                            'transaction_sell_lines.id',
                            'transaction_sell_lines.quantity as quantity_ordered',
                            'transaction_sell_lines.sell_line_note as sell_line_note',
                            'transaction_sell_lines.box_no as box_no',
                            'transaction_sell_lines.parent_sell_line_id',
                            'transaction_sell_lines.lot_no_line_id',
                            'transaction_sell_lines.line_discount_type',
                            'transaction_sell_lines.line_discount_amount',
                            'transaction_sell_lines.res_service_staff_id',
                            'units.id as unit_id',
                            'transaction_sell_lines.sub_unit_id',
                            'transaction_sell_lines.pos_line_tax_id',
                            'transaction_sell_lines.city_tax_id',
                            'transaction_sell_lines.pos_line_tax_amount',
                            'categories.name as catName',
                            'sub_cat.name as subCatName',
                            DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
                        )
                        ->get();
        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                $product_deatails = ProductVariation::where('product_id', $sell_details[$key]->product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();
                $sell_details[$key]['cost'] = $product_deatails->variations[0]->dpp_inc_tax;
                //If modifier or combo sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'modifier')
                            ->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }

                    //Get details of combo items
                    if ($sell_details[$key]->product_type == 'combo') {
                        $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'combo')
                            ->get()
                            ->toArray();
                        if (!empty($sell_line_combos)) {
                            $sell_details[$key]->combo_products = $sell_line_combos;
                        }

                        //calculate quantity available if combo product
                        $combo_variations = [];
                        foreach ($sell_line_combos as $combo_line) {
                            $combo_variations[] = [
                                'variation_id' => $combo_line['variation_id'],
                                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                                'unit_id' => null
                            ];
                        }
                        $sell_details[$key]->qty_available =
                        $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

                        if ($transaction->status == 'final') {
                            $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
                        }

                        $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                    }
                }
                //calculate tax details
                $sell_details[$key]['tax'] = $this->productUtil->calculateRuleTax($value->pos_line_tax_id,$value->city_tax_id, $value->dpp_inc_tax, $value->default_sell_price,$value->contact_id, $sell_details[$key]->product_id);
            }
        }

        return $sell_details;
    }

    public function del_invoice_pdf_log($id,$reason="")
    {
        ini_set("pcre.backtrack_limit", "5000000");

        $set_upload_dir_path = 'delinvoices';

        $upload_pdf_path = config('constants.mpdf_temp_path').'/'.$set_upload_dir_path;

        if(!is_dir($upload_pdf_path))
        {
            mkdir($upload_pdf_path, 0777, true);
        }

        if(is_dir($upload_pdf_path))
        {
            $transaction = Transaction::where('id', $id)->with(['sell_lines','payment_lines','business', 'location'])->first();

            if (!empty($transaction)) {

                $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

                $business_id = request()->session()->get('user.business_id');
                $user_del_id = request()->session()->get('user.id');
                $transaction = Transaction::where('business_id', $business_id)
                                       ->findorfail($id);
                $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'delete_html_content_pdf',false, true, $invoice_layout_id);
                $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
                $pdf_html= view('sale_pos.partials.delete_export_pdf_invoice')->with(compact('receipt', 'title'))->render();
                $for_pdf = true;
                $html = $pdf_html;
                //print_r($html); exit;
                $mpdf = new \Mpdf\Mpdf();
                $mpdf->WriteHTML($html);
                $transaction_invoice_no = str_replace('/', '-', $transaction->invoice_no);
                $file = $upload_pdf_path.'/'.$transaction_invoice_no.'.pdf';
                $mpdf->Output($file, 'F');

                if(file_exists($file))
                {
                    $file_tobe_saved = file_get_contents($file);

                    $sell_lines_data = "";
                    $payment_lines_data = "";

                    if(!empty($transaction->sell_lines))
                    {
                        $sell_lines_data = json_encode($transaction->sell_lines);
                    }

                    if(!empty($transaction->payment_lines))
                    {
                        $payment_lines_data = json_encode($transaction->payment_lines);
                    }

                    $transaction_line_data = json_encode($transaction);


                    $delinvoicelog = Delinvoicelog::create([
                    'transaction_id' => $transaction->id,
                    'description' => 'deleted',
                    'business_id' => $transaction->business_id,
                    'location_id' => $transaction->location_id,
                    'contact_id' => $transaction->contact_id,
                    'invoice_no' => $transaction->invoice_no,
                    'type' => $transaction->type,
                    'status' => $transaction->status,
                    'payment_status' => $transaction->payment_status,
                    'transaction_date' => $transaction->transaction_date,
                    'transaction_line' => $transaction_line_data,
                    'sell_lines' => $sell_lines_data,
                    'payment_lines' => $payment_lines_data,
                    'deleted_by' => $user_del_id,
                    'invoice_pdf' => $file_tobe_saved,
                    'reason' => $reason
                    ]);

                    if($delinvoicelog){
                        unlink($file);
                    }
                }
                //exit;
            }
        }
    }
    public function download_deleted_invoice($id)
    {
        $delinvoicelog = Delinvoicelog::find($id);
        if($delinvoicelog)
        {
            $filedownload = $delinvoicelog->invoice_pdf;
            return response($filedownload)
                    ->header('Content-Type', 'PDF')
                    ->header('Content-Disposition', 'attachment; filename='.$delinvoicelog->invoice_no.'.pdf');
        }
    }
    public function sendEmailforInvoice($id)
    {
        try {
            $transaction =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*','contacts.email as email','contacts.notify_email as notify_email')
                ->where('transactions.id', $id)->first();

            if(!empty($transaction->notify_email)){
                $transaction->email = strtoupper($transaction->notify_email);
            }

            $invoice_print = request()->get('invoice_print');
            $invoice_show_type = request()->get('invoice_show_type');
            if (isset($invoice_print))
            {
                if(isset($invoice_show_type) && $invoice_show_type == "packing_slip")
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'print',$invoice_show_type);
                }
                else
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'print');
                }
            }
            else
            {
                if(isset($invoice_show_type) && $invoice_show_type == "packing_slip")
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'',$invoice_show_type);
                }
                else
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email);
                }
            }
            if(!empty($send_email))
            {
                $output = ['success' => true, 'message' => __('lang_v1.notification_sent_successfully')];
            }
            else
            {
                $output = ['success' => false,
                            'message' => __('messages.something_went_wrong')
                        ];
            }

            $user_id = request()->session()->get('user.id');

            $this->transactionUtil->Delinvoicelog('send_email',$user_id,$transaction->id,null);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                            'message' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }

    public function sendEmailforInvoiceWithPdf($id,Request $request)
    {
        try {
            $transaction =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*','contacts.email as email','contacts.notify_email as notify_email')
                ->where('transactions.id', $id)->first();

            if(!empty($transaction->notify_email)){
                $transaction->email = strtoupper($transaction->notify_email);
            }

            // if(!empty($request->pdf_b64)){
            //     $pdf_b64 = $request->pdf_b64;
            //     $bin = base64_decode($pdf_b64, true);
            //     if (strpos($bin, '%PDF') !== 0) {
            //         // invalid pdf - send mail without pdf
            //     }
            //     else{
            //         $file_dir = base_path('public/email_attached_invoices').date('/Y_m_d');
            //         if(!FacadesFile::exists($file_dir)){
            //             FacadesFile::makeDirectory($file_dir);
            //         }
            //         if(FacadesFile::exists($file_dir)){
            //             $file_name = $file_dir."/".$transaction->id.".pdf";
            //             if(file_put_contents($file_name, $bin) != FALSE){
            //                 $transaction->pdf_invoice = $file_name;
            //             }
            //         }
            //     }
            // }

            $invoice_print = request()->get('invoice_print');
            $invoice_show_type = request()->get('invoice_show_type');
            if (isset($invoice_print))
            {
                if(isset($invoice_show_type) && $invoice_show_type == "packing_slip")
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'print',$invoice_show_type);
                }
                else
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'print');
                }
            }
            else
            {
                if(isset($invoice_show_type) && $invoice_show_type == "packing_slip")
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'',$invoice_show_type);
                }
                else
                {
                    $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email);
                }
            }
            if(!empty($send_email))
            {
                $output = ['success' => true, 'message' => __('lang_v1.notification_sent_successfully')];
            }
            else
            {
                $output = ['success' => false,
                            'message' => __('messages.something_went_wrong')
                        ];
            }

            $user_id = request()->session()->get('user.id');

            $this->transactionUtil->Delinvoicelog('send_email',$user_id,$transaction->id,null);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                            'message' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }
 public function sendEmailforInvoiceWithPdf1($id,Request $request)
    {
        try {
            $transaction =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*','contacts.email as email','contacts.notify_email as notify_email')
                ->where('transactions.id', $id)->first();

            if(!empty($transaction->notify_email)){
                $transaction->email = strtoupper($transaction->notify_email);
            }

            if(!empty($request->pdf_b64)){
                $pdf_b64 = $request->pdf_b64;
                $bin = base64_decode($pdf_b64, true);
                if (strpos($bin, '%PDF') !== 0) {
                    // invalid pdf - send mail without pdf
                }
                else{
                    $file_dir = base_path('public/email_attached_invoices').date('/Y_m_d');
                    if(!FacadesFile::exists($file_dir)){
                        FacadesFile::makeDirectory($file_dir);
                    }
                    if(FacadesFile::exists($file_dir)){
                        $file_name = $file_dir."/".$transaction->id.".pdf";
                        if(file_put_contents($file_name, $bin) != FALSE){
                            $transaction->pdf_invoice = $file_name;
                        }
                    }
                }
            }

            $invoice_print = request()->get('invoice_print');
            $invoice_show_type = request()->get('invoice_show_type');
            // if (isset($invoice_print))
            // {
            //     if(isset($invoice_show_type) && $invoice_show_type == "packing_slip")
            //     {
            //         $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'print',$invoice_show_type);
            //     }
            //     else
            //     {
            //         $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'print');
            //     }
            // }
            // else
            // {
            //     if(isset($invoice_show_type) && $invoice_show_type == "packing_slip")
            //     {
            //         $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email,'',$invoice_show_type);
            //     }
            //     else
            //     {
            //         $send_email = $this->notificationUtil->SendEmailNotification($transaction->business_id, 'new_sale', $transaction, $transaction->email);
            //     }
            // }
            // if(!empty($invoice_show_type))
            // {
                $output = ['success' => true, 'message' => __('lang_v1.notification_sent_successfully'),$invoice_show_type];
            // }
            // else
            // {
            //     $output = ['success' => false,
            //                 'message' => __('messages.something_went_wrong')
            //             ];
            // }

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                            'message' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }

    //all Invoice at a time start
    public function allInvoiceDisplay()
    {
        $business_id = request()->session()->get('user.business_id');

       /* $tran =Transaction::where('transactions.status', 'final')
        ->where('transactions.type', 'sell')
        ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name')
        ->get();*/


        // $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $categoriesdata = Category::forDropdown($business_id, 'product');
        $categories = Category::where('business_id',$business_id)->where('category_type','product')->get();

        $tax_rates = TaxRate::join('categories as cat', 'cat.id', '=', 'tax_rates.category')->select(DB::raw("CONCAT(tax_rates.name,' - ',cat.name) AS name"),'tax_rates.id')->orderBy('name')->pluck('name','id');


        return view('sale_pos.all_invoice_display')->with(compact('tax_rates','categories','categoriesdata'));
    }

    public function search_all_Invoice(Request $request)
    {

     if($request->ajax())
     {
        $ranges = $request->post('ranges');
        $query = $request->get('query');
        $rates = $request->get('rates');
        $state = $request->get('state');
        $product_category = $request->get('product_category');

        $start = $request->post('start');
        $limit = $request->post('limit');
        $set_upload_dir_path = $request->get('upload_dir_path');
        $unit_total_amount = $request->post('unit_total_amount');
        $unit_discount_amount = $request->post('unit_discount_amount');
        $unit_total_paid = $request->post('unit_total_paid');
        $unit_sell_due = $request->post('unit_sell_due');
        $total_row = $request->post('total_row');

        if($ranges!='' && $start!='' && $limit!='')
        {
            $output = '';
            $top_output_header = '';
            $shipping_statuses = $this->transactionUtil->shipping_statuses();
            $business_id = request()->session()->get('user.business_id');

            if(isset($ranges))
            {
                $myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $from = str_replace(' ', '', $fr1);
                $to = str_replace(' ', '', $to2);
                $new_fromdate = date('Y-m-d',strtotime($from));
                $new_todate = date('Y-m-d',strtotime('+23 hour +59 minutes +59 seconds',strtotime($to)));
            }
            if(empty($total_row))
            {
                $data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*','contacts.first_name','contacts.contact_id as accountid','contacts.state as state')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final');
            // return $data->get();
                if(isset($ranges))
                {
                    $data->whereDate('transactions.transaction_date','>=',$new_fromdate)->whereDate('transactions.transaction_date','<=',$new_todate);
                }

                if(isset($query)){
                 $data->where('invoice_no', 'like', '%'.$query.'%');
                }
                if (!empty($rates)) {
                    $data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
                    // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
                    $tax_rates_id = $request->get('rates');

                    $data->where(function ($query) use($tax_rates_id) {
                        $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                              ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
                    });
                }
                if(!empty($state)){
                    $data->where('contacts.state', 'like', '%'.$state.'%');
                }

                $data = $data->groupBy('transactions.id')
                ->orderBy('transactions.transaction_date','desc')->get();
                //echo "sdf"; exit;

                    if(empty($product_category))
                    {
                       $filtered_sells_for_count = $data;
                    }
                    else
                    {
                        $filtered_sells_for_count = [];
                        foreach($data as $row){

                            $transaction_id = $row->id;
                            $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                            if(count($transaction_sells)>0)
                            {
                                foreach($product_category as $category){
                                    foreach($transaction_sells as $sell){
                                        if(isset($sell->product_single->category_id))
                                        {
                                            $mathch_cat_id = $sell->product_single->category_id;
                                            if($mathch_cat_id == $category){
                                                array_push($filtered_sells_for_count,$row);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }
                //$total_row = count($total_result);
                //echo $total_row; exit;
                $total_row = count($filtered_sells_for_count);
            }
            $result = 'next';
            if( ($start + $limit ) >= $total_row)
            {
                    $result = 'finished';
            }

        }

      if($total_row > 0)
      {

        /*if(isset($set_upload_dir_path) && $set_upload_dir_path!="")
            {
                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;
            }
            else
            {
                //$set_upload_dir_path = 'invoices_'.$ranges;
                $set_upload_dir_path = time();

                $upload_pdf_path = config('constants.mpdf_temp_path').'/invoices/'.$set_upload_dir_path;

                if(is_dir($upload_pdf_path))
                {
                    //rmdir($upload_pdf_path);
                    //Storage::deleteDirectory($upload_pdf_path);
                    if(File::deleteDirectory($upload_pdf_path))
                    {
                        mkdir($upload_pdf_path, 0777, true);
                    }
                }
                else
                {
                    mkdir($upload_pdf_path, 0777, true);
                }
                if (!is_dir($upload_pdf_path))
                {
                        if(mkdir($upload_pdf_path, 0777, true))
                        {
                            //$output .= '<button class="btn btn-sm btn-primary"><a href="download_zip/'.$set_upload_dir_path.'" target="_blank" style="color:white; ">Download Invoices</a></button> <br>';
                        }
                }
            }*/

        $print_data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->select('transactions.*','contacts.first_name','contacts.contact_id as accountid','contacts.state as state' , DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as total_paid'))
        ->where('transactions.business_id', $business_id)
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final')
        ->whereDate('transactions.transaction_date','>=',$new_fromdate)
        ->whereDate('transactions.transaction_date','<=',$new_todate);
        //->whereMonth('transactions.transaction_date','=',$month)
        //->whereYear('transactions.transaction_date','=',$year)
        if(isset($query)){
             $print_data->where('invoice_no', 'like', '%'.$query.'%');
        }
        if (!empty($rates)) {
            $print_data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
            // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
            $tax_rates_id = $request->get('rates');

            $print_data->where(function ($query) use($tax_rates_id) {
                $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                      ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
            });
        }
        if(!empty($state)){
            $print_data->where('contacts.state', 'like', '%'.$state.'%');
        }
        if($result=='finished')
        {
            $print_data = $print_data->groupBy('transactions.id')
                ->orderBy('transactions.transaction_date','desc')->offset($start)->limit($total_row)->get();
        }
        else
        {
            $print_data = $print_data->groupBy('transactions.id')
                ->orderBy('transactions.transaction_date','desc')->offset($start)->limit($limit)->get();
        }
        //echo "sdf"; exit;

            if(empty($product_category))
            {
               $filtered_sells = $print_data;
            }
            else
            {
                $filtered_sells = [];
                foreach($print_data as $row){

                    $transaction_id = $row->id;
                    $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                    if(count($transaction_sells)>0)
                    {
                        foreach($product_category as $category){
                            foreach($transaction_sells as $sell){
                                if(isset($sell->product_single->category_id))
                                {
                                    $mathch_cat_id = $sell->product_single->category_id;
                                    if($mathch_cat_id == $category){
                                        array_push($filtered_sells,$row);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

            }

            if($start==0)
            {
                $output .='<table class="table table-bordered table-striped" id="invoice_all_table">
                        <thead>
                        <tr>
                            <th>SrNo.</th>
                            <th>Date</th>
                            <th>Invoice No.</th>
                            <th>Customer Name</th>
                            <th>Total Amount</th>
                            <th>Discount Amount</th>
                            <th>Total Paid</th>
                            <th>Sell Due</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Shipping Status</th>
                        </tr>
                        </thead><tbody>';
            }
            $i=$start+1;
            foreach($filtered_sells as $row)
            {
                $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
                if (!empty($discount) && $row->discount_type == 'percentage')
                {
                    $discount = $row->total_before_tax * ($discount / 100);
                }

                $final_total =  $row->final_total + $discount;
                $sell_due =  $row->final_total - $row->total_paid;


                // For Order status Start

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

                    $status = '<span class="label ' . $bg_color .'">' . $order_status . '</span>';

                // For Order Status End

                // For Payment Status Start

                $payment_status = $row->payment_status;

                if (in_array($payment_status, ['partial', 'due']) && !empty($row->pay_term_number) && !empty($row->pay_term_type)) {
                    $transaction_date = \Carbon::parse($row->transaction_date);
                    $due_date = $row->pay_term_type == 'days' ? $transaction_date->addDays($row->pay_term_number) : $transaction_date->addMonths($row->pay_term_number);
                    $now = \Carbon::now();
                    if ($now->gt($due_date)) {
                        $payment_status = $payment_status == 'due' ? 'overdue' : 'partial-overdue';
                    }
                }

                switch ($payment_status) {
                    case 'paid':
                        $payment_status_bg_color = 'bg-light-green';
                        break;
                    case 'due':
                        $payment_status_bg_color = 'bg-yellow';
                        break;
                    case 'partial':
                        $payment_status_bg_color = 'bg-aqua';
                        break;
                    case 'overdue':
                        $payment_status_bg_color = 'bg-red';
                        break;
                    case 'partial-overdue':
                        $payment_status_bg_color = 'bg-red';
                        break;
                    default:
                        $payment_status_bg_color = '';
                        break;
                }

                // For Payment Status End

                // For Shpping Status Start

                $shipping_status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                $shipping_status = !empty($row->shipping_status) ? '<span class="label ' . $shipping_status_color .'">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

                // For Shpping Status End

                $output .= '<tr>';
                $output .= '<td>'.$i.'</td>';
                $output .= '<td>'.$this->transactionUtil->format_date($row->transaction_date,true).'</td>';
                $output .= '<td>'.'<a href="open_invoice/'.$row->id.'" target="_blank">'.$row->invoice_no.'</a>'.'</td>';
                $output .= '<td>'.$row->first_name.' ('.$row->accountid.')'.'</td>';

                $output .= '<td>'.'$ '.$this->productUtil->num_f($final_total).'</td>';

                $output .= '<td>'.'$ '.$this->productUtil->num_f($discount).'</td>';

                $output .= '<td>'.'$ '.$this->productUtil->num_f($row->total_paid).'</td>';

                $output .= '<td>'.'$ '.$this->productUtil->num_f($sell_due).'</td>';
                $output .= '<td>'.$status.'</td>';
                $output .= '<td>'.'<span class="label '.$payment_status_bg_color.'">'.__('lang_v1.'.$payment_status).'</span>'.'</td>';
                $output .= '<td>'.$shipping_status.'</td>';
                $output .= '</tr>';

                $unit_total_amount = $unit_total_amount + $final_total;
                $unit_discount_amount = $unit_discount_amount + $discount;
                $unit_total_paid = $unit_total_paid + $row->total_paid;
                $unit_sell_due = $unit_sell_due + $sell_due;
                //$this->export_invoice_pdf($row->id,$upload_pdf_path);
                $i++;
            }



            if($result=='finished')
            {
                //$output .='</tbody>';
                $output .= '
                    <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="3"><strong>Total:</strong></td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_total_amount).'</td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_discount_amount).'</td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_total_paid).'</td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_sell_due).'</td>
                    </tr>
                ';
                //$output .='</table>';
                $top_output_header .= '
                    <table class="table table-bordered table-striped" id="invoice_top_table"><thead>
                    <tr class="bg-gray font-17 footer-total text-center">
                        <td colspan="3"><strong>Total:</strong></td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_total_amount).'</td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_discount_amount).'</td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_total_paid).'</td>
                        <td colspan="2">'.'$ '.$this->productUtil->num_f($unit_sell_due).'</td>
                    </tr></thead></table>
                ';

            }

      }
      else
      {
        $output = '
            <td align="center" colspan="5">No Data Found<td>';
      }

      $data = array(
       'start' => $start,
       'limit' => $limit,
       'upload_dir_path' => '',
       'result' => $result,
       'table_data'  => $output,
       'total_data'  => $total_row,
       'unit_total_amount' => $unit_total_amount,
       'unit_discount_amount' => $unit_discount_amount,
       'unit_total_paid' => $unit_total_paid,
       'unit_sell_due' => $unit_sell_due,
       'top_output_header' => $top_output_header,
      );
      echo json_encode($data);
     }
    }

    public function export_all_Invoice(Request $request)
    {
        $ranges = $request->post('ranges');
        $query = $request->get('query');
        $rates = $request->get('rates');
        $state = $request->get('state');
        $product_category = $request->get('product_category');

        if($ranges!='')
        {
            $shipping_statuses = $this->transactionUtil->shipping_statuses();
            $business_id = request()->session()->get('user.business_id');

            if(isset($ranges))
            {
                $myArray = explode('-', $ranges);
                // return $myArray;
                $fr1 = $myArray[0];
                $to2 = $myArray[1];
                $from = str_replace(' ', '', $fr1);
                $to = str_replace(' ', '', $to2);
                $new_fromdate = date('Y-m-d',strtotime($from));
                $new_todate = date('Y-m-d',strtotime('+23 hour +59 minutes +59 seconds',strtotime($to)));
            }
            try {
                $print_data =Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*','contacts.first_name','contacts.contact_id as accountid','contacts.state as state' , DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                                TP.transaction_id=transactions.id) as total_paid'))
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->whereDate('transactions.transaction_date','>=',$new_fromdate)
                ->whereDate('transactions.transaction_date','<=',$new_todate);

                if(isset($query)){
                $print_data->where('invoice_no', 'like', '%'.$query.'%');
                }
                if (!empty($rates)) {
                    $print_data->Join('transaction_sell_lines as tsl', 'transactions.id', '=', 'tsl.transaction_id');
                    // $sells->where('tsl.pos_line_tax_id', request()->input('tax_rates'));
                    $tax_rates_id = $request->get('rates');

                    $print_data->where(function ($query) use($tax_rates_id) {
                        $query->where('tsl.pos_line_tax_id', '=', $tax_rates_id)
                              ->orWhere('tsl.city_tax_id', '=', $tax_rates_id);
                    });
                }
                if(!empty($state)){
                    $print_data->where('contacts.state', 'like', '%'.$state.'%');
                }

                $print_data = $print_data->groupBy('transactions.id')
                        ->orderBy('transactions.transaction_date','desc')->get();

                if(empty($product_category))
                {
                   $filtered_sells = $print_data;
                }
                else
                {
                    $filtered_sells = [];
                    foreach($print_data as $row){

                        $transaction_id = $row->id;
                        $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                        if(count($transaction_sells)>0)
                        {
                            foreach($product_category as $category){
                                foreach($transaction_sells as $sell){
                                    if(isset($sell->product_single->category_id))
                                    {
                                        $mathch_cat_id = $sell->product_single->category_id;
                                        if($mathch_cat_id == $category){
                                            array_push($filtered_sells,$row);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if(count($filtered_sells)>0)
                {
                    $export_data = [];
                    $unit_total_amount = 0;
                    $unit_discount_amount = 0;
                    $unit_total_paid = 0;
                    $unit_sell_due = 0;
                    $i=1;
                    foreach($filtered_sells as $row)
                    {
                        $temp = [];
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
                        if (!empty($discount) && $row->discount_type == 'percentage')
                        {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        $final_total =  $row->final_total + $discount;
                        $sell_due =  $row->final_total - $row->total_paid;


                        // For Order status Start

                        if($row->order_picking_status == '2' && $row->order_packing_status == '0'){
                                $order_status = 'Picking Completed';
                            }elseif($row->order_packing_status == '2' && $row->order_picking_status == '2'){
                                $order_status = 'Packing Completed';
                            }elseif($row->order_packing_status == '1' && $row->order_picking_status == '2'){
                                $order_status = 'Packing Started';
                            }elseif($row->order_picking_status == '0'){
                                $order_status = 'Received';
                            }elseif($row->order_picking_status == '1'){
                                $order_status = 'Picking Started';
                            }elseif($row->order_picking_status == '3'){
                                $order_status = 'Cancel';
                            }else{
                                $order_status = 'Received';
                            }

                        // For Order Status End

                        // For Payment Status Start

                        $payment_status = $row->payment_status;

                        if (in_array($payment_status, ['partial', 'due']) && !empty($row->pay_term_number) && !empty($row->pay_term_type)) {
                            $transaction_date = \Carbon::parse($row->transaction_date);
                            $due_date = $row->pay_term_type == 'days' ? $transaction_date->addDays($row->pay_term_number) : $transaction_date->addMonths($row->pay_term_number);
                            $now = \Carbon::now();
                            if ($now->gt($due_date)) {
                                $payment_status = $payment_status == 'due' ? 'overdue' : 'partial-overdue';
                            }
                        }

                        // For Payment Status End

                        // For Shpping Status Start
                        $shipping_status = !empty($row->shipping_status) ? $shipping_statuses[$row->shipping_status] : '';
                        // For Shpping Status End

                        $temp['SrNo.'] = $i;
                        $temp['Date'] = $this->transactionUtil->format_date($row->transaction_date,true);
                        $temp['Invoice No.'] = $row->invoice_no;
                        $temp['Customer Name'] = $row->first_name.' ('.$row->accountid.')';
                        $temp['Total Amount'] = $this->productUtil->num_f($final_total);
                        $temp['Discount Amount'] = $this->productUtil->num_f($discount);
                        $temp['Total Paid'] = $this->productUtil->num_f($row->total_paid);
                        $temp['Sell Due'] = $this->productUtil->num_f($sell_due);
                        $temp['Order Status'] = $order_status;
                        $temp['Payment Status'] = $payment_status;
                        $temp['Shipping Status'] = $shipping_status;

                        $unit_total_amount = $unit_total_amount + $final_total;
                        $unit_discount_amount = $unit_discount_amount + $discount;
                        $unit_total_paid = $unit_total_paid + $row->total_paid;
                        $unit_sell_due = $unit_sell_due + $sell_due;
                        //$this->export_invoice_pdf($row->id,$upload_pdf_path);
                        $export_data[] = $temp;
                        $i++;
                    }
                    $temp_last = [] ;
                    $temp_last['SrNo.'] = '';
                    $temp_last['Date'] = '';
                    $temp_last['Invoice No.'] = '';
                    $temp_last['Customer Name'] = 'Total:';
                    $temp_last['Total Amount'] = $this->productUtil->num_f($unit_total_amount);
                    $temp_last['Discount Amount'] = $this->productUtil->num_f($unit_discount_amount);
                    $temp_last['Total Paid'] = $this->productUtil->num_f($unit_total_paid);
                    $temp_last['Sell Due'] = $this->productUtil->num_f($unit_sell_due);
                    $temp_last['Order Status'] = '';
                    $temp_last['Payment Status'] = '';
                    $temp_last['Shipping Status'] ='';

                    $export_data[] =$temp_last;
                    /*echo "<pre>";
                    print_r($export_data);
                    exit;*/

                    if (ob_get_contents()) ob_end_clean();
                    ob_start();
                    return collect($export_data)->downloadExcel(
                        'sales_data.xlsx',
                        null,
                        true
                    );
                }
                else
                {
                    $message = 'No Data Found!!';
                }
            } catch (\Exception $e) {

                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $message = trans("messages.something_went_wrong");

                return redirect()->action('SellPosController@allInvoiceDisplay')->with('success', $message);
            }

        }
        else
        {
           $message = 'No Data Found!!';
        }

        return redirect()->action('SellPosController@allInvoiceDisplay')->with('success', $message);
    }
    //all Invoice at a time end

    // echecks generator start
    public function eCheckGenerate()
    {
        return view('sale_pos.partials.echeck_generate');
    }

    public function eCheckGeneratePdf(Request $request)
    {
        $receipts = array();
        $amt_string = "";
        $receipts['name'] = $request->input('name');
        $receipts['address1'] = $request->input('address1');
        $receipts['address2'] = $request->input('address2');
        $receipts['address3'] = $request->input('address3');
        $receipts['edate'] = $request->input('edate');
        $receipts['rname'] = $request->input('rname');
        $receipts['amount'] = $request->input('amount');
        $receipts['final_amount'] = '';
        if($receipts['amount'] > 0) {

                $dollars = intval($receipts['amount']);
                $cents = round(($receipts['amount'] - $dollars)*100);
                //$dollars_str = TextualNumber::GetText($dollars);
                $dollars_str = $this->numToWords($dollars);

                $amt_string = ucfirst($dollars_str);
                if( $cents > 0 ) {
                    $amt_string .= " and ".$cents."/100";
                } else {
                    $amt_string .= " and 00/100";
                }
                //$amt_string .= "***";

                $receipts['final_amount'] = '**'.$this->productUtil->num_f($receipts['amount']);

        }
        $receipts['amtstring'] = $amt_string;
        $receipts['bname'] = $request->input('bname');
        $receipts['baddress1'] = $request->input('baddress1');
        $receipts['baddress2'] = $request->input('baddress2');
        $receipts['baddress3'] = $request->input('baddress3');

        $receipts['pname'] = $request->input('pname');
        $receipts['paddress1'] = $request->input('paddress1');
        $receipts['paddress2'] = $request->input('paddress2');
        $receipts['paddress3'] = $request->input('paddress3');

        $receipts['anumber'] = $request->input('anumber');

        $receipts['memo'] = $request->input('memo');
        $receipts['tcode'] = $request->input('tcode');
        $receipts['rno'] = $request->input('rno');
        $receipts['cno'] = $request->input('cno');

        if(!empty($receipts['cno']))
        {
            $str_length = 9;
            $num = $receipts['cno'];
            $str = substr("000000000{$num}", -$str_length);
            $receipts['cnobottom'] = $str;
            //echo $receipts['cno']; exit;
        }
        else
        {
            $receipts['cno'] ='';
            $receipts['cnobottom'] = "";
        }

        if($request->input('display')=='preview')
        {
            return view('sale_pos.partials.echeck_generate_preview')
               ->with(compact('receipts'));
        }
        else
        {
            $pdf_html= view('sale_pos.receipts.echeck_pdf', compact('receipts'))->render();
            $for_pdf = true;
            $html = $pdf_html;
            //print_r($html); exit;
            $mpdf = new \Mpdf\Mpdf();
            $this->add_custom_fonts_to_mpdf($mpdf);
            $mpdf->WriteHTML($html);
            //$file = $upload_pdf_path . '/' . time() .'_'.$id. '_invoice.pdf';
            $file = 'echecks.pdf';
            $mpdf->Output($file, 'D');
            //return view('sale_pos.partials.echeck_generate');
        }
    }

    public function add_custom_fonts_to_mpdf($mpdf, $fonts_list='')
    {
        $fontdata = [
            'micr-emicren-regular' => [
                'R' => 'micr-encoding.regular.ttf',
                'B' => 'micr-encoding.regular.ttf',
            ],
        ];

        foreach ($fontdata as $f => $fs) {
            // add to fontdata array
            $mpdf->fontdata[$f] = $fs;

            // add to available fonts array
            foreach (['R', 'B', 'I', 'BI'] as $style) {
                if (isset($fs[$style]) && $fs[$style]) {
                    // warning: no suffix for regular style! hours wasted: 2
                    $mpdf->available_unifonts[] = $f . trim($style, 'R');
                }
            }

        }

        $mpdf->default_available_fonts = $mpdf->available_unifonts;
    }

    public function numToWords($number)
    {
      if (($number < 0) || ($number > 999999999)){
        return "$number out of script range";
      }

      $lakhs = floor($number / 100000);  /* lakhs (giga) */
      $number -= $lakhs * 100000;

      $thousands = floor($number / 1000);     /* Thousands (kilo) */
      $number -= $thousands * 1000;
      $hundreds = floor($number / 100);      /* Hundreds (hecto) */
      $number -= $hundreds * 100;
      $tens = floor($number / 10);       /* Tens (deca) */
      $ones = $number % 10;               /* Ones */
      $res = "";

      //echo "<hr>".$lakhs;


     if ($lakhs){

        $res .= $this->numToWords($lakhs) ;
        $res.=($lakhs>10)?" hundred":" hundred";
      }

      if($thousands){
        $res .= (empty($res) ? "" : " ") .
        $this->numToWords($thousands) . " Thousand";
      }

      if ($hundreds){
        $res .= (empty($res) ? "" : " ") .
        $this->numToWords($hundreds) . " Hundred";
      }

      $arr_ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
      "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
      "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen",
      "Nineteen");
      $arr_tens = array("", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty",
        "Seventy", "Eighty", "Ninety");

        if ($tens || $ones){
            if (!empty($res)){
                $res .= " and ";
            }

            if ($tens < 2){
                $res .= $arr_ones[$tens * 10 + $ones];
            }
            else{
                $res .= $arr_tens[$tens];
                if ($ones){
                    $res .= "-" . $arr_ones[$ones];
                }
            }
        }

        if (empty($res)){
            $res = "Zero";
        }

        return $res;
    }
    // echecks generator end

    public function getInvoicePDF($id, $receipt_type = 'open_invoice'){
        $file_dir = base_path('public/email_attached_invoices').date('/Y_m_d');
        if(!FacadesFile::exists($file_dir)){
            FacadesFile::makeDirectory($file_dir);
        }
        if(FacadesFile::exists($file_dir)){
            ini_set("pcre.backtrack_limit", "5000000");
            $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();
            if (!empty($transaction)) {
                $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

                $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content_pdf',false, true, $invoice_layout_id);
                $title = $transaction->business->name . ' | ' . $transaction->invoice_no;

                $pdf_html= view('sale_pos.receipts_new.show_pdf_view')->with(compact('receipt', 'title'))->render();
                // $pdf_html = $this->open_invoice($id)->render();

                print_r($pdf_html);
                $mpdf = new \Mpdf\Mpdf();
                $mpdf->WriteHTML($pdf_html);
                $file = $file_dir . '/' . $transaction->invoice_no. '.pdf';
                $mpdf->Output($file, 'F');
                return $file;
            }
        }
        return "";
    }

    public function getStockHistory(Request $request){
        // dd($request);
        // $request->get('product_category');
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            try {
                $product_id = $request->get('product_id');
                $variation_id = $request->get('variation_id');

                $stock_history = $this->productUtil->getVariationStockHistory($business_id, $variation_id, $location_id = 4, null);
                $data1 = '';
                $data1 .= "<div class='row'>
               				<div  class='col-sm-2'>".__('lang_v1.type')."</div>
            				<div  class='col-sm-1'>".__('lang_v1.quantity_change')."</div>
            				<div  class='col-sm-1'>".__('lang_v1.new_quantity')."</div>
            				<div  class='col-sm-3'>".__('lang_v1.date')."</div>
            				<div  class='col-sm-5'>".__('purchase.ref_no')."</div>
            		</div>";
            	$i = 1;
            	$data='';
        		foreach($stock_history as $history){
            	    if($i <= 10){
            	                $data .= '<div class="col-sm-12" style="padding:0;"><hr class= "" style= "border :1px solid;margin-top:0px !important;margin-bottom:0px !important;margin-right:10px !important;margin-left:10px !important;"></div>';
                        	    $data .= '<div  class="form-group col-sm-2">';

            					if($history['type_label'] == 'Sell Return'){
	                                    $data .= 'Credit Memo';
        		                }else{
            						    $data .= $history["type_label"];
        		                }
        						$data .='</div>';

        					   $data .='<div  class="form-group col-sm-1">';
        					    if($history['quantity_change'] > 0 ) {
                                    $data .='+<span class="display_currency" data-is_quantity="true">'.round($history["quantity_change"],2).'</span> ';
                                }else{
                                    $data .='<span class="display_currency" data-is_quantity="true">'.round($history["quantity_change"],2).'</span> ';
    		                    }
                                $data .='</div>';

            					$data .='<div  class="form-group col-sm-1">';
                                   $data .=' <span class="display_currency" data-is_quantity="true">'.round($history["stock"],2).'</span>';
                                $data .='</div>';

        					    $data .='<div  class="form-group col-sm-3">';
        					        $data .=  date('m/d/Y h:i',strtotime($history["date"]));
                                $data .='</div>';


            					if($history['type'] == 'sell'){
            						if(empty($history['supplier_business_name'])){
        					            $data .='<div  class="form-group col-sm-5">';
                                            $data .='<a href="#" data-href="action(\'SellController@show\', $history["transaction_id"])" class="btn-modal" data-container=".view_modal">'.$history["ref_no"].':'. $history["company_name"].':<br> '.$history["dba_name"].'</a>';
                                        $data .='</div>';
            						}else{
        					            $data .='<div  class="form-group col-sm-5">';
                                            $data .='<a href="#" data-href="action(\'SellController@show\', $history["transaction_id"])" class="btn-modal" data-container=".view_modal">'.$history["ref_no"].' :'. $history["supplier_business_name"].' :<br>'.$history["dba_name"].'</a>';
                                        $data .='</div>';
        		                    }
            					}elseif ($history['type'] == 'purchase'){
            						if(empty($history['supplier_business_name'])){
        					            $data .='<div  class="form-group col-sm-5">';
                                            $data .='<a href="#" data-href="action(\'PurchaseController@show\', $history["transaction_id"])" class="btn-modal" data-container=".view_modal">'. $history["ref_no"].' :'.$history["company_name"] .':'. $history["dba_name"].'</a>';
                                        $data .='</div>';
            						}else{
        					            $data .='<div  class="form-group col-sm-5">';
                                            $data .='<a href="#" data-href="action(\'PurchaseController@show\', $history["transaction_id"])" class="btn-modal" data-container=".view_modal">'.$history["ref_no"].': '.$history["supplier_business_name"].' :<br> '.$history["dba_name"].'</a>';
                                        $data .='</div>';
            						}
                                }
                				elseif($history['type'] == 'purchase_transfer'){
            						if(empty($history['supplier_business_name'])){
                					    $data .='<div  class="form-group col-sm-5">';
                                            $data .='<a href="#" data-href="action(\'SellReturnController@show\', $history["transaction_id"])" class="btn-modal" data-container=".view_modal">'.$history["ref_no"].' :'. $history["company_name"] .' :'.$history["dba_name"].'</a>';
                                        $data .='</div>';
            						}else{
                					    $data .='<div  class="form-group col-sm-5">';
                                           $data .='<a href="#" data-href="action(\'SellReturnController@show\', $history["transaction_id"])" class="btn-modal" data-container=".view_modal">'.$history["ref_no"].': '.$history["supplier_business_name"].' :<br>'.$history["dba_name"].'</a>';
                                        $data .='</div>';
                                    }
            					}elseif($history['type'] == 'stock_adjustment'){

    					            $data .='<div  class="form-group col-sm-5">';
                                        $data .='<a href="#" data-href="{{action(\'StockAdjustmentController@show\', $history["transaction_id"])" class="btn-modal" data-container=".view_modal">'.$history["ref_no"].'</a>';
                                    $data .='</div>';

            					}elseif($history['type'] == 'opening_stock'){
    					            $data .='<div  class="form-group col-sm-5">-</div>';


            					}elseif($history['type'] == 'sell_transfer'){
            						if(empty($history['supplier_business_name'])){
        					            $data .='<div  class="form-group col-sm-5">';
                                            $data .='<a href="#"  class="btn-modal" data-container=".view_modal">'.$history["ref_no"].' :'. $history["company_name"].' :<br>'. $history["dba_name"].'</a>';
                                        $data .='</div>';
            						}else{
                					    $data .='<div  class="form-group col-sm-5">';
                                            $data .='<a href="#"  class="btn-modal" data-container=".view_modal">'.$history["ref_no"].': '.$history["supplier_business_name"].' :<br>'. $history["dba_name"].'</a>';
                                        $data .='</div>';
            						}
            					}elseif($history['type'] == 'purchase_transfer'){
            						if(empty($history['supplier_business_name'])){
                					    $data .='<div  class="form-group col-sm-5">';
                					        $data .=' <a href="#"   class="btn-modal" data-container=".view_modal">'.$history["ref_no"].' :' .$history["company_name"].':'. $history["dba_name"].'</a>';
                                        $data .='</div>';

            						}else{
                					    $data .='<div  class="form-group col-sm-5">';
                                           $data .=' <a href="#"  class="btn-modal" data-container=".view_modal">'.$history["ref_no"].' : '.$history["supplier_business_name"].' :<br>'. $history["dba_name"] .'</a>';
                                        $data .='</div>';
            						}
            					}elseif($history['type'] == 'production_purchase'){
            						if(empty($history['supplier_business_name'])){
                					    $data .='<div  class="form-group col-sm-5">';
                					        $data .='  <a href="#"  class="btn-modal" data-container=".view_modal">'.$history["ref_no"] .': '.$history["company_name"] .':<br> '.$history["dba_name"].'</a>';
                                        $data .='</div>';

            						}else{
                					    $data .='<div  class="form-group col-sm-5">';
                					        $data .=' <a href="#"  class="btn-modal" data-container=".view_modal">'.$history["ref_no"].' : '.$history["supplier_business_name"].' :<br>'.$history["dba_name"].'</a>';
                                        $data .='</div>';

            						}
            					}elseif($history['type'] == 'purchase_return'){
            						if(empty($history['supplier_business_name'])){
                					    $data .='<div  class="form-group col-sm-5">';
                					        $data .='<a href="#" data-href="action(\'PurchaseReturnController@show\', $history["transaction_id"])"  class="btn-modal" data-container=".view_modal">'.$history["ref_no"] .' :'. $history["company_name"].' :<br>'. $history["dba_name"].'</a>';
                                        $data .='</div>';

            						}else{
                					    $data .='<div  class="form-group col-sm-5">';
                					        $data .=' <a href="#" data-href="action(\'PurchaseReturnController@show\', $history["transaction_id"])"  class="btn-modal" data-container=".view_modal">'.$history["ref_no"] .' : '.$history["supplier_business_name"] .':<br> '.$history["dba_name"].'</a>';
                                        $data .='</div>';

            						}
            					}
            	        }

    			     $i = $i+1;
        		}
                $output =$data1 . $data ; //'<h1>'.$request->get('product_id').'</h1>'; //['success' => 1, 'receipt' => '$receipt'];

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => 0,
                        'msg' => trans("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }


    public function packing_slip_blank_dl($invoice_token)
    {
        $transaction = Transaction::where('invoice_token', $invoice_token)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            $receipt['html_content2'] = $receipt['html_content7'];

            $contact = Contact::find($transaction->contact_id);
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;


            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;
            return view('sale_pos.partials.show_blank_packingslip')
                    ->with(compact('receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    public function packing_slip_blank_forward($invoice_token)
    {
        $transaction = Transaction::where('invoice_token', $invoice_token)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $client = new Client();
            try{
                $response = $client->post('https://app-pdf-bbtl.azurewebsites.net/generate-pdf', [
                    'form_params' => [
                        'fwdurl' => config('business-info.erp_url') . '/ps/' . $invoice_token
                    ]
                ]);
                if($response->getStatusCode() == 200){
                    $pdfContent = $response->getBody()->getContents();
                    $headers = [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="document.pdf"',
                    ];
                    // dd($pdfContent);
                    return response($pdfContent, 200, $headers);
                }
            }
            catch(Exception $e){
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            }

        }
        return redirect()->away("https://www.google.com");
        // die(__("messages.something_went_wrong"));
    }


    public function packing_slip_blank_without_price_dl($invoice_token)
    {
        $transaction = Transaction::where('invoice_token', $invoice_token)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            $receipt['html_content2'] = $receipt['html_content9'];

            $contact = Contact::find($transaction->contact_id);
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;

            $wa_number = '';
            if(!empty($contact)){
                $wa_number = preg_replace('/[^0-9]/', '', $contact->whatsapp ?? '');
                if(strlen($wa_number) == 10){
                    $wa_number = "1".$wa_number;
                }
            }

            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;
            return view('sale_pos.partials.show_blank_packingslip_without_price')
                    ->with(compact('wa_number','receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    public function packing_slip_blank_without_price_send($invoice_token)
    {
        $transaction = Transaction::where('invoice_token', $invoice_token)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $client = new Client();
            try{
                $response = $client->post('https://app-pdf-bbtl.azurewebsites.net/generate-pdf', [
                    'form_params' => [
                        'fwdurl' => config('business-info.erp_url') . '/pswpl/' . $invoice_token
                    ]
                ]);
                if($response->getStatusCode() == 200){
                    $pdfContent = $response->getBody()->getContents();
                    $headers = [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="document.pdf"',
                    ];
                    // dd($pdfContent);
                    return response($pdfContent, 200, $headers);
                }
            }
            catch(Exception $e){
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            }

        }
        return redirect()->away("https://www.google.com");
        // die(__("messages.something_went_wrong"));
    }


    public function packing_slip_blank_without_price_send11($invoice_token)
    {
        $transaction = Transaction::where('invoice_token', $invoice_token)->with(['business', 'location'])->first();

        if (!empty($transaction)) {
            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : null;

            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser', 'html_content',false, true, $invoice_layout_id);
            $receipt['html_content2'] = $receipt['html_content9'];

            $contact = Contact::find($transaction->contact_id);
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;

            $url =route('show_pswp', ['token' => $transaction->invoice_token]);

            // $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            $tid = $transaction->id;
             $wa_number = '';
            if(!empty($contact)){
                $wa_number = preg_replace('/[^0-9]/', '', $contact->whatsapp ?? '');
                if(strlen($wa_number) == 10){
                    $wa_number = "1".$wa_number;
                }
            }
            return view('sale_pos.partials.show_blank_packingslip_without_price')
                    ->with(compact('wa_number','receipt', 'title','tid'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }
    // START the new invoice repair module
    public static function theInvoiceRepairer($specifiedDate = null)
    {
        if (is_null($specifiedDate)) {
            $specifiedDate = date('Y-m-d'); // Default to current date if not specified
        }

        $duplicateInvoices = DB::table('transactions')
            ->select('invoice_no', DB::raw('COUNT(*) as dup_count'))
            ->whereDate('transaction_date', $specifiedDate)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->groupBy('invoice_no')
            ->having('dup_count', '>', 1)
            ->get();

        $report = [];

        foreach ($duplicateInvoices as $dupInvoice) {
            $transactions = Transaction::where('invoice_no', $dupInvoice->invoice_no)
                ->whereDate('transaction_date', $specifiedDate)
                ->orderBy('transaction_date')
                ->get();

            $dupNo = 1;
            foreach ($transactions as $transaction) {
                if ($dupNo > 1) {
                    $scheme = InvoiceScheme::where('business_id', $transaction->business_id)
                        ->first();

                    $newInvoiceNo = self::generateNewInvoiceNo($scheme);
                    $oldInvoiceNo = $transaction->invoice_no;

                    $transaction->invoice_no = $newInvoiceNo;
                    $transaction->save();

                    $scheme->invoice_count += 1;
                    $scheme->save();

                    $report[] = [
                        'transaction_id' => $transaction->id,
                        'transaction_date' => $transaction->transaction_date,
                        'old_invoice_no' => $oldInvoiceNo,
                        'new_invoice_no' => $newInvoiceNo,
                        'specified_date' => $specifiedDate,
                    ];
                }
                $dupNo++;
            }
        }

         if (!empty($report)) {
        $recipients = ['kashyap.s@brainbean.in', 'jay@brainbean.in'];
        foreach ($recipients as $email) {
            Notification::route('mail', $email)
                ->notify(new InvoiceRepairReportNotification($report));
        }
    }
        return $report;
    }

    private static function generateNewInvoiceNo($scheme)
    {
        $prefix = $scheme->scheme_type == 'blank' ? $scheme->prefix : date('Y') . '-';
        $count = $scheme->start_number + $scheme->invoice_count;
        $count = str_pad($count, $scheme->total_digits, '0', STR_PAD_LEFT);

        return $prefix . $count;
    }

    public function repairInvoices(Request $request)
    {
        $specifiedDate = $request->input('date');
        $report = self::theInvoiceRepairer($specifiedDate);
        return response()->json($report);
    }
    // the new invoice repair module END

}