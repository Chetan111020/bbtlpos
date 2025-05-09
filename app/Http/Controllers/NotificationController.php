<?php

namespace App\Http\Controllers;

use \Notification;

use App\Contact;
use App\BusinessLocation;

use App\Notifications\CustomerNotification;
use App\Notifications\SupplierNotification;
use App\Http\Controllers\SellPosController;

use App\NotificationTemplate;
use App\Restaurant\Booking;
// use Collective\Html\HtmlFacade as HTML;

use App\Utils\NotificationUtil;

use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;

use Illuminate\Http\Request;

use App\InvoiceLayout;
use App\InvoiceScheme;
use PDF;
use ZipArchive;
use File;
use QrCode;
use App\VariationGroupPrice;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Storage;

use App\Delinvoicelog;

class NotificationController extends Controller
{
    protected $notificationUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $contactUtil;
    protected $productUtil;
    /**
     * Constructor
     *
     * @param NotificationUtil $notificationUtil
     * @return void
     */
    public function __construct(NotificationUtil $notificationUtil,BusinessUtil $businessUtil , TransactionUtil  $transactionUtil, ContactUtil $contactUtil, ProductUtil $productUtil)
    {
        $this->notificationUtil = $notificationUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
    }


    /**
     * Display a notification view.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTemplate($id, $template_for)
    {
        $business_id = request()->session()->get('user.business_id');

        $notification_template = NotificationTemplate::getTemplate($business_id, $template_for);

        $contact = null;
        $transaction = null;
        if ($template_for == 'new_booking') {
            $transaction = Booking::where('business_id', $business_id)
                            ->with(['customer'])
                            ->find($id);

            $contact = $transaction->customer;
        } elseif ($template_for == 'send_ledger') {
            $contact = Contact::find($id);
        } else {
            $transaction = Transaction::where('business_id', $business_id)
                            ->with(['contact'])
                            ->find($id);

            $contact = $transaction->contact;
        }

        if(!empty($contact->notify_email)){
            $contact->email = $contact->notify_email;
        }

        $customer_notifications = NotificationTemplate::customerNotifications();
        $supplier_notifications = NotificationTemplate::supplierNotifications();
        $general_notifications = NotificationTemplate::generalNotifications();

        $template_name = '';

        $tags = [];
        if (array_key_exists($template_for, $customer_notifications)) {
            $template_name = $customer_notifications[$template_for]['name'];
            $tags = $customer_notifications[$template_for]['extra_tags'];
        } elseif (array_key_exists($template_for, $supplier_notifications)) {
            $template_name = $supplier_notifications[$template_for]['name'];
            $tags = $supplier_notifications[$template_for]['extra_tags'];
        } elseif (array_key_exists($template_for, $general_notifications)) {
            $template_name = $general_notifications[$template_for]['name'];
            $tags = $general_notifications[$template_for]['extra_tags'];
        }

        //for send_ledger notification template
        $start_date = request()->input('start_date');
        $end_date = request()->input('end_date');

        return view('notification.show_template')
                ->with(compact('notification_template', 'transaction', 'tags', 'template_name', 'contact', 'start_date', 'end_date'));
    }

    /**
     * Sends notifications to customer and supplier
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        // if (!auth()->user()->can('send_notification')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $notAllowed = $this->notificationUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $user_id = $request->session()->get('user.id');
            $customer_notifications = NotificationTemplate::customerNotifications();
            $supplier_notifications = NotificationTemplate::supplierNotifications();

            $data = $request->only(['to_email', 'subject', 'email_body', 'mobile_number', 'sms_body', 'notification_type', 'cc', 'bcc']);

            $emails_array = array_map('trim', explode(',', $data['to_email']));

            $transaction_id = $request->input('transaction_id');
            $business_id = request()->session()->get('business.id');

            $orig_data = [
                'email_body' => $data['email_body'],
                'sms_body' => $data['sms_body'],
                'subject' => $data['subject']
            ];

            if ($request->input('template_for') == 'new_booking') {
                $tag_replaced_data = $this->notificationUtil->replaceBookingTags($business_id, $orig_data, $transaction_id);

                $data['email_body'] = $tag_replaced_data['email_body'];
                $data['sms_body'] = $tag_replaced_data['sms_body'];
                $data['subject'] = $tag_replaced_data['subject'];
            } else {
                $tag_replaced_data = $this->notificationUtil->replaceTags($business_id, $orig_data, $transaction_id);

                $data['email_body'] = $tag_replaced_data['email_body'];
                $data['sms_body'] = $tag_replaced_data['sms_body'];
                $data['subject'] = $tag_replaced_data['subject'];
            }


            $data['email_settings'] = request()->session()->get('business.email_settings');

            $data['sms_settings'] = request()->session()->get('business.sms_settings');

            $notification_type = $request->input('notification_type');

            if (array_key_exists($request->input('template_for'), $customer_notifications)) {
                if ($notification_type == 'email_only') {
                    $user_id = $request->session()->get('user.id');
                    // if($user_id == 6){

                    //     $file = $this->getInvoicePDF($transaction_id, 'open_invoice');

                    //     $transaction = Transaction::where('id', $transaction_id)->with(['business', 'location'])->first();


                    //     // $mpdf = $this->getMpdf();
                    //     // $mpdf->WriteHTML($html);

                    //     // $file = config('constants.mpdf_temp_path') . '/' . time() . 'invoice.pdf';
                    //     // $mpdf->Output($file, 'F');


                    //     // $data['attachment'] =  $file;
                    //     // $data['attachment_name'] = $transaction->invoice_no.'.pdf';

                    //     // echo "<pre>";
                    //     // print_r($pdf_html);
                    //     // die;




                    // }

                    Notification::route('mail', $emails_array)
                                    ->notify(new CustomerNotification($data));
                    //added by developer1
                    $this->EmailInvoicelog('email',$user_id,$transaction_id);
                    //added by developer1

                    $user_id = request()->session()->get('user.id');
        
                    $this->transactionUtil->Delinvoicelog('send_email',$user_id,$transaction_id,null);
                } elseif ($notification_type == 'sms_only') {
                    $this->notificationUtil->sendSms($data);
                    //added by developer1
                    $this->EmailInvoicelog('sms',$user_id,$transaction_id);
                    //added by developer1
                    $user_id = request()->session()->get('user.id');
        
                    $this->transactionUtil->Delinvoicelog('send_email',$user_id,$transaction_id,null);
                } elseif ($notification_type == 'both') {
                    Notification::route('mail', $emails_array)
                                ->notify(new CustomerNotification($data));

                    $this->notificationUtil->sendSms($data);
                    //added by developer1
                    $this->EmailInvoicelog('email_sms',$user_id,$transaction_id);
                    //added by developer1
                    $user_id = request()->session()->get('user.id');
        
                    $this->transactionUtil->Delinvoicelog('send_email',$user_id,$transaction_id,null);
                
                }
            } elseif (array_key_exists($request->input('template_for'), $supplier_notifications)) {
                if ($notification_type == 'email_only') {
                    Notification::route('mail', $emails_array)
                                    ->notify(new SupplierNotification($data));
                } elseif ($notification_type == 'sms_only') {
                    $this->notificationUtil->sendSms($data);
                } elseif ($notification_type == 'both') {
                    Notification::route('mail', $emails_array)
                                ->notify(new SupplierNotification($data));

                    $this->notificationUtil->sendSms($data);
                }
            }

            $output = ['success' => 1, 'msg' => __('lang_v1.notification_sent_successfully')];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }

    public function getBulkNotificationTemplate(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $template_for = isset($_REQUEST['template_for']) ? $_REQUEST['template_for'] : '';
        $notification_template = NotificationTemplate::getTemplate($business_id, $template_for);
        $customer_notifications = NotificationTemplate::customerNotifications();

        if(isset($_REQUEST['selected_rows']) && count($_REQUEST['selected_rows']) > 0 && isset($_REQUEST['mobile_number']) && count($_REQUEST['mobile_number']) > 0){

            $final_selected_id = array_combine($_REQUEST['selected_rows'], $_REQUEST['mobile_number']);

            foreach($final_selected_id as $key => $selected_id){

                $orig_data = [
                    'sms_body' => $notification_template['sms_body'],
                    'subject' => $notification_template['subject'],
                ];

                $tag_replaced_data = $this->notificationUtil->replaceTags($business_id, $orig_data, $key);

                $orig_data['sms_body'] = $tag_replaced_data['sms_body'];
                $orig_data['subject'] = $tag_replaced_data['subject'];
                $tag_replaced_data['sms_settings'] = request()->session()->get('business.sms_settings');

                if (array_key_exists($request->input('template_for'), $customer_notifications)) {
                    $tag_replaced_data['mobile_number'] = $selected_id;
                    $this->notificationUtil->sendSms($tag_replaced_data);
                    $output = ['success' => 1, 'msg' => __('lang_v1.notification_sent_successfully')];
                 } else{
                    $output = ['success' => 0, 'msg' => __('messages.something_went_wrong') ];
                 }
            }

        }
       return $output;

    }


    public function getBulkEmailNotificationTemplate(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $notification_template = NotificationTemplate::getTemplate($business_id, 'payment_reminder');
        $customer_notifications = NotificationTemplate::customerNotifications();

        if(isset($_REQUEST['selected_rows']) && count($_REQUEST['selected_rows']) > 0 && isset($_REQUEST['to_email']) && count($_REQUEST['to_email']) > 0){

             $final_selected_id = array_combine($_REQUEST['selected_rows'], $_REQUEST['to_email']);

            foreach($final_selected_id as $key => $selected_id){

                $orig_data = [
                     'email_body' => $notification_template['email_body'],
                    'sms_body' => $notification_template['sms_body'],
                    'subject' => $notification_template['subject'],
                ];

                $tag_replaced_data = $this->notificationUtil->replaceTags($business_id, $orig_data, $key);

                $orig_data['email_body'] = $tag_replaced_data['email_body'];
                $orig_data['sms_body'] = $tag_replaced_data['sms_body'];
                $orig_data['subject'] = $tag_replaced_data['subject'];
                $tag_replaced_data['email_settings'] = request()->session()->get('business.email_settings');

                if (array_key_exists($request->input('template_for'), $customer_notifications)) {
                    Notification::route('mail',$selected_id)->notify(new CustomerNotification($tag_replaced_data));
                    $output = ['success' => 1, 'msg' => __('lang_v1.notification_sent_successfully')];
                 } else{
                    $output = ['success' => 0, 'msg' => __('messages.something_went_wrong') ];
                 }
            }

        }
       return $output;

    }

    //added by developer1 for notification log

    public function EmailInvoicelog($type,$user_id,$transaction_id,$pro=''){
        $user = auth()->user();
        $date = \Carbon::now()->toDateTimeString();

        if($type=='email')
        {
            $change_message = 'Email Notification was sent by '.$user->first_name.' at '.$date;
        }
        elseif($type=='sms')
        {
            $change_message = 'SMS Notification was sent by '.$user->first_name.' at '.$date;
        }
        elseif($type=='email_sms')
        {
            $change_message = 'Email and SMS Notification was sent by '.$user->first_name.' at '.$date;
        }


        $log = new Delinvoicelog();
        $log->user_id = $user_id;
        $log->transaction_id = $transaction_id;
        $log->description = 'added';
        $log->message = $change_message;
        $log->save();
    }

    //added by developer1 for notification log

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

        $ledger_details = null;
        $ledger_details = $this->transactionUtil->getLedgerDetails($transaction->contact_id, $start_date, $end_date, $advance_balance);


        $receipt_details->currency = $currency_details;

        $jadoo_products = $this->productUtil->GetJadooProductlist();
        /*echo "<pre>";
        print_r($jadoo_products);
        exit;*/

