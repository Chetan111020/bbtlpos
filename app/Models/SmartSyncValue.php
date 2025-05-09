<?php

namespace App\Models;

use App\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SmartSyncValue extends Model
{
    //
    protected $fillable = [
        'smart_key',
        'smart_value'
    ];

    const FULL_STOCK_CATEGORIES = [597,616,671,679,593,691,699,688,690,678,675,672,674,677,673,693,685,676,680,659,723,739,722,719,729,645];
    const FULL_STOCK_BRANDS = [682,81,809,39,707];
    const FULL_STOCK_VALUE = 1000;

    public static function getSmartValue($smart_key, $default_value = 0){
        return self::where('smart_key', $smart_key)->first()->smart_value ?? $default_value;
    }

    public static function getValues(array $keys, $default_value = 0){
        $smart_sync_values = self::whereIn('smart_key', $keys)->get();
        $values_array = [];
        foreach($smart_sync_values as $smart_sync_value){
            $values_array[$smart_sync_value->smart_key] = $smart_sync_value->smart_value;
        }

        $formatted_values = [];
        foreach($keys as $key){
            $formatted_values[$key] = $default_value;
            if(!empty($values_array[$key])){
                $formatted_values[$key] = $values_array[$key];
            }
        }
        return $formatted_values;
    }

    public static function setSmartValue($smart_key, $smart_value){
        if(!empty($smart_key) && isset($smart_value)){
            self::updateOrCreate(
                ['smart_key' => $smart_key],
                ['smart_value' => $smart_value]
            );
        }
        return $smart_value;
    }

    public static function productBaseQuery($sync_type, $business_id = 4){

        if($sync_type == 'delete'){
            $products = Product::where(function($query){
                $query->where('not_for_selling','=',1)
                    ->orWhere('woocommerce_disable_sync','=',1);
            })
            ->whereNotNull('woocommerce_product_id');
            return $products;
        }

        $products = Product::select('*', 'main_image as image')
            ->where('business_id', $business_id)
            ->where('not_for_selling','<>',1)
            ->where('woocommerce_disable_sync','<>',1)
            ->whereIn('type', ['single', 'variable'])
        ->where(function($query) use($sync_type){
            if($sync_type == 'all'){
                $query->where(function($query){
                    $query->whereRaw('synced_at < updated_at')
                        ->orWhereNull('synced_at');
                })->whereNull('web_error_code');
            }
            else if($sync_type == 'new'){
                $query->whereNull('woocommerce_product_id')
                    ->whereNull('web_error_code');
            }
            else if($sync_type == 'failed'){
                $query->whereNotNull('web_error_code');
            }
            else if($sync_type == 'forced'){
                // no conditions on force sync
            }
            else{
                $query->where('id','<>','id');
            }
        });
        return $products;
    }

    public static function getEstimatedTime($subject_type, $subject_params){
        $estimated_dur = 5; //minutes
        if($subject_type == "SmartSync:Products"){
            $sync_types = ['new', 'all', 'failed', 'delete'];
            foreach($sync_types as $sync_type){
                if(Str::contains($subject_params, $sync_type)){
                    $sync_count = SmartSyncValue::productBaseQuery($sync_type)->count();
                    $estimated_dur = (ceil($sync_count / 100) * 5);
                    break;
                }
            }
        }
        return $estimated_dur;
    }

    public static function checkForFullStock($category_id, $brand_id){
        if(empty($category_id) || empty($brand_id)){
            return false;
        }
        return (in_array($category_id, self::FULL_STOCK_CATEGORIES) && in_array($brand_id, self::FULL_STOCK_BRANDS));
    }
}