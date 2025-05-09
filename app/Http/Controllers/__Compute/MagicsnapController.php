<?php

namespace App\Http\Controllers\__Compute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Product;
use App\Brands;
use App\ProductVariation;
use App\Variation;
use Illuminate\Support\Facades\DB;
use App\Category;
use Exception;

class MagicsnapController extends Controller
{
    public function dash()
    {
        return view('product.magicsnap_import_products');
    }
public function checkExistingSkus(Request $request)
{
    try {
        $validated = $request->validate([
            '*.sku' => 'required|string',
            '*.name' => 'required|string',
        ]);

        $products = $validated;
        $existingProducts = [];

        foreach ($products as $product) {
            $filteredSKU = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $product['sku']));
            $filteredProductName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $product['name']));

            // Simulate REGEXP_REPLACE using nested REPLACE functions
            $query = DB::table('products')
                ->whereRaw("
                    LOWER(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(sku, '-', ''),
                                    '_', ''),
                                ' ', ''),
                            '.', ''),
                        ',', '')) = ?", [$filteredSKU])
                ->orWhereRaw("
                    LOWER(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(item_code, '-', ''),
                                    '_', ''),
                                ' ', ''),
                            '.', ''),
                        ',', '')) = ?", [$filteredSKU])
                ->orWhereRaw("
                    LOWER(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(sku2, '-', ''),
                                    '_', ''),
                                ' ', ''),
                            '.', ''),
                        ',', '')) = ?", [$filteredSKU])
                ->orWhereRaw("
                    LOWER(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(sku3, '-', ''),
                                    '_', ''),
                                ' ', ''),
                            '.', ''),
                        ',', '')) = ?", [$filteredSKU])
                ->orWhereRaw("
                    LOWER(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(name, '-', ''),
                                    '_', ''),
                                ' ', ''),
                            '.', ''),
                        ',', '')) = ?", [$filteredProductName]);

            $existingProduct = $query->first();

            if ($existingProduct) {
                $existingProducts[] = ['sku' => $product['sku'], 'name' => $product['name']];
            }
        }

        return response()->json($existingProducts);
    } catch (\Exception $e) {
        \Log::error('Error checking existing SKUs: ' . $e->getMessage());
        return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
    }
}



private function barcodeValidator($sku)
{
    $filteredSKU = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $sku));

    // Simulate REGEXP_REPLACE using nested REPLACE functions
    $existingSKU = DB::table('products')
        ->whereRaw("
            LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(sku, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$filteredSKU])
        ->orWhereRaw("
            LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(item_code, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$filteredSKU])
        ->orWhereRaw("
            LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(sku2, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$filteredSKU])
        ->orWhereRaw("
            LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(sku3, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$filteredSKU])
        ->first();

    if ($existingSKU) {
        return $this->generateUniqueSKU($sku);
    }

    return $sku;
}

