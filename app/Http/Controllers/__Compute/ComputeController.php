<?php

namespace App\Http\Controllers\__Compute;

use App\Barcode;
use App\Contact;
use App\Exports\QrExport;
use App\Helpers\TransactionHelper;
use App\Http\Controllers\Controller;
use App\Models\AuditTransaction;
use App\Models\SmartSyncValue;
use App\Product;
use App\PurchaseActivityLog;
use App\SellingPriceGroup;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\Variation;
use App\VariationGroupPrice;
use App\VendorcreditmemoActivityLog;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\RecalculateLog;
use App\Utils\TransactionUtil;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ComputeController extends Controller{

    public function test(){

        // foreach([] as $id){
        //     $transaction = Transaction::find($id);
        //     $transaction->recalculate();
        // }
    }

    public function statements(){

        //Set maximum php execution time
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);

        echo "<br/>Statement check:<br/>";
        TransactionHelper::checkStatements(request()->get('page', 1));

        echo "<br/>Over Payment check:<br/>";
        $op = DB::table('overpaid_transactions')->get();
        foreach($op as $opt){
            echo "T:" . $opt->id . " C:" . $opt->contact_id . " INV:" . $opt->invoice_no . " DIFF:" . round($opt->final_total - $opt->paid_total, 2) . "<br/>";
        }

        echo "<br/>Under Payment check:<br/>";
        $op = DB::table('underpaid_transactions')->get();
        foreach($op as $opt){
            echo "T:" . $opt->id . " C:" . $opt->contact_id . " INV:" . $opt->invoice_no . " DIFF:" . round($opt->final_total - $opt->paid_total, 2) . "<br/>";
        }

        echo "<br/>Neg Adv check:<br/>";
        $op = Contact::where('balance','<',0)->where('contact_status','active')->get();
        foreach($op as $opt){
            echo "CID:" . $opt->id . " CNO:" . $opt->contact_id . " BAL:" . $opt->balance . " NAME:" . $opt->name . "<br/>";
        }
    }

    public function test2(){

        $contact_id = 1272;

        $payments = TransactionPayment::where('payment_for',$contact_id)
            ->whereNull('transaction_id')
            ->where(function($q){
                $q->where('parent_id','0')
                    ->orWhere('parent_id','')
                ->orWhereNull('parent_id');
            })
        ->get();

        $adv_parent = 0;
        $adv_child = 0;
        $etc_parent = 0;
        $etc_child = 0;
        $cr_parent = 0;
        $cr_child = 0;

        $per_pay = 0;
        $per_pay2 = 0;
        foreach($payments as $payment){
            $amount = $payment->amount;
            if($payment->is_return == 1){
                $amount = $amount * -1;
            }

            if($payment->method == 'advance'){
                $adv_parent += $amount;
            }
            elseif($payment->method == 'credit_memo' || $payment->method == 'credit'){
                $cr_parent += $amount;
            }
            else{
                $etc_parent += $amount;
                $per_pay = $amount;
            }
            $per_pay2 = 0;

            $child_payments = TransactionPayment::where('parent_id',$payment->id)->get();
            foreach($child_payments as $child_payment){
                if($child_payment->payment_for != $payment->payment_for){
                    dd('mismatch contact payments');
                }
                else{
                    $amount2 = $child_payment->amount;
                    if($child_payment->is_return == 1){
                        $amount2 = $amount2 * -1;
                    }

                    if($child_payment->method == 'advance'){
                        $adv_child += $amount2;
                    }
                    elseif($child_payment->method == 'credit_memo' || $child_payment->method == 'credit'){
                        $cr_child += $amount2;
                    }
                    else{
                        $etc_child += $amount2;
                        $per_pay2 += $amount2;
                    }
                }
            }

            if(round($per_pay, 2) != round($per_pay2, 2)){
                // dd($payment);
            }


        }

        $etc_diff = $etc_parent - $etc_parent + ($adv_parent - $adv_child);

        $cr = Transaction::where('type','sell_return')->where('contact_id',$contact_id)->sum('final_total');
        $cr_diff = $cr - $cr_parent;
        $cr_diss = $cr_parent - $cr_child;

        echo "Total Etc Parent: $etc_parent <br/>";
        echo "Total Etc Child: $etc_child <br/>";
        echo "Total Etc Diff: ". ($etc_parent-$etc_child) . "<br/><br/>";

        echo "Total adv Parent: $adv_parent <br/>";
        echo "Total adv Child: $adv_child <br/>";
        echo "Total adv Diff: ". ($adv_parent-$adv_child) . "<br/><br/>";

        echo "Total cm: $cr <br/>";
        echo "Total cm Parent: $cr_parent <br/>";
        echo "Payment cm Diff: ". ($cr-$cr_parent) . "<br/><br/>";

        echo "Total cm Parent: $cr_parent <br/>";
        echo "Total cm Child: $cr_child <br/>";
        echo "Total cm Diff: ". ($cr_parent-$cr_child) . "<br/><br/>";


        // echo "Total x for statement: ". ($etc_child + $adv_child + $cr_child) ." <br/>";
        // echo "Total x for spr: ". ($etc_parent + $adv_parent + $cr_parent) ." <br/>";
        // echo "<br/>";

        if($etc_parent-$etc_child != $adv_parent){
            echo "-- Advance balance discrepancy <br/>";
        }



        // dd($etc_diff, $cr_diff, $cr_diss);



        // Log::info('Got order bruv');
        // $last_sell = DB::table('transaction_sell_lines as tsl')
        //     ->join('transactions as t','t.id','=','tsl.transaction_id')
        //     ->where('t.type','sell')
        //     ->where('t.status','final')
        //     ->where('t.contact_id',1765)
        //     ->where('tsl.variation_id',11301)
        //     // ->when(!empty($comparable_date), function($query) use($comparable_date){
        //     //     $query->where('t.transaction_date','>=',$comparable_date);
        //     // })
        //     ->orderBy('tsl.id','desc')
        // ->get();

        // dd($last_sell->toArray());


        // $transaction = Transaction::find();
        // $count = 0;
        // foreach($transaction->sell_lines()->get() as $line){
        //     // $count++;
        //     $var = Variation::find($line->variation_id);
        //     $vld = VariationGroupPrice::where('variation_id',$var->id)->where('price_group_id',69)->first();
        //     if(!empty($vld->price_inc_tax) && $vld->price_inc_tax == $line->unit_price_inc_tax){
        //         $count++;
        //     }
        //     else if(!empty($var->sell_price_inc_tax) && $var->sell_price_inc_tax == $line->unit_price_inc_tax){
        //         $count++;
        //     }
        // }
        // dd($count);

        // foreach([] as $id){
        //     $transaction = Transaction::find($id);
        //     $transaction->recalculate();
        // }

        // $products = Product::all();
        // $count = 0;
        // foreach($products as $product){
        //     $filePath = public_path('/uploads' . $product->main_image);
        //     if (File::exists($filePath)) {
        //         $fileSize = File::size($filePath);
        //         $maxSize = 1024 * 1024;
        //         if($fileSize > ($maxSize * 2)){
        //             $count++;
        //         }
        //     }
        // }
        // return $count;
    }


    public function wahaMessage(Request $request){
        if(empty(config('business-info.waha_base_url'))){
            return response(['success'=> 0, 'msg'=>'Service not available']);
        }

        if(empty($request->number) || empty($request->tid)){
            return response(['success'=> 0, 'msg'=>'Insufficent data']);
        }

        $transaction = Transaction::find($request->tid);
        if(!isset($transaction)){
            return response(['success'=> 0, 'msg'=>'Invoice not found']);
        }

        $transactionUtil = new TransactionUtil();
        $url = $transactionUtil->getInvoiceUrl($transaction->id, $transaction->business_id);
        $msg_url = 'https://invoicetopdf.com/';
        $log_slip_type = '';

        $tr_new = Transaction::find($transaction->id);
        if(empty($tr_new->invoice_token)){
            return response(['success'=> 0, 'msg'=>'Invoice token not found']);
        }
        if(empty($request->receipt_type)){
            $log_slip_type = 'Packing Slip';
            // $msg_url .= "ps/" . $tr_new->invoice_token;
            $msg_url .= "ps.php?invoice=" . $tr_new->invoice_token;
        }
        elseif($request->receipt_type == 'invoice'){
            $log_slip_type = 'Tax Invoice';
            // $msg_url .= "printemailinvoice/" . $tr_new->invoice_token . "/print";
            $msg_url .= "printemailinvoice.php?invoice=" . $tr_new->invoice_token;
        }
        elseif($request->receipt_type == 'blank_slip_without_price'){
            $log_slip_type = 'Blank Slip Without Price';
            // $msg_url .= "printemailinvoice/" . $tr_new->invoice_token . "/print";
            $msg_url .= "pswpl.php?invoice=" . $tr_new->invoice_token;
        }
        elseif($request->receipt_type == 'revision'){
            $log_slip_type = 'Invoice';
            // $msg_url .= "invoice/" . $tr_new->invoice_token;
            $msg_url .= "viewinvoice.php?invoice=" . $tr_new->invoice_token;
        }
        else{
            return response(['success'=> 0, 'msg'=>'Receipt not found']);
        }

        $number = $request->number;
        $chat_id = $number . '@c.us';
        $msg = "Hi there, this is your invoice: " . $msg_url;

        $client = new Client();
        $proceed_with_request = false;
        $number_not_active = false;
        $waha_base_url = config('business-info.waha_base_url');
        $waha_session = 'default';
        $output = [
            'success' => 0,
            'msg' => 'Something went wrong'
        ];
        try{
            $response = $client->get($waha_base_url . '/api/sessions/'.$waha_session);
            $check_content = $response->getBody()->getContents();
            if(!empty($check_content)){
                $check_data = json_decode($check_content);
                // dd($check_data);
                if(!empty($check_data->status) && $check_data->status == 'WORKING'){

                    $response = $client->get($waha_base_url . '/api/contacts/check-exists?phone='.$number.'&session='.$waha_session);
                    $check_content = $response->getBody()->getContents();
                    if(!empty($check_content)){
                        $check_data = json_decode($check_content);
                        if(!empty($check_data->numberExists)){
                            $proceed_with_request = true;
                        }
                        else{
                            $number_not_active = true;
                        }
                    }

                }
            }

            if($proceed_with_request){
                $response = $client->post($waha_base_url . '/api/'.$waha_session.'/presence', [
                    'json' => [
                        'chatId' => $chat_id,
                        "presence" => "typing"
                    ]
                ]);
                sleep(rand(2,6));
                $response = $client->post($waha_base_url . '/api/'.$waha_session.'/presence', [
                    'json' => [
                        'chatId' => $chat_id,
                        "presence" => "paused"
                    ]
                ]);
                $response = $client->post($waha_base_url . '/api/sendText', [
                    'json' => [
                        'session' => $waha_session,
                        'chatId' => $chat_id,
                        "text" => $msg
                    ]
                ]);

                $output['success'] = 1;
                $output['msg'] = 'Message sent, waiting for confirmation';

                $check_content = $response->getBody()->getContents();
                if(!empty($check_content)){
                    $check_data = json_decode($check_content);
                    if(!empty($check_data->id->id)){
                        $output['msg'] = 'Message sent successfully';
                    }
                }

            }
            else{
                $output['msg'] = $number_not_active ? 'Number is not on whatsapp' : 'Please start the session';
            }
            $message = $log_slip_type . " sent, On ". $number .", Status: " . $output['msg'] . ", ";
            $transactionUtil->Delinvoicelog('send_whatsapp',auth()->user()->id,$transaction->id,$message);
        }
        catch(Exception $e){
            Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }
        return response($output);
    }

    public function balanceTransferOnContact($contact_id){
        $transactions = DB::table('transactions as t')
            ->join('transaction_payments as tp','t.id','=','tp.transaction_id')
            ->select([
                't.*',
                DB::raw('SUM(IF(tp.is_return = 1,(-1 * tp.amount),tp.amount)) as paid_total'),
            ])
            ->where('t.contact_id', $contact_id)
            ->groupBy('t.id')
            ->having('t.final_total','<',DB::raw('paid_total'))
        ->get();

        foreach ($transactions as $transaction) {
            echo "Processing Invoice: " . $transaction->invoice_no ."<br/>";
            echo $this->balanceTransfer($transaction->id);
            echo "<br/><br/>";
        }
    }

    public function balanceTransfer($id){
        return TransactionHelper::balanceTransfer($id);
    }

    public function setSmartValue_custom(Request $request){
        if(!auth()->user()->roles()->whereIn('id',[9,15])->exists() && !empty($request->set_key)){
            return json_encode('Operation Not Allowed');
        }
        $smart_value = $request->set_value ?? 1;
        if($smart_value != 0 && $smart_value != 1){
            $smart_value = 1;
        }
        return SmartSyncValue::setSmartValue('custom_'.$request->set_key, $smart_value);
    }

    public function updateSalePriceGroup($id,Request $request){

        $transaction = Transaction::find($id);
        $customer = Contact::find($request->customer);

        $sell_price_group = SellingPriceGroup::find($customer->customer_group_id ?? 0);

        $payments = TransactionPayment::where('transaction_id',$transaction->id ?? 0)->get();

        if(isset($customer) && isset($transaction) && count($payments) < 1){
            $logMessage = '';

            $transaction_sell_lines = TransactionSellLine::where('transaction_id',$transaction->id)->get();

            $final_total_before_dis = 0;
            foreach($transaction_sell_lines as $sell_line){

                $conditions = [];
                $conditions['variation_id'] = $sell_line->variation_id;
                $conditions['price_group_id'] = $sell_price_group->id ?? 0;
                $groupprice = VariationGroupPrice::where($conditions)->first();
                $normalprice = Variation::find($sell_line->variation_id);

                $newprice = !empty($groupprice->price_inc_tax) ? $groupprice->price_inc_tax : $normalprice->default_sell_price;
                $final_total_before_dis += ($newprice * $sell_line->quantity);

                if ($sell_line->unit_price_before_discount != $newprice) {
                    $logMessage .= 'Sale line Sales price ($' . $this->num_uf($sell_line->unit_price_before_discount) . ' --> $' . $this->num_uf($newprice) . ') of ' . $sell_line->product->name . ', ';
                }

                TransactionSellLine::find($sell_line->id)->update([
                    'unit_price_before_discount' => $newprice,
                    'unit_price' => $newprice,
                    'unit_price_inc_tax' => $newprice
                ]);

            }

            $audit = new AuditTransaction();
            $audit->transaction_id = $transaction->id;
            $audit->old_value = $transaction->final_total;
            $audit->old_contact_id = $transaction->contact_id;
            $audit->old_selling_group_id = $transaction->selling_price_group_id ?? 0;
            $audit->user_id = Auth::user()->id;

            $transaction->total_before_tax = $final_total_before_dis;
            $transaction->final_total = $final_total_before_dis - $transaction->discount_amount + $transaction->shipping_charges;
            $transaction->selling_price_group_id = $sell_price_group->id ?? 0;
            $transaction->contact_id = $customer->id;
            $transaction->save();

            $audit->new_value = $transaction->final_total;
            $audit->new_contact_id = $transaction->contact_id;
            $audit->new_selling_group_id = $transaction->selling_price_group_id;
            $audit->save();

            if ($transaction->final_total != $audit->old_value) {
                $logMessage .= 'Total Payable ($' . $this->num_uf($audit->old_value) . ' --> $' . $this->num_uf($transaction->final_total) . '), ';
            }

            if ($transaction->selling_price_group_id != $audit->old_selling_group_id) {
                $new_group_name = !empty($sell_price_group->name) ? $sell_price_group->name : 'Default Group';
                $old_group = SellingPriceGroup::find($audit->old_selling_group_id ?? 0);
                $old_group_name = !empty($old_group->name) ? $old_group->name : 'Default Group';
                $logMessage .= 'Selling price group from ' . $old_group_name .  ' --> '. $new_group_name .' , ';
            }

            if ($transaction->contact_id != $audit->old_contact_id) {
                $logMessage .= 'Customer, ';
            }

            if (!empty($logMessage)) {
                $this->Recalculatelog('edited', Auth::user()->id, $transaction->id, $logMessage);
            }
        }

        return redirect()->back();
    }

    public function getGroupPrice($variation_id, $customer_id){
        $customer = Contact::find($customer_id);
        $group_id = $customer->customer_group_id ?? 0;
        $variation = Variation::find($variation_id);

        if(isset($variation)){
            $vgp = VariationGroupPrice::where('variation_id', $variation_id)
                ->where('price_group_id', $group_id)
            ->first();

            if(!empty($vgp->price_inc_tax)){
                return response(['success' => 1, 'price' => round($vgp->price_inc_tax, 2)]);
            }
            elseif(!empty($variation->sell_price_inc_tax)){
                return response(['success' => 1, 'price' => round($variation->sell_price_inc_tax, 2)]);
            }
        }
        return response(['success' => 0]);
    }

    public function getGroupDetails($customer_id){
        $customer = Contact::find($customer_id);
        $group_id = $customer->customer_group_id ?? 0;
        $group_name = 'Default Selling Price';
        $group = SellingPriceGroup::find($group_id);

        if(isset($group)){
            $group_name = $group->name;
        }
        return response(['group_id' => $group_id, 'group_name' => $group_name]);
    }

    public function num_uf($input_number, $currency_details = null)
    {
        $thousand_separator  = '';
        $decimal_separator  = '';

        if (!empty($currency_details)) {
            $thousand_separator = $currency_details->thousand_separator;
            $decimal_separator = $currency_details->decimal_separator;
        } else {
            $thousand_separator = session()->has('currency') ? session('currency')['thousand_separator'] : '';
            $decimal_separator = session()->has('currency') ? session('currency')['decimal_separator'] : '';
        }

        $num = str_replace($thousand_separator, '', $input_number);
        $num = str_replace($decimal_separator, '.', $num);

        return (float)$num;
    }

    public function Recalculatelog($type, $user_id, $transaction_id, $pro = '')
    {
        $user = auth()->user();
        $message = rtrim($pro, ',');
        $date = \Carbon::now()->toDateTimeString();
        $change_message = '';

        if (!empty($message)) {
            $change_message = $message . ' was changed by ' . $user->first_name . ' at ' . $date;
        }

        if (!empty($message) && $type == 'added') {
            $change_message = $message . ' was added by ' . $user->first_name . ' at ' . $date;
        }

        $log = new Recalculatelog();
        $log->user_id = $user_id;
        $log->transaction_id = $transaction_id;
        $log->description = $type;
        $log->message = $change_message;
        $log->save();
    }

    public function removeBackground(){
        $output = [
            'success' => 0,
            'msg' => 'No products found.'
        ];
        $ids = request()->input('ids');
        if(empty($ids)){
            return json_encode($output);
        }
        $product_ids = explode('-', $ids);
        $products = Product::whereIn('id',$product_ids)->whereNotNull('main_image')->get();

        $success_count = 0;
        $fail_count = 0;
        foreach($products as $product){
            $client = new Client();
            $response = $client->post('https://clipdrop-api.co/remove-background/v1', [
                'headers' => [
                    'x-api-key' => '395dd51f1a92a1142300079dcfa9d15ef5a0dca3bd9c883d9de557224bc870bd73bddedcb942bc7d72310747b2b2e7b3'
                ],
                'multipart' => [
                    [
                        'name' => 'image_file',
                        'contents' => fopen(public_path('uploads'.$product->main_image),'r')
                    ]
                ]
            ]);
            if($response->getStatusCode() == 200){
                $img_name_parts = explode("/",$product->main_image);
                $img_name = $img_name_parts[count($img_name_parts)-1];
                $img_save_name = "/api_imgs/".$img_name.".nobg.png";

                $remaining_credits = implode("_",$response->getHeader('x-remaining-credits'));
                $body = $response->getBody()->getContents();
                file_put_contents(public_path("uploads".$img_save_name),$body);
                $product->main_image = $img_save_name;
                $product->woocommerce_media_id = null;
                $product->save();

                SmartSyncValue::setSmartValue('clipdrop_remaining_credits',$remaining_credits);
                $success_count++;
                // $base64 = base64_encode($body);
                // $mime = "image/png";
                // $img = ('data:' . $mime . ';base64,' . $base64);
            }
            else{
                $fail_count++;
            }
        }
        if($success_count == 0){
            $output['success'] = 0;
            $output['msg'] = '0 Success, '.$fail_count.' Failed';
        }
        else{
            $output['success'] = 1;
            $output['msg'] = $success_count.' Success, '.$fail_count.' Failed';
        }
        return json_encode($output);
    }

    public function sendSmsApi(){
        $output = [
            'success' => false,
            'msg' => 'Insufficient data provided'
        ];

        $msg_content = request()->input('msg_content');
        $contact_no = request()->input('contact_no');
        if(empty($msg_content) || empty($contact_no)){
            return json_encode($output);
        }

        $output['msg'] = "Something went wrong";

        $transaction = Transaction::findOrFail($msg_content);
        $sell_lines = TransactionSellLine::where('transaction_id', $transaction->id)->get();

        $sms_content = "";
        foreach($sell_lines as $sell_line){
            $product = Product::find($sell_line->product_id);
            if(!empty($product)){
                $sms_content .= $product->name;
                $sms_content .= " ( Q." . round($sell_line->quantity) . " x $" . number_format($sell_line->unit_price, 2) . ")";
                $sms_content .= " $" . number_format($sell_line->unit_price * $sell_line->quantity, 2);
                $sms_content .= "\n\n";
            }
        }
        $sms_content .= "-----------------------------\n";
        $sms_content .= "Final Total $" . number_format($transaction->final_total, 2);

        $chunkSize = 1000;
        $length = strlen($sms_content);
        $pass = 0;
        $fail = 0;
        $last_error = '';
        for ($i = 0; $i < $length; $i += $chunkSize) {
            $chunk = substr($sms_content, $i, $chunkSize);

            $client = new Client();
            $response = $client->post('https://textbelt.com/text', [
                'form_params' => [
                    'phone' => '+'.$contact_no,
                    'message' => $chunk,
                    'key' => 'dcf42371bf1f42cd2b0e63edc1e7f84e4aad7dde4KxxN6Yf3rmHnDwzpkMHhPBYp',
                ]
            ]);

            $body = json_decode($response->getBody()->getContents());
            if(isset($body->quotaRemaining)){
                SmartSyncValue::setSmartValue('textbelt_remaining_credits',$body->quotaRemaining);
            }
            if(isset($body->error)){
                $last_error = $body->error;
                SmartSyncValue::setSmartValue('textbelt_last_error',$body->error);
            }

            if($body->success){
                $pass++;
            }
            else{
                $fail++;
            }
        }

        if($pass > 0 && $fail == 0){
            $output['success'] = true;
            $output['msg'] = "SMS has been sent";
        }
        elseif($pass > 0 && $fail > 0){
            $output['success'] = false;
            $output['msg'] = "SMS partially sent";
        }

        if(!empty($last_error)){
            $output['msg'] .= ' - ' . $last_error;
        }

        return json_encode($output);
    }

    public function markPaid($id){
        $transaction = Transaction::findOrFail($id);

        if($transaction->type == 'purchase'){
            $log = new PurchaseActivityLog();
        }
        else if($transaction->type == 'purchase_return'){
            $log = new VendorcreditmemoActivityLog();
        }
        else{
            return json_encode(['success' => 0, 'msg' => 'Not found']);
        }

        $transaction->payment_status = 'paid';
        $transaction->save();

        $log->user_id = auth()->user()->id;
        $log->transaction_id = $transaction->id;
        $log->description = "edited";
        $log->message = "Marked as Paid";
        $log->type = $transaction->type;
        $log->save();

        return json_encode(['success' => 1, 'msg' => 'Payment status updated', 'type' => $transaction->type]);
    }

    public function markDue($id){
        $transaction = Transaction::findOrFail($id);

        if($transaction->type == 'purchase'){
            $log = new PurchaseActivityLog();
        }
        else if($transaction->type == 'purchase_return'){
            $log = new VendorcreditmemoActivityLog();
        }
        else{
            return json_encode(['success' => 0, 'msg' => 'Not found']);
        }

        $transaction->payment_status = 'due';
        $transaction->save();

        $log->user_id = auth()->user()->id;
        $log->transaction_id = $transaction->id;
        $log->description = "edited";
        $log->message = "Marked as Due";
        $log->type = $transaction->type;
        $log->save();

        return json_encode(['success' => 1, 'msg' => 'Payment status updated', 'type' => $transaction->type]);
    }

    public function qrCreate(Request $request){
        $business_id = $request->session()->get('user.business_id');
        $barcode_settings = Barcode::where('business_id', $business_id)
            ->orWhereNull('business_id')
            ->select(DB::raw('CONCAT(name, ", ", COALESCE(description, "")) as name, id'))
        ->pluck('name', 'id');

        return view('qr_labels.create')->with(compact('barcode_settings'));
    }

    public function qrGenerate(Request $request){
        $request->validate([
            "aisle_start_from" => "numeric",
            "aisle_count" => "numeric",
            "aisle_inc" => "numeric",
            "rack_start_from" => "numeric",
            "rack_count" => "numeric",
            "rack_inc" => "numeric",
            "shelf_start_from" => "numeric",
            "shelf_count" => "numeric",
            "shelf_inc" => "numeric",
            "bin_start_from" => "numeric",
            "bin_count" => "numeric",
            "bin_inc" => "numeric",
            "label_h" => "numeric",
            "label_w" => "numeric"
        ]);

        $labels = [];
        $labels[] = [
            'A',
            'R',
            'S',
            'B',
            // 'QR',
        ];

        $a_start_from = $request->aisle_start_from ?? 1;
        $a_count = empty($request->show_aisle) ? 1 : ($request->aisle_count ?? 1);
        $a_inc = $request->aisle_inc ?? 1;
        for($a = 1; $a <= $a_count; $a++){
            $aisle = $a_start_from;

            $r_start_from = $request->rack_start_from ?? 1;
            $r_count = empty($request->show_rack) ? 1 : ($request->rack_count ?? 1);
            $r_inc = $request->rack_inc ?? 1;
            for($r = 1; $r <= $r_count; $r++){
                $rack = $r_start_from;

                $s_start_from = $request->shelf_start_from ?? 1;
                $s_count = empty($request->show_shelf) ? 1 : ($request->shelf_count ?? 1);
                $s_inc = $request->shelf_inc ?? 1;
                for($s = 1; $s <= $s_count; $s++){
                    $shelf = $s_start_from;

                    $b_start_from = $request->bin_start_from ?? 1;
                    $b_count = empty($request->show_bin) ? 1 : ($request->bin_count ?? 1);
                    $b_inc = $request->bin_inc ?? 1;
                    for($b = 1; $b <= $b_count; $b++){
                        $bin = $b_start_from;

                        // $qr_string = "A:$aisle|R:$rack|S:$shelf|B:$bin";
                        $qr_string = "$aisle    $rack   $shelf  $bin";

                        $labels[] = [
                            'A' => $aisle,
                            'R' => $rack,
                            'S' => $shelf,
                            'B' => $bin,
                            // 'QR' => $qr_string
                        ];

                        $b_start_from += $b_inc;
                    }
                    $s_start_from += $s_inc;
                }
                $r_start_from += $r_inc;
            }
            $a_start_from += $a_inc;
        }

        $label_h = $request->label_h ?? 1;
        $label_w = $request->label_w ?? 1;
        $show_params = [
            'A' => !empty($request->show_aisle),
            'R' => !empty($request->show_rack),
            'S' => !empty($request->show_shelf),
            'B' => !empty($request->show_bin)
        ];

        // return view('qr_labels.generate',compact('labels','show_params','label_h','label_w'));
        return Excel::download(new QrExport($labels), config('business-info.erp_name') . " - Qr Codes.xlsx");
    }

    public function qrScan(){
        return view('qr_labels.scan');
    }

    public function qrUpdate(Request $request){
        $vids = $request->products;
        if(!empty($vids)){
            $product_ids = Variation::select('product_id')
                ->whereIn('id', $vids)
            ->get();

            $products = Product::whereIn('id', array_column($product_ids->toArray(), 'product_id'))
            ->update([
                'aisle' => empty($request->a) ? 0 : $request->a,
                'rack' => empty($request->r) ? 0 : $request->r,
                'shelf' => empty($request->s) ? 0 : $request->s,
                'bin' => empty($request->b) ? 0 : $request->b,
            ]);

            return json_encode(['success' => 1]);
        }
        return json_encode(['success' => 0]);
    }

    public function backOrderReport(Request $request){
        if($request->ajax()){

            $customer_id = false;

            if(isset($request->customer) && $request->customer != 'all'){
                $customer_id = $request->customer;
            }

            $variation_id = false;

            if(isset($request->variation_id) && $request->variation_id != 'all'){
                $variation_id = $request->variation_id;
            }

            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
            if(isset($request->dates)){
                $date_arr = explode(' - ',$request->dates);
                $startDate = date('Y-m-d',strtotime($date_arr[0]));
                $endDate = date('Y-m-d',strtotime($date_arr[1]));;
            }

            $reportData = DB::table('transactions as t')
                ->select(
                    't.id',
                    't.transaction_date',
                    't.invoice_no',
                    't.final_total',
                    'c.name',
                    DB::raw('count(tsl.variation_id) as products'),
                )
                ->join('contacts as c','c.id','=','t.contact_id')
                ->join('transaction_sell_lines as tsl','tsl.transaction_id','=','t.id')
                // ->join('variation_location_details as vld','vld.variation_id','=','tsl.variation_id')
                ->where('t.type', 'sell')
                ->where('t.status', 'draft')
                ->where('t.transaction_date','>=',$startDate)
                ->where('t.transaction_date','<=',$endDate." 23:59:59")
                ->when($customer_id,function($query) use ($customer_id){
                    $query->where('t.contact_id',$customer_id);
                })
                ->when($variation_id,function($query) use ($variation_id){
                    $query->where('tsl.variation_id',$variation_id);
                })
                ->groupBy(
                    't.id',
                    't.transaction_date',
                    't.invoice_no',
                    't.final_total',
                    'c.name'
                )
                ->orderBy('t.transaction_date','desc')
            ->get();

            $filteredReportData = collect();
            foreach($reportData as $item){
                $item->back_order = DB::table('transaction_sell_lines as tsl')
                    ->join('variation_location_details as vld','vld.variation_id','=','tsl.variation_id')
                    ->where('tsl.transaction_id',$item->id)
                    ->where('vld.location_id',4)
                    ->whereRaw('vld.qty_available >= tsl.quantity')
                    ->when($variation_id,function($query) use ($variation_id){
                        $query->where('tsl.variation_id',$variation_id);
                    })
                ->count('vld.qty_available');

                $item->final_total = $this->format_money_display($item->final_total);

                if($item->back_order > 0){
                    $filteredReportData->add($item);
                }
            }
            return DataTables::of($filteredReportData)
                ->addColumn('action',function($row){
                    $html = '<a href="#" data-href="' . action("SellController@show", [$row->id]) . '" class="btn bg-white btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a>';
                    $html .= "&nbsp;&nbsp;<a target='_blank' class='btn btn-info' href='/pos/".$row->id."/edit'>Edit</a>";
                    $html .= "&nbsp;&nbsp;<a target='_blank' class='btn btn-primary' href='/sells/duplicate/create/".$row->id."?back_order=1'>Duplicate</a>";
                    return $html;
                })
                ->rawColumns(['action'])
            ->make(true);
        }
        $customers = Contact::whereIn('contacts.type', ['customer', 'both'])
            ->orderBy('name')
        ->get();
        return view('report.back_order_report',compact('customers'));
    }

    function format_money($value){
        return "$ ".number_format($value,2,'.','');
    }
    function format_money_display($value){
        return "$ ".number_format($value,2,'.',',');
    }
    function format_number($value){
        return number_format($value,2,'.','');
    }

    public function sendEmailViaApi($to_email, $subject, $htmlbody){
        $output = [
            'success' => 0,
            'msg' => 'Missing Parameters.'
        ];

        $client = new Client();
        $response = $client->post('https://api.zeptomail.com/v1.1/email', [
            'headers' => [
                'authorization' => 'Zoho-enczapikey wSsVR610rhDyCK19yjz8J+0+zwxVBlOkEE0p2gSpuXKvSPvFpcc+kxGaAwCgSKQeFmI7FzcUou0snEhSgDtd2tgtw10CASiF9mqRe1U4J3x17qnvhDzOWGhYlBWPK44JwQRvm2BjFssn+g=='
            ],
            'form_params' => [
                [
                    'from' => [
                        'address' => 'noreply@xyzfasteners.com',
                        'name' => 'No Reply - XYZ Fasteners',
                    ],
                    'to' => [
                        [
                            "email_address" => [
                                "address" => $to_email,
                                "name" => $to_email,
                            ]
                        ]
                    ],
                    'subject' => $subject,
                    'htmlbody' => $htmlbody,
                ]
            ]
        ]);

        if($response->getStatusCode() == 200){
            $output['success'] = 1;
            $output['msg'] = 'Email has sent';
        }
        else{
            $output['success'] = 0;
            $output['msg'] = 'Something went wrong';
        }

        return $output;
    }
}