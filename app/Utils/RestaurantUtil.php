<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

use Spatie\Permission\Models\Role;

use App\Transaction;
use App\BusinessLocation;
use App\User;
use App\TransactionSellLine;
use App\Restaurant\Booking;

use App\VariationLocationDetails;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Orderpackinglog;

class RestaurantUtil extends Util
{
    protected $productUtil;
    protected $transactionUtil;

    public function __construct(ProductUtil $productUtil,TransactionUtil $transactionUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Retrieves all orders/sales
     *
     * @param int $business_id
     * @param array $filter
     * *For new orders order_status is 'received'
     *
     * @return obj $orders
     */
    public function getAllOrders($business_id, $filter = [])
    {
        $query = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftjoin(
                'business_locations AS bl',
                'transactions.location_id',
                '=',
                'bl.id'
            )
            ->leftJoin('users as ur', 'transactions.created_by', '=', 'ur.id')
            ->leftjoin(
                'res_tables AS rt',
                'transactions.res_table_id',
                '=',
                'rt.id'
            )
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final');

            if(!empty($filter['order_status']) && $filter['order_status'] == 'packing_completed'){
                $query->where('order_picking_status', '=', '2')->where('order_packing_status', '=', '2')->orderBy('created_at', 'desc');
            }else{
                if (!empty($filter['order_status']) && $filter['order_status'] == 'received')
                {
                    $query->where('transactions.order_picking_status', '!=' , '2');
                    $query->where('transactions.order_packing_status', '!=' , '1');
                }else{
                    $query->where('transactions.order_packing_status', '!=' , '2');
                }

            }

            if(!empty($filter['order_status']) && $filter['order_status'] == 'picking_started'){
                $query->where('order_picking_status', '=', '1')->where('order_packing_status', '=', '0');
            }else{
                $query->where('transactions.order_picking_status', '!=' , '1');
            }

        // ->where('transactions.res_order_status', '!=' ,'served');

        if (empty($filter['order_status'])) {
            $query->where(function ($q) {
                $q->where('res_order_status', '!=', 'served')
                    ->orWhereNull('res_order_status');
            });
        }

        //For new orders order_status is 'received'
        if (!empty($filter['order_status']) && $filter['order_status'] == 'received') {
            $query->whereNull('res_order_status');
        }elseif(!empty($filter['order_status']) && $filter['order_status'] == 'picking_started'){
            $query->where('order_picking_status', '=', '1');
        }elseif(!empty($filter['order_status']) && $filter['order_status'] == 'packing_started'){
            $query->where('order_picking_status', '=', '2')->where('order_packing_status', '=', '1');
        }elseif(!empty($filter['order_status']) && $filter['order_status'] == 'picking_completed'){
            $query->where('order_picking_status', '=', '2')->where('order_packing_status', '=', '0');
        }

        if (!empty($filter['line_order_status'])) {
            if ($filter['line_order_status'] == 'received') {
                $query->whereHas('sell_lines', function ($q) {
                    $q->whereNull('res_line_order_status')
                        ->orWhere('res_line_order_status', 'received');
                }, '>=', 1);
            }

            if ($filter['line_order_status'] == 'cooked') {
                $query->whereHas('sell_lines', function ($q) {
                    $q->where('res_line_order_status', '!=', 'cooked');
                }, '=', 0);
            }

            if ($filter['line_order_status'] == 'served') {
                $query->whereHas('sell_lines', function ($q) {
                    $q->where('res_line_order_status', '!=', 'served');
                }, '=', 0);
            }
        }

        if (!empty($filter['search'])) {
            $query->where('transactions.invoice_no', 'like', '%' . $filter['search'] . '%')
                ->orWhere('contacts.first_name', 'like', '%' . $filter['search'] . '%')
                ->orWhere('contacts.middle_name', 'like', '%' . $filter['search'] . '%')
                ->orWhere('contacts.last_name', 'like', '%' . $filter['search'] . '%');
        }
//        $query->whereHas('sell_lines', function($q) {
//            $q->count();
//        });

        $orders = $query->select(
            'transactions.*',
            'contacts.name as customer_name',
            'contacts.sales_rep',
            'ur.first_name',
            'ur.last_name',
            'contacts.city',
            'contacts.state',
            'contacts.payment_status as p_status',
            'bl.name as business_location',
            'rt.name as table_name'
        )->with(['sell_lines'])
            ->orderBy('priority_order', 'desc')
            ->orderBy('created_at', 'asc')->limit(100,0)
            //->orderBy('created_at', 'desc')
            ->get();

        //echo "<pre>"; print_r($orders);exit;

        return $orders;
    }

    public function service_staff_dropdown($business_id)
    {
        //Get all service staff roles
        $service_staff_roles = Role::where('business_id', $business_id)
            ->where('is_service_staff', 1)
            ->get()
            ->pluck('name')
            ->toArray();

        $service_staff = [];

        //Get all users of service staff roles
        if (!empty($service_staff_roles)) {
            $service_staff = User::where('business_id', $business_id)->role($service_staff_roles)->get()->pluck('first_name', 'id');
        }

        return $service_staff;
    }

    public function is_service_staff($user_id)
    {
        $is_service_staff = false;
        $user = User::find($user_id);
        if ($user->roles->first()->is_service_staff == 1) {
            $is_service_staff = true;
        }

        return $is_service_staff;
    }

    /**
     * Retrieves line orders/sales
     *
     * @param int $business_id
     * @param array $filter
     * *For new orders order_status is 'received'
     *
     * @return obj $orders
     */
    public function getLineOrders($business_id, $filter = [])
    {
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->leftjoin(
                'res_tables AS rt',
                't.res_table_id',
                '=',
                'rt.id'
            )
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final');

        if (empty($filter['order_status'])) {
            $query->where(function ($q) {
                $q->where('res_line_order_status', '!=', 'served')
                    ->orWhereNull('res_line_order_status');
            });
        }

//        if (!empty($filter['waiter_id'])) {
//            $query->where('transaction_sell_lines.res_service_staff_id', $filter['waiter_id']);
//        }
//
        $orders = $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            't.id as transaction_id',
            'c.name as customer_name',
            'bl.name as business_location',
            'rt.name as table_name',
            't.created_at',
            't.invoice_no',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.res_line_order_status',
            'u.short_name as unit',
            'transaction_sell_lines.id'
        )
            ->orderBy('created_at', 'desc')
            ->get();

        return $orders;
    }

