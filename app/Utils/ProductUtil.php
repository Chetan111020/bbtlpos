<?php

namespace App\Utils;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Discount;
use App\Media;
use App\Product;
use App\ProductRack;
use App\ProductVariation;
use App\PurchaseLine;
use App\PoLine;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\TransactionSellLinesPurchaseLines;
use App\Unit;
use App\Variation;
use App\VariationGroupPrice;
use App\VariationLocationDetails;
use App\VariationTemplate;
use App\VariationValueTemplate;
use App\ProductActivityLog;
use App\ProductActivitiesLog;
use Illuminate\Support\Facades\DB;
use App\JadooProduct;

class ProductUtil extends Util
{
    /**
     * Create single type product variation
     *
     * @param (int or object) $product
     * @param $sku
     * @param $purchase_price
     * @param $dpp_inc_tax (default purchase pric including tax)
     * @param $profit_percent
     * @param $selling_price
     * @param $combo_variations = []
     *
     * @return boolean
     */
    public function createSingleProductVariation($product, $sku, $purchase_price, $dpp_inc_tax, $profit_percent, $selling_price, $selling_price_inc_tax, $combo_variations = [], $stock=0, $location_id=0 , $selling_price_tier1=0, $selling_price_tier2=0, $selling_price_tier3=0, $selling_price_tier4=0)
    {
        if (!is_object($product)) {
            $product = Product::find($product);
        }

        //create product variations
        $product_variation_data = [
                                    'name' => 'DUMMY',
                                    'is_dummy' => 1
                                ];
        $product_variation = $product->product_variations()->create($product_variation_data);

        //create variations
        $variation_data = [
                'name' => 'DUMMY',
                'product_id' => $product->id,
                'sub_sku' => $product->sku,
                'default_purchase_price' => $this->num_uf($purchase_price),
                'dpp_inc_tax' => $this->num_uf($dpp_inc_tax),
                'profit_percent' => $this->num_uf($profit_percent),
                'default_sell_price' => $this->num_uf($selling_price),
                'sell_price_inc_tax' => $this->num_uf($selling_price),
                'combo_variations' => $combo_variations
            ];
        $variation = $product_variation->variations()->create($variation_data);

        // product tier price start
        if(isset($variation))
        {
            $this->AddOrUpdateProductTiersPrice($variation,$selling_price_tier1,$selling_price_tier2,$selling_price_tier3,$selling_price_tier4);
        }
        // product tier price end

        Media::uploadMedia($product->business_id, $variation, request(), 'variation_images');
        if($location_id != 0) {
          $stocks[$location_id][$variation->id][0]['quantity']=$stock;
          $stocks[$location_id][$variation->id][0]['purchase_price']=$purchase_price;
          $data = app('App\Http\Controllers\OpeningStockController')->addStock($stocks, $location_id,  $product->id);
        }

        return true;
    }

    /**
     * Create variable type product variation
     *
     * @param (int or object) $product
     * @param $input_variations
     *
     * @return boolean
     */
   public function createVariableProductVariations($product, $input_variations, $location_id, $business_id = null)
    {
        if (!is_object($product)) {
            $product = Product::find($product);
        }

        //create product variations
        foreach ($input_variations as $key => $value) {
            $images = [];
            $stock = [];
            $barcode = [];
            $aisle = [];
            $shelf = [];
            $rack = [];
            $bin = [];
            $variation_template_name = !empty($value['name']) ? $value['name'] : null;
            $variation_template_id = !empty($value['variation_template_id']) ? $value['variation_template_id'] : null;

            if (empty($variation_template_id)) {
                if ($variation_template_name != 'DUMMY') {
                    $variation_template = VariationTemplate::where('business_id', $business_id)
                                                        ->whereRaw('LOWER(name)="' . strtolower($variation_template_name) . '"')
                                                        ->with(['values'])
                                                        ->first();
                    if (empty($variation_template)) {
                        $variation_template = VariationTemplate::create([
                            'name' => $variation_template_name,
                            'business_id' => $business_id
                        ]);
                    }
                    $variation_template_id = $variation_template->id;
                }
            } else {
                $variation_template = VariationTemplate::with(['values'])->find($value['variation_template_id']);
                $variation_template_id = $variation_template->id;
                $variation_template_name = $variation_template->name;
            }

            $product_variation_data = [
                                    'name' => $variation_template_name,
                                    'product_id' => $product->id,
                                    'is_dummy' => 0,
                                    'variation_template_id' => $variation_template_id
                                ];
            $product_variation = ProductVariation::create($product_variation_data);

            //create variations
            if (!empty($value['variations'])) {
                $variation_data = [];

                $c = Variation::withTrashed()
                        ->where('product_id', $product->id)
                        ->count() + 1;

                foreach ($value['variations'] as $k => $v) {
                    $sub_sku = empty($v['sub_sku'])? $this->generateSubSku($product->sku, $c, $product->barcode_type) :$v['sub_sku'];
                    $variation_value_id = !empty($v['variation_value_id']) ? $v['variation_value_id'] : null;
                    $variation_value_name = !empty($v['value']) ? $v['value'] : null;

                    if (!empty($variation_value_id)) {
                        $variation_value = $variation_template->values->filter(function ($item) use ($variation_value_id) {
                            return $item->id == $variation_value_id;
                        })->first();
                        $variation_value_name = $variation_value->name;
                    } else {
                        if (!empty($variation_template)) {
                            $variation_value =  VariationValueTemplate::where('variation_template_id', $variation_template->id)
                                ->whereRaw('LOWER(name)="' . $variation_value_name . '"')
                                ->first();
                            if (empty($variation_value)) {
                                $variation_value =  VariationValueTemplate::create([
                                    'name' => $variation_value_name,
                                    'variation_template_id' => $variation_template->id
                                ]);
                            }
                            $variation_value_id = $variation_value->id;
                            $variation_value_name = $variation_value->name;
                        } else {
                            $variation_value_id = null;
                            $variation_value_name = $variation_value_name;
                        }
                    }

                    $variation_data[] = [
                      'name' => $variation_value_name,
                      'variation_value_id' => $variation_value_id,
                      'product_id' => $product->id,
                      'sub_sku' => $sub_sku,
                      'default_purchase_price' => $this->num_uf($v['default_purchase_price']),
                      'dpp_inc_tax' => $this->num_uf($v['dpp_inc_tax']),
                      'profit_percent' => $this->num_uf($v['profit_percent']),
                      'default_sell_price' => $this->num_uf($v['default_sell_price']),
                      'sell_price_inc_tax' => $this->num_uf($v['sell_price_inc_tax'])
                    ];
                    $c++;
                    $images[] = 'variation_images_' . $key . '_' . $k;
                    $purchase_prices[] = $v['default_purchase_price'];
                    $stock[] = $v['on_hand'];
                    $barcode[] = $v['barcode'];
                    $aisle[] = $v['aisle'];
                    $rack[] = $v['rack'];
                    $shelf[] = $v['shelf'];
                    $bin[] = $v['bin'];
                }
                $variations = $product_variation->variations()->createMany($variation_data);

                $i = 0;
                foreach ($variations as $variation) {
                    Media::uploadMedia($product->business_id, $variation, request(), $images[$i]);
                    $stocks[$location_id][$variation->id][0]['quantity']= $stock[$i];
                    $stocks[$location_id][$variation->id][0]['barcode']= $barcode[$i];
                    $stocks[$location_id][$variation->id][0]['aisle']= $aisle[$i];
                    $stocks[$location_id][$variation->id][0]['rack']= $rack[$i];
                    $stocks[$location_id][$variation->id][0]['shelf']= $shelf[$i];
                    $stocks[$location_id][$variation->id][0]['bin']= $bin[$i];
                    $stocks[$location_id][$variation->id][0]['purchase_price']= $purchase_prices[$i];
                    $i++;
                }
                if($location_id != 0){
                  return $data = app('App\Http\Controllers\OpeningStockController')->addStock($stocks, $location_id, $product->id);
                }
            }
        }
    }

    /**
     * Update variable type product variation
     *
     * @param $product_id
     * @param $input_variations_edit
     *
     * @return boolean
     */
   public function updateVariableProductVariations($product_id, $input_variations_edit,$location_id)
    {
        $product = Product::find($product_id);
        $stock = [];
        $barcode_new = [];
        $aisle = [];
        $shelf = [];
        $rack = [];
        $bin = [];
        //Update product variations
        $product_variation_ids = [];
        $variations_ids = [];

        foreach ($input_variations_edit as $key => $value) {
            $product_variation_ids[] = $key;

            $product_variation = ProductVariation::find($key);
            $product_variation->name = $value['name'];
            $product_variation->save();

            //Update existing variations
            if (!empty($value['variations_edit'])) {
                foreach ($value['variations_edit'] as $k => $v) {
                    $data = [
                        'name' => $v['value'],
                        'default_purchase_price' => $this->num_uf($v['default_purchase_price']),
                        'dpp_inc_tax' => $this->num_uf($v['dpp_inc_tax']),
                        'profit_percent' => $this->num_uf($v['profit_percent']),
                        'default_sell_price' => $this->num_uf($v['default_sell_price']),
                        'sell_price_inc_tax' => $this->num_uf($v['sell_price_inc_tax']),
                    ];
                    if (!empty($v['sub_sku'])) {
                        $data['sub_sku'] = $v['sub_sku'];
                    }
                    $variation = Variation::where('id', $k)
                            ->where('product_variation_id', $key)
                            ->first();

                    $variation->update($data);

                    Media::uploadMedia($product->business_id, $variation, request(), 'edit_variation_images_' . $key . '_' . $k);

                    $variations_ids[] = $k;
                    $stocks[$location_id][$variation->id][0]['quantity']= $v['on_hand'];
                    $stocks[$location_id][$variation->id][0]['barcode']= $v['barcode'];
                    $stocks[$location_id][$variation->id][0]['aisle']= $v['aisle'];
                    $stocks[$location_id][$variation->id][0]['rack']= $v['rack'];
                    $stocks[$location_id][$variation->id][0]['shelf']= $v['shelf'];
                    $stocks[$location_id][$variation->id][0]['bin']= $v['bin'];
                    $stocks[$location_id][$variation->id][0]['purchase_price']= $v['default_purchase_price'];
                }
                if($location_id != 0){
                  $data = app('App\Http\Controllers\OpeningStockController')->addStock($stocks, $location_id, $product->id);
                }
            }

            //Add new variations
            if (!empty($value['variations'])) {
                $variation_data = [];
                $c = Variation::withTrashed()
                                ->where('product_id', $product->id)
                                ->count()+1;
                $media = [];
                foreach ($value['variations'] as $k => $v) {
                    $sub_sku = empty($v['sub_sku'])? $this->generateSubSku($product->sku, $c, $product->barcode_type) :$v['sub_sku'];

                    $variation_value_name = !empty($v['value'])? $v['value'] : null;
                    $variation_value_id = null;

                    if (!empty($product_variation->variation_template_id)) {
                        $variation_value =  VariationValueTemplate::where('variation_template_id', $product_variation->variation_template_id)
                                ->whereRaw('LOWER(name)="' . $v['value'] . '"')
                                ->first();
                        if (empty($variation_value)) {
                            $variation_value =  VariationValueTemplate::create([
                                'name' => $v['value'],
                                'variation_template_id' => $product_variation->variation_template_id
                            ]);
                        }

                        $variation_value_id = $variation_value->id;
                    }

                    $variation_data[] = [
                      'name' => $variation_value_name,
                      'variation_value_id' => $variation_value_id,
                      'product_id' => $product->id,
                      'sub_sku' => $sub_sku,
                      'default_purchase_price' => $this->num_uf($v['default_purchase_price']),
                      'dpp_inc_tax' => $this->num_uf($v['dpp_inc_tax']),
                      'profit_percent' => $this->num_uf($v['profit_percent']),
                      'default_sell_price' => $this->num_uf($v['default_sell_price']),
                      'sell_price_inc_tax' => $this->num_uf($v['sell_price_inc_tax'])
                    ];
                    $c++;
                    $media[] = 'variation_images_' . $key . '_' . $k;
                    $stock[] = $v['on_hand'];
                    $barcode_new[] = $v['barcode'];
                    $aisle[] = $v['aisle'];
                    $rack[] = $v['rack'];
                    $shelf[] = $v['shelf'];
                    $bin[] = $v['bin'];
                }
                $new_variations = $product_variation->variations()->createMany($variation_data);

                $i = 0;
                foreach ($new_variations as $new_variation) {
                    $variations_ids[] = $new_variation->id;
                    Media::uploadMedia($product->business_id, $new_variation, request(), $media[$i]);

                    $purchase_price = $v['default_purchase_price'];
                    $stocks[$location_id][$new_variation->id][0]['quantity']= $stock[$i];
                    $stocks[$location_id][$new_variation->id][0]['barcode']= $barcode_new[$i];
                    $stocks[$location_id][$new_variation->id][0]['shelf']= $shelf[$i];
                    $stocks[$location_id][$new_variation->id][0]['aisle']= $aisle[$i];
                    $stocks[$location_id][$new_variation->id][0]['rack']= $rack[$i];
                    $stocks[$location_id][$new_variation->id][0]['bin']= $bin[$i];
                    $stocks[$location_id][$new_variation->id][0]['purchase_price']= $purchase_price;
                    $i++;
                }
                if($location_id != 0){
                  $data = app('App\Http\Controllers\OpeningStockController')->addStock($stocks, $location_id, $product->id);
                }
            }
        }

        //Check if purchase or sell exist for the deletable variations
        $count_purchase = PurchaseLine::join(
            'transactions as T',
            'purchase_lines.transaction_id',
            '=',
            'T.id'
            )
              ->where('T.type', 'purchase')
              ->where('T.status', 'received')
              ->where('T.business_id', $product->business_id)
              ->where('purchase_lines.product_id', $product->id)
              ->whereNotIn('purchase_lines.variation_id', $variations_ids)
              ->count();

        $count_sell = TransactionSellLine::join(
            'transactions as T',
            'transaction_sell_lines.transaction_id',
            '=',
            'T.id'
            )
              ->where('T.type', 'sell')
              ->where('T.status', 'final')
              ->where('T.business_id', $product->business_id)
              ->where('transaction_sell_lines.product_id', $product->id)
              ->whereNotIn('transaction_sell_lines.variation_id', $variations_ids)
              ->count();

        $is_variation_delatable = $count_purchase > 0 || $count_sell > 0? false : true;

        if ($is_variation_delatable) {
            Variation::whereNotIn('id', $variations_ids)
                ->where('product_variation_id', $key)
                ->delete();
        } else {
            throw new \Exception(__('lang_v1.purchase_already_exist'));
        }

        ProductVariation::where('product_id', $product_id)
                ->whereNotIn('id', $product_variation_ids)
                ->delete();
    }

    /**
     * Checks if products has manage stock enabled then Updates quantity for product and its
     * variations
     *
     * @param $location_id
     * @param $product_id
     * @param $variation_id
     * @param $new_quantity
     * @param $old_quantity = 0
     * @param $number_format = null
     * @param $uf_data = true, if false it will accept numbers in database format
     * @param $transaction -- new stockadjustment (new Adjust)
     *
     * @return boolean
     */
    public function updateProductQuantity($location_id, $product_id, $variation_id, $new_quantity, $old_quantity = 0, $number_format = null, $uf_data = true, $transaction = null)
    {
        if ($uf_data) {
            $qty_difference = $this->num_uf($new_quantity, $number_format) - $this->num_uf($old_quantity, $number_format);
        } else {
            $qty_difference = $new_quantity - $old_quantity;
        }

        $product = Product::find($product_id);

        //Check if stock is enabled or not.
        if ($product->enable_stock == 1 && $qty_difference != 0) {
            $variation = Variation::where('id', $variation_id)
                            ->where('product_id', $product_id)
                            ->first();

            //Add quantity in VariationLocationDetails
            $variation_location_d = VariationLocationDetails::where('variation_id', $variation->id)
                          ->where('product_id', $product_id)
                          ->where('product_variation_id', $variation->product_variation_id)
                          ->where('location_id', $location_id)
                          ->first();

            if (empty($variation_location_d)) {
                $variation_location_d = new VariationLocationDetails();
                $variation_location_d->variation_id = $variation->id;
                $variation_location_d->product_id = $product_id;
                $variation_location_d->location_id = $location_id;
                $variation_location_d->product_variation_id = $variation->product_variation_id;
                $variation_location_d->qty_available = 0;
            }

            if($transaction)
            {
                $variation_location_d->qty_available -= $this->num_uf($new_quantity, $number_format);
            }else{
                $variation_location_d->qty_available += $qty_difference;
            }
            $variation_location_d->save();
        }

        return true;
    }

