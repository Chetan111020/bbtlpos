<?php

namespace App\Http\Controllers\__Compute;

use App\Http\Controllers\Controller;
use App\OrderDelivery;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class OrderFlowController extends Controller{

    public function index(){
        return view('orderflow.index');
    }

    public function getOrders(){
        $orders = DB::table('transactions as t')
            ->leftJoin('contacts as c','c.id','=','t.contact_id')
            ->leftJoin('users as u','u.id','=','t.created_by')
            ->leftJoin('transaction_sell_lines as tsl','t.id','=','tsl.transaction_id')
            ->select(
                't.*',
                'c.name as customer_name',
                'c.sales_rep',
                'c.city',
                'c.state',
                'u.first_name',
                'u.last_name',
                DB::raw('COUNT(tsl.id) as total_items')
            )
            ->where('t.business_id', auth()->user()->business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where(function($query){
                $query->where('t.order_picking_status', '!=', '2')
                ->orWhere('t.order_packing_status', '!=', '2');
            })
            ->groupBy('t.id')
            ->orderBy('t.priority_order', 'desc')
            ->orderBy('t.created_at', 'asc')
            ->limit(100)
        ->get();

        $shippings = [
            'posShippingModalUpdateDelivery' => 'Delivery',
            'posShippingModalUpdatePickup' => 'Pickup',
            'posShippingModalUpdateShipping' => 'Shipping',
            'posShippingModalUpdatePallet' => 'Consignment',
            'posShippingModalUpdateSelfNonPicking' => 'Walk In (Self)',
            'posShippingModalUpdateDeliveryNonPicking' => 'Walk In (Delivery)',
            'posShippingModalUpdateShippingNonPicking' => 'Walk In (Shipping)',
        ];

        $customer_payments_status = [
            'ask_for_payment_before_ship' => 'Ask For Payment Before Shipping',
            'ok_to_ship' => 'Okay to Deliver/Ship (Payment Confirmed)'
        ];

        $total_counts = [
            'all' => 0,
            'start_picking' => 0,
            'continue_picking' => 0,
            'start_packing' => 0,
            'continue_packing' => 0,
        ];

        foreach($orders as $order){
            $order->city = ucwords(strtolower($order->city));
            $order->state = ucwords(strtolower($order->state));
            $order->delivery_method = $shippings[$order->delivery_method] ?? $order->delivery_method;
            // $order->p_status = $customer_payments_status[$order->p_status] ?? 'Ask In The Office';
            $order->final_total = number_format($order->final_total, 2);
            $order->transaction_date = date('m/d/Y H:i',strtotime($order->transaction_date));

            $order->info_loader = false;
            $order->acc_color = 'indigo';
            $order->flow_status = 'packing';

            if($order->order_picking_status == 0){
                $order->acc_color = 'pink';
                $order->flow_status = 'picking';
                $order->flow_label = 'Start Picking';
                $total_counts['start_picking']++;
            }
            else if($order->order_picking_status == 1){
                $order->acc_color = 'pink';
                $order->flow_status = 'picking';
                $order->flow_label = 'Continue Picking';
                $total_counts['continue_picking']++;
            }
            else{
                if($order->order_packing_status == 0){
                    $order->flow_label = 'Start Packing';
                    $total_counts['start_packing']++;
                }
                else if($order->order_packing_status == 1){
                    $order->flow_label = 'Continue Packing';
                    $total_counts['continue_packing']++;
                }
            }
            $total_counts['all']++;
        }

        $resp = [
            'orders' => $orders,
            'total_counts' => $total_counts
        ];

        return response($resp);
    }

    public function view($id){
        $tr = Transaction::with([
                'contact', 'sales_person','sell_lines', 'sell_lines.product', 'payment_lines'
            ])
            ->where('business_id', auth()->user()->business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->where('id', $id)
        ->first();

        $order_info = (object) [];
        if($tr) {
            $order_info->id = $tr->id;
            $order_info->invoice_no = $tr->invoice_no;
            $order_info->transaction_date = date('m/d/Y H:i', strtotime($tr->transaction_date));
            $order_info->contact = (object)[
                'name' => $tr->contact->name,
                'address' => ucwords(strtolower(implode(', ', $tr->contact->getCleanAddress()))),
                'mobile' => $tr->contact->mobile ?? '--',
                'landline' => $tr->contact->landline ?? '--',
            ];

            $order_info->added_by = $tr->sales_person->first_name;
            $order_info->delivery_method = $tr->getDeliveryMethod();

            $order_info->picking_color = 'red';
            $order_info->picking_status = 'Picking Pending';
            $order_info->picked_by = '--';
            if($tr->order_picking_status == 1){
                $order_info->picking_color = 'amber';
                $order_info->picking_status = 'Picking Started';
            }
            elseif($tr->order_picking_status == 2){
                $order_info->picking_color = 'emerald';
                $order_info->picking_status = 'Picking Completed';
                $picked_by = User::find($tr->picked_by);
                if(!empty($picked_by->first_name)){
                    $order_info->picked_by = $picked_by->first_name;
                }
            }

            $order_info->packing_color = 'red';
            $order_info->packing_status = 'Packing Pending';
            $order_info->packed_by = '--';
            if($tr->order_packing_status == 1){
                $order_info->packing_color = 'amber';
                $order_info->packing_status = 'Packing Started';
            }
            elseif($tr->order_packing_status == 2){
                $order_info->packing_color = 'emerald';
                $order_info->packing_status = 'Packing Completed';
                $packed_by = User::find($tr->packed_by);
                if(!empty($packed_by->first_name)){
                    $order_info->packed_by = $packed_by->first_name;
                }
            }

            $order_info->delivery_color = 'red';
            $order_info->delivery_status = 'Delivery';
            $order_info->delivered_by = 'Pending';
            if($order_info->delivery_method == 'Delivery'){
                $order_delivery = OrderDelivery::where('transaction_id', $tr->id)->with(['user'])->latest()->first();
                if(!empty($order_delivery)){
                    $order_info->delivery_status = ($order_delivery->status == 'delivered') ? 'Delivered' : 'Not Delivered';
                    $order_info->delivery_color = ($order_delivery->status == 'delivered') ? 'emerald' : 'red';
                    $order_info->delivered_by = 'by ' . ($order_delivery->user->first_name ?? '--');
                }
            }
            else{
                $order_info->delivery_color = 'emerald';
                $order_info->delivery_status = 'Delivery';
                $order_info->delivered_by = 'Not Applicable';
            }

            $order_info->payment_status = ucfirst($tr->payment_status);
            $order_info->accent_color = ($tr->payment_status == 'paid') ? 'emerald' : 'red';

            $sub_total = 0;
            $formatted_lines = [];
            foreach($tr->sell_lines as $line){
                $formatted_lines[] = [
                    'product' => [
                        'name' => $line->product->name,
                        'sku' => $line->product->sku,
                    ],
                    'quantity' => round($line->quantity),
                    'unit_price' => number_format($line->unit_price, 2),
                    'line_tax' => number_format($line->pos_line_tax_amount, 2),
                    'line_total' => number_format($line->quantity * $line->unit_price, 2),
                ];
                $sub_total += round($line->quantity * $line->unit_price ,2);
            }
            $order_info->sell_lines = (object) $formatted_lines;
            $order_info->subtotal = number_format($sub_total, 2);
            $order_info->total_tax = number_format($tr->sell_lines->sum('pos_line_tax_amount'), 2);
            $order_info->shipping_charges = number_format($tr->shipping_charges, 2);

            if($tr->discount_type == 'fixed' || $tr->discount_amount = 0){
                $order_info->total_discount = '$ '.number_format($tr->discount_amount, 2);
            }
            else{
                $order_info->total_discount = number_format($tr->discount_amount, 2).' %';
            }

            $order_info->final_total = number_format($tr->final_total, 2);
            $order_info->total_paid = $tr->payment_lines->sum('amount');
            $order_info->total_due = number_format($tr->final_total - $order_info->total_paid, 2);
            $order_info->total_paid = number_format($order_info->total_paid, 2);

            return response(['success' => 1, 'msg' => '', 'order_info' => $order_info]);
        } else {
            return response(['success' => 0, 'msg' => 'Record not found']);
        }
    }

    public function picking($id){
        // return view('orderflow.picking');
    }

    public function addToMylist(Request $request,$id){
        $tr = Transaction::find($id);
        if ($tr) {
            $tr->queue_user_id = ($request->input('op') == 'remove') ? null : $request->input('queueval');
            $tr->save();

            return response(['success' => 1, 'msg' => 'Queue updated successfully']);
        } else {
            return response(['success' => 0, 'msg' => 'Record not found']);
        }
    }
}