<?php

namespace App\Http\Controllers\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use App\Transaction;
use App\TransactionSellLine;

use App\Utils\Util;

use App\Utils\RestaurantUtil;

class KitchenController extends Controller
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
        // if (!auth()->user()->can('sell.view')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');
        $orders = $this->restUtil->getAllOrders($business_id, ['line_order_status' => 'received']);

        return view('restaurant.kitchen.index', compact('orders'));
    }

    /**
     * Marks an order as cooked
     * @return json $output
     */
    public function markAsCooked($id)
    {
        // if (!auth()->user()->can('sell.update')) {
        //     abort(403, 'Unauthorized action.');
        // }
        try {
            $business_id = request()->session()->get('user.business_id');
            $sl = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                ->where('t.business_id', $business_id)
                ->where('transaction_id', $id)
                ->where(function ($q) {
                    $q->whereNull('res_line_order_status')
                        ->orWhere('res_line_order_status', 'received');
                })
                ->update(['res_line_order_status' => 'cooked']);

            $output = ['success' => 1,
                'msg' => trans("restaurant.order_successfully_marked_cooked")
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
     * Retrives fresh orders
     *
     * @return Json $output
     */
    public function refreshOrdersList(Request $request)
    {

        // if (!auth()->user()->can('sell.view')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');
        $orders_for = $request->orders_for;
        $filter = [];
        $service_staff_id = request()->session()->get('user.id');

        if (!$this->restUtil->is_service_staff($service_staff_id) && !empty($request->input('service_staff_id'))) {
            $service_staff_id = $request->input('service_staff_id');
        }

        if ($orders_for == 'kitchen') {
            $filter['line_order_status'] = 'received';
        } elseif ($orders_for == 'waiter') {
            $filter['waiter_id'] = $service_staff_id;
        }

        $orders = $this->restUtil->getAllOrders($business_id, $filter);
        return view('restaurant.partials.show_orders', compact('orders', 'orders_for'));
    }

    /**
     * Retrives fresh orders
     *
     * @return Json $output
     */
    public function refreshLineOrdersList(Request $request)
    {

        // if (!auth()->user()->can('sell.view')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $business_id = request()->session()->get('user.business_id');
        $orders_for = $request->orders_for;
        $filter = [];
        $service_staff_id = request()->session()->get('user.id');

        if (!$this->restUtil->is_service_staff($service_staff_id) && !empty($request->input('service_staff_id'))) {
            $service_staff_id = $request->input('service_staff_id');
        }

        if ($orders_for == 'kitchen') {
            $filter['order_status'] = 'received';
        } elseif ($orders_for == 'waiter') {
            $filter['waiter_id'] = $service_staff_id;
        }

        $line_orders = $this->restUtil->getLineOrders($business_id, $filter);
        return view('restaurant.partials.line_orders', compact('line_orders', 'orders_for'));
    }

    public function startPicking($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $orderdetails = $this->restUtil->getOrderProducts($id, $business_id);
                            
        $orderProduct = $this->restUtil->getOrderProduct($id, $business_id);

        $pickedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'picked_product');
        $pickedProductsCount = count($pickedProducts);

        $outOfStockProducts = $this->restUtil->getPickedProducts($id, $business_id, 'out_of_stock');
        $outOfStockProductsCount = count($outOfStockProducts);

        $editedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'edited');
        $editedProductsCount = count($editedProducts);

        $incorrectLocationProducts = $this->restUtil->getPickedProducts($id, $business_id, 'incorrect_location');
        $incorrectLocationProductsCount = count($incorrectLocationProducts);

        return view('restaurant.orders.order-picking', compact(
            'orderdetails',
            'orderProduct',
            'pickedProducts',
            'pickedProductsCount',
            'outOfStockProducts',
            'outOfStockProductsCount',
            'editedProducts',
            'editedProductsCount',
            'incorrectLocationProducts',
            'incorrectLocationProductsCount'
        ));
    }


    public function pickProduct(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->pickAProduct($request, $id, $business_id);
        return redirect()->back();
    }

    public function outOfStock($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->outOfStockProduct($id, $business_id);
        return redirect()->back();
    }

    public function incorrectLocation($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->incorrectLocation($id, $business_id);
        return redirect()->back();
    }


    public function saveAndHold($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->saveAndHoldOrder($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function finalizePickingOrder(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $pickedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'picked_product');
        $pickedProductsCount = count($pickedProducts);

        if ($pickedProductsCount != 0) {
            $this->restUtil->finalizePickingOrder($request, $id, $business_id);
            return redirect(action('Restaurant\OrderController@index'));
        } else {
            return redirect(action('Restaurant\OrderController@index'));
        }
    }

    public function cancelPikingOrder($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->cancelPikingOrder($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function orderQueue($id)
    {
//        $business_id = request()->session()->get('user.business_id');
//        $this->restUtil->cancelPikingOrder($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function updateProductQty(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->updateProductQty($request, $business_id);
    }

    public function startPacking($id)
    {
        echo "ddddd"; 
        //print_r($packedProducts);
        exit;
        $business_id = request()->session()->get('user.business_id');
        $orderdetails = $this->restUtil->getOrderProductForPacking($id, $business_id);
        $orderProduct = $this->restUtil->getOrderProduct($id, $business_id);

        $invoiced_products = $this->restUtil->getInvoiceProducts($id, $business_id);
        $invoiceProductCount = count($invoiced_products);

        $packedProducts = $this->restUtil->getPackedProducts($id, $business_id, 'packed_product');
        
        $packedProductsCount = count($packedProducts);


        $editedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'edited');
        $editedProductsCount = count($editedProducts);

        $incorrectLocationProducts = $this->restUtil->getPickedProducts($id, $business_id, 'incorrect_location');
        $incorrectLocationProductsCount = count($incorrectLocationProducts);
        return view('restaurant.orders.order-packing',compact(
            'orderdetails',
            'orderProduct',
            'editedProducts',
            'invoiced_products',
            'invoiceProductCount',
            'packedProducts',
            'editedProductsCount',
            'packedProductsCount',
            'incorrectLocationProducts',
            'incorrectLocationProductsCount'
        ));
    }

    public function packProduct(Request $request){
        $this->restUtil->packProduct($request);
        return \response()->json();
    }
}