private function generateUniqueSKU($sku)
{
    // Filter SKU and convert to uppercase
    $filteredSKU = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $sku));

    // Extract the last character of the SKU
    $lastCharacter = substr($filteredSKU, -1);

    // Check if the last character is a letter or a number
    if (ctype_alpha($lastCharacter)) {
        // If it's a letter, attach the next alphabet
        $newCharacter = chr(((ord($lastCharacter) - 65 + 1) % 26) + 65);
    } else {
        // If it's a number, attach the next number
        $newCharacter = ($lastCharacter + 1) % 10;
    }

    // Append the new character to the SKU
    $newSKU = $filteredSKU . $newCharacter;

    // Check for uniqueness
    $attempt = 0;
    $max_attempts = 10;
    while ($attempt < $max_attempts) {
        $existingSKU = DB::table('products')
            ->whereRaw("LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(sku, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$newSKU])
            ->orWhereRaw("LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(item_code, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$newSKU])
            ->orWhereRaw("LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(sku2, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$newSKU])
            ->orWhereRaw("LOWER(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(sku3, '-', ''),
                            '_', ''),
                        ' ', ''),
                    '.', ''),
                ',', '')) = ?", [$newSKU])
            ->first();

        if (!$existingSKU) {
            return $newSKU;
        }

        // Update the new character based on the last character
        if (ctype_alpha($lastCharacter)) {
            $newCharacter = chr(((ord($newCharacter) - 65 + 1) % 26) + 65);
        } else {
            $newCharacter = ($newCharacter + 1) % 10;
        }

        // Append the new character to the SKU
        $newSKU = substr($filteredSKU, 0, -1) . $newCharacter;

        $attempt++;
    }

    throw new \Exception("Unable to generate a unique SKU for {$sku}");
}

    public function submitSelectedProducts(Request $request)
{
    DB::beginTransaction();

    try {
        $products = json_decode($request->getContent(), true);
        $business_id = $request->session()->get('user.business_id');
        $created_by = $request->session()->get('user.id');
        $productIds = []; // Array to store generated product IDs
        $variationIds = []; // Array to store generated variation IDs

        $baseUrl = 'https://magicsnap.bbtl.app/mainproductimages/';

        foreach ($products as $product) {
            try {
                $imageUrl = $baseUrl . $product['image'];
                // $imageData = file_get_contents($imageUrl);
                $client = new Client();
                $response = $client->get($imageUrl);
                if ($response->getStatusCode() == 200) {
                    $imageData = $response->getBody()->getContents();
                }
                else{
                    throw new Exception("Failed to fetch image");
                }

                $imageName = uniqid() . '_' . basename($imageUrl);
                $imagePath = 'img/' . $imageName;
                $brandName = $product['brand_name'];
                $filteredBrandName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $brandName));
                $brand_id = null;
                if (!empty($brandName)) {
                    $brand = DB::table('brands')
                        ->whereRaw("LOWER(
                            REPLACE(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(name, '-', ''),
                                        '_', ''),
                                    ' ', ''),
                                '.', ''),
                            ',', '')) = ?", [$filteredBrandName])
                        ->first();
                    if ($brand) {
                        $brand_id = $brand->id;
                    } else {
                        $formattedBrandName = ucwords(trim(preg_replace('/\s+/', ' ', $brandName)));
                        $newBrand = Brands::create([
                            'name' => $formattedBrandName,
                            'created_by' => $created_by,
                            'business_id' => $business_id
                        ]);
                        $brand_id = $newBrand->id;
                    }
                }

                $categoriesName = $product['categories_name'];
                $subCatName = $product['sub_cat_name'];
                $category_id = null;
                $sub_category_id = null;

                if (!empty($categoriesName)) {
                    $filteredCategoriesName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $categoriesName));
                    $filteredSubCatName = !empty($subCatName) ? strtolower(preg_replace('/[^A-Za-z0-9]/', '', $subCatName)) : null;

                    if ($filteredSubCatName) {
                        $subCategory = DB::table('categories')
                            ->whereRaw("LOWER(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(name, '-', ''),
                                            '_', ''),
                                        ' ', ''),
                                    '.', ''),
                                ',', '')) = ?", [$filteredSubCatName])
                            ->first();

                        if ($subCategory) {
                            $sub_category_id = $subCategory->id;
                            $category_id = $subCategory->parent_id;
                        } else {
                            $formattedSubCatName = ucwords(trim(preg_replace('/\s+/', ' ', $subCatName)));
                            $formattedCategoriesName = ucwords(trim(preg_replace('/\s+/', ' ', $categoriesName)));

                            $parentCategory = DB::table('categories')
                                ->whereRaw("LOWER(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(name, '-', ''),
                                                '_', ''),
                                            ' ', ''),
                                        '.', ''),
                                    ',', '')) = ?", [$filteredCategoriesName])
                                ->first();

                            if (!$parentCategory) {
                                $parentCategory = Category::create([
                                    'name' => $formattedCategoriesName,
                                    'parent_id' => 0,
                                    'created_by' => $created_by,
                                    'business_id' => $business_id,
                                    'category_type' => 'product',
                                    'cig_cat' => 0
                                ]);
                            }

                            $category_id = $parentCategory->id;

                            $newSubCategory = Category::create([
                                'name' => $formattedSubCatName,
                                'parent_id' => $category_id,
                                'created_by' => $created_by,
                                'business_id' => $business_id,
                                'category_type' => 'product',
                                'cig_cat' => 0
                            ]);

                            $sub_category_id = $newSubCategory->id;
                        }
                    } else {
                        $category = DB::table('categories')
                            ->whereRaw("LOWER(
                                REPLACE(
                                    REPLACE(
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(name, '-', ''),
                                            '_', ''),
                                        ' ', ''),
                                    '.', ''),
                                ',', '')) = ?", [$filteredCategoriesName])
                            ->first();

                        if ($category) {
                            $category_id = $category->id;
                        } else {
                            $formattedCategoriesName = ucwords(trim(preg_replace('/\s+/', ' ', $categoriesName)));
                            $newCategory = Category::create([
                                'name' => $formattedCategoriesName,
                                'parent_id' => 0,
                                'created_by' => $created_by,
                                'business_id' => $business_id,
                                'category_type' => 'product',
                                'cig_cat' => 0
                            ]);

                            $category_id = $newCategory->id;
                        }
                    }
                }

                Storage::disk('local')->put($imagePath, $imageData);
                $fullImagePath = '/' . $imagePath; // Prepends slash to the stored path

            } catch (\Exception $e) {
                \Log::error('Error fetching or saving the image', ['url' => $imageUrl, 'error' => $e->getMessage()]);
                $fullImagePath = ''; // Handle cases where the image is not downloadable or savable
            }

            $validatedSKU = $this->barcodeValidator($product['sku']);

            $productData = [
                'name' => trim(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8')),
                'sku' => strtoupper($validatedSKU),
                'unit_id' => 5,
                'brand_id' => $brand_id ?? null,
                'category_id' => $category_id ?? null,
                'sub_category_id' => $sub_category_id ?? null,
                'type' => "single",
                'main_image' => $fullImagePath,
                'image' => "",
                'tax_type' => "exclusive",
                'created_by' => $created_by,
                'business_id' => $business_id,
                'created_at' => now(),
                'updated_at' => now()
            ];

            try {
                $productId = Product::insertGetId($productData);
                $productIds[] = $productId; // Store product ID

            } catch (\Exception $e) {
                \Log::error('Error inserting product', ['error' => $e->getMessage(), 'data' => $productData]);
                DB::rollback();
                return response()->json(['message' => 'Failed to add products', 'error' => $e->getMessage()], 500);
            }
        }

        foreach ($productIds as $index => $productId) {
            $productVariation = ProductVariation::create([
                'name' => 'DUMMY',
                'product_id' => $productId,
                'is_dummy' => 1,
            ]);

            $validatedSKU = $this->barcodeValidator($products[$index]['sku']);

            $variation = Variation::create([
                'product_id' => $productId,
                'product_variation_id' => $productVariation->id,
                'default_purchase_price' => 0.0000,
                'dpp_inc_tax' => 0.0000,
                'default_sell_price' => $products[$index]['default_sell_price'],
                'sell_price_inc_tax' => $products[$index]['default_sell_price'],
                'profit_percent' => 0,
                'sub_sku' => strtoupper($validatedSKU),
                'name' => 'DUMMY',
            ]);

            $variationIds[] = $variation->id; // Store variation ID
        }

        DB::commit();

        return response()->json([
            'message' => 'Products successfully added',
            'product_ids' => $productIds,
            'variation_ids' => $variationIds
        ]);
    } catch (\Exception $e) {
        DB::rollback();

        \Log::error('Error submitting products', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Failed to add products', 'error' => $e->getMessage()], 500);
    }
}

}