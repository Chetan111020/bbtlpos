<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Transaction;
use App\Contact;
use App\Product;
use App\Variation;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\TaxRate;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\TransactionSellLine;
use App\Events\TransactionPaymentDeleted;
use App\TransactionPayment;
use App\CreditmemoActivityLog;

class SellReturnController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $contactUtil;
    protected $businessUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ContactUtil $contactUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->join(
                    'transaction_sell_lines AS tsl',
                    'transactions.id',
                    '=',
                    'tsl.transaction_id'
                )
                ->leftJoin(
                    'transaction_payments AS TP',
                    'transactions.id',
                    '=',
                    'TP.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell_return')
                ->where('transactions.status', 'final')
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.discount_amount',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.final_total',
                    'transactions.payment_status',
                    'bl.name as business_location',
                    'transactions.invoice_no as parent_sale',
                    'transactions.id as parent_sale_id',
                     'tsl.unit_price_inc_tax as unit_price',
                    // 'transaction_sell_lines.unit_price_inc_tax',
                    DB::raw('(Select method from transaction_payments as tp1 where tp1.transaction_id = transactions.id  limit 0,1) as p_method'),
                    //DB::raw('SUM(TP.amount) as amount_paid'),

                    DB::raw('(SELECT SUM(TP.amount) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as amount_paid'),

                    DB::raw('(SUM(tsl.quantity_returned) + SUM(tsl.gar_box_return_qty)) as quantity_returned')
                );

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

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
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
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                        <li><a href="#" class="btn-modal" data-container=".view_modal" data-href="{{action(\'SellReturnController@show\', [$parent_sale_id])}}"><i class="fas fa-eye" aria-hidden="true"></i> @lang("messages.view")</a></li>
                        <li><a href="{{action(\'SellReturnController@add\', [$parent_sale_id])}}" ><i class="fa fa-edit" aria-hidden="true"></i> @lang("messages.edit")</a></li>
                       @if($p_method == "" || $p_method == null) <li><a href="{{action(\'SellReturnController@destroy\', [$id])}}" class="delete_sell_return" ><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</a></li>@endif

                        <li><a class=""  target="_blank" href="{{action(\'SellReturnController@printInvoice2\', [$id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("Print")</a></li>

                    @if($payment_status != "paid")
                        <li><a href="{{action(\'TransactionPaymentController@addPayment\', [$id])}}" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a></li>
                    @endif

                    <li><a href="{{action(\'TransactionPaymentController@show\', [$id])}}" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.view_payments")</a></li>
                    <li><a href="#" data-href="{{ action("NotificationController@getTemplate", ["transaction_id" => $id,"template_for" => "sell_return"])}}" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("Credit Memo Notification") . '</a></li>

                    </ul>
                    </div>'
                )
                // <li><a href="#" class="print-invoice" data-href="{{action(\'SellReturnController@printInvoice\', [$id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a></li>
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn('parent_sale', function ($row) {
                    return '<button type="button" class="btn btn-link btn-modal" data-container=".view_modal" data-href="' . action('SellController@show', [$row->parent_sale_id]) . '">' . $row->parent_sale . '</button>';
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    '<a href="{{ action("TransactionPaymentController@show", [$id])}}" class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}</span></a>'
                )
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $due . '">' . $due . '</sapn>';
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view")) {
                            return action('SellReturnController@show', [$row->parent_sale_id]);
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['final_total', 'action', 'parent_sale', 'payment_status', 'payment_due', 'quantity_returned'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('sell_return.index')->with(compact('business_locations', 'customers', 'sales_representative'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     if (!auth()->user()->can('sell.create')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $business_id = request()->session()->get('user.business_id');

    //     //Check if subscribed or not
    //     if (!$this->moduleUtil->isSubscribed($business_id)) {
    //         return $this->moduleUtil->expiredResponse(action('SellReturnController@index'));
    //     }

    //     $business_locations = BusinessLocation::forDropdown($business_id);
    //     //$walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

    //     return view('sell_return.create')
    //         ->with(compact('business_locations'));
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add($id)
    {
        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $sell = Transaction::where('business_id', $business_id)
            ->with(['sell_lines', 'location', 'return_parent', 'contact', 'tax', 'sell_lines.sub_unit', 'sell_lines.product','sell_lines.variations', 'sell_lines.product.unit'])
            ->find($id);

        $taxDetails = array();
        foreach ($sell->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }
            //tax calculation
            $taxDetails[$key] = $this->productUtil->getProductTax($value->product->id,$value->variations->id,$sell->contact_id,0,$value->variations->sell_price_inc_tax);

            $sell->sell_lines[$key]->formatted_qty = $this->transactionUtil->num_f($value->quantity, false, null, true);
        }

        $customers = Contact::customersDropdown($business_id, false);
        return view('sell_return.edit')
            ->with(compact('sell','customers','taxDetails'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->except('_token');
            $receipt = false;

            if (!empty($input['products'])) {
                $business_id = $request->session()->get('user.business_id');

                //Check if subscribed or not
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse(action('SellReturnController@index'));
                }

                $user_id = $request->session()->get('user.id');

                DB::beginTransaction();
                //dd($input);

                //$itemQTY = count($input['products']);
                //$transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id,$itemQTY);

                //$this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);

                //$sell_return = $this->transactionUtil->addSellReturn($input, $business_id, $user_id);
                //$receipt = $this->receiptContent($business_id, $sell_return->location_id, $sell_return->id);

                // added by developer1
                if(isset($input['transaction_id']) && $input['transaction_id']){

                    $this->transactionUtil->UpdateSellReturnLog($input, $business_id, $user_id,'',$input['transaction_id']);
                    $sell_return = $this->transactionUtil->addSellReturn($input, $business_id, $user_id);

                }
                else
                {
                    $sell_return = $this->transactionUtil->addSellReturn($input, $business_id, $user_id);
                    $transaction = Transaction::latest('id')->first();
                    $transaction_id = $transaction->id;
                    $this->transactionUtil->CreditmemoActivityLog('added',$user_id,$transaction_id);
                }

                DB::commit();

                if(isset($input['save_new']) && $input['save_new'] == 'save_new'){
                    $receipt = true;
                }

                if(isset($input['save_close']) && $input['save_close'] == 'save_close'){
                    $receipt = false;
                }

                $output = ['success' => 1,
                    'msg' => __('lang_v1.success'),
                    'receipt' => $receipt
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            } else {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
                $msg = __("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            }

            $output = ['success' => 0,
                'msg' => $msg
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $sell = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'return_parent',
                'tax',
                'sell_lines',
                'sell_lines.product',
                'sell_lines.variations',
                'sell_lines.sub_unit',
                'sell_lines.product',
                'sell_lines.product.unit',
                'location'
            )
            ->select('transactions.*',DB::raw('(SELECT SUM(TP.amount) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as amount_paid'))
            ->first();

        $taxDetails = array();
        foreach ($sell->sell_lines as $key => $value) {

            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }

            //tax calculation
            $taxDetails[$key] = $this->productUtil->getProductTax($value->product->id,$value->variations->id,$sell->contact_id,0,$value->variations->sell_price_inc_tax);
        }

        $sell_taxes = [];
        if (!empty($sell->tax)) {
            if ($sell->tax->is_tax_group) {
                $sell_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->tax, $sell->tax_amount));
            } else {
                $sell_taxes[$sell->tax->name] = $sell->tax_amount;
            }
        }

        $total_discount = 0;
        if ($sell->discount_type == 'fixed') {
            $total_discount = $sell->discount_amount;
        } elseif ($sell->discount_type == 'percentage') {
            $discount_percent = $sell->discount_amount;
            if ($discount_percent == 100) {
                $total_discount = $sell->total_before_tax;
            } else {
                $total_after_discount = $sell->final_total - $sell->tax_amount;
                $total_before_discount = $total_after_discount * 100 / (100 - $discount_percent);
                $total_discount = $total_before_discount - $total_after_discount;
            }
        }

        //added by developer 1
        $CreditmemoActivityLog = CreditmemoActivityLog::join('users','users.id','=','creditmemo_activity_log.user_id')
              ->where('transaction_id',$id)
              ->whereIn('description', ['added', 'edited'])
                ->select('creditmemo_activity_log.created_at as datetime',
                    'creditmemo_activity_log.message as message',
                    'creditmemo_activity_log.description as description',
                    'users.first_name as first_name'
                )
                ->get();

        return view('sell_return.show')
            ->with(compact('sell', 'sell_taxes', 'total_discount','taxDetails','CreditmemoActivityLog'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $reason = !empty(request()->get('reason'))?request()->get('reason'):"";
                $business_id = request()->session()->get('user.business_id');
                //Begin transaction
                DB::beginTransaction();

                $this->DeleteCreditmemoActivityLog($id,$reason);

                $sell_return = Transaction::where('id', $id)
                    ->where('business_id', $business_id)
                    ->where('type', 'sell_return')
                    ->with(['sell_lines', 'payment_lines'])
                    ->first();

                //delete from advance amount start
                $contact_id = $sell_return->contact_id;
                $contact = Contact::find($contact_id);
                $contact->balance = $contact->balance - $sell_return->final_total;
                $contact->save();
                //delete from advance amount end

                $sell_lines = TransactionSellLine::where('transaction_id',
                    $sell_return->id)
                    ->get();

                if (!empty($sell_return)) {
                    $transaction_payments = $sell_return->payment_lines;

                    foreach ($sell_lines as $sell_line) {
                        if ($sell_line->quantity_returned > 0) {
                            $quantity = 0;
                            $quantity_before = $this->transactionUtil->num_f($sell_line->quantity_returned);

                            $sell_line->quantity_returned = 0;
                            $sell_line->save();

                            //update quantity sold in corresponding purchase lines
                            $this->transactionUtil->updateQuantitySoldFromSellLine($sell_line, 0, $quantity_before);

                            // Update quantity in variation location details
                            $this->productUtil->updateProductQuantity($sell_return->location_id, $sell_line->product_id, $sell_line->variation_id, 0, $quantity_before);
                        }
                    }

                    $sell_return->delete();
                    foreach ($transaction_payments as $payment) {
                        if($payment->advance_amt) $this->transactionUtil->updateContactBalance($sell_return->contact_id, $payment->advance_amt , 'deduct');
                        event(new TransactionPaymentDeleted($payment));
                    }
                }

                DB::commit();

                $output = ['success' => 1,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (\Exception $e) {
                DB::rollBack();

                if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                    $msg = $e->getMessage();
                } else {
                    \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
                    $msg = __('messages.something_went_wrong');
                }

                $output = ['success' => 0,
                    'msg' => $msg
                ];
            }

            return $output;
        }
    }
    // added by developer1
    public function DeleteCreditmemoActivityLog($id,$reason="")
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $sell = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'sell_lines','payment_lines'
            )
            ->select('transactions.*',DB::raw('(SELECT SUM(TP.amount) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as amount_paid'))
            ->first();
        if(!empty($sell))
        {
            $totalTax=0;
            $quantity_returned=0;
            $sell_lines_data = "";
            if(!empty($sell->sell_lines))
            {
                foreach ($sell->sell_lines as $sellKey => $sell_line) {
                    if($sell_line->quantity_returned == 0)
                    {
                        continue;
                    }
                    $totalTax += $sell_line->pos_line_tax_amount + $sell_line->city_tax_amount;
                    $quantity_returned += $sell_line->quantity_returned + $sell_line->gar_box_return_qty;
                }
                $sell_lines_data = json_encode($sell->sell_lines);
            }

            if(!empty($sell->payment_lines))
            {
                $payment_lines_data = json_encode($sell->payment_lines);
            }

            CreditmemoActivityLog::create([
                        'user_id' => $user_id,
                        'transaction_id' => $sell->id,
                        'description' => 'deleted',
                        'business_id' => $sell->business_id,
                        'location_id' => $sell->location_id,
                        'contact_id' => $sell->contact_id,
                        'invoice_no' => $sell->invoice_no,
                        'quantity_returned' => $quantity_returned,
                        'type' => $sell->type,
                        'status' => $sell->status,
                        'payment_status' => $sell->payment_status,
                        'final_total' => $sell->final_total,
                        'amount_paid' => $sell->amount_paid,
                        'discount_amount' => $sell->discount_amount,
                        'box_qty' => $sell->box_qty,
                        'tax' => $totalTax,
                        'reason' => $reason,
                        'sell_lines' => $sell_lines_data,
                        'payment_lines' => $payment_lines_data,
                        'additional_notes' =>  $sell->additional_notes,
                        'document' =>  $sell->document,
                        'created_by' => $sell->created_by,
                        'transaction_date' => $sell->transaction_date,
                        ]);
        }
    }

    /**
     * Returns the content for the receipt
     *
     * @param  int $business_id
     * @param  int $location_id
     * @param  int $transaction_id
     * @param string $printer_type = null
     *
     * @return array
     */
    private function receiptContent(
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

            $tax_details = $this->transactionUtil->getTaxDetails($transaction_id);

            $paid_data = $this->getTotalPaidforSellreturn($transaction_id, $business_details, $receipt_printer_type);

            $tr = Transaction::find($transaction_id);
            if(!empty($tr->contact->supplier_business_name)){
                $title = $tr->contact->supplier_business_name . ' - ' . $tr->invoice_no . ' - Credit Memo';
            }
            else{
                $title = $tr->contact->name . ' - ' . $tr->invoice_no . ' - Credit Memo';
            }


            //If print type browser - return the content, printer - return printer config data, and invoice format config
            if ($receipt_printer_type == 'printer') {
                $output['print_type'] = 'printer';
                $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
                $output['data'] = $receipt_details;
            } else {
                $output['html_content'] = view('sell_return.receipt', compact('title','receipt_details' , 'tax_details' , 'paid_data'))->render();
            }
        }

        return $output;
    }

    public function getTotalPaidforSellreturn($transaction_id, $business_details,$receipt_printer_type)
    {
        $data = Transaction::leftJoin(
                    'transaction_payments AS TP',
                    'transactions.id',
                    '=',
                    'TP.transaction_id'
                )
                ->where('transactions.id', $transaction_id)
                ->where('transactions.type', 'sell_return')
                ->where('transactions.status', 'final')
                ->select(
                    'transactions.id',
                    'transactions.final_total',
                    DB::raw('(SELECT SUM(TP.amount) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as amount_paid')
                )->first();

        $output= [];
        if(isset($data))
        {

            $show_currency = true;
            if ($receipt_printer_type == 'printer' && trim($business_details->currency_symbol) != '$') {
                $show_currency = false;
            }

            $due = $data->final_total - $data->amount_paid;
            $output['total_paid'] = ($data->amount_paid == 0) ? 0 : $this->transactionUtil->num_f($data->amount_paid, $show_currency, $business_details);
            $output['total_due'] = ($due == 0) ? 0 : $this->transactionUtil->num_f($due, $show_currency, $business_details);
        }
        return (object)$output;
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
                    ->first();
                if (empty($transaction)) {
                    return $output;
                }

                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, 'browser');

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

    public function printInvoice2(Request $request, $transaction_id)
    {

        $business_id = $request->session()->get('user.business_id');
        $transaction = Transaction::where('business_id', $business_id)
        ->where('id', $transaction_id)
        ->first();

        $location_id = $transaction->location_id;
        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_id, $location_details->invoice_layout_id);
        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, 'html');

        $tax_details = $this->transactionUtil->getTaxDetails($transaction_id);

        $paid_data = $this->getTotalPaidforSellreturn($transaction_id, $business_details, 'html');

        $tr = Transaction::find($transaction_id);
        if(!empty($tr->contact->supplier_business_name)){
            $title = $tr->contact->supplier_business_name . ' - ' . $tr->invoice_no . ' - Credit Memo';
        }
        else{
            $title = $tr->contact->name . ' - ' . $tr->invoice_no . ' - Credit Memo';
        }

        $id = $transaction_id;

        return view('sell_return.receipt2')
            ->with(compact('title','receipt_details' , 'tax_details' , 'paid_data', 'id'));

    }
     public function getCreditMemo(Request $request, $token)
    {

                // $business_id = $request->session()->get('user.business_id');

                // $transaction = Transaction::where('business_id', $business_id)
                //     ->where('id', $transaction_id)
                //     ->first();
                //     // echo "<pre>";print_r($transaction);
                // if (empty($transaction)) {
                //     // return $output;
                // }

                // $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id);
                // echo "<pre>";print_r($receipt);


                $transaction = Transaction::where('invoice_token', $token)->with(['business', 'location'])->first();

                $receipt = $this->receiptContent($transaction->business_id,$transaction->location_id, $transaction->id);

                // echo "<pre>";print_r($receipt);
                $output['data'] = $receipt;
                return $output['data']['html_content'];




    }
    public function sellReturn(Request $request)
    {
        $sell = "";
        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        if ($request->searchKey) {
            $sell = Transaction::where('business_id', $business_id)
                ->with(['sell_lines', 'location', 'return_parent', 'contact', 'tax', 'sell_lines.sub_unit', 'sell_lines.product', 'sell_lines.product.unit'])
                ->whereNotNull('return_parent_id');

            foreach ($sell->sell_lines as $key => $value) {
                if (!empty($value->sub_unit_id)) {
                    $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                    $sell->sell_lines[$key] = $formated_sell_line;
                }

                $sell->sell_lines[$key]->formatted_qty = $this->transactionUtil->num_f($value->quantity, false, null, true);
            }
        }
        $contact = $request->contact ?? 0;
        $customers = Contact::customersDropdown($business_id, false);
        return view('sell_return.add')
            ->with(compact('contact','sell', 'customers'));
    }

    public function getItemForReturn(Request $request)
    {
        $sell = "";
        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }
        $row_count = request()->get('product_row');
        $row_count = $row_count + 1;
        $business_id = request()->session()->get('user.business_id');
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        if ($request->searchKey) {

            $query = Variation::join('products AS p', 'variations.product_id', '=', 'p.id')
                        ->join('product_variations AS pv', 'variations.product_variation_id', '=', 'pv.id')
                        ->leftjoin('variation_location_details AS vld', 'variations.id', '=', 'vld.variation_id')
                        ->leftjoin('units', 'p.unit_id', '=', 'units.id')
                        ->leftjoin('tax_rates', 'p.sub_category_id', '=', 'tax_rates.sub_category')
                        ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                        ->leftjoin('categories as sub_cat', 'p.sub_category_id', '=', 'sub_cat.id')
                        ->leftjoin('transaction_sell_lines as tsl', 'p.id', '=', 'tsl.product_id')
                        ->leftjoin('brands', function ($join) {
                            $join->on('p.brand_id', '=', 'brands.id')
                                ->whereNull('brands.deleted_at');
                        })
                        ->where('p.business_id', $business_id);

                $query->where(function ($query) use ($request) {
                    //$query->where('p.name', 'like', '%' . $request->searchKey .'%');
                    //$query->where('name', $request->searchKey);
                    // $query->orWhere('variations.id', '=', $request->searchKey);
                    $query->orWhere('p.sku', '=', $request->searchKey);
                        //->orWhere('p.item_code', '=',  $request->searchKey );
                    //$query->where('name', $request->searchKey);
                });

                $sells = $query->select(
                    DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name,
                            ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                    'p.id as product_id',
                    'p.sku',
                    'p.brand_id',
                    'p.category_id',
                    'p.tax as tax_id',
                    'p.enable_stock',
                    'p.product_description',
                    'p.enable_sr_no',
                    'p.qty_box',
                    'p.updated_at',
                    'tax_rates.taxvalue',
                    'tax_rates.tax',
                    'tax_rates.tax_percent',
                    'tax_rates.city_tax_value',
                    'p.type as product_type',
                    'p.main_image as product_image',
                    'p.name as product_actual_name',
                    'p.warranty_id',
                    'p.category_id',
                    'p.sub_category_id',
                    'categories.name as catName',
                    'sub_cat.name as subCatName',
                    'pv.name as product_variation_name',
                    'pv.is_dummy as is_dummy',
                    'variations.name as variation_name',
                    'variations.sub_sku',
                    'p.barcode_type',
                    'vld.qty_available',
                    'variations.default_sell_price',
                    'variations.sell_price_inc_tax',
                    'variations.id as variation_id',
                    'variations.combo_variations',  //Used in combo products
                    'units.short_name as unit',
                    'units.id as unit_id',
                    'units.allow_decimal as unit_allow_decimal',
                    'brands.name as brand',
                    'tsl.gar_box_return_qty',
                    'tsl.gar_piece_return_qty',
                    DB::raw("(SELECT purchase_price_inc_tax FROM purchase_lines WHERE
                                variation_id=variations.id ORDER BY id DESC LIMIT 1) as last_purchased_price")
                )
                ->firstOrFail();



                $last_sell = Transaction::join(
                    'transaction_sell_lines AS tsl',
                    'transactions.id',
                    '=',
                    'tsl.transaction_id'
                )
                // ->leftjoin('contacts AS c', 'c.id', '=', 'transactions.contact_id')
                // ->leftjoin('products AS p', 'p.id', '=', 'tsl.product_id')
                ->where('transactions.contact_id', $request->customer_id)
                ->where('tsl.product_id', $sells->product_id)
                // ->select('transactions.contact_id')
                ->orderBy('tsl.created_at','desc')
                ->first();

                //tax calculation
                $sells['taxDetail']= $this->productUtil->getProductTax($sells['product_id'],$sells['variation_id'],$request->customer_id,0,$sells['sell_price_inc_tax']);

                // $taxType = 2;
                // if(isset($last_sell->tax_type) && $last_sell->tax_type=='inclusive'){
                //     $taxType=1;
                // }

                // $subcategoryId = $last_sell->sub_category_id;
                // if($last_sell->sub_category_id==null || $last_sell->sub_category_id=='' || $last_sell->sub_category_id==0){
                //     $subcategoryId = '';
                // }

                // $taxDetail = TaxRate::select('tax','taxvalue','tax_percent','city_tax_value')
                // ->where('tax_type',$taxType)
                // ->where('category',$last_sell->category_id)
                // ->where('sub_category',$subcategoryId)
                // ->where('business_id',$last_sell->business_id)
                // ->where('state',$last_sell->state)
                // ->first();

                // if(isset($last_sell->unit_price_inc_tax) && $last_sell->unit_price_inc_tax)
                // {
                //     $sellPrice = $last_sell->unit_price_inc_tax;
                // }else{
                //     $sellPrice = $sells->sell_price_inc_tax;
                // }
                // $stateTax = 0;
                // $cityTax = 0;
                // if(isset($taxDetail))
                // {

                //     $stateTax =  $taxDetail->taxvalue;
                //     $cityTax =  $taxDetail->city_tax_value;
                //     if(isset($taxDetail->tax)){
                //         $stateTax = ($taxDetail->tax / 100) * $sellPrice;
                //     }
                //     if(isset($taxDetail->tax_percent)){
                //         $cityTax = ($taxDetail->tax_percent/100) * $sellPrice;
                //     }

                // }

                // $sells = Product::orderBy('updated_at','desc')
                // ->where(function ($query) use ($request) {
                //     $query->where('name', 'like', '%' . $request->searchKey .'%');
                //     $query->orWhere('sku', '=', $request->searchKey)
                //         ->orWhere('item_code', '=',  $request->searchKey );
                // })
                // ->whereHas('transaction', function ($query) use ($request, $business_id) {
                //     $query->where('business_id', $business_id)
                //     ->where('contact_id', $request->customer_id);
                //     //$query->where('name', $request->searchKey);
                // })
                //->where('contact_id', $request->customer_id)
                //->get();
                // if($sells) {
                //     foreach ($sells as $key => $value) {
                //         if (!empty($value->sub_unit_id)) {
                //             $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                //             $sells[$key] = $formated_sell_line;
                //         }
                //         $sells[$key]->formatted_qty = $this->transactionUtil->num_f($value->quantity, false, null, true);
                //     }
                // }
        }
        return view('sell_return.get-item-for-return', compact('sells', 'row_count', 'last_sell'));
    }

    public function getItemForReturnList(Request $request)
    {
        $sell = "";
        if (!auth()->user()->can('access_sell_return')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        //Check if subscribed or not
        // if (!$this->moduleUtil->isSubscribed($business_id)) {
        //     return $this->moduleUtil->expiredResponse();
        // }
        if ($request->searchKey) {
            $sells = TransactionSellLine::
                with([
                    'transaction',
                    'transaction.location',
                    'transaction.return_parent',
                    'transaction.contact', 'transaction.tax',
                    'transaction.purchase_lines',
                    'sub_unit',
                    'product',
                    'product.unit'
                ])->orderBy('updated_at','desc')
                //->where('transaction.business_id', $business_id)
                ->whereHas('product', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->searchKey .'%');
                    //$query->where('name', $request->searchKey);
                    $query->orWhere('sku', '=', $request->searchKey)
                        ->orWhere('item_code', '=',  $request->searchKey );
                    //$query->where('name', $request->searchKey);
                })
                ->whereHas('transaction', function ($query) use ($request, $business_id) {
                    $query->where('business_id', $business_id);
                    // ->where('contact_id', $request->customer_id);
                    //$query->where('name', $request->searchKey);
                })
                //->where('contact_id', $request->customer_id)
                ->get();
                if($sells) {
                    foreach ($sells as $key => $value) {
                        if (!empty($value->sub_unit_id)) {
                            $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                            $sells[$key] = $formated_sell_line;
                        }
                        $sells[$key]->formatted_qty = $this->transactionUtil->num_f($value->quantity, false, null, true);
                    }
            }
        }
        return $sells;
    }

    public function getCustomerInvoice(Request $request)
    {
        $sell = "";
        $business_id = request()->session()->get('user.business_id');
        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        if ($request->customer_id) {
            $sell = Transaction::where('transactions.business_id', $business_id)
                ->where('transactions.contact_id', $request->customer_id)
                ->with([
                    'sell_lines',
                    'location',
                    'return_parent',
                    'contact',
                    'sales_person',
                    'tax',
                    'sell_lines.sub_unit',
                    'sell_lines.product',
                    'sell_lines.product.unit'
                ])->orderBy('updated_at','desc')
                ->first();

            if ($sell != null) {
                return response()->json($sell);
            } else {
                return response()->json(0);
            }

        }
    }

    public function getsellreturn(Request $request){
        $customer_id = $request->customer_id;


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


        return view('contact.sell_return')
             ->with(compact('customer_id','sell_return','due_payment','partial_payment','paid_payment','total_paid','total_due'));
    }
}
