<?php

namespace App\Http\Controllers;

use App\Contact;

use App\Events\TransactionPaymentAdded;

use App\Events\TransactionPaymentUpdated;
use App\Transaction;
use App\TransactionPayment;

use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;

use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Delpaymentslog;

class TransactionPaymentController extends Controller
{
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param TransactionUtil $transactionUtil
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            $business_id = $request->session()->get('user.business_id');
            $transaction_id = $request->input('transaction_id');
            $transaction = Transaction::where('business_id', $business_id)->with(['contact'])->findOrFail($transaction_id);

            if (!auth()->user()->can('purchase.payments') || !auth()->user()->can('sell.payments')) {
                abort(403, 'Unauthorized action.');
            }
            $discount_type = $request->discount_type;
            $discount_amount = $request->discount_amount;
            $final_total = $request->discount_total;

        // Transaction::where('id', $transaction_id)
        // ->update(['discount_type'=> $discount_type,'discount_amount'=> $discount_amount,'final_total'=> $final_total]);

        $bill_amount = $request->bill_amt;
         $amount = $request->amount;
         $final_amt  = 0;
         if($amount > $bill_amount){
             $final_amt  =  (float)$amount - (float)$bill_amount;

         }

        //  return 123;


            if ($transaction->payment_status != 'paid') {
                $inputs = $request->only(['amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number','payment_location']);
                $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
                $inputs['transaction_id'] = $transaction->id;
                $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
                $inputs['created_by'] = auth()->user()->id;
                $inputs['payment_for'] = $transaction->contact_id;
                $inputs['discount_type'] = $request->discount_type;
                $inputs['discount_amount'] = $request->discount_amount;
                $inputs['cash_note'] = $request->cash_note ?? '';
                // $inputs['final_total'] = $request->discount_total;
            //     'discount_type' => !empty($input['discount_type']) ? $input['discount_type'] : null,
            // 'discount_amount' => $uf_data ? $this->num_uf($input['discount_amount']) : $input['discount_amount'],

                if($request->discount_type != '' && $request->discount_amount>0){
                    $discount_amount = $request->discount_amount;
                    if($request->discount_type == 'percentage'){
                        $discount_amount = ($request->amount * $request->discount_amount)/100;
                    }
                    $transaction->discount_amount = $transaction->discount_amount + $discount_amount;
                    $transaction->discount_type = 'fixed';
                    if($transaction->final_total > $discount_amount)
                    $transaction->final_total = $transaction->final_total - $discount_amount;
                }
                $transaction->save();
            // return $inputs['final_total'];


                if ($inputs['method'] == 'custom_pay_1') {
                    $inputs['transaction_no'] = $request->input('transaction_no_1');
                } elseif ($inputs['method'] == 'custom_pay_2') {
                    $inputs['transaction_no'] = $request->input('transaction_no_2');
                } elseif ($inputs['method'] == 'custom_pay_3') {
                    $inputs['transaction_no'] = $request->input('transaction_no_3');
                }
                else{
                    $pay_parts = explode('_',$inputs['method']);
                    if(count($pay_parts) == 3 && $pay_parts[0] = 'custom'){
                        $inputs['transaction_no'] = $request->input('transaction_no_'.$pay_parts[2]);
                    }
                }

                if (!empty($request->input('account_id')) && $inputs['method'] != 'advance') {
                    $inputs['account_id'] = $request->input('account_id');
                }

                $prefix_type = 'purchase_payment';
                if (in_array($transaction->type, ['sell', 'sell_return'])) {
                    $prefix_type = 'sell_payment';
                } elseif (in_array($transaction->type, ['expense', 'expense_refund'])) {
                    $prefix_type = 'expense_payment';
                }

                DB::beginTransaction();

                $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
                //Generate reference number
                $inputs['payment_ref_no'] = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);

                $inputs['business_id'] = $request->session()->get('business.id');
                $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

                //Pay from advance balance
                $payment_amount = $inputs['amount'];
                $contact_balance = !empty($transaction->contact) ? $transaction->contact->balance : 0;
                if ($inputs['method'] == 'advance' && $inputs['amount'] > $contact_balance) {
                    throw new \Exception(__('lang_v1.required_advance_balance_not_available'));
                }

                // $inputs['is_advance'] = 1;
                // $inputs['advance_amt'] = $final_amt;

                if($inputs['method'] == 'credit'){
                    //make paid credit entries
                    $this->updateCreditTransaction($inputs['amount'], $transaction->contact->id);
                }
                if($final_amt > 0 ){
                    $change_return = $this->dummyPaymentLine;
                    $change_return['is_return'] = 1;
                    $change_return['is_advance'] = 1;
                    $change_return['advance_amt'] = $final_amt;
                    $change_return['amount'] = $final_amt;
                    $change_return['note'] = "Adv payment of $".$final_amt;
                    $input['payment'][] = $change_return;
                    $this->transactionUtil->updateContactBalance($transaction->contact->id, $final_amt , 'add');
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                }

                if (!empty($inputs['amount'])) {
                    $tp = TransactionPayment::create($inputs);
                    $inputs['transaction_type'] = $transaction->type;
                    event(new TransactionPaymentAdded($tp, $inputs));
                }

