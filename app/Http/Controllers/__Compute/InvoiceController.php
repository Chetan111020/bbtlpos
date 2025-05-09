<?php

namespace App\Http\Controllers\__Compute;

use App\Category;
use App\Contact;
use App\Http\Controllers\Controller;
use App\JadooProduct;
use App\TaxRate;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\Models\StaticJadooInvoice;
use App\TransactionSellLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class InvoiceController extends Controller {

    public function smartInvoice($id, $preview = "", $strict_check = true) {
        $transaction = Transaction::where('id', $id)->with(['business', 'location'])->first();
        if (!empty($transaction)) {
            $transactionUtil = new TransactionUtil();
            $businessUtil = new BusinessUtil();

            $invoice_layout_id = $transaction->is_direct_sale ? $transaction->location->sale_invoice_layout_id : $transaction->location->invoice_layout_id;
            $invoice_layout = $businessUtil->invoiceLayout($transaction->business_id, $transaction->location->id, $invoice_layout_id);
            $business_details = $businessUtil->getDetails($transaction->business_id);

            $receipt_details = $transactionUtil->getReceiptDetails($id, $transaction->location->id, $invoice_layout, $business_details, $transaction->location, 'html');
            $tax_details = $transactionUtil->getTaxDetails($id);

            $contact = Contact::find($transaction->contact_id);
            $title =  !empty($contact->name) ?  $contact->name . ' | ' . $transaction->invoice_no :  $contact->supplier_business_name . ' | ' . $transaction->invoice_no;

            $MAGIC = empty($tax_details);
            if(empty($preview)){
                $preview = request()->input('preview', null);
            }
            $roles_verified = true;
            if($strict_check){
                $roles_verified = auth()->user()->roles()->whereIn('id',[9,15])->exists();
            }
            if($MAGIC && !empty($preview) && $preview == "1" && $roles_verified){
                $MAGIC = false;
            }

            // jadoo is currently disabled, comment to enable
            $MAGIC = false;

            if($MAGIC){
                // $new_lines_arr = $this->getJadooLines($receipt_details->lines);
                // if(!empty($preview) && $preview == "2"){
                    $subtotal = round(TransactionSellLine::where('transaction_id', $transaction->id)->sum(DB::raw('unit_price * quantity')), 2);
                    $new_lines_arr = $this->handleDynamicLines($receipt_details->lines, $transaction->id, $subtotal);
                // }
                // else
                if(!empty($preview) && $preview == "regen"){
                    $this->deleteExistingLines($transaction->id); // Delete existing lines
                    $new_lines_arr = $this->getDynamicJadooLines($receipt_details->lines);
                    $this->storeJadooLines($new_lines_arr, $transaction->id); // Store newly generated lines
                    return redirect()->route('invoice.smart', $id);
                    // $this->refresh();
                }
            }
            else{
              $box = request()->input('box', 'yes');
                        if ($box == 'yes') {
                            $new_lines_arr = $this->getBoxLines($receipt_details->lines);
                        } else {
                            $new_lines_arr = $this->getBoxlessLines($receipt_details->lines);
            }
            }

            $url = $transactionUtil->getInvoiceUrl($id, $transaction->business_id);
            $tr_new = Transaction::find($id);
            $wa_number = '';
            $wa_link = '';
            if(!empty($contact) && !empty($tr_new->invoice_token)){
                $wa_number = preg_replace('/[^0-9]/', '', $contact->whatsapp ?? '');
                if(strlen($wa_number) == 10){
                    $wa_number = "1".$wa_number;
                }
                if(strlen($wa_number) > 10){
                    $wa_link = "Hi there, this is your invoice: https://invoicetopdf.com/psd/" . $tr_new->invoice_token;
                }
            }
            $box = request()->input('box', 'yes');
            return view('invoice.smart_invoice', compact('wa_number','wa_link','MAGIC','receipt_details','new_lines_arr','tax_details','title','id','box'));
        }
        else {
            abort(404);
        }
    }
    private function getBoxlessLines($lines) {
        $new_lines_arr = [];
        foreach ($lines as $line) {
            $line['box_id'] = -1;
            $line['quantity'] = $line['quantity_uf'];
            $line['line_total'] = number_format($line['unit_price_inc_tax_uf'] * $line['quantity'], 2);
            $new_lines_arr[] = $line;
        }
        return $new_lines_arr;
    }
    private function getBoxLines($lines){
        $new_lines_arr = [];
        foreach ($lines as $line){
            $line_box_wise_qty_array = [];

            if(!empty($line['box_no'])){
                $boxes = explode(',',$line['box_no']);
                foreach($boxes as $box){
                    $box_store = explode(":",$box);
                    if(count($box_store) == 2 && !empty($box_store[1])){
                        $line_box_wise_qty_array[str_replace('BOX','',$box_store[0])] = $box_store[1];
                    }
                }
            }

            if(!empty($line_box_wise_qty_array)){
                $total_qty = $line['quantity_uf'];
                $boxed_qty = 0;
                foreach($line_box_wise_qty_array as $key=>$box_qty){
                    $line['box_id'] = $key;
                    $line['quantity'] = $box_qty;
                    $line['line_total'] = number_format($line['unit_price_inc_tax_uf'] * $line['quantity'], 2);
                    $new_lines_arr[] = $line;
                    $boxed_qty += $box_qty;
                }
                if($boxed_qty < $total_qty){
                    $line['box_id'] = -1;
                    $line['quantity'] = round($total_qty - $boxed_qty);
                    $line['line_total'] = number_format($line['unit_price_inc_tax_uf'] * $line['quantity'], 2);
                    $new_lines_arr[] = $line;
                }
            }
            else{
                $line['box_id'] = -1;
                $line['quantity'] = $line['quantity_uf'];
                $new_lines_arr[] = $line;
            }
        }
        usort($new_lines_arr, function($a, $b) {
            return $a['box_id'] > $b['box_id'];
        });
        return $new_lines_arr;
    }

    private function getJadooLines($lines){
        $products = JadooProduct::all();
        $new_lines_arr = [];
        foreach($lines as $line){
            foreach($products as $product){
                if(strpos(strtolower($line['name']), strtolower($product->name)) !== false){
                    $line['name'] = $product->jadoo_name;
                    $line['sub_sku'] = $product->barcode;
                    $line['item_code'] = $product->itemcode . "-" . rand(1111, 9999) . $line['pro_id'];
                    break;
                }
            }
            $new_lines_arr[] = $line;
        }

        $product_list = [];
        $merged_lines = [];
        $i = 0;
        foreach($new_lines_arr as $new_line){
            $new_line['box_id'] = -1;
            $line_unique_string = $new_line['name'] . '___' . $new_line['unit_price'];

            if(in_array($line_unique_string, $product_list)){
                $key = array_search($line_unique_string, $product_list);
                if(!empty($merged_lines[$key])){
                    $merged_lines[$key]['quantity_uf'] += $new_line['quantity_uf'];
                    $merged_lines[$key]['quantity'] = $merged_lines[$key]['quantity_uf'];
                    $merged_lines[$key]['line_total'] = number_format($merged_lines[$key]['unit_price_inc_tax_uf'] * $merged_lines[$key]['quantity'], 2);
                }
            }
            else{
                $product_list[$i] = $line_unique_string;
                $new_line['quantity'] = $new_line['quantity_uf'];
                $merged_lines[$i] = $new_line;
                $i++;
            }
        }

        return $merged_lines;
    }

    private function getDynamicJadooLines($lines){
        // $MAGIC_CATEGORIES = [701];
        $MAGIC_CATEGORIES = array_column(TaxRate::select('category')->get()->toArray(), 'category');
        $dynamic_amount = 0;

        $new_lines_arr = [];
        foreach($lines as $line){
            $line['box_id'] = -1;
            $line['quantity'] = round($line['quantity']);
            if(!empty($line['cat']->id) && in_array($line['cat']->id, $MAGIC_CATEGORIES)){
                $dynamic_amount += round($line['line_total_uf'], 2);
            }
            else{
                $new_lines_arr[] = $line;
            }
        }

        foreach($this->generateDynamicLines($dynamic_amount) as $dynamic_line){
            $prepared_line = [];
            $prepared_line['box_id'] = -1;
            $prepared_line['sub_sku'] = $dynamic_line->sku;
            $prepared_line['item_code'] = $dynamic_line->item_code;
            $prepared_line['name'] = $dynamic_line->name;
            $prepared_line['quantity'] = round($dynamic_line->quantity);
            $prepared_line['unit_price'] = round($dynamic_line->default_sell_price, 2);
            $prepared_line['line_total'] = number_format($prepared_line['quantity'] * $prepared_line['unit_price'], 2, '.', '');
            $prepared_line['unit_price'] = number_format($dynamic_line->default_sell_price, 2, '.', '');
            $new_lines_arr[] = $prepared_line;
        }
        // dd($new_lines_arr);
        return $new_lines_arr;
    }

    private function generateDynamicLines($amount){
        $productSelectionLimit = rand(1, 5); //MAX QUANTITY
        $retries = 5; // TIMES TO RETRY FOR RANDOM SELECTION INSTEAD OF MAX SELL:VALUE (ON FIRST ROUND)
        $threshold = 0; // Threshold to consider as zero amount

        $remainingAmount = $amount;
        $selectedProducts = [];
        $productQuantities = [];
        $selectedProductCounts = [];

        $i = 0;
        while ($remainingAmount > $threshold) {
            if ($retries > 0) {
                $product = $this->fetchProduct($remainingAmount, $selectedProductCounts, $productSelectionLimit, $retries <= 1 || $i < rand(3, 8));
                $i++;
            } else {
                $this->addDummyProduct($remainingAmount, $selectedProducts, $productQuantities);
                break;
            }
            if ($product) {
                if (!isset($productQuantities[$product->product_id])) {
                    $productQuantities[$product->product_id] = 0;
                    $selectedProducts[] = $product;
                }
                $productQuantities[$product->product_id]++;
                $remainingAmount -= $product->default_sell_price;
                if (!isset($selectedProductCounts[$product->product_id])) {
                    $selectedProductCounts[$product->product_id] = 0;
                }
                $selectedProductCounts[$product->product_id]++;
            } else {
                $retries--;
            }
        }
        $finalProducts = array_map(function ($product) use ($productQuantities) {
            $product->quantity = $productQuantities[$product->product_id];
            return $product;
        }, $selectedProducts);
        return $finalProducts;
    }

    private function fetchProduct($remainingAmount, $selectedProductCounts, $productSelectionLimit, $useRandomSelection = false){
        // $MAGIC_REVISED_CATEGORIES = array_column(Category::where('name','like','%glass%')->get()->toArray(), 'id');
        $taxable_categories = array_column(TaxRate::select('category')->get()->toArray(), 'category');
        $additional_tax_exception_categories = [712, 704, 730, 731, 732, 733, 734, 735, 736, 737, 689];
        $additional_tax_exception_categories2 = array_column(Category::where('name','like','%delta%')->orWhere('name','like','%hhc%')->get()->toArray(), 'id');
        $taxable_categories = array_merge($taxable_categories, $additional_tax_exception_categories, $additional_tax_exception_categories2);

        // dd($taxable_categories);
        $MAGIC_REVISED_CATEGORIES = array_column(Category::whereNotIn('id', $taxable_categories)->get()->toArray(), 'id');

        $query = Variation::select('variations.product_id', 'products.name', 'products.sku', 'products.item_code', 'variations.default_sell_price')
                    ->join('products', 'products.id', '=', 'variations.product_id')
                    ->where('not_for_selling', '<>', 1)
                    ->where('default_sell_price', '<=', $remainingAmount)
                    ->whereIn('products.category_id', $MAGIC_REVISED_CATEGORIES)
                    ->where(function($q){
                        $q->where('products.name','not like','%STUNDENGLASS%');
                    })
                    ->whereNotIn('variations.product_id', array_keys($selectedProductCounts, $productSelectionLimit, true));

        if ($useRandomSelection) {
            $product = $query->inRandomOrder()->first();
        } else {
            $product = $query->orderBy('default_sell_price', 'desc')->first();
        }

        return $product;
    }

    private function addDummyProduct($remainingAmount, &$selectedProducts, &$productQuantities){
        if ($remainingAmount > 0) {
            $dummyProductId = rand(10000, 99999);
            $selectedProducts[] = (object) [
                'product_id' => $dummyProductId,
                'name' => 'Portable Glass Mount',
                'default_sell_price' => $remainingAmount,
                'sku' => '100015943415',
                'item_code' => 'GXD 43415',
            ];
            $productQuantities[$dummyProductId] = 1;
        }
    }
    private function handleDynamicLines($lines, $transaction_id, $expectedTotal) {
        $stored_lines = $this->getStoredJadooLines($transaction_id);
        $stored_lines_sum = $stored_lines->sum('line_total');
        $istored_lines_sum = $this->normalizeAndFormatNumber($stored_lines_sum);
        $iexpectedTotal = $this->normalizeAndFormatNumber($expectedTotal);
        if ($stored_lines->isEmpty() || $istored_lines_sum != $iexpectedTotal) {
            if (!$stored_lines->isEmpty()) {
                $this->deleteExistingLines($transaction_id);
            }
            $new_lines_arr = $this->getDynamicJadooLines($lines);
            $this->storeJadooLines($new_lines_arr, $transaction_id);
            return $new_lines_arr;
        }
        return $stored_lines;
    }
    private function normalizeAndFormatNumber($number) {
        return round($number, 2);
        // $number = preg_replace('/[^0-9.]+/', '', $number);
        // return number_format((float)$number, 2, '.', '');
    }
    private function refresh(){
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            $uri = preg_replace('/(\?|&)preview=regen/', '$1', $uri);
            header('Location: ' . $uri);
            exit;
        }
    }
    private function getStoredJadooLines($transaction_id) {
        return StaticJadooInvoice::where('transaction_id', $transaction_id)->get();
    }

    private function storeJadooLines($lines, $transaction_id) {
        foreach ($lines as $line) {
            StaticJadooInvoice::create(array_merge($line, ['transaction_id' => $transaction_id]));
        }
    }
    private function deleteExistingLines($transaction_id) {
        StaticJadooInvoice::where('transaction_id', $transaction_id)->delete();
    }
}