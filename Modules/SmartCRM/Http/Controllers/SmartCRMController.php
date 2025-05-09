<?php

namespace Modules\SmartCRM\Http\Controllers;

use App\Business;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SmartCRM\Models\FollowUp;
use App\User;
use App\Contact;
use Carbon\Carbon;

class SmartCRMController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $customers = Contact::customersCompanyDropdown($business_id, false, false, FollowUp::viewOwnCustomersOnly());
        $users = User::forDropdown($business_id);
        $status = FollowUp::keyValueMap(FollowUp::STATUS);
        $priorities = FollowUp::keyValueMap(FollowUp::PRIORITIES);
        $channel = FollowUp::keyValueMap(FollowUp::CHANNEL);

        //  $allCustomer = Contact::where('business_id', $business_id)
        // ->where('type', 'customer')
        // ->active()
        // ->count();

        $allCustomer = count($customers);

        $openStatus = FollowUp::where('status', 'open')
        ->when(FollowUp::viewOwnCustomersOnly(), function($query){
            $query->where('assigned_to', auth()->user()->id);
        })->count();
        $inProcessStatus = FollowUp::where('status', 'in_process')
        ->when(FollowUp::viewOwnCustomersOnly(), function($query){
            $query->where('assigned_to', auth()->user()->id);
        })->count();
        $closedStatus = FollowUp::where('status', 'closed')
        ->when(FollowUp::viewOwnCustomersOnly(), function($query){
            $query->where('assigned_to', auth()->user()->id);
        })->count();
        // return view('dashboard.cashier_Index', compact('customers', 'allCustomer', 'openStatus', 'inProcessStatus', 'closedStatus', 'users', 'status', 'priorities', 'channel'));
        return view('smartcrm::dashboard', compact('customers', 'allCustomer', 'openStatus', 'inProcessStatus', 'closedStatus', 'users', 'status', 'priorities', 'channel'));
    }

    public function fetchChartData(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();

        $date_format = $business->date_format ?? 'm/d/Y'; // selected format
        $timezone = $business->time_zone ?? 'America/New_York'; // correct timezone string
        $currency_id = $business->currency_id ?? 2;

        // Convert date_from
        if ($request->date_from) {
            $date_from = Carbon::parse($request->date_from)
                ->timezone($timezone)
                ->format($date_format);
        } else {
            $date_from = Carbon::now($timezone)->subDays(7)->format($date_format);
        }

        // Convert date_to
        if ($request->date_to) {
            $date_to = Carbon::parse($request->date_to)
                ->timezone($timezone)
                ->format($date_format);
        } else {
            $date_to = Carbon::now($timezone)->format($date_format);
        }

        $followups = FollowUp::chartData($business_id);

        $data = [
            'data' => $followups,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'timezone' => $timezone,
            'currency_id' => $currency_id,
            'date_format' => $date_format,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data fetched successfully',
            'data' => $data,
        ], 200);
    }


    public function fetchChartDataRaw(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();

        $currency_id = $business->currency_id ?? 2;

        if ($request->date_from) {
            $date_from = Carbon::parse($request->date_from);
        } else {
            $date_from = Carbon::now()->subDays(7);
        }

        if ($request->date_to) {
            $date_to = Carbon::parse($request->date_to);
        } else {
            $date_to = Carbon::now();
        }

        $data = [
            'date_from' => $date_from,
            'date_to' => $date_to,    
            'currency_id' => $currency_id,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data fetched successfully',
            'data' => $data,
        ], 200);
    }


    public function fetchBarChartData(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
    
        $date_from = $request->date_from ?? \Carbon\Carbon::now()->subDays(7)->format('Y-m-d');
        $date_to = $request->date_to ?? \Carbon\Carbon::now()->format('Y-m-d');
    
        $actual_data = Transaction::where('business_id', $business_id)
            ->whereBetween('created_at', [$date_from, $date_to])
            ->with('location', 'business', 'sales_person') // you already eager loaded sales_person
            ->get();
    
        $salespersonTotals = [];
    
        foreach ($actual_data as $transaction) {
            if ($transaction->sales_person) {
                $salesPersonName = $transaction->sales_person->first_name . ' ' . $transaction->sales_person->last_name;
                
                if (!isset($salespersonTotals[$salesPersonName])) {
                    $salespersonTotals[$salesPersonName] = 0;
                }
    
                $salespersonTotals[$salesPersonName] += $transaction->final_total;
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Data fetched successfully',
            'data' => $salespersonTotals,  // Returning the final array
        ], 200);
    }

    public function fetchLeaderboardChartData(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $date_from = $request->date_from ?? \Carbon\Carbon::now()->subDays(7)->format('Y-m-d');
        $date_to = $request->date_to ?? \Carbon\Carbon::now()->format('Y-m-d');

        $transactions = Transaction::where('business_id', $business_id)
            ->whereBetween('created_at', [$date_from, $date_to])
            ->with(['sales_person', 'contact'])
            ->get();

        $groupedData = [];

        foreach ($transactions as $transaction) {
            if ($transaction->sales_person && $transaction->contact) {
                $salesPersonName = $transaction->sales_person->first_name . ' ' . $transaction->sales_person->last_name;
                $clientName = $transaction->contact->name ?? 'Unknown Client';

                $key = $salesPersonName . '___' . $clientName;

                if (!isset($groupedData[$key])) {
                    $groupedData[$key] = [
                        'rep' => $salesPersonName,
                        'client' => $clientName,
                        'total_orders' => 0,
                        'total_amount' => 0,
                    ];
                }

                $groupedData[$key]['total_orders'] += 1;
                $groupedData[$key]['total_amount'] += $transaction->final_total;
            }
        }

        // Now split based on rep
        $finalData = [];
        foreach ($groupedData as $item) {
            $repName = $item['rep'];

            if (!isset($finalData[$repName])) {
                $finalData[$repName] = [];
            }
            $finalData[$repName][] = $item;
        }

        // Limit each rep to Top 3 clients based on total_amount
        foreach ($finalData as $rep => $clients) {
            usort($clients, function ($a, $b) {
                return $b['total_amount'] <=> $a['total_amount']; // Descending by total_amount
            });
            $finalData[$rep] = array_slice($clients, 0, 3); // Keep only Top 3
        }

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard Data fetched successfully',
            'data' => $finalData,
        ]);
    }

    public function fetchDataForHeatMap(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
    
        $date_from = $request->date_from ?? \Carbon\Carbon::now()->subDays(7)->format('Y-m-d');
        $date_from = \Carbon\Carbon::parse($date_from)->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d');

        // date_to is always 6 days after date_from to complete the week (Monday to Sunday)
        $date_to = \Carbon\Carbon::parse($date_from)->addDays(6)->format('Y-m-d');

        $user_ids = User::where('business_id', $business_id)->pluck('id');
        // Fetch all FollowUps in date range, using scheduled_at
        $followups = FollowUp::whereIn('assigned_to', $user_ids)
            ->whereDate('scheduled_at', '>=', $date_from)
            ->whereDate('scheduled_at', '<=', $date_to)
            ->with('agent')
            ->get();
        // dd($followups);
        $reps = [];
        $rep_index = [];
        $data = [];
    
        // Build rep list first
        foreach ($followups as $followup) {
            if ($followup->agent) {
                $rep_name = $followup->agent->first_name . ' ' . $followup->agent->last_name;
                if (!in_array($rep_name, $reps)) {
                    $rep_index[$rep_name] = count($reps); // mapping rep_name -> y index
                    $reps[] = $rep_name;
                }
            }
        }
    
        // Now process followups
        foreach ($followups as $followup) {
            if ($followup->agent) {
                $rep_name = $followup->agent->first_name . ' ' . $followup->agent->last_name;
                $y = $rep_index[$rep_name];
    
                $dayOfWeek = \Carbon\Carbon::parse($followup->scheduled_at)->dayOfWeekIso; // 1=Monday .. 7=Sunday
                $x = $dayOfWeek - 1; // x should be 0-6
    
                $key = $x . '-' . $y;
    
                if (!isset($data[$key])) {
                    $data[$key] = [
                        'x' => $x,
                        'y' => $y,
                        'v' => 0
                    ];
                }
    
                $data[$key]['v'] += 1;
            }
        }
    
        $matrix_data = array_values($data); // reset keys
    
        return response()->json([
            'success' => true,
            'message' => 'Heatmap data fetched successfully',
            'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'reps' => $reps,
            'matrix_data' => $matrix_data
        ]);
    }
    

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('smartcrm::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('smartcrm::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('smartcrm::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}