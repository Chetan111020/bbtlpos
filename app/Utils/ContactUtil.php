<?php

namespace App\Utils;

use App\Contact;
use App\Utils\TransactionUtil;
use App\Transaction;
use DB;
use App\Customerlog;
use App\User;
use Modules\SmartCRM\Models\Leads;
class ContactUtil extends Util
{

    /**
     * Returns Walk In Customer for a Business
     *
     * @param int $business_id
     *
     * @return array/false
     */
    public function getWalkInCustomer($business_id, $array = true)
    {
        $contact = Contact::whereIn('type', ['customer', 'both'])
                    ->where('business_id', $business_id)
                    ->where('is_default', 1)
                    ->first();

        if (!empty($contact)) {
            $output = $array ? $contact->toArray() : $contact;
            return $output;
        } else {
            return null;
        }
    }

    /**
     * Returns the customer group
     *
     * @param int $business_id
     * @param int $customer_id
     *
     * @return array
     */
    public function getCustomerGroup($business_id, $customer_id)
    {
        $cg = [];

        if (empty($customer_id)) {
            return $cg;
        }

        $contact = Contact::leftjoin('customer_groups as CG', 'contacts.customer_group_id', 'CG.id')
            ->where('contacts.id', $customer_id)
            ->where('contacts.business_id', $business_id)
            ->select('CG.*')
            ->first();

        return $contact;
    }

    /**
     * Returns the contact info
     *
     * @param int $business_id
     * @param int $contact_id
     *
     * @return array
     */
    public function getContactInfo($business_id, $contact_id)
    {
       $contact = Contact::where('contacts.id', $contact_id)
                    ->where('contacts.business_id', $business_id)
                    ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                    ->leftjoin('users as sale','sale.id','=','contacts.sales_rep')
                    ->leftjoin('users as account','account.id','=','contacts.account_rep')
                    ->leftjoin('users as createdfor','createdfor.id','=','contacts.created_by')
                    ->with(['business'])
                    ->select(
                        DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                        DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                        DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                        DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                        'contacts.*',
                        'sale.first_name as sales_firstname',
                        'sale.last_name as sales_lastname',
                        'account.first_name as acc_firstname',
                        'account.last_name as acc_lastname',
                        'createdfor.username as username'
                    )->first();

        return $contact;
    }

    public function getContactInfoByName($business_id, $name)
    {
        $contact = Contact::where('contacts.supplier_business_name', $name)
                    ->where('contacts.business_id', $business_id)
                    ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                    ->with(['business'])
                    ->select(
                        DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                        DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                        DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                        'contacts.*'
                    )->first();

        return $contact;
    }

    public function createNewContact($input)
    {
        //Check Contact id
        $count = 0;
        if (!empty($input['contact_id'])) {
            $count = Contact::where('business_id', $input['business_id'])
                            ->where('contact_id', $input['contact_id'])
                            ->count();
        }
        if ($count == 0) {
            //Update reference count
            $ref_count = $this->setAndGetReferenceCount('contacts', $input['business_id']);

            if (empty($input['contact_id'])) {
                //Generate reference number
                $input['contact_id'] = $this->generateReferenceNumber('contacts', $ref_count, $input['business_id']);
            }

            $opening_balance = isset($input['opening_balance']) ? $input['opening_balance'] : 0;
            if (isset($input['opening_balance'])) {
                unset($input['opening_balance']);
            }

            if(isset($input['type']) && $input['type'] == 'customer') {
                if (empty($input['sales_rep'])) {
                    $input['sales_rep'] = 6;
                }
                if (empty($input['account_rep'])) {
                    $input['account_rep'] = 6;
                }
            }

            $contact = Contact::create($input);

            if(isset($input['sync']) && $input['sync'] == 1) {
                \App::call('Modules\Woocommerce\Http\Controllers\WoocommerceController@syncCustomer',  [
                    "id" => $contact->id
                ]);

            }


            //Add opening balance
            if (!empty($opening_balance)) {
                $transactionUtil = new TransactionUtil();
                $transactionUtil->createOpeningBalanceTransaction($contact->business_id, $contact->id, $opening_balance, $contact->created_by, false);
            }

            $output = ['success' => true,
                        'data' => $contact,
                        'msg' => __("contact.added_success")
                    ];
            return $output;
        } else {
            throw new \Exception("Error Processing Request", 1);
        }
    }

