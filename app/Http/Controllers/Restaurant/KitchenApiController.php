<?php

namespace App\Http\Controllers\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use App\Transaction;
use App\TransactionSellLine;

use App\Utils\Util;

use App\Utils\RestaurantUtil;
use Illuminate\Support\Facades\DB;

use App\VariationLocationDetails;
use App\Product;
use App\Variation;
use App\PickPackStartLog;
class KitchenApiController extends Controller
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
    public function getOrderData($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $orderdetails = $this->restUtil->getOrderProducts($id, $business_id);
        // dd($orderdetails);
        $orderProduct = $this->restUtil->getOrderProduct($id, $business_id);

        $contacts = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                            ->leftJoin('users as ur', 'transactions.created_by', '=', 'ur.id')

                            ->select('contacts.id','contacts.payment_status as p_status','transactions.order_picking_status','transactions.invoice_no','transactions.additional_notes','contacts.first_name as customer_name', 'contacts.supplier_business_name as company_name','ur.first_name','ur.last_name')
                            ->findorfail($id);
        // print_r($contacts);

        $pickedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'picked_product');
        $pickedProductsCount = count($pickedProducts);

        $outOfStockProducts = $this->restUtil->getPickedProducts($id, $business_id, 'out_of_stock');
        $outOfStockProductsCount = $outOfStockProducts->count();

        $editedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'edited');
        $editedProductsCount = count($editedProducts);

        $this->restUtil->startPicking($id, $business_id);

        $incorrectLocationProducts = $this->restUtil->getPickedProducts($id, $business_id, 'incorrect_location');
        $incorrectLocationProductsCount = count($incorrectLocationProducts);
        // dd($pickedProducts);
        $transaction_id = $id;

        // Picking started log
        $user_id = request()->session()->get('user.id');
        if($contacts->order_picking_status == 0){
            PickPackStartLog::create([
                'transaction_id' => $id,
                'user_id' => $user_id,
                'type' => 'start picking',
                'contact_id' => $contacts->id,
                'invoice_no' => $contacts->invoice_no,
            ]);
        }
        $data = [
            'id' => $id,
            'orderdetails' => $orderdetails,
            'orderProduct' => $orderProduct,
            'contacts' => $contacts,
            'pickedProducts' => $pickedProducts,
            'pickedProductsCount' => $pickedProductsCount,
            'outOfStockProducts' => $outOfStockProducts,
            'outOfStockProductsCount' => $outOfStockProductsCount,
            'editedProducts' => $editedProducts,
            'editedProductsCount' => $editedProductsCount,
            'incorrectLocationProducts' => $incorrectLocationProducts,
            'incorrectLocationProductsCount' => $incorrectLocationProductsCount
        ];
        return response()->json(['success' => true, 'message' => 'Order Picking Started Successfully.', 'data' => $data]);
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
        // dd($orderdetails);
        $orderProduct = $this->restUtil->getOrderProduct($id, $business_id);

        $contacts = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                            ->leftJoin('users as ur', 'transactions.created_by', '=', 'ur.id')

                            ->select('contacts.id','contacts.payment_status as p_status','transactions.order_picking_status','transactions.invoice_no','transactions.additional_notes','contacts.first_name as customer_name', 'contacts.supplier_business_name as company_name','ur.first_name','ur.last_name')
                            ->findorfail($id);
        // print_r($contacts);

        $pickedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'picked_product');
        $pickedProductsCount = count($pickedProducts);

        $outOfStockProducts = $this->restUtil->getPickedProducts($id, $business_id, 'out_of_stock');
        $outOfStockProductsCount = $outOfStockProducts->count();

        $editedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'edited');
        $editedProductsCount = count($editedProducts);

        $this->restUtil->startPicking($id, $business_id);

        $incorrectLocationProducts = $this->restUtil->getPickedProducts($id, $business_id, 'incorrect_location');
        $incorrectLocationProductsCount = count($incorrectLocationProducts);
        // dd($pickedProducts);
        $transaction_id = $id;

        // Picking started log
        $user_id = request()->session()->get('user.id');
        if($contacts->order_picking_status == 0){
            PickPackStartLog::create([
                'transaction_id' => $id,
                'user_id' => $user_id,
                'type' => 'start picking',
                'contact_id' => $contacts->id,
                'invoice_no' => $contacts->invoice_no,
            ]);
        }
        return view('restaurant.orders.order-picking-jeet', compact(
            'orderdetails',
            'contacts',
            'orderProduct',
            'pickedProducts',
            'pickedProductsCount',
            'outOfStockProducts',
            'outOfStockProductsCount',
            'editedProducts',
            'editedProductsCount',
            'incorrectLocationProducts',
            'incorrectLocationProductsCount',
            'transaction_id'
        ));
    }


    public function pickProduct(Request $request, $id)
    {
        if ($request->session()->has('raw')) {
            $request->session()->forget('raw');
        }
        if($request->raw)
        {
            $i =  $request->raw;
            if($request->raw > 0)
            {
                $i = $request->raw - 1;
            }
            $request->session()->put('raw', $i);
            $request->session()->get('raw');
        }
        $business_id = request()->session()->get('user.business_id');
        $staff_id = request()->session()->get('user.id');
        $this->restUtil->pickAProduct($request, $id, $business_id, $staff_id);
        return response()->json(['success' => true, 'message' => 'Product picked successfully.']);
    }

    public function outOfStock(Request $request, $id)
    {
        if ($request->session()->has('raw')) {
            $request->session()->forget('raw');
        }
        if($request->raw)
        {
            $i =  $request->raw;
            if($request->raw > 0)
            {
                $i = $request->raw - 1;
            }
            $request->session()->put('raw', $i);
            $request->session()->get('raw');
        }
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->outOfStockProduct($id, $business_id);
        return response()->json(['success' => true, 'message' => 'Product out of stock successfully.']);
    }

    public function incorrectLocation($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->incorrectLocation($id, $business_id);
        return response()->json(['success' => true, 'message' => 'Product Mark As Incorrect Location successfully.']);
    }


    public function saveAndHold($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->saveAndHoldOrder($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function saveAndHoldPacking($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->saveAndHoldOrderPacking($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    //recalculation finalize picking
    public function finalizePickingOrder_two($request, $id, $business_id)
    {
        $pickedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'picked_product');
        $pickedProductsCount = count($pickedProducts);

        if ($pickedProductsCount != 0) {
            $this->restUtil->finalizePickingOrder($request, $id, $business_id);
        }
    }

    public function finalizePickingOrder(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        self::finalizePickingOrder_two($request, $id, $business_id);

        $pickedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'picked_product');
        $pickedProductsCount = count($pickedProducts);

        if ($pickedProductsCount != 0) {
            $this->restUtil->finalizePickingOrder($request, $id, $business_id);


            $transaction = Transaction::find($id);
            $transaction->recalculate();

            return redirect(action('Restaurant\OrderController@index'));
        } else {


            $transaction = Transaction::find($id);
            $transaction->recalculate();

            return redirect(action('Restaurant\OrderController@index'));
        }
    }

    //recalculation finalize packing
    public function finalizePackingOrder_two($request, $id, $business_id)
    {
        $packedProducts = $this->restUtil->getPackedProducts($id, $business_id, 'packed_product');
        $packedProductsCount = count($packedProducts);

        if ($packedProductsCount != 0) {
            //Staff Store - packed_by
            $staff_id = request()->session()->get('user.id');
            $this->restUtil->startPacking($id, $business_id, $staff_id);


            $this->restUtil->finalizePackingOrder($request, $id, $business_id);

            $result = $request->finalizeprint;
            if($result){
                $transaction = Transaction::where('business_id', $business_id)
                            ->findOrFail($id);
                if (empty($transaction->invoice_token)) {
                    $transaction->invoice_token = $this->generateToken();
                    $transaction->save();
                }
            }
        }
    }

    public function finalizePackingOrder(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        self::finalizePackingOrder_two($request, $id, $business_id);

        $packedProducts = $this->restUtil->getPackedProducts($id, $business_id, 'packed_product');
        $packedProductsCount = count($packedProducts);

        if ($packedProductsCount != 0) {
            //Staff Store - packed_by
            $staff_id = request()->session()->get('user.id');
            $this->restUtil->startPacking($id, $business_id, $staff_id);

            $this->restUtil->finalizePackingOrder($request, $id, $business_id);

            $result = request()->get('finalizeprint');
            // if($result){
            //     $transaction = Transaction::where('business_id', $business_id)
            //                 ->findOrFail($id);
            //     if (empty($transaction->invoice_token)) {
            //         $transaction->invoice_token = $this->generateToken();
            //         $transaction->save();
            //     }
            //     return redirect(action('SellPosController@showInvoice', [$transaction->invoice_token]));
            // }
            // return redirect(action('Restaurant\OrderController@index'));
            if($result){
                $transaction = Transaction::where('business_id', $business_id)
                            ->findOrFail($id);
                if (empty($transaction->invoice_token)) {
                    $transaction->invoice_token = $this->generateToken();
                    $transaction->save();
                }
                $iurl = "/invoice/".$transaction->invoice_token;
                $surl = "/sells/pos/packing_slip/".$id;
            }else{

                $transaction = Transaction::find($id);
                $transaction->recalculate();

                return redirect(action('Restaurant\OrderController@index'));
            }

            $transaction = Transaction::find($id);
            $transaction->recalculate();

            if($result && $result == 'print'){
                return back()->withInput(array('status' => true, 'finalizeprint' => 'print', 'surl' => '', 'iurl' => $iurl ));
                //return redirect(action('SellPosController@showInvoice', [$transaction->invoice_token]));
            }elseif($result && $result == 'finalizeprintpackingslip'){
                return back()->withInput(array('status' => true, 'finalizeprint' => 'finalizeprintpackingslip', 'surl' => $surl, 'iurl' => '' ));
            }elseif($result &&  $result == 'printboth'){
                return back()->withInput(array('status' => true, 'finalizeprint' => 'printboth' , 'surl' => $surl, 'iurl' => $iurl ));
            }
            else{
                return redirect(action('Restaurant\OrderController@index'));
            }
        } else {

            $transaction = Transaction::find($id);
            $transaction->recalculate();

            return redirect(action('Restaurant\OrderController@index'));
        }
    }

    public function generateToken()
    {
        return md5(rand(1, 10) . microtime());
    }

    public function cancelPikingOrder($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->cancelPikingOrder($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function undoPicking($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->undoPicking($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function undoOutofStock($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->undoOutofStock($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function cancelPakingOrder($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->cancelPakingOrder($id, $business_id);
        return redirect(action('Restaurant\OrderController@index'));
    }

    public function undoPacking($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $this->restUtil->undoPacking($id, $business_id);
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

    //New Edit Product Qty
    public function editProductQty(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $sell_line = TransactionSellLine::find($request->p_id);
        if($sell_line)
        {
            if($sell_line->quantity != $request->p_qty)
            {
                DB::table('transaction_sell_lines')
                ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                ->where('transaction_sell_lines.id', $request->p_id)
                ->where('t.business_id', $business_id)
                ->update([
                    'edit_quantity' => $request->p_qty,
                    'quantity' => $sell_line->quantity,
                    'edited' => 1
                ]);
            }

            $sell_line = TransactionSellLine::find($request->p_id);
            if($sell_line->quantity == $request->p_qty)
            {
                DB::table('transaction_sell_lines')
                ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                ->where('transaction_sell_lines.id', $request->p_id)
                ->where('t.business_id', $business_id)
                ->update([
                    'edit_quantity' => 0.00,
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
            }
        }
        $this->restUtil->packing_started_time($request->p_id);
    }

    public function startPacking($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $orderdetails = $this->restUtil->getOrderProductForPacking($id, $business_id);
        $orderProduct = $this->restUtil->getOrderProduct($id, $business_id, 1);

        $contacts = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                            ->leftJoin('users as ur', 'transactions.created_by', '=', 'ur.id')
                            ->leftJoin('users as pb', 'transactions.picked_by', '=', 'pb.id')
                            ->select('contacts.id','contacts.payment_status as p_status','transactions.order_packing_status','transactions.invoice_no','transactions.additional_notes','contacts.first_name as customer_name', 'contacts.supplier_business_name as company_name','ur.first_name','ur.last_name','pb.first_name as pb_first_name' ,'pb.last_name as pb_last_name' )
                            ->findorfail($id);

        $invoiced_products = $this->restUtil->getInvoiceProducts($id, $business_id);
        $invoiceProductCount = count($invoiced_products);

        $packedProducts = $this->restUtil->getPackedProducts($id, $business_id, 'packed_product');
        $packedProductsCount = count($packedProducts);

        $packedProductDetails = $this->restUtil->getPackedProductDetails($id, $business_id, 'packed_product');

        $outOfStockProducts = $this->restUtil->getPickedProducts($id, $business_id, 'out_of_stock');
        $outOfStockProductsCount = $outOfStockProducts->count();

        $staff_id = request()->session()->get('user.id');
        //Staff Store - packed_by
        // $this->restUtil->startPacking($id, $business_id, $staff_id);

        $editedProducts = $this->restUtil->getPickedProducts($id, $business_id, 'edited');
        $editedProductsCount = count($editedProducts);

        $incorrectLocationProducts = $this->restUtil->getPickedProducts($id, $business_id, 'incorrect_location');
        $incorrectLocationProductsCount = count($incorrectLocationProducts);
        $transaction_id = $id;

        $get_last_selected_no =  DB::select("SELECT box_no FROM transaction_sell_lines where transaction_id = '". $id ."' ORDER BY updated_at DESC limit 1");
        //$get_last_selected_no =  DB::select("SELECT box_no FROM transaction_sell_lines where transaction_id = '". $id ."' ORDER BY packing_started_time DESC limit 1");


        if(!empty($get_last_selected_no))
        {
            $get_last_selected_no_arr[0]['box_no'] = $get_last_selected_no[0]->box_no;
            $last_selected_no = $this->restUtil->getBoxQty($get_last_selected_no_arr);
        }
        else
        {
            $last_selected_no = 0;
        }

        //print_r($last_selected_no);

        $box_nos =  TransactionSellLine::where('transaction_id', $id)->select('box_no')->get()->toArray();
        $last_box_no = 0;
        if(!empty($box_nos))
        {
            $last_box_no =  $this->restUtil->getBoxQty($box_nos);
        }

        if($last_box_no == '0' || $last_box_no == null){
            $last_box_no = 1;
        }
    //     echo $last_box_no;
    // die;
        // dd($orderProduct);
        $transaction = Transaction::find($id);
        if($last_box_no == 0 ){
            $transaction->update(['box_qty' => '0']);
        }
        else{
            $transaction->update(['box_qty' => $last_box_no]);

        }
        // packing started log
        $user_id = request()->session()->get('user.id');

        if($contacts->order_packing_status == 0){
            PickPackStartLog::create([
                'transaction_id' => $id,
                'user_id' => $user_id,
                'type' => 'start packing',
                'contact_id' => $contacts->id,
                'invoice_no' => $contacts->invoice_no,
            ]);
        }

        return view('restaurant.orders.order-packing',compact(
            'orderdetails',
            'orderProduct',
            'contacts',
            'editedProducts',
            'invoiced_products',
            'outOfStockProducts',
            'outOfStockProductsCount',
            'invoiceProductCount',
            'packedProducts',
            'editedProductsCount',
            'packedProductsCount',
            'incorrectLocationProducts',
            'incorrectLocationProductsCount',
            'transaction_id',
            'packedProductDetails',
            'transaction',
            'last_box_no',
            'last_selected_no'
        ));
    }

    public function packProduct(Request $request){
        $result = $this->restUtil->packProduct($request);
        $business_id = request()->session()->get('user.business_id');
        $staff_id = request()->session()->get('user.id');

        if (isset($request->packed_by_id) && $request->packed_by_id) {
            $sell_line = TransactionSellLine::find($request->searchKey);
            $this->restUtil->pickAEditProduct($request, $sell_line->id, $business_id, $staff_id);
            $sell_line_update = TransactionSellLine::find($request->searchKey);
            DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('transaction_sell_lines.id', $request->searchKey)
            ->where('t.business_id', $business_id)
            ->update([
                'packing_status' => 1,
                //'box_no' => $request->box_no,
                // 't.box_qty' => $request->box_no,
                'is_packed' => 1,
                'packing_qty' =>  (int)$sell_line_update->quantity
            ]);
        }

        $order_id = str_replace("#","",$request->order_id);

        $transaction_sell_line = DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->select("transaction_sell_lines.*")
            ->where('t.invoice_no', $order_id)
            ->whereRaw('transaction_sell_lines.edit_quantity = transaction_sell_lines.packing_qty')
            ->where(function ($update) use ($request) {
                if (isset($request->packed_by_id) && $request->packed_by_id) {
                    $update->where('transaction_sell_lines.id', $request->searchKey);
                }else{
                    $update->where('p.item_code', $request->searchKey)
                    ->orWhere('p.sku', $request->searchKey);
                }
            })->first();

        if(!$transaction_sell_line)
        {
            $transaction_sell_line = DB::table('transaction_sell_lines')
                ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
                ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
                ->select("transaction_sell_lines.*")
                ->where('t.invoice_no', $order_id)
                ->whereRaw('transaction_sell_lines.quantity = transaction_sell_lines.packing_qty')
                ->where(function ($update) use ($request) {
                    if (isset($request->packed_by_id) && $request->packed_by_id) {
                        $update->where('transaction_sell_lines.id', $request->searchKey);
                    }else{
                        $update->where('p.item_code', $request->searchKey)
                        ->orWhere('p.sku', $request->searchKey);
                    }
                })->first();
        }

        if($transaction_sell_line){
            $this->restUtil->pickAEditProduct($request, $transaction_sell_line->id, $business_id, $staff_id);

            if(TransactionSellLine::where('id', $transaction_sell_line->id)->where('edit_quantity', 0)->first())
            {
                TransactionSellLine::where('id', $transaction_sell_line->id)->whereRaw('quantity = packing_qty')->whereIn('edit_quantity', [0])->update([
                    'packing_status' => 1,
                    //'box_no' => $request->box_no,
                    'is_packed' => 1
                ]);
            }
        }
        return \response()->json($result);
    }

    //added by developer 1 - 8thDec
    public function packProductIdentify(Request $request){
        $business_id = request()->session()->get('user.business_id');
        $staff_id = request()->session()->get('user.id');

        $order_id = str_replace("#","",$request->order_id);
        $result = DB::table('transaction_sell_lines')
            ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->select("transaction_sell_lines.id", "transaction_sell_lines.packing_qty", "transaction_sell_lines.quantity", "transaction_sell_lines.updated_piking_qty", "p.sku")
            ->where('t.invoice_no', $order_id)
            ->where(function ($update) use ($request) {
                    $update->where('p.item_code', $request->searchKey)
                    ->orWhere('p.sku', $request->searchKey);
            })->first();

        if($result){
            $response = ['status' => true , "transaction_sell_line_sku" => $result->sku];
        }
        else
        {
            $response = ['status' => false];
        }

        return \response()->json($response);
    }

    //selected pick produts
    public function selectedPickProduct(Request $request)
    {
        if ($request->session()->has('raw')) {
            $request->session()->forget('raw');
        }
        if(count($request->selected_rows) > 0)
        {
            $request->selected_rows;
            $business_id = request()->session()->get('user.business_id');
            $staff_id = request()->session()->get('user.id');
            foreach ($request->selected_rows as $key => $id) {
                $query = DB::table('transaction_sell_lines')
                    ->leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                    ->where('transaction_sell_lines.id', $id)
                    ->where('t.business_id', $business_id);

                $query->update([
                    'is_picked' => 1,
                    'picking_started_time' => \Carbon::now(),
                    't.order_picking_status' => 1,
                    't.picked_by' => $staff_id
                ]);
                $this->restUtil->pickAEditProduct($request, $id, $business_id, $staff_id);
            }
            return 1;
        }
        return 0;
    }

}