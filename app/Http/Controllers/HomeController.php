<?php

namespace App\Http\Controllers;

use App\BusinessLocation;

use App\Charts\CommonChart;
use App\Currency;
use App\Transaction;
use App\Utils\BusinessUtil;

use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\VariationLocationDetails;
use Datatables;
use DB;
use Illuminate\Http\Request;
use App\Utils\Util;
use App\Utils\RestaurantUtil;
use App\User;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;
use DateTime;
use App\TransactionPayment;

class HomeController extends Controller
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
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (auth()->user()->roles()->whereIn('id',[9,15])->exists()) {
            $start = \Carbon::create('2021-05-31')->startOfDay()->toDateString();
        $end = \Carbon::now()->subMonth()->endOfDay()->toDateString();
        $newbalance = Transaction::join('contacts as c','transactions.contact_id','=','c.id')
            ->where('transactions.type','sell')
            ->where('transactions.status','final')
            ->where('c.contact_status','active')
            ->where('transactions.payment_status','!=','paid')
            ->whereDate('transactions.transaction_date','>=',$start)
            ->whereDate('transactions.transaction_date','<=',$end)
            ->select('c.id','c.name as customer','transactions.id as transaction_id','c.email as email','c.mobile','c.balance','transactions.invoice_no','transactions.transaction_date','transactions.final_total','transactions.additional_notes','transactions.payment_status',
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                            TP.transaction_id=transactions.id) as total_paid')
            )->get();

            $Receivable = $newbalance->sum(function ($transaction) {
                return $transaction->final_total - $transaction->total_paid;
            });

           $totalReceivable = "$" . number_format($Receivable, 2, '.', ',');

            return view('home.index2', compact('totalReceivable'));
        }

        if (!auth()->user()->can('dashboard.data')) {
            return view('home.index');
        }


        // $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        // $date_filters['this_fy'] = $fy;
        // $date_filters['this_month']['start'] = date('Y-m-01');
        // $date_filters['this_month']['end'] = date('Y-m-t');
        // $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        // $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));

        // $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();

        // //Chart for sells last 30 days
        // $sells_last_30_days = $this->transactionUtil->getSellsLast30Days($business_id);
        // $labels = [];
        // $all_sell_values = [];
        // $dates = [];
        // for ($i = 29; $i >= 0; $i--) {
        //     $date = \Carbon::now()->subDays($i)->format('Y-m-d');
        //     $dates[] = $date;

        //     $labels[] = date('j M Y', strtotime($date));

        //     if (!empty($sells_last_30_days[$date])) {
        //         $all_sell_values[] = (float) $sells_last_30_days[$date];
        //     } else {
        //         $all_sell_values[] = 0;
        //     }
        // }

        // //Get sell for indivisual locations
        // $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        // $location_sells = [];
        // $sells_by_location = $this->transactionUtil->getSellsLast30Days($business_id, true);
        // foreach ($all_locations as $loc_id => $loc_name) {
        //     $values = [];
        //     foreach ($dates as $date) {
        //         $sell = $sells_by_location->first(function ($item) use ($loc_id, $date) {
        //             return $item->date == $date &&
        //                 $item->location_id == $loc_id;
        //         });

        //         if (!empty($sell)) {
        //             $values[] = (float) $sell->total_sells;
        //         } else {
        //             $values[] = 0;
        //         }
        //     }
        //     $location_sells[$loc_id]['loc_label'] = $loc_name;
        //     $location_sells[$loc_id]['values'] = $values;
        // }

        // $sells_chart_1 = new CommonChart;

        // $sells_chart_1->labels($labels)
        //                 ->options($this->__chartOptions(__(
        //                     'home.total_sells',
        //                     ['currency' => $currency->code]
        //                     )));

        // if (!empty($location_sells)) {
        //     foreach ($location_sells as $location_sell) {
        //         $sells_chart_1->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
        //     }
        // }

        // if (count($all_locations) > 1) {
        //     $sells_chart_1->dataset(__('report.all_locations'), 'line', $all_sell_values);
        // }

        // //Chart for sells this financial year
        // $sells_this_fy = $this->transactionUtil->getSellsCurrentFy($business_id, $fy['start'], $fy['end']);

        // $labels = [];
        // $values = [];

        // $months = [];
        // $date = strtotime($fy['start']);
        // $last   = date('m-Y', strtotime($fy['end']));

        // $fy_months = [];
        // do {
        //     $month_year = date('m-Y', $date);
        //     $fy_months[] = $month_year;

        //     $month_number = date('m', $date);

        //     $labels[] = \Carbon::createFromFormat('m-Y', $month_year)
        //                     ->format('M-Y');
        //     $date = strtotime('+1 month', $date);

        //     if (!empty($sells_this_fy[$month_year])) {
        //         $values[] = (float) $sells_this_fy[$month_year];
        //     } else {
        //         $values[] = 0;
        //     }
        // } while ($month_year != $last);

        // $fy_sells_by_location = $this->transactionUtil->getSellsCurrentFy($business_id, $fy['start'], $fy['end'], true);
        // $fy_sells_by_location_data = [];

        // foreach ($all_locations as $loc_id => $loc_name) {
        //     $values_data = [];
        //     foreach ($fy_months as $month) {
        //         $sell = $fy_sells_by_location->first(function ($item) use ($loc_id, $month) {
        //             return $item->yearmonth == $month &&
        //                 $item->location_id == $loc_id;
        //         });

        //         if (!empty($sell)) {
        //             $values_data[] = (float) $sell->total_sells;
        //         } else {
        //             $values_data[] = 0;
        //         }
        //     }
        //     $fy_sells_by_location_data[$loc_id]['loc_label'] = $loc_name;
        //     $fy_sells_by_location_data[$loc_id]['values'] = $values_data;
        // }

        // $sells_chart_2 = new CommonChart;
        // $sells_chart_2->labels($labels)
        //             ->options($this->__chartOptions(__(
        //                 'home.total_sells',
        //                 ['currency' => $currency->code]
        //                     )));
        // if (!empty($fy_sells_by_location_data)) {
        //     foreach ($fy_sells_by_location_data as $location_sell) {
        //         $sells_chart_2->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
        //     }
        // }
        // if (count($all_locations) > 1) {
        //     $sells_chart_2->dataset(__('report.all_locations'), 'line', $values);
        // }

        // //Get Dashboard widgets from module
        // $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        // $widgets = [];

        // foreach ($module_widgets as $widget_array) {
        //     if (!empty($widget_array['position'])) {
        //         $widgets[$widget_array['position']][] = $widget_array['widget'];
        //     }
        // }

        $start = \Carbon::create('2021-05-31')->startOfDay()->toDateString();
        $end = \Carbon::now()->subMonth()->endOfDay()->toDateString();
        $newbalance = Transaction::join('contacts as c','transactions.contact_id','=','c.id')
            ->where('transactions.type','sell')
            ->where('transactions.status','final')
            ->where('c.contact_status','active')
            ->where('transactions.payment_status','!=','paid')
            ->whereDate('transactions.transaction_date','>=',$start)
            ->whereDate('transactions.transaction_date','<=',$end)
            ->select('c.id','c.name as customer','transactions.id as transaction_id','c.email as email','c.mobile','c.balance','transactions.invoice_no','transactions.transaction_date','transactions.final_total','transactions.additional_notes','transactions.payment_status',
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                            TP.transaction_id=transactions.id) as total_paid')
            )->get();

            $Receivable = $newbalance->sum(function ($transaction) {
                return $transaction->final_total - $transaction->total_paid;
            });

           $totalReceivable = "$" . number_format($Receivable, 2, '.', ',');

        return view('home.index', compact('totalReceivable'));
    }