    public function updateContact($input, $id, $business_id)
    {


        $count = 0;
        //Check Contact id
        if (!empty($input['contact_id'])) {

            $count = Contact::where('business_id', $business_id)
                    ->where('contact_id', $input['contact_id'])
                    ->where('id', '!=', $id)
                    ->count();
        }


        if ($count == 0) {

            //Get opening balance if exists
            $ob_transaction =  Transaction::where('contact_id', $id)
                                    ->where('type', 'opening_balance')
                                    ->first();
            $opening_balance = isset($input['opening_balance']) ? $input['opening_balance'] : 0;

            if (isset($input['opening_balance'])) {
                unset($input['opening_balance']);
            }

            $contact = Contact::where('business_id', $business_id)->findOrFail($id);

            foreach ($input as $key => $value) {

                $contact->$key = $value;
            }

            $contact->save();
            // if(!$input['sync']) {
            //     \App::call('Modules\Woocommerce\Http\Controllers\WoocommerceController@syncCustomer',  [
            //         "id" => $contact->id
            //     ]);
            // }


            if(isset($input['sync']) && $input['sync'] == 1) {
                \App::call('Modules\Woocommerce\Http\Controllers\WoocommerceController@syncCustomer',  [
                    "id" => $contact->id
                ]);

            }

            $transactionUtil = new TransactionUtil();
            if (!empty($ob_transaction)) {
                if($input['type']=='supplier')
                {
                   /* $opening_balance_paid = $transactionUtil->getTotalAmountPaid($ob_transaction->id);
                    if (!empty($opening_balance_paid)) {
                        $opening_balance += $opening_balance_paid;
                    }

                    $ob_transaction->final_total = $opening_balance;
                    $ob_transaction->save();
                    //Update opening balance payment status
                    $transactionUtil->updatePaymentStatus($ob_transaction->id, $ob_transaction->final_total);*/
                }
            } else {
                //Add opening balance
                if (!empty($opening_balance)) {
                    $transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $opening_balance, $contact->created_by, false);
                }
            }

            $output = ['success' => true,
                        'msg' => __("contact.updated_success"),
                        'data' => $contact
                        ];
        } else {
            throw new \Exception("Error Processing Request", 1);
        }

