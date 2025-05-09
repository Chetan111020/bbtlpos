<?php

namespace App\Http\Controllers;

use App\AccountTransaction;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\Helpers\ProductHelper;
use App\Product;
use App\ProductTemp;
use App\PurchaseLine;
use App\PoLine;
use App\TaxRate;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;

use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;

use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

use App\Utils\Util;
use App\VariationLocationDetails;

use Schema;
use Arr;
use DateTime;
use File;
use App\PurchaseActivityLog;

class PurchaseOrderController extends Controller
{

    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $util;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil, Util $util)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
        $this->util = $util;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
    }
    
    public function bulkDelete(Request $request){
        $po_ids = $request->po_ids ?? [];
        $po_success = [];
        $po_fail = [];
        foreach($po_ids as $po){
            // dd($po);
            $temp_res = $this->destroy($po);
            if($temp_res['success']){
                $po_success[] = $po;
            }
            else{
                $po_fail[] = $po;
            }
        }
        $msg = "";
        $msg2 = "";
        if(count($po_success) >= 0){
            $msg = count($po_success)." Purchase orders Deleted";
        }
        if(count($po_fail) >= 0){
            $msg2 = count($po_fail)." Purchase Order Failed";
        }

        $output = [
            'success' => true,
            'msg' => __($msg),
            'msg2' => __($msg2)
        ];
        return $output;
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchases = $this->transactionUtil->getListPurchasesOrder($business_id);

        //   return $purchases->get();

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchases->where('transactions.location_id', request()->location_id);
            }
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $purchases->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $purchases->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            if (!empty(request()->status)) {
                $purchases->where('transactions.status', request()->status);
            }
            
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchases->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions.created_by', request()->session()->get('user.id'));
            }

            return Datatables::of($purchases)
                ->addColumn('bulk_option',function($row){
                    return  '<input type="checkbox" name="po_ids[]" class="row-select bulk_hook" value="' . $row->id .'">' ;
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                                __("messages.actions") .
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" data-href="' . action('PurchaseOrderController@show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.view")) {
                        // $html .='<li><a href="#" class="print-invoice" data-href="'. route('purchase.printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i> '. __("messages.print") .'</a></li>';
                       
                        $html .= '<li><a href="#" class="print-invoice" data-href="' . action('PurchaseOrderController@printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>'. __("messages.print") .'</a></li>';
                    }
                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseOrderController@edit', [$row->id]) . '"><i class="fas fa-edit"></i>' . __("messages.edit") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.delete")) {
                        $html .= '<li><a href="' . action('PurchaseOrderController@destroy', [$row->id]) . '" class="delete-purchase"><i class="fas fa-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    $html .= '<li><a href="' . action('LabelsController@show') . '?purchase_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fas fa-barcode"></i>' . __('barcode.labels') . '</a></li>';

                    if (auth()->user()->can("purchase.view") && !empty($row->document)) {
                        $document_name = !empty(explode("_", $row->document, 2)[1]) ? explode("_", $row->document, 2)[1] : $row->document ;
                        $html .= '<li><a href="' . url('uploads/documents/' . $row->document) .'" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __("purchase.download_document") . '</a></li>';
                        if (isFileImage($document_name)) {
                            $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) .'" class="view_uploaded_document"><i class="fas fa-picture-o" aria-hidden="true"></i>' . __("lang_v1.view_document") . '</a></li>';
                        }
                    }
                                        
                    if (auth()->user()->can("purchase.create")) {
                        $html .= '<li class="divider"></li>';
                        if ($row->payment_status != 'paid') {
                            $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a></li>';
                        }
                        $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) .
                        '" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i>' . __("purchase.view_payments") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a target="_blank" href="' . action('ContactController@show', [$row->contact_id]) . '"><i class="fas fa-eye" aria-hidden="true"></i>View Profile</a></li>';
                    }
                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseReturnController@add', [$row->id]) .
                        '"><i class="fas fa-undo" aria-hidden="true" ></i>' . __("lang_v1.purchase_return") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) {
                        $html .= '<li><a href="#" data-purchase_id="' . $row->id .
                        '" data-status="' . $row->status . '" class="update_status"><i class="fas fa-edit" aria-hidden="true" ></i>' . __("lang_v1.update_status") . '</a></li>';
                    }

                    if ($row->status == 'ordered') {
                        $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "new_order"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.new_order_notification") . '</a></li>';
                    } elseif ($row->status == 'received') {
                        $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "items_received"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_received_notification") . '</a></li>';
                    } elseif ($row->status == 'pending') {
                        $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "items_pending"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_pending_notification") . '</a></li>';
                    }

                    $html .=  '</ul></div>';
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {
                    return !empty($row->return_exists) ? $row->ref_no . ' <small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned') .'"><i class="fas fa-undo"></i></small>' : $row->ref_no;
                })
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'status',
                    '<a href="#" @if(auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) class="update_status no-print" data-purchase_id="{{$id}}" data-status="{{$status}}" @endif><span class="label @transaction_status($status) status-label" data-status-name="{{__(\'lang_v1.\' . $status)}}" data-orig-value="{{$status}}">{{__(\'lang_v1.\' . $status)}}
                        </span></a>'
                )
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id, 'for_purchase' => true]);
                    }
                )
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("purchase.view")) {
                            return  action('PurchaseOrderController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['bulk_option','final_total', 'action', 'payment_status', 'status', 'ref_no'])
                ->make(true);

                // return $row->final_total;
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();
        
        return view('purchase_order.index')
            ->with(compact('business_locations', 'suppliers', 'orderStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
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

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true);

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        $users = User::select('id','first_name','last_name')->get();

        $product_ids = request()->products ?? "";
        return view('purchase_order.create')
            ->with(compact('product_ids','taxes', 'users', 'orderStatuses', 'business_locations', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'bl_attributes'));
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

        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('PurchaseOrderController@index'));
            }

            $transaction_data = $request->only([ 
                'ref_no', 
                'status', 
                'contact_id', 
                'transaction_date', 
                'received_date', 
                'total_before_tax', 
                'location_id',
                'discount_type', 
                'discount_amount','tax_id', 'tax_amount', 'shipping_details', 
                'shipping_charges', 'final_total', 
                'additional_notes', 
                'shipping_date', 'shipping_carier','tracking_id', 'eta',
                'exchange_rate', 'pay_term_number', 
                'pay_term_type'
            ]);

            $exchange_rate = $transaction_data['exchange_rate'];

            //Reverse exchange rate and save it.
            //$transaction_data['exchange_rate'] = $transaction_data['exchange_rate'];

            //TODO: Check for "Undefined index: total_before_tax" issue
            //Adding temporary fix by validating
            $request->validate([
                'status' => 'required',
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                // 'document' => 'file|max:'. (config('constants.document_size_limit') / 1000000)
            ]);

            $user_id = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            //Update business exchange rate.
            Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            //unformat input values
            $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details)*$exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($transaction_data['discount_type'] == 'fixed') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details)*$exchange_rate;
            } elseif ($transaction_data['discount_type'] == 'percentage') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details);
            } else {
                $transaction_data['discount_amount'] = 0;
            }

            // $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount'], $currency_details)*$exchange_rate;
            $transaction_data['shipping_charges'] = $this->productUtil->num_uf($transaction_data['shipping_charges'], $currency_details)*$exchange_rate;
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details)*$exchange_rate;

            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['status'] = $request->input('status');
            $transaction_data['type'] = 'purchase_order';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
            // $transaction_data['received_date'] = $this->productUtil->uf_date($transaction_data['received_date'], true);

            //upload document
            $transaction_data['extra_document'] = $this->util->uploadMultipleFile($request, 'document', 'documents');
            // $transaction_data['document'] = $this->util->uploadFile($request, 'document', 'documents');
            
            DB::beginTransaction();

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            //Generate reference number
            if (empty($transaction_data['ref_no'])) {
                // $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
                    $last_ref_no = DB::table('transactions')->select('ref_no')->where('type', 'purchase_order')->latest()->first();
                    if(isset($last_ref_no))
                    {
                        $old_ref_no = substr($last_ref_no->ref_no, 2);
                    }
                    else
                    {
                        $old_ref_no = 0;
                    }
                    $transaction_data['ref_no'] = 'PO'.str_pad(($old_ref_no + 1), 2, "0", STR_PAD_LEFT);
            }

            $transaction = Transaction::create($transaction_data);

            //added by developer1
            $transaction = Transaction::latest('id')->first(); 
            $transaction_id = $transaction->id;
            $this->transactionUtil->PurchaseActivityLog('added',$user_id,$transaction_id,"","purchase_order");
            //added by developer1
            
            $purchase_lines = [];
            $purchases = $request->input('purchases');

            if(!empty($purchases)){
                $this->productUtil->createOrUpdatePOLines($transaction, $purchases, $currency_details, $enable_product_editing);
            }
            
            //product temp saved
            if(isset($_REQUEST['ProductTemp']) && count($_REQUEST['ProductTemp']) > 0){
                 $delete_ids = DB::table('product_temps')->where('po_id', $transaction->id)->delete();
                foreach ($_REQUEST['ProductTemp'] as $value1) {
                    $product_temp = new ProductTemp();
                    $product_temp->po_id = $transaction->id;
                    $product_temp->business_id = $business_id;
                    $product_temp->name = $value1['name'];
                    $product_temp->on_hand = "0";
                    $product_temp->purchase_qty = $value1['quantity'];
                    $product_temp->previous_cost = $value1['pp_without_discount'];
                    $product_temp->total ="0.00";
                    $product_temp->total_sold = "0.00";
                    $product_temp->save();
                }
            }else{
                $delete_ids = DB::table('product_temps')->where('po_id', $transaction->id)->delete();
            }

            //Add Purchase payments
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $request->input('payment'));

            //update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);
            
            DB::commit();
            
            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_add_success')
                        ];
            return redirect()->action('PurchaseOrderController@edit', [$transaction->id])->with('status', $output);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('purchase-order')->with('status', $output);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (!auth()->user()->can('purchase.view')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $purchase = Transaction::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(
                                    'contact',
                                    'po_lines',
                                    'po_lines.product',
                                    'po_lines.product.unit',
                                    'po_lines.variations',
                                    'po_lines.variations.product_variation',
                                    'po_lines.sub_unit',
                                    'location',
                                    'payment_lines',
                                    'tax'
                                )
                                ->firstOrFail();
        
        // return $purchase;

        foreach ($purchase->po_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->po_lines[$key] = $formated_purchase_line;
            }
        }
        
        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }
        // return $purchase;

        $temp_products = ProductTemp::where('business_id', $business_id)->where('po_id', $id)->get();

        //added by developer 1
        $PurchaseActivityLog = PurchaseActivityLog::join('users','users.id','=','purchase_activity_log.user_id')
              ->where('transaction_id',$id)
              ->where('type','purchase_order')
              ->whereIn('description', ['added', 'edited'])
                ->select('purchase_activity_log.created_at as datetime',
                    'purchase_activity_log.message as message',
                    'purchase_activity_log.description as description',
                    'users.first_name as first_name'
                )
                ->get();

        return view('purchase_order.show')
                ->with(compact('taxes', 'purchase', 'payment_methods', 'purchase_taxes', 'temp_products','PurchaseActivityLog'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('PurchaseOrderController@index'));
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

        $business = Business::find($business_id);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
        $purchase = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(
                        'contact',
                        'po_lines',
                        'po_lines.product',
                        'po_lines.product.unit',
                        //'po_lines.product.unit.sub_units',
                        'po_lines.variations',
                        'po_lines.variations.product_variation',
                        'location',
                        'po_lines.sub_unit'
                    )
                    ->first();
                    
        $temp_products = ProductTemp::where('business_id', $business_id)
                    ->where('po_id', $id)->get();

        foreach ($purchase->po_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->po_lines[$key] = $formated_purchase_line;
            }

            $query = Variation::where('product_id', $purchase->po_lines[$key]->product_id)->with(['sell_lines']);
            $query->withCount(['sell_lines AS total_sold' => function ($query)
                    {
                        $query->select(DB::raw("SUM(quantity) as totalsold"));
                    }
                ]);

            $purchase->po_lines[$key]['total_sold'] = $query->first()->total_sold ? $query->first()->total_sold : 0;;
        }

        // return $purchase;
        
        $orderStatuses = $this->productUtil->orderStatuses();

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
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

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        return view('purchase_order.edit')
            ->with(compact(
                'taxes',
                'purchase',
                'orderStatuses',
                'business_locations',
                'business',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'bl_attributes',
                'temp_products'
            ));
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

        // return $request->all();

        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // return $request->all();
            $transaction = Transaction::findOrFail($id);
            
            //Validate document size
            $request->validate([
                'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
            ]);

            $before_status = $transaction->status;
            $business_id = request()->session()->get('user.business_id');
            
            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $update_data = $request->only([ 
                'ref_no', 
                'status', 
                'contact_id',
                'transaction_date', 
                'received_date', 
                'total_before_tax',
                'discount_type', 
                'discount_amount', 
                'tax_id',
                'tax_amount', 
                'shipping_details',
                'shipping_charges', 
                'final_total',
                'additional_notes', 
                'shipping_date', 'shipping_carier','tracking_id', 'eta',
                'exchange_rate', 'pay_term_number', 'pay_term_type']);

            $exchange_rate = $update_data['exchange_rate'];

            //Reverse exchage rate and save
            //$update_data['exchange_rate'] = number_format(1 / $update_data['exchange_rate'], 2);

            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);
            // $update_data['received_date'] = $this->productUtil->uf_date($update_data['received_date'], true);

            //unformat input values
            $update_data['total_before_tax'] = $this->productUtil->num_uf($update_data['total_before_tax'], $currency_details) * $exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($update_data['discount_type'] == 'fixed') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif ($update_data['discount_type'] == 'percentage') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details);
            } else {
                $update_data['discount_amount'] = 0;
            }
            

            $update_data['tax_amount'] = $this->productUtil->num_uf($update_data['tax_amount'], $currency_details) * $exchange_rate;
            $update_data['shipping_charges'] = $this->productUtil->num_uf($update_data['shipping_charges'], $currency_details) * $exchange_rate;
            $update_data['final_total'] = $this->productUtil->num_uf($update_data['final_total'], $currency_details) * $exchange_rate;
            //unformat input values ends

            //upload document
            $document_name = $this->util->uploadMultipleFileUpdate($request, 'document', 'documents');
            // $document_name = $this->util->uploadFile($request, 'document', 'documents');
            if ($document_name && count($document_name) > 0) {
                $data_two = [];
                $count = 0;
                $old_file = [];
                if($transaction['extra_document'] && count($transaction['extra_document']) > 0)
                {
                   $count = count($transaction['extra_document']);  
                   $old_file = $transaction->extra_document;
                }
                foreach ($document_name as $key => $value) {
                   $key = $key + $count; 
                    $data_two[] = $value;
                }
                $document_name = array_merge($old_file, $data_two);
                $update_data['extra_document'] = $document_name;
            }
            $user_id = $request->session()->get('user.id');
            DB::beginTransaction();

            //added by developer1
            $this->transactionUtil->UpdatePurchaseOrderLog($update_data, $business_id, $user_id,'',$id,$request->input('purchases'));
            //added by developer1

            //update transaction
            $transaction->update($update_data);
            // return $transaction;
            //Update transaction payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id);

            $purchases = $request->input('purchases');

             //product temp saved
            if(isset($_REQUEST['ProductTemp']) && count($_REQUEST['ProductTemp']) > 0){
                 $delete_ids = DB::table('product_temps')->where('po_id', $transaction->id)->delete();
                foreach ($_REQUEST['ProductTemp'] as $value1) {
                    $product_temp = new ProductTemp();
                    $product_temp->po_id = $transaction->id;
                    $product_temp->business_id = $business_id;
                    $product_temp->name = $value1['name'];
                    $product_temp->on_hand = "0";
                    $product_temp->purchase_qty = $value1['quantity'];
                    $product_temp->previous_cost = $value1['pp_without_discount'];
                    $product_temp->total ="0.00";
                    $product_temp->total_sold = "0.00";
                    $product_temp->save();
                }
            }else{
                 $delete_ids = DB::table('product_temps')->where('po_id', $transaction->id)->delete();
            }

           if(!empty($purchases)){
               $enable_product_editing = 2;
               $delete_purchase_lines = $this->productUtil->createOrUpdatePOLines($transaction, $purchases, $currency_details, $enable_product_editing, $before_status); 
               
               //Update mapping of purchase & Sell.
                 $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);
            }

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            DB::commit();
          // return  $transaction = Transaction::findOrFail($id);
            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_update_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return back()->with('status', $output);
        }
        return redirect()->action('PurchaseOrderController@edit', [$id])->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $reason = !empty(request()->get('reason'))?request()->get('reason'):"";
                $business_id = request()->session()->get('user.business_id');

                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist')
                    ];
                    return $output;
                }
        
                $transaction = Transaction::where('id', $id)
                                ->where('business_id', $business_id)
                                ->with(['po_lines'])
                                ->first();

                //Check if lot numbers from the purchase is selected in sale
                if (request()->session()->get('business.enable_lot_number') == 1 && $this->transactionUtil->isLotUsed($transaction)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.lot_numbers_are_used_in_sale')
                    ];
                    return $output;
                }
                
                $delete_purchase_lines = $transaction->po_lines;
                DB::beginTransaction();

                $this->DeletePurchaseOrderActivityLog($id,$reason);

                $transaction_status = $transaction->status;
                if ($transaction_status != 'received') {
                    $transaction->delete();
                } else {
                    //Delete purchase lines first
                    $delete_purchase_line_ids = [];
                    foreach ($delete_purchase_lines as $purchase_line) {
                        $delete_purchase_line_ids[] = $purchase_line->id;
                        $this->productUtil->decreaseProductQuantity(
                            $purchase_line->product_id,
                            $purchase_line->variation_id,
                            $transaction->location_id,
                            $purchase_line->quantity
                        );
                    }
                    PoLine::where('transaction_id', $transaction->id)
                                ->whereIn('id', $delete_purchase_line_ids)
                                ->delete();

                    //Update mapping of purchase & Sell.
                    $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($transaction_status, $transaction, $delete_purchase_lines);
                }

                //Delete Transaction
                $transaction->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $id)->delete();

                DB::commit();

                $output = ['success' => true,
                            'msg' => __('lang_v1.purchase_delete_success')
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => $e->getMessage()
                        ];
        }

        return $output;
    }
    
    /**
     * Retrieves supliers list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSuppliers()
    {
        if (request()->ajax()) {
            $term = request()->q;
            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = Contact::where('business_id', $business_id)
                            ->active();

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                $query->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                ->where('uca.user_id', $user_id);
            }
            $suppliers = $query->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term .'%')
                                ->orWhere('supplier_business_name', 'like', '%' . $term .'%')
                                ->orWhere('contacts.contact_id', 'like', '%' . $term .'%');
            })
                        ->select('contacts.id', 'name as text', 'supplier_business_name as business_name', 'contact_id', 'contacts.pay_term_type', 'contacts.pay_term_number', 'contacts.balance')
                        ->onlySuppliers()
                        ->get();
            return json_encode($suppliers);
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductList()
    {
        if (request()->ajax()) {
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sku2', 'like', '%' . $term .'%');
                    $query->orWhere('sku3', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                    $query->orWhere('products.item_code', 'like', '%' . $term .'%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->where('products.not_for_selling', 0)
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    'products.not_for_selling',
                    'products.case_qty',
                    // 'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku',
                    'variations.dpp_inc_tax',
                    'variations.sell_price_inc_tax'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->orderBy('products.name', 'asc')->get();
                
            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['not_for_selling'] = $product->not_for_selling;
                $products_array[$product->product_id]['case_qty'] = !empty($product->case_qty) ? $product->case_qty : 0;
                $products_array[$product->product_id]['is_inactive'] = $product->is_inactive;
                $products_array[$product->product_id]['dpp_inc_tax'] = $product->dpp_inc_tax;
                $products_array[$product->product_id]['sell_price_inc_tax'] = $product->sell_price_inc_tax;
                $products_array[$product->product_id]['variation_id'] = $product->variation_id;
                $products_array[$product->product_id]['variations'][]
                = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku
                        ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    $C = ' | C: $'.  number_format($value['dpp_inc_tax'], 2);
                    $SP = ' | SP: $'.  number_format($value['sell_price_inc_tax'], 2);
                    $OH = ' | OH: '. (integer)VariationLocationDetails::where('variation_id', $value['variation_id'])->value('qty_available') ?? 0;
                    $CQ = ' | CQ: '.  $value['case_qty'];

                    $notSell = '';
                    $active = '';
                    if($value['is_inactive'] === 1)
                    {
                        $active = ' | (Item <span class="label bg-gray">' . __("lang_v1.inactive") .'</span>)';
                    }else{
                        $active = ' | (Item <span class="label bg-gray">Active</span>)';
                    }
                    if($value['not_for_selling'] === 1)
                    {
                        $notSell = ' | <span class="label bg-gray">' . __("lang_v1.not_for_selling") .
                    '</span>';
                    } 

                    if ($no_of_records > 1 && $value['type'] != 'single' && !$only_variations) {
                        $result[] = [ 'id' => $i,
                                    'text' => $value['name'] . ' - ' . $value['sku']. $C . $SP . $OH . $CQ .$active .' | ' .$notSell ,
                                    'variation_id' => 0,
                                    'product_id' => $key,
                                    'not_for_selling' => $value['not_for_selling'],
                                ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text . ' (' . $variation['variation_name'] . ')';
                        }
                        $i++;
                        $result[] = [ 'id' => $i,
                                            'text' => $text . ' - ' . $variation['sub_sku'].  $C. $SP . $OH . $CQ . $active .$notSell ,
                                            'product_id' => $key ,
                                            'variation_id' => $variation['variation_id'],
                                            'not_for_selling' => $value['not_for_selling'],
                                        ];
                    }
                    $i++;
                }
            }
            
            return json_encode($result);
        }
    }

    public function getSupplierProducts()
    {
        if (request()->ajax()) {

            $business_id = request()->session()->get('user.business_id');

              $q = Product::leftJoin(
                    'variations',
                    'products.id',
                    '=',
                    'variations.product_id'
                )
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('transaction_sell_lines as t', 't.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
                ->active()
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    'products.item_code',
                    'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.dpp_inc_tax',
                    'variations.default_sell_price',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku',
                    'vld.qty_available as current_stock',
                    \DB::raw('SUM(t.quantity) as total_sold'),
                );

            if(request()->input('purchase_product')) {
                $q->join('purchase_lines as pl', 'pl.product_id', '=', 'products.id')
                    ->leftJoin('transactions as tr', function ($join) {
                        $join->on('tr.id', '=' , 'pl.transaction_id');
                });
                $q->where('tr.type','=','purchase')->where('tr.contact_id', request()->supplier_id);
            } else {
                $q->where('business_id', $business_id);
                $q->where('supplier_id', request()->supplier_id);
            }



            $start = request()->start_date;
            $end =  request()->end_date;

            // if (!empty($start) && !empty($end)) {
            //     $q->whereDate('t.created_at', '>=', $start)
            //       ->whereDate('t.created_at', '<=', $end);
            // }

            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }

            if(request()->input('alert_stock')){
                $q->where('alert_quantity', '<=', 50);
            }
     
            $products = $q->groupBy('variation_id')->get();

            foreach ($products as $key => $value) {

                $query = Variation::where('product_id', $value->product_id)
                            ->with([
                                'sell_lines' => function ($q) use ($start, $end) {
                                    if (!empty($start) && !empty($end)) {
                                        $q->whereDate('created_at', '>=', $start)
                                            ->whereDate('created_at', '<=', $end);
                                    }
                                },
                            ]);

                $query->withCount(['sell_lines AS total_sold' => function ($query) use ($start, $end)
                    {
                        if (!empty($start) && !empty($end)) {
                            $query->whereDate('created_at', '>=', $start)
                                    ->whereDate('created_at', '<=', $end);
                        }
                        $query->select(DB::raw("SUM(quantity) as totalsold"));
                    }
                ]);

                $products[$key]->total_sold =  $query->first()->total_sold ? $query->first()->total_sold : 0;

            }

            return DataTables::of($products)
                        ->addColumn('item_code', function ($products) {
                            return $products->item_code;
                        })->addColumn('name', function ($products) {
                            // return '<a href="javascript:void(0);" onclick="addToDataTable('.$products->variation_id.')">'.$products->name.'</a>'; 
                            return $products->name;
                        })->addColumn('current_stock', function ($products) {
                            return $products->current_stock;
                        })->addColumn('checkbox', function ($products) {
                            return  '<input type="checkbox" name="product_ids[]" data-variation="' . $products->variation_id .'" class="row-select" value="' . $products->product_id .'">' ;
                        })->addColumn('total_sold', function ($products) {
                            return $products->total_sold;
                        })   
                        ->escapeColumns('name')
                        ->make(true);
        }
    }

    public function get_total_sold()
    {

        if (request()->ajax()) {
            $start = request()->start_date;
            $end =  request()->end_date;

            $query = Variation::where('product_id', request()->product_id)
                        ->with([
                            'sell_lines' => function ($q) use ($start, $end) {
                                if (!empty($start) && !empty($end)) {
                                    $q->whereDate('created_at', '>=', $start)
                                        ->whereDate('created_at', '<=', $end);
                                }
                            },
                        ]);

            $query->withCount(['sell_lines AS total_sold' => function ($query) use ($start, $end)
                {
                    if (!empty($start) && !empty($end)) {
                        $query->whereDate('created_at', '>=', $start)
                                ->whereDate('created_at', '<=', $end);
                    }
                    $query->select(DB::raw("SUM(quantity) as totalsold"));
                }
            ]);

            $totalsold =  $query->first()->total_sold ? $query->first()->total_sold : 0.0000;
             return ['success' => true, 'totalsold' => $totalsold, 'product_id' => request()->product_id];
        }

    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProducts()
    {
        if (request()->ajax()) {
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                    $query->orWhere('products.item_code', 'like', '%' . $term .'%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    'products.item_code',
                    'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.dpp_inc_tax',
                    'variations.default_sell_price',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();
        

            return DataTables::of($products)
            ->addColumn('item_code', function ($products) {
                return $products->item_code;
            })->addColumn('name', function ($products) {
                return '<a href="javascript:void(0);" onclick="addToDataTable('.$products->variation_id.')">'.$products->name.'</a>'; 
                // return $products->name;
            })->addColumn('dpp_inc_tax', function ($products) {
                return $products->dpp_inc_tax;
            })->addColumn('default_sell_price', function ($products) {
                return $products->default_sell_price;
            })->addColumn('sku', function ($products) {
                return $products->sku;
            })
            ->escapeColumns('name')
            ->make(true);
        }
    }
    
    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchaseEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                                    ->with(['unit'])
                                    ->first();
                
                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $start = request()->start_date;
                $end =  request()->end_date;

                $query = Variation::where('product_id', $product_id)
                                ->with([
                                    'product_variation', 
                                    'variation_location_details' => function ($q) use ($location_id) {
                                        $q->where('location_id', $location_id);
                                    },
                                    'sell_lines' => function ($q) use ($start, $end) {
                                        if (!empty($start) && !empty($end)) {
                                            $q->whereDate('created_at', '>=', $start)
                                                ->whereDate('created_at', '<=', $end);
                                        }
                                    },
                                ]);

                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }

                $query->withCount(['sell_lines AS total_sold' => function ($query) use ($start, $end)
                    {
                        if (!empty($start) && !empty($end)) {
                            $query->whereDate('created_at', '>=', $start)
                                    ->whereDate('created_at', '<=', $end);
                        }
                        $query->select(DB::raw("SUM(quantity) as totalsold"));
                    }
                ]);

                $variations =  $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                            ->ExcludeForTaxGroup()
                            ->get();
                $edit = $request->input('edit');

                //get safety stock
                $productSafetyStock = ProductHelper::getSafetyStock($product_id);
                $safeStockQty = $productSafetyStock[0];
                $bufferStockQty = $productSafetyStock[1];
                
                return view('purchase_order.partials.purchase_entry_row')
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'edit',
                        'safeStockQty',
                        'bufferStockQty'
                    ));
            }
        }
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkRefNumber(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $contact_id = $request->input('contact_id');
        $ref_no = $request->input('ref_no');
        $purchase_id = $request->input('purchase_id');

        $count = 0;
        if (!empty($contact_id) && !empty($ref_no)) {
            //check in transactions table
            $query = Transaction::where('business_id', $business_id)
                            ->where('ref_no', $ref_no)
                            ->where('contact_id', $contact_id);
            if (!empty($purchase_id)) {
                $query->where('id', '!=', $purchase_id);
            }
            $count = $query->count();
        }
        if ($count == 0) {
            echo "true";
            exit;
        } else {
            echo "false";
            exit;
        }
    }


    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $taxes = TaxRate::where('business_id', $business_id)
                                ->pluck('name', 'id');
            $purchase = Transaction::where('business_id', $business_id)
                                    ->where('id', $id)
                                    ->with(
                                        'contact',
                                        'po_lines',
                                        'po_lines.product',
                                        'po_lines.variations',
                                        'po_lines.variations.product_variation',
                                        'location',
                                        'payment_lines'
                                    )
                                    ->first();
            $payment_methods = $this->productUtil->payment_types(null, false, $business_id);
            $temp_products = ProductTemp::where('business_id', $business_id)->where('po_id', $id)->get();
            $output = ['success' => 1, 'receipt' => []];
            $output['receipt']['html_content'] = view('purchase_order.partials.show_details', compact('taxes', 'purchase', 'payment_methods', 'temp_products'))->render();
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }

    /**
     * Update purchase status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        if (!auth()->user()->can('purchase.update') && !auth()->user()->can('purchase.update_status')) {
            abort(403, 'Unauthorized action.');
        }
        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($request->input('purchase_id'), $edit_days)) {
            return ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])];
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                                ->where('type', 'purchase_order')
                                ->with(['po_lines'])
                                ->findOrFail($request->input('purchase_id'));

            $before_status = $transaction->status;
            

            $update_data['status'] = $request->input('status');


            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            foreach ($transaction->po_lines as $purchase_line) {
                $this->productUtil->updateProductStock($before_status, $transaction, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity, $purchase_line->quantity, $currency_details);
            }

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, null);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('purchase.purchase_update_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }

        return $output;
    }
    
    //  public function printInvoice(Request $request, $id)
    // {
        
    //     try {
    //         $business_id = request()->session()->get('user.business_id');
    //         $taxes = TaxRate::where('business_id', $business_id)
    //                             ->pluck('name', 'id');
    //         $location_id = 4;
    //         $business_location = BusinessLocation::find($location_id);
    //         $purchase = Transaction::where('business_id', $business_id)
    //                                 ->where('id', $id)
    //                                 ->where('type','purchase_order')
    //                                 ->with(
    //                                     'contact',
    //                                     'po_lines',
    //                                     'po_lines.product',
    //                                     'po_lines.product.unit',
    //                                     'po_lines.variations',
    //                                     'po_lines.variations.product_variation',
    //                                     'po_lines.sub_unit',
    //                                     'location',
    //                                     'payment_lines',
    //                                     'business'
    //                                 )
    //                                 ->firstOrFail();
  
    //         // echo "<pre>";
    //         // print_r($purchase);
    //         // die;
    //         $payment_methods = $this->productUtil->payment_types(null, false, $business_id);

    //         $output = ['success' => 1, 'receipt' => []];
    //         $output['receipt']['html_content'] = view('purchase_order.receipt', compact('taxes', 'purchase', 'payment_methods','business_location'))->render();
    //                     // echo "<pre>";
    //         // echo "<pre>";
    //         // print_r($output['receipt']['html_content']);
    //         // die;
    //     } catch (\Exception $e) {
    //         \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
    //         $output = ['success' => 0,
    //                         'msg' => __('messages.something_went_wrong')
    //                     ];
    //     }

    //     return $output;
    // }

    // added by developer1 
    public function DeletePurchaseOrderActivityLog($id,$reason="")
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'po_lines','payment_lines'
            )
            ->select('transactions.*',DB::raw('(SELECT SUM(TP.amount) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as amount_paid'))
            ->first();
        if(!empty($purchase)) 
        {
            $purchase_lines_data = "";
            if(!empty($purchase->po_lines))
            {
                $purchase_lines_data = json_encode($purchase->po_lines);
            }
            if(!empty($purchase->payment_lines))
            {
                $payment_lines_data = json_encode($purchase->payment_lines);
            }
            PurchaseActivityLog::create([
                        'user_id' => $user_id,
                        'transaction_id' => $purchase->id,
                        'description' => 'deleted',
                        'business_id' => $purchase->business_id,
                        'location_id' => $purchase->location_id,
                        'contact_id' => $purchase->contact_id,
                        'invoice_no' => $purchase->ref_no,
                        'type' => $purchase->type,
                        'status' => $purchase->status,
                        'payment_status' => $purchase->payment_status,
                        'final_total' => $purchase->final_total,
                        'amount_paid' => $purchase->amount_paid,
                        'pay_term_number' => $purchase->pay_term_number,
                        'pay_term_type' => $purchase->pay_term_type,
                        'discount_type' => $purchase->discount_type,
                        'discount_amount' => $purchase->discount_amount,
                        'tax_id' => $purchase->tax_id,
                        'tax' => $purchase->tax_amount,
                        'shipping_details' => $purchase->shipping_details,
                        'shipping_charges' => $purchase->shipping_charges,
                        'shipping_date' => $purchase->shipping_date,
                        'shipping_carrier' => $purchase->shipping_carrier,
                        'tracking_id' => $purchase->tracking_id,
                        'eta' => $purchase->eta,
                        'box_qty' => $purchase->box_qty,
                        'reason' => $reason,
                        'purchase_lines' => $purchase_lines_data,
                        'payment_lines' => $payment_lines_data,
                        'additional_notes' =>  $purchase->additional_notes,
                        'extra_document' =>  json_encode($purchase->extra_document),
                        'exchange_rate' =>  $purchase->exchange_rate,
                        'created_by' => $purchase->created_by,
                        'received_date' => $purchase->received_date,
                        'transaction_date' => $purchase->transaction_date,
                        ]);
        }
    }
    // added by developer1
    
}
