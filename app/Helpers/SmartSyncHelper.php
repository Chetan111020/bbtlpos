<?php

namespace App\Helpers;

use App\Business;
use App\Contact;
use App\Models\SmartSyncTask;
use App\Models\SmartSyncValue;
use App\Product;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\VariationGroupPrice;
use App\VariationLocationDetails;
use Automattic\WooCommerce\Client;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Woocommerce\Utils\WoocommerceUtil;

class SmartSyncHelper
{
    public static $business_id = 4;
    public static $location_id = 4;
    public static $price_group_id = 68;

    public static function smartSyncManager($sync_type, $task = null){
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $client = self::getClient();
        if($sync_type == "delete"){
            self::printAndLog(" > Processing Delete Request");
            $deleted = self::deleteProducts($client, $task);
        }
        else if($sync_type == "inventory"){
            self::syncAllInventory($task);
        }
        else if($sync_type == "webstat"){
            self::getWebsiteProductStat($task);
        }
        else if($sync_type == "tierprice"){
            self::syncTierPrices($task);
            // self::syncCSP($task);
        }
        else if($sync_type == "reconsile"){
            self::reconsileAllProducts($task);
        }
        else{
            // legacy categories and brand sync
            $wooutil = new WoocommerceUtil(new TransactionUtil(),new ProductUtil());
            $wooutil->syncBrands(4, 6);
            $wooutil->syncCategories(4, 6);

            self::printAndLog(" > Processing Sync Request");
            $products = SmartSyncValue::productBaseQuery($sync_type)->get();
            if(!empty($products) && count($products) > 0){
                self::printAndLog(" > Processing Products");
                if($sync_type == 'failed'){
                    // uncomment when needed
                    // $products = self::resolveKnownErrors($products);
                }
                $prepared_products = self::prepareProducts($products);
                self::printAndLog(" > Syncing Products");

                if(!empty($task)){
                    $task->refresh();
                    if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                        $task->display_msg = self::printAndLog("The task has been aborted.");
                        $task->save();
                        return FALSE;
                    }
                }
                self::syncProducts($client, $prepared_products, 'create', $task);

                if(!empty($task)){
                    $task->refresh();
                    if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                        $task->display_msg = self::printAndLog("The task has been aborted.");
                        $task->save();
                        return FALSE;
                    }
                }
                self::syncProducts($client, $prepared_products, 'update', $task);
            }
            else{
                self::printAndLog(" > No Products To Sync");
            }
        }
    }

    public static function getClient(){
        $business = Business::find(self::$business_id);
        $woocommerce_api_settings = json_decode($business->woocommerce_api_settings);
        if (!empty($woocommerce_api_settings)) {
            $woocommerce = new Client(
                $woocommerce_api_settings->woocommerce_app_url,
                $woocommerce_api_settings->woocommerce_consumer_key,
                $woocommerce_api_settings->woocommerce_consumer_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v3',
                    'timeout' => 10000,
                    'verify_ssl' => false
                ]
            );

            return $woocommerce;
        }
        else{
            return null;
        }
    }

    public static function resolveKnownErrors($products){
        $resolved_products = collect();
        $max_request = 300;

        foreach($products as $product){
            // if($product->web_error_code == 'woocommerce_rest_product_invalid_id'){
            //     $product->woocommerce_product_id = null;
            //     $resolved_products->add($product);
            // }
            // else if($product->web_error_code == 'woocommerce_product_image_upload_error'){
            //     $product->image = "";
            //     $resolved_products->add($product);
            // }
            // else
            //  if($product->web_error_code == 'product_invalid_sku' && $max_request > 0){
            //     $client = self::getClient();
            //     $response = (array) $client->get('products',[ 'sku' => $product->id ]);
            //     $max_request--;
            //     if(!empty($response) && count($response) == 1 && !empty($response[0]->id)){
            //         $current_woo_id = $response[0]->id;
            //         $duplicates = Product::where('business_id', self::$business_id)
            //             ->where('woocommerce_product_id', $current_woo_id)
            //         ->count();

            //         if($duplicates == 0){
            //             $product->woocommerce_product_id = $current_woo_id;
            //             $product->web_error_code = null;
            //             $product->save();
            //             $resolved_products->add($product);
            //         }
            //         else{
            //             $product->web_error_code = 'product_invalid_sku__duplicate';
            //             $product->save();
            //         }
            //     }
            //     else{
            //         $product->web_error_code = 'product_invalid_sku__unresolved';
            //         $product->save();
            //     }
            // }
        }
        return $resolved_products;
    }

    public static function prepareProducts($products){
        $prepared_products = [];
        foreach($products as $product){
            try{
                // skip product if variation not found
                $first_variation = $product->variations->first();
                if (empty($first_variation)) {
                    continue;
                }

                //Set product category
                $product_cat = [];
                if (!empty($product->category->woocommerce_cat_id)) {
                    $product_cat[] = ['id' => $product->category->woocommerce_cat_id];
                }
                if (!empty($product->sub_category->woocommerce_cat_id)) {
                    $product_cat[] = ['id' => $product->sub_category->woocommerce_cat_id];
                }

                //Set product brand
                $product_brands = [];
                if (!empty($product->brand->woocommerce_brand_id)) {
                    $product_brands = [$product->brand->woocommerce_brand_id];
                }

                $sync_product = array();
                $sync_product['name'] = str_replace('"',"''",$product->name);
                $sync_product['type'] = $product->type == 'single' ? 'simple' : 'variable';
                $sync_product['categories'] = $product_cat;
                $sync_product['brands'] = $product_brands;
                $sync_product['description'] = str_replace('"',"'",$product->product_description);
                $sync_product['short_description'] = $product->sku;

                //assign quantity and price if single product
                if ($product->type == 'single') {
                    $manage_stock = false;
                    $qty_available = 0;
                    if ($product->enable_stock == 1 && $product->type == 'single') {
                        $manage_stock = true;
                        $variation_location_details = $first_variation->variation_location_details;
                        foreach ($variation_location_details as $vld) {
                            if ($vld->location_id == self::$location_id) {
                                $qty_available = $vld->qty_available;
                            }
                        }
                    }
                    $sync_product['manage_stock'] = $manage_stock;
                    $sync_product['stock_quantity'] = number_format((float) $qty_available, 0, ".", "");

                    $price = $first_variation->sell_price_inc_tax;
                    $tier_price = VariationGroupPrice::where('variation_id', $first_variation->id)->where('price_group_id',self::$price_group_id)->value('price_inc_tax');
                    if(!empty($tier_price)){
                        $price = $tier_price;
                    }

                    $sync_product['regular_price'] = number_format((float) $price, 2, ".", "");

                    $sync_product['sale_price'] = number_format((float) $price, 2, ".", "");
                    if($product->sales_price > 0){
                        if($product->srp > 0){
                            $sync_product['regular_price'] = number_format($product->srp,2,'.','');
                        }
                        $sync_product['sale_price'] = number_format($product->sales_price,2,'.','');
                    }

                }
                //set attributes for variable products
                if ($product->type == 'variable') {
                    $variation_attr_data = [];

                    foreach ($product->variations as $variation) {
                        if (!empty($variation->product_variation->variation_template->woocommerce_attr_id)) {
                            $woocommerce_attr_id = $variation->product_variation->variation_template->woocommerce_attr_id;
                            $variation_attr_data[$woocommerce_attr_id][] = $variation->name;
                        }
                    }

                    foreach ($variation_attr_data as $key => $value) {
                        $sync_product['attributes'][] = [
                            'id' => $key,
                            'variation' => true,
                            'visible'   => true,
                            'options' => $value
                        ];
                    }
                }

                if($product->out_of_stock == 1){
                    $sync_product['stock_status'] = "outofstock";
                    $sync_product['stock_quantity'] = 0;
                }

                if(SmartSyncValue::checkForFullStock($product->category_id, $product->brand_id)){
                    $sync_product['stock_status'] = "instock";
                    $sync_product['stock_quantity'] = SmartSyncValue::FULL_STOCK_VALUE;
                }

                if(!empty($product->woocommerce_media_id)){
                    $sync_product['images'] = [['id' => $product->woocommerce_media_id]];
                }
                else{
                    $image_url = $product->image_url;
                    if(!Str::contains($image_url, 'default') && Str::contains($product->image, '.')){
                        if (file_exists(public_path() . "/uploads" . $product->image)) {
                            $sync_product['images'] = [['src' => $product->image_url]];
                        }
                    }
                }

                if(empty($product->woocommerce_product_id)){
                    $sync_product['sku'] = $product->id;
                    $prepared_products['create'][] = $sync_product;
                    $prepared_products['raw_create'][] = $product;
                }
                else{
                    $sync_product['id'] = $product->woocommerce_product_id;
                    $prepared_products['update'][] = $sync_product;
                    $prepared_products['raw_update'][] = $product;
                }
            }
            catch(Exception $e){
                if(empty($product->woocommerce_product_id)){
                    $prepared_products['create_failed'][] = $product->id;
                }
                else{
                    $prepared_products['update_failed'][] = $product->id;
                }

                $product->web_error_code = 'failed_to_prepare';
                $product->save();

                Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message: failed_to_prepare - " . $product->id . " - " . $e->getMessage());
            }
        }
        return $prepared_products;
    }

    public static function syncProducts($client, $prepared_products, $op_type, $task = null){
        if($op_type != 'create' && $op_type != 'update'){
            self::printAndLog(' > Invalid opeartion type');
            self::printAndLog(' > Exiting...');
        }
        else{
            if(!empty($prepared_products[$op_type])){
                $total_count = count($prepared_products[$op_type]);
                self::printAndLog(self::getDecoreLine("Total products to ".$op_type." : ".$total_count,70), FALSE);
                $success_count = 0;
                $failed_count = 0;
                $unknown_count = 0;
                $failed_ids = [];
                $i = 0;
                foreach(array_chunk($prepared_products[$op_type],50) as $chunk){
                    $synced_ids = [];
                    try{
                        $response = (object) $client->post('products/batch',[
                            $op_type => $chunk
                        ]);
                        if(!empty($response->$op_type)){
                            $fc2 = 0;
                            $sc2 = 0;

                            foreach($response->$op_type as $woo_product){
                                $current_product = $prepared_products['raw_'.$op_type][$i];
                                if(!empty($woo_product->error)){
                                    $fc2++;
                                    $failed_ids[] = $woo_product->id;
                                    $current_product->web_error_code = $woo_product->error->code ?? 'unknown_error';
                                }
                                else{
                                    $sc2++;
                                    $synced_ids[] = $woo_product->id;
                                    $current_product->web_error_code = null;
                                    if($op_type == 'create'){
                                        $current_product->woocommerce_product_id = $woo_product->id;
                                    }
                                    $current_product->woocommerce_media_id = !empty($woo_product->images[0]->id) ? $woo_product->images[0]->id : null;
                                }
                                $current_product->save();
                                $i++;
                            }
                            $uc2 = count($chunk) - $sc2 - $fc2;
                            $success_count += $sc2;
                            $failed_count += $fc2;
                            $unknown_count += $uc2;
                            $completed_total = $success_count+$failed_count+$unknown_count;
                            $perc = number_format(($completed_total * 100) / $total_count);
                            self::printAndLog("$perc% Complete -- $sc2 Pass, $fc2 Fail, $uc2 Error");

                            if(!empty($task)){
                                $task->progress = $perc;
                                $task->display_msg = ucfirst($op_type) . " - $completed_total out of $total_count completed.";
                                $task->save();
                            }

                            if(!empty($synced_ids)){
                                $curr_time = new DateTime();
                                Product::whereIn('woocommerce_product_id',$synced_ids)->update(['synced_at' => $curr_time->modify('+1 minute')->format('Y-m-d H:i:s')]);
                            }
                        }
                    }
                    catch(Exception $e){
                        Log::emergency("SmartSync Error - File:" . $e->getFile(). "Line:" . $e->getLine(). "Message: ". $e->getMessage());
                        Log::emergency("Chunk Woocommerce Product IDs: " . implode(',',array_column($chunk,'id')));
                        $unknown_count += count($chunk);
                        $i += count($chunk);
                        self::printAndLog("Batch failed!");

                        if(!empty($task)){
                            $task->display_msg = ucfirst($op_type) . " - Something went wrong. Skipping Batch.";
                            $task->save();
                        }
                    }

                    if(!empty($task)){
                        $task->refresh();
                        if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                            $task->display_msg = self::printAndLog("The task has been aborted.");
                            $task->save();
                            break;
                        }
                    }
                }

                $message = self::getDecoreLine("Sync Completed",70);
                $message .= "\n$success_count Pass, $failed_count Fail, $unknown_count Error";
                $message .= self::getDecoreLine("--------------",70);
                self::printAndLog($message, FALSE);

                if(!empty($failed_ids)){
                    Log::emergency("Failed Woocommerce Product IDs: " . implode(',',$failed_ids));
                }
            }
        }
    }

    public static function deleteProducts($client, $task = null){
        $products = SmartSyncValue::productBaseQuery('delete')
            ->pluck('woocommerce_product_id')
        ->toArray();

        $total_count = count($products);
        self::printAndLog(' > Total Products To Delete : '.$total_count);
        $deleted_products = [];
        $deleted_total = 0;
        foreach (array_chunk($products, 100) as $chunk) {
            $response = $client->post('products/batch', [
                'delete' => $chunk
            ]);
            if (!empty($response->delete)) {
                foreach ($response->delete as $item) {
                    if ($item->id != 0) {
                        $deleted_products[] = $item->id;
                    }
                }
                $deleted_total = count($deleted_products);
                self::printAndLog(" > $deleted_total out of $total_count deleted.");

                if(!empty($task)){
                    $task->progress = round(($deleted_total * 100)/$total_count,1);
                    $task->display_msg = "Delete - $deleted_total out of $total_count completed.";
                    $task->save();
                }
            }

            if(!empty($task)){
                $task->refresh();
                if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                    $task->display_msg = self::printAndLog("The task has been aborted.");
                    $task->save();
                    break;
                }
            }
        }
        if(!empty($task) && $deleted_total == 0){
            $task->progress = 100;
            $task->display_msg = "No products to delete. Sync completed.";
            $task->save();
        }
        self::printAndLog(" > Sync completed\n");

        $products = Product::whereIn('woocommerce_product_id',$deleted_products)
        ->update([
            'synced_at' => null,
            'web_error_code' => null,
            'woocommerce_product_id' => null,
            'woocommerce_media_id' => null
        ]);

        return count($deleted_products);
    }

    public static function getWebsiteProductStat($task = null){
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        self::printAndLog(" > Website products count started");

        $business = Business::find(self::$business_id);
        $woocommerce_api_settings = json_decode($business->woocommerce_api_settings);
        $client = self::getClient($woocommerce_api_settings);

        // $woo_products_to_delete = [3431,72705,78881,72765,72767,78951,78955,78959,78963,78967,78971,78976];
        // $response = $client->post('products/batch', [
        //     'delete' => $woo_products_to_delete
        // ]);

        $response = (object) $client->get('reports/products/totals');
        $new_count = 0;
        foreach($response as $item){
            if($item->slug == 'simple'){
                $new_count = $item->total;
            }
        }
        $keystore1 = SmartSyncValue::setSmartValue('total_website_products', $new_count);

        self::printAndLog(" > Fetching website products");

        $mismatch = [];
        $web_products = [];
        $count = 100;
        $page = 1;
        $diff_status = "";
        while($count == 100 && $page < 200){
            $response2 = (object) $client->get('products',[
                '_fields' => 'id,sku,status',
                'per_page' => '100',
                'page' => $page,
                'type' => 'simple'
            ]);
            $page++;
            $count = 0;
            foreach($response2 as $item){
                $count++;
                $web_products[] = $item->id;
                // $erp_products = Product::where('id',$item->sku ?? '-1')->orWhere('woocommerce_product_id',$item->id)->count();
                // if($erp_products > 1){
                //     $mismatch[] = $item->id;
                // }
                if($item->status != "publish"){
                    $diff_status .= $item->id . "-" . $item->status . ",";
                }
            }
        }

        self::printAndLog(" > Filtering website only products");

        $products = Product::select('woocommerce_product_id')->where(function($query){
            $query->whereNotNull('woocommerce_product_id')
                ->orwhere('not_for_selling', '<>', 1);
        })->get();
        $web_only_products = array_diff($web_products,array_column($products->toArray(),'woocommerce_product_id'));

        $keystore2 = SmartSyncValue::setSmartValue('website_only_product_ids', implode(',',$web_only_products));
        $keystore3 = SmartSyncValue::setSmartValue('website_only_products', count($web_only_products));
        $keystore4 = SmartSyncValue::setSmartValue('mismatch_assigned', implode(',',$mismatch));
        $keystore5 = SmartSyncValue::setSmartValue('website_product_status', $diff_status);
        $keystore6 = SmartSyncValue::setSmartValue('total_website_products_counted', count($web_products));

        self::printAndLog(" > Sync completed");

        if(!empty($task)){
            $task->progress = 100;
            $task->display_msg = "Website products count saved.";
            $task->save();
        }
    }

    public static function printAndLog($message, $new_line = TRUE){
        $prefix_msg = $new_line ? "\n" : "";
        echo $prefix_msg.$message;
        Log::info($prefix_msg.$message);
        return $message;
    }

    public static function getDecoreLine($str,$len,$ch = '-'){
        $new_str = $str;
        $str_len = strlen($new_str);
        $padd_len = $len - $str_len;
        if($padd_len > 0){
            $pre_padd_len = floor($padd_len / 2) - 1;
            $post_padd_len = ceil($padd_len / 2) - 1;

            $pre_str = "\n";
            for($i=0;$i<$pre_padd_len;$i++){
                $pre_str .= $ch;
            }

            $post_str = "";
            for($i=0;$i<$post_padd_len;$i++){
                $post_str .= $ch;
            }

            $new_str = $pre_str . " " . $str . " " . $post_str;
        }
        return $new_str;
    }

    // customized to use tier 1 price in website
    public static function syncAllInventory($task = null){
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        self::printAndLog(" > Inventory sync started");

        // $products = Product::join('variations as v','v.product_id','=','products.id')
        //     ->leftJoin('variation_location_details as vld','vld.variation_id','=','v.id')
        //     ->where('products.type','single')
        //     ->where('products.business_id',self::$business_id)
        //     ->where('vld.location_id',self::$location_id)
        //     ->where('products.not_for_selling','<>',1)
        //     ->where('products.woocommerce_disable_sync','<>',1)
        //     ->whereNotNull('products.woocommerce_product_id')
        //     ->select(
        //         'products.id as pid',
        //         'products.woocommerce_product_id as woo_id',
        //         'v.sell_price_inc_tax as regular_price',
        //         'vld.qty_available as stock_quantity',
        //     )
        //     ->orderBy('products.id')
        // ->get();

        $products = Product::join('variations as v','v.product_id','=','products.id')
            ->leftJoin('variation_location_details as vld','vld.variation_id','=','v.id')
            ->leftJoin('variation_group_prices as vgp','vgp.variation_id','=','v.id')
            ->where('products.type','single')
            ->where('products.business_id',self::$business_id)
            ->where('vld.location_id',self::$location_id)
            ->where('vgp.price_group_id', self::$price_group_id)
            ->where('products.not_for_selling','<>',1)
            ->where('products.woocommerce_disable_sync','<>',1)
            ->whereNotNull('products.woocommerce_product_id')
            ->select(
                'products.id as pid',
                'products.out_of_stock',
                'products.srp',
                'products.sales_price',
                'products.category_id',
                'products.brand_id',
                'products.woocommerce_product_id as woo_id',
                'v.sell_price_inc_tax as regular_price',
                'vld.qty_available as stock_quantity',
                'vgp.price_inc_tax as tier_price',
            )
            ->orderBy('products.id')
        ->get();

        self::printAndLog(" > Preparing products");

        $prepared_products = [];
        foreach($products as $product){
            if(!empty($product->woo_id) && !empty($product->regular_price)){
                $tmp = [];
                $tmp['id'] = $product->woo_id;

                if(!empty($product->tier_price)){
                    $tmp['regular_price'] = number_format($product->tier_price,2,'.','');
                    $tmp['sale_price'] = number_format($product->tier_price,2,'.','');
                }
                else{
                    $tmp['regular_price'] = number_format($product->regular_price,2,'.','');
                    $tmp['sale_price'] = number_format($product->regular_price,2,'.','');
                }

                if($product->sales_price > 0){
                    if($product->srp > 0){
                        $tmp['regular_price'] = number_format($product->srp,2,'.','');
                    }
                    $tmp['sale_price'] = number_format($product->sales_price,2,'.','');
                }

                $tmp['stock_quantity'] = number_format($product->stock_quantity ?? 0,0,'.','');
                if(!empty($product->out_of_stock)){
                    $tmp['stock_status'] = "outofstock";
                    $tmp['stock_quantity'] = 0;
                }
                else{
                    $tmp['stock_status'] = "instock";
                }

                if(SmartSyncValue::checkForFullStock($product->category_id, $product->brand_id)){
                    $tmp['stock_status'] = "instock";
                    $tmp['stock_quantity'] = SmartSyncValue::FULL_STOCK_VALUE;
                }

                $prepared_products[] = $tmp;
            }
        }

        self::printAndLog(" > Syncing inventory");

        if(count($prepared_products) > 0){
            $success_count = 0;
            $failed_count = 0;
            $unknown_count = 0;
            $failed_ids = [];

            $business = Business::find(self::$business_id);
            $woocommerce_api_settings = json_decode($business->woocommerce_api_settings);
            $client = self::getClient($woocommerce_api_settings);
            $total_count = count($prepared_products);

            self::printAndLog(self::getDecoreLine("Total Products : ".$total_count,70), FALSE);

            foreach(array_chunk($prepared_products,100) as $chunk){
                try{
                    $response = (object) $client->post('products/batch',[
                        'update' => $chunk
                    ]);

                    if(!empty($response->update)){
                        $fc2 = 0;
                        $sc2 = 0;
                        foreach($response->update as $updated_stocks){
                            if(empty($updated_stocks->error)){
                                $sc2++;
                            }
                            else{
                                $fc2++;
                                $failed_ids[] = $updated_stocks->id;
                            }
                        }
                        $uc2 = count($chunk) - $sc2 - $fc2;

                        $success_count += $sc2;
                        $failed_count += $fc2;
                        $unknown_count += $uc2;

                        $completed_total = $success_count+$failed_count+$unknown_count;
                        $perc = round(($completed_total * 100) / $total_count,1);

                        self::printAndLog("$perc% Complete -- $sc2 Pass, $fc2 Fail, $uc2 Error");

                        if(!empty($task)){
                            $task->progress = $perc;
                            $task->display_msg = "Inventory - $completed_total out of $total_count completed.";
                            $task->save();
                        }

                    }
                }
                catch(Exception $e){
                    Log::emergency("SmartSync Error - File:" . $e->getFile(). "Line:" . $e->getLine(). "Message: ". $e->getMessage());
                    Log::emergency("Chunk Woocommerce Product IDs: " . implode(',',array_column($chunk,'id')));
                    $unknown_count += count($chunk);
                    self::printAndLog("Batch failed!");

                    if(!empty($task)){
                        $task->display_msg = "Inventory - Something went wrong. Skipping Batch.";
                        $task->save();
                    }
                }

                if(!empty($task)){
                    $task->refresh();
                    if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                        $task->display_msg = self::printAndLog("The task has been aborted.");
                        $task->save();
                        break;
                    }
                }
            }

            $message = self::getDecoreLine("Sync Completed", 70);
            $message .= "\nTotal Products: $total_count -- $success_count Pass, $failed_count Fail, $unknown_count Error";
            $message .= self::getDecoreLine("--------------", 70);
            self::printAndLog($message, FALSE);

            if(!empty($failed_ids)){
                Log::emergency("Failed Woocommerce Product IDs: " . implode(',',$failed_ids));
            }
        }
        else{
            self::printAndLog(" > No products to sync");
        }
        self::printAndLog(" > Sync completed\n");
    }

    public static function deleteSelectedProducts($ids, $task = null){
        $products = Product::whereIn('id',$ids)
            ->pluck('woocommerce_product_id')
        ->toArray();

        $total_count = count($products);
        self::printAndLog(' > Total Products To Delete : '.$total_count);
        $deleted_products = [];
        $deleted_total = 0;
        $client = Self::getClient();
        foreach (array_chunk($products, 100) as $chunk) {
            $response = $client->post('products/batch', [
                'delete' => $chunk
            ]);
            if (!empty($response->delete)) {
                foreach ($response->delete as $item) {
                    if ($item->id != 0) {
                        $deleted_products[] = $item->id;
                    }
                }
                $deleted_total = count($deleted_products);
                self::printAndLog(" > $deleted_total out of $total_count deleted.");

                if(!empty($task)){
                    $task->progress = round(($deleted_total * 100)/$total_count,1);
                    $task->display_msg = "Delete - $deleted_total out of $total_count completed.";
                    $task->save();
                }
            }

            if(!empty($task)){
                $task->refresh();
                if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                    $task->display_msg = self::printAndLog("The task has been aborted.");
                    $task->save();
                    break;
                }
            }
        }
        if(!empty($task) && $deleted_total == 0){
            $task->progress = 100;
            $task->display_msg = "No products to delete. Sync completed.";
            $task->save();
        }
        self::printAndLog(" > Sync completed\n");

        $products = Product::whereIn('woocommerce_product_id',$deleted_products)
        ->update([
            'synced_at' => null,
            'web_error_code' => null,
            'woocommerce_product_id' => null,
            'woocommerce_media_id' => null
        ]);

        return count($deleted_products);
    }

    public static function syncTierPrices($task = null){
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        self::printAndLog(" > Tier prices sync started");

        $tiers = [
            68 => 'Tier1',
            69 => 'Tier2',
            70 => 'Tier3'
        ];

        $client = self::getClient();
        foreach($tiers as $tier_id => $tier_name){
            $gsp_data = [];
            $products = DB::table('products as p')
                ->join('variations as v','p.id','=','v.product_id')
                ->leftJoin('variation_group_prices as vgp', function($join) use($tier_id){
                    $join->on('v.id','=','vgp.variation_id')
                        ->where('vgp.price_group_id',$tier_id);
                })
                ->select(
                    'p.woocommerce_product_id as woo_id',
                    DB::raw('COALESCE(vgp.price_inc_tax, v.sell_price_inc_tax) as group_price')
                )
                ->whereNotNull('p.woocommerce_product_id')
                // ->whereNull('p.web_error_code')
            ->get();

            foreach($products as $product){
                $temp = [
                    "product_id"=> $product->woo_id,
                    "group_name"=> $tier_name,
                    "min_qty"=> 1,
                    "discount_type"=> "flat",
                    "gsp_price"=> round($product->group_price, 4),
                ];
                $temp['hash'] = md5(implode("|", $temp));
                $gsp_data[] = $temp;
            }

            try{
                $response = (object) $client->post('csp/gsp',[
                    'gsp_data' => $gsp_data
                ]);


                if(!empty($task)){
                    $task->progress += 33;
                    $task->display_msg = "Syncing tier prices...";
                    $task->save();
                    $task->refresh();
                    if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                        $task->display_msg = self::printAndLog("The task has been aborted.");
                        $task->save();
                        break;
                    }
                }

            }
            catch(Exception $e){
                Log::emergency("SmartSync Error - File:" . $e->getFile(). "Line:" . $e->getLine(). "Message: ". $e->getMessage());

                if(!empty($task)){
                    $task->progress += 33;
                    $task->display_msg = "Syncing tier prices... ( Something went wrong )";
                    $task->save();
                    $task->refresh();
                    if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                        $task->display_msg = self::printAndLog("The task has been aborted.");
                        $task->save();
                        break;
                    }
                }
            }

        }

        self::printAndLog(" > Tier price sync complete \n");

        if(!empty($task)){
            $task->progress = 100;
            $task->display_msg = "Tier price sync complete.";
            $task->save();
        }

    }

    public static function syncCSP($task = null){
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        self::printAndLog(" > CSP sync started");
        $client = self::getClient();

        $customers = Contact::where('type','customer')->whereNotNull('woocommerce_user_id')->where('id',1371)->get();
        $total_customers = count($customers);
        $total_count = 0;
        foreach($customers as $customer){
            $csp_data = [];
            $customer_group_id = $customer->customer_group_id ?? 0;

            $products = DB::table('products as p')
                ->join('variations as v','p.id','=','v.product_id')
                ->leftJoin('variation_group_prices as vgp', function($join) use($customer_group_id){
                    $join->on('v.id','=','vgp.variation_id')
                        ->where('vgp.price_group_id',$customer_group_id);
                })
                ->select(
                    'v.id as var_id',
                    'v.sell_updated_at as last_variation_update',
                    'p.woocommerce_product_id as woo_id',
                    DB::raw('COALESCE(vgp.price_inc_tax, v.sell_price_inc_tax) as group_price')
                )
                ->whereNotNull('p.woocommerce_product_id')
                ->whereNull('p.web_error_code')
            ->get();

            foreach($products as $product){
                $last_sell = DB::table('transaction_sell_lines as tsl')
                    ->join('transactions as t','t.id','=','tsl.transaction_id')
                    ->where('t.type','sell')
                    ->where('t.status','final')
                    ->where('t.contact_id',$customer->id)
                    ->where('tsl.variation_id',$product->var_id)
                    ->when(!empty($product->last_variation_update), function($query) use($product){
                        $query->where('t.transaction_date','>=',$product->last_variation_update);
                    })
                    ->orderBy('tsl.id','desc')
                ->first();

                $csp_price = $product->group_price;
                if(isset($last_sell) && !empty($last_sell->unit_price)){
                    $csp_price = $last_sell->unit_price;
                }

                $temp = [
                    "product_id"=> $product->woo_id,
                    "customer_id"=> $customer->woocommerce_user_id,
                    "min_qty"=> 1,
                    "discount_type"=> "flat",
                    "csp_price"=> round($csp_price, 4),
                ];
                $temp['hash'] = md5(implode("|", $temp));
                $csp_data[] = $temp;
            }

            try{
                $response = (object) $client->post('csp/usp',[
                    'csp_data' => $csp_data
                ]);

                $total_count++;

                if(!empty($task)){
                    $task->progress += round(($total_count * 100) / $total_customers, 1);
                    $task->display_msg = self::printAndLog("Syncing customer prices..." . $task->progress . " % Done");
                    $task->save();
                    $task->refresh();
                    if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                        $task->display_msg = self::printAndLog("The task has been aborted.");
                        $task->save();
                        break;
                    }
                }

            }
            catch(Exception $e){
                Log::emergency("SmartSync Error - File:" . $e->getFile(). "Line:" . $e->getLine(). "Message: ". $e->getMessage());

                if(!empty($task)){
                    $task->progress += 33;
                    $task->display_msg = "Syncing customer prices... ( Something went wrong )";
                    $task->save();
                    $task->refresh();
                    if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                        $task->display_msg = self::printAndLog("The task has been aborted.");
                        $task->save();
                        break;
                    }
                }
            }
        }

        self::printAndLog(" > CSP sync complete \n");

        if(!empty($task)){
            $task->progress = 100;
            $task->display_msg = "CSP sync complete.";
            $task->save();
        }

    }

    public static function reconsileAllProducts($task = null){
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $ids = [];
        if(isset($task)){
            $ids = $task->getProductsFromTask();
        }

        $business_id = 4;
        $products = Product::join('variations', 'variations.product_id', '=', 'products.id')
            ->select('products.id as p_id', 'variations.id as variation_id')
            ->where('business_id',$business_id)
            ->where('not_for_selling','<>', 1)
            ->when(count($ids) > 0, function($q) use($ids){
                $q->whereIn('products.id',$ids);
            })
        ->get();

        // dd(count($products));

        $product_util = new ProductUtil();

        $total_products = count($products);
        $completed = 0;

        self::printAndLog(self::getDecoreLine("Reconsiling ".$total_products." Products", 70), FALSE);
        foreach($products as $product){
            $stock_history = $product_util->getVariationStockHistory($business_id, $product->variation_id, 4);
            if(count($stock_history)){
                // VariationLocationDetails::where('product_id',$product->p_id)->update(['qty_available'=>$stock_history[0]['stock']]);

                $inv = $stock_history[0]['stock'];
                $location_id = 4;
                $variation = Variation::where('product_id',$product->p_id)->first();
                $details = VariationLocationDetails::where('variation_id', $variation->id)
                    ->where('location_id', $location_id)
                ->first();
                if (empty($details)) {
                    $details = VariationLocationDetails::create([
                        'product_id' => $product->p_id,
                        'location_id' => $location_id,
                        'variation_id' => $variation->id,
                        'product_variation_id' => $variation->product_variation_id,
                        'qty_available' => $inv
                    ]);
                }
                else{
                    $details->qty_available = $inv;
                    $details->save();
                }

                Product::where('id',$product->p_id)->update(['is_qty_updated'=>1]);
            }
            else{
                 Product::where('id',$product->p_id)->update(['is_qty_updated'=>2]);
            }
            $completed++;
            if($completed % 25 == 0){
                self::printAndLog('Products Reconsile - '. round(($completed * 100) / $total_products, 2) . " % Complete");

                if(!empty($task)){
                    $task->progress = round(($completed * 100) / $total_products, 2);
                    $task->display_msg = "Products Reconsile - $completed out of $total_products completed.";
                    $task->save();
                }
            }

            if(!empty($task)){
                $task->refresh();
                if($task->status == SmartSyncTask::TASK_STATUS_ABORTED){
                    $task->display_msg = self::printAndLog("The task has been aborted.");
                    $task->save();
                    break;
                }
            }
        }
        if(!empty($task)){
            $task->progress = 100;
            $task->display_msg = "Reconsile ".$total_products." Products Completed.";
            $task->save();
        }

        self::printAndLog(self::getDecoreLine('Products Reconsile Completed', 70), FALSE);
    }

    // public static function reconsilePurchaseProducts($id, $task=null)
    // {
    //     ini_set('memory_limit', '-1');
    //     ini_set('max_execution_time', 0);

    //     $business_id = 4;
    //     $products = PurchaseLine::join('products AS p','purchase_lines.product_id','=','p.id')
    //                 ->join('variations AS variations','purchase_lines.variation_id','=','variations.id')
    //                 ->where('purchase_lines.transaction_id', $id)
    //                 ->where('p.business_id',$business_id)
    //                 ->where('p.not_for_selling','<>', 1)
    //                 ->select('p.id as p_id', 'variations.id as variation_id')
    //                 ->get();

    //     $product_util = new ProductUtil();

    //     $total_products = count($products);
    //     $completed = 0;

    //     self::printAndLog(self::getDecoreLine("Reconsiling ".$total_products." Products", 70), FALSE);
    //     foreach($products as $product){
    //         $stock_history = $product_util->getVariationStockHistory($business_id, $product->variation_id, 4);
    //         if(count($stock_history)){
    //             VariationLocationDetails::where('product_id',$product->p_id)->update(['qty_available'=>$stock_history[0]['stock']]);
    //             Product::where('id',$product->p_id)->update(['is_qty_updated'=>1]);
    //         }
    //         else{
    //              Product::where('id',$product->p_id)->update(['is_qty_updated'=>2]);
    //         }
    //         $completed++;
    //         if($completed % 25 == 0){
    //             self::printAndLog('Products Reconsile - '. round(($completed * 100) / $total_products, 2) . " % Complete");
    //         }
    //     }
    //     self::printAndLog(self::getDecoreLine('Products Reconsile Completed', 70), FALSE);
    // }
}