        return $output;
    }

    public function getContactQuery($business_id, $type, $state = '', $city = '', $customer_id='',$acc_rep='',$sale_rep='')
    {
        $query = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                    ->leftjoin('selling_price_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
                    ->leftjoin('users as sale','sale.id','=','contacts.sales_rep')
                    ->leftjoin('users as account','account.id','=','contacts.account_rep')
                    ->leftjoin('users as createdfor','createdfor.id','=','contacts.created_by')
                    ->where('contacts.business_id', $business_id);
//                    ->where('contacts.state', $state)
//                    ->where('contacts.city', $city);
        if (!empty($state)){
            $query->where('contacts.state', $state);
        }
        if (!empty($city)){
            $query->where('contacts.city', $city);
        }
        if (!empty($customer_id)){
            $query->where('contacts.id', $customer_id);
        }
        if ($type == 'supplier') {
           $query->onlySuppliers();
        } elseif ($type == 'customer') {
            $query->onlyCustomers();
        }
        if (!empty($contact_ids)) {
            $query->whereIn('contacts.id', $contact_ids);
        }

        if(!empty($acc_rep)){
            // $query->where('account.id', $acc_rep);
            if($acc_rep == 6){
                $query->where(function($q){
                    $q->whereNull('contacts.account_rep')->orWhere('account.id', 6);
                });
            }
            else{
                $query->where('account.id', $acc_rep);
            }
        }
        if(!empty($sale_rep)){
            // $query->where('sale.id', $sale_rep);
            if($sale_rep == 6){
                $query->where(function($q){
                    $q->whereNull('contacts.sales_rep')->orWhere('sale.id', 6);
                });
            }
            else{
                $query->where('sale.id', $sale_rep);
            }
        }

        $query->select([
            'contacts.*',
            'createdfor.username as created_username',
            'cg.name as customer_group',
            'sale.first_name as sales_firstname',
            'sale.last_name as sales_lastname',
            'account.first_name as acc_firstname',
            'account.last_name as acc_lastname',
            'sale.id as saleid','account.id as accountid',

            DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
            DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid")
        ]);

        if (in_array($type, ['supplier', 'both'])) {
            $query->addSelect([
                DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                DB::raw("SUM(IF(t.type = 'expense', final_total, 0)) as total_expense"),
                DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_paid"),
            ]);
        }

        if (in_array($type, ['customer', 'both'])) {
            $query->addSelect([
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid")
            ]);
        }
        $query->groupBy('contacts.id');

        return $query;
    }

     public function CustomerLog($type,$user_id,$customer_id,$business_id,$pro='')
    {
        $user = auth()->user();
        $message = rtrim($pro, ',');
        $date = \Carbon::now()->toDateTimeString();
        $change_message = '';
        if(!empty($message)){
            $change_message = $message.' was changed by '.$user->first_name.' at '.$date;
        }

        if(!empty($message) && $type=='added')
        {
            $change_message = $message.' was added by '.$user->first_name.' at '.$date;
        }

        if(!empty($message) && $type=='deleted')
        {
            $change_message = '';
        }

        $log = new Customerlog();
        $log->user_id = $user_id;
        $log->contact_id = $customer_id;
        $log->description = $type;
        $log->message = $change_message;
        $log->save();
    }
    public function UpdateCustomerLog($input, $business_id, $user_id, $id)
    {
        $pro='';

        $customer = Contact::find($id);

        $customer_id = $id;
        $salesRepName = $this->getUserNameById($customer->sales_rep);
        $accountRepName = $this->getUserNameById($customer->account_rep);

        if($customer->type == 'customer'){
            if(isset($customer->id)){
                // $pro .= $customer;
                if($customer->first_name != $input['first_name']){
                    $pro .= 'First Name ('.$customer->first_name.' --> '.$input['first_name'].'), ';
                }

                if (!empty($input['whatsapp']) && $customer->whatsapp != $input['whatsapp']) {
                    $pro .= 'Whatsapp (' . $customer->whatsapp . ' --> ' . $input['whatsapp'] . '), ';
                }

                if($customer->tax != $input['tax_number']){
                    $pro .= 'Tax ('.$customer->tax.' --> '.$input['tax_number'].'), ';
                }

                if($customer->supplier_business_name != $input['supplier_business_name']){
                    $pro .= 'Supplier Business Name ('.$customer->supplier_business_name.' --> '.$input['supplier_business_name'].'), ';
                }

                if($customer->address_line_1 != $input['address_line_1']){
                    $pro .= 'Address Line 1 ('.$customer->address_line_1.' --> '.$input['address_line_1'].'), ';
                }

                if($customer->tobacco_license_no != $input['tobacco_license_no']){
                    $pro .= 'Tobacco License No ('.$customer->tobacco_license_no.' --> '.$input['tobacco_license_no'].'), ';
                }

                if($customer->expiry_date != $input['expiry_date']){
                    $pro .= 'Expiry Date ('.$customer->expiry_date.' --> '.$input['expiry_date'].'), ';
                }

                if($customer->contact_person_1 != $input['contact_person_1']){
                    $pro .= 'Contact Person 1 ('.$customer->contact_person_1.' --> '.$input['contact_person_1'].'), ';
                }

                if($customer->contact_person_2 != $input['contact_person_2']){
                    $pro .= 'Contact Person 2 ('.$customer->contact_person_2.' --> '.$input['contact_person_2'].'), ';
                }

                if($customer->email != $input['email']){
                    $pro .= 'Email ('.$customer->email.' --> '.$input['email'].'), ';
                }

                if($customer->address_line_2 != $input['address_line_2']){
                    $pro .= 'Address Line 2 ('.$customer->address_line_2.' --> '.$input['address_line_2'].'), ';
                }

                if($customer->city != $input['city']){
                    $pro .= 'City ('.$customer->city.' --> '.$input['city'].'), ';
                }

                if($customer->state != $input['state']){
                    $pro .= 'State ('.$customer->state.' --> '.$input['state'].'), ';
                }

                // if($customer->sales_rep != $input['sales_rep']){
                //     $pro .= 'Sales Rep ('.$customer->sales_rep.' --> '.$input['sales_rep'].'), ';
                // }

                // if($customer->account_rep != $input['account_rep']){
                //     $pro .= 'Account Rep ('.$customer->account_rep.' --> '.$input['account_rep'].'), ';
                // }

                if (isset($input['sales_rep']) && $customer->sales_rep != $input['sales_rep']) {
                    $pro .= 'Sales Rep (' . $salesRepName . ' --> ' . $this->getUserNameById($input['sales_rep']) . '), ';
                }

                if (isset($input['account_rep']) && $customer->account_rep != $input['account_rep']) {
                    $pro .= 'Account Rep (' . $accountRepName . ' --> ' . $this->getUserNameById($input['account_rep']) . '), ';
                }

                if($customer->opening_balance != $input['opening_balance']){
                    $pro .= 'Opening Balance ('.$customer->opening_balance.' --> '.$input['opening_balance'].'), ';
                }

                if($customer->credit_limit != $input['credit_limit']){
                    $pro .= 'Credit limit ('.$customer->credit_limit.' --> '.$input['credit_limit'].'), ';
                }

                if($pro!="")
                {
                    $this->CustomerLog('edited',$user_id,$customer_id,$business_id,$pro);
                }
            }
        }
    }
    private function getUserNameById($userId)
    {
        $user = User::find($userId);
        if(!empty($user)){
            return $user->first_name . ' ' . $user->last_name; // Replace 'N/A' with a default value if user is not found
        }
        else{
            return "N/A";
        }
    }

    public function createNewLeads($input)
    {
        //Check Contact id
        $count = 0;
        if (!empty($input['contact_id'])) {
            $count = Leads::where('business_id', $input['business_id'])
                            ->where('contact_id', $input['contact_id'])
                            ->count();
        }
        if ($count == 0) {
            //Update reference count
            $ref_count = $this->setAndGetReferenceCount('contacts', $input['business_id']);

            if (empty($input['contact_id'])) {
                //Generate reference number
                $input['contact_id'] = $this->generateReferenceNumber('contacts', $ref_count, $input['business_id']);
            }

            $opening_balance = isset($input['opening_balance']) ? $input['opening_balance'] : 0;
            if (isset($input['opening_balance'])) {
                unset($input['opening_balance']);
            }

            $contact = Leads::create($input);

            // if(!empty($input['sync']) && $input['sync'] == 1) {
            //     \App::call('Modules\Woocommerce\Http\Controllers\WoocommerceController@syncCustomer',  [
            //         "id" => $contact->id
            //     ]);

            // }


            //Add opening balance
            if (!empty($opening_balance)) {
                $transactionUtil = new TransactionUtil();
                $transactionUtil->createOpeningBalanceTransaction($contact->business_id, $contact->id, $opening_balance, $contact->created_by, false);
            }

            $output = ['success' => true,
                        'data' => $contact,
                        'msg' => __("Lead record added")
                    ];
            return $output;
        } else {
            throw new \Exception("Error Processing Request", 1);
        }
    }

     public function updateLeads($input, $id, $business_id)
    {


        $count = 0;
        //Check Contact id
        if (!empty($input['contact_id'])) {

            $count = Leads::where('business_id', $business_id)
                    ->where('contact_id', $input['contact_id'])
                    ->where('id', '!=', $id)
                    ->count();
        }


        if ($count == 0) {

            $contact = Leads::where('business_id', $business_id)->findOrFail($id);

            foreach ($input as $key => $value) {

                $contact->$key = $value;
            }

            $contact->save();
            // if(!$input['sync']) {
            //     \App::call('Modules\Woocommerce\Http\Controllers\WoocommerceController@syncCustomer',  [
            //         "id" => $contact->id
            //     ]);
            // }

            $output = ['success' => true,
                        'msg' => __("Lead Updated Successfully"),
                        'data' => $contact
                        ];
        } else {
            throw new \Exception("Error Processing Request", 1);
        }

        return $output;
    }
}