    /**
     * Checks if products has manage stock enabled then Decrease quantity for product and its variations
     *
     * @param $product_id
     * @param $variation_id
     * @param $location_id
     * @param $new_quantity
     * @param $old_quantity = 0
     *
     * @return boolean
     */
    public function decreaseProductQuantity($product_id, $variation_id, $location_id, $new_quantity, $old_quantity = 0, $adjustment_type = '')
    {
        $qty_difference = $new_quantity - $old_quantity;
        $product = Product::find($product_id);
        //Check if stock is enabled or not.
        if ($product->enable_stock == 1) {
            //Decrement Quantity in variations location table
            $details = VariationLocationDetails::where('variation_id', $variation_id)
                ->where('product_id', $product_id)
                ->where('location_id', $location_id)
                ->first();
            //If location details not exists create new one
            if (empty($details)) {
                $variation = Variation::find($variation_id);
                $details = VariationLocationDetails::create([
                            'product_id' => $product_id,
                            'location_id' => $location_id,
                            'variation_id' => $variation_id,
                            'product_variation_id' => $variation->product_variation_id,
                            'qty_available' => 0
                          ]);
            }
            if($adjustment_type == 'normal'){
                $details->qty_available = $qty_difference;
            }elseif($adjustment_type == 'abnormal'){
                $details->increment('qty_available', $qty_difference);
            }else{
                $details->decrement('qty_available', $qty_difference);
            }
            $details->save();
        }

        return true;
    }

    /**
     * Decrease the product quantity of combo sub-products
     *
     * @param $combo_details
     * @param $location_id
     *
     * @return void
     */
    public function decreaseProductQuantityCombo($combo_details, $location_id)
    {
        //product_id = child product id
        //variation id is child product variation id
        foreach ($combo_details as $details) {
            $this->decreaseProductQuantity(
                $details['product_id'],
                $details['variation_id'],
                $location_id,
                $details['quantity']
            );
        }
    }

    /**
     * Get all details for a product from its variation id
     *
     * @param int $variation_id
     * @param int $business_id
     * @param int $location_id
     * @param bool $check_qty (If false qty_available is not checked)
     *
     * @return array
     */
    public function getDetailsFromVariation($variation_id, $business_id, $location_id = null, $check_qty = true)
    {
        $query = Variation::join('products AS p', 'variations.product_id', '=', 'p.id')
                ->join('product_variations AS pv', 'variations.product_variation_id', '=', 'pv.id')
                ->leftjoin('variation_location_details AS vld', 'variations.id', '=', 'vld.variation_id')
                ->leftjoin('units', 'p.unit_id', '=', 'units.id')
                ->leftjoin('tax_rates', 'p.sub_category_id', '=', 'tax_rates.sub_category')
                ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                ->leftjoin('categories as sub_cat', 'p.sub_category_id', '=', 'sub_cat.id')
                ->leftjoin('transaction_sell_lines as tsl', 'p.id', '=', 'tsl.product_id')
                ->leftjoin('brands', function ($join) {
                    $join->on('p.brand_id', '=', 'brands.id')
                        ->whereNull('brands.deleted_at');
                })
                ->where('p.business_id', $business_id)
                ->where('variations.id', $variation_id);

        //Add condition for check of quantity. (if stock is not enabled or qty_available > 0)
        // if ($check_qty) {
        //     $query->where(function ($query) use ($location_id) {
        //         $query->where('p.enable_stock', '!=', 1)
        //             ->orWhere('vld.qty_available', '>', 0);
        //     });
        // }

        if (!empty($location_id) && $check_qty) {
            //Check for enable stock, if enabled check for location id.
            $query->where(function ($query) use ($location_id) {
                $query->where('p.enable_stock', '!=', 1)
                            ->orWhere('vld.location_id', $location_id);
            });
        }

        $product = $query->select(
            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name,
                    ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
            'p.id as product_id',
            'p.not_for_selling as not_for_selling',
            'p.out_of_stock as out_of_stock',
            'p.brand_id',
            'p.category_id',
            'p.tax as tax_id',
            'p.enable_stock',
            'p.product_description',
            'p.enable_sr_no',
            'p.qty_box',
            'p.ml',
            'p.sales_price as web_sale_price',
            'p.srp',
            'p.updated_at',
            'tax_rates.taxvalue',
            'tax_rates.tax',
            'tax_rates.tax_percent',
            'tax_rates.city_tax_value',
            'p.type as product_type',
            'p.main_image as product_image',
            'p.name as product_actual_name',
            'p.warranty_id',
            'p.category_id',
            'p.sub_category_id',
            'categories.name as catName',
            'sub_cat.name as subCatName',
            'pv.name as product_variation_name',
            'pv.is_dummy as is_dummy',
            'variations.name as variation_name',
            'variations.sub_sku',
            'p.barcode_type',
            'p.aisle as A',
            'p.rack as R',
            'p.shelf as S',
            'p.bin as B',
            'p.sku as b_code',
            'p.item_code as icode',
            'p.sku2 as b_code2',
            'p.sku3 as b_code3',
            'vld.qty_available',
            'variations.default_purchase_price',
            'variations.default_sell_price',
            'variations.sell_price_inc_tax',
            'variations.id as variation_id',
            'variations.combo_variations',  //Used in combo products
            'units.short_name as unit',
            'units.id as unit_id',
            'units.allow_decimal as unit_allow_decimal',
            'brands.name as brand',
            'tsl.gar_box_return_qty',
            'tsl.gar_piece_return_qty',
            'variations.sell_updated_at as last_variation_update',
            'variations.t1_updated_at',
            'variations.t2_updated_at',
            'variations.t3_updated_at',
            DB::raw("(SELECT purchase_price_inc_tax FROM purchase_lines WHERE
                        variation_id=variations.id ORDER BY id DESC LIMIT 1) as last_purchased_price")
        )
        ->firstOrFail();

        if ($product->product_type == 'combo') {
            if ($check_qty) {
                $product->qty_available = $this->calculateComboQuantity($location_id, $product->combo_variations);
            }

            $product->combo_products = $this->calculateComboDetails($location_id, $product->combo_variations);
        }

        return $product;
    }

    public function getDetailsFromVariationForReturnConsignment($variation_id, $business_id, $location_id = null, $check_qty = true)
    {
        $query = Variation::join('products AS p', 'variations.product_id', '=', 'p.id')
                ->join('product_variations AS pv', 'variations.product_variation_id', '=', 'pv.id')
                ->leftjoin('variation_location_details AS vld', 'variations.id', '=', 'vld.variation_id')
                ->leftjoin('units', 'p.unit_id', '=', 'units.id')
                ->leftjoin('tax_rates', 'p.sub_category_id', '=', 'tax_rates.sub_category')
                ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                ->leftjoin('categories as sub_cat', 'p.sub_category_id', '=', 'sub_cat.id')
                ->leftjoin('transaction_sell_lines as tsl', 'p.id', '=', 'tsl.product_id')
                ->leftjoin('brands', function ($join) {
                    $join->on('p.brand_id', '=', 'brands.id')
                        ->whereNull('brands.deleted_at');
                })
                ->where('p.business_id', $business_id)
                ->where('variations.id', $variation_id);

        //Add condition for check of quantity. (if stock is not enabled or qty_available > 0)
        // if ($check_qty) {
        //     $query->where(function ($query) use ($location_id) {
        //         $query->where('p.enable_stock', '!=', 1)
        //             ->orWhere('vld.qty_available', '>', 0);
        //     });
        // }

        if (!empty($location_id) && $check_qty) {
            //Check for enable stock, if enabled check for location id.
            $query->where(function ($query) use ($location_id) {
                $query->where('p.enable_stock', '!=', 1)
                            ->orWhere('vld.location_id', $location_id);
            });
        }

        $product = $query->select(
            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name,
                    ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
            'p.id as product_id',
            'p.not_for_selling as not_for_selling',
            'p.out_of_stock as out_of_stock',
            'p.brand_id',
            'p.category_id',
            'p.tax as tax_id',
            'p.enable_stock',
            'p.product_description',
            'p.enable_sr_no',
            'p.qty_box',
            'p.ml',
            'p.sales_price as web_sale_price',
            'p.srp',
            'p.updated_at',
            'tax_rates.taxvalue',
            'tax_rates.tax',
            'tax_rates.tax_percent',
            'tax_rates.city_tax_value',
            'p.type as product_type',
            'p.main_image as product_image',
            'p.name as product_actual_name',
            'p.warranty_id',
            'p.category_id',
            'p.sub_category_id',
            'categories.name as catName',
            'sub_cat.name as subCatName',
            'pv.name as product_variation_name',
            'pv.is_dummy as is_dummy',
            'variations.name as variation_name',
            'variations.sub_sku',
            'p.barcode_type',
            'p.aisle as A',
            'p.rack as R',
            'p.shelf as S',
            'p.bin as B',
            'p.sku as b_code',
            'p.item_code as icode',
            'p.sku2 as b_code2',
            'p.sku3 as b_code3',
            'vld.qty_available',
            'variations.default_purchase_price',
            'variations.default_sell_price',
            'variations.sell_price_inc_tax',
            'variations.id as variation_id',
            'variations.combo_variations',  //Used in combo products
            'units.short_name as unit',
            'units.id as unit_id',
            'units.allow_decimal as unit_allow_decimal',
            'brands.name as brand',
            'tsl.gar_box_return_qty',
            'tsl.gar_piece_return_qty',
            'variations.sell_updated_at as last_variation_update',
            'variations.t1_updated_at',
            'variations.t2_updated_at',
            'variations.t3_updated_at',
            DB::raw("(SELECT purchase_price_inc_tax FROM purchase_lines WHERE
                        variation_id=variations.id ORDER BY id DESC LIMIT 1) as last_purchased_price")
        )
        ->firstOrFail();

        if ($product->product_type == 'combo') {
            if ($check_qty) {
                $product->qty_available = $this->calculateComboQuantity($location_id, $product->combo_variations);
            }

            $product->combo_products = $this->calculateComboDetails($location_id, $product->combo_variations);
        }

        return $product;
    }

    /**
     * Calculates the quantity of combo products based on
     * the quantity of variation items used.
     *
     * @param int $location_id
     * @param array $combo_variations
     *
     * @return int
     */
    public function calculateComboQuantity($location_id, $combo_variations)
    {

      //get stock of the items and calcuate accordingly.
        $combo_qty = 0;
        foreach ($combo_variations as $key => $value) {
            $vld = VariationLocationDetails::where('variation_id', $value['variation_id'])
              ->where('location_id', $location_id)
              ->first();
            $product = Product::find($vld->product_id);

            $variation_qty = !empty($vld) ? $vld->qty_available : 0;
            $multiplier = $this->getMultiplierOf2Units($product->unit_id, $value['unit_id']);

            if ($key == 0) {
                $combo_qty = ($variation_qty/$multiplier) / $combo_variations[$key]['quantity'];
            } else {
                $combo_qty = min($combo_qty, ($variation_qty/$multiplier) / $combo_variations[$key]['quantity']);
            }
        }

        return floor($combo_qty);
    }

    /**
     * Calculates the quantity of combo products based on
     * the quantity of variation items used.
     *
     * @param int $location_id
     * @param array $combo_variations
     *
     * @return int
     */
    public function calculateComboDetails($location_id, $combo_variations)
    {
        $details = [];

        foreach ($combo_variations as $key => $value) {
            $variation = Variation::with('product')->findOrFail($value['variation_id']);

            $vld = VariationLocationDetails::where('variation_id', $value['variation_id'])
              ->where('location_id', $location_id)
              ->first();

            $variation_qty = !empty($vld) ? $vld->qty_available : 0;
            $multiplier = $this->getMultiplierOf2Units($variation->product->unit_id, $value['unit_id']);

            $details[] = [
              'variation_id' => $value['variation_id'],
              'product_id' => $variation->product_id,
              'qty_required' => $this->num_uf($value['quantity']) * $multiplier
            ];
        }

        return $details;
    }

    /**
     * Calculates the total amount of invoice
     *
     * @param array $products
     * @param int $tax_id
     * @param array $discount['discount_type', 'discount_amount']
     *
     * @return Mixed (false, array)
     */
    public function calculateInvoiceTotal($products, $tax_id, $discount = null, $uf_number = true, $tax_flag = 0)
    {

        if (empty($products)) {
            return false;
        }

        $output = ['total_before_tax' => 0, 'tax' => 0, 'discount' => 0, 'final_total' => 0];
        $totalTax = 0;
        //Sub Total
        foreach ($products as $product) {
            $stateTax=0;
            $cityTax = 0;
            $unit_price_inc_tax = $uf_number ? $this->num_uf($product['unit_price_inc_tax']) : $product['unit_price_inc_tax'];
            $quantity = $uf_number ? $this->num_uf($product['quantity']) : $product['quantity'];

            //$output['total_before_tax'] += $quantity * $unit_price_inc_tax;
            $gar_quantity = (isset($product['gar_box_return_qty']) && $product['gar_box_return_qty']) ? ($uf_number ? $this->num_uf($product['gar_box_return_qty']) : $product['gar_box_return_qty']) : 0;

            $g_piece_quantity = (isset($product['gar_piece_return_qty']) && $product['gar_piece_return_qty']) ? ($uf_number ? $this->num_uf($product['gar_piece_return_qty']) : $product['gar_piece_return_qty']) : 0;

            $gar_piece_return_price = (isset($product['gar_piece_return_price']) && $product['gar_piece_return_price']) ? ($uf_number ? $this->num_uf($product['gar_piece_return_price']) : $product['gar_piece_return_price']) : 0;

            $gar_piece_return_total_price = $g_piece_quantity * $gar_piece_return_price;

            $quantity = $quantity + $gar_quantity;

            $output['total_before_tax'] += ($quantity * $unit_price_inc_tax) + $gar_piece_return_total_price;

            if(isset($product['state_tax'])){
                //$stateTax = $quantity * $product['state_tax'];
                $stateTax = $product['state_tax'];
            }
            if(isset($product['city_tax'])){
                //$cityTax = $quantity * $product['city_tax'];
                $cityTax = $product['city_tax'];
            }

            $totalTax = $totalTax + $stateTax + $cityTax;

            //Add modifier price to total if exists
            if (!empty($product['modifier_price'])) {
                foreach ($product['modifier_price'] as $key => $modifier_price) {
                    $modifier_price = $uf_number ? $this->num_uf($modifier_price) : $modifier_price;
                    $uf_modifier_price = $this->num_uf($modifier_price);
                    $modifier_qty = isset($product['modifier_quantity'][$key]) ? $product['modifier_quantity'][$key] : 0;
                    $modifier_total = $uf_modifier_price * $modifier_qty;
                    $output['total_before_tax'] += $modifier_total;
                }
            }

        }
        $output['totaltax'] = ($tax_flag == 0) ? 0 : $totalTax;
        //Calculate discount
        if (is_array($discount)) {
            $discount_amount = $uf_number ? $this->num_uf($discount['discount_amount']) : $discount['discount_amount'];
            if ($discount['discount_type'] == 'fixed') {
                $output['discount'] = $discount_amount;
            } else {
                $output['discount'] = ($discount_amount/100)*$output['total_before_tax'];
            }
        }

        //Tax
        $output['tax'] = 0;
        if (!empty($tax_id)) {
            $tax_details = TaxRate::find($tax_id);
            if (!empty($tax_details)) {
                $output['tax_id'] = $tax_id;
                $output['tax'] = ($tax_details->amount/100) * ($output['total_before_tax'] - $output['discount']);
            }
        }

        //Calculate total
        $output['final_total'] = ((int)$tax_flag == 0) ? $output['total_before_tax'] + $output['tax'] - $output['discount'] : $output['total_before_tax'] + $output['tax'] + $totalTax - $output['discount'];

        return $output;
    }

