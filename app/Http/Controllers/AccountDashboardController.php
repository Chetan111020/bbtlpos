<?php

namespace App\Http\Controllers;

use App\BusinessLocation;

use App\Charts\CommonChart;
use App\Currency;
use App\Transaction;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\VariationLocationDetails;
use Datatables;
use DB;
use Illuminate\Http\Request;
use App\Utils\Util;
use App\Utils\RestaurantUtil;
use App\User;
use App\Product;
use App\Contact;
use App\Variation;

use DateTime;



use Illuminate\Notifications\DatabaseNotification;

class AccountDashboardController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $commonUtil;
    protected $restUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function Index()
    {
        return view('dashboard.account_index');
    }

    public function GetPurchaseSell()
    {


        // $start = Carbon::now()->subDays(6);
        // $end = Carbon::now();
        $start = request()->start;
        $end = request()->end;
       
        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);
        // $dtbefore->modify('-139 day');

        $sell = DB::table('transactions as t')
            ->where('type', 'sell')
            ->where('status', 'final')
            ->select(
                DB::raw('DATE(t.transaction_date) as trdate'),
                // DB::raw('SUM(tr.unit_price * tr.quantity) as sales')
                DB::raw('SUM(final_total) as sales')
            )
            // ->leftJoin('transaction_sell_lines as tr', 't.id', '=', 'tr.transaction_id')
            ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();

        $purchase = DB::table('transactions as t')
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->select(
                DB::raw('DATE(t.transaction_date) as trdate'),
                DB::raw('SUM(tr.purchase_price * tr.quantity) as purchase_amt')
            )
            ->leftJoin('purchase_lines as tr', 't.id', '=', 'tr.transaction_id')
            ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();


        $dates = [];
        for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
            array_push($dates, $i->format('Y-m-d'));
        }

        $all_sell = [];
        $n = 0;
        $m = 0;
        $all_purchase = [];
        foreach ($dates as $date) {

            if (in_array($date, array_column(collect($sell)->toArray(), 'trdate'))) {
                array_push($all_sell, $sell[$n]->sales ?? 0);
                $n++;
            } else {
                array_push($all_sell, 0);
            }

            if (in_array($date, array_column($purchase->toArray(), 'trdate'))) {
                array_push($all_purchase, $purchase[$m]->purchase_amt ?? 0);
                $m++;
            } else {
                array_push($all_purchase, 0);
            }
        }

        $mainChart = [
            $all_sell,
            $all_purchase,
            $dates
        ];
        return $mainChart;
    }

    public function GetTotal()
    {
        if (request()->ajax()) {
        // $start = Carbon::now()->subDays(6)->format('Y-m-d');
        // $end = Carbon::now();

        $start = '';
        $end = '';
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
        }


        $location_id = request()->location_id;
        $business_id = request()->session()->get('user.business_id');
        // $contact = Contact::where('business_id', $business_id)->find($id);

        $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id);
        $net_profit = $this->getProfitLossDetails($business_id, $location_id, $start, $end);
        $total_invoice = DB::table('transactions as t')
            ->select(
                'discount_amount',
                DB::raw("SUM(IF(type = 'sell' AND status = 'final', final_total, 0)) as total_invoice")
            )
            ->whereBetween(DB::raw('date(transaction_date)'), [$start, $end])
            ->get();


        $purchase_due = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase')
            ->select(
                'final_total',
                DB::raw("SUM((SELECT SUM(tp.amount) FROM transaction_payments as tp WHERE tp.transaction_id=transactions.id)) as total_paid")
            )
            ->whereBetween(DB::raw('date(transaction_date)'), [$start, $end])
            ->groupBy('transactions.id')
            ->get();

        $ob_transaction =  Transaction::where('type', 'opening_balance')
            // ->whereBetween(DB::raw('date(transaction_date)'), [$start, $end])
            ->get();
        $opening_balance = !empty($ob_transaction->final_total) ? $ob_transaction->final_total : 0;

        //Deduct paid amount from opening balance.
        if (!empty($opening_balance)) {
            $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction);
            if (!empty($opening_balance_paid)) {
                $opening_balance = $opening_balance - $opening_balance_paid;
            }

            $opening_balance = $this->commonUtil->num_f($opening_balance);
        }

        $output['invoice_due'] = $sell_details['invoice_due'];
        $output['purchase_due'] = $purchase_due->sum('final_total') - $purchase_due->sum('total_paid');
        $output['total_invoice'] = $total_invoice->sum('total_invoice') + $total_invoice->sum('discount_amount');
        $output['opening_balance'] = $opening_balance;
        $output['net_profit'] = $net_profit['net_profit'];

        return $output;
        }
    }

    public function AccountPay()
    {
        // $start = request()->start;
        // $end = request()->end;

        $business_id = request()->session()->get('user.business_id');

        $purchase_due = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase')
            ->select(
                'final_total',
                DB::raw("SUM((SELECT SUM(tp.amount) FROM transaction_payments as tp WHERE tp.transaction_id=transactions.id)) as total_paid")
            )
            // ->whereBetween(DB::raw('date(transaction_date)'), [$start, $end])
            ->groupBy('transactions.id')
            ->get();

        $due = $purchase_due->sum('final_total') - $purchase_due->sum('total_paid');

        $output['account_pay'] = $due;

        return $output;
    }

    public function AccountReceiv()
    {
        $location_id = request()->location_id;
        $business_id = request()->session()->get('user.business_id');
        
        $query = Transaction::where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.status', 'final')
                    ->select(
                        'final_total',
                        DB::raw('(SELECT SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)) FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id) as total_paid')
                    )
                    ->get();

        $output['account_re'] = $query->sum('final_total') - $query->sum('total_paid');
        
        return $output;
    }
    private function __transactionQuery($contact_id, $start, $end = null)
    {
        $business_id = request()->session()->get('user.business_id');
        $transaction_type_keys = array_keys(Transaction::transactionTypes());

        $query = Transaction::where('transactions.contact_id', $contact_id)
            ->where('transactions.business_id', $business_id)
            ->where('status', '!=', 'draft')
            ->whereIn('type', $transaction_type_keys);

        if (!empty($start)  && !empty($end)) {
            $query->whereDate(
                'transactions.transaction_date',
                '>=',
                $start
            )
                ->whereDate('transactions.transaction_date', '<=', $end)->get();
        }

        if (!empty($start)  && empty($end)) {
            $query->whereDate('transactions.transaction_date', '<', $start);
        }

        return $query;
    }
    public function GetIncomeExpence()
    {
        

        // $start = Carbon::now()->subDays(6);
        // $end = Carbon::now();
        $start = request()->start;
        $end = request()->end;
        // $start = "2022-07-16";
        // $end = "2022-07-16";

        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);

        $business_id = request()->session()->get('user.business_id');

        $income =  DB::table('transactions as t')
            ->where('type', 'sell')
            ->where('status', 'final')
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('DATE(t.transaction_date) as trdate'),
                // DB::raw('SUM(tr.unit_price * tr.quantity) as sales')
                DB::raw('SUM(final_total) as sales')
            )
            ->leftJoin('transaction_sell_lines as tr', 't.id', '=', 'tr.transaction_id')
            ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();

        // $expenses = Transaction::leftJoin('expense_categories AS ec', 'transactions.expense_category_id', '=', 'ec.id')
        // ->join(
        //     'business_locations AS bl',
        //     'transactions.location_id',
        //     '=',
        //     'bl.id'
        // )
        // ->where('transactions.business_id', $business_id)
        // ->whereIn('transactions.type', ['expense', 'expense_refund'])
        // ->select(DB::raw('DATE(transactions.transaction_date) as trdate'),
        // // DB::raw('SUM(tr.unit_price * tr.quantity) as sales')
        // DB::raw('SUM(final_total) as expense')
        // )
        // ->whereDate('transactions.transaction_date', '>=', $dtbefore->format('Y-m-d'))
        // ->whereDate('transactions.transaction_date', '<=', $dtcurr->format('Y-m-d'))
        // ->groupBy('trdate')
        // ->orderBy('trdate')
        // ->get();

        $expenses = Transaction::leftJoin('expense_categories AS ec', 'transactions.expense_category_id', '=', 'ec.id')
            ->whereIn('transactions.type', ['expense', 'expense_refund'])
            ->select(
                DB::raw('DATE(transactions.transaction_date) as trdate'),
                DB::raw('SUM(final_total) as expense')
            )
            ->whereDate('transactions.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transactions.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();
        $dates = [];
        for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
            array_push($dates, $i->format('Y-m-d'));
        }

        $all_income = [];
        $n = 0;
        $m = 0;
        $all_expenses = [];
        foreach ($dates as $date) {

            if (in_array($date, array_column(collect($income)->toArray(), 'trdate'))) {
                array_push($all_income, $income[$n]->sales ?? 0);
                $n++;
            } else {
                array_push($all_income, 0);
            }

            if (in_array($date, array_column($expenses->toArray(), 'trdate'))) {
                array_push($all_expenses, $expenses[$m]->expense ?? 0);
                $m++;
            } else {
                array_push($all_expenses, 0);
            }
        }

        $mainChart = [
            $all_income,
            $all_expenses,
            $dates
        ];
        return $mainChart;
    }

    public function getAvgGpData($business_id, $start_date, $end_date)
    {
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
        $with = [];
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $sells = $this->transactionUtil->getListSells_New($business_id);
        $myquery = $this->transactionUtil->getListSells_New1($business_id);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $myquery .= ' AND transactions.location_id = "' . $permitted_locations . '" ';
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $myquery .= ' AND transactions.created_by = "' . request()->session()->get('user.id') . '" ';
            $sells->where('transactions.created_by', request()->session()->get('user.id'));
        }

        if (!empty($start_date) && !empty($end_date)) {
            $myquery .= 'AND transactions.transaction_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ';
            $sells->whereDate('transactions.transaction_date', '>=', $start_date)
                ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if ($is_woocommerce) {
            $addSelect = 'transactions.woocommerce_order_id';
            $sells->addSelect('transactions.woocommerce_order_id');
            if (request()->only_woocommerce_sells) {
                $myquery .= ' AND transactions.woocommerce_order_id IS NOT NULL ';
                $sells->whereNotNull('transactions.woocommerce_order_id');
            }
        }

        $sells->groupBy('transactions.id');

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
        $result = $sells->get();

        $dueSum = $result->sum(function ($row) {
            $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
            $data =  DB::table('transaction_sell_lines')
                ->select(DB::raw("(1-((sum(purchase_price*quantity))/(sum(unit_price_inc_tax*quantity)-$discount)))*100 as GP"))
                ->where('transaction_id', $row->id)->first();
            $gross_profit = round($data->GP, 2);
            $gp = $gross_profit . '%';
            return $gross_profit;
        });
        $avg = count($result);
        if ($avg != 0) {
            return ($dueSum / $avg);
        }
        return $dueSum;
    }
    public function getProfitLossDetails($business_id, $location_id, $start_date, $end_date, $user_id = null)
    {
        //For Opening stock date should be 1 day before
        $day_before_start_date = \Carbon::createFromFormat('Y-m-d', $start_date)->subDay()->format('Y-m-d');

        $filters = ['user_id' => $user_id];

        //Get Purchase details
        $purchase_details = $this->transactionUtil->getPurchaseTotals(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        //Get Sell details
        $sell_details = $this->transactionUtil->getSellTotals(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $transaction_types = [
            'purchase_return', 'sell_return', 'expense', 'stock_adjustment', 'sell_transfer', 'purchase', 'sell'
        ];

        $transaction_totals = $this->transactionUtil->getTransactionTotals(
            $business_id,
            $transaction_types,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $gross_profit = $this->transactionUtil->getGrossProfit(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $data['total_purchase_shipping_charge'] = !empty($purchase_details['total_shipping_charges']) ? $purchase_details['total_shipping_charges'] : 0;
        $data['total_sell_shipping_charge'] = !empty($sell_details['total_shipping_charges']) ? $sell_details['total_shipping_charges'] : 0;
        //Shipping
        $data['total_transfer_shipping_charges'] = !empty($transaction_totals['total_transfer_shipping_charges']) ? $transaction_totals['total_transfer_shipping_charges'] : 0;
        //Discounts
        $total_purchase_discount = $transaction_totals['total_purchase_discount'];
        // $total_sell_discount = $transaction_totals['total_sell_discount'];
        $total_reward_amount = $transaction_totals['total_reward_amount'];
        $total_sell_round_off = $transaction_totals['total_sell_round_off'];


        $data['total_purchase_discount'] = !empty($total_purchase_discount) ? $total_purchase_discount : 0;

        $data['total_sell_round_off'] = !empty($total_sell_round_off) ? $total_sell_round_off : 0;

        //Expense
        $data['total_expense'] =  $transaction_totals['total_expense'];

        //Stock adjustments
        $data['total_adjustment'] = $transaction_totals['total_adjustment'];
        $data['total_recovered'] = $transaction_totals['total_recovered'];


        $data['total_reward_amount'] = !empty($total_reward_amount) ? $total_reward_amount : 0;

        $moduleUtil = new ModuleUtil();

        $module_parameters = [
            'business_id' => $business_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location_id' => $location_id,
            'user_id' => $user_id
        ];
        $modules_data = $moduleUtil->getModuleData('profitLossReportData', $module_parameters);

        $data['left_side_module_data'] = [];
        $data['right_side_module_data'] = [];
        $module_total = 0;
        if (!empty($modules_data)) {
            foreach ($modules_data as $module_data) {
                if (!empty($module_data[0])) {
                    foreach ($module_data[0] as $array) {
                        $data['left_side_module_data'][] = $array;
                        if (!empty($array['add_to_net_profit'])) {
                            $module_total -= $array['value'];
                        }
                    }
                }
                if (!empty($module_data[1])) {
                    foreach ($module_data[1] as $array) {
                        $data['right_side_module_data'][] = $array;
                        if (!empty($array['add_to_net_profit'])) {
                            $module_total += $array['value'];
                        }
                    }
                }
            }
        }


        $data['net_profit'] = $module_total + $gross_profit
            + ($data['total_sell_round_off'] + $data['total_recovered'] + $data['total_sell_shipping_charge'] + $data['total_purchase_discount']
            ) - ($data['total_reward_amount'] + $data['total_expense'] + $data['total_adjustment'] + $data['total_transfer_shipping_charges'] + $data['total_purchase_shipping_charge']
            );

        return $data;
    }



    public function abc()
    {
    }
}
