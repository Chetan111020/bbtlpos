<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use DB;
use Carbon\Carbon;
use App\Utils\ModuleUtil;
use App\Charts\CommonChart;
use App\Currency;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\VariationLocationDetails;
use Datatables;
use App\Utils\Util;
use App\Utils\RestaurantUtil;
use App\User;
use App\Product;
use App\Contact;
use App\Variation;
use Modules\SmartCRM\Models\FollowUp;
use DateTime;

class CashierController extends Controller
{
    public function Index()
    {
        $business_id = $request->session()->get('user.business_id');

        $customers = Contact::customersDropdown($business_id, false);
        $users = User::forDropdown($business_id);
        $status = FollowUp::keyValueMap(FollowUp::STATUS);
        $priorities = FollowUp::keyValueMap(FollowUp::PRIORITIES);
        $channel = FollowUp::keyValueMap(FollowUp::CHANNEL);

         $allCustomer = Contact::where('business_id', $business_id)
        ->where('type', 'customer')
        ->active()
        ->count();
        $openStatus = FollowUp::where('status', 'open')->count();
        $inProcessStatus = FollowUp::where('status', 'in_process')->count();
        $closedStatus = FollowUp::where('status', 'closed')->count();

        return view('dashboard.cashier_Index', compact('customers', 'allCustomer', 'openStatus', 'inProcessStatus', 'closedStatus', 'users', 'status', 'priorities', 'channel'));
        // return view('dashboard.cashier_Index');
    }

