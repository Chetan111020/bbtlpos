<?php

namespace App\Models;
use App\User;
use App\Product;
use App\Transaction;
use App\TransactionSellLine;
use App\Variation;
use App\VariationGroupPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class NeatTxnProLog extends Model
{
    use SoftDeletes;

    protected $table = 'neat_txn_pro_logs';
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'neat_txn_id',
        'txn_id',
        'product_id',
        'activity_title',
        'qty',
        'new_qty',
        'price',
        'new_price',
        'pre_loaded_price',
        'tax_amount',
        'new_tax_amount',
        'is_last_selling_price',
        'price_difference',
        'new_price_difference'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $attributes = [
    ];

 public static function logActivity($neat_txn_id, $txn_id, $products, $previous_products_record = [])
    {
        try {
            Log::info("Processing products for txn_id: $txn_id");

            // Store previous logs in an array for easier lookup
            $existingLogMap = [];
            foreach ($previous_products_record as $log) {
                $existingLogMap[$log['product_id']] = $log;
            }

            // Track products to identify removals
            $currentProductIds = array_column($products, 'product_id');

            foreach ($products as $product) {
                $productId = $product['product_id'];
                $existingLog = $existingLogMap[$productId] ?? null;

                // Determine the activity title based on the presence of the product in previous records
                $activity_title = $existingLog ? 'Edited' : 'Created';
                
                switch ($activity_title) {
                    case 'Created':
                        // Handle product creation logic here
                        static::create([
                            'neat_txn_id' => $neat_txn_id,
                            'txn_id' => $txn_id,
                            'product_id' => $productId,
                            'activity_title' => 'Added',
                            'qty' => $product['quantity'],
                            'new_qty' => null,
                            'price' => $product['unit_price'],
                            'new_price' => null,
                            'tax_amount' => $product['pos_line_tax_amount'],
                            'new_tax_amount' => null ,
                            'price_difference' => static::calculatePriceDifference($txn_id, $productId, $product['unit_price']),
                            'new_price_difference' => null,
                            'pre_loaded_price' => $product['pre_loaded_unit_price'] ?? null,
                            'is_last_selling_price' => ((!empty($product['last_sell_price']) && !empty($product['unit_price']) && round($product['unit_price'], 2) == round($product['last_sell_price'], 2)) ? 1 : 0) ?? null
                        ]);
                        break;

                    case 'Edited':
                        if ($existingLog) {
                            // Determine if the product is changed
                            $isChanged = false;
                            $changes = [];

                            if ($existingLog['quantity'] != $product['quantity']) {
                                $changes['new_qty'] = $product['quantity'];
                                $changes['qty'] = $existingLog['quantity'];
                                $changes['price'] = $existingLog['unit_price'];
                                $isChanged = true;
                            }
                            if (round($existingLog['unit_price'],2) != round($product['unit_price'],2)) {
                                $changes['new_price'] = $product['unit_price'];
                                $changes['price'] = $existingLog['unit_price'];
                                $changes['qty'] = $existingLog['quantity'];
                                $isChanged = true;
                            }
                            if (round($existingLog['pos_line_tax_amount'],2) != round($product['pos_line_tax_amount'],2)) {
                                $changes['new_tax_amount'] = round($product['pos_line_tax_amount'],2);
                                $changes['tax_amount'] = round($existingLog['pos_line_tax_amount'],2);
                                $changes['qty'] = $existingLog['quantity'];
                                $changes['price'] = $existingLog['unit_price'];
                                $isChanged = true;
                            }
                            if ($isChanged) {
                                // Log the changes
                                $changes['new_price_difference'] = static::calculatePriceDifference($txn_id, $productId, $product['unit_price']);
                                $changes['price_difference'] = static::calculatePriceDifference($txn_id, $productId, $existingLog['unit_price']);
                                $changes['pre_loaded_price'] = $product['pre_loaded_unit_price'] ?? null;
                                $changes['neat_txn_id'] = $neat_txn_id;
                                $changes['txn_id'] = $txn_id;
                                $changes['product_id'] = $productId;
                                $changes['activity_title'] = 'Changed';
                             // $changes['is_last_selling_price'] = ((!empty($existingLog['is_lsp']) && $existingLog['is_lsp'] == 1) ? 2 : 0) ?? null;
                                static::create($changes);
                            }
                        }
                        break;
                }
            }

            // Handle removed products
            foreach ($previous_products_record as $existingLog) {
                if (!in_array($existingLog['product_id'], $currentProductIds)) {
                    // Log the removed product
                    static::create([
                        'neat_txn_id' => $neat_txn_id,
                        'txn_id' => $txn_id,
                        'product_id' => $existingLog['product_id'],
                        'activity_title' => 'Removed',
                        'qty' => $existingLog['quantity'],
                        'price' => $existingLog['unit_price'],
                        'pre_loaded_price' => $product['pre_loaded_unit_price'] ?? null,
                      //'is_last_selling_price' => $existingLog['is_lsp'] ?? null,
                        'tax_amount' => $existingLog['pos_line_tax_amount'],
                        'new_qty' => null,
                        'new_price' => null,
                        'new_tax_amount' => null,
                        'price_difference' => null,
                        'new_price_difference' => null
                    ]);
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error processing products for txn_id: $txn_id", [
                'exception' => $e->getMessage(),
                'neat_txn_id' => $neat_txn_id,
                'txn_id' => $txn_id,
                'products' => $products
            ]);
            return false;
        }
    }

     public static function logIndividualActivity($neat_txn_id,  $activity_title, $activity_by, $txn_id, $price, $product_id, $previous_qty, $new_qty, $previous_tax, $new_tax)
    {
        // Check if a log entry for the same product already exists
        $existing_log = self::where('neat_txn_id', $neat_txn_id)
            ->where('txn_id', $txn_id)
            ->where('product_id', $product_id)
            ->first();
    
        if ($existing_log && $activity_title == 'Edited While Picking' && $activity_by == Auth::id()) {
            // Update the existing log entry with the new quantity
            $existing_log->new_qty = $new_qty;
            $existing_log->new_tax_amount = $new_tax;
            $existing_log->save();
        } else {
            // Create a new log entry if none exists
            return self::create([
                'neat_txn_id' => $neat_txn_id,
                'txn_id' => $txn_id,
                'product_id' => $product_id,
                'price' => $price,
                'activity_title' => 'Changed',
                'qty' => $previous_qty,
                'new_qty' => $new_qty,
                'tax_amount' => $previous_tax,
                'new_tax_amount' => $new_tax,
            ]);
        }
    }
    public static function logIndividualActivity2($neat_txn_id,  $activity_title, $activity_by, $txn_id, $price, $product_id, $previous_qty, $new_qty, $previous_tax, $new_tax)
    {
        // Check if a log entry for the same product already exists
        $existing_log = self::where('neat_txn_id', $neat_txn_id)
            ->where('txn_id', $txn_id)
            ->where('product_id', $product_id)
            ->first();
    
        if ($existing_log && $activity_title == 'Edited While Packing' && $activity_by == Auth::id()) {
            // Update the existing log entry with the new quantity
            $existing_log->new_qty = $new_qty;
            $existing_log->new_tax_amount = $new_tax;
            $existing_log->save();
        } else {
            // Create a new log entry if none exists
            return self::create([
                'neat_txn_id' => $neat_txn_id,
                'txn_id' => $txn_id,
                'product_id' => $product_id,
                'price' => $price,
                'activity_title' => 'Changed',
                'qty' => $previous_qty,
                'new_qty' => $new_qty,
                'tax_amount' => $previous_tax,
                'new_tax_amount' => $new_tax,
            ]);
        }
    }
        
    private static function calculatePriceDifference($txn_id, $productId, $unit_price)
    {
        try {
            $transaction = Transaction::find($txn_id);
            $selling_price_group_id = $transaction->selling_price_group_id ?? null;
            $transactionSellLine = TransactionSellLine::where('transaction_id', $txn_id)
                ->where('product_id', $productId)
                ->first();
            if (!$transactionSellLine) {
                return null;
            }
            $variation_id = $transactionSellLine->variation_id;
            $variation = Variation::find($variation_id);
            if (!$variation) {
                return null;
            }
            $default_sell_price = $variation->default_sell_price;
            $price_inc_taxes = [];
            if ($selling_price_group_id !== null) {
                $price_inc_taxes = VariationGroupPrice::where('variation_id', $variation_id)
                    ->where('price_group_id', '<=', $selling_price_group_id)
                    ->pluck('price_inc_tax')
                    ->toArray();
            }
            $all_prices = array_merge([$default_sell_price], $price_inc_taxes);
            if (empty($all_prices)) {
                return null;
            }
            $min_safe_price = min($all_prices);
            $max_safe_price = max($all_prices);
            if ($unit_price >= $min_safe_price && $unit_price <= $max_safe_price) {
                return 0;
            } elseif ($unit_price < $min_safe_price) {
                return -1;
            } else {
                return 1;
            }
        } catch (\Exception $e) {
            Log::error("Error calculating price difference for product_id: $productId", [
                'exception' => $e->getMessage(),
                'txn_id' => $txn_id,
                'product_id' => $productId
            ]);
            return null;
        }
    }


      public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
