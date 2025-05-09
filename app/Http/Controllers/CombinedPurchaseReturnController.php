<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\PurchaseActivityLog;
use App\PurchaseLine;
use App\TaxRate;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\VendorcreditmemoActivityLog;

class CombinedPurchaseReturnController extends Controller
{

    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $moduleUtil;
    protected $transactionUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil, TransactionUtil $transactionUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('purchase_return.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();

        return view('purchase_return.create')
            ->with(compact('business_locations', 'taxes'));
    }

    public function returnConsigment($id)
    {   
        if (!auth()->user()->can('purchase_return.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();

        $purchaseData = [];
        
        if($id && $id != null){
            $purchaseData = $this->getPurchaseData($id,$business_id);
        }
        $purchase = [];

        foreach ($purchaseData->purchase_lines as $key => $value) {
            $send_request = new Request([
                'row_index' => $key,
                'variation_id' => $value->variation_id,
                'location_id' => $purchaseData->location_id,
            ]);
        
            $send_request->setLaravelSession(app('session')->driver());
            $send_request->headers->set('X-Requested-With', 'XMLHttpRequest');
        
            $response = $this->getProductRowForConsignment($send_request);
        
            if ($response) {
                $purchase[$key] = $response->render();
            }
        }
        // dd($purchaseData,$purchase);
        return view('purchase_return.consignment-return')
            ->with(compact('business_locations', 'taxes','id','purchase','purchaseData'));

    }
    public function getPurchaseData($id,$business_id)
    {
        $purchase = Transaction::where('business_id', $business_id)
                        ->where('type', 'purchase')
                        ->with(['purchase_lines', 'contact', 'tax', 'return_parent', 'purchase_lines.sub_unit', 'purchase_lines.product', 'purchase_lines.product.unit'])
                        ->find($id);

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        foreach ($purchase->purchase_lines as $key => $value) {
            $qty_available = $value->quantity - $value->quantity_sold - $value->quantity_adjusted;

            $purchase->purchase_lines[$key]->formatted_qty_available = $this->transactionUtil->num_f($qty_available);
        }

        return $purchase;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        // dd($request);
        if (!auth()->user()->can('purchase_return.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            DB::beginTransaction();

            $input_data = $request->only([ 'location_id', 'transaction_date', 'final_total', 'ref_no',
                'tax_id', 'tax_amount', 'contact_id','box_qty',
                'discount_type','additional_notes']);
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $user_id = $request->session()->get('user.id');

            $input_data['type'] = 'purchase_return';
            $input_data['business_id'] = $business_id;
            $input_data['location_id'] = 4 ;
            $input_data['shipping_charges'] = $request->shipping_charges ?? 0;
            $input_data['discount_amount'] = $request->discount_amount ?? 0;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['total_before_tax'] = $input_data['final_total'] - ($input_data['tax_amount'] ?? 0);

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('purchase_return');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('purchase_return', $ref_count);
            }

            //upload document
            $input_data['document'] = $this->productUtil->uploadFile($request, 'document', 'documents');

            $products = $request->input('products');

            if (!empty($products)) {
                $product_data = [];

                foreach ($products as $product) {
                    $unit_price = $this->productUtil->num_uf($product['box_price']);//unit_price
                    $return_line = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => 0,
                        'purchase_price' => $unit_price,
                        'purchase_price_inc_tax' => $unit_price,
                        'pp_without_discount' => $unit_price,
                        'inv_return' =>  $product['inv_return'],
                        'loose_qty' =>  $product['loose_qty'],
                        'loose_price' =>  $product['loose_price'],
                        'vendor_return_qty' =>  $product['vendor_return_qty'] ?? 0,
                        'box_price' =>  $product['box_price'],
                        'sub_total' =>  $product['sub_total'],
                        'quantity_returned' => 0,
                        'lot_number' => !empty($product['lot_number']) ? $product['lot_number'] : null,
                        'exp_date' => !empty($product['exp_date']) ? $this->productUtil->uf_date($product['exp_date']) : null
                    ];

                    $return_line['is_deducted'] = 1;
                    if(isset($product['is_deducted']) && $product['is_deducted'] == 'on' && $product['inv_return'] > 0){
                        $return_line['is_deducted'] = 0;
                        $return_line['quantity_returned'] = $this->productUtil->num_uf($product['inv_return']);

                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            4,//$input_data['location_id'],
                            $this->productUtil->num_uf($product['inv_return'])
                        );
                    }

                    $product_data[] = $return_line;
                }

                $purchase_return = Transaction::create($input_data);
                $purchase_return->purchase_lines()->createMany($product_data);
                //added by developer 1
                $transaction = Transaction::latest('id')->first();
                $transaction_id = $transaction->id;
                $this->transactionUtil->VendorcreditmemoActivityLog('added',$user_id,$transaction_id);

                //update payment status
                $this->transactionUtil->updatePaymentStatus($purchase_return->id, $purchase_return->final_total);
            }

            $output = ['success' => 1,
                            'msg' => __('lang_v1.purchase_return_added_success')
                        ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('purchase-return')->with('status', $output);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('purchase_return.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $purchase_return = Transaction::where('business_id', $business_id)
                                    ->with(['contact'])
                                    ->find($id);


        if(!auth()->user()->can('purchase_return.view') && auth()->user()->can('purchase_return.view.own')){
            if($purchase_return->created_by != auth()->user()->id){
                abort(403, 'Unauthorized action.');
            }
        }

        $location_id = $purchase_return->location_id;
        $purchase_lines = PurchaseLine::
                        join(
                            'products AS p',
                            'purchase_lines.product_id',
                            '=',
                            'p.id'
                        )
                        ->join(
                            'variations AS variations',
                            'purchase_lines.variation_id',
                            '=',
                            'variations.id'
                        )
                        ->join(
                            'product_variations AS pv',
                            'variations.product_variation_id',
                            '=',
                            'pv.id'
                        )
                        ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                            $join->on('variations.id', '=', 'vld.variation_id')
                                ->where('vld.location_id', '=', $location_id);
                        })
                        ->leftjoin('units', 'units.id', '=', 'p.unit_id')
                        ->where('purchase_lines.transaction_id', $id)
                        ->select(
                            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name,
                                    ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                            'p.id as product_id',
                            'p.enable_stock',
                            'pv.is_dummy as is_dummy',
                            'variations.sub_sku',
                            'vld.qty_available',
                            'variations.id as variation_id',
                            'units.short_name as unit',
                            'units.allow_decimal as unit_allow_decimal',
                            'purchase_lines.purchase_price',
                            'purchase_lines.id as purchase_line_id',
                            'purchase_lines.quantity_returned as quantity_returned',
                            'purchase_lines.lot_number',
                            'purchase_lines.exp_date',
                            'purchase_lines.*'
                        )
                        ->get();

        foreach ($purchase_lines as $key => $value) {
            $purchase_lines[$key]->qty_available += $value->quantity_returned;
            $purchase_lines[$key]->formatted_qty_available = $this->productUtil->num_f($purchase_lines[$key]->qty_available);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
                        ->ExcludeForTaxGroup()
                        ->get();

        return view('purchase_return.edit')
            ->with(compact('business_locations', 'taxes', 'purchase_return', 'purchase_lines'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (!auth()->user()->can('purchase_return.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input_data = $request->only(['transaction_date', 'final_total',
                'tax_id', 'tax_amount', 'contact_id','discount_type','additional_notes','box_qty'
            ]);

            $input_data['shipping_charges'] = $request->shipping_charges ?? 0;
            $input_data['discount_amount'] = $request->discount_amount ?? 0;

            $business_id = $request->session()->get('user.business_id');

            if (!empty($request->input('ref_no'))) {
                $input_data['ref_no'] = $request->input('ref_no');
            }

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
            $input_data['total_before_tax'] = $input_data['final_total'] - ($input_data['tax_amount'] ?? 0);

            //upload document
            $doc_name = $this->productUtil->uploadFile($request, 'document', 'documents');

            if (!empty($doc_name)) {
                $input_data['document'] = $doc_name;
            }

            $products = $request->input('products');
            $purchase_return_id = $request->input('purchase_return_id');
            $purchase_return = Transaction::where('business_id', $business_id)
                                ->where('type', 'purchase_return')
                                ->find($purchase_return_id);

            if(!auth()->user()->can('purchase_return.view') && auth()->user()->can('purchase_return.view.own')){
                if($purchase_return->created_by != auth()->user()->id){
                    abort(403, 'Unauthorized action.');
                }
            }

            if (!empty($products)) {
                $product_data = [];
                $updated_purchase_lines = [];

                foreach ($products as $product) {
                    $unit_price = $this->productUtil->num_uf($product['box_price']);//unit_price
                    if (!empty($product['purchase_line_id'])) {
                        $return_line = PurchaseLine::find($product['purchase_line_id']);
                        $updated_purchase_lines[] = $return_line->id;

                        $new_deduct = (isset($product['is_deducted']) && $product['is_deducted'] == 'on');
                        $old_deduct = ($return_line->is_deducted == 0);

                        $return_line->quantity_returned = 0;
                        $return_line->is_deducted = 1;

                        //tt
                        if($old_deduct && $new_deduct){
                            $return_line->is_deducted = 0;
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $purchase_return->location_id,
                                $this->productUtil->num_uf($product['inv_return']),
                                $return_line->inv_return
                            );
                            $return_line->quantity_returned = $this->productUtil->num_uf($product['inv_return']);
                        }
                        //tf
                        if($old_deduct && !$new_deduct){
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $purchase_return->location_id,
                                ($return_line->inv_return)*-1
                            );
                        }
                        //ft
                        if(!$old_deduct && $new_deduct){
                            $return_line->is_deducted = 0;
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $purchase_return->location_id,
                                $this->productUtil->num_uf($product['inv_return'])
                            );
                            $return_line->quantity_returned = $this->productUtil->num_uf($product['inv_return']);
                        }

                    } else {
                        $return_line = new PurchaseLine([
                            'product_id' => $product['product_id'],
                            'variation_id' => $product['variation_id'],
                            'quantity' => 0
                        ]);
                        $return_line->is_deducted = 1;
                        $return_line->quantity_returned = 0;
                        if(isset($product['is_deducted']) && $product['is_deducted'] == 'on'){
                            $return_line->is_deducted = 0;
                            //Decrease available quantity
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $purchase_return->location_id,
                                $this->productUtil->num_uf($product['inv_return'])
                            );
                            $return_line->quantity_returned = $this->productUtil->num_uf($product['inv_return']);
                        }
                    }

                    $return_line->inv_return = $product['inv_return'];
                    $return_line->loose_price = $product['loose_price'];
                    $return_line->loose_qty = $product['loose_qty'];
                    $return_line->box_price = $product['box_price'];
                    $return_line->sub_total = $product['sub_total'];

                    $return_line->purchase_price = $unit_price;
                    $return_line->pp_without_discount = $unit_price;
                    $return_line->purchase_price_inc_tax = $unit_price;

                    $return_line->lot_number = !empty($product['lot_number']) ? $product['lot_number'] : null;
                    $return_line->exp_date = !empty($product['exp_date']) ? $this->productUtil->uf_date($product['exp_date']) : null;
                    $product_data[] = $return_line;
                }

                //added by developer1
                $user_log_id = $request->session()->get('user.id');
                $this->transactionUtil->UpdatePurchaseReturnLog($request, $business_id, $user_log_id,'',$request->input('purchase_return_id'));
                //added by developer1

                $purchase_return->update($input_data);

                //If purchase line deleted add return quantity to stock
                $deleted_purchase_lines = PurchaseLine::where('transaction_id', $purchase_return_id)
                            ->whereNotIn('id', $updated_purchase_lines)
                            ->get();

                foreach ($deleted_purchase_lines as $dpl) {
                    $this->productUtil->updateProductQuantity($purchase_return->location_id, $dpl->product_id, $dpl->variation_id, $dpl->quantity_returned, 0, null, false);
                }

                PurchaseLine::where('transaction_id', $purchase_return_id)
                            ->whereNotIn('id', $updated_purchase_lines)
                            ->delete();

                $purchase_return->purchase_lines()->saveMany($product_data);

                //update payment status
                $this->transactionUtil->updatePaymentStatus($purchase_return->id, $purchase_return->final_total);
            }

            $output = ['success' => 1,
                            'msg' => __('lang_v1.purchase_return_updated_success')
                        ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('purchase-return')->with('status', $output);
    }

    /**
     * Return product rows
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProductRow(Request $request)
    {
        if (request()->ajax()) {
            $row_index = $request->input('row_index');
            $variation_id = $request->input('variation_id');
            $location_id = $request->input('location_id');

            $business_id = $request->session()->get('user.business_id');
            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, 4, false);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);

            if($product->qty_available <= 0){
                $product->product_name .= " (Out of Stock)";
            }

            return view('purchase_return.partials.product_table_row')
            ->with(compact('product', 'row_index'));
        }
    }
    public function getProductRowForConsignment(Request $request)
    {
        if ($request->ajax()) {
            $row_index = $request->input('row_index');
            $variation_id = $request->input('variation_id');
            $location_id = $request->input('location_id');

            $business_id = $request->session()->get('user.business_id');
            $product = $this->productUtil->getDetailsFromVariationForReturnConsignment($variation_id, $business_id, 4, false);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);

            if($product->qty_available <= 0){
                $product->product_name .= " (Out of Stock)";
            }
            // dd($product);
            return view('purchase_return.partials.return_consignment_table_row')
            ->with(compact('product', 'row_index'));
        }
    }
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax() || true) {
            try {
                $output = ['success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];
                $business_id = $request->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                    ->where('id', $transaction_id)
                    ->first();
                if (empty($transaction)) {
                    return $output;
                }

                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, 'browser');

                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                $output = ['success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];
            }
            return $output;
        }
    }


    /**
     * Returns the content for the receipt
     *
     * @param  int $business_id
     * @param  int $location_id
     * @param  int $transaction_id
     * @param string $printer_type = null
     *
     * @return array
     */
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null
    )
    {
        $output = ['is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => []
        ];

        $location_details = BusinessLocation::find($location_id);

        //Check if printing of invoice is enabled or not.
        if ($location_details->print_receipt_on_invoice == 1) {
            //If enabled, get print type.
            $output['is_enabled'] = true;

            $purchase = Transaction::where('business_id', $business_id)
                            ->with(['return_parent', 'return_parent.tax', 'purchase_lines', 'contact', 'tax', 'purchase_lines.sub_unit', 'purchase_lines.product', 'purchase_lines.product.unit'])
                            ->find($transaction_id);

            foreach ($purchase->purchase_lines as $key => $value) {
                if (!empty($value->sub_unit_id)) {
                    $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                    $purchase->purchase_lines[$key] = $formated_purchase_line;
                }
            }

            $purchase_taxes = [];
            if (!empty($purchase->return_parent->tax)) {
                if ($purchase->return_parent->tax->is_tax_group) {
                    $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->return_parent->tax, $purchase->return_parent->tax_amount));
                } else {
                    $purchase_taxes[$purchase->return_parent->tax->name] = $purchase->return_parent->tax_amount;
                }
            }

            //For combined purchase return return_parent is empty
            if (empty($purchase->return_parent) && !empty($purchase->tax)) {
                if ($purchase->tax->is_tax_group) {
                    $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
                } else {
                    $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
                }
            }

            $output['html_content'] = view('purchase_return.show_new')
                    ->with(compact('purchase', 'purchase_taxes'))->render();
        }

        return $output;
    }

}