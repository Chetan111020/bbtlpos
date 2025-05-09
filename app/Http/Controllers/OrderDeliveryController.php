<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use App\TransactionSellLine;
use Yajra\DataTables\Facades\DataTables;
use App\OrderDelivery;
use App\User;
use DB;
use Illuminate\Validation\ValidationException;
use App\OrderDeliveryLog;

class OrderDeliveryController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
             $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }

            $delivery = OrderDelivery::leftjoin('users as u', 'u.id', '=', 'order_delivery.user_id')
                        ->leftjoin('transactions as t', 'order_delivery.transaction_id', '=', 't.id')
                        ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->select(
                    'order_delivery.id',
                    'order_delivery.transaction_id',
                    'order_delivery.status',
                    'order_delivery.img',
                    'order_delivery.payment_amount',
                    'order_delivery.payment_amount2',
                    'order_delivery.payment_amount3',
                    'order_delivery.payment_method',
                    'order_delivery.user_id',
                    'order_delivery.signature',
                    'order_delivery.lat',
                    'order_delivery.lng',
                    'order_delivery.created_at',
                    // 't.invoice_no',
                    'c.name as customer_name',
                                        't.invoice_no',
                    DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as created_by")
                )
                ->whereDate('order_delivery.created_at','>=', $start)
                ->whereDate('order_delivery.created_at','<=', $end)
                ->orderByDesc('order_delivery.created_at');

                // if (!auth()->user()->can('sell.view') && auth()->user()->can('view_own_sell_only')) {
                //     $delivery->where('t.created_by', request()->session()->get('user.id'));
                // }

                if (!empty(request()->assigned_to)) {
                    $assigned_to = request()->assigned_to;
                    $delivery->where('order_delivery.user_id', $assigned_to);
                }
            return Datatables::of($delivery->get())
                ->addColumn(
                    'action',
                    function ($row) {

                        $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                data-toggle="dropdown" aria-expanded="false">' .
                            __("messages.actions") .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left my-dropdown-1" role="menu">';


                         $html .=
                        '<li><a href="'. action('OrderDeliveryController@show', [$row->id]) .'" class="view-modal"><i class="fa fa-eye"></i> ' . __("messages.view") . '</a></li>';

                        if(auth()->user()->can('delivery.update')){
                            $html .= '<li><a href="' . action('OrderDeliveryController@edit', [$row->id]) . '" class=""><i class="fa fa-edit"></i> Edit</a></li>';
                        }
                        if(auth()->user()->can('delivery.delete')){
                            $html .=
                            '<li><a href="' . action('OrderDeliveryController@delete', [$row->id]) . '" class="delete-product"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                        }
                        if(auth()->user()->can('delivery.viewlocation')){
                            $html .= '<li><a class="view-location" data-lat="' . $row->lat . '" data-lng="' . $row->lng . '"><i class="fa fa-map-marker"></i> ' . __("View Delivery Location") . '</a></li>';
                        }
                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->editColumn('user_id', function ($row) {
                    return $row->created_by;
                })
                ->editColumn('status', function ($row) {
                    $status = $row->status;
                    if ($status == 'delivered') {
                        return '<span class="label label-success">' . __("Delivered") . '</span>';
                    } else if($status == 'not_delivered'){
                        return '<span class="label label-danger">' . __("Not Delivered") . '</span>';
                    }else{
                        return '';
                    }
                })
                ->editColumn('img', function ($row) {
                    $myimg = "/img/default.png";
                    if (strpos($row->img, '.') !== false) {
                        $myimg = asset('/uploads' . $row->img);
                    }
                    return '<div style="display: flex;"><img src="' . $myimg . '" alt="Product image" class="product-thumbnail-small"></div>';
                })
                 ->editColumn('signature', function ($row) {
                    $myimg = "/img/default.png";
                    if (strpos($row->signature, '.') !== false) {
                        $myimg = asset('/uploads/delivery/signature/' . $row->signature);
                    }
                    return '<div style="display: flex;"><img src="' . $myimg . '" alt="Product image" class="product-thumbnail-small"></div>';
                })
                 ->editColumn('payment_amount', function($row){
                      $payment_amount = is_numeric($row->payment_amount) ? floatval($row->payment_amount) : 0;
                        $payment_amount2 = is_numeric($row->payment_amount2) ? floatval($row->payment_amount2) : 0;
                        $payment_amount3 = is_numeric($row->payment_amount3) ? floatval($row->payment_amount3) : 0;

                        $data = $payment_amount + $payment_amount2 + $payment_amount3;
                        $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $data . '">' . $data . '</span>';
                        return $html;
                })
                 ->editColumn('created_at',function($row){
                    if ($row->created_at) {
                         return \Carbon\Carbon::parse($row->created_at)->format('m/d/Y h:i A');

                    }
                })
                ->rawColumns(['action', 'img', 'status', 'created_at', 'signature','payment_amount'])
                ->make(true);
        }
        $business_id = request()->session()->get('user.business_id');

        //  $users = User::forDropdown($business_id);
                 $users = User::forDropdown($business_id, false, false, true);

        return view('delivery.index', compact('users'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->can('delivery.create')) {
            abort(403, 'Unauthorized action.');
        }
        // $orders = Transaction::leftJoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
        //     ->select(
        //         'transactions.id as transaction_id',
        //         'transactions.invoice_no',
        //         'c.name as customer'
        //         )
        //     // ->where('tl.packing_status', '2')
        //     ->where('transactions.order_packing_status', '2')
        //     ->where('transactions.order_picking_status', '2')
        //     ->get();
         $query = Transaction::leftJoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
        ->select(
            'transactions.id as transaction_id',
            'transactions.invoice_no',
            'c.name as customer',
            'c.mobile as mobile',
            DB::raw('CONCAT_WS(", ", c.address_line_1, c.address_line_2, c.city, c.state, c.country, c.zip_code) AS address'),
            'c.lat as lat',
            'c.lgn as lgn'
        );

        //  if (auth()->user()->can('sell.view')) {
            $orders = $query
                ->where('transactions.type','sell')
                ->where('transactions.status','final')
                ->where('transactions.order_packing_status', '2')
                ->where('transactions.order_picking_status', '2')
                ->where('transactions.transaction_date','>','2024-02-01')
                ->orderBy('transactions.transaction_date','desc')
                ->get();
        // } elseif (auth()->user()->can('view_own_sell_only')) {
        //     $orders = $query
        //         ->where('c.created_by', auth()->user()->id)
        //         ->where('transactions.order_packing_status', '2')
        //         ->where('transactions.order_picking_status', '2')
        //         ->get();
        // } else {
        //     $orders = [];
        // }

        return view('delivery.create', compact('orders'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'transaction_id' => 'required',
                'deliveryStatus' => 'required',
                'img' => 'required_if:deliveryStatus,delivered|file',
            ], [
                'transaction_id.required' => 'Invoice No is required.',
                'deliveryStatus.required' => 'Delivery Status is required.',
                'img.required_if' => 'Image is required when delivery status is delivered.',
            ]);

            $img_details = null;

            if ($request->hasFile('img')) {
                $mainImagePath = $request->file('img')->store('delivery', 'local');
                $img_details = '/' . $mainImagePath;
            } else {
                $img_details = ' ';
            }

             if($request->signed){
                $folderPath = public_path('uploads/delivery/signature');


                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                $image_parts = explode(";base64,", $request->signed);

                $image_type_aux = explode("image/", $image_parts[0]);

                $image_type = $image_type_aux[1];

                $image_base64 = base64_decode($image_parts[1]);

                $fileName = uniqid() . '.' . $image_type;
                $file = $folderPath . '/' . $fileName;
                file_put_contents($file, $image_base64);
            }else{
                $fileName = ' ';
            }


            $store = OrderDelivery::create([
                'transaction_id' => $request->transaction_id,
                'status' => $request->deliveryStatus,
                'payment_amount' => $request->payment_amount,
                 'payment_amount2' => $request->payment_amount2,
                'payment_amount3' => $request->payment_amount3,
                'payment_method' => $request->payment_method,
                'payment_method2' => $request->payment_method2,
                'payment_method3' => $request->payment_method3,
                'cheque_number' => $request->cheque_number,
                'cheque_number2' => $request->cheque_number2,
                'cheque_number3' => $request->cheque_number3,
                'driver_note' => $request->note,
                'user_id' => $request->user_id,
                'img' => $img_details,
                'signature' => $fileName,
                'lat' => $request->lat,
                'lng' => $request->long
            ]);

            $output = [
                'success' => 1,
                'msg' => __('Add Successfully'),
                'redirect' => action('OrderDeliveryController@index'),
            ];
            return response()->json($output);
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                // If a validation error occurs, Laravel throws a ValidationException
                $errors = $e->errors();
                $output = [
                    'success' => 0,
                    'msg' => __('Validation error'),
                    'errors' => $errors,
                ];

                return response()->json($output, 422); // 422 Unprocessable Entity status code for validation errors
            }

            // Handle other exceptions
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return response()->json($output, 500);
        }
        session()->flash('status', $output);
        return response()->json($output);
        // return redirect()->action('OrderDeliveryController@index')->with('status', $output);
    }

    public function show($id)
    {
        $delivery = OrderDelivery::findOrFail($id);

        $customer = Transaction::leftJoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
                    ->select(
                        'transactions.id as transaction_id',
                        'transactions.invoice_no',
                        'c.name as customer_name'
                        )
                    ->where('transactions.id', $delivery->transaction_id)
                    ->first();

         $deliverylog = OrderDeliveryLog::join('users','users.id','=','order_delivery_log.user_id')
        ->where('order_id',$id)
            ->select('order_delivery_log.created_at as datetime',
                'order_delivery_log.message as message',
                'order_delivery_log.description as description',
                'users.first_name as first_name'
            )
            ->get();

        return view('delivery.show', compact('delivery', 'customer', 'deliverylog'));
    }

    public function edit($id)
    {
        $edit = OrderDelivery::findOrFail($id);
        $query = Transaction::leftJoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
        ->select(
            'transactions.id as transaction_id',
            'transactions.invoice_no',
            'c.name as customer',
            'c.mobile as mobile',
            DB::raw('CONCAT_WS(", ", c.address_line_1, c.address_line_2, c.city, c.state, c.country, c.zip_code) AS address'),
            'c.lat as lat',
            'c.lgn as lgn'
        );
        $orders = $query
                ->where('transactions.type','sell')
                ->where('transactions.status','final')
                ->where('transactions.order_packing_status', '2')
                ->where('transactions.order_picking_status', '2')
                ->where('transactions.transaction_date','>','2024-02-01')
                ->orderBy('transactions.transaction_date','desc')
                ->get();


        return view('delivery.newedit', compact('edit', 'orders'));
    }
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('delivery.update')) {
            abort(403, 'Unauthorized action.');
        }
        try {

            //  $updateData = [
            //     'status' => $request->deliveryStatus,
            //     'payment_amount' => $request->payment_amount,
            //     'payment_amount2' => $request->payment_amount2,
            //     'payment_amount3' => $request->payment_amount3,
            //     'payment_method' => $request->payment_method,
            //     'payment_method2' => $request->payment_method2,
            //     'payment_method3' => $request->payment_method3,
            //     'cheque_number' => $request->cheque_number,
            //     'cheque_number2' => $request->cheque_number2,
            //     'cheque_number3' => $request->cheque_number3,
            //     'driver_note' => $request->note,
            // ];

             $input = $request->only(['status', 'payment_amount','payment_amount2',
            'payment_amount3', 'payment_method', 'payment_method2', 'payment_method3','cheque_number', 'cheque_number2',
            'cheque_number3', 'driver_note']);

            $input['status'] = $request->status;
            $input['payment_amount'] = $request->payment_amount;
            $input['payment_amount2'] = $request->payment_amount2;
            $input['payment_amount3'] = $request->payment_amount3;
            $input['payment_method'] = $request->payment_method;
            $input['payment_method2'] = $request->payment_method2;
            $input['payment_method3'] = $request->payment_method3;
            $input['cheque_number'] = $request->cheque_number;
            $input['cheque_number2'] = $request->cheque_number2;
            $input['cheque_number3'] = $request->cheque_number3;
            $input['driver_note'] = $request->driver_note;

            if ($request->hasFile('img')) {
                $mainImagePath = $request->file('img')->store('delivery', 'local');
                $updateData['img'] = '/' . $mainImagePath;
            }
            $user_id = request()->session()->get('user.id');
            $business_id = $request->session()->get('user.business_id');

            $log = $this->UpdateDeliveryLog($input, $business_id, $user_id, $id);

            $update = OrderDelivery::findOrFail($id)->update($input);
             $output = [
                'success' => 1,
                'msg' => __('Delivery Update.')
            ];

            return redirect()->action('OrderDeliveryController@index')->with('status', $output);
        } catch (\Exception $e) {
            // DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            // return $e;
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
             return redirect()->back()->with('status', $output);
        }


    }

    public function delete($id)
    {
        if (!auth()->user()->can('delivery.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $delete = OrderDelivery::destroy($id);
            if ($delete == 1) {
                $output = [
                    'success' => 1,
                    'msg' => __('Deleted Successfully')
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __("You don't have permission to delete")
                ];
            }
        } catch (\Exception $e) {
            // DB::rollBack();
            return $e;
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        return redirect()->back()->with('status', $output);

    }
    public function checkOrder(Request $request)
    {
        $order = $request->transaction_id;
        $orders = OrderDelivery::where('transaction_id', $order)->first();

        if ($orders) {
            $data = [
                'success' => true,
                'message' => 'Order Already Exists'
            ];
        } else {
            $data = [
                'success' => false,
                'message' => ''
            ];
        }
        return response()->json($data);
    }

     public function DeliveryLog($type,$user_id,$order_id,$business_id,$pro='')
    {
        $user = auth()->user();
        $message = rtrim($pro, ',');
        $date = \Carbon::now()->toDateTimeString();
        $change_message = '';
        if(!empty($message)){
            $change_message = $message.' was changed by '.$user->first_name.' at '.$date;
        }

        if(!empty($message) && $type=='added')
        {
            $change_message = $message.' was added by '.$user->first_name.' at '.$date;
        }

        if(!empty($message) && $type=='deleted')
        {
            $change_message = '';
        }

        $log = new OrderDeliveryLog();
        $log->user_id = $user_id;
        $log->order_id = $order_id;
        $log->description = $type;
        $log->message = $change_message;
        $log->save();
    }
    public function UpdateDeliveryLog($input, $business_id, $user_id, $id)
    {
        $pro='';

        $order = OrderDelivery::find($id);

        $order_id = $id;

            if(isset($order->id)){
                // $pro .= $customer;
                if($input['status'] == 'delivered'){
                    if($order->status != $input['status']){
                        $oldstatus = 'Not Delivered';
                        $status = 'Delivered';
                        $pro .= 'Status ('.$oldstatus.' --> '.$status.'), ';
                    }
                }elseif($input['status'] == 'not_delivered'){
                    if($order->status != $input['status']){
                        $oldstatus = 'Delivered';
                        $status = 'Not Delivered';
                        $pro .= 'Status ('.$oldstatus.' --> '.$status.'), ';
                    }
                }

                if($order->payment_amount != $input['payment_amount']){
                    $pro .= 'Payment Amount ('.$order->payment_amount.' --> '.$input['payment_amount'].'), ';
                }

                if($order->payment_amount2 != $input['payment_amount2']){
                    $pro .= 'Payment Amount 2 ('.$order->payment_amount2.' --> '.$input['payment_amount2'].'), ';
                }

                if($order->payment_amount3 != $input['payment_amount3']){
                    $pro .= 'Payment Amount 3 ('.$order->payment_amount3.' --> '.$input['payment_amount3'].'), ';
                }

                if($order->payment_method != $input['payment_method']){
                    $pro .= 'Payment Method ('.$order->payment_method.' --> '.$input['payment_method'].'), ';
                }

                if($order->payment_method2 != $input['payment_method2']){
                    $pro .= 'Payment Method 2 ('.$order->payment_method2.' --> '.$input['payment_method2'].'), ';
                }

                if($order->payment_method3 != $input['payment_method3']){
                    $pro .= 'Payment Method 3 ('.$order->payment_method3.' --> '.$input['payment_method3'].'), ';
                }

                if($order->cheque_number != $input['cheque_number']){
                    $pro .= 'Cheque Number ('.$order->cheque_number.' --> '.$input['cheque_number'].'), ';
                }

                if($order->cheque_number2 != $input['cheque_number2']){
                    $pro .= 'Cheque Number 2 ('.$order->cheque_number2.' --> '.$input['cheque_number2'].'), ';
                }

                if($order->cheque_number3 != $input['cheque_number3']){
                    $pro .= 'Cheque Number 3 ('.$order->cheque_number3.' --> '.$input['cheque_number3'].'), ';
                }

                if($order->driver_note != $input['driver_note']){
                    $pro .= 'Cheque Number 3 ('.$order->driver_note.' --> '.$input['driver_note'].'), ';
                }

                // if($order->img != $input['img']){
                //     $pro .= 'Image Was Changed';
                // }

                if($pro!="")
                {
                    $this->DeliveryLog('edited',$user_id,$order_id,$business_id,$pro);
                }
            }
    }
}