    /**
     * Generates product sku
     *
     * @param string $string
     *
     * @return generated sku (string)
     */
    public function generateProductSku($string)
    {
        $business_id = request()->session()->get('user.business_id');
        $sku_prefix = Business::where('id', $business_id)->value('sku_prefix');

        return $sku_prefix . str_pad($string, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Gives list of trending products
     *
     * @param int $business_id
     * @param array $filters
     *
     * @return Obj
     */
    public function getTrendingProducts($business_id, $filters = [])
    {
        $query = Transaction::join(
            'transaction_sell_lines as tsl',
            'transactions.id',
            '=',
            'tsl.transaction_id'
        )
                    ->join('products as p', 'tsl.product_id', '=', 'p.id')
                    ->leftjoin('units as u', 'u.id', '=', 'p.unit_id')
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.status', 'final');

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }
        if (!empty($filters['location_id'])) {
            $query->where('transactions.location_id', $filters['location_id']);
        }
        if (!empty($filters['category'])) {
            $query->where('p.category_id', $filters['category']);
        }
        if (!empty($filters['sub_category'])) {
            $query->where('p.sub_category_id', $filters['sub_category']);
        }
        if (!empty($filters['brand'])) {
            $query->where('p.brand_id', $filters['brand']);
        }
        if (!empty($filters['unit'])) {
            $query->where('p.unit_id', $filters['unit']);
        }
        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        } else {
            $query->limit(5);
        }

        if (!empty($filters['product_type'])) {
            $query->where('p.type', $filters['product_type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween(DB::raw('date(transaction_date)'), [$filters['start_date'],
                $filters['end_date']]);
        }

        // $sell_return_query = "(SELECT SUM(TPL.quantity) FROM transactions AS T JOIN purchase_lines AS TPL ON T.id=TPL.transaction_id WHERE TPL.product_id=tsl.product_id AND T.type='sell_return'";
        // if ($permitted_locations != 'all') {
        //     $sell_return_query .= ' AND T.location_id IN ('
        //      . implode(',', $permitted_locations) . ') ';
        // }
        // if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        //     $sell_return_query .= ' AND date(T.transaction_date) BETWEEN \'' . $filters['start_date'] . '\' AND \'' . $filters['end_date'] . '\'';
        // }
        // $sell_return_query .= ')';

        $products = $query->select(
            DB::raw("(SUM(tsl.quantity) - COALESCE(SUM(tsl.quantity_returned), 0)) as total_unit_sold"),
            'p.name as product',
            'u.short_name as unit'
        )->whereNull('tsl.parent_sell_line_id')
                        ->groupBy('tsl.product_id')
                        ->orderBy('total_unit_sold', 'desc')
                        ->get();
        return $products;
    }

    /**
     * Gives list of products based on products id and variation id
     *
     * @param int $business_id
     * @param int $product_id
     * @param int $variation_id = null
     *
     * @return Obj
     */
    public function getDetailsFromProduct($business_id, $product_id, $variation_id = null)
    {
        $product = Product::leftjoin('variations as v', 'products.id', '=', 'v.product_id')
                        ->whereNull('v.deleted_at')
                        ->where('products.business_id', $business_id);

        if (!is_null($variation_id) && $variation_id !== '0') {
            $product->where('v.id', $variation_id);
        }

        $product->where('products.id', $product_id);

        $products = $product->select(
            'products.id as product_id',
            'products.name as product_name',
            'products.sku as sku',
            'products.item_code as item_code',
            'v.id as variation_id',
            'v.name as variation_name'
        )
                    ->get();

        return $products;
    }