        $blank_slip_number = $transaction->invoice_no ?? '0';
        $blank_slip_number .= '1';
        $blank_slip_number .= $transaction->contact->zip_code ?? '0';
        $blank_slip_number .= $transaction->contact->mobile ?? '0';

        if ($is_package_slip) {
            $output['html_content'] = view('sale_pos.receipts.packing_slip', compact('receipt_details','tax_details','jadoo_products'))->render();
            $output['html_content1'] = view('sale_pos.receipts.driver_invoice', compact('receipt_details','tax_details','ledger_details'))->render();
            $output['html_content2'] = view('sale_pos.receipts.classic', compact('receipt_details','tax_details','jadoo_products'))->render();
            $output['html_content3'] = view('sale_pos.receipts.invoicegen', compact('receipt_details','tax_details'))->render();
            $output['html_content4'] = view('sale_pos.receipts.packing_gen', compact('receipt_details','tax_details'))->render();
            $output['html_content5'] = view('sale_pos.receipts.packing_slip_blank', compact('blank_slip_number','receipt_details','tax_details','jadoo_products'))->render();
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
                $output['html_content_pdf'] = view('sale_pos.receipts.export_pdf', compact('receipt_details','tax_details','qrcode'))->render();
                $output['html_content_pdf2'] = view('sale_pos.receipts_new.open_invoice_pdf', compact('url','receipt_details','tax_details','jadoo_products','qrcode'))->render();
            }
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