    /**
     * Function to show booking events on a calendar
     *
     * @param array $filters
     *
     * @return array
     */
    public function getBookingsForCalendar($filters)
    {
        $start_date = request()->start;
        $end_date = request()->end;
        $query = Booking::where('business_id', $filters['business_id'])
            ->whereBetween(DB::raw('date(booking_start)'), [$filters['start_date'], $filters['end_date']])
            ->with(['customer', 'table']);

        if (!empty($filters['user_id'])) {
            $query->where('created_by', $filters['user_id']);

            $query->where(function ($q) use ($filters) {
                $q->where('created_by', $filters['user_id'])
                    ->orWhere('correspondent_id', $filters['user_id'])
                    ->orWhere('waiter_id', $filters['user_id']);
            });
        }

        if (!empty($filters['location_id'])) {
            $query->where('bookings.location_id', $filters['location_id']);
        }
        $bookings = $query->get();

        $events = [];

        foreach ($bookings as $booking) {

            //Skip event if customer not found
            if (empty($booking->customer)) {
                continue;
            }

            $customer_name = $booking->customer->name;
            $table_name = optional($booking->table)->name;

            $backgroundColor = '#3c8dbc';
            $borderColor = '#3c8dbc';
            if ($booking->booking_status == 'completed') {
                $backgroundColor = '#00a65a';
                $borderColor = '#00a65a';
            } elseif ($booking->booking_status == 'cancelled') {
                $backgroundColor = '#f56954';
                $borderColor = '#f56954';
            } elseif ($booking->booking_status == 'waiting') {
                $backgroundColor = '#FFAD46';
                $borderColor = '#FFAD46';
            }
            if (!empty($filters['color'])) {
                $backgroundColor = $filters['color'];
                $borderColor = $filters['color'];
            }
            $title = $customer_name;
            if (!empty($table_name)) {
                $title .= ' - ' . $table_name;
            }
            $events[] = [
                'title' => $title,
                'title_html' => $customer_name . '<br>' . $table_name,
                'start' => $booking->booking_start,
                'end' => $booking->booking_end,
                'customer_name' => $customer_name,
                'table' => $table_name,
                'url' => action('Restaurant\BookingController@show', [$booking->id]),
                'event_url' => action('Restaurant\BookingController@index'),
                // 'start_time' => $start_time,
                // 'end_time' =>  $end_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'allDay' => false,
                'event_type' => 'bookings'
            ];
        }

        return $events;
    }

    public function getOrderProducts($id, $business_id)
    {
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')
            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->leftjoin(
                'res_tables AS rt',
                't.res_table_id',
                '=',
                'rt.id'
            )
            ->where('transaction_sell_lines.is_picked', 0)
            ->where('transaction_sell_lines.is_packed', 0)
            ->where('transaction_sell_lines.out_of_stock', 0)
            ->where('t.id', $id)
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final');

        $products = $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'p.sku as sku',
            'p.aisle as aisle',
            'p.rack as rack',
            'p.shelf as shelf',
            'p.bin as bin',
            'p.main_image as image',
            'p.item_code as item_code',
            //'pl.quantity as stock',
            'vld.qty_available as stock',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            't.id as transaction_id',
            'c.name as customer_name',
            'cs.name as sales_rep',
            'bl.name as business_location',
            'rt.name as table_name',
            't.created_at',
            't.invoice_no',
            'transaction_sell_lines.sell_line_note',
            'transaction_sell_lines.edit_quantity',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.unit_price',
            'transaction_sell_lines.res_line_order_status',
            'u.short_name as unit',
            'transaction_sell_lines.id'
        )
            ->orderBy('p.aisle', 'ASC')
            ->orderBy('p.rack', 'ASC')
            ->orderBy('p.shelf', 'ASC')
            ->orderBy('p.bin', 'ASC')
            ->groupBy('transaction_sell_lines.id')
            ->get();