    /**
     * F => D (Previous product Increase)
     * D => F (All product decrease)
     * F => F (Newly added product drerease)
     *
     * @param  object $transaction_before
     * @param  object  $transaction
     * @param  array  $input
     *
     * @return void
     */
    public function adjustProductStockForInvoice($status_before, $transaction, $input, $uf_data = true)
    {
        if ($status_before == 'final' && $transaction->status == 'draft') {
            foreach ($input['products'] as $product) {
                if (!empty($product['transaction_sell_lines_id'])) {
                    $this->updateProductQuantity($input['location_id'], $product['product_id'], $product['variation_id'], $product['quantity'], 0, null, false);

                    //Adjust quantity for combo items.
                    if (isset($product['product_type']) && $product['product_type'] == 'combo') {
                        //Giving quantity in minus will increase the qty
                        foreach ($product['combo'] as $value) {
                            $this->updateProductQuantity($input['location_id'], $value['product_id'], $value['variation_id'], $value['quantity'], 0, null, false);
                        }

                        // $this->updateEditedSellLineCombo($product['combo'], $input['location_id']);
                    }
                }
            }
        } elseif ($status_before == 'draft' && $transaction->status == 'final') {
            foreach ($input['products'] as $product) {
                $uf_quantity = $uf_data ? $this->num_uf($product['quantity']) : $product['quantity'];

                $this->decreaseProductQuantity(
                    $product['product_id'],
                    $product['variation_id'],
                    $input['location_id'],
                    $uf_quantity
                );

                //Adjust quantity for combo items.
                if (isset($product['product_type']) && $product['product_type'] == 'combo') {
                    $this->decreaseProductQuantityCombo($product['combo'], $input['location_id']);

                    //$this->decreaseProductQuantityCombo($product['variation_id'], $input['location_id'], $uf_quantity);
                }
            }
        } elseif ($status_before == 'final' && $transaction->status == 'final') {
            foreach ($input['products'] as $product) {
                if (empty($product['transaction_sell_lines_id'])) {
                    $uf_quantity = $uf_data ? $this->num_uf($product['quantity']) : $product['quantity'];
                    $this->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $input['location_id'],
                        $uf_quantity
                    );

                    //Adjust quantity for combo items.
                    if (isset($product['product_type']) && $product['product_type'] == 'combo') {
                        $this->decreaseProductQuantityCombo($product['combo'], $input['location_id']);

                        //$this->decreaseProductQuantityCombo($product['variation_id'], $input['location_id'], $uf_quantity);
                    }
                }
            }
        }
    }

    /**
     * Updates variation from purchase screen
     *
     * @param array $variation_data
     *
     * @return void
     */
    public function updateProductFromPurchase($variation_data, $data = Null)
    {
        $variation_details = Variation::where('id', $variation_data['variation_id'])
                                        ->with(['product', 'product.product_tax'])
                                        ->first();
        $tax_rate = 0;
        if (!empty($variation_details->product->product_tax->amount)) {
            $tax_rate = $variation_details->product->product_tax->amount;
        }

        if (!isset($variation_data['sell_price_inc_tax'])) {
            $variation_data['sell_price_inc_tax'] = $variation_details->sell_price_inc_tax;
        }

        if (($variation_details->default_purchase_price != $variation_data['pp_without_discount']) ||
            ($variation_details->sell_price_inc_tax != $variation_data['sell_price_inc_tax'])
            ) {

            if($data)
            {
                //Set default purchase price exc. tax
                $variation_details->default_purchase_price = $variation_data['pp_without_discount'];

                //Set default purchase price inc. tax
                $variation_details->dpp_inc_tax = $this->calc_percentage($variation_details->default_purchase_price, $tax_rate, $variation_details->default_purchase_price);
            }

            //Set default sell price inc. tax
            $variation_details->sell_price_inc_tax = $variation_data['sell_price_inc_tax'];

            //set sell price inc. tax
            $variation_details->default_sell_price = $this->calc_percentage_base($variation_details->sell_price_inc_tax, $tax_rate);

            //set profit margin
            $variation_details->profit_percent = $this->get_percent($variation_details->default_purchase_price, $variation_details->default_sell_price);

            $variation_details->save();
        }
    }

    /**
     * Generated SKU based on the barcode type.
     *
     * @param string $sku
     * @param string $c
     * @param string $barcode_type
     *
     * @return void
     */
    public function generateSubSku($sku, $c, $barcode_type)
    {
        $sub_sku = $sku . $c;

        if (in_array($barcode_type, ['C128', 'C39'])) {
            $sub_sku = $sku . '-' . $c;
        }

        return $sub_sku;
    }

    /**
     * Add rack details.
     *
     * @param int $business_id
     * @param int $product_id
     * @param array $product_racks
     * @param array $product_racks
     *
     * @return void
     */
    public function addRackDetails($business_id, $product_id, $product_racks)
    {
        if (!empty($product_racks)) {
            $data = [];
            foreach ($product_racks as $location_id => $detail) {
                $data[] = ['business_id' => $business_id,
                        'location_id' => $location_id,
                        'product_id' => $product_id,
                        'rack' => !empty($detail['rack']) ? $detail['rack'] : null,
                        'row' => !empty($detail['row']) ? $detail['row'] : null,
                        'position' => !empty($detail['position']) ? $detail['position'] : null,
                        'created_at' => \Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon::now()->toDateTimeString()
                    ];
            }

            ProductRack::insert($data);
        }
    }

    /**
     * Get rack details.
     *
     * @param int $business_id
     * @param int $product_id
     *
     * @return void
     */
    public function getRackDetails($business_id, $product_id, $get_location = false)
    {
        $query = ProductRack::where('product_racks.business_id', $business_id)
                    ->where('product_id', $product_id);

        if ($get_location) {
            $racks = $query->join('business_locations AS BL', 'product_racks.location_id', '=', 'BL.id')
                ->select(['product_racks.rack',
                        'product_racks.row',
                        'product_racks.position',
                        'BL.name'])
                ->get();
        } else {
            $racks = collect($query->select(['rack', 'row', 'position', 'location_id'])->get());

            $racks = $racks->mapWithKeys(function ($item, $key) {
                return [$item['location_id'] => $item->toArray()];
            })->toArray();
        }

        return $racks;
    }

    /**
     * Update rack details.
     *
     * @param int $business_id
     * @param int $product_id
     * @param array $product_racks
     *
     * @return void
     */
    public function updateRackDetails($business_id, $product_id, $product_racks)
    {
        if (!empty($product_racks)) {
            foreach ($product_racks as $location_id => $details) {
                ProductRack::where('business_id', $business_id)
                    ->where('product_id', $product_id)
                    ->where('location_id', $location_id)
                    ->update(['rack' => !empty($details['rack']) ? $details['rack'] : null,
                            'row' => !empty($details['row']) ? $details['row'] : null,
                            'position' => !empty($details['position']) ? $details['position'] : null
                        ]);
            }
        }
    }

    /**
     * Retrieves selling price group price for a product variation.
     *
     * @param int $variation_id
     * @param int $price_group_id
     * @param int $tax_id
     *
     * @return decimal
     */
    public function getVariationGroupPrice($variation_id, $price_group_id, $tax_id)
    {
        $price_inc_tax =
        VariationGroupPrice::where('variation_id', $variation_id)
                        ->where('price_group_id', $price_group_id)
                        ->value('price_inc_tax');

        $price_exc_tax = $price_inc_tax;
        if (!empty($price_inc_tax) && !empty($tax_id)) {
            $tax_amount = TaxRate::where('id', $tax_id)->value('amount');
            $price_exc_tax = $this->calc_percentage_base($price_inc_tax, $tax_amount);
        }
        return [
            'price_inc_tax' => $price_inc_tax,
            'price_exc_tax' => $price_exc_tax
        ];
    }

    /**
     * Creates new variation if not exists.
     *
     * @param int $business_id
     * @param string $name
     *
     * @return obj
     */
    public function createOrNewVariation($business_id, $name)
    {
        $variation = VariationTemplate::where('business_id', $business_id)
                                    ->where('name', 'like', $name)
                                    ->with(['values'])
                                    ->first();

        if (empty($variation)) {
            $variation = VariationTemplate::create([
            'business_id' => $business_id,
            'name' => $name
            ]);
        }
        return $variation;
    }

    /**
     * Adds opening stock to a single product.
     *
     * @param int $business_id
     * @param obj $product
     * @param array $input
     * @param obj $transaction_date
     * @param int $user_id
     *
     * @return void
     */
    public function addSingleProductOpeningStock($business_id, $product, $input, $transaction_date, $user_id)
    {
        $locations = BusinessLocation::forDropdown($business_id)->toArray();

        $tax_percent = !empty($product->product_tax->amount) ? $product->product_tax->amount : 0;
        $tax_id = !empty($product->product_tax->id) ? $product->product_tax->id : null;

        foreach ($input as $key => $value) {
            $location_id = $key;
            $purchase_total = 0;
            //Check if valid location
            if (array_key_exists($location_id, $locations)) {
                $purchase_lines = [];

                $purchase_price = $this->num_uf(trim($value['purchase_price']));
                $item_tax = $this->calc_percentage($purchase_price, $tax_percent);
                $purchase_price_inc_tax = $purchase_price + $item_tax;
                $qty = $this->num_uf(trim($value['quantity']));

                $exp_date = null;
                if (!empty($value['exp_date'])) {
                    $exp_date = \Carbon::createFromFormat('d-m-Y', $value['exp_date'])->format('Y-m-d');
                }

                $lot_number = null;
                if (!empty($value['lot_number'])) {
                    $lot_number = $value['lot_number'];
                }

                if ($qty > 0) {
                    $qty_formated = $this->num_f($qty);
                    //Calculate transaction total
                    $purchase_total += ($purchase_price_inc_tax * $qty);
                    $variation_id = $product->variations->first()->id;

                    $purchase_line = new PurchaseLine();
                    $purchase_line->product_id = $product->id;
                    $purchase_line->variation_id = $variation_id;
                    $purchase_line->item_tax = $item_tax;
                    $purchase_line->tax_id = $tax_id;
                    $purchase_line->quantity = $qty;
                    $purchase_line->pp_without_discount = $purchase_price;
                    $purchase_line->purchase_price = $purchase_price;
                    $purchase_line->purchase_price_inc_tax = $purchase_price_inc_tax;
                    $purchase_line->exp_date = $exp_date;
                    $purchase_line->lot_number = $lot_number;
                    $purchase_lines[] = $purchase_line;

                    $this->updateProductQuantity($location_id, $product->id, $variation_id, $qty_formated);
                }

                //create transaction & purchase lines
                if (!empty($purchase_lines)) {
                    $transaction = Transaction::create(
                        [
                  'type' => 'opening_stock',
                  'opening_stock_product_id' => $product->id,
                  'status' => 'received',
                  'business_id' => $business_id,
                  'transaction_date' => $transaction_date,
                  'total_before_tax' => $purchase_total,
                  'location_id' => $location_id,
                  'final_total' => $purchase_total,
                  'payment_status' => 'paid',
                  'created_by' => $user_id
                ]
              );
                    $transaction->purchase_lines()->saveMany($purchase_lines);
                }
            }
        }
    }

    /**
     * Add/Edit transaction purchase lines
     *
     * @param object $transaction
     * @param array $input_data
     * @param array $currency_details
     * @param boolean $enable_product_editing
     * @param string $before_status = null
     *
     * @return array
     */
    public function createOrUpdatePurchaseLines($transaction, $input_data, $currency_details, $enable_product_editing, $before_status = null)
    {
        $updated_purchase_lines = [];
        $updated_purchase_line_ids = [0];
        $exchange_rate = !empty($transaction->exchange_rate) ? $transaction->exchange_rate : 1;

        foreach ($input_data as $data) {
            $multiplier = 1;
            if (isset($data['sub_unit_id']) && $data['sub_unit_id'] == $data['product_unit_id']) {
                unset($data['sub_unit_id']);
            }

            if (!empty($data['sub_unit_id'])) {
                $unit = Unit::find($data['sub_unit_id']);
                $multiplier = !empty($unit->base_unit_multiplier) ? $unit->base_unit_multiplier : 1;
            }
            $new_quantity = $this->num_uf($data['quantity']) * $multiplier;

            $new_quantity_f = $this->num_f($new_quantity);
            //update existing purchase line
            if (isset($data['purchase_line_id'])) {
                $purchase_line = PurchaseLine::findOrFail($data['purchase_line_id']);
                $updated_purchase_line_ids[] = $purchase_line->id;
                $old_qty = $this->num_f($purchase_line->quantity);

                $this->updateProductStock($before_status, $transaction, $data['product_id'], $data['variation_id'], $new_quantity, $purchase_line->quantity, $currency_details);
            } else {
                //create newly added purchase lines
                $purchase_line = new PurchaseLine();
                $purchase_line->product_id = $data['product_id'];
                $purchase_line->variation_id = $data['variation_id'];

                //Increase quantity only if status is received
                if ($transaction->status == 'received') {
                    $this->updateProductQuantity($transaction->location_id, $data['product_id'], $data['variation_id'], $new_quantity_f, 0, $currency_details);
                }
            }
            if(isset($data['update_cost']) )
            {
                $purchase_line->update_cost = 1;
            }

            $purchase_line->quantity = $new_quantity;
            $purchase_line->pp_without_discount = ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
            $purchase_line->discount_percent = isset($data['discount_percent']) ? $this->num_uf($data['discount_percent'], $currency_details) : 0;
            $purchase_line->purchase_price = isset($data['purchase_price']) ? ( ($this->num_uf($data['purchase_price'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            // $purchase_line->purchase_price_inc_tax = isset($data['purchase_price_inc_tax']) ? (($this->num_uf($data['purchase_price_inc_tax'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            // $purchase_line->purchase_price_inc_tax =  ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
            $purchase_line->purchase_price_inc_tax =  isset($data['purchase_price']) ? (($this->num_uf($data['purchase_price'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            $purchase_line->item_tax = isset($data['item_tax']) ?  (($this->num_uf($data['item_tax'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            $purchase_line->tax_id = $data['purchase_line_tax_id'];
            $purchase_line->lot_number = !empty($data['lot_number']) ? $data['lot_number'] : null;
            $purchase_line->mfg_date = !empty($data['mfg_date']) ? $this->uf_date($data['mfg_date']) : null;
            $purchase_line->exp_date = !empty($data['exp_date']) ? $this->uf_date($data['exp_date']) : null;
            $purchase_line->sub_unit_id = !empty($data['sub_unit_id']) ? $data['sub_unit_id'] : null;
            $purchase_line->default_sell_price = !empty($data['default_sell_price']) ? $data['default_sell_price'] : null;

            $updated_purchase_lines[] = $purchase_line;

            //Edit product price
            if ($enable_product_editing == 1) {
                if (isset($data['default_sell_price'])) {
                    $variation_data['sell_price_inc_tax'] = ($this->num_uf($data['default_sell_price'], $currency_details)) / $multiplier;
                }
                $variation_data['pp_without_discount'] = ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
                $variation_data['variation_id'] = $purchase_line->variation_id;
                $variation_data['purchase_price'] = $purchase_line->purchase_price;

                $this->updateProductFromPurchase($variation_data);
            }

            if(isset($data['update_cost']) )
            {
                if (isset($data['default_sell_price'])) {
                    $variation_data['sell_price_inc_tax'] = ($this->num_uf($data['default_sell_price'], $currency_details)) / $multiplier;
                }
                $variation_data['pp_without_discount'] = ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
                $variation_data['variation_id'] = $purchase_line->variation_id;
                $variation_data['purchase_price'] = $purchase_line->purchase_price;
                $this->updateProductFromPurchase($variation_data, 1);
            }
        }

        //unset deleted purchase lines
        $delete_purchase_line_ids = [];
        $delete_purchase_lines = null;
        if (!empty($updated_purchase_line_ids)) {
            $delete_purchase_lines = PurchaseLine::where('transaction_id', $transaction->id)
                    ->whereNotIn('id', $updated_purchase_line_ids)
                    ->get();

            if ($delete_purchase_lines->count()) {
                foreach ($delete_purchase_lines as $delete_purchase_line) {
                    $delete_purchase_line_ids[] = $delete_purchase_line->id;

                    //decrease deleted only if previous status was received
                    if ($before_status == 'received') {
                        $this->decreaseProductQuantity(
                            $delete_purchase_line->product_id,
                            $delete_purchase_line->variation_id,
                            $transaction->location_id,
                            $delete_purchase_line->quantity
                    );
                    }
                }
                //Delete deleted purchase lines
                PurchaseLine::where('transaction_id', $transaction->id)
                        ->whereIn('id', $delete_purchase_line_ids)
                        ->delete();
            }
        }

        //update purchase lines
        if (!empty($updated_purchase_lines)) {
            $transaction->purchase_lines()->saveMany($updated_purchase_lines);
        }

        return $delete_purchase_lines;
    }

    /**
     * Add/Edit transaction po lines
     *
     * @param object $transaction
     * @param array $input_data
     * @param array $currency_details
     * @param boolean $enable_product_editing
     * @param string $before_status = null
     *
     * @return array
     */
    public function createOrUpdatePOLines($transaction, $input_data, $currency_details, $enable_product_editing, $before_status = null)
    {
        $updated_purchase_lines = [];
        $updated_purchase_line_ids = [0];
        $exchange_rate = !empty($transaction->exchange_rate) ? $transaction->exchange_rate : 1;

        foreach ($input_data as $data) {
            $multiplier = 1;
            if (isset($data['sub_unit_id']) && $data['sub_unit_id'] == $data['product_unit_id']) {
                unset($data['sub_unit_id']);
            }

            if (!empty($data['sub_unit_id'])) {
                $unit = Unit::find($data['sub_unit_id']);
                $multiplier = !empty($unit->base_unit_multiplier) ? $unit->base_unit_multiplier : 1;
            }
            $new_quantity = isset($data['quantity']) ? $this->num_uf($data['quantity']) : 0 * $multiplier;

            $new_quantity_f = $this->num_f($new_quantity);
            //update existing purchase line
            if (isset($data['purchase_line_id'])) {
                $purchase_line = PoLine::findOrFail($data['purchase_line_id']);
                $updated_purchase_line_ids[] = $purchase_line->id;
                $old_qty = $this->num_f($purchase_line->quantity);

                // $this->updateProductStock($before_status, $transaction, $data['product_id'], $data['variation_id'], $new_quantity, $purchase_line->quantity, $currency_details);
            } else {
                //create newly added purchase lines
                $purchase_line = new PoLine();
                $purchase_line->product_id = $data['product_id'];
                $purchase_line->variation_id = $data['variation_id'];

                //Increase quantity only if status is received
                if ($transaction->status == 'received') {
                    // $this->updateProductQuantity($transaction->location_id, $data['product_id'], $data['variation_id'], $new_quantity_f, 0, $currency_details);
                }
            }
            if(isset($data['update_cost']) )
            {
                $purchase_line->update_cost = 1;
            }

            $purchase_line->quantity = $new_quantity;
            $purchase_line->pp_without_discount = ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
            $purchase_line->discount_percent = isset($data['discount_percent']) ? $this->num_uf($data['discount_percent'], $currency_details) : 0;
            $purchase_line->purchase_price = isset($data['purchase_price']) ? ( ($this->num_uf($data['purchase_price'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            // $purchase_line->purchase_price_inc_tax = isset($data['purchase_price_inc_tax']) ? (($this->num_uf($data['purchase_price_inc_tax'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            // $purchase_line->purchase_price_inc_tax =  ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
            $purchase_line->purchase_price_inc_tax =  isset($data['purchase_price']) ? (($this->num_uf($data['purchase_price'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            $purchase_line->item_tax = isset($data['item_tax']) ?  (($this->num_uf($data['item_tax'], $currency_details)*$exchange_rate) / $multiplier) : 0;
            $purchase_line->tax_id = $data['purchase_line_tax_id'];
            $purchase_line->lot_number = !empty($data['lot_number']) ? $data['lot_number'] : null;
            $purchase_line->mfg_date = !empty($data['mfg_date']) ? $this->uf_date($data['mfg_date']) : null;
            $purchase_line->exp_date = !empty($data['exp_date']) ? $this->uf_date($data['exp_date']) : null;
            $purchase_line->sub_unit_id = !empty($data['sub_unit_id']) ? $data['sub_unit_id'] : null;
            $purchase_line->default_sell_price = !empty($data['default_sell_price']) ? $data['default_sell_price'] : null;

            $updated_purchase_lines[] = $purchase_line;

            //Edit product price
            if ($enable_product_editing == 1) {
                if (isset($data['default_sell_price'])) {
                    $variation_data['sell_price_inc_tax'] = ($this->num_uf($data['default_sell_price'], $currency_details)) / $multiplier;
                }
                $variation_data['pp_without_discount'] = ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
                $variation_data['variation_id'] = $purchase_line->variation_id;
                $variation_data['purchase_price'] = $purchase_line->purchase_price;

                $this->updateProductFromPurchase($variation_data);
            }

            if(isset($data['update_cost']) )
            {
                if (isset($data['default_sell_price'])) {
                    $variation_data['sell_price_inc_tax'] = ($this->num_uf($data['default_sell_price'], $currency_details)) / $multiplier;
                }
                $variation_data['pp_without_discount'] = ($this->num_uf($data['pp_without_discount'], $currency_details)*$exchange_rate) / $multiplier;
                $variation_data['variation_id'] = $purchase_line->variation_id;
                $variation_data['purchase_price'] = $purchase_line->purchase_price;
                $this->updateProductFromPurchase($variation_data, 1);
            }
        }

        //unset deleted purchase lines
        $delete_purchase_line_ids = [];
        $delete_purchase_lines = null;
        if (!empty($updated_purchase_line_ids)) {
            $delete_purchase_lines = PoLine::where('transaction_id', $transaction->id)
                    ->whereNotIn('id', $updated_purchase_line_ids)
                    ->get();

            if ($delete_purchase_lines->count()) {
                foreach ($delete_purchase_lines as $delete_purchase_line) {
                    $delete_purchase_line_ids[] = $delete_purchase_line->id;

                    //decrease deleted only if previous status was received
                    if ($before_status == 'received') {
                    //     $this->decreaseProductQuantity(
                    //         $delete_purchase_line->product_id,
                    //         $delete_purchase_line->variation_id,
                    //         $transaction->location_id,
                    //         $delete_purchase_line->quantity
                    // );
                    }
                }
                //Delete deleted purchase lines
                PoLine::where('transaction_id', $transaction->id)
                        ->whereIn('id', $delete_purchase_line_ids)
                        ->delete();
            }
        }

        //update purchase lines
        if (!empty($updated_purchase_lines)) {
            $transaction->purchase_lines()->saveMany($updated_purchase_lines);
        }

        return $delete_purchase_lines;
    }
    /**
     * Updates product stock after adding or updating purchase
     *
     * @param string $status_before
     * @param obj $transaction
     * @param integer $product_id
     * @param integer $variation_id
     * @param decimal $new_quantity in database format
     * @param decimal $old_quantity in database format
     * @param array $currency_details
     *
     */
    public function updateProductStock($status_before, $transaction, $product_id, $variation_id, $new_quantity, $old_quantity, $currency_details)
    {
        $new_quantity_f = $this->num_f($new_quantity);
        $old_qty = $this->num_f($old_quantity);
        //Update quantity for existing products
        if ($status_before == 'received' && $transaction->status == 'received') {
            //if status received update existing quantity
            $this->updateProductQuantity($transaction->location_id, $product_id, $variation_id, $new_quantity_f, $old_qty, $currency_details);
        } elseif ($status_before == 'received' && $transaction->status != 'received') {
            //decrease quantity only if status changed from received to not received
            $this->decreaseProductQuantity(
                $product_id,
                $variation_id,
                $transaction->location_id,
                $old_quantity
            );
        } elseif ($status_before != 'received' && $transaction->status == 'received') {
            $this->updateProductQuantity($transaction->location_id, $product_id, $variation_id, $new_quantity_f, 0, $currency_details);
        }
    }

    /**
     * Recalculates purchase line data according to subunit data
     *
     * @param integer $purchase_line
     * @param integer $business_id
     *
     * @return array
     */
    public function changePurchaseLineUnit($purchase_line, $business_id)
    {
        $base_unit = $purchase_line->product->unit;
        $sub_units = $base_unit->sub_units;

        $sub_unit_id = $purchase_line->sub_unit_id;

        $sub_unit = $sub_units->filter(function ($item) use ($sub_unit_id) {
            return $item->id == $sub_unit_id;
        })->first();

        if (!empty($sub_unit)) {
            $multiplier = $sub_unit->base_unit_multiplier;
            $purchase_line->quantity = $purchase_line->quantity / $multiplier;
            $purchase_line->pp_without_discount = $purchase_line->pp_without_discount * $multiplier;
            $purchase_line->purchase_price = $purchase_line->purchase_price * $multiplier;
            $purchase_line->purchase_price_inc_tax = $purchase_line->purchase_price_inc_tax * $multiplier;
            $purchase_line->item_tax = $purchase_line->item_tax * $multiplier;
            $purchase_line->quantity_returned = $purchase_line->quantity_returned / $multiplier;
            $purchase_line->quantity_sold = $purchase_line->quantity_sold / $multiplier;
            $purchase_line->quantity_adjusted = $purchase_line->quantity_adjusted / $multiplier;
        }

        //SubUnits
        $purchase_line->sub_units_options = $this->getSubUnits($business_id, $base_unit->id, false, $purchase_line->product_id);

        return $purchase_line;
    }

    /**
     * Recalculates sell line data according to subunit data
     *
     * @param integer $unit_id
     *
     * @return array
     */
    public function changeSellLineUnit($business_id, $sell_line)
    {
        $unit_details = $this->getSubUnits($business_id, $sell_line->unit_id, false, $sell_line->product_id);

        $sub_unit = null;
        $sub_unit_id = $sell_line->sub_unit_id;
        foreach ($unit_details as $key => $value) {
            if ($key == $sub_unit_id) {
                $sub_unit = $value;
            }
        }

        if (!empty($sub_unit)) {
            $multiplier = $sub_unit['multiplier'];
            $sell_line->quantity_ordered = $sell_line->quantity_ordered / $multiplier;
            $sell_line->item_tax = $sell_line->item_tax * $multiplier;
            $sell_line->default_sell_price = $sell_line->default_sell_price * $multiplier;
            $sell_line->unit_price_before_discount = $sell_line->unit_price_before_discount * $multiplier;
            $sell_line->sell_price_inc_tax = $sell_line->sell_price_inc_tax * $multiplier;
            $sell_line->sub_unit_multiplier = $multiplier;

            $sell_line->unit_details = $unit_details;
        }

        return $sell_line;
    }

    /**
     * Retrieves current stock of a variation for the given location
     *
     * @param int $variation_id, int location_id
     *
     * @return float
     */
    public function getCurrentStock($variation_id, $location_id)
    {
        $current_stock = VariationLocationDetails::where('variation_id', $variation_id)
                                              ->where('location_id', $location_id)
                                              ->value('qty_available');

        if (null == $current_stock) {
            $current_stock = 0;
        }

        return $current_stock;
    }

    /**
     * Adjusts stock over selling with purchases, opening stocks andstock transfers
     * Also maps with respective sells
     *
     * @param obj $transaction
     *
     * @return void
     */
    public function adjustStockOverSelling($transaction)
    {
        if ($transaction->status != 'received') {
            return false;
        }

        foreach ($transaction->purchase_lines as $purchase_line) {
            if ($purchase_line->product->enable_stock == 1) {

        //Available quantity in the purchase line
                $purchase_line_qty_avlbl = $purchase_line->quantity_remaining;

                if ($purchase_line_qty_avlbl <= 0) {
                    continue;
                }

                //update sell line purchase line mapping
                $sell_line_purchase_lines =
        TransactionSellLinesPurchaseLines::where('purchase_line_id', 0)
                ->join('transaction_sell_lines as tsl', 'tsl.id', '=', 'transaction_sell_lines_purchase_lines.sell_line_id')
                ->join('transactions as t', 'tsl.transaction_id', '=', 't.id')
                ->where('t.location_id', $transaction->location_id)
                ->where('tsl.variation_id', $purchase_line->variation_id)
                ->where('tsl.product_id', $purchase_line->product_id)

                ->select('transaction_sell_lines_purchase_lines.*')
                ->get();

                foreach ($sell_line_purchase_lines as $slpl) {
                    if ($purchase_line_qty_avlbl > 0) {
                        if ($slpl->quantity <= $purchase_line_qty_avlbl) {
                            $purchase_line_qty_avlbl -= $slpl->quantity;
                            $slpl->purchase_line_id = $purchase_line->id;
                            $slpl->save();
                            //update purchase line quantity sold
                            $purchase_line->quantity_sold += $slpl->quantity;
                            $purchase_line->save();
                        } else {
                            $diff = $slpl->quantity - $purchase_line_qty_avlbl;
                            $slpl->purchase_line_id = $purchase_line->id;
                            $slpl->quantity = $purchase_line_qty_avlbl;
                            $slpl->save();

                            //update purchase line quantity sold
                            $purchase_line->quantity_sold += $slpl->quantity;
                            $purchase_line->save();

                            TransactionSellLinesPurchaseLines::create([
                'sell_line_id' => $slpl->sell_line_id,
                'purchase_line_id' => 0,
                'quantity' => $diff
              ]);
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds out most relevant descount for the product
     *
     * @param obj $product, int $business_id, int $location_id, bool $is_cg,
     * bool $is_spg
     *
     * @return obj discount
     */
    public function getProductDiscount($product, $business_id, $location_id, $is_cg = false, $is_spg = false, $variation_id = null)
    {
        $now = \Carbon::now()->toDateTimeString();

        //Search if both category and brand matches
        $query1 = Discount::where('business_id', $business_id)
                    ->where('location_id', $location_id)
                    ->where('is_active', 1)
                    ->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->where('brand_id', $product->brand_id)
                    ->where('category_id', $product->category_id)
                    ->orderBy('priority', 'desc')
                    ->latest();
        if ($is_cg) {
            $query1->where('applicable_in_cg', 1);
        }
        if ($is_spg) {
            $query1->where('applicable_in_spg', 1);
        }

        $discount = $query1->first();

        //Search if either category or brand matches
        if (empty($discount)) {
            $query2 = Discount::where('business_id', $business_id)
                    ->where('location_id', $location_id)
                    ->where('is_active', 1)
                    ->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->where(function ($q) use ($product) {
                        $q->whereRaw('(brand_id="' . $product->brand_id .'" AND category_id IS NULL)')
                        ->orWhereRaw('(category_id="' . $product->category_id .'" AND brand_id IS NULL)');
                    })
                    ->orderBy('priority', 'desc');
            if ($is_cg) {
                $query2->where('applicable_in_cg', 1);
            }
            if ($is_spg) {
                $query2->where('applicable_in_spg', 1);
            }
            $discount = $query2->first();
        }

        //Search if variation has discount
        if (!empty($variation_id)) {
          $query3 = Discount::where('business_id', $business_id)
                      ->where('location_id', $location_id)
                      ->where('is_active', 1)
                      ->where('starts_at', '<=', $now)
                      ->where('ends_at', '>=', $now)
                      ->whereHas('variations', function($q) use ($variation_id){
                        $q->where('variation_id', $variation_id);
                      })
                      ->orderBy('priority', 'desc')
                      ->latest();
          if ($is_cg) {
              $query3->where('applicable_in_cg', 1);
          }
          if ($is_spg) {
              $query3->where('applicable_in_spg', 1);
          }
          $discount_by_variation = $query3->first();
          if (!empty($discount_by_variation) && !empty($discount)) {
            $discount = $discount_by_variation->priority >= $discount->priority ? $discount_by_variation : $discount;
          } else if (empty($discount)) {
              $discount = $discount_by_variation;
          }

        }

        if (!empty($discount)) {
            $discount->formated_starts_at = $this->format_date($discount->starts_at->toDateTimeString(), true);
            $discount->formated_ends_at = $this->format_date($discount->ends_at->toDateTimeString(), true);
        }

        return $discount;
    }

    /**
     * Filters product as per the given inputs and return the details.
     *
     * @param string $search_type (like or exact)
     *
     * @return object
     */
    public function filterProduct($business_id, $search_term, $location_id = null, $not_for_selling = null, $price_group_id = null, $product_types = [], $search_fields = [], $check_qty = false, $search_type = 'like'){

        $query = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->active()
                ->whereNull('variations.deleted_at')
                ->leftjoin('units as U', 'products.unit_id', '=', 'U.id')
                ->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'VLD.variation_id');

                        //Include Location
                        if (!empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                //Check null to show products even if no quantity is available in a location.
                                //TODO: Maybe add a settings to show product not available at a location or not.
                                $query->orWhereNull('VLD.location_id');
                            });
                            ;
                        }
                    }
                );

        // if (!is_null($not_for_selling)) {
        //     $query->where('products.not_for_selling', $not_for_selling);
        // }

        if (!empty($price_group_id)) {
            $query->leftjoin(
                'variation_group_prices AS VGP',
                function ($join) use ($price_group_id) {
                    $join->on('variations.id', '=', 'VGP.variation_id')
                        ->where('VGP.price_group_id', '=', $price_group_id);
                }
            );
        }

        $query->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

        if (!empty($product_types)) {
            $query->whereIn('products.type', $product_types);
        }

        if (in_array('lot', $search_fields)) {
            $query->leftjoin('purchase_lines as pl', 'variations.id', '=', 'pl.variation_id');
        }

        //Include search
        if (!empty($search_term)) {

            //Search with like condition
            if($search_type == 'like'){
                $query->where(function ($query) use ($search_term, $search_fields) {

                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('sku', $search_fields)) {
                        if(is_numeric($search_term) && (strlen($search_term) >= 9 && strlen($search_term) <= 14)){
                            // echo "hi1";exit;
                            $query->orWhere('sku', '=', $search_term);
                        }else{
                            // echo "hi2";exit;
                            $query->orWhere('sku', 'like', '%' . $search_term .'%');
                        }
                    }

                    if (in_array('item_code', $search_fields)) {
                        $query->orWhere('item_code', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', 'like', '%' . $search_term .'%');
                    }
                });
            }

            //Search with exact condition
            if($search_type == 'exact'){
                $query->where(function ($query) use ($search_term, $search_fields) {

                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', $search_term);
                    }

                    if (in_array('sku', $search_fields)) {
                        $query->orWhere('sku', $search_term);
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', $search_term);
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', $search_term);
                    }
                });
            }
        }


        //Include check for quantity
        if ($check_qty) {
            $query->where('VLD.qty_available', '>', 0);
        }

        if (!empty($location_id)) {
            $query->ForLocation($location_id);
        }

        $query->select(
                'products.id as product_id',
                'products.name',
                'products.type',
                'products.item_code',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'products.sku as sku',
                'variations.default_sell_price',
                'variations.sell_price_inc_tax as selling_price',
                'variations.sub_sku',
                'U.short_name as unit',
                'variations.dpp_inc_tax',
                'variations.sell_price_inc_tax',
                'products.is_inactive',
                'products.not_for_selling'
            );

        if (!empty($price_group_id)) {
            $query->addSelect('VGP.price_inc_tax as variation_group_price');
        }

        if (in_array('lot', $search_fields)) {
            $query->addSelect('pl.id as purchase_line_id', 'pl.lot_number');
        }

        $query->groupBy('variations.id');
        // return $query->orderBy('VLD.qty_available', 'desc')->get();
        return $query->orderBy('products.name', 'asc')->get();


    }

    public function filterProductForReturns($business_id, $search_term, $location_id = null, $not_for_selling = null, $price_group_id = null, $product_types = [], $search_fields = [], $check_qty = false, $search_type = 'like'){

        $query = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->active()
                ->whereNull('variations.deleted_at')
                ->leftjoin('units as U', 'products.unit_id', '=', 'U.id')
                ->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'VLD.variation_id');

                        //Include Location
                        if (!empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                //Check null to show products even if no quantity is available in a location.
                                //TODO: Maybe add a settings to show product not available at a location or not.
                                $query->orWhereNull('VLD.location_id');
                            });
                            ;
                        }
                    }
                );

        if (!is_null($not_for_selling)) {
            $query->where('products.not_for_selling', $not_for_selling);
        }

        if (!empty($price_group_id)) {
            $query->leftjoin(
                'variation_group_prices AS VGP',
                function ($join) use ($price_group_id) {
                    $join->on('variations.id', '=', 'VGP.variation_id')
                        ->where('VGP.price_group_id', '=', $price_group_id);
                }
            );
        }

        $query->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

        if (!empty($product_types)) {
            $query->whereIn('products.type', $product_types);
        }

        if (in_array('lot', $search_fields)) {
            $query->leftjoin('purchase_lines as pl', 'variations.id', '=', 'pl.variation_id');
        }

        //Include search
        if (!empty($search_term)) {

            //Search with like condition
            if($search_type == 'like'){
                $query->where(function ($query) use ($search_term, $search_fields) {

                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('sku', $search_fields)) {
                        if(is_numeric($search_term) && (strlen($search_term) >= 9 && strlen($search_term) <= 14)){
                            // echo "hi1";exit;
                            $query->orWhere('sku', '=', $search_term);
                        }else{
                            // echo "hi2";exit;
                            $query->orWhere('sku', 'like', '%' . $search_term .'%');
                        }
                    }

                    if (in_array('item_code', $search_fields)) {
                        $query->orWhere('item_code', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', 'like', '%' . $search_term .'%');
                    }
                });
            }

            //Search with exact condition
            if($search_type == 'exact'){
                $query->where(function ($query) use ($search_term, $search_fields) {

                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', $search_term);
                    }

                    if (in_array('sku', $search_fields)) {
                        $query->orWhere('sku', $search_term);
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', $search_term);
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', $search_term);
                    }
                });
            }
        }


        //Include check for quantity
        if ($check_qty) {
            $query->where('VLD.qty_available', '>', 0);
        }

        if (!empty($location_id)) {
            $query->ForLocation($location_id);
        }

        $query->select(
                'products.id as product_id',
                'products.name',
                'products.type',
                'products.item_code',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'products.sku as sku',
                'variations.default_sell_price',
                'variations.sell_price_inc_tax as selling_price',
                'variations.sub_sku',
                'U.short_name as unit',
                'variations.dpp_inc_tax',
                'variations.sell_price_inc_tax',
                'products.is_inactive',
                'products.not_for_selling'
            );

        if (!empty($price_group_id)) {
            $query->addSelect('VGP.price_inc_tax as variation_group_price');
        }

        if (in_array('lot', $search_fields)) {
            $query->addSelect('pl.id as purchase_line_id', 'pl.lot_number');
        }

        $query->groupBy('variations.id');
        // return $query->orderBy('VLD.qty_available', 'desc')->get();
        return $query->orderBy('products.name', 'asc')->get();


    }

    public function filterPosProduct($business_id, $search_term, $location_id = null, $not_for_selling = null, $price_group_id = null, $product_types = [], $search_fields = [], $check_qty = false, $search_type = 'like'){
        $query = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->active()
                ->whereNull('variations.deleted_at')
                ->leftjoin('units as U', 'products.unit_id', '=', 'U.id')
                ->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'VLD.variation_id');

                        //Include Location
                        if (!empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                //Check null to show products even if no quantity is available in a location.
                                //TODO: Maybe add a settings to show product not available at a location or not.
                                $query->orWhereNull('VLD.location_id');
                            });
                        }
                    }
                );

            $url = str_replace(url('/'), '', url()->previous());
           if($url == '/pos/create' || $url == '/pos/edit'){

           }

        if (!is_null($not_for_selling)) {
            $query->where('products.not_for_selling', $not_for_selling);
        }

        if (!empty($price_group_id)) {
            $query->leftjoin(
                'variation_group_prices AS VGP',
                function ($join) use ($price_group_id) {
                    $join->on('variations.id', '=', 'VGP.variation_id')
                        ->where('VGP.price_group_id', '=', $price_group_id);
                }
            );
        }

        $query->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

        if (!empty($product_types)) {
            $query->whereIn('products.type', $product_types);
        }

        if (in_array('lot', $search_fields)) {
            $query->leftjoin('purchase_lines as pl', 'variations.id', '=', 'pl.variation_id');
        }

        //Include search
        if (!empty($search_term)) {

            //Search with like condition
            if($search_type == 'like'){
                $query->where(function ($query) use ($search_term, $search_fields) {

                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('sku', $search_fields)) {
                        if(is_numeric($search_term) && (strlen($search_term) >= 9 && strlen($search_term) <= 14)){
                            // echo "hi1";exit;
                            $query->orWhere('sku', '=', $search_term);
                        }else{
                            // echo "hi2";exit;
                            $query->orWhere('sku', 'like', '%' . $search_term .'%');
                        }
                    }

                    if (in_array('sku2', $search_fields)) {
                        if(is_numeric($search_term) && (strlen($search_term) >= 9 && strlen($search_term) <= 14)){
                            // echo "hi1";exit;
                            $query->orWhere('sku2', '=', $search_term);
                        }else{
                            // echo "hi2";exit;
                            $query->orWhere('sku2', 'like', '%' . $search_term .'%');
                        }
                    }

                    if (in_array('sku3', $search_fields)) {
                        if(is_numeric($search_term) && (strlen($search_term) >= 9 && strlen($search_term) <= 14)){
                            // echo "hi1";exit;
                            $query->orWhere('sku3', '=', $search_term);
                        }else{
                            // echo "hi2";exit;
                            $query->orWhere('sku3', 'like', '%' . $search_term .'%');
                        }
                    }

                    if (in_array('item_code', $search_fields)) {
                        $query->orWhere('item_code', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', 'like', '%' . $search_term .'%');
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', 'like', '%' . $search_term .'%');
                    }
                });
            }

            //Search with exact condition
            if($search_type == 'exact'){
                $query->where(function ($query) use ($search_term, $search_fields) {

                    if (in_array('name', $search_fields)) {
                        $query->where('products.name', $search_term);
                    }

                    if (in_array('sku', $search_fields)) {
                        $query->orWhere('sku', $search_term);
                    }

                    if (in_array('sku2', $search_fields)) {
                        $query->orWhere('sku2', $search_term);
                    }

                    if (in_array('sku3', $search_fields)) {
                        $query->orWhere('sku3', $search_term);
                    }

                    if (in_array('sub_sku', $search_fields)) {
                        $query->orWhere('sub_sku', $search_term);
                    }

                    if (in_array('lot', $search_fields)) {
                        $query->orWhere('pl.lot_number', $search_term);
                    }
                });
            }
        }


        //Include check for quantity
        if ($check_qty) {
            $query->where('VLD.qty_available', '>', 0);
        }

        if (!empty($location_id)) {
            $query->ForLocation($location_id);
        }

        $query->select(
                'products.id as product_id',
                'products.name',
                'products.type',
                'products.srp as web_reg_price',
                'products.sales_price as web_sale_price',
                'products.item_code',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'products.sku as sku',
                'variations.default_sell_price',
                'variations.sell_price_inc_tax as selling_price',
                'variations.sub_sku',
                'U.short_name as unit',
                'variations.dpp_inc_tax',
                'variations.sell_price_inc_tax',
                'products.is_inactive',
                'products.not_for_selling'
            );

        if (!empty($price_group_id)) {
            $query->addSelect('VGP.price_inc_tax as variation_group_price');
        }

        if (in_array('lot', $search_fields)) {
            $query->addSelect('pl.id as purchase_line_id', 'pl.lot_number');
        }

        $query->groupBy('variations.id');
        // return $query->orderBy('VLD.qty_available', 'desc')->get();
        // dd($query->toSql());
        return $query->orderBy('products.name', 'asc')->get();


    }

    public function getProductStockDetails($business_id, $filters, $for)
    {
        $query = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                  ->join('units', 'p.unit_id', '=', 'units.id')
                  ->leftjoin('variation_location_details as vld', 'variations.id', '=', 'vld.variation_id')
                  ->leftjoin('business_locations as l', 'vld.location_id', '=', 'l.id')
                  ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                  ->where('p.business_id', $business_id)
                  ->whereIn('p.type', ['single', 'variable']);

        $permitted_locations = auth()->user()->permitted_locations();
        $location_filter = '';

        if ($permitted_locations != 'all') {
            $query->whereIn('vld.location_id', $permitted_locations);

            $locations_imploded = implode(', ', $permitted_locations);
            $location_filter .= "AND transactions.location_id IN ($locations_imploded) ";
        }

        $filters['location_id'] = 4;
        if (!empty($filters['location_id'])) {
            $location_id = $filters['location_id'];

            $query->where('vld.location_id', $location_id);

            $location_filter .= "AND transactions.location_id=$location_id";

            //If filter by location then hide products not available in that location
            $query->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                  ->where(function ($q) use ($location_id) {
                      $q->where('pl.location_id', $location_id);
                  });
        }

        if (!empty($filters['category_id'])) {
            $query->where('p.category_id', $filters['category_id']);
        }
        if (!empty($filters['sub_category_id'])) {
            $query->where('p.sub_category_id', $filters['sub_category_id']);
        }
        if (!empty($filters['brand_id'])) {
            $query->where('p.brand_id', $filters['brand_id']);
        }
        if (!empty($filters['unit_id'])) {
            $query->where('p.unit_id', $filters['unit_id']);
        }

        if (!empty($filters['tax_id'])) {
            $query->where('p.tax', $filters['tax_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('p.type', $filters['type']);
        }

        if (isset($filters['only_mfg_products']) && $filters['only_mfg_products'] == 1) {
            $query->join('mfg_recipes as mr', 'mr.variation_id', '=', 'variations.id');
        }

        if (isset($filters['active_state']) && $filters['active_state'] == 'active') {
            $query->where('p.is_inactive', 0);
        }
        if (isset($filters['active_state']) && $filters['active_state'] == 'inactive') {
            $query->where('p.is_inactive', 1);
        }
        if (isset($filters['not_for_selling']) && $filters['not_for_selling'] == 1) {
            $query->where('p.not_for_selling', 1);
        }

        if (!empty($filters['repair_model_id'])) {
            $query->where('p.repair_model_id', request()->get('repair_model_id'));
        }

        //TODO::Check if result is correct after changing LEFT JOIN to INNER JOIN
        $pl_query_string = $this->get_pl_quantity_sum_string('pl');

        if ($for == 'view_product' && !empty(request()->input('product_id'))) {
            $location_filter = 'AND transactions.location_id=l.id';
        }

        $products = $query->select(
            // DB::raw("(SELECT SUM(quantity) FROM transaction_sell_lines LEFT JOIN transactions ON transaction_sell_lines.transaction_id=transactions.id WHERE transactions.status='final' $location_filter AND
            //     transaction_sell_lines.product_id=products.id) as total_sold"),

            DB::raw("(SELECT SUM(TSL.quantity - TSL.quantity_returned) FROM transactions
                  JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                  WHERE transactions.status='final' AND transactions.type='sell' AND transactions.location_id=vld.location_id
                  AND TSL.variation_id=variations.id) as total_sold"),
            DB::raw("(SELECT SUM(IF(transactions.type='sell_transfer', TSL.quantity, 0) ) FROM transactions
                  JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                  WHERE transactions.status='final' AND transactions.type='sell_transfer' AND transactions.location_id=vld.location_id AND (TSL.variation_id=variations.id)) as total_transfered"),
            DB::raw("(SELECT SUM(IF(transactions.type='stock_adjustment', SAL.quantity, 0) ) FROM transactions
                  JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                  WHERE transactions.type='stock_adjustment' AND transactions.location_id=vld.location_id
                    AND (SAL.variation_id=variations.id)) as total_adjusted"),
            DB::raw("(SELECT SUM( COALESCE(pl.quantity - ($pl_query_string), 0) * purchase_price_inc_tax) FROM transactions
                  JOIN purchase_lines AS pl ON transactions.id=pl.transaction_id
                  WHERE transactions.status='received' AND transactions.location_id=vld.location_id
                  AND (pl.variation_id=variations.id)) as stock_price"),
            DB::raw("SUM(vld.qty_available) as stock"),
            'variations.sub_sku as sku',
            'p.name as product',
            'p.type',
            'p.sales_price as web_sale_price',
            'p.id as product_id',
            'units.short_name as unit',
            'p.enable_stock as enable_stock',
            'variations.sell_price_inc_tax as unit_price',
            'pv.name as product_variation',
            'variations.name as variation_name',
            'l.name as location_name',
            'l.id as location_id',
            'variations.id as variation_id'
        )->groupBy('variations.id', 'vld.location_id');

        if (isset($filters['show_manufacturing_data']) && $filters['show_manufacturing_data']) {
            $pl_query_string = $this->get_pl_quantity_sum_string('PL');
            $products->addSelect(
                DB::raw("(SELECT COALESCE(SUM(PL.quantity - ($pl_query_string)), 0) FROM transactions
                    JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                    WHERE transactions.status='received' AND transactions.type='production_purchase' AND transactions.location_id=vld.location_id
                    AND (PL.variation_id=variations.id)) as total_mfg_stock")
            );
        }

        // $products = TransactionSellLinesPurchaseLines::leftJoin('transaction_sell_lines
        //             as SL', 'SL.id', '=', 'transaction_sell_lines_purchase_lines.sell_line_id')
        //         ->leftJoin('stock_adjustment_lines
        //             as SAL', 'SAL.id', '=', 'transaction_sell_lines_purchase_lines.stock_adjustment_line_id')
        //         ->leftJoin('transactions as sale', 'SL.transaction_id', '=', 'sale.id')
        //         ->leftJoin('transactions as stock_adjustment', 'SAL.transaction_id', '=', 'stock_adjustment.id')
        //         ->join('purchase_lines as PL', 'PL.id', '=', 'transaction_sell_lines_purchase_lines.purchase_line_id')
        //         ->join('transactions as purchase', 'PL.transaction_id', '=', 'purchase.id')
        //         ->join('business_locations as bl', 'purchase.location_id', '=', 'bl.id')
        //         ->join(
        //             'variations',
        //             'PL.variation_id',
        //             '=',
        //             'variations.id'
        //             )
        //         ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
        //         ->join('products as p', 'PL.product_id', '=', 'p.id')
        //         ->join('units as u', 'p.unit_id', '=', 'u.id')
        //         ->leftJoin('contacts as suppliers', 'purchase.contact_id', '=', 'suppliers.id')
        //         ->leftJoin('contacts as customers', 'sale.contact_id', '=', 'customers.id')
        //         ->where('purchase.business_id', $business_id)
        //         ->select(
        //             'variations.sub_sku as sku',
        //             'p.type as product_type',
        //             'p.name as product_name',
        //             'variations.name as variation_name',
        //             'variations.sell_price_inc_tax',
        //             'pv.name as product_variation',
        //             'u.short_name as unit',
        //             'purchase.transaction_date as purchase_date',
        //             'purchase.ref_no as purchase_ref_no',
        //             'purchase.type as purchase_type',
        //             'suppliers.name as supplier',
        //             'PL.purchase_price_inc_tax as purchase_price',
        //             'sale.transaction_date as sell_date',
        //             'stock_adjustment.transaction_date as stock_adjustment_date',
        //             'sale.invoice_no as sale_invoice_no',
        //             'stock_adjustment.ref_no as stock_adjustment_ref_no',
        //             'customers.name as customer',
        //             'transaction_sell_lines_purchase_lines.quantity as quantity',
        //             'SL.unit_price_inc_tax as selling_price',
        //             'SAL.unit_price as stock_adjustment_price',
        //             'transaction_sell_lines_purchase_lines.stock_adjustment_line_id',
        //             'transaction_sell_lines_purchase_lines.sell_line_id',
        //             'transaction_sell_lines_purchase_lines.purchase_line_id',
        //             'transaction_sell_lines_purchase_lines.qty_returned',
        //             'bl.name as location'
        //         );

        if (!empty($filters['product_id'])) {
            $products->where('p.id', $filters['product_id']);
                    //->groupBy('l.id');
        }

        if ($for == 'view_product') {
            return $products->get();
        } else if ($for == 'api') {
            return $products->paginate();
        } else {
            return $products;
        }
    }

    public function getProductStockDetailsForStockReport($business_id, $filters, $for)
    {
        $query = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                  ->join('units', 'p.unit_id', '=', 'units.id')
                  ->leftjoin('variation_location_details as vld', 'variations.id', '=', 'vld.variation_id')
                  ->leftjoin('business_locations as l', 'vld.location_id', '=', 'l.id')
                  ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                  ->where('p.business_id', $business_id)
                  //->where('l.business_id', $business_id)
                  ->where('l.name','!=','Test Company')
                  ->whereIn('p.type', ['single', 'variable']);

                  if(isset($filters['start_date']) && isset($filters['end_date']))
                  {
                    $from = date($filters['start_date']);
                    $to = date($filters['end_date']);
                    //$query->whereBetween('vld.created_at',  [$from, $to]);
                    //$query->whereDate('vld.created_at', '>=', $from);
                                $query->whereDate('vld.created_at', '<=', $to);
                  }


        $permitted_locations = auth()->user()->permitted_locations();
        $location_filter = '';

        if ($permitted_locations != 'all') {
            $query->whereIn('vld.location_id', $permitted_locations);

            $locations_imploded = implode(', ', $permitted_locations);
            $location_filter .= "AND transactions.location_id IN ($locations_imploded) ";
        }

        if (!empty($filters['location_id'])) {
            $location_id = $filters['location_id'];

            $query->where('vld.location_id', $location_id);

            $location_filter .= "AND transactions.location_id=$location_id";

            //If filter by location then hide products not available in that location
            $query->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                  ->where(function ($q) use ($location_id) {
                      $q->where('pl.location_id', $location_id);
                  });
        }

        if (!empty($filters['category_id'])) {
            $query->where('p.category_id', $filters['category_id']);
        }
        if (!empty($filters['sub_category_id'])) {
            $query->where('p.sub_category_id', $filters['sub_category_id']);
        }
        if (!empty($filters['brand_id'])) {
            $query->where('p.brand_id', $filters['brand_id']);
        }
        if (!empty($filters['unit_id'])) {
            $query->where('p.unit_id', $filters['unit_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('p.supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['tax_id'])) {
            $query->where('p.tax', $filters['tax_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('p.type', $filters['type']);
        }

        if (isset($filters['only_mfg_products']) && $filters['only_mfg_products'] == 1) {
            $query->join('mfg_recipes as mr', 'mr.variation_id', '=', 'variations.id');
        }

        if (isset($filters['active_state']) && $filters['active_state'] == 'active') {
            $query->where('p.is_inactive', 0);
        }
        if (isset($filters['active_state']) && $filters['active_state'] == 'inactive') {
            $query->where('p.is_inactive', 1);
        }
        if (isset($filters['not_for_selling']) && $filters['not_for_selling'] == 1) {
            $query->where('p.not_for_selling', 1);
        }
        if (isset($filters['only_for_selling']) && $filters['only_for_selling'] == 1) {
            $query->where('p.not_for_selling', '<>', 1);
        }

        if (!empty($filters['repair_model_id'])) {
            $query->where('p.repair_model_id', request()->get('repair_model_id'));
        }

        //TODO::Check if result is correct after changing LEFT JOIN to INNER JOIN
        $pl_query_string = $this->get_pl_quantity_sum_string('pl');

        if ($for == 'view_product' && !empty(request()->input('product_id'))) {
            $location_filter = 'AND transactions.location_id=l.id';
        }

        $start = $filters['start_date'].' 00:00:00';
        $end = $filters['end_date'].' 23:59:59';

        // dd($start,$end);

        $products = $query->select(
      DB::raw("(SELECT SUM(TSL.quantity - TSL.quantity_returned) FROM transactions
                  JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                  WHERE  transactions.status='final' AND transactions.type='sell' AND transactions.location_id=vld.location_id
                  AND TSL.variation_id=variations.id AND Date(transactions.transaction_date) >= '$start' AND Date(transactions.transaction_date) <= '$end') as total_sold"),

         DB::raw("(SELECT SUM(TSL.quantity - TSL.quantity_returned) FROM transactions
                  JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                  WHERE transactions.status='final'
                    AND transactions.location_id=vld.location_id
                    AND TSL.variation_id=variations.id
                    AND transactions.transaction_date >= '$start'
                    AND transactions.transaction_date <= '$end'
                    AND transactions.type IN ('sell', 'sell_return')) as total_sold_new"),
            // DB::raw("(SELECT SUM(TSL.quantity - TSL.quantity_returned) FROM transactions
            //       JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
            //       WHERE  transactions.status='final' AND transactions.type='sell' AND transactions.location_id=vld.location_id
            //       AND TSL.variation_id=variations.id AND Date(TSL.created_at) >= '$start' AND Date(TSL.created_at) <= '$end') as total_sold"),

            DB::raw("(SELECT SUM(PL.quantity - PL.quantity_returned) FROM transactions
                  JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                  WHERE  transactions.status='final' AND transactions.type='purchase' AND transactions.location_id=vld.location_id
                  AND PL.variation_id=variations.id  AND Date(PL.created_at) >= '$start' AND Date(PL.created_at) <= '$end') as total_purchased"),

            // DB::raw("(SELECT SUM(COALESCE(PL.quantity- PL.quantity_returned)) FROM transactions
            //         LEFT JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
            //         WHERE transactions.status='received' AND transactions.type='purchase' AND transactions.location_id=$location_id
            //         AND PL.variation_id=variations.id  AND Date(transactions.transaction_date) >= '$start' AND Date(transactions.transaction_date) <= '$end') as total_purchased"),

            DB::raw("(SELECT SUM(IF(transactions.type='sell_transfer', TSL.quantity, 0) ) FROM transactions
                  JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                  WHERE  transactions.status='final' AND transactions.type='sell_transfer' AND transactions.location_id=vld.location_id AND (TSL.variation_id=variations.id)) as total_transfered"),

            DB::raw("(SELECT SUM(IF(transactions.type='stock_adjustment', SAL.quantity, 0) ) FROM transactions
                  JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                  WHERE  transactions.type='stock_adjustment' AND transactions.location_id=vld.location_id
                    AND (SAL.variation_id=variations.id)) as total_adjusted"),

            DB::raw("(SELECT SUM( COALESCE(pl.quantity - ($pl_query_string), 0) * purchase_price_inc_tax) FROM transactions
                  JOIN purchase_lines AS pl ON transactions.id=pl.transaction_id
                  WHERE transactions.status='received' AND transactions.location_id=vld.location_id
                  AND (pl.variation_id=variations.id)) as stock_price"),

            DB::raw("(
                    SELECT SUM(pl.quantity)
                    FROM purchase_lines AS pl
                    JOIN transactions AS t ON t.id = pl.transaction_id
                    WHERE t.is_consignment = 1
                      AND t.type = 'purchase'
                      AND pl.product_id = p.id
                ) as consignment_stock"),
            DB::raw("SUM(vld.qty_available) as stock"),
            // DB::raw("(Select SUM(vld.qty_available) from variation_location_details as vld where vld.created_at >= ".$filters['stock_start']." AND vld.created_at <=  ".$filters['stock_end']." AND variations.id=vld.variation_id)  as stock"),
            'variations.sub_sku as sku',
            'p.name as product',
            'p.type',
            'p.id as product_id',
            'units.short_name as unit',
            'p.enable_stock as enable_stock',
            'variations.sell_price_inc_tax as unit_price',
            'variations.default_purchase_price as unit_purchase_price',
            'pv.name as product_variation',
            'variations.name as variation_name',
            'l.name as location_name',
            'l.id as location_id',
            'variations.id as variation_id'
        )->groupBy('variations.id', 'vld.location_id');

        if (isset($filters['show_manufacturing_data']) && $filters['show_manufacturing_data']) {
            $pl_query_string = $this->get_pl_quantity_sum_string('PL');
            $products->addSelect(
                DB::raw("(SELECT COALESCE(SUM(PL.quantity - ($pl_query_string)), 0) FROM transactions
                    JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                    WHERE transactions.status='received' AND transactions.type='production_purchase' AND transactions.location_id=vld.location_id
                    AND (PL.variation_id=variations.id)) as total_mfg_stock")
            );
        }



        $query->withCount(['sell_lines AS total_sell' => function ($query) use($start, $end)
            {
                if (!empty($start) && !empty($end)) {
                    $query->whereDate('created_at', '>=', $start)
                            ->whereDate('created_at', '<=', $end);
                }
                $query->select(DB::raw("SUM(quantity) as total_sell_qty"));
            }
        ]);

        $query->withCount(['purchase_lines AS total_purchase' => function ($query) use($start, $end)
            {
                if (!empty($start) && !empty($end)) {
                    $query->whereDate('created_at', '>=', $start)
                            ->whereDate('created_at', '<=', $end);
                }
                $query->select(DB::raw("SUM(quantity) as total_purchase_qty"));
            }
        ]);


        // $products = TransactionSellLinesPurchaseLines::leftJoin('transaction_sell_lines
        //             as SL', 'SL.id', '=', 'transaction_sell_lines_purchase_lines.sell_line_id')
        //         ->leftJoin('stock_adjustment_lines
        //             as SAL', 'SAL.id', '=', 'transaction_sell_lines_purchase_lines.stock_adjustment_line_id')
        //         ->leftJoin('transactions as sale', 'SL.transaction_id', '=', 'sale.id')
        //         ->leftJoin('transactions as stock_adjustment', 'SAL.transaction_id', '=', 'stock_adjustment.id')
        //         ->join('purchase_lines as PL', 'PL.id', '=', 'transaction_sell_lines_purchase_lines.purchase_line_id')
        //         ->join('transactions as purchase', 'PL.transaction_id', '=', 'purchase.id')
        //         ->join('business_locations as bl', 'purchase.location_id', '=', 'bl.id')
        //         ->join(
        //             'variations',
        //             'PL.variation_id',
        //             '=',
        //             'variations.id'
        //             )
        //         ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
        //         ->join('products as p', 'PL.product_id', '=', 'p.id')
        //         ->join('units as u', 'p.unit_id', '=', 'u.id')
        //         ->leftJoin('contacts as suppliers', 'purchase.contact_id', '=', 'suppliers.id')
        //         ->leftJoin('contacts as customers', 'sale.contact_id', '=', 'customers.id')
        //         ->where('purchase.business_id', $business_id)
        //         ->select(
        //             'variations.sub_sku as sku',
        //             'p.type as product_type',
        //             'p.name as product_name',
        //             'variations.name as variation_name',
        //             'variations.sell_price_inc_tax',
        //             'pv.name as product_variation',
        //             'u.short_name as unit',
        //             'purchase.transaction_date as purchase_date',
        //             'purchase.ref_no as purchase_ref_no',
        //             'purchase.type as purchase_type',
        //             'suppliers.name as supplier',
        //             'PL.purchase_price_inc_tax as purchase_price',
        //             'sale.transaction_date as sell_date',
        //             'stock_adjustment.transaction_date as stock_adjustment_date',
        //             'sale.invoice_no as sale_invoice_no',
        //             'stock_adjustment.ref_no as stock_adjustment_ref_no',
        //             'customers.name as customer',
        //             'transaction_sell_lines_purchase_lines.quantity as quantity',
        //             'SL.unit_price_inc_tax as selling_price',
        //             'SAL.unit_price as stock_adjustment_price',
        //             'transaction_sell_lines_purchase_lines.stock_adjustment_line_id',
        //             'transaction_sell_lines_purchase_lines.sell_line_id',
        //             'transaction_sell_lines_purchase_lines.purchase_line_id',
        //             'transaction_sell_lines_purchase_lines.qty_returned',
        //             'bl.name as location'
        //         );

        if (!empty($filters['product_id'])) {
            $products->where('p.id', $filters['product_id']);
                    //->groupBy('l.id');
        }

        if ($for == 'view_product') {
            return $products->get();
        } else if ($for == 'api') {
            return $products->paginate();
        } else {
            return $products;
        }
    }

    /**
     * Gives the details of combo product
     *
     * @param array $combo_variations
     * @param int $business_id
     *
     * @return array
     */
    public function __getComboProductDetails($combo_variations, $business_id)
    {
        foreach ($combo_variations as $key => $value) {
            $combo_variations[$key]['variation'] =
                Variation::with(['product'])
                    ->find($value['variation_id']);

            $combo_variations[$key]['sub_units'] = $this->getSubUnits($business_id, $combo_variations[$key]['variation']['product']->unit_id, true);

            $combo_variations[$key]['multiplier'] = 1;

            if (!empty($combo_variations[$key]['sub_units'])) {
                if (isset($combo_variations[$key]['sub_units'][$combo_variations[$key]['unit_id']])) {
                    $combo_variations[$key]['multiplier'] = $combo_variations[$key]['sub_units'][$combo_variations[$key]['unit_id']]['multiplier'];
                    $combo_variations[$key]['unit_name'] = $combo_variations[$key]['sub_units'][$combo_variations[$key]['unit_id']]['name'];
                }
            }
        }

        return $combo_variations;
    }

    public function getVariationStockDetails($business_id, $variation_id, $location_id)
    {
          $purchase_details = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                    ->join('units', 'p.unit_id', '=', 'units.id')
                    ->leftjoin('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                    ->leftjoin('purchase_lines as pl', 'pl.variation_id', '=', 'variations.id')
                    ->leftjoin('transactions as t', 'pl.transaction_id', '=', 't.id')
                    ->where('t.location_id', $location_id)
                    ->where('t.status', 'received')
                    ->where('p.business_id', $business_id)
                    ->where('variations.id', $variation_id)
                    ->select(
                        DB::raw("SUM(IF(t.type='purchase', pl.quantity, 0)) as total_purchase"),
                        DB::raw("SUM(IF(t.type='purchase_return', pl.quantity_returned, 0)) as total_purchase_return"),
                        //DB::raw("SUM(pl.quantity_adjusted) as total_adjusted"),
                        DB::raw("SUM(IF(t.type='opening_stock', pl.quantity, 0)) as total_opening_stock"),
                        DB::raw("SUM(IF(t.type='purchase_transfer', pl.quantity, 0)) as total_purchase_transfer"),
                        DB::raw("SUM(IF(t.type = 'purchase' AND t.is_consignment = 1, pl.quantity, 0)) as total_consingment_qnty"),
                        'variations.sub_sku as sub_sku',
                        'p.name as product',
                        'p.type',
                        'p.sku',
                        'p.id as product_id',
                        'units.short_name as unit',
                        'pv.name as product_variation',
                        'variations.name as variation_name',
                        'variations.id as variation_id'
                    )
                  ->get()->first();

            $purchase_return = DB::table('purchase_lines as pl')
                    ->select(DB::raw('SUM(quantity_returned) as purchase_return'))
                    ->join('transactions as t', 'pl.transaction_id', '=', 't.id')
                    ->where('t.location_id', $location_id)
                    ->where('t.type','purchase_return')
                    ->where('pl.variation_id', $variation_id)
                    ->get();
            $pr_qty = $purchase_return[0]->purchase_return ?? 0;

            $sell_details = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
                    ->leftjoin('transaction_sell_lines as sl', 'sl.variation_id', '=', 'variations.id')
                    ->join('transactions as t', 'sl.transaction_id', '=', 't.id')
                    ->where('t.location_id', $location_id)
                    ->where('t.status', 'final')
                    ->where('p.business_id', $business_id)
                    ->where('variations.id', $variation_id)
                    ->select(
                        DB::raw("SUM(IF(t.type='sell', sl.quantity, 0)) as total_sold"),
                        DB::raw("SUM(IF(t.type='sell' AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH), sl.quantity, 0)) as total_sold_last_1_month"),
                        DB::raw("SUM(IF(t.type='sell' AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH), sl.quantity, 0)) as total_sold_last_2_months"),
                        DB::raw("SUM(IF(t.type='sell' AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH), sl.quantity, 0)) as total_sold_last_3_months"),
                        DB::raw("SUM(IF(t.type='sell_return', sl.quantity_returned, 0)) as total_sell_return"),
                        DB::raw("SUM(IF(t.type='sell_transfer', sl.quantity, 0)) as total_sell_transfer")
                    )
                  ->get()->first();
        $stock_adjustment = Transaction::leftjoin('transaction_sell_lines as sl',
            'sl.transaction_id', '=', 'transactions.id')->leftjoin('purchase_lines as pl','pl.transaction_id', '=', 'transactions.id')
              ->leftjoin('stock_adjustment_lines as al','al.transaction_id', '=', 'transactions.id')
              ->where('transactions.location_id', $location_id)
              ->where( function($q) use ($variation_id){
                   $q->where('sl.variation_id', $variation_id)
                     ->orWhere('al.variation_id', $variation_id);
                        })
                ->where('transactions.type','stock_adjustment')
                ->select('transactions.id as transaction_id', 'transactions.type as transaction_type', 'sl.quantity as sell_line_quantity',
                            'al.quantity as stock_adjusted', 'al.old_qty as old_qty', 'transactions.return_parent_id', 'transactions.transaction_date',
                        'transactions.status','transactions.invoice_no', 'transactions.ref_no', 'transactions.adjustment_type')
                ->orderBy('transactions.transaction_date', 'asc')
                ->groupBy('transactions.id')
                ->get();

        $stock_adjust_abnormal=0;
        $stock_adjust_normal=0;
        foreach($stock_adjustment as $stock){
            if($stock->adjustment_type == 'normal'){
               $old_qty_adjust = $stock->old_qty;
                $change = $stock->stock_adjusted - $old_qty_adjust;
                 $quantity_change = $change;
                $stock_adjust_normal += $quantity_change;
            }
            if($stock->adjustment_type == 'abnormal'){
                $quantity_change = +1 * $stock->stock_adjusted;
                $stock_adjust_abnormal +=  $quantity_change;
            }
        }

        $stock_adjust = $stock_adjust_abnormal + $stock_adjust_normal;

        $current_stock = VariationLocationDetails::where('variation_id',
                                            $variation_id)
                                        ->where('location_id', $location_id)
                                        ->first();

        if ($purchase_details->type == 'variable') {
            $product_name = $purchase_details->product . ' - ' . $purchase_details->product_variation . ' - ' . $purchase_details->variation_name . ' (' . $purchase_details->sub_sku . ')';
        } else {
            $product_name = $purchase_details->product . ' (' . $purchase_details->sku . ')';
        }

        $output = [
            'variation_id' => $variation_id,
            'variation' => $product_name,
            'unit' => $purchase_details->unit,
            'total_purchase' => $purchase_details->total_purchase,
            'total_consignment_qnty' => $purchase_details->total_consingment_qnty,
            'total_purchase_return' => $pr_qty,//$purchase_details->total_purchase_return,
            'total_adjusted' => $stock_adjust,
            'total_opening_stock' => $purchase_details->total_opening_stock,
            'total_purchase_transfer' => $purchase_details->total_purchase_transfer,
            'total_sold' => $sell_details->total_sold,
            'total_sell_return' => $sell_details->total_sell_return,
            'total_sell_transfer' => $sell_details->total_sell_transfer,
            'current_stock' => $current_stock->qty_available ?? 0,
            'total_sold_last_1_month' => $sell_details->total_sold_last_1_month,
            'total_sold_last_2_months' => $sell_details->total_sold_last_2_months,
            'total_sold_last_3_months' => $sell_details->total_sold_last_3_months,
        ];

        return $output;
    }

    public function getVariationStockHistory($business_id, $variation_id, $location_id, $type='')
    {
      $stock_history = Transaction::leftjoin('transaction_sell_lines as sl',
                                'sl.transaction_id', '=', 'transactions.id','sl.transaction_id','=','transactions.return_parent_id')
                              ->leftjoin('purchase_lines as pl',
                                  'pl.transaction_id', '=', 'transactions.id','pl.transaction_id','=','transactions.return_parent_id')
                              ->leftjoin('stock_adjustment_lines as al',
                                  'al.transaction_id', '=', 'transactions.id')
                              // ->leftjoin('transactions as return', 'transactions.return_parent_id', '=', 'return.id')
                              // ->leftjoin('purchase_lines as rpl',
                                  // 'rpl.transaction_id', '=', 'return.id')
                              // ->leftjoin('transaction_sell_lines as rsl',
                              //         'rsl.transaction_id', '=', 'return.id')
                              //  ->join('contacts','contacts.id','=','transactions.contact_id')
                              ->where('transactions.location_id', $location_id)
                              ->where( function($q) use ($variation_id){
                                  $q->where('sl.variation_id', $variation_id)
                                      ->orWhere('pl.variation_id', $variation_id)
                                      ->orWhere('al.variation_id', $variation_id);
                                      // ->orWhere('rpl.variation_id', $variation_id)
                                      // ->orWhere('rsl.variation_id', $variation_id);
                              });

                                if(!empty($type)){
                                     $stock_history->where('transactions.type',$type);
                                }

                                $stock_history = $stock_history->whereIn('transactions.type', ['sell', 'purchase', 'stock_adjustment', 'opening_stock', 'sell_transfer', 'purchase_transfer', 'production_purchase', 'purchase_return', 'sell_return'])
                                    ->select(
                                        'transactions.id as transaction_id',
                                        'transactions.type as transaction_type',
                                        'sl.quantity as sell_line_quantity',
                                        'pl.quantity as purchase_line_quantity',
                                        'sl.quantity_returned as sell_return',
                                        'pl.quantity_returned as purchase_return',
                                        'al.quantity as stock_adjusted',
                                        'transactions.return_parent_id',
                                        'transactions.transaction_date',
                                        'transactions.status',
                                        'transactions.invoice_no',
                                        'transactions.ref_no',
                                        'transactions.adjustment_type',
                                        DB::raw("(SELECT c.name  FROM contacts as c
                                            WHERE c.id=transactions.contact_id ) as company_name"),
                                        DB::raw("(SELECT c.supplier_business_name FROM contacts as c WHERE c.id=transactions.contact_id) as supplier_business_name "),
                                         DB::raw("(SELECT c.first_name  FROM contacts as c
                                            WHERE c.id=transactions.contact_id ) as dba_name")
                                        // 'contacts.name as company_name',
                                        // 'contacts.supplier_business_name as supplier_business_name'
                                    )
                                ->orderBy('transactions.transaction_date', 'asc')
                                //->groupBy('transactions.id')
                                ->get();

        $stock_history_array = [];
        $stock = 0;
        foreach ($stock_history as $stock_line) {

            if ($stock_line->transaction_type == 'sell') {
                if ($stock_line->status != 'final') {
                    continue;
                }
                $quantity_change =  -1 * $stock_line->sell_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'sell',
                    'type_label' => __('sale.sale'),
                    'ref_no' => $stock_line->invoice_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'purchase') {
                if ($stock_line->status != 'received') {
                    continue;
                }
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase',
                    'type_label' => __('lang_v1.purchase'),
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'stock_adjustment') {
               if($stock_line->adjustment_type == 'normal'){
                    $old_qty_adjust = $stock_line->old_qty;
                    $change = $stock_line->stock_adjusted - $old_qty_adjust;
                    $quantity_change = $change;
                    $stock = $stock_line->stock_adjusted;

                    $nameStock = $stock_line->adjustment_type;
                }elseif($stock_line->adjustment_type == 'abnormal'){
                    $quantity_change = +1 * $stock_line->stock_adjusted;
                    $stock += $quantity_change;
                    $nameStock = $stock_line->adjustment_type;
                }else{
                    $quantity_change = -1 * $stock_line->stock_adjusted;
                    $stock += $quantity_change;
                    $nameStock = '';
                }
                // $quantity_change = -1 * $stock_line->stock_adjusted;
                // $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'stock_adjustment',
                    'type_label' => __('stock_adjustment.stock_adjustment') .':'. __('stock_adjustment.'.$nameStock),
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'adjustment_type' => $stock_line->adjustment_type,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'opening_stock') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'opening_stock',
                    'type_label' => __('report.opening_stock'),
                    'ref_no' => $stock_line->ref_no ?? '',
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'sell_transfer') {
                $quantity_change = -1 * $stock_line->sell_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'sell_transfer',
                    'type_label' => __('lang_v1.stock_transfers') . ' (' . __('lang_v1.out') . ')',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'purchase_transfer') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase_transfer',
                    'type_label' => __('lang_v1.stock_transfers') . ' (' . __('lang_v1.in') . ')',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'production_purchase') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'production_purchase',
                    'type_label' => __('manufacturing::lang.manufactured'),
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'purchase_return') {
                $quantity_change =  -1 * $stock_line->purchase_return;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase_return',
                    'type_label' => __('lang_v1.purchase_return'),
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            } elseif ($stock_line->transaction_type == 'sell_return') {
                $quantity_change = $stock_line->sell_return;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase_transfer',
                    'type_label' => __('lang_v1.sell_return'),
                    'ref_no' => $stock_line->invoice_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'company_name' => $stock_line->company_name,
                    'supplier_business_name' => $stock_line->supplier_business_name,
                    'dba_name' => $stock_line->dba_name,

                ];
            }
        }

        return array_reverse($stock_history_array);
    }

public function getVariationStockHistoryShortned($business_id, $variation_id, $location_id, $type='')
    {
      $stock_history = Transaction::leftjoin('transaction_sell_lines as sl',
                                'sl.transaction_id', '=', 'transactions.id','sl.transaction_id','=','transactions.return_parent_id')
                              ->leftjoin('purchase_lines as pl',
                                  'pl.transaction_id', '=', 'transactions.id','pl.transaction_id','=','transactions.return_parent_id')
                              ->leftjoin('stock_adjustment_lines as al',
                                  'al.transaction_id', '=', 'transactions.id')
                              ->where('transactions.location_id', $location_id)
                              ->where( function($q) use ($variation_id){
                                  $q->where('sl.variation_id', $variation_id)
                                      ->orWhere('pl.variation_id', $variation_id)
                                      ->orWhere('al.variation_id', $variation_id);
                              });
                                if(!empty($type)){
                                     $stock_history->where('transactions.type',$type);
                                }
                                $stock_history = $stock_history->whereIn('transactions.type', ['sell', 'purchase', 'stock_adjustment', 'opening_stock', 'sell_transfer', 'purchase_transfer', 'production_purchase', 'purchase_return', 'sell_return'])
                                    ->select(
                                        'transactions.id as transaction_id',
                                        'transactions.type as transaction_type',
                                        'sl.quantity as sell_line_quantity',
                                        'pl.quantity as purchase_line_quantity',
                                        'sl.quantity_returned as sell_return',
                                        'pl.quantity_returned as purchase_return',
                                        'al.quantity as stock_adjusted',
                                        'transactions.return_parent_id',
                                        'transactions.transaction_date',
                                        'transactions.status',
                                        'transactions.invoice_no',
                                        'transactions.ref_no',
                                        'transactions.adjustment_type',
                                        DB::raw("(SELECT c.name  FROM contacts as c
                                            WHERE c.id=transactions.contact_id ) as company_name"),
                                        DB::raw("(SELECT c.supplier_business_name FROM contacts as c WHERE c.id=transactions.contact_id) as supplier_business_name "),
                                         DB::raw("(SELECT c.first_name  FROM contacts as c
                                            WHERE c.id=transactions.contact_id ) as dba_name")
                                    )
                                ->orderBy('transactions.transaction_date', 'asc')
                                ->get();
        $stock_history_array = [];
        $stock = 0;
        foreach ($stock_history as $stock_line) {
            if ($stock_line->transaction_type == 'sell') {
                if ($stock_line->status != 'final') {
                    continue;
                }
                $quantity_change =  -1 * $stock_line->sell_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'sell',
                    'ref_no' => $stock_line->invoice_no,
                    'transaction_id' => $stock_line->transaction_id,
                ];
            } elseif ($stock_line->transaction_type == 'purchase') {
                if ($stock_line->status != 'received') {
                    continue;
                }
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                ];
            } elseif ($stock_line->transaction_type == 'stock_adjustment') {
               if($stock_line->adjustment_type == 'normal'){
                    $old_qty_adjust = $stock_line->old_qty;
                    $change = $stock_line->stock_adjusted - $old_qty_adjust;
                    $quantity_change = $change;
                    $stock = $stock_line->stock_adjusted;

                    $nameStock = $stock_line->adjustment_type;
                }elseif($stock_line->adjustment_type == 'abnormal'){
                    $quantity_change = +1 * $stock_line->stock_adjusted;
                    $stock += $quantity_change;
                    $nameStock = $stock_line->adjustment_type;
                }else{
                    $quantity_change = -1 * $stock_line->stock_adjusted;
                    $stock += $quantity_change;
                    $nameStock = '';
                }
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'stock_adjustment',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                    'adjustment_type' => $stock_line->adjustment_type,
                ];
            } elseif ($stock_line->transaction_type == 'opening_stock') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'opening_stock',
                    'ref_no' => $stock_line->ref_no ?? '',
                    'transaction_id' => $stock_line->transaction_id,
                ];
            } elseif ($stock_line->transaction_type == 'sell_transfer') {
                $quantity_change = -1 * $stock_line->sell_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'sell_transfer',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                ];
            } elseif ($stock_line->transaction_type == 'purchase_transfer') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase_transfer',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                ];
            } elseif ($stock_line->transaction_type == 'production_purchase') {
                $quantity_change = $stock_line->purchase_line_quantity;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'production_purchase',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                ];
            } elseif ($stock_line->transaction_type == 'purchase_return') {
                $quantity_change =  -1 * $stock_line->purchase_return;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase_return',
                    'ref_no' => $stock_line->ref_no,
                    'transaction_id' => $stock_line->transaction_id,
                ];
            } elseif ($stock_line->transaction_type == 'sell_return') {
                $quantity_change = $stock_line->sell_return;
                $stock += $quantity_change;
                $stock_history_array[] = [
                    'date' => $stock_line->transaction_date,
                    'quantity_change' => $quantity_change,
                    'stock' => $stock,
                    'type' => 'purchase_transfer',
                    'ref_no' => $stock_line->invoice_no,
                    'transaction_id' => $stock_line->transaction_id,
                ];
            }
        }
        return array_reverse($stock_history_array);
    }
    public function getCustomerAddress($customer_id){
        return "";
        $customer = DB::table('contacts')
            ->where('id',$customer_id)
            ->select('shipping_address')
            ->first();
        if ($customer != null){
            return $customer->shipping_address;
        }else{
            return "";
        }

    }

    public function getTaxData($customer_id,$products){
        $customer = DB::table('contacts')->where('id',$customer_id)->first();
        $stateTax = 0.00;
        $cityTax = 0.00;
        $totalTax = 0.00;
        foreach ($products as $item) {
            $product = DB::table('products')
                ->leftJoin('tax_rates','products.category_id','=','tax_rates.category')
                ->where('products.id',$item)
                ->select('tax_rates.state','tax_rates.taxvalue','tax_rates.city_tax_value')
                ->first();
            if ($customer->state == $product->state){
                $stateTax = ($stateTax+$product->taxvalue);
                $cityTax = ($cityTax+$product->city_tax_value);
            }
        }
        $totalTax = ($stateTax+$cityTax);
        $tax = ([
            'cityTax' => $cityTax,
            'stateTax' => $stateTax,
            'totalTax' => $totalTax,
        ]);
        return $tax;
    }

    public function getProductTax($product_id, $variation_id, $customer_id, $cost, $selling_price){
        //state tax calculation
        $product = Product::where('id',$product_id)->first();
        $category_id = $product->category_id;
        $sub_category_id = $product->sub_category_id;
        $customer_state = Contact::where('id',$customer_id)->value('state');
        $today = date('Y-m-d');
        $data = [];
        $data["tax"] = 0;
        $selling_price=str_replace(",","",$selling_price);
        $taxrate = TaxRate::where('inactive',null)->where('tax_type',1)->where(function ($query) use($today) {
              $query->where('begining_date', '<=', $today)
                  ->orWhereNull('begining_date');
              })->where(function ($query) use($today) {
                  $query->where('end_date', '>=', $today)
                      ->orWhereNull('end_date');
              });


            if($category_id ){
                $taxrate = $taxrate->where(function ($query) use ($category_id) {
                    $query->where('category', '=', $category_id)
                          ->orWhere('category', '=', '');
                });
                if($sub_category_id != null){
                    $taxrate = $taxrate->where(function ($query) use ($sub_category_id) {
                        $query->where('sub_category', '=', $sub_category_id)
                              ->orWhere('sub_category', '=', '');
                    });
                }
            }
            if($customer_state != ''){
                $taxrate = $taxrate->where(function ($query) use ($customer_state) {
                    $query->where('state', '=', $customer_state)
                          ->orWhere('state', '=', '');
                });
            }
          $taxrates = $taxrate->first();
          // return $taxrates;
          $is_nyc = Contact::where('id',$customer_id)->value('is_nyc');
        if($taxrates){
            $tax_type = 1;
            // if($taxrates->tax_type == 1){
                $tax_percent = $taxrates->tax;
                $tax_value = $taxrates->taxvalue;
            // } else{
            //     $tax_percent = $taxrates->tax_percent;
            //     $tax_value = $taxrates->city_tax_value;
            // }
            if($tax_percent!= null){
                $tax = ($selling_price * $tax_percent)/100;
                $tax_type = 2; //fixed amount
                $tax_value = $tax_percent;
            } else {
                $tax = $tax_value;
            }
            $data['tax_type'] = $tax_type;
            $data['tax_value'] = $tax_value;
            $data['tax'] = round($tax, 2);
            $data['rule'] = $taxrates->id;
            $data['name'] = $taxrates->name;
            $data['state'] = $taxrates->state;
            $data['every_item'] = $taxrates->every;
            $data['is_applicable'] = 1;
            $data['city_state'] = $taxrates->tax_type;
            $data['is_ml'] = $taxrates->is_ml;

            if($taxrates->tax_type == 1){
                if($taxrates->state !='' && $customer_state != $taxrates->state){
                  $data['is_applicable'] = 0;
                }
            }
            // else if($taxrates->tax_type == 2 && $is_nyc != 1) {
            //     $data['is_applicable'] = 0;
            // }

        }

        //City tax calculation
        $city_tax_id = 0;
        $city_tax = 0;
        $first_item_value = 0;
        $second_item_value = 0;
        $city_every_item = '';
        $city_tax_name =  '';
        $city_tax_type = 1;
        $customer_is_nyc = Contact::where('id',$customer_id)->value('is_nyc');
        if($customer_is_nyc == 1){
            $taxrate_city = TaxRate::where('inactive',null)->where('tax_type',2)->where(function ($query) use($today) {
            $query->where('begining_date', '<=', $today)
                ->orWhereNull('begining_date');
            })->where(function ($query) use($today) {
              $query->where('end_date', '>=', $today)
                ->orWhereNull('end_date');
            });


            if($category_id ){
                $taxrate_city = $taxrate_city->where(function ($query) use ($category_id) {
                    $query->where('category', '=', $category_id)
                          ->orWhere('category', '=', '');
                });
                if($sub_category_id != null){
                    $taxrate_city = $taxrate_city->where(function ($query) use ($sub_category_id) {
                        $query->where('sub_category', '=', $sub_category_id)
                              ->orWhere('sub_category', '=', '');
                    });
                }
            }

          $taxrates_city = $taxrate_city->first();
          if($taxrates_city && $taxrates_city->id){
            $city_tax_id = $taxrates_city->id;
            $city_tax_name = $taxrates_city->name;

            if($taxrates_city->city_tax_value != null){
                $city_tax_percent = $taxrates_city->tax_percent;
                $city_tax_value = $taxrates_city->city_tax_value;
                if($city_tax_percent!= null){
                    $city_tax_type = 2;
                    $city_tax = ($selling_price * $city_tax_percent)/100;
                    $tax_value = $city_tax_percent;
                } else {
                    $city_tax_type = 1;
                    $city_tax = $city_tax_value;
                }
            } else{
                $first_item_value =  $taxrates_city->first_item_value;
                $second_item_value =  $taxrates_city->second_item_value;
            }
            $city_every_item = $taxrates_city->everycity;
            $first_item_value = $first_item_value;
            $second_item_value = $second_item_value;
          }
        }
        $data['city_tax_id'] = $city_tax_id;
        $data['city_tax_type'] = $city_tax_type;
        $data['city_tax'] = $city_tax;
        $data['city_every_item'] = $city_every_item;
        $data['first_item_value'] = $first_item_value;
        $data['second_item_value'] = $second_item_value;
        $data['city_tax_name'] = $city_tax_name;

        // return $taxrates_city;
        return $data;
    }

    public function calculateRuleTax($tax_id, $city_tax_id, $cost, $selling_price, $customer_id, $product_id){
        $product = Product::where('id',$product_id)->first();
        $data = [];
        if($tax_id != null){
        $taxrates = TaxRate::where('id',$tax_id)->first();

        $customer_state = Contact::where('id',$customer_id)->value('state');
        $is_nyc = Contact::where('id',$customer_id)->value('is_nyc');

        $data["tax"] = 0;
          if($taxrates){
             if($taxrates->tax_type == 1){
                $tax_percent = $taxrates->tax;
                $tax_value = $taxrates->taxvalue;
            } else{
                $tax_percent = $taxrates->tax_percent;
                $tax_value = $taxrates->city_tax_value;
            }
            $tax_type = 1;

              if($tax_percent!= null){
                  $tax = ($selling_price * $tax_percent)/100;
                  $tax_value = $tax_percent ;
                  $tax_type = 2; //fixed amount
              } else {
                $tax = $tax_value;
              }
              $data['tax_type'] = $tax_type;
              $data['tax_value'] = $tax_value;
              $data['tax'] = $tax;
              $data['rule'] = $taxrates->id;
              $data['name'] = $taxrates->name;
              $data['state'] = $taxrates->state;
              $data['every_item'] = $taxrates->every;
              $data['is_applicable'] = 1;
              $data['city_state'] = $taxrates->tax_type;
               if($taxrates->tax_type == 1){
                    if($taxrates->state !='' && $customer_state != $taxrates->state){
                      $data['is_applicable'] = 0;
                    }
                }
                // else if($taxrates->tax_type == 2 && $is_nyc != 1) {
                //     $data['is_applicable'] = 0;
                // }
            }
        }
                //City tax calculation
        $city_tax = 0;
        $first_item_value = 0;
        $second_item_value = 0;
        $city_every_item = '';
        $city_tax_name = '';
        $city_tax_type = 1;
        $customer_is_nyc = Contact::where('id',$customer_id)->value('is_nyc');
        // if($customer_is_nyc == 1){
            $taxrates_city = TaxRate::where('id',$city_tax_id)->first();
            if($taxrates_city && $taxrates_city->id){
            $city_tax_id = $taxrates_city->id;
            $city_tax_name = $taxrates_city->name;
                if($taxrates_city->city_tax_value != null){
                    $city_tax_percent = $taxrates_city->tax_percent;
                    $city_tax_value = $taxrates_city->city_tax_value;
                    if($city_tax_percent!= null){
                        $city_tax = ($selling_price * $city_tax_percent)/100;
                        $tax_value = $city_tax_percent;
                        $city_tax_type = 2;
                    } else {
                        $city_tax = $city_tax_value;
                        $city_tax_type = 1;
                    }
                } else{
                    $first_item_value =  $taxrates_city->first_item_value;
                    $second_item_value =  $taxrates_city->second_item_value;
                }
            $city_every_item = $taxrates_city->everycity;
            $first_item_value = $first_item_value;
            $second_item_value = $second_item_value;
          }
        // }
        $data['city_tax_id'] = $city_tax_id;
        $data['city_tax_type'] = $city_tax_type;
        $data['city_tax'] = $city_tax;
        $data['city_tax_name'] = $city_tax_name;
        $data['city_every_item'] = $city_every_item;
        $data['first_item_value'] = $first_item_value;
        $data['second_item_value'] = $second_item_value;
        return $data;
    }

    public function ProductActivityLog($type,$user_id,$product_id,$pro=''){
        $user = auth()->user();
        $message = rtrim($pro, ',');
        $date = \Carbon::now()->toDateTimeString();
        $change_message = '';
        if(!empty($message)){
            $change_message = $message.' was changed by '.$user->first_name.' at '.$date;
        }

        $log = new ProductActivityLog();
        $log->user_id = $user_id;
        $log->product_id = $product_id;
        $log->description = $type;
        $log->message = $change_message;
        $log->save();
    }

    public function ProductActivitiesLog($type,$user_id,$product_id,$product=''){

        $product_data = Product::find($product_id);
        // echo $product->not_for_selling;
        // echo "<pre>";
        // print_r($product_data);
        // die;

        $user = auth()->user();

        $log = new ProductActivitiesLog();
        $log->user_id = $user_id;
        $log->product_id = $product_id;
        $log->status = $type;

        if(isset($product_data->id)){
            if(!empty($product->name) && $product_data->name != $product->name){
                $log->old_name = $product_data->name;
                $log->new_name = $product->name;
            }
            if(!empty($product->item_code) &&  $product_data->item_code != $product->item_code){
                $log->old_item_code = $product_data->item_code;
                $log->new_item_code = $product->item_code;
            }

            if(!empty($product->sku) && $product_data->sku != $product->sku){
                $log->old_sku = $product_data->sku;
                $log->new_sku = $product->sku;
            }

            if($product_data->woocommerce_disable_sync != $product->woocommerce_disable_sync || isset($product->sync_with_woocommerce) ){
                $log->old_sync_status = $product_data->woocommerce_disable_sync;
                if(isset($product->sync_with_woocommerce)){
                    $log->new_sync_status = $product->sync_with_woocommerce;
                }else{
                    $log->new_sync_status = $product->woocommerce_disable_sync;
                }
            }

            if(!empty($product->is_inactive) && $product_data->is_inactive != $product->is_inactive){
                $log->old_active_status = $product_data->is_inactive;
                $log->new_active_status = $product->is_inactive;
            }

            if($product_data->not_for_selling != $product->not_for_selling || isset($product->forselling) && !isset($product->not_for_selling)){

                $log->old_not_for_selling = $product_data->not_for_selling;
                if(isset($product->forselling)){
                     $log->new_not_for_selling = 0;
                }else{
                     $log->new_not_for_selling = $product->not_for_selling;
                }
            }

            if(!empty($product->category_id) &&  $product_data->category_id != $product->category_id){
                $log->old_category_id = $product_data->category_id;
                $log->new_category_id = $product->category_id;
            }

            if(!empty($product->sub_category_id) &&  $product_data->sub_category_id != $product->sub_category_id){
                $log->old_sub_category_id = $product_data->sub_category_id;
                $log->new_sub_category_id = $product->sub_category_id;
            }

            if(!empty($product->brand_id) &&  $product_data->brand_id != $product->brand_id){
                $log->old_brand_id = $product_data->brand_id;
                $log->new_brand_id = $product->brand_id;
            }

        }

        $log->save();
    }


    // product tier price start
    public function AddOrUpdateProductTiersPrice($variation,$selling_price_tier1,$selling_price_tier2,$selling_price_tier3,$selling_price_tier4,$t1_last_prices = false,$t2_last_prices = false,$t3_last_prices = false,$t4_last_prices = false)
    {
        $variation_group_prices = [];
        $price_group_ids= array('68','69','70','80');

        foreach($price_group_ids as $pg_id)
        {
            $variation_group_price =
                VariationGroupPrice::where('variation_id', $variation->id)
                                    ->where('price_group_id', $pg_id)
                                    ->first();

            if($pg_id=='68')
            {
                if (empty($variation_group_price)) {
                    $variation_group_price = new VariationGroupPrice([
                        'variation_id' => $variation->id,
                        'price_group_id' => $pg_id
                    ]);
                }
                if($t1_last_prices){
                    $variation->t1_updated_at = date('Y-m-d H:i:s');
                    $variation->save();
                }
                $variation_group_price->price_inc_tax = $this->num_uf($selling_price_tier1);
            }
            if($pg_id=='69')
            {
                if (empty($variation_group_price)) {
                    $variation_group_price = new VariationGroupPrice([
                        'variation_id' => $variation->id,
                        'price_group_id' => $pg_id
                    ]);
                }

                if($t2_last_prices){
                    $variation->t2_updated_at = date('Y-m-d H:i:s');
                    $variation->save();
                }

                $variation_group_price->price_inc_tax = $this->num_uf($selling_price_tier2);
            }
            if($pg_id=='70')
            {
                if (empty($variation_group_price)) {

                    $variation_group_price = new VariationGroupPrice([
                        'variation_id' => $variation->id,
                        'price_group_id' => $pg_id
                    ]);
                }

                if($t3_last_prices){
                    $variation->t3_updated_at = date('Y-m-d H:i:s');
                    $variation->save();
                }

                $variation_group_price->price_inc_tax = $this->num_uf($selling_price_tier3);
            }
            if($pg_id=='80')
            {
                if (empty($variation_group_price)) {

                    $variation_group_price = new VariationGroupPrice([
                        'variation_id' => $variation->id,
                        'price_group_id' => $pg_id
                    ]);
                }

                if($t3_last_prices){
                    $variation->t3_updated_at = date('Y-m-d H:i:s');
                    $variation->save();
                }

                $variation_group_price->price_inc_tax = $this->num_uf($selling_price_tier4);
            }

            $variation_group_prices[] = $variation_group_price;
        }
        /*echo "<pre>";
        print_r($variation_group_prices);
        echo "</pre>";
        exit;*/

        if (!empty($variation_group_prices)) {
            $variation->group_prices()->saveMany($variation_group_prices);
            return true;
        }
    }
    // product tier price end

    //Jadoo Products list
    public function GetJadooProductlist()
    {
            $jadoo_products = JadooProduct::where('status','1')->get();
            //echo "<pre>";
            //print_r($jadoo_products); exit;
            $final_array = [];
            if (count($jadoo_products)>0) {
                foreach($jadoo_products as $key=>$value)
                {
                    $final_array['product'][$value->name] = $value->jadoo_name;
                    $final_array['barcode'][$value->name] = $value->barcode;
                    $final_array['itemcode'][$value->name] = $value->itemcode;
                    //$final_array['jadoo_name'][$key] = $value->jadoo_name;
                }
            }
            /*echo "<pre>";
            print_r($final_array); exit;*/
            return $final_array;
    }
}