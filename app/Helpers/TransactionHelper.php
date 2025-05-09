<?php

namespace App\Helpers;

use App\Business;
use App\Contact;
use App\Notifications\InvoiceDoubledMail;
use App\Transaction;
use App\TransactionPayment;
use App\Utils\TransactionUtil;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TransactionHelper
{
    public static function checkMismatchInvoices(){

        $whitelisted_ids = [
            17310,17201,17027,15694,15692,15625,15619,15561,14325,14235,
            12672,12368,11481,11471,10534,10157,10013,9325,8519,8405,
            8367,8310,6275,6272,6265,6090,5879,5852,5497,5489,18776,18774,18840,
            18922,19521,20078,22779,23143,23355
        ];

        $records = DB::table('mismatch_totals')->whereNotIn('id', $whitelisted_ids)->get();
        if(count($records) > 0){
            try{
                $business = Business::find(4);
                $data['email_settings'] = $business->email_settings;
                $data['to_email'] = 'jp94258@gmail.com';
                $data['email_body'] = count($records) . " invoices have mismatch total. Invoice No: " . implode(" , ", array_column($records->toArray(), 'invoice_no'));

                Notification::route('mail', $data['to_email'])->notify(new InvoiceDoubledMail($data));
            }
            catch(Exception $e){
                Log::emergency("Notif Email Send Fail- File:" . $e->getFile(). " Line:" . $e->getLine(). " Message: " . $e->getMessage());
            }
        }
    }

    /**
     * Transfer overpaid invoice balance to advance
     *
     * - Only processed if any single payment to this invoice is greater then the difference amount
     */
    public static function balanceTransfer($id){
        $transaction = DB::table('transactions as t')
            ->join('transaction_payments as tp','t.id','=','tp.transaction_id')
            ->select([
                't.*',
                DB::raw('SUM(IF(tp.is_return = 1,(-1 * tp.amount),tp.amount)) as paid_total'),
            ])
            ->where('t.id', $id)
            ->groupBy('t.id')
            ->having('t.final_total','<',DB::raw('paid_total'))
        ->first();

        if(!empty($transaction)){
            $diff_amount = round($transaction->paid_total - $transaction->final_total, 2);

            $target_payment = TransactionPayment::where('transaction_id', $transaction->id)
                ->where('parent_id','<>',0)
                ->whereNotNull('parent_id')
                ->where('amount', '>', $diff_amount)
                ->orderBy('amount')
            ->first();

            if(!empty($target_payment)){
                $target_payment->amount = round($target_payment->amount - $diff_amount, 2);
                $target_payment->save();

                $contact = Contact::find($transaction->contact_id);
                $contact->balance = round($contact->balance + $diff_amount, 2);
                $contact->save();

                return "Excess Balance transferred to Advance. Amount: " . $diff_amount . ", Payment Ref: " . $target_payment->payment_ref_no;
            }
            else{
                return "Payment greater than diff amount not found. Diff Amount: " . $diff_amount;
            }
        }
        else{
            return "Transaction conditions does not match";
        }
    }

    public static function updateContactsTransactionDate(){
        $contacts = Contact::where('type','customer')->get();
        foreach ($contacts as $contact) {
            $latest_transaction = Transaction::where('contact_id', $contact->id)
                ->where('type','sell')
                ->where('status','final')
                ->orderBy('transaction_date', 'desc')
            ->first();

            if (!empty($latest_transaction->transaction_date)) {
                $contact->update(['last_transaction_date' => $latest_transaction->transaction_date ?? null]);
            }
            else{
                $contact->update(['last_transaction_date' => null]);
            }
        }
    }

    public static function checkStatements($page = 1){
        $business_id = 4;
        $start = '2021-01-01';
        $end = '2025-12-31';
        // ->where('type','supplier')
        $offset = ($page - 1) * 1000;
        $contacts = Contact::where('contact_status', 'active')->offset($offset)->take(1000)->get();
        // dd($contacts);
        $transactionUtil = new TransactionUtil();
        // dd($contacts);
        foreach ($contacts as $contact) {

            $contact_id = $contact->id;
            $advance_balance = $contact->balance;

            $ledger_details = $transactionUtil->getLedgerDetails($contact_id, $start, $end, $advance_balance);
            // dd($ledger_details);

            // if($ledger_details['purchase_return'] < 0 || $ledger_details['total_return'] < 0){
            //     echo $contact->type . ";" . $contact->id . ";" . $contact->name . ";" . ";" . "<br/>";
            // }

            $balance = 0;
            // Process ledger details
            for ($i = count($ledger_details['ledger']) - 1; $i >= 0; $i--) {

                if ($contact->type == 'supplier') {
                    if ($ledger_details['ledger'][$i]['total'] > 0 && ( $ledger_details['ledger'][$i]['type'] == 'expense' || $ledger_details['ledger'][$i]['type'] == 'Purchase')) {
                        $balance += $ledger_details['ledger'][$i]['total'];
                        $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'];
                    }
                    elseif ($ledger_details['ledger'][$i]['total'] > 0 && $ledger_details['ledger'][$i]['type'] == 'Purchase Return') {
                        $balance -= $ledger_details['ledger'][$i]['total'];
                        $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
                    }
                    elseif ($ledger_details['ledger'][$i]['payment_method'] == 'Advance') {
                        // Do nothing
                    }
                    elseif ($ledger_details['ledger'][$i]['type'] == 'Payment') {
                        $tr_type = $ledger_details['ledger'][$i]['transaction_type'] ?? '';
                        if ($tr_type == 'purchase_return' || $tr_type == 'purchase_return_parent') {
                            $balance += (float)$ledger_details['ledger'][$i]['credit'];
                        } else {
                            if ($ledger_details['ledger'][$i]['debit'] <= 0) {
                                $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['credit'];
                            }
                            $balance -= (float)$ledger_details['ledger'][$i]['debit'];
                            $ledger_details['ledger'][$i]['credit'] = '';
                        }
                    }
                }
                else {
                    if ($ledger_details['ledger'][$i]['type'] == 'Payment' && $ledger_details['ledger'][$i]['transaction_type'] == 'expense') {
                        $ledger_details['ledger'][$i]['debit'] = "";
                    }
                    if ($ledger_details['ledger'][$i]['type'] == 'Sell' || $ledger_details['ledger'][$i]['type'] == 'Opening Balance') {
                        $balance += $ledger_details['ledger'][$i]['total'];
                    } else {
                        if ($ledger_details['ledger'][$i]['ref_no'] && $ledger_details['ledger'][$i]['is_advance'] == 1 && $ledger_details['ledger'][$i]['advance_amt'] > 0 && $i != count($ledger_details['ledger']) - 1) {
                            if ($ledger_details['ledger'][$i]['transaction_id'] == $ledger_details['ledger'][$i + 1]['transaction_id'] && $ledger_details['ledger'][$i + 1]['payment_status'] == '') {
                                $ledger_details['ledger'][$i + 1]['others'] = $ledger_details['ledger'][$i]['others'];
                            }
                        }
                        elseif ($ledger_details['ledger'][$i]['payment_method'] == 'Advance' || $ledger_details['ledger'][$i]['payment_method'] == 'Credit') {
                            // Do nothing
                        }
                        else {
                            if (($ledger_details['ledger'][$i]['total'] > 0 && $ledger_details['ledger'][$i]['type'] == 'expense') || $ledger_details['ledger'][$i]['type'] == 'Sell Return') {
                                // Do nothing
                            }
                            elseif ($ledger_details['ledger'][$i]['total'] > 0) {
                                $balance -= $ledger_details['ledger'][$i]['total'];
                            }
                            elseif ($ledger_details['ledger'][$i]['credit'] > 0) {
                                $balance -= $ledger_details['ledger'][$i]['credit'];
                            }
                            elseif ($ledger_details['ledger'][$i]['credit'] < 0) {
                                $balance -= $ledger_details['ledger'][$i]['credit'];
                            }
                            elseif ($ledger_details['ledger'][$i]['type'] == 'Purchase' && $ledger_details['ledger'][$i]['total'] < 0) {
                                $balance -= $ledger_details['ledger'][$i]['total'];
                            }
                        }
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Sell Return' && $ledger_details['ledger'][$i]['transaction_type'] == 'not_payment') {
                        $balance -= $ledger_details['ledger'][$i]['total'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Payment' && $ledger_details['ledger'][$i]['transaction_type'] == 'sell_return') {
                        $balance += $ledger_details['ledger'][$i]['debit'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Payment') {
                        $ledger_details['ledger'][$i]['total'] = $ledger_details['ledger'][$i]['credit'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'Sell' || $ledger_details['ledger'][$i]['type'] == 'Opening Balance') {
                        $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
                    }
                    elseif ($ledger_details['ledger'][$i]['type'] == 'Sell Return') {
                        $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'];
                    }
                    else {
                        $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['credit'];
                    }

                    if ($ledger_details['ledger'][$i]['type'] == 'expense') {
                        $balance += $ledger_details['ledger'][$i]['total'];
                        if ($ledger_details['ledger'][$i]['total'] >= 0) {
                            $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
                        }
                        else {
                            $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'] * -1;
                        }
                        $ledger_details['ledger'][$i]['type'] = "Adjustment";
                    }
                }

                $ledger_details['ledger'][$i]['balance'] = $balance;
            }
            // dd($ledger_details);
            $statement_bal = round($ledger_details['ledger'][1]['balance'] ?? 0, 2);
            $summary_bal = round($ledger_details['balance_due'] ?? 0, 2);

            if($statement_bal != $summary_bal){
                echo $contact->type . ";" . $contact->id . ";" . $contact->name . ";" . $statement_bal . ";" . $summary_bal . "<br/>";
            }
        }

    }
}