                //update payment status
                $this->transactionUtil->updatePaymentStatus($transaction_id, $transaction->final_total);

                //add parent_id in return amount
                $returnPayment = TransactionPayment::where('transaction_id',$transaction_id)->where('is_return',1)->orderBy('id')->first();
                If($returnPayment){
                    $returnPayment->parent_id = $tp->id;
                    $returnPayment->save();
                }
                DB::commit();
            }

            $output = ['success' => true,
                            'msg' => __('purchase.payment_added_success')
                        ];
        } catch (\Exception $e) {
            // return $e;
            // return $e->getMessage();
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                          'msg' => __('messages.something_went_wrong')
                      ];

        }

        return redirect()->back()->with(['status' => $output]);
    }

     public function updateCreditTransaction($amount, $contact_id){
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $credit_amount = Transaction::leftJoin(
            'transaction_payments AS TP',
            'transactions.id',
            '=',
            'TP.transaction_id'
        )->select(
            'transactions.*',
            DB::raw('SUM(TP.amount) as amount_paid')
        )->groupBy('transactions.id')->where('type','sell_return')->where('contact_id',$contact_id)->whereIn('payment_status',['due', 'partial'])->get();
        $new_amount = $amount;
        $tp_amount = 0;
        foreach($credit_amount as $credit){
            if($new_amount > 0){
                $available_amount = $credit->final_total- $credit->amount_paid;
                if($new_amount > $available_amount) {
                    $new_amount -=$available_amount;
                    $tp_amount =  $available_amount;
                } else if($new_amount <= $available_amount) {
                    $tp_amount =  $new_amount;
                    $new_amount = 0;
                }
                //Create transaction and update credit transaction status
                $tp_data = [
                    'transaction_id' => $credit->id,
                    'paid_on' => \Carbon::now()->toDateTimeString(),
                    'business_id' => $business_id,
                    'amount' => $tp_amount,
                    'method' => "cash",
                    'transaction_no' => '',
                    'card_type' => 'credit',
                    'created_by' => $user_id,
                    'payment_for' =>  $contact_id,
                ];
                $tp = TransactionPayment::create($tp_data);
                $this->transactionUtil->updatePaymentStatus($credit->id, $credit->final_total);
                // return $tp;
            }
        }
        // $paid_credit = TransactionPayment::leftjoin('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')->where('t.contact_id', $contact_id)->where('method','credit')->sum('amount');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $transaction = Transaction::where('id', $id)
                                        ->with(['contact', 'business', 'transaction_for'])
                                        ->first();
            $payments_query = TransactionPayment::where('transaction_id', $id);

            $accounts_enabled = false;
            if ($this->moduleUtil->isModuleEnabled('account')) {
                $accounts_enabled = true;
                $payments_query->with(['payment_account']);
            }

            $payments = $payments_query->get();
            $location_id = !empty($transaction->location_id) ? $transaction->location_id : null;
            $payment_types = $this->transactionUtil->payment_types($location_id, true);

            return view('transaction_payment.show_payments')
                    ->with(compact('transaction', 'payments', 'payment_types', 'accounts_enabled'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $payment_line = TransactionPayment::where('method', '!=', 'advance')->findOrFail($id);
            // $payment_line = TransactionPayment::findOrFail($id);

            $transaction = Transaction::where('id', $payment_line->transaction_id)
                                        ->where('business_id', $business_id)
                                        ->with(['contact', 'location'])
                                        ->first();

            $payment_types = $this->transactionUtil->payment_types($transaction->location,1);

            //Accounts
            $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);

            return view('transaction_payment.edit_payment_row')
                        ->with(compact('transaction', 'payment_types', 'payment_line', 'accounts'));
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
        if (!auth()->user()->can('purchase.payments') && !auth()->user()->can('sell.payments')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $inputs = $request->only(['amount', 'method', 'note', 'card_number', 'card_holder_name',
            'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security','cash_note',
            'cheque_number', 'bank_account_number','payment_location']);
            //$inputs['paid_on'] = $this->transactionUtil->uf_date($format_to_paid_on, true);
            $inputs['paid_on'] = \Carbon::parse($request->input('paid_on'))->format('Y-m-d G:i:s A');
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);

            if ($inputs['method'] == 'custom_pay_1') {
                $inputs['transaction_no'] = $request->input('transaction_no_1');
            } elseif ($inputs['method'] == 'custom_pay_2') {
                $inputs['transaction_no'] = $request->input('transaction_no_2');
            } elseif ($inputs['method'] == 'custom_pay_3') {
                $inputs['transaction_no'] = $request->input('transaction_no_3');
            }
            else{
                $pay_parts = explode('_',$inputs['method']);
                if(count($pay_parts) == 3 && $pay_parts[0] = 'custom'){
                    $inputs['transaction_no'] = $request->input('transaction_no_'.$pay_parts[2]);
                }
            }

            if (!empty($request->input('account_id'))) {
                $inputs['account_id'] = $request->input('account_id');
            }

            // $payment = TransactionPayment::findOrFail($id);
            $payment = TransactionPayment::where('method', '!=', 'advance')->findOrFail($id);

            $old_discount_amt = $payment->discount_amount;
            $old_discount_type = $payment->discount_type;
            $old_amt = $payment->amount;
            //Update parent payment if exists
            if (!empty($payment->parent_id)) {
                $parent_payment = TransactionPayment::find($payment->parent_id);
                $parent_payment->amount = $parent_payment->amount - ($payment->amount - $inputs['amount']);
                $parent_payment->discount_amount=$request->discount_amount;
                $parent_payment->discount_type=$request->discount_type;
                $parent_payment->save();
            }else {
                $payment->discount_amount=$request->discount_amount;
                $payment->discount_type=$request->discount_type;
            }

            $business_id = $request->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                                ->find($payment->transaction_id);

            if($request->discount_type != ''){
                if($old_discount_type == 'percentage'){
                      $old_discount_amt = ($old_amt * $old_discount_amt)/100;
                }
                $discount_amount = $request->discount_amount;
                if($request->discount_type == 'percentage'){
                    $discount_amount = ($this->transactionUtil->num_uf($request->amount) * $request->discount_amount)/100;
                }
                if($old_discount_amt > $discount_amount){
                    $new_final_total = $transaction->final_total + ($old_discount_amt - $discount_amount);
                } else{
                    $new_final_total = $transaction->final_total - ($discount_amount - $old_discount_amt);
                }
                $transaction->discount_amount = $transaction->discount_amount + $discount_amount - $old_discount_amt;
                $transaction->discount_type = 'fixed';
                $transaction->final_total =  $new_final_total;
            } else{
                if($old_discount_amt > 0){
                    $new_final_total = $transaction->final_total - $old_discount_amt;
                    $transaction->discount_amount = $transaction->discount_amount - $old_discount_amt;
                    $transaction->final_total =  $new_final_total;
                }
            }
            $transaction->save();

            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (!empty($document_name)) {
                $inputs['document'] = $document_name;
            }

            DB::beginTransaction();
            //added by developer1
            $user_id = $request->session()->get('user.id');
            $this->transactionUtil->UpdatePaymentTransactionLog($request, $business_id, $user_id,'',$id,$transaction->location_id);
            $payment->update($inputs);


            //update payment status
            $this->transactionUtil->updatePaymentStatus($payment->transaction_id);

            DB::commit();

            //event
            event(new TransactionPaymentUpdated($payment, $transaction->type));

            $output = ['success' => true,
                            'msg' => __('purchase.payment_updated_success')
                        ];
        } catch (\Exception $e) {
            // return $e;
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                          'msg' => __('messages.something_went_wrong')
                      ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('purchase.payments') && !auth()->user()->can('sell.payments')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {

                $payment = TransactionPayment::findOrFail($id);

                DB::beginTransaction();
                $discount_amt = $payment->discount_amount;
                $discount_type = $payment->discount_type;
                $amt = $payment->amount;
                if($discount_type == 'percentage'){
                      $discount_amt = ($amt * $discount_amt)/100;
                }

                if (!empty($payment->transaction_id)) {
                    $return_payment = TransactionPayment::where('parent_id', $payment->id)->where('is_return',1)->first();
                    if($return_payment){
                        $this->transactionUtil->updateContactBalance($return_payment->payment_for, $return_payment->advance_amt , 'deduct');
                        TransactionPayment::deletePayment($return_payment);
                    }
                    TransactionPayment::deletePayment($payment);
                    $transaction_id = $payment->transaction_id;

                    $transaction = Transaction::find($transaction_id);
                    $transaction->discount_amount =  $transaction->discount_amount - $discount_amt;
                    $transaction->final_total =  $transaction->final_total + $discount_amt;
                    $transaction->save();
                    // Transaction::where('id',$transaction_id)->update(['discount_amount'=>0]);
                } else { //advance payment
                    $adjusted_payments = TransactionPayment::where('parent_id',
                                                $payment->id)
                                                ->get();

                    $total_adjusted_amount = $adjusted_payments->sum('amount');

                    //Get customer advance share from payment and deduct from advance balance
                    $total_customer_advance = $payment->amount - $total_adjusted_amount;
                    if ($total_customer_advance > 0) {
                        $this->transactionUtil->updateContactBalance($payment->payment_for, $total_customer_advance , 'deduct');
                    }

                    //Delete all child payments
                    foreach ($adjusted_payments as $adjusted_payment) {
                        //Make parent payment null as it will get deleted
                        $adjusted_payment->parent_id = null;
                        TransactionPayment::deletePayment($adjusted_payment);
                    }

                    //Delete advance payment
                    $payment->delete();
                }
                if($payment->is_advance == 1 && $payment->is_return == 1 && $payment->advance_amt>0){
                    $this->transactionUtil->updateContactBalance($payment->payment_for, $payment->advance_amt , 'deduct');
                }
                $reason = !empty(request()->get('reason'))?request()->get('reason'):"";
                $this->del_payments_log($payment,$reason);
                DB::commit();

                $output = ['success' => true,
                                'msg' => __('purchase.payment_deleted_success')
                            ];
            } catch (\Exception $e) {
                DB::rollBack();

                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                                'msg' => __('messages.something_went_wrong')
                            ];
            }

            return $output;
        }
    }

    /**
     * Adds new payment to the given transaction.
     *
     * @param  int  $transaction_id
     * @return \Illuminate\Http\Response
     */
    public function addPayment($transaction_id)
    {
        if (!auth()->user()->can('purchase.payments') && !auth()->user()->can('sell.payments')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                                        ->with(['contact', 'location'])
                                        ->findOrFail($transaction_id);
            if ($transaction->payment_status != 'paid') {
                $show_advance = in_array($transaction->type, ['sell', 'purchase']) ? true : false;
                $payment_types = $this->transactionUtil->payment_types($transaction->location, $show_advance);

                $paid_amount = $this->transactionUtil->getTotalPaid($transaction_id);

                // if($transaction->discount_type && $transaction->discount_amount){
                //     if($transaction->discount_type == 'fixed'){
                //         $amount = $transaction->final_total - $transaction->discount_amount - $paid_amount;
                //     }
                //     if($transaction->discount_type == 'percentage'){
                //         $percentage_amt = ($paid_amount * $transaction->discount_amount) / 100;
                //         $amount = $transaction->final_total - $percentage_amt - $paid_amount;
                //     }

                // }else if($transaction->is_advance == 1 && $transaction->payment_status == 'paid'){
                //          $amount =  $transaction->amount - $transaction->final_total - $transaction->advanceamt;
                // }
                // else{
                    $amount = $transaction->final_total - $paid_amount;
                // }
                // $amount = $transaction->final_total - $paid_amount;
                if ($amount < 0) {
                    $amount = 0;
                }

                $amount_formated = $this->transactionUtil->num_f($amount);

                $payment_line = new TransactionPayment();
                $payment_line->amount = $amount;
                $payment_line->method = 'cash';
                $payment_line->paid_on = \Carbon::now()->toDateTimeString();

                //Accounts
                $user_role = Auth::user()->getRoleNameAttribute();
                if($user_role == "Admin" || $user_role == "Administrator"){
                    $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);
                }
                else{
                    $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, false);
                }

                $view = view('transaction_payment.payment_row')
                ->with(compact('transaction', 'payment_types', 'payment_line', 'amount_formated', 'accounts'))->render();

                $output = [ 'status' => 'due',
                                    'view' => $view];
            } else {
                $output = [ 'status' => 'paid',
                                'view' => '',
                                'msg' => __('purchase.amount_already_paid')  ];
            }

            return json_encode($output);
        }
    }

    /**
     * Shows contact's payment due modal
     *
     * @param  int  $contact_id
     * @return \Illuminate\Http\Response
     */
    public function getPayContactDue($contact_id)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $supplier_list = Contact::where('type','supplier')->where('business_id',$business_id)->get();
            $customer_list = Contact::where('type','customer')->where('business_id',$business_id)->get();

            $due_payment_type = request()->input('type');
            $query = Contact::where('contacts.id', $contact_id)
                            ->join('transactions AS t', 'contacts.id', '=', 't.contact_id');
            $query2 = Contact::where('contacts.id', $contact_id)
                            ->join('transactions AS t', 'contacts.id', '=', 't.contact_id');

            if ($due_payment_type == 'purchase') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'expense', final_total, 0)) as total_expense"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id',
                    'contacts.type as contact_type',
                    'contacts.balance'
                    );
            } elseif ($due_payment_type == 'purchase_return') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_return_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id',
                    'contacts.type as contact_type'
                    );
            } elseif ($due_payment_type == 'sell') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id',
                    'contacts.type as contact_type'
                );
                $query2->select(
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_return_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id',
                    'contacts.type as contact_type'
                );
            } elseif ($due_payment_type == 'sell_return') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_return_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id',
                    'contacts.type as contact_type'
                    );
            }

            //Query for opening balance details
            $query->addSelect(
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                'contacts.balance as balance'
            );
            $contact_details = $query->first();

            $payment_line = new TransactionPayment();
            if ($due_payment_type == 'purchase') {
                $contact_details->total_purchase = empty($contact_details->total_purchase) ? 0 : $contact_details->total_purchase;
                //$payment_line->amount =  $contact_details->total_purchase + $contact_details->total_expense  - $contact_details->total_paid    - $contact_details->balance  - $contact_details->total_purchase_return; //- $total_discount;

                // $contact_details->total_purchase - $contact_details->total_paid - $contact_details->total_purchase_return;
                $payment_line->amount = $contact_details->total_purchase - $contact_details->total_paid;
            } elseif ($due_payment_type == 'purchase_return') {
                $payment_line->amount = $contact_details->total_purchase_return -
                                    $contact_details->total_return_paid;
            } elseif ($due_payment_type == 'sell') {
                $contact_details2 = $query2->first();

                $contact_details->total_invoice = empty($contact_details->total_invoice) ? 0 : $contact_details->total_invoice;

                $payment_line->amount = $contact_details->total_invoice - $contact_details->total_paid;

                //$payment_line->amount -= $contact_details2->total_sell_return - $contact_details2->total_return_paid;
            } elseif ($due_payment_type == 'sell_return') {
                $payment_line->amount = $contact_details->total_sell_return -
                                    $contact_details->total_return_paid;
            }

            //If opening balance due exists add to payment amount
            $contact_details->opening_balance = !empty($contact_details->opening_balance) ? $contact_details->opening_balance : 0;
            $contact_details->opening_balance_paid = !empty($contact_details->opening_balance_paid) ? $contact_details->opening_balance_paid : 0;
            $ob_due = $contact_details->opening_balance - $contact_details->opening_balance_paid ;
            if ($ob_due > 0) {
                $payment_line->amount += $ob_due;
            }

            $amount_formated = $this->transactionUtil->num_f($payment_line->amount);

            $contact_details->total_paid = empty($contact_details->total_paid) ? 0 : $contact_details->total_paid;

            $payment_line->method = 'cash';
            $payment_line->paid_on = \Carbon::now()->toDateTimeString();

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

            //Accounts
            $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

            // return $contact_details->total_invoice;
            $sells = [];
            $ledger = [];
            if($due_payment_type=='sell' || $due_payment_type=='sell_return')
            {
                $transactions_invocies = $this->__transactionQuery($contact_id,'sell')
                                ->with(['location'])
                                ->select(
                                        'transactions.id',
                                        'transactions.transaction_date',
                                        'transactions.invoice_no',
                                        'transactions.type',
                                        'transactions.final_total',
                                        DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                                    TP.transaction_id=transactions.id) as total_paid')
                                )
                                ->orderBy('transactions.transaction_date')
                                ->get();


                foreach ($transactions_invocies as $transaction) {
                    $sells[] = [
                        't_date' => $this->transactionUtil->format_date($transaction->transaction_date, true),
                        'invoice_no' => $transaction->invoice_no,
                        'type' => $transaction->type,
                        'final_total' => $transaction->final_total,
                        'amount' => $transaction->final_total - $transaction->total_paid,
                        'transaction_id' => $transaction->id
                    ];
                }

                $transactions_credit_memo = $this->__transactionQuery($contact_id,'sell_return')
                                ->with(['location'])
                                ->select(
                                        'transactions.id',
                                        'transactions.transaction_date',
                                        'transactions.invoice_no',
                                        'transactions.type',
                                        'transactions.final_total',
                                        DB::raw('(SELECT SUM(TP.amount) FROM transaction_payments AS TP WHERE                       TP.transaction_id=transactions.id) as amount_paid')
                                )
                                ->get();


                foreach ($transactions_credit_memo as $transaction) {
                    $ledger[] = [
                        't_date' => $this->transactionUtil->format_date($transaction->transaction_date, true),
                        'invoice_no' => $transaction->invoice_no,
                        'type' => $transaction->type,
                        'final_total' => $transaction->final_total,
                        'credit' => $transaction->final_total - $transaction->amount_paid,
                        'transaction_id' => $transaction->id
                    ];
                }
            }
            if($due_payment_type=='purchase' || $due_payment_type=='purchase_return')
            {
                $transactions_purchases = $this->__transactionQuery($contact_id,'purchase')
                                ->with(['location'])
                                ->select(
                                        'transactions.id',
                                        'transactions.transaction_date',
                                        'transactions.ref_no',
                                        'transactions.type',
                                        'transactions.final_total',
                                        DB::raw("(SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=transactions.id) as total_paid")
                                )
                                ->get();


                foreach ($transactions_purchases as $transaction) {
                    $sells[] = [
                        't_date' => $this->transactionUtil->format_date($transaction->transaction_date, true),
                        'invoice_no' => $transaction->ref_no,
                        'type' => $transaction->type,
                        'final_total' => $transaction->final_total,
                        'amount' => $transaction->final_total - $transaction->total_paid,
                        'transaction_id' => $transaction->id
                    ];
                }

                $transactions_vendor_credit_memo = $this->__transactionQuery($contact_id,'purchase_return')
                                ->with(['location'])
                                ->select(
                                        'transactions.id',
                                        'transactions.transaction_date',
                                        'transactions.ref_no',
                                        'transactions.type',
                                        'transactions.final_total',
                                        DB::raw('(SELECT SUM(TP.amount) FROM transaction_payments AS TP WHERE TP.transaction_id=transactions.id) as amount_paid'),
                                        DB::raw("(SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=transactions.id) as amount_paid")
                                )
                                ->get();


                foreach ($transactions_vendor_credit_memo as $transaction) {
                    $ledger[] = [
                        't_date' => $this->transactionUtil->format_date($transaction->transaction_date, true),
                        'invoice_no' => $transaction->ref_no,
                        'type' => $transaction->type,
                        'final_total' => $transaction->final_total,
                        'credit' => $transaction->final_total - $transaction->amount_paid,
                        'transaction_id' => $transaction->id
                    ];
                }
            }
            return view('transaction_payment.pay_supplier_due_modal')
                        ->with(compact('contact_details', 'payment_types', 'payment_line', 'due_payment_type', 'ob_due', 'amount_formated', 'accounts','supplier_list','customer_list','sells','ledger'));
        }
    }

    private function __transactionQuery($contact_id,$type="")
    {

        $business_id = request()->session()->get('user.business_id');
        // $transaction_type_keys = array_keys(Transaction::transactionTypes());
        // dd($business_id);
        $query = Transaction::where('transactions.contact_id', $contact_id)
                        ->where('transactions.business_id', $business_id)
                        ->where('status', '!=', 'draft')
                        ->where('payment_status', '!=', 'paid');
        if($type!="")
        {
            $query->where('type', $type);
        }

                        // dd($query);
        return $query;
    }

    /**
     * Adds Payments for Contact due
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postPayContactDue(Request  $request)
    {

        // return 123;
        if (!auth()->user()->can('purchase.create') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            if(isset($request->add_advance)){
                //Add transaction
                $business_id = auth()->user()->business_id;
                $user_id = request()->session()->get('user.id');
                $transaction_data = [
                    "type" => "advance",
                    "business_id" => $business_id,
                    "final_total" => $request->amount,
                    "exchange_rate" =>1,
                    "created_by" =>$user_id,
                ];
                $tp = Transaction::create($transaction_data);
                $request->transaction_id = $tp->id;
                $this->transactionUtil->payContact($request);
            }
            else
            {
                $credit_memo_type = "";

                if($request->due_payment_type == "sell" || $request->due_payment_type == "sell_return")
                {
                    $credit_memo_type = "sell_return";
                }

                if($request->due_payment_type == "purchase" || $request->due_payment_type == "purchase_return")
                {
                    $credit_memo_type = "purchase_return";
                }

                if(isset($request->sell_return_check) && !empty($request->sell_return_check) && !isset($request->add_advance) && $request->method == 'credit_memo' && isset($request->sell_check) && !empty($request->sell_check))
                {
                    foreach($request->sell_return_check as $id=>$amount)
                    {
                        if(!empty($amount))
                        {
                            $this->transactionUtil->payContactForSellOrPurchaseReturn($request,true,$id,$amount,$credit_memo_type);
                        }
                    }

                    if(isset($request->transaction_ids) && !empty($request->transaction_ids))
                    {
                        if(isset($request->discount) && !empty($request->discount))
                        {
                            $this->transactionUtil->payContactForSellOrPurchase($request,true,$request->transaction_ids,$request->sell_check,$request->discount);
                        }
                        else
                        {
                            $this->transactionUtil->payContactForSellOrPurchase($request,true,$request->transaction_ids,$request->sell_check);
                        }
                    }
                }
                elseif(isset($request->sell_check) && !empty($request->sell_check) && !isset($request->add_advance))
                {

                    if(isset($request->transaction_ids) && !empty($request->transaction_ids))
                    {

                        if(isset($request->discount) && !empty($request->discount))
                        {
                            $this->transactionUtil->payContactForSellOrPurchase($request,true,$request->transaction_ids,$request->sell_check,$request->discount);
                        }
                        else
                        {
                            $this->transactionUtil->payContactForSellOrPurchase($request,true,$request->transaction_ids,$request->sell_check);
                        }
                    }
                }
                elseif(isset($request->sell_return_check) && !empty($request->sell_return_check) && !isset($request->add_advance) && $request->method == 'credit_memo')
                {
                    foreach($request->sell_return_check as $id=>$amount)
                    {
                        if(!empty($amount))
                        {
                            $this->transactionUtil->payContactForSellOrPurchaseReturn($request,true,$id,$amount,$credit_memo_type);
                        }
                    }
                }
                else
                {
                    $this->transactionUtil->payContact($request);
                }
            }

            DB::commit();
            $output = ['success' => true,
                            'msg' => __('purchase.payment_added_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                          'msg' => "File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage()
                      ];
        }
        return 1;

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * view details of single..,
     * payment.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function viewPayment($payment_id)
    {
        if (!auth()->user()->can('purchase.payments') && !auth()->user()->can('sell.payments')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('business.id');
            $single_payment_line = TransactionPayment::findOrFail($payment_id);

            $transaction = null;
            if (!empty($single_payment_line->transaction_id)) {
                $transaction = Transaction::where('id', $single_payment_line->transaction_id)
                                ->with(['contact', 'location', 'transaction_for'])
                                ->first();
            } else {
                $transaction = TransactionPayment::where('business_id', $business_id)
                        ->where('parent_id', $payment_id)
                        ->with(['transaction', 'transaction.contact', 'transaction.location', 'transaction.transaction_for'])
                        ->first();
                // $transaction = $child_payment->transaction;
            }

            $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

            $activity_logs = Delpaymentslog::join('users','users.id','=','delpaymentslog.created_by')
              ->where('payment_id',$payment_id)
              ->whereIn('description', ['added', 'edited'])
                ->select('delpaymentslog.created_at as datetime',
                    'delpaymentslog.message as message',
                    'delpaymentslog.description as description',
                    'users.first_name as first_name'
                )
                ->get();

            return view('transaction_payment.single_payment_view')
                    ->with(compact('single_payment_line', 'transaction', 'payment_types','activity_logs'));
        }
    }

    /**
     * Retrieves all the child payments of a parent payments
     * payment.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showChildPayments($payment_id)
    {
        if (!auth()->user()->can('purchase.payments') && !auth()->user()->can('sell.payments')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('business.id');

            $child_payments = TransactionPayment::where('business_id', $business_id)
                                                    ->where('parent_id', $payment_id)
                                                    ->with(['transaction', 'transaction.contact'])
                                                    ->get();

            $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

            return view('transaction_payment.show_child_payments')
                    ->with(compact('child_payments', 'payment_types'));
        }
    }

    /**
    * Retrieves list of all opening balance payments.
    *
    * @param  int  $contact_id
    * @return \Illuminate\Http\Response
    */

    public function getOpeningBalancePayments($contact_id)
    {
        if (!auth()->user()->can('purchase.payments') && !auth()->user()->can('sell.payments')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('business.id');
        if (request()->ajax()) {
            $query = TransactionPayment::leftjoin('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'opening_balance')
                ->where('t.contact_id', $contact_id)
                ->where('transaction_payments.business_id', $business_id)
                ->select(
                    'transaction_payments.amount',
                    'method',
                    'paid_on',
                    'transaction_payments.payment_ref_no',
                    'transaction_payments.document',
                    'transaction_payments.id',
                    'cheque_number',
                    'card_transaction_number',
                    'bank_account_number'
                )
                ->groupBy('transaction_payments.id');


            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            return Datatables::of($query)
                ->editColumn('paid_on', '{{@format_datetime($paid_on)}}')
                ->editColumn('method', function ($row) {
                    $method = __('lang_v1.' . $row->method);
                    if ($row->method == 'cheque') {
                        $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method = __('lang_v1.custom_payment_1') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method = __('lang_v1.custom_payment_2') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method = __('lang_v1.custom_payment_3') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } else {
                        $method = '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    }
                    return $method;
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency paid-amount" data-orig-value="' . $row->amount . '" data-currency_symbol = true>' . $row->amount . '</span>';
                })
                ->addColumn('action', '<button type="button" class="btn btn-primary btn-xs view_payment" data-href="{{ action("TransactionPaymentController@viewPayment", [$id]) }}"><i class="fas fa-eye"></i> @lang("messages.view")
                    </button> <button type="button" class="btn btn-info btn-xs edit_payment"
                    data-href="{{action("TransactionPaymentController@edit", [$id]) }}"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    &nbsp; <button type="button" class="btn btn-danger btn-xs delete_payment"
                    data-href="{{ action("TransactionPaymentController@destroy", [$id]) }}"
                    ><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</button> @if(!empty($document))<a href="{{asset("/uploads/documents/" . $document)}}" class="btn btn-success btn-xs" download=""><i class="fa fa-download"></i> @lang("purchase.download_document")</a>@endif')
                ->rawColumns(['amount', 'method', 'action'])
                ->make(true);
        }
    }

    // added by developer 1
    public function del_payments_log($transacion_payment,$reason="")
    {
        if(!empty($transacion_payment))
        {
            $transaction = Transaction::where('id', $transacion_payment->transaction_id)->with(['business', 'location'])->first();

            $transaction_id = "";
            $location_id = "";
            $invoice_no = "";

            if (!empty($transaction)) {
                $transaction_id = $transaction->id;
                $location_id = $transaction->location_id;
                $invoice_no = $transaction->invoice_no;
            }

            $contact = Contact::where('id', $transacion_payment->payment_for)->first();
            $user_del_id = request()->session()->get('user.id');

                $contact_type = "";

                if(!empty($contact))
                {
                    $contact_type= $contact->type;
                }

                $delpaymentslog = Delpaymentslog::create([
                'payment_id' => $transacion_payment->id,
                'transaction_id' => $transaction_id,
                'description' => 'deleted',
                'business_id' => $transacion_payment->business_id,
                'location_id' => $location_id,
                'contact_id' => $transacion_payment->payment_for,
                'contact_type' => $contact_type,
                'reason' => $reason,
                'is_return' => $transacion_payment->is_return,
                'amount' => $transacion_payment->amount,
                'method' => $transacion_payment->method,

                'transaction_no' => $transacion_payment->transaction_no,
                'card_transaction_number' => $transacion_payment->card_transaction_number,
                'card_number' => $transacion_payment->card_number,
                'card_type' => $transacion_payment->card_type,
                'card_holder_name' => $transacion_payment->card_holder_name,
                'card_month' => $transacion_payment->card_month,
                'card_year' => $transacion_payment->card_year,
                'card_security' => $transacion_payment->card_security,
                'cheque_number' => $transacion_payment->cheque_number,
                'bank_account_number' => $transacion_payment->bank_account_number,

                'paid_on' => $transacion_payment->paid_on,
                'is_advance' => $transacion_payment->is_advance,
                'advance_amt' => $transacion_payment->advance_amt,
                'invoice_no' => $invoice_no,
                'parent_id' => $transacion_payment->parent_id,
                'note' => $transacion_payment->note,
                'document' => $transacion_payment->document,
                'payment_ref_no' => $transacion_payment->payment_ref_no,
                'account_id' => $transacion_payment->account_id,
                'discount_type' => $transacion_payment->discount_type,
                'discount_amount' => $transacion_payment->discount_amount,
                'created_by' => $transacion_payment->created_by,
                'deleted_by' => $user_del_id
                ]);
        }
    }
    //Reconcile payments
    public function getReconcilePayments($contact_id)
    {
        if (request()->ajax()) {
        $business_id = request()->session()->get('user.business_id');
        $transactions = $this->__ReconcileTransactionQuery($contact_id)
                            ->with(['location'])->get();
        $invoice_type = request()->input('type');
        if(empty($invoice_type))
        {
            $invoice_type = 'sell';
        }
        $invoice_sum = $transactions->where('type', $invoice_type)->sum('final_total');
        $total_discount = 0;

        if($invoice_type == 'purchase')
        {
            $expense_sum = $transactions->where('type', 'expense')->sum('final_total');
            $total_invoice = $invoice_sum + $expense_sum;
        }
        else
        {
            $total_discount = $transactions->where('type', $invoice_type)->sum('discount_amount');
            $total_invoice = $invoice_sum + $total_discount;
        }

        ////for payments list
        $reconcile_payments = $this->__ReconcilePaymentsQuery($contact_id)
                        ->select('transaction_payments.*','t.contact_id as transaction_contact_id','transaction_payments.payment_for as payment_contact_id','t.invoice_no','t.ref_no','f.name as payment_for_name','transaction_payments.amount as payment_for_amount','c.name as transaction_contact_name', 't.final_total',DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as created_by",'t.type as transaction_type','t.discount_type','t.discount_type','t.total_before_tax'))
                        ->get();

        $payments = $this->__paymentQuery($contact_id)
                        ->select('transaction_payments.*', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no')
                        ->get();

        $total_invoice_paid = $payments->where('transaction_type', 'sell')->where('is_return', 0)->sum('amount');
        $total_sell_change_return = $payments->where('transaction_type', 'sell')->where('is_return', 1)->sum('amount');
        $total_sell_change_return = !empty($total_sell_change_return) ? $total_sell_change_return : 0;
        $total_invoice_paid -= $total_sell_change_return;
        $total_purchase_paid = $payments->where('transaction_type', 'purchase')->where('is_return', 0)->sum('amount');
        $total_sell_return_paid = $payments->where('transaction_type', 'sell_return')->sum('amount');
        $total_purchase_return_paid = $payments->where('transaction_type', 'purchase_return')->sum('amount');
        $total_paid = $total_invoice_paid + $total_purchase_paid - $total_purchase_return_paid;
        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
        return view('contact.reconcile_payments')
            ->with(compact(
                'reconcile_payments','total_invoice','total_paid','total_discount','invoice_type','payment_types'
            ));
        }
    }

    /**
     * Query to get reconcile payments details for a customer
     *
     */
    private function __ReconcilePaymentsQuery($contact_id)
    {

        $query = TransactionPayment::leftJoin(
            'transactions as t',
            'transaction_payments.transaction_id',
            '=',
            't.id'
        )
            ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
            ->leftJoin('users as u', 'transaction_payments.created_by', '=', 'u.id')
            ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
            ->leftjoin('contacts as f', 'f.id', '=', 'transaction_payments.payment_for')

            ->where(function ($query) use ($contact_id) {
                $query->where('transaction_payments.payment_for', $contact_id);
                $query->orWhere('t.contact_id', $contact_id);
            });
            //->where('transaction_payments.payment_for', $contact_id);
            //->where('t.contact_id', $contact_id);
            //->whereNull('transaction_payments.parent_id');

        $query->whereDate('paid_on', '>=', '2021-01-01');

        /*if (!empty($start)  && !empty($end)) {
            $query->whereDate('paid_on', '>=', $start)
                        ->whereDate('paid_on', '<=', $end);
        }

        if (!empty($start)  && empty($end)) {
            $query->whereDate('paid_on', '<', $start);
        }*/

        return $query;
    }

    /**
     * Query to get reconcile transaction totals for a customer
     *
     */
    private function __ReconcileTransactionQuery($contact_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $transaction_type_keys = array_keys(Transaction::transactionTypes());

        $query = Transaction::where('transactions.contact_id', $contact_id)
                        ->where('transactions.business_id', $business_id)
                        ->where('status', '!=', 'draft')
                        ->whereIn('type', $transaction_type_keys);
        $query->whereDate(
                'transactions.transaction_date',
                '>=',
                '2021-01-01');

        return $query;
    }

    private function __paymentQuery($contact_id)
    {
        $business_id = request()->session()->get('user.business_id');

        $query = TransactionPayment::leftJoin(
            'transactions as t',
            'transaction_payments.transaction_id',
            '=',
            't.id'
        )
            ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
            ->where('transaction_payments.payment_for', $contact_id);
            //->whereNull('transaction_payments.parent_id');

       $query->whereDate('paid_on', '>=', '2021-01-01');

        return $query;
    }
}
