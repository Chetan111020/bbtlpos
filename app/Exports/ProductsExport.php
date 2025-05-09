<?php

namespace App\Exports;

use App\Product;
use App\VariationLocationDetails;
use Maatwebsite\Excel\Concerns\FromArray;
use Carbon\Carbon;

class ProductsExport implements FromArray
{
    public function array():array {
        $business_id = request()->session()->get('user.business_id');
        
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        
        $products = Product::where('business_id', $business_id)
                    ->with(['brand', 'unit', 'category', 'sub_category', 'product_variations', 'product_variations.variations', 'product_tax', 'product_locations'])
                    ->select('products.*','main_image as image')
                    ->where('not_for_selling','<>',1)
                    ->where('srp','>',0)
                    ->where('sales_price','>',0)
                    // ->where('products.created_at', '>=', $threeMonthsAgo)
                    ->get();

        //set headers
        $products_array = [['ID','ITEM CODE','NAME', 'BRAND', 'UNIT', 'CATEGORY', 'SUB-CATEGORY', 'SKU (Leave blank to auto generate sku)', 'BARCODE TYPE', 'MANAGE STOCK (1=yes 0=No)', 'ALERT QUANTITY', 'EXPIRES IN', 'EXPIRY PERIOD UNIT (months/days)', 'APPLICABLE TAX', 'Selling Price Tax Type (inclusive or exclusive)', 'PRODUCT TYPE (single or variable)', 'VARIATION NAME (Keep blank if product type is single)', 'VARIATION VALUES (| seperated values & blank if product type if single)', 'VARIATION SKUs (| seperated values & blank if product type if single)', 'PURCHASE PRICE (Including tax)', 'PURCHASE PRICE (Excluding tax)', 'PROFIT MARGIN', 'SELLING PRICE', 'OPENING STOCK', 'OPENING STOCK LOCATION', 'EXPIRY DATE', 'ENABLE IMEI OR SERIAL NUMBER(1=yes 0=No)', 'WEIGHT', 'RACK', 'ROW', 'POSITION', 'IMAGE', 'PRODUCT DESCRIPTION', 'CUSTOM FIELD 1', 'CUSTOM FIELD 2', 'CUSTOM FIELD 3', 'CUSTOM FIELD 4', 'NOT FOR SELLING(1=yes 0=No)', 'PRODUCT LOCATIONS','WOOCOM SYNC DISABLE (1=yes 0=No)' , 'QUANTITY AVAILABLE']];
        foreach ($products as $product) {
            $product_variation = $product->product_variations->first();

            $product_variation_name = $product->type == 'variable' ? $product_variation->name : '';
            $variation_values = $product->type == 'variable' ? implode('|', $product_variation->variations->pluck('name')->toArray()) : '';
            $variation_skus = implode('|', $product_variation->variations->pluck('sub_sku')->toArray());
            $purchase_prices = implode('|', $product_variation->variations->pluck('dpp_inc_tax')->toArray());
            $purchase_prices_ex_tax = implode('|', $product_variation->variations->pluck('default_purchase_price')->toArray());
            $profit_percents = implode('|', $product_variation->variations->pluck('profit_percent')->toArray());
            $selling_prices = $product->tax_type == 'inclusive' ? implode('|', $product_variation->variations->pluck('sell_price_inc_tax')->toArray()) : implode('|', $product_variation->variations->pluck('default_sell_price')->toArray());
            $locations = implode(',', $product->product_locations->pluck('name')->toArray());

            $vars = $product_variation->variations->pluck('id')->toArray();
            $vld = VariationLocationDetails::where('location_id',4)
                ->where('variation_id',$vars[0] ?? 0)
            ->first();

            $rack_details = [];
            $row_details = [];
            $position_details = [];

            $product_arr = [
                $product->id,
                $product->item_code,
                $product->name,
                $product->brand->name ?? '',
                $product->unit->short_name ?? '',
                $product->category->name ?? '',
                $product->sub_category->name ?? '',
                $product->sku,
                $product->barcode_type,
                $product->enable_stock,
                $product->alert_quantity,
                $product->srp,
                $product->sales_price,
                $product->product_tax->name ?? '',
                $product->tax_type,
                $product->type,
                $product_variation_name,
                $variation_values,
                $variation_skus,
                $purchase_prices,
                $purchase_prices_ex_tax,
                $profit_percents,
                $selling_prices,
                '',
                '',
                '',
                $product->enable_sr_no,
                $product->weight,
                implode('|', $rack_details),
                implode('|', $row_details),
                implode('|', $position_details),
                $product->image_url,
                $product->product_description,
                $product->product_custom_field1,
                $product->product_custom_field2,
                $product->product_custom_field3,
                $product->product_custom_field4,
                $product->not_for_selling,
                $locations,
                $product->woocommerce_disable_sync,
                $vld->qty_available ?? 0
            ];

            $products_array[] = $product_arr;
        }

        return $products_array;
    }
}