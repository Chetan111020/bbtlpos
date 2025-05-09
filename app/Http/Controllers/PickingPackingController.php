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
use App\TransactionPayment;

use DateTime;

use Illuminate\Notifications\DatabaseNotification;

class PickingPackingController extends Controller
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

        return view('dashboard.picking_packing', compact('totalReceivable'));
    }

      public function GetPickingTotal()
    {
        $business_id = request()->session()->get('user.business_id');

        $start = '';
        $end = '';
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
        }

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
            ->where('order_picking_status', '=', '2')
            ->where('order_packing_status', '=', '1')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->count();

        $complete_order = Transaction::where('type', 'sell')
            ->where('status', 'final')
            ->where('order_picking_status', '=', '2')
            ->where('order_packing_status', '=', '2')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            // ->where('shipping_status',['ordered','shipping'])
            ->count();

        $pending_order = Transaction::where('type', 'sell')
            ->where('status', 'final')
            ->where('order_packing_status', '!=', '2')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            // ->where('shipping_status','shipping')
            ->count();
        $total = [
            $received,
            $picking_complete,
            $picking_start,
            $packing_started,
            $complete_order,
            $pending_order
        ];

        return $total;
    }

    public function pickingchart()
    {
        $start = request()->start;
        $end = request()->end;

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

    public function TotalOrderChart()
    {
        $start = request()->start;
        $end = request()->end;
        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);
        $complete_order = Transaction::where('type', 'sell')
            ->where('status', 'final')
            ->select(DB::raw("(COUNT(*)) as count"), DB::raw('DATE(transaction_date) as trdate'),)
            ->where('order_picking_status', '=', '2')->where('order_packing_status', '=', '2')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();

        $pending_order = Transaction::where('type', 'sell')
            ->where('status', 'final')
            ->select(DB::raw("(COUNT(*)) as count1"), DB::raw('DATE(transaction_date) as trdate'),)
            // ->where('order_picking_status','!=' ,'2')
            ->where('order_packing_status', '!=', '2')
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();

        $total_order = Transaction::where('type', 'sell')
            ->where('status', 'final')
            ->select(DB::raw("(COUNT(*)) as total_order"), DB::raw('DATE(transaction_date) as trdate'),)
            ->whereDate('transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('trdate')
            ->orderBy('trdate')
            ->get();

        $dates = [];
        for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
            array_push($dates, $i->format('Y-m-d'));
        }

        $all_complete_order = [];
        $n = 0;
        $m = 0;
        $o = 0;
        $all_pending_order = [];
        $all_order = [];
        foreach ($dates as $date) {

            if (in_array($date, array_column($complete_order->toArray(), 'trdate'))) {
                array_push($all_complete_order, $complete_order[$n]->count ?? 0);
                $n++;
            } else {
                array_push($all_complete_order, 0);
            }

            if (in_array($date, array_column($pending_order->toArray(), 'trdate'))) {
                array_push($all_pending_order, $pending_order[$m]->count1 ?? 0);
                $m++;
            } else {
                array_push($all_pending_order, 0);
            }

            if (in_array($date, array_column($total_order->toArray(), 'trdate'))) {
                array_push($all_order, $total_order[$o]->total_order ?? 0);
                $o++;
            } else {
                array_push($all_order, 0);
            }
        }

        $mainChart = [
            $all_complete_order,
            $all_pending_order,
            $all_order,
            $dates
        ];
        return $mainChart;
    }

    public function TotalPaymentChart()
    {
        $start = request()->start;
        $end = request()->end;

        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);

        $totalPayment = TransactionPayment::whereNull('parent_id')
            ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
            ->sum('amount');

        $chequePayment = TransactionPayment::whereNull('parent_id')
            ->where('method', 'cheque')
            ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
            ->sum('amount');

        $cashPayment = TransactionPayment::whereNull('parent_id')
            ->where('method', 'cash')
            ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
            ->sum('amount');

        $zellePayment = TransactionPayment::whereNull('parent_id')
            ->where('method', 'zelle')
            ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
            ->sum('amount');

        $creditPayment = TransactionPayment::whereNull('parent_id')
            ->where('method', 'credit')
            ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
            ->sum('amount');

        $otherPayment = TransactionPayment::whereNull('parent_id')
            ->whereNotIn('method', ['cheque', 'cash', 'zelle', 'credit'])
            ->whereDate('paid_on', '>=', $dtbefore->format('Y-m-d'))
            ->whereDate('paid_on', '<=', $dtcurr->format('Y-m-d'))
            ->sum('amount');
        $chart = [
            $chequePayment,
            $cashPayment,
            $zellePayment,
            $creditPayment,
            $otherPayment,
            $totalPayment,
        ];

        return $chart;
    }
     public function WeeklyWebsiteOrders()
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        $weeklyData = DB::table('transactions')
                    ->where('type', 'sell')
                    ->whereIn('status', ['draft', 'final'])
                    ->where('is_quotation', 0)
                    ->whereNotNull('woocommerce_order_id')
                    ->select(
                        DB::raw('SUM(final_total) as total_draft_amount'),
                        DB::raw('COUNT(id) as orders'),
                        DB::raw('DATE_FORMAT(transaction_date, "%W") as day_of_week')
                    )
                    ->whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
                    ->groupBy('day_of_week')
                    ->orderByRaw('DAYOFWEEK(transaction_date)')
                    ->get();

        return $weeklyData;

    }
}