        return $products;
    }

    public function getOrderProductForPacking($id, $business_id)
    {
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
              ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')

            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->leftjoin(
                'res_tables AS rt',
                't.res_table_id',
                '=',
                'rt.id'
            )
            ->where('transaction_sell_lines.is_packed', 0)
            ->where('transaction_sell_lines.out_of_stock', 0)
            ->where('t.id', $id)
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->whereRaw('transaction_sell_lines.quantity != transaction_sell_lines.packing_qty')
            ->where('t.status', 'final');

        $products = $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'p.sku as sku',
            'p.aisle as aisle',
            'p.rack as rack',
            'p.shelf as shelf',
            'p.bin as bin',
           // 'p.name as image',
            'p.main_image as image',
            'p.item_code as item_code',
          //  'pl.quantity as stock',
             'vld.qty_available as stock',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            't.id as transaction_id',
            'c.name as customer_name',
            'cs.name as sales_rep',
            'bl.name as business_location',
            'rt.name as table_name',
            't.created_at',
            't.invoice_no',
            'transaction_sell_lines.sell_line_note',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.edit_quantity',
            'transaction_sell_lines.packing_qty',
            'transaction_sell_lines.res_line_order_status',
            'u.short_name as unit',
            'transaction_sell_lines.id',
            'transaction_sell_lines.box_no'
        )
            ->orderBy('p.aisle', 'ASC')
            ->orderBy('p.rack', 'ASC')
            ->orderBy('p.shelf', 'ASC')
            ->orderBy('p.bin', 'ASC')
            // ->groupBy('p.sku')
            ->groupBy('transaction_sell_lines.id')
        ->get();

        return $products;
    }

    public function getInvoiceProducts($id, $business_id){
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
              ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')

            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->leftjoin(
                'res_tables AS rt',
                't.res_table_id',
                '=',
                'rt.id'
            )

            ->where('t.id', $id)
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final');

        $products = $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'p.sku as sku',
            'p.aisle as aisle',
            'p.rack as rack',
            'p.shelf as shelf',
            'p.bin as bin',
            'p.main_image as image',
            'p.item_code as item_code',
           // 'pl.quantity as stock',
             'vld.qty_available as stock',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            't.id as transaction_id',
            'c.name as customer_name',
            'cs.name as sales_rep',
            'bl.name as business_location',
            'rt.name as table_name',
            't.created_at',
            't.invoice_no',
            'transaction_sell_lines.sell_line_note',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.res_line_order_status',
            'u.short_name as unit',
            'transaction_sell_lines.id',
            'transaction_sell_lines.is_packed',
            'transaction_sell_lines.out_of_stock',
            'transaction_sell_lines.box_no'
        )
            ->orderBy('created_at', 'desc')
            // ->groupBy('p.sku')
            ->groupBy('transaction_sell_lines.id')
            ->get();

        return $products;
    }

    public function getOrderProduct($id, $business_id, $is_packing = null)
    {
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('users as ur', 't.created_by', '=', 'ur.id')
            ->leftJoin('users as pb', 't.picked_by', '=', 'pb.id')
            ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')
            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->leftjoin(
                'res_tables AS rt',
                't.res_table_id',
                '=',
                'rt.id'
            );
            if($is_packing == null){
                $query->where('transaction_sell_lines.is_picked', 0);
            }

            $query->where('transaction_sell_lines.is_packed', 0)
            ->where('transaction_sell_lines.out_of_stock', 0)
            ->where('t.id', $id)
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final');

        $product = $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'p.sku as sku',
            'p.aisle as aisle',
            'p.rack as rack',
            'p.shelf as shelf',
            'p.bin as bin',
            'p.main_image as image',
            'p.item_code as item_code',
            //'pl.quantity as stock',
            'vld.qty_available as stock',
            't.shipping_status',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            't.id as transaction_id',
            'c.name as customer_name',
            'cs.name as sales_rep',
            'bl.name as business_location',
            'rt.name as table_name',
            't.additional_notes',
            'ur.first_name',
            'ur.last_name',
            'pb.first_name as pb_first_name',
            'pb.last_name  as pb_last_name',
            't.created_at',
            't.invoice_no',
            'transaction_sell_lines.unit_price',
            'transaction_sell_lines.sell_line_note',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.edit_quantity',
            'transaction_sell_lines.res_line_order_status',
            'u.short_name as unit',
            'transaction_sell_lines.id'
        )
            ->orderBy('p.aisle', 'ASC')
            ->orderBy('p.rack', 'ASC')
            ->orderBy('p.shelf', 'ASC')
            ->orderBy('p.bin', 'ASC')
            ->groupBy('p.sku')
            ->first();

        return $product;
    }

    public function getPickedProducts($id, $business_id, $key)
    {
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
              ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')

            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->leftjoin(
                'res_tables AS rt',
                't.res_table_id',
                '=',
                'rt.id'
            );

        if ($key == "out_of_stock") {
            $query->where('transaction_sell_lines.is_picked', 0)
                ->where('transaction_sell_lines.out_of_stock', 1)
                ->where('transaction_sell_lines.edited', 0)
                ->where('transaction_sell_lines.incorrect_location', 0);
        }

        if ($key == "edited") {

            $query->where('transaction_sell_lines.out_of_stock', 0)
                //->where('transaction_sell_lines.is_picked', 1)
                ->where('transaction_sell_lines.edited', 1);
                //->where('transaction_sell_lines.incorrect_location', 0);
        }

        if ($key == "incorrect_location") {
            $query->where('transaction_sell_lines.out_of_stock', 0)
                ->where('transaction_sell_lines.incorrect_location', 1);
        }

        $query->where('t.id', $id)
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final');

        if ($key == "picked_product") {
            $query->where('transaction_sell_lines.is_picked', 1)
                ->where('transaction_sell_lines.out_of_stock', 0);
               // ->orWhere('transaction_sell_lines.edited', 1);
        }

        $product = $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'p.sku as sku',
            'p.aisle as aisle',
            'p.rack as rack',
            'p.shelf as shelf',
            'p.bin as bin',
            'p.main_image as image',
            'p.item_code as item_code',
           // 'pl.quantity as stock',
             'vld.qty_available as stock',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            't.id as transaction_id',
            'c.name as customer_name',
            'cs.name as sales_rep',
            'bl.name as business_location',
            'rt.name as table_name',
            't.created_at',
            't.invoice_no',
            'transaction_sell_lines.sell_line_note',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.res_line_order_status',
            'u.short_name as unit',
            'transaction_sell_lines.id',
            'transaction_sell_lines.picking_started_time',
            'transaction_sell_lines.picking_completed_time',
            'transaction_sell_lines.box_no'
        )
            ->orderBy('created_at', 'desc')
            // ->groupBy('p.sku')
            ->groupBy('transaction_sell_lines.id')
            ->get();

        return $product;
    }

    public function pickAProduct($request, $id, $business_id, $picked_by)
    {
        $query = DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $id)
            ->where('t.business_id', $business_id);

        if ($request->updatedPickedQty != "" || $request->updatedPickedQty != null) {
            $query->update([
                'is_picked' => 1,
                'edited' => 1,
                't.order_picking_status' => 1,
                'quantity' => $request->updatedPickedQty,
                'updated_piking_qty' => $request->updatedPickedQty,
                't.picked_by' => $picked_by
            ]);
        } else {
            $query->update([
                'is_picked' => 1,
                'picking_started_time' => \Carbon::now(),
                't.order_picking_status' => 1,
                't.picked_by' => $picked_by
            ]);
            $this->pickAEditProduct($request, $id, $business_id, $picked_by);
        }
    }

    public function startPicking($id, $business_id)
    {
        $query = DB::table('transactions')
            ->where('transactions.id', $id)
            ->where('transactions.business_id', $business_id);
        $query->update([
                'transactions.order_picking_status' => 1
            ]);
    }

    public function startPacking($id, $business_id, $staff_id)
    {
        $query = DB::table('transactions')
            ->where('transactions.id', $id)
            ->where('transactions.business_id', $business_id);
        $query->update([
                'transactions.packed_by' => $staff_id
            ]);
    }

    public function outOfStockProduct($id, $business_id)
    {
        $result = DB::table('transaction_sell_lines')
                ->where('id', $id)
                ->first();
        $sell_line_Before = TransactionSellLine::find($id);
        $transaction_before = Transaction::where('id', $result->transaction_id)
            ->where('business_id', $business_id)
            ->firstOrFail();
        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'out_of_stock' => 1,
                'out_of_stock_qty' => $result->quantity,
                'quantity' => 0,
                'is_picked' => 0
            ]);

        // $transaction = Transaction::where('id', $result->transaction_id)
        //     ->where('business_id', $business_id)
        //     ->firstOrFail();

        // $before_line_price = $result->quantity * $result->unit_price ;

        // $total = $transaction->final_total - $before_line_price;
        // $total_before_tax = $transaction->total_before_tax - $before_line_price;
        // $updated_before_tax_total = $total_before_tax;
        // $transaction->bf_final_total = $transaction->final_total;
        // $transaction->final_total = $updated_before_tax_total;
        // $transaction->total_before_tax = $updated_before_tax_total;
        // $transaction->save();

        $transaction = Transaction::findOrFail($result->transaction_id);
        $transaction->recalculate();

        $this->editQtylogforInvoice($id,$sell_line_Before,$transaction_before->final_total," (out of stock) ");
        $variation_detail = VariationLocationDetails::where('product_id',$result->product_id)->where('variation_id', $result->variation_id)->firstOrFail();
        $variation_detail->qty_available = $variation_detail->qty_available + $result->quantity;
        $variation_detail->save();
    }

    public function incorrectLocation($id, $business_id)
    {
        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'incorrect_location' => 1
            ]);
    }

    public function saveAndHoldOrder($id, $business_id)
    {
        DB::table('transactions')
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->update([
                'order_picking_status' => 1
            ]);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'picking_status' => 1
            ]);
    }

    public function saveAndHoldOrderPacking($id, $business_id)
    {
        DB::table('transactions')
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->update([
                'order_packing_status' => 1
            ]);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'packing_status' => 1
            ]);
    }

    public function finalizePickingOrder($request, $id, $business_id)
    {
        DB::table('transactions')
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->update([
                'order_picking_status' => 2,
                'order_picking_time' => $request->timingCount
            ]);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'picking_completed_time' => \Carbon::now(),
                'picking_status' => 2
            ]);
    }

    public function finalizePackingOrder($request, $id, $business_id)
    {
        $box_qty = $request->box_qty ?? 0;
        //$box_no = TransactionSellLine::where('transaction_id',$id)->max('box_no');

        $box_nos =  TransactionSellLine::where('transaction_id', $id)->select('box_no')->get()->toArray();
        if(!empty($box_nos))
        {
            $box_no =  $this->getBoxQty($box_nos);
        }

        if(!empty($box_no)){
            $box_qty = $box_no;
        }

        DB::table('transactions')
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->update([
                'box_qty' => $box_qty,
                'order_packing_status' => 2,
            ]);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'packing_completed_time' => \Carbon::now(),
                'packing_status' => 2
            ]);
    }

    public function cancelPikingOrder($id, $business_id)
    {
        DB::table('transactions')
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->update([
                'order_picking_status' => 0
            ]);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'is_picked' => 0,
                'out_of_stock' => 0,
                'incorrect_location' => 0,
                'edited' => 0,
                'updated_piking_qty' => null,
                'picking_status' => 0
            ]);
    }

    public function undoPicking($id, $business_id)
    {
        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'is_picked' => 0,
                'out_of_stock' => 0,
                'incorrect_location' => 0,
                'edited' => 0,
                'updated_piking_qty' => null,
                'picking_status' => 1,
                'picking_started_time' => null,
                'picking_completed_time' => null
            ]);
    }

    public function undoOutofStock($id, $business_id)
    {
        $result = DB::table('transaction_sell_lines')
                ->where('id', $id)
                ->first();
        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                //'is_picked' => 1,
                'out_of_stock' => 0,
                'incorrect_location' => 0,
                'out_of_stock_qty' => 0,
                'quantity' => $result->out_of_stock_qty,
                'edited' => 0,
                'updated_piking_qty' => null,
                'picking_status' => 0
            ]);

            // $tsl = TransactionSellLine::findOrFail($id);

            // $transaction = Transaction::where('id', $result->transaction_id)
            // ->where('business_id', $business_id)
            // ->firstOrFail();
            // $transaction->final_total = $transaction->bf_final_total;
            // $transaction->total_before_tax += round($tsl->quantity * $tsl->unit_price_inc_tax, 2);
            // $transaction->bf_final_total = 0;
            // $transaction->save();

            $transaction = Transaction::findOrFail($result->transaction_id);
            $transaction->recalculate();

        $variation_detail = VariationLocationDetails::where('product_id',$result->product_id)->where('variation_id', $result->variation_id)->firstOrFail();
        $variation_detail->qty_available = $variation_detail->qty_available - $result->out_of_stock_qty;
        $variation_detail->save();
    }

    public function cancelPakingOrder($id, $business_id)
    {
        DB::table('transactions')
            ->where('id', $id)
            ->where('business_id', $business_id)
            ->update([
                'order_packing_status' => 0,
                'box_qty' => 0,
            ]);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'is_packed' => 0,
                'box_no' => 0,
                'packing_status' => 0
            ]);
    }

    public function undoPacking($id, $business_id)
    {
        $scanned_by_id = request()->session()->get('user.id');

        $result = DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->select("transaction_sell_lines.id", "transaction_sell_lines.packing_qty", "transaction_sell_lines.quantity", "transaction_sell_lines.updated_piking_qty","transaction_sell_lines.box_no","transaction_sell_lines.transaction_id","transaction_sell_lines.product_id","t.invoice_no","t.contact_id")
            ->where('transaction_sell_lines.id', $id)
            ->where('t.business_id', $business_id)
            ->first();

        $packing_logs = array('transaction_sell_line_id'=>$result->id,'invoice_no' => $result->invoice_no,'packing_qty' => $result->packing_qty,'transaction_id' => $result->transaction_id , 'product_id' => $result->product_id , 'contact_id' => $result->contact_id , 'scan_status'=>'undo' , 'scanned_by' => $scanned_by_id);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $id)
            ->where('t.business_id', $business_id)
            ->update([
                'is_packed' => 0,
                'box_no' => 0,
                't.box_qty' => 0,
                'packing_started_time' => Null,
                'packing_completed_time' => Null,
                'packing_status' => 0,
                'packing_qty' => 0
            ]);
        Orderpackinglog::create($packing_logs);
    }

    public function updateProductQty($request, $business_id)
    {
        $sell_line = TransactionSellLine::with(['product', 'warranties'])
            ->find($request->p_id);

        DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $request->p_id)
            ->where('t.business_id', $business_id)
            ->update([
                'quantity' => $request->p_qty,
                'edited' => 1
            ]);

        DB::table('transaction_sell_lines')
        ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
        ->where('transaction_sell_lines.id', $request->p_id)
        ->where('t.business_id', $business_id)
        ->where('transaction_sell_lines.quantity', $sell_line->packing_qty)
        ->update([
            'quantity' => $request->p_qty,
            'edited' => 1,
            'is_packed' => 1,
        ]);

        $this->packing_started_time($request->p_id);

        $transaction = Transaction::where('id', $sell_line->transaction_id)
            ->where('business_id', $business_id)
            ->firstOrFail();

        $before_line_price = $sell_line->quantity * $sell_line->unit_price ;

        $after_line_price = $request->p_qty * $sell_line->unit_price ;

        $total = $transaction->final_total - $before_line_price;
        $total_before_tax = $transaction->total_before_tax - $before_line_price;
        $updatedtotal = $total + $after_line_price;
        $updated_before_tax_total = $total_before_tax + $after_line_price;

        $transaction->final_total = $updatedtotal;
        $transaction->total_before_tax = $updated_before_tax_total;
        $transaction->save();

    }

    public function packProduct($request){
        $order_id = str_replace("#","",$request->order_id);
        $result = DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->select("transaction_sell_lines.id", "transaction_sell_lines.packing_qty", "transaction_sell_lines.quantity", "transaction_sell_lines.updated_piking_qty","transaction_sell_lines.box_no","transaction_sell_lines.transaction_id","transaction_sell_lines.product_id","t.contact_id")
            ->where('t.invoice_no', $order_id)
            ->where(function ($update) use ($request) {
                if (isset($request->packed_by_id) && $request->packed_by_id) {
                    $update->where('transaction_sell_lines.id', $request->searchKey);
                }else{
                    $update->where('p.item_code', $request->searchKey)
                    ->orWhere('p.sku', $request->searchKey)
                    ->orWhere('p.sku2', $request->searchKey)
                    ->orWhere('p.sku3', $request->searchKey);
                }
            })->first();
        if($result){
            $quantity = (int)$result->updated_piking_qty  ?? (int)$result->quantity;
            if((int)$result->packing_qty >= (int)$result->quantity)
            {
                return $response = ['status' => false, 'msg' => 'Item Has Been Already Packed..!'];
            }
            $query = DB::table('transaction_sell_lines')
                ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
                ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
                ->where('t.invoice_no', $order_id)
                ->where(function ($update) use ($request) {
                    if (isset($request->packed_by_id) && $request->packed_by_id) {
                        $update->where('transaction_sell_lines.id', $request->searchKey);
                    }else{
                        $update->where('p.item_code', $request->searchKey)
                        ->orWhere('p.sku', $request->searchKey)
                        ->orWhere('p.sku2', $request->searchKey)
                        ->orWhere('p.sku3', $request->searchKey);
                    }
                });

            //Edited by developer1
            if(isset($result->box_no) && !empty($result->box_no) && $result->box_no != '0' && $result->box_no != '')
            {
                if(strpos($request->box_no,'BOX')!== false && !strpos($request->box_no,'undefined')!== false)
                {
                    $final_box_no = $this->mergeBoxQty($request->box_no,$result->box_no);
                }
                elseif(!strpos($request->box_no,'undefined')!== false)
                {
                    $final_box_no = '';
                }
                else
                {
                    $final_box_no = $request->box_no;
                }
            }
            else
            {
                $final_box_no = $request->box_no;
            }

            $query->update(['box_no' => $final_box_no]);

            //Edited by developer1
                $packed_log_qty = 1;
                $scan_status = 'scan';
                if(isset($request->all_pack) && $request->all_pack){
                    $packed_log_qty  = (int)$result->quantity - (int)$result->packing_qty;
                    $scan_status = 'packed';
                    $query->update([
                        'is_packed' => 1,
                        't.order_packing_status' => 1,
                        'transaction_sell_lines.updated_at' => \Carbon::now(),
                        'packing_qty' => (int)$result->quantity
                    ]);
                }else{

                    if((int)$result->quantity - (int)$result->packing_qty == 1){
                        $query->update([
                            'is_packed' => 1,
                            't.order_packing_status' => 1,
                            'transaction_sell_lines.updated_at' => \Carbon::now(),
                            'packing_qty' => (int)$result->packing_qty + 1
                        ]);
                    }else{
                        $query->update([
                            'transaction_sell_lines.updated_at' => \Carbon::now(),
                            'packing_qty' => (int)$result->packing_qty + 1
                        ]);
                    }
                }
                $this->packing_started_time($result->id);

                $scanned_by_id = request()->session()->get('user.id');
                $packing_logs = array('transaction_sell_line_id'=>$result->id,'invoice_no' => $order_id,'packing_qty' => $packed_log_qty,'transaction_id' => $result->transaction_id , 'product_id' => $result->product_id , 'contact_id' => $result->contact_id , 'scan_status'=>$scan_status , 'scanned_by' => $scanned_by_id);
                Orderpackinglog::create($packing_logs);

            $response = ['status' => true];
        }else{
            $response = ['status' => false];
        }
        return  $response;
    }

    public function getPackedProducts($id, $business_id, $key){
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
              ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')

            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->leftjoin(
                'res_tables AS rt',
                't.res_table_id',
                '=',
                'rt.id'
            );

        if ($key == "packed_product") {
            $query->where('transaction_sell_lines.is_packed', 1)
                ->where('transaction_sell_lines.out_of_stock', 0)
                ->whereIn('transaction_sell_lines.edited', [1,0]);
        }


        if ($key == "edited") {
            $query->where('transaction_sell_lines.is_packed', 1)
                ->where('transaction_sell_lines.out_of_stock', 0)
                ->where('transaction_sell_lines.edited', 1)
                ->where('transaction_sell_lines.incorrect_location', 0);
        }

        if ($key == "incorrect_location") {
            $query->where('transaction_sell_lines.is_packed', 1)
                ->where('transaction_sell_lines.out_of_stock', 0)
                ->where('transaction_sell_lines.edited', 0)
                ->where('transaction_sell_lines.incorrect_location', 1);
        }

        $query->where('t.id', $id)
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final');

        $product = $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'p.sku as sku',
            'p.aisle as aisle',
            'p.rack as rack',
            'p.shelf as shelf',
            'p.bin as bin',
            'p.main_image as image',
            'p.item_code as item_code',
           // 'pl.quantity as stock',
             'vld.qty_available as stock',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            't.id as transaction_id',
            'c.name as customer_name',
            'cs.name as sales_rep',
            'bl.name as business_location',
            'rt.name as table_name',
            't.created_at',
            't.invoice_no',
            'transaction_sell_lines.sell_line_note',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.edit_quantity',
            'transaction_sell_lines.res_line_order_status',
            'u.short_name as unit',
            'transaction_sell_lines.id',
            'transaction_sell_lines.packing_started_time',
            'transaction_sell_lines.packing_completed_time',
            'transaction_sell_lines.box_no'
        )
            ->orderBy('created_at', 'desc')
            // ->groupBy('p.sku')
            ->groupBy('transaction_sell_lines.id')
            ->get();

        return $product;
    }

    public function getPackedProductDetails($id, $business_id, $key){
        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id');

        $query->where('transaction_sell_lines.packing_qty', '>', 0)
            ->where('transaction_sell_lines.out_of_stock', 0)
            ->whereIn('transaction_sell_lines.edited', [1,0]);

        $query->where('t.id', $id)
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final');

        $product = $query->select(
            'transaction_sell_lines.id as id',
            'p.name as product_name',
            'p.sku as sku',
            'p.item_code as item_code',
            'p.main_image as image',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.edit_quantity',
            'transaction_sell_lines.packing_qty',
            'transaction_sell_lines.updated_piking_qty',
            'transaction_sell_lines.packing_started_time',
            'transaction_sell_lines.packing_completed_time',
            'transaction_sell_lines.box_no'
        )
            ->orderBy('transaction_sell_lines.updated_at', 'desc')
            ->groupBy('p.sku')
            ->first();

        return $product;
    }

    //
    public function packing_started_time($result_id)
    {
        $sell_line = TransactionSellLine::whereRaw('quantity = packing_qty')->find($result_id);
        if($sell_line)
        {
            if(!$sell_line->packing_started_time && $sell_line->is_packed === 1)
            {
                $sell_line->packing_started_time = \Carbon::now();
                $sell_line->save();
            }
        }
    }

    // Pick A Edit Product
    public function pickAEditProduct($request, $id, $business_id, $picked_by)
    {
        $sell_line = TransactionSellLine::find($id);
        $sell_line_Before = TransactionSellLine::find($id);
        $transaction_before = Transaction::where('id', $sell_line_Before->transaction_id)->where('business_id', $business_id)->first();
        if($sell_line->edit_quantity != 0)
        {
            $current_stock = VariationLocationDetails::where('product_id', $sell_line->product_id)->first();
            if($current_stock){
                if($sell_line->edit_quantity > $sell_line->quantity)
                {
                    $quantity = $sell_line->edit_quantity - $sell_line->quantity;
                    $qty = $current_stock->qty_available - $quantity;
                }else{
                    $quantity = $sell_line->quantity - $sell_line->edit_quantity;
                    $qty = $current_stock->qty_available + $quantity;
                }
                $sell_line->quantity = (int)$sell_line->edit_quantity;
                $sell_line->edit_quantity = 0.00;
                $sell_line->save();
                VariationLocationDetails::where('product_id', $sell_line['product_id'])
                                    ->update(['qty_available'=> $qty]);

                $tax = $this->getProductTaxCal($request, $id);
                if($tax && !empty($sell_line->pos_line_tax_id))
                {
                    $sell_line_tax_add = TransactionSellLine::find($id);
                    $sell_line_tax_add->pos_line_tax_id = $tax['state_tax_id'] ?? Null;
                    $sell_line_tax_add->pos_line_tax_amount = $tax['state_tax'] ?? 0.00;
                    $sell_line_tax_add->city_tax_id = $tax['city_tax_id'] ?? Null;
                    $sell_line_tax_add->city_tax_amount = $tax['city_tax'] ?? 0.00;
                    $sell_line_tax_add->save();
                }
                $this->transactionInvoiceTotal($sell_line->transaction_id, $business_id);

                $this->editQtylogforInvoice($id,$sell_line_Before,$transaction_before->final_total);

                // $transaction = Transaction::where('id', $sell_line->transaction_id)
                // ->where('business_id', $business_id)
                // ->firstOrFail();

                // if($transaction)
                // {
                //     $before_line_price = $sell_line_Before->quantity * $sell_line_Before->unit_price ;

                //     $after_line_price = $sell_line->quantity * $sell_line_Before->unit_price ;

                //     $total = $transaction->final_total - $before_line_price;
                //     $total_before_tax = $transaction->total_before_tax - $before_line_price;
                //     $updatedtotal = $total + $after_line_price;
                //     $updated_before_tax_total = $total_before_tax + $after_line_price;

                //     $transaction->final_total = $updatedtotal;
                //     $transaction->total_before_tax = $updated_before_tax_total;
                //     $transaction->save();
                // }
            }
        }
    }

    /* Single product tax calculation */
    public function getProductTaxCal($request, $id)
    {
        $tr_sell_line = DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->select('transaction_sell_lines.*', 't.contact_id', 'v.sell_price_inc_tax', 'p.qty_box')
            ->where(function ($q) use ($request, $id) {
                    $q->where('transaction_sell_lines.id', $id);
            })->first();

        $tr_sell = json_encode($tr_sell_line);
        /*   Tax check */
        $result = $this->productUtil->getProductTax($tr_sell_line->product_id, $tr_sell_line->variation_id, $tr_sell_line->contact_id, 0, $tr_sell_line->sell_price_inc_tax);

        $hide_tax = 'hide';
        if(session()->get('business.enable_inline_tax') == 1){
            $hide_tax = '';
        }

        $tax_id = $tr_sell_line->tax_id;
        $item_tax = !empty($tr_sell_line->item_tax) ? $tr_sell_line->item_tax : 0;
        $unit_price_inc_tax = $tr_sell_line->sell_price_inc_tax;
        if($hide_tax == 'hide'){
            $tax_id = null;
            $unit_price_inc_tax = $tr_sell_line->unit_price;
        }
        $business_id = request()->session()->get('user.business_id');

        $total = $tr_sell_line->quantity * $unit_price_inc_tax;
        $new_total = $total;
        $tax_single = 0;
        $every_item = 0;
        $total_tax = 0;
        $tax_type = 0;
        $quantity = $tr_sell_line->quantity;
        $qty_box = $tr_sell_line->qty_box;
        $new_qty = $quantity;
        if($qty_box > 1)
        {
            $new_qty = $quantity * $qty_box;
        }

        if($result['tax'] > 0){
            $tax_single = $result['tax'];
            $state = $result['state'];
            $every_item = $result['every_item'];
            $tax_type = $result['tax_type'];
            //State tax calculation
            if($result['rule'] == 55 || $result['rule'] == 58 || $result['rule'] ==  59 || $result['rule'] ==  61 || $result['rule'] ==  62){
                $total_tax = floatval($tax_single) * floatval($quantity);
            }
            else if($every_item > 1){
                $times_of_apply_tax = intval( floatval($new_qty)/$every_item );
                $total_tax = floatval($tax_single) * $times_of_apply_tax;
            }else{
                if($tax_type == 1) $total_tax = floatval($tax_single) * floatval($new_qty);
                if($tax_type == 2) $total_tax = floatval($tax_single) * floatval($quantity);
            }
            $new_total = floatval($total) + floatval($total_tax);
            // tr.find('span.pos_line_totalamt_text').text(__currency_trans_from_en(new_total,true));
        }

        $city_tax_amt = 0;
        $city_tax_id = 0;
        $city_tax_name = 0;
        $first_item_value = 0;
        $second_item_value = 0;
        $city_tax = 0;
        $city_every_item = 0;
        $city_tax_type = 0;
        if($result['city_tax_id'] != 0 ){
            $city_tax_id = $result['city_tax_id'];
            $city_tax = $result['city_tax'];
            $city_every_item = $result['city_every_item'];
            $first_item_value = $result['first_item_value'];
            $second_item_value = $result['second_item_value'];
            $city_tax_name = $result['city_tax_name'];
            $city_tax_type = $result['city_tax_type'];

            if( $city_tax != 0 ){
                if($city_every_item > 1) {
                    $times_of_apply_tax = intval(floatval($new_qty)/$city_every_item);
                    $city_tax_amt = $times_of_apply_tax * $city_tax ;
                } else{
                    if($city_tax_type == 1)  $city_tax_amt = $new_qty * $city_tax ;
                    if($city_tax_type == 2)  $city_tax_amt = $quantity * $city_tax ;
                }
            } else {
                if($city_tax_type == 1) $second_applicable_qty = $new_qty - $quantity;
                if($city_tax_type == 2) $second_applicable_qty = 0;
                $city_tax_amt = floatval($first_item_value * $quantity) + floatval($second_item_value * $second_applicable_qty);
            }
        }
        $new_tax_total = floatval($total_tax) + floatval($city_tax_amt);

        $data = [];
        $data['success'] = 1;
        $data['msg'] = 'New Tax!';
        $data['state_tax_id'] = $result['rule'] ?? Null;
        $data['state_tax'] = floatval($total_tax) ?? 0.00;
        $data['city_tax'] = floatval($city_tax_amt) ?? 0.00;
        $data['city_tax_id'] = $city_tax_id ?? Null;
        $data['new_tax_total'] = $new_tax_total ?? Null;
        return $data;
    }

    /*Transaction Invoice Total Calculation  */
    public function transactionInvoiceTotal($transaction_id, $business_id)
    {
        $transaction = Transaction::where('id', $transaction_id)->where('business_id', $business_id)->first();
        if($transaction)
        {
            $tran_sell_line = TransactionSellLine::where('transaction_id', $transaction->id)->get();
            Transaction::where('id', $transaction->id)->update(['final_total' => null, 'total_before_tax' => null] );
            foreach ($tran_sell_line as $key => $sell_line) {
                if($sell_line->quantity > 0)
                {
                    $after_line_price = $sell_line->quantity * $sell_line->unit_price;
                    Transaction::where('id', $transaction->id)->increment('final_total', $after_line_price);
                    Transaction::where('id', $transaction->id)->increment('final_total', $sell_line->pos_line_tax_amount);
                    Transaction::where('id', $transaction->id)->increment('final_total', $sell_line->city_tax_amount);
                    Transaction::where('id', $transaction->id)->increment('total_before_tax', $after_line_price);
                }
            }
            Transaction::where('id', $transaction->id)->increment('final_total', $transaction->shipping_charges);
            Transaction::where('id', $transaction->id)->decrement('final_total', $transaction->discount_amount);
        }
    }

    // BOX QTY By developer1

    public function getBoxQty($box_nos)
    {
        $max_array = [];

        foreach ($box_nos as $key => $value)
        {

            $final_e_box_str = "";
            if(isset($value['box_no']) && $value['box_no']!='0' && $value['box_no']!= "")
            {
                $explode_box = explode(',', $value['box_no']);

                foreach ($explode_box as $e_box)
                {
                    $explode_box_str = explode(':', $e_box);

                    foreach ($explode_box_str as $e_box_str)
                    {
                        if (strpos($e_box_str, 'BOX') !== false) {

                            $final_e_box_str_to_arr = explode('BOX',$e_box_str);
                            $max_array[] = $final_e_box_str_to_arr[1];
                        }
                    }
                }
            }
        }

        if(count($max_array)>0)
        {
            return max($max_array);
        }
        else
        {
            return 0;
        }
    }
    // BOX QTY By developer1

    //merge BOX QTY By developer1
    public function mergeBoxQty($input_box_no,$result_box_nos)
    {
        //echo $result_box_nos;
        //echo $input_box_no;
        $matched_array = [];
        $res_array = [];
        $input_array = [];
        if(strpos($result_box_nos,'BOX')!== false && strpos($input_box_no,'BOX')!== false)
        {

            $explode_box = explode(',',$result_box_nos);

            $input_explode_box = explode(',',$input_box_no);

            //$final_explode_box = array_merge($explode_box,$input_explode_box);

            foreach ($explode_box as $e_box)
            {
                $explode_box_str = explode(':', $e_box);

                $res_array[$explode_box_str[0]] = $explode_box_str[1];
                $sum_qty = $explode_box_str[1];

                if (strpos($explode_box_str[0], 'BOX') !== false)
                {
                    foreach ($input_explode_box as $input_e_box)
                    {
                        $input_explode_box_str = explode(':', $input_e_box);
                        $input_array[$input_explode_box_str[0]] = $input_explode_box_str[1];

                        if (strpos($input_explode_box_str[0], 'BOX') !== false)
                        {
                            if(strtolower($explode_box_str[0]) == strtolower($input_explode_box_str[0]))
                            {
                                $sum_qty += $input_explode_box_str[1];
                                $matched_array[$explode_box_str[0]] = $sum_qty;
                            }
                        }
                    }
                }
            }
        }

        //echo print_r($matched_array);
        //exit;
        $res_updated_array = array_merge($res_array,$matched_array);
        $res_final_array = $res_updated_array + $input_array;

        if(!empty($res_final_array))
        {
            $final_box_sting = "";
            $count = 1;
            foreach($res_final_array as $key=>$value)
            {
                if($count < count($res_final_array))
                {
                    $final_box_sting .= $key.":".$value.",";
                }
                else
                {
                    $final_box_sting .= $key.":".$value;
                }
                $count++;
            }
            return $final_box_sting;
        }
        else
        {
            return $input_box_no;
        }
    }
    //merge BOX QTY By developer1

    public function editQtylogforInvoice($sell_line_id="", $old_sell_line="",$old_final_total="",$out_of_stock_txt="")
    {
        if(!empty($sell_line_id) && !empty($old_sell_line))
        {
            $pro='';

            $sell_line = TransactionSellLine::with(['product.unit','sub_unit'])->find($sell_line_id);
            $transaction = Transaction::where('id', $sell_line->transaction_id)->first();

            $product_details = DB::table('products')->where('id', $sell_line->product_id)->first();

            if(!empty($product_details))
            {
                $product_name = $product_details->name.' ('.$product_details->sku.')';
            }
            else
            {
                $product_name = "Product";
            }

            $unit_log = "";

            if(!empty($sell_line['sub_unit']))
            {
                $unit_log = $sell_line['sub_unit']['short_name'];
            }
            else
            {
                $unit_log = $sell_line['product']['unit']['short_name'];
            }

            if($sell_line['quantity']!=$old_sell_line['quantity'])
            {
                $pro .= 'Sale line Quantity ('.$this->num_uf($old_sell_line['quantity']).$unit_log.' --> '.$this->num_uf($sell_line['quantity']).$unit_log.') of '.$product_name.' at pick and pack stage'.$out_of_stock_txt.', ';
            }

            if($old_final_total!=$transaction->final_total)
            {
                $pro .= 'Total Payable ($'.$this->num_uf($old_final_total).' --> $'.$this->num_uf($transaction->final_total).') at pick and pack stage'.$out_of_stock_txt.', ';
            }

            if($pro!="")
            {
                $this->transactionUtil->Delinvoicelog('edited',auth()->user()->id,$sell_line->transaction_id,$pro);
            }
        }
    }
}