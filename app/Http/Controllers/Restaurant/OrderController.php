<?php

namespace App\Http\Controllers\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use App\Transaction;
use App\User;
use App\TransactionSellLine;

use App\Utils\Util;
use App\Utils\RestaurantUtil;

class OrderController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;
    protected $restUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @param RestaurantUtil $restUtil
     * @return void
     */
    public function __construct(Util $commonUtil, RestaurantUtil $restUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return redirect()->to('/order-flow');
        //
        // if (!auth()->user()->can('sell.view')) {
        //     abort(403, 'Unauthorized action.');
        // }
         try {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $order_status_list = [
            'received'=>'Received',
            'picking_started'=>'Picking Started',
            'picking_completed'=>'Picking Completed',
            'packing_started'=>'Packing Started',
            'packing_completed'=>'Packing Completed'
       ];

//        $is_service_staff = false;
        $orders = [];
//        $service_staff = [];
        $line_orders = [];
//        if ($this->restUtil->is_service_staff($user_id)) {
//            $is_service_staff = true;
        $order_status = isset(request()->order_status) ? request()->order_status : "";
        $order_no = isset(request()->order_no) ? request()->order_no : "";
        if (empty(request()->order_no) && empty(request()->order_status)) {
            $orders = $this->restUtil->getAllOrders($business_id, ['waiter_id' => $user_id]);
        } elseif (!empty(request()->order_no)) {
            $orders = $this->restUtil->getAllOrders($business_id, ['search' => request()->order_no, 'order_status' => $order_status]);
        } elseif (!empty(request()->order_status)) {
            $orders = $this->restUtil->getAllOrders($business_id, ['order_status' => $order_status]);
        }

        $order_status_list_check = [
            'received'=>'received',
            'picking_started'=>'picking_started',
            'picking_completed'=>'picking_completed',
            'packing_started'=>'packing_started',
            'packing_completed'=>'packing_completed'
       ];

        // $ord_list = 0;
        // $ord_list1 = 0;
        // $ord_list2 = 0;
        // $ord_list3 = 0;
        // $ord_list4 = 0;
        // $ord_list5 = 0;


        $ord_list = 0;
        $ord_list1 = [
            'name' => 'received',
            'count' => 0,
        ];
        $ord_list2 = [
            'name' => 'picking_completed',
            'count' => 0,
        ];
        $ord_list3 = [
            'name' => 'picking_started',
            'count' => 0,
        ];
        $ord_list4 = [
            'name' => 'packing_completed',
            'count' => 0,
        ];
        $ord_list5 = [
            'name' => 'packing_started',
            'count' => 0,
        ];

        foreach ($order_status_list_check as $key => $ord_sta) {
            if($ord_sta === 'received')
            {
                $ord_list = $this->restUtil->getAllOrders($business_id, ['order_status' => $ord_sta]);
                if(count($ord_list)>0){
                    $ord_list1  = [
                        'name' => 'received',
                        'count' => count($ord_list),
                    ];
                }
            }
            if($ord_sta === 'picking_completed')
            {
                 $ord_list = $this->restUtil->getAllOrders($business_id, ['order_status' => $ord_sta]);
                if(count($ord_list)>0){
                $ord_list2  = [
                    'name' => 'picking_completed',
                    'count' => count($ord_list),
                ];
                }
            }
            if($ord_sta === 'picking_started')
            {
                $ord_list = $this->restUtil->getAllOrders($business_id, ['order_status' => $ord_sta]);
                if(count($ord_list)>0){
                $ord_list3  = [
                    'name' => 'picking_started',
                    'count' => count($ord_list),
                ];
                }
            }
            if($ord_sta === 'packing_completed')
            {
                $ord_list = $this->restUtil->getAllOrders($business_id, ['order_status' => $ord_sta]);
                if(count($ord_list)>0){
                $ord_list4  = [
                    'name' => 'packing_completed',
                    'count' => count($ord_list),
                ];
                }
            }
            if($ord_sta === 'packing_started')
            {
                $ord_list = $this->restUtil->getAllOrders($business_id, ['order_status' => $ord_sta]);
                if(count($ord_list)>0){
                $ord_list5  = [
                    'name' => 'packing_started',
                    'count' => count($ord_list),
                ];
                }
            }

        }

//            dd($orders);

//        } elseif (!empty(request()->service_staff)) {
//            $orders = $this->restUtil->getAllOrders($business_id, ['waiter_id' => request()->service_staff]);
//
//            $line_orders = $this->restUtil->getLineOrders($business_id, ['waiter_id' => request()->service_staff]);
//        }
//
//        if (!$is_service_staff) {
//            $service_staff = $this->restUtil->service_staff_dropdown($business_id);
//        }

//        return view('restaurant.orders.index', compact('orders', 'is_service_staff', 'service_staff', 'line_orders'));

        return view('restaurant.orders.index', compact('orders', 'line_orders', 'order_status_list', 'order_status', 'order_no', 'ord_list', 'ord_list1', 'ord_list2', 'ord_list3', 'ord_list4', 'ord_list5'));
         } catch (\Exception $e) {
             return $e;
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = ['success' => 0,
                'msg' => trans("messages.something_went_wrong")
            ];
        }

    }

    /**
     * Marks an order as served
     * @return json $output
     */
    public function markAsServed($id)
    {
        // if (!auth()->user()->can('sell.update')) {
        //     abort(403, 'Unauthorized action.');
        // }
        try {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                ->where('t.business_id', $business_id)
                ->where('transaction_id', $id);

            if ($this->restUtil->is_service_staff($user_id)) {
                $query->where('res_waiter_id', $user_id);
            }

            $query->update(['res_line_order_status' => 'served']);

            $output = ['success' => 1,
                'msg' => trans("restaurant.order_successfully_marked_served")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = ['success' => 0,
                'msg' => trans("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Marks an line order as served
     * @return json $output
     */
    public function markLineOrderAsServed($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = TransactionSellLine::where('id', $id);

            if ($this->restUtil->is_service_staff($user_id)) {
                $query->where('res_service_staff_id', $user_id);
            }
            $sell_line = $query->first();

            if (!empty($sell_line)) {
                $sell_line->res_line_order_status = 'served';
                $sell_line->save();
                $output = ['success' => 1,
                    'msg' => trans("restaurant.order_successfully_marked_served")
                ];
            } else {
                $output = ['success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = ['success' => 0,
                'msg' => trans("messages.something_went_wrong")
            ];
        }

        return $output;
    }
}