public function pickingchart()
    {
        $yesterday = Carbon::yesterday();

        $start = $yesterday->copy()->startOfDay();
        $end = $yesterday->copy()->endOfDay();


        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);

        $received = Transaction::where('type', 'sell')
            ->where('status', 'final')
            ->where('order_picking_status', '=', '0')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->count();

        $picking_complete = Transaction::where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('order_picking_status', '=', '2')
            ->where('order_packing_status', '=', '0')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->count();

        $picking_start = Transaction::where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('order_picking_status', '=', '1')->where('order_packing_status', '=', '0')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->count();


        $packing_started = Transaction::where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('order_picking_status', '=', '2')->where('order_packing_status', '=', '1')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->count();

        $complete_order = Transaction::where('type', 'sell')
        ->where('status', 'final')
        ->where('order_picking_status', '=', '2')->where('order_packing_status', '=', '2')
        ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
        ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
        // ->where('shipping_status',['ordered','shipping'])
        ->count();


        $total_order = Transaction::where('type', 'sell')
        ->where('status', 'final')
        ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
        ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
        ->count();

        $chart = [
            $received,
            $picking_start,
            $picking_complete,
            $packing_started,
            $complete_order,
            $total_order
        ];

        return $chart;
    }
    public function TotalPaymentChart()
    {
        $yesterday = Carbon::yesterday();

        $start = $yesterday->copy()->startOfDay();
        $end = $yesterday->copy()->endOfDay();

        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);


        $start = request()->start;
        $end = request()->end;
        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);

        $query = TransactionPayment::leftjoin('transactions as t', function ($join) {
            $join->on('transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', 4)
                ->whereIn('t.type', ['sell', 'opening_balance']);
            })
            ->leftjoin('contacts as c', 'transaction_payments.payment_for', '=', 'c.id')
            ->where('transaction_payments.business_id', 4)
                ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
                ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d') . "23:59:59")
            ->where(function ($q){
                $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('sell', 'opening_balance') AND transaction_payments.parent_id IS NULL)")
                    ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('sell', 'opening_balance') AND transactions.business_id = 4 AND tp.parent_id=transaction_payments.id)");
            });

        $payments = $query->get();

        $chequePayment = 0;
        $cashPayment = 0;
        $zellePayment = 0;
        $creditPayment = 0;
        $bankPayment = 0;
        $creditCardPayment = 0;
        $otherPayment = 0;
        $totalPayment = 0;

        foreach($payments as $payment){
            if($payment->method == 'cheque'){
                $chequePayment += round($payment->amount, 2);
            }
            elseif($payment->method == 'cash'){
                $cashPayment += round($payment->amount, 2);
            }
            elseif($payment->method == 'credit_memo'){
                $creditPayment += round($payment->amount, 2);
            }
            elseif($payment->method == 'custom_pay_3'){
                $zellePayment += round($payment->amount, 2);
            }
            elseif(in_array($payment->method, ['bank_transfer', 'custom_pay_1', 'custom_pay_2', 'custom_pay_6', 'custom_pay_7'])){
                $bankPayment += round($payment->amount, 2);
            }
            elseif($payment->method == 'card'){
                $creditCardPayment += round($payment->amount, 2);
            }
            else{
                $otherPayment += round($payment->amount, 2);
            }
            $totalPayment += round($payment->amount, 2);
        }

        // TransactionPayment::whereNull('parent_id')
        //     ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
        //     ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
        //     ->sum('amount');

        // $chequePayment = TransactionPayment::whereNull('parent_id')
        //     ->where('method', 'cheque')
        //     ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
        //     ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
        //     ->sum('amount');
        // $chequePayment = $query;
        // $chequePayment = $chequePayment->where('method', 'cheque')->sum('amount');

        // $cashPayment = TransactionPayment::whereNull('parent_id')
        //     ->where('method', 'cash')
        //     ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
        //     ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
        //     ->sum('amount');
        // $cashPayment = $query;
        // $cashPayment = $cashPayment->where('method', 'cash')->sum('amount');

        // $zellePayment = TransactionPayment::whereNull('parent_id')
        //     ->where('method', 'zelle')
        //     ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
        //     ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
        //     ->sum('amount');
        // $zellePayment = $query;
        // $zellePayment = $zellePayment->where('method', 'custom_pay_3')->sum('amount');

        // $creditPayment = TransactionPayment::whereNull('parent_id')
        //     ->where('method', 'credit')
        //     ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
        //     ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
        //     ->sum('amount');
        // $creditPayment = $query;
        // $creditPayment = $creditPayment->where('method', 'credit_memo')->sum('amount');

        // $otherPayment = TransactionPayment::whereNull('parent_id')
        //     ->whereNotIn('method', ['cheque', 'cash', 'zelle', 'credit'])
        //     ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
        //     ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
        //     ->sum('amount');
        // $otherPayment = $query;
        // $otherPayment = $otherPayment->whereNotIn('method', ['cheque', 'cash', 'custom_pay_3', 'credit_memo'])->sum('amount');

        $chart = [
            $chequePayment,
            $cashPayment,
            $zellePayment,
            $creditPayment,
            $bankPayment,
            $creditCardPayment,
            $otherPayment,
            $totalPayment,
        ];

        return $chart;
    }
    public function getGraph()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('dashboard.data')) {
            return view('home.graph');
        }

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $date_filters['this_fy'] = $fy;
        $date_filters['this_month']['start'] = date('Y-m-01');
        $date_filters['this_month']['end'] = date('Y-m-t');
        $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));

        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();

        //Chart for sells last 30 days
        $sells_last_30_days = $this->transactionUtil->getSellsLast30Days($business_id);

        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            if (!empty($sells_last_30_days[$date])) {
                $all_sell_values[] = (float) $sells_last_30_days[$date];
            } else {
                $all_sell_values[] = 0;
            }
        }

        //Get sell for indivisual locations
        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        $location_sells = [];
        $sells_by_location = $this->transactionUtil->getSellsLast30Days($business_id, true);
        foreach ($all_locations as $loc_id => $loc_name) {
            $values = [];
            foreach ($dates as $date) {
                $sell = $sells_by_location->first(function ($item) use ($loc_id, $date) {
                    return $item->date == $date &&
                        $item->location_id == $loc_id;
                });

                if (!empty($sell)) {
                    $values[] = (float) $sell->total_sells;
                } else {
                    $values[] = 0;
                }
            }
            $location_sells[$loc_id]['loc_label'] = $loc_name;
            $location_sells[$loc_id]['values'] = $values;
        }

        $sells_chart_1 = new CommonChart;

        $sells_chart_1->labels($labels)
                        ->options($this->__chartOptions(__(
                            'home.total_sells',
                            ['currency' => $currency->code]
                            )));

        if (!empty($location_sells)) {
            foreach ($location_sells as $location_sell) {
                $sells_chart_1->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }

        if (count($all_locations) > 1) {
            $sells_chart_1->dataset(__('report.all_locations'), 'line', $all_sell_values);
        }

        //Chart for sells this financial year
        $sells_this_fy = $this->transactionUtil->getSellsCurrentFy($business_id, $fy['start'], $fy['end']);

        $labels = [];
        $values = [];

        $months = [];
        $date = strtotime($fy['start']);
        $last   = date('m-Y', strtotime($fy['end']));

        $fy_months = [];
        do {
            $month_year = date('m-Y', $date);
            $fy_months[] = $month_year;

            $month_number = date('m', $date);

            $labels[] = \Carbon::createFromFormat('m-Y', $month_year)
                            ->format('M-Y');
            $date = strtotime('+1 month', $date);

            if (!empty($sells_this_fy[$month_year])) {
                $values[] = (float) $sells_this_fy[$month_year];
            } else {
                $values[] = 0;
            }
        } while ($month_year != $last);

        $fy_sells_by_location = $this->transactionUtil->getSellsCurrentFy($business_id, $fy['start'], $fy['end'], true);
        $fy_sells_by_location_data = [];

        foreach ($all_locations as $loc_id => $loc_name) {
            $values_data = [];
            foreach ($fy_months as $month) {
                $sell = $fy_sells_by_location->first(function ($item) use ($loc_id, $month) {
                    return $item->yearmonth == $month &&
                        $item->location_id == $loc_id;
                });

                if (!empty($sell)) {
                    $values_data[] = (float) $sell->total_sells;
                } else {
                    $values_data[] = 0;
                }
            }
            $fy_sells_by_location_data[$loc_id]['loc_label'] = $loc_name;
            $fy_sells_by_location_data[$loc_id]['values'] = $values_data;
        }

        $sells_chart_2 = new CommonChart;
        $sells_chart_2->labels($labels)
                    ->options($this->__chartOptions(__(
                        'home.total_sells',
                        ['currency' => $currency->code]
                            )));
        if (!empty($fy_sells_by_location_data)) {
            foreach ($fy_sells_by_location_data as $location_sell) {
                $sells_chart_2->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }
        if (count($all_locations) > 1) {
            $sells_chart_2->dataset(__('report.all_locations'), 'line', $values);
        }

        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        $widgets = [];

        foreach ($module_widgets as $widget_array) {
            if (!empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }

        return view('home.graph', compact('date_filters', 'sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations'));
    }
    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end, $location_id);

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id);

            $transaction_types = [
                'purchase_return', 'sell_return', 'expense'
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end,
                $location_id
            );

            $total_purchase_inc_tax = !empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];

            $total_purchase = $total_purchase_inc_tax - $total_purchase_return_inc_tax;
            $output = $purchase_details;
            $output['total_purchase'] = $total_purchase;

            $total_sell_inc_tax = !empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $total_sell_return_inc_tax = !empty($transaction_totals['total_sell_return_inc_tax']) ? $transaction_totals['total_sell_return_inc_tax'] : 0;

            $output['total_sell'] = $total_sell_inc_tax - $total_sell_return_inc_tax;

            $output['invoice_due'] = $sell_details['invoice_due'];
            $output['total_expense'] = $transaction_totals['total_expense'];

            return $output;
        }
    }

    /**
     * Retrieves sell products whose available quntity is less than alert quntity.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductStockAlert()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $query = VariationLocationDetails::join(
                'product_variations as pv',
                'variation_location_details.product_variation_id',
                '=',
                'pv.id'
            )
                    ->join(
                        'variations as v',
                        'variation_location_details.variation_id',
                        '=',
                        'v.id'
                    )
                    ->join(
                        'products as p',
                        'variation_location_details.product_id',
                        '=',
                        'p.id'
                    )
                    ->leftjoin(
                        'business_locations as l',
                        'variation_location_details.location_id',
                        '=',
                        'l.id'
                    )
                    ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                    ->where('p.business_id', 4)
                    ->where('p.enable_stock', 1)
                    ->where('p.is_inactive', 0)
                    ->whereRaw('variation_location_details.qty_available <= p.alert_quantity')
                    ->whereRaw('variation_location_details.qty_available > 0');

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('variation_location_details.location_id', $permitted_locations);
            }

            $products = $query->select(
                'p.name as product',
                'p.type',
                'pv.name as product_variation',
                'v.name as variation',
                'p.item_code as location',
                'variation_location_details.qty_available as stock',
                'u.short_name as unit'
            )
                    ->groupBy('variation_location_details.id')
                    ->orderBy('stock', 'asc');

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    if ($row->type == 'single') {
                        return $row->product;
                    } else {
                        return $row->product . ' - ' . $row->product_variation . ' - ' . $row->variation;
                    }
                })
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0 ;
                    return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false>'. (float)$stock . '</span> ' . $row->unit;
                })
                ->removeColumn('unit')
                ->removeColumn('type')
                ->removeColumn('product_variation')
                ->removeColumn('variation')
                ->rawColumns([2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchasePaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format("Y-m-d H:i:s");

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase')
                    ->where('transactions.payment_status', '!=', 'paid');
              //      ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(c.pay_term_type = 'days', c.pay_term_number, 30 * c.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            $dues =  $query->select(
                'transactions.id as id',
                'c.name as supplier',
                'ref_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                        ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;
                    return '<span class="display_currency" data-currency_symbol="true">' .
                    $due . '</span>';
                })
                ->editColumn('ref_no', function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        return  '<a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->ref_no . '</a>';
                    }
                    return $row->ref_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([1, 2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesPaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format("Y-m-d H:i:s");

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.payment_status', '!=', 'paid');
              //      ->whereNotNull('transactions.pay_term_number')
              //      ->whereNotNull('transactions.pay_term_type')
                //    ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            $dues =  $query->select(
                'transactions.id as id',
                'c.name as customer',
                'transactions.invoice_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                        ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;
                    return '<span class="display_currency" data-currency_symbol="true">' .
                    $due . '</span>';
                })
                ->editColumn('invoice_no', function ($row) {
                    if (auth()->user()->can('sell.view')) {
                        return  '<a href="#" data-href="' . action('SellController@show', [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->invoice_no . '</a>';
                    }
                    return $row->invoice_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([1, 2])
                ->make(false);
        }
    }

    public function loadMoreNotifications()
    {
        $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10);

        if (request()->input('page') == 1) {
            auth()->user()->unreadNotifications->markAsRead();
        }
        $notifications_data = $this->commonUtil->parseNotifications($notifications);

        return view('layouts.partials.notification_list', compact('notifications_data'));
    }

    /**
     * Function to count total number of unread notifications
     *
     * @return json
     */
    public function getTotalUnreadNotifications()
    {
        $unread_notifications = auth()->user()->unreadNotifications;
        $total_unread = $unread_notifications->count();

        $notification_html = '';
        $modal_notifications = [];
        foreach ($unread_notifications as $unread_notification) {
            if (isset($data['show_popup'])) {
                $modal_notifications[] = $unread_notification;
                $unread_notification->markAsRead();
            }
        }
        if (!empty($modal_notifications)) {
            $notification_html = view('home.notification_modal')->with(['notifications' => $modal_notifications])->render();
        }

        return [
            'total_unread' => $total_unread,
            'notification_html' => $notification_html
        ];
    }

    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                    'title' => [
                        'text' => $title
                    ]
                ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical'
            ],
        ];
    }

    public function getCalendar()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->restUtil->is_admin(auth()->user(), $business_id);
        $is_superadmin = auth()->user()->can('superadmin');
        if (request()->ajax()) {
            $data = [
                'start_date' => request()->start,
                'end_date' => request()->end,
                'user_id' => ($is_admin || $is_superadmin) && !empty(request()->user_id) ? request()->user_id : auth()->user()->id,
                'location_id' => !empty(request()->location_id) ? request()->location_id : null,
                'business_id' => $business_id,
                'events' => request()->events ?? [],
                'color' => '#007FFF'
            ];
            $events = [];

            if (in_array('bookings', $data['events'])) {
                $events = $this->restUtil->getBookingsForCalendar($data);
            }

            $module_events = $this->moduleUtil->getModuleData('calendarEvents', $data);

            foreach ($module_events as $module_event) {
                $events = array_merge($events, $module_event);
            }

            return $events;
        }

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        $users = [];
        if ($is_admin) {
            $users = User::forDropdown($business_id, false);
        }

        $event_types = [
            'bookings' => [
                'label' => __('restaurant.bookings'),
                'color' => '#007FFF'
            ]
        ];
        $module_event_types = $this->moduleUtil->getModuleData('eventTypes');
        foreach ($module_event_types as $module_event_type) {
            $event_types = array_merge($event_types, $module_event_type);
        }

        return view('home.calendar')->with(compact('all_locations', 'users', 'event_types'));
    }

    public function showNotification($id)
    {
        $notification = DatabaseNotification::find($id);

        $data = $notification->data;

        $notification->markAsRead();

        return view('home.notification_modal')->with([
                'notifications' => [$notification]
            ]);
    }
}