                $pdf = PDF::loadView('sale_pos.receipts_new.show_pdf_view', compact('receipt', 'title'))->Output();
                    // $pdf->render();

                    // $pdf->stream('document.pdf');


                // $pdf_html = $this->open_invoice($id)->render();
                // $mpdf = new \Mpdf\Mpdf();
                // $mpdf->WriteHTML($pdf_html);

                // $pdf = PDF::loadView('sale_pos.receipts_new.show_pdf_view', compact('receipt', 'title'));
                // // Storage::put($file_dir."/".$transaction->id.".pdf", $pdf->output());
                // public_path('email_attached_invoices/'.$transaction->id.".pdf");
                // return $pdf->download($transaction->id.".pdf");
                // // $file_name = $file_dir."/".$transaction->id.".pdf";
                // // return $pdf->download($file_name);
                // // $file = $file_dir . '/' . $transaction->invoice_no. '.pdf';
                // // $mpdf->Output();
                // // echo  $mpdf;
                // // die;

                $bin = base64_decode($pdf,true);

                // if (strpos($bin, '%PDF') !== 0) {
                //     // invalid pdf - send mail without pdf
                //      echo  $bin;
                // die;
                // }
                // else{
                    $file_dir = base_path('public/email_attached_invoices').date('/Y_m_d');
                    if(!FacadesFile::exists($file_dir)){
                        FacadesFile::makeDirectory($file_dir);
                    }
                    if(FacadesFile::exists($file_dir)){
                        $file_name = $file_dir."/".$transaction->id.".pdf";
                        if(file_put_contents($file_name, $bin) != FALSE){
                          return $transaction->pdf_invoice = $file_name;
                        }
                    }
                // }
                // die;
                // // print_r($pdf_html);
                // // $mpdf = new \Mpdf\Mpdf();
                // // $mpdf->WriteHTML($pdf_html);
                // // $file = $file_dir . '/' . $transaction->invoice_no. '.pdf';
                // // return $mpdf->Output($file, 'F');
                // // return $file;

            }
        }
        return "";
    }
    
        
        
    public function getBulkSmsTemplate(Request $request)
    {
      
        $business_id = request()->session()->get('user.business_id');
        $template_for = isset($_REQUEST['template_for']) ? $_REQUEST['template_for'] : ''; 
        $notification_template = NotificationTemplate::getTemplate($business_id, $template_for);
        $customer_notifications = NotificationTemplate::customerNotifications();
        
        $startDate = $request->get('date');
        $myArray = explode('-', $startDate);
        $fr1 = $myArray[0];
        $to2 = $myArray[1];
        $from = str_replace(' ', '', $fr1);
        $to = str_replace(' ', '', $to2);
   
        $new_fromdate = date('Y-m-d',strtotime($fr1));
        $new_todate = date('Y-m-d',strtotime($to2));

        // echo $new_todate;
        // die;
        
       
        if(isset($_REQUEST['selected_rows']) && count($_REQUEST['selected_rows']) > 0 && isset($_REQUEST['mobile_number']) && count($_REQUEST['mobile_number']) > 0){
            
            $final_selected_id = array_combine($_REQUEST['selected_rows'], $_REQUEST['mobile_number']);
            // echo "<pre>";
            // print_r( $final_selected_id);
            // die;
    
    
            foreach($final_selected_id as $key => $selected_id){
                
           
                $orig_data = [
                    'sms_body' => $notification_template['sms_body'],
                    'subject' => $notification_template['subject'],
                ];
            
                $tag_replaced_data = $this->notificationUtil->replaceTagsForBalanceDue($business_id, $orig_data, $key,$new_fromdate,$new_todate);
                // echo "<pre>";
                // print_r( $tag_replaced_data);
                // die;
                $orig_data['sms_body'] = $tag_replaced_data['sms_body'];
                $orig_data['subject'] = $tag_replaced_data['subject'];
                $tag_replaced_data['sms_settings'] = request()->session()->get('business.sms_settings');
                
                if (array_key_exists($request->input('template_for'), $customer_notifications)) {
                    
                    $util = new \App\Utils\Util;
                    $due = $util->getContactDue($key);
                    if($due > 0){
                        $tag_replaced_data['mobile_number'] = $selected_id;
                        // $tag_replaced_data['mobile_number'] = '+1'.$selected_id;

                    }
                    else{
                        $tag_replaced_data['mobile_number'] = '';
                    }
                    
                    // if($tag_replaced_data['mobile_number'] == '+10000000000'){
                    //     $tag_replaced_data['mobile_number'] = '+919428293911';
                    // }
                    
                    // echo "<pre>";
                    // print_r( $tag_replaced_data['mobile_number']);
                    // die; 
                    
                    $this->notificationUtil->sendSms($tag_replaced_data);
                    $output = ['success' => 1, 'msg' => __('lang_v1.notification_sent_successfully')];
                 } else{
                    $output = ['success' => 0, 'msg' => __('messages.something_went_wrong') ];
                 }
            }
            
        }
      return $output;
      
    }
    
}