    public function getFinalTransactions()
    {
        // $start = '';
        //     $end = '';
        //     if (!empty(request()->start_date) && !empty(request()->end_date)) {
        //         $start = request()->start_date;
        //         $end =  request()->end_date;
        //     }

        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $final = Transaction::where('transactions.type', 'sell')
        ->where('status', 'final')
        ->where('is_direct_sale', 0)
        ->join('contacts', 'transactions.contact_id','=','contacts.id')
        ->select(
            'contacts.name',
        'transactions.final_total','invoice_no')
        ->orderBy('transactions.transaction_date', 'desc')
        ->groupBy('transactions.id')
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contacts.id',array_column($customers->toArray(), 'id'));
        })
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->limit(10)
        ->get();
        // ->whereBetween('transactions.created_at',[$start,$end])
        // ->whereDate('transactions.transaction_date', '>=' ,$start)
        // ->whereDate('transactions.transaction_date', '<=', $end)
        // ->get();

        return $final;
        // return view('dashboard.cashier_Index',compact('draft','quotation','final'));
    }

    public function GetQuotation()
    {
        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $quotation = Transaction::where('transactions.type', 'sell')
        // ->where('is_direct_sale', 0)
        ->where('transactions.status', 'draft')
        ->where('is_quotation', 1)
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contacts.id',array_column($customers->toArray(), 'id'));
        })
        ->join('contacts', 'transactions.contact_id','=','contacts.id')
        ->select('contacts.name', 'transactions.final_total','invoice_no','contacts.mobile')
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->orderBy('transactions.transaction_date', 'desc')
        ->groupBy('transactions.id')
        ->limit(10)
        ->get();

        return $quotation;
    }

    public function GetDraft()
    {
        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $draft = Transaction::where('transactions.type', 'sell')
        ->where('is_direct_sale', 0)
        ->where('transactions.status', 'draft')
        ->where('is_quotation', 0)
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contacts.id',array_column($customers->toArray(), 'id'));
        })
        ->join('contacts', 'transactions.contact_id','=','contacts.id')
        ->select('contacts.name', 'transactions.final_total','invoice_no','contacts.mobile')
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->orderBy('transactions.transaction_date', 'desc')
        ->groupBy('transactions.id')
        ->limit(10)
        ->get();

        return $draft;
    }

    public function GetPartialInvoices()
    {
        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $draft = Transaction::where('transactions.type', 'sell')
        ->where('is_direct_sale', 0)
        ->where('transactions.status', 'final')
        ->where('transactions.payment_status', 'partial')
        ->where('is_quotation', 0)
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contacts.id',array_column($customers->toArray(), 'id'));
        })
        ->join('contacts', 'transactions.contact_id','=','contacts.id')
        ->select('contacts.name', 'transactions.final_total','invoice_no','contacts.mobile')
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->orderBy('transactions.transaction_date', 'desc')
        ->groupBy('transactions.id')
        ->limit(10)
        ->get();

        return $draft;
    }

    public function GetDueInvoices()
    {
        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $draft = Transaction::where('transactions.type', 'sell')
        ->where('is_direct_sale', 0)
        ->where('transactions.status', 'final')
        ->where('transactions.payment_status', 'due')
        ->where('is_quotation', 0)
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contacts.id',array_column($customers->toArray(), 'id'));
        })
        ->join('contacts', 'transactions.contact_id','=','contacts.id')
        ->select('contacts.name', 'transactions.final_total','invoice_no','contacts.mobile')
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->orderBy('transactions.transaction_date', 'desc')
        ->groupBy('transactions.id')
        ->limit(10)
        ->get();

        return $draft;
    }

    public function GetPaidInvoices()
    {
        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $draft = Transaction::where('transactions.type', 'sell')
        ->where('is_direct_sale', 0)
        ->where('transactions.status', 'final')
        ->where('transactions.payment_status', 'paid')
        ->where('is_quotation', 0)
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contacts.id',array_column($customers->toArray(), 'id'));
        })
        ->join('contacts', 'transactions.contact_id','=','contacts.id')
        ->select('contacts.name', 'transactions.final_total','invoice_no','contacts.mobile')
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->orderBy('transactions.transaction_date', 'desc')
        ->groupBy('transactions.id')
        ->limit(10)
        ->get();

        return $draft;
    }
    public function get_totals()
    {
        $start = '2022-01-01';
        $end = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $draft = Transaction::where('transactions.type', 'sell')
        ->where('is_direct_sale', 0)
        ->where('transactions.status', 'draft')
        ->where('is_quotation', 0)
        // ->join('contacts', 'transactions.contact_id','=','contacts.id')
        // ->select('contacts.name', 'transactions.final_total','invoice_no')
        // ->orderBy('transactions.created_at', 'desc')
        // ->groupBy('transactions.id')
        // ->get()
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contact_id',array_column($customers->toArray(), 'id'));
        })
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->count();

        $quotation = Transaction::where('transactions.type', 'sell')
        ->where('is_direct_sale', 0)
        ->where('transactions.status', 'draft')
        ->where('is_quotation', 1)
        // ->join('contacts', 'transactions.contact_id','=','contacts.id')
        // ->select('contacts.name', 'transactions.final_total','invoice_no')
        // ->orderBy('transactions.created_at', 'desc')
        // ->groupBy('transactions.id')
        // ->get()
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contact_id',array_column($customers->toArray(), 'id'));
        })
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->count();

        $final = Transaction::where('transactions.type', 'sell')
        ->where('transactions.status', 'final')
        // ->where('is_direct_sale', 0)
        // ->join('contacts', 'transactions.contact_id','=','contacts.id')
        // ->select('contacts.name',
        // 'transactions.final_total','invoice_no')
        // ->orderBy('transactions.created_at', 'desc')
        // ->groupBy('transactions.id')
        // ->get()
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contact_id',array_column($customers->toArray(), 'id'));
        })
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->count();

        // $start = '2021-05-31';
        // $end = \Carbon::now();
        // $start = Carbon::now()->subDays(6);
        // $end = Carbon::now();
        $business_id = request()->session()->get('user.business_id');
        $total_sell = $this->getSale($business_id,$start,$end);

        $customer = Contact::where('business_id', $business_id)
                        ->where('type', 'customer')
                        ->active()
                        ->count();

        $total_transactions = $draft + $quotation + $final;
        $output['draft'] = $draft;
        $output['total_transactions'] = $final;
        $output['total_sell'] = $total_sell;


        // $output['quotation'] = $quotation;
        // $output['final'] = $final;

        return $output;
    }

    public function getSale($business_id)
    {
        $start_date = '2022-01-01';
        $end_date = Carbon::today()->toDateString();

        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        // $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $with = [];
        $sells = Transaction::select(
            DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                    TP.transaction_id=transactions.id) as total_paid'),
            'discount_amount',
            'discount_type',
            'total_before_tax',
            'final_total'
            )
            ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
                $query->whereIn('contact_id',array_column($customers->toArray(), 'id'));
            })
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final');

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $sells->where('transactions.created_by', request()->session()->get('user.id'));
        }

        if (!empty($start_date) && !empty($end_date)) {
            $sells->whereDate('transactions.transaction_date', '>=', $start_date)
                ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        // if ($is_woocommerce) {
        //     $sells->addSelect('transactions.woocommerce_order_id');
        //     if (request()->only_woocommerce_sells) {
        //         $sells->whereNotNull('transactions.woocommerce_order_id');
        //     }
        // }

        $sells->groupBy('transactions.id');

        $result = $sells->get();
        $dueSum = $result->sum(function ($row) {
            $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

            if (!empty($discount) && $row->discount_type == 'percentage') {
                $discount = $row->total_before_tax * ($discount / 100);
            }

            return ($row->final_total + $discount);
        });
        return $dueSum;
    }


    public function total_sales()
    {
        // $start = Carbon::now()->subDays(6);
        // $end = Carbon::now();

        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();
        $dtcurr = new DateTime($end);
        $dtbefore = new DateTime($start);
        // $dtcurr = new DateTime($end);
        // $dtbefore = new DateTime($start);


        $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());

        $sell = DB::table('transactions as t')
        ->where('type', 'sell')
        ->where('status', 'final')
        ->select(DB::raw('DATE(t.transaction_date) as trdate'),
        // DB::raw("SUM(t.final_total) - SUM(t.total_before_tax * IF(discount_type='percentage',(t.discount_amount/100),0)) as sales"),

        // DB::raw("SUM(final_total) - SUM(t.total_before_tax * (t.discount_amount/100)) as sales"),

        // DB::raw('SUM(tr.unit_price * tr.quantity) as sales')
        DB::raw('SUM(t.final_total + IF(t.discount_type="percentage", (t.total_before_tax * (t.discount_amount / 100)), t.discount_amount)) as sales')
        )
        ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
            $query->whereIn('contact_id',array_column($customers->toArray(), 'id'));
        })
        ->whereDate('t.transaction_date', '>=' ,$dtbefore)
        ->whereDate('t.transaction_date', '<=', $dtcurr)
        ->groupBy('trdate')
        ->orderBy('trdate')
        ->get();

    // $sell = $this->getSaleData($business_id, $dtbefore, $dtcurr);
    // return collect($dueSum)->toArray();

    $dates = [];
    for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
        array_push($dates, $i->format('Y-m-d'));
    }

    $all_sell = [];
    $n = 0;
    foreach ($dates as $date) {

        if (in_array($date, array_column(collect($sell)->toArray(), 'trdate'))) {
            array_push($all_sell, $sell[$n]->sales ?? 0);
            $n++;
        } else {
            array_push($all_sell, 0);
        }
    }

    $mainChart = [
        $all_sell,
        $dates
    ];
    return $mainChart;
    }

    public function getdata()
    {
        $start = '2022-01-01';

        $end = Carbon::today()->toDateString();


        $final = Transaction::where('transactions.type', 'sell')
        ->where('is_direct_sale', 0)
        ->join('contacts', 'transactions.contact_id','=','contacts.id')
        ->select(
            'contacts.name',
        'transactions.final_total','invoice_no')
        // ->whereBetween('transactions.created_at',[$start,$end])
        // ->groupBy('transactions.id')
        ->limit(10)
        // ->get();
        ->where('transactions.status','final')
        ->whereDate('transactions.transaction_date', '>=' ,$start)
        ->whereDate('transactions.transaction_date', '<=', $end)
        ->orderBy('transactions.created_at', 'desc')
        ->groupBy('transactions.id')
        ->get();

        return json_encode($final);
    }

    public function GoogleMap(Request $request)
    {
        if ($request->ajax()) {
             $today = Carbon::today()->toDateString();

            $customers = Contact::getOwnCustomers(4, false, false, FollowUp::viewOwnCustomersOnly());
            $googlemap = DB::table('contacts as c')
            ->leftJoin('transactions as t', 'c.id', '=', 't.contact_id')
            ->select(
                DB::raw('SUM(if(t.type="sell" and t.status = "final",t.final_total,0 )) as sale'),
                'c.lat',
                'c.lgn',
                'c.name',
                'c.id as customer_id'
            )
            ->when(FollowUp::viewOwnCustomersOnly(), function($query) use ($customers){
                $query->whereIn('c.id',array_column($customers->toArray(), 'id'));
            })
            // ->where('t.type', 'sell')
            // ->where('t.status', 'final')
            // ->when(
            //     !auth()->user()->hasRole('Admin#' . auth()->user()->business_id)
            //     && !auth()->user()->hasRole('Administration#' . auth()->user()->business_id),
            //     function ($query) {
            //         $query->where(function ($data) {
            //             $data->where('t.created_by', request()->session()->get('user.id'))
            //                 ->orWhere('c.sales_rep', auth()->user()->id);
            //         });
            //     }
            // )
            ->groupBy('c.lat', 'c.lgn');

            $map = $googlemap->get();

            $leadsData = DB::table('leads')
                ->select('lat', 'lgn', 'name')
                ->when(FollowUp::viewOwnCustomersOnly(), function($query){
                    $query->where('created_by', auth()->user()->id);
                })
                ->get();

            $contactsWithFollowUps = DB::table('follow_ups')
            ->whereDate('scheduled_at', '<=', $today)
            ->pluck('contact_id')
            ->all();

            $lat = [];
            $lgn = [];
            $name = [];
            $sale = [];
            $customer_id = [];
            $has_follow_up = [];

            foreach ($map as $data) {
                $lat[] = round($data->lat, 7);
                $lgn[] = round($data->lgn, 7);
                $name[] = $data->name;
                $sale[] = round($data->sale, 2);
                $customer_id[] = $data->customer_id;
                $has_follow_up[] = in_array($data->customer_id, $contactsWithFollowUps);
            }

            $locationData = [];
            for ($i = 0; $i < count($lat); $i++) {
                $locationData[$i][] = $lat[$i];
                $locationData[$i][] = $lgn[$i];
                $locationData[$i][] = $name[$i];
                $locationData[$i][] = $sale[$i];
                $locationData[$i][] = $customer_id[$i];
                $locationData[$i][] = $has_follow_up[$i];
            }

             $leadsLocationData = [];
                foreach ($leadsData as $data) {
                    $leadsLocationData[] = [
                        round($data->lat, 7),
                        round($data->lgn, 7),
                        $data->name,
                    ];
                }
            // return $locationData;
                return ['salesData' => $locationData, 'leadsData' => $leadsLocationData];
        }

    }
}
