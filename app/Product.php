<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $appends = ['image_url'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'sub_unit_ids' => 'array',
    ];

    /**
     * Get the products image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!empty($this->image)) {
            $image_url = asset('/uploads' .$this->image);
        } else {
            $image_url = asset('/img/default.png');
        }
        return $image_url;
    }

    /**
    * Get the products image path.
    *
    * @return string
    */
    public function getImagePathAttribute()
    {
        if (!empty($this->image)) {
            $image_path = public_path('uploads') . '/' . config('constants.product_img_path') . '/' . $this->image;
        } else {
            $image_path = null;
        }
        return $image_path;
    }

    public function product_variations()
    {
        return $this->hasMany(\App\ProductVariation::class);
    }

    /**
     * Get the brand associated with the product.
     */
    public function brand()
    {
        return $this->belongsTo(\App\Brands::class);
    }

    /**
    * Get the unit associated with the product.
    */
    public function unit()
    {
        return $this->belongsTo(\App\Unit::class);
    }
    /**
     * Get category associated with the product.
     */
    public function category()
    {
        return $this->belongsTo(\App\Category::class);
    }

    public function category_single()
    {
        return $this->hasOne(\App\Category::class,'id','category_id');
    }

    /**
     * Get sub-category associated with the product.
     */
    public function sub_category()
    {
        return $this->belongsTo(\App\Category::class, 'sub_category_id', 'id');
    }

    /**
     * Get the brand associated with the product.
     */
    public function product_tax()
    {
        return $this->belongsTo(\App\TaxRate::class, 'tax', 'id');
    }

    /**
     * Get the variations associated with the product.
     */
    public function variations()
    {
        return $this->hasMany(\App\Variation::class);
    }

    /**
     * If product type is modifier get products associated with it.
     */
    public function modifier_products()
    {
        return $this->belongsToMany(\App\Product::class, 'res_product_modifier_sets', 'modifier_set_id', 'product_id');
    }

    /**
     * If product type is modifier get products associated with it.
     */
    public function modifier_sets()
    {
        return $this->belongsToMany(\App\Product::class, 'res_product_modifier_sets', 'product_id', 'modifier_set_id');
    }

    /**
     * Get the purchases associated with the product.
     */
    public function purchase_lines()
    {
        return $this->hasMany(\App\PurchaseLine::class);
    }

    /**
     * Scope a query to only include active products.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('products.is_inactive', 0);
    }

    /**
     * Scope a query to only include inactive products.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('products.is_inactive', 1);
    }

    /**
     * Scope a query to only include products for sales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProductForSales($query)
    {
        return $query->where('not_for_selling', 0);
    }

    /**
     * Scope a query to only include products not for sales.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProductNotForSales($query)
    {
        return $query->where('not_for_selling', 1);
        // return $query->where(function($q){
        //     $q->where('not_for_selling', 1)
        //         ->orWhere('products.is_inactive', 1);
        // });
    }

    public function product_locations()
    {
        return $this->belongsToMany(\App\BusinessLocation::class, 'product_locations', 'product_id', 'location_id');
    }

    /**
     * Scope a query to only include products available for a location.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLocation($query, $location_id)
    {
        return $query->where(function ($q) use ($location_id) {
            $q->whereHas('product_locations', function ($query) use ($location_id) {
                $query->where('product_locations.location_id', $location_id);
            });
        });
    }

    /**
     * Get warranty associated with the product.
     */
    public function warranty()
    {
        return $this->belongsTo(\App\Warranty::class);
    }
    public static function forDropdown($business_id)
    {
        $products = Product::where('business_id', $business_id)
                    ->pluck('name', 'id');

        return $products;
    }

    public static $inventoryItemList=[
        'LOGIC POWER CARTRIDGES 27MG TOBACCO-BOX OF 10',
        'LOGIC POWER KIT 27MG TOBACCO-BOX OF 5',
        'LOGIC PRO CAPSULES 20MG TOBACCO-BOX OF 10',
        'LOGIC PRO KIT CAPSULE TANK SYSTEM-BOX OF 5',
        'BLU DISPOSABLE 2.4% CHERRY CRUSH- BOX OF 5',
        'BLU DISPOSABLE 2.4% MAGINFICENT MENTHOL- BOX OF 5',
        'BLU DISPOSABLE 2.4% POLAR MINT- BOX OF 5',
        'BLU DISPOSABLE 2.4% TOBACCO CLASSIC- BOX OF 5',
        'BLU PLUS TANK 2.4% MENTHOL-BOX OF 5',
        'BLU PLUS TANK 2.4% TOBACCO-BOX OF 5',
        'BLU PLUS XPRESS KIT -BOX OF 5',
        'MYBLU DEVICE KIT BOX OF 5',
        'MYBLU INTENSE PS 2.4% TOBACCO-BOX OF 5',
        'MYBLU INTENSE PS 2.5% TOBACCO CHILL-BOX OF 5',
        'MYBLU INTENSE PS 3.6% TOBACCO-BOX OF 5',
        'MYBLU INTENSE PS 4.0% TOBACCO CHILL-BOX OF 5',
        'MYBLU PS 2.4% GOLD LEAF-BOX OF 5',
        'MYBLU PS 2.4% MENTHOL-BOX OF 5',
        'ROGUE NICOTINE POUCHES 3MG HONEY LEMON-PACK OF 5',
        'ROGUE NICOTINE POUCHES 3MG MANGO-PACK OF 5',
        'ROGUE NICOTINE POUCHES 3MG PEPPERMINT-PACK OF 5',
        'ROGUE NICOTINE POUCHES 3MG WINTERGREEN-PACK OF 5',
        'ROGUE NICOTINE POUCHES 6MG HONEY LEMON-PACK OF 5',
        'ROGUE NICOTINE POUCHES 6MG MANGO-PACK OF 5',
        'ROGUE NICOTINE POUCHES 6MG PEPPERMINT-PACK OF 5',
        'ROGUE NICOTINE POUCHES 6MG WINTERGREEN-PACK OF 5',
        'ROGUE NICOTINE POUCHES 6MG CINNAMON APPLE COMBO PACK OF 10',
        'E CIG KIT VUSE ALTO BLUE',
        'E CIG KIT VUSE ALTO GOLD',
        'E CIG KIT VUSE ALTO RED',
        'E CIG KIT VUSE ALTO TEAL',
        'VUSE KIT ALTO SLATE',
        'EKIT VUSE ALTO ROSE GOLD',
        'EKIT VUSE ALTO SILVER',
        'VUSE ALTO GOLDEN TOBACCO 2.4% 1CT',
        'VUSE ALTO GOLDEN TOBACCO 2.4% 4COUNT',
        'VUSE ALTO GOLDEN TOBACCO 5% 1CT',
        'VUSE ALTO GOLDEN TOBACCO 5% 4COUNT',
        'VUSE ALTO MENTHOL 2.4% 1CT',
        'VUSE ALTO MENTHOL 2.4% 4COUNT',
        'VUSE ALTO MENTHOL 5% 1CT',
        'VUSE ALTO MENTHOL 5% 4COUNT',
        'VUSE ALTO POD GOLDEN TOBACCO 1.8 0.2M',
        'VUSE ALTO POD GOLDEN TOBACCO 2.4 0.2M',
        'VUSE ALTO POD GOLDEN TOBACCO 5.0 0.2M',
        'VUSE ALTO POD MENTHOL 1.8 0.2M',
        'VUSE ALTO POD MENTHOL 2.4 0.2M',
        'VUSE ALTO POD MENTHOL 5.0 0.2M',
        'VUSE ALTO POD RICH TOBACCO 1.8 0.2M',
        'VUSE ALTO POD RICH TOBACCO 2.4 0.2M',
        'VUSE ALTO POD RICH TOBACCO 5.0 0.2M',
        'VELO HARD BERRY LOZENGE 12 COUNT',
        'VELO HARD CREMA LOZENGE 12 COUNT',
        'VELO HARD DARK MINT LOZENGE 12 COUNT',
        'VELO HARD MINT LOZENGE 12 COUNT',
        'VELO POUCH CITRUS 2 MG 15 COUNT',
        'VELO POUCH MINT 2 MG 15 COUNT',
        'VELO LARGE BLACK CHERRY POUCH 4MG 20CT',
        'VELO CINNAMON POUCHES 4MG 20COUNT',
        'VELO CITRUS BURST POUCHES 4MG 20COUNT',
        'VELO LARGE POUCH CITRUS 4 MG 15 COUNT',
        'VELO DRAGON FRUIT POUCHES 4MG 20COUNT',
        'VELO LARGE POUCH MINT 4 MG 15 COUNT',
        'VELO LARGE PEPPERMINT POUCH 4MG 20CT',
        'VELO SPEARMINT POUCHES 4MG 20COUNT',
        'VELO WINTERGREEN POUCHES 4MG 20COUNT',
        'VELO LARGE BLACK CHERRY POUCH 7MG 20CT',
        'VELO CINNAMON POUCHES 7MG 20COUNT',
        'VELO CITRUS BURST POUCHES 7MG 20COUNT',
        'VELO DRAGON FRUIT POUCHES 7MG 20COUNT',
        'VELO LARGE PEPPERMINT POUCH 7MG 20CT',
        'VELO SPEARMINT POUCHES 7MG 20COUNT',
        'VELO WINTERGREEN POUCHES 7MG 20COUNT',
        'VELO POUCH COFFEE 4MG 20CT',
        'VELO POUCH COFFEE 7MG 20CT',
        'EACC VUSE SOLO - BOX OF 5',
        'VUSE CART MEN 5MG 4.8%',
        'VUSE CART ORIG 5MG 4.8%',
        'ERFL VUSE VIBE TANK MEN 3.0% 2CART/PK',
        'ERFL VUSE VIBE TANK ORIG 3.0% 2CART/PK',
        'VUSE KIT VIBE POWER UNIT - BOX OF 5',
    ];
}
