<?php

namespace App\Http\Controllers;
use Auth;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Media;
use App\Product;
use App\ProductVariation;
use App\PurchaseLine;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Unit;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Variation;
use App\VariationGroupPrice;
use App\VariationLocationDetails;
use App\VariationTemplate;
use App\BarcodeSetting;
use App\Warranty;
use App\Contact;
use App\Exports\ProductsExport;
use App\Helpers\SmartSyncHelper;
use App\ProductActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\OnhandItem;
use Log;
use App\JadooProduct;
use App\Models\SmartSyncValue;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $moduleUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('product.view') && !auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        if (request()->ajax()) {
            $query = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
                ->leftJoin('contacts as suppliers', 'products.supplier_id', '=', 'suppliers.id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

                $not_for_selling = request()->get('not_for_selling', null);
                if ($not_for_selling == 'true') {
                    $query->ProductNotForSales();
                }
                elseif ($not_for_selling == 'false') {
                    $query->ProductForSales();
                }

            //Filter by location
            $location_id = request()->get('location_id', null);
            $permitted_locations = auth()->user()->permitted_locations();

            if (!empty($location_id) && $location_id != 'none') {
                if ($permitted_locations == 'all' || in_array($location_id, $permitted_locations)) {
                    $query->whereHas('product_locations', function ($query) use ($location_id) {
                        $query->where('product_locations.location_id', '=', $location_id);
                    });
                }
            } elseif ($location_id == 'none') {
                $query->doesntHave('product_locations');
            } else {
                if ($permitted_locations != 'all') {
                    $query->whereHas('product_locations', function ($query) use ($permitted_locations) {
                        $query->whereIn('product_locations.location_id', $permitted_locations);
                    });
                } else {
                    $query->with('product_locations');
                }
            }

            $products = $query->select(
                'products.id',
                'products.name as product',
                'products.type',
                'c1.name as category',
                'c2.name as sub_category',
                'units.actual_name as unit',
                'brands.name as brand',
                'tax_rates.name as tax',
                'products.sku',
                'products.ml',
                'products.sku2',
                'products.sku3',
                'products.image',
                'products.main_image',
                'products.compressed_image',
                'products.item_code',
                'products.sales_price',
                'products.enable_stock',
                'products.is_inactive',
                'products.srp as web_reg_price',
                'products.sales_price as web_sale_price',
                'products.not_for_selling',
                'products.product_custom_field1',
                'products.product_custom_field2',
                'products.product_custom_field3',
                'products.product_custom_field4',
                'v.profit_percent as profit_percent',
                'products.qty_box',
                'products.aisle as aisle',
                'products.rack as rack',
                'products.shelf as shelf',
                'products.bin as bin',
                'vld.qty_available as current_stock',
                DB::raw('MAX(v.default_sell_price) as max_price'),
                DB::raw('MIN(v.default_sell_price) as min_price'),
                DB::raw('MAX(v.dpp_inc_tax) as max_purchase_price'),
                DB::raw('MIN(v.dpp_inc_tax) as min_purchase_price'),
                DB::raw('suppliers.name as vendor'),
                'products.case_qty'


                )->groupBy('products.id');

            $type = request()->get('type', null);
            if (!empty($type)) {
                $products->where('products.type', $type);
            }

            $category_id = request()->get('category_id', null);
            if (!empty($category_id)) {
                $products->where('products.category_id', $category_id);
            }

            $sub_category_id_filter = request()->get('sub_category_id_filter', null);
            if (!empty($sub_category_id_filter)) {
                $products->where('products.sub_category_id', $sub_category_id_filter);
            }
            $brand_id = request()->get('brand_id', null);
            if (!empty($brand_id)) {
                $products->where('products.brand_id', $brand_id);
            }

            $unit_id = request()->get('unit_id', null);
            if (!empty($unit_id)) {
                $products->where('products.unit_id', $unit_id);
            }

            $tax_id = request()->get('tax_id', null);
            if (!empty($tax_id)) {
                $products->where('products.tax', $tax_id);
            }

            // $active_state = request()->get('active_state', null);
            // if ($active_state == 'active') {
            //     $products->Active();
            // }
            // if ($active_state == 'inactive') {
            //     $products->Inactive();
            // }
            // $not_for_selling = request()->get('not_for_selling', null);
            // if ($not_for_selling == 'true') {
            //     $products->ProductNotForSales();
            // }
            // $is_inactive = request()->get('active_state');
            // if ($is_inactive == 'inactive') {
            //     $products->Inactive();
            // }



            $active_state = request()->get('active_state', 0);
            if ($active_state == 'active') {
                $products->Active();
            }
            if ($active_state == 'inactive') {
                $products->Inactive();
            }

             $column = request()->get('filter_column', null);
            $operator = request()->get('filter_operator', null);
            $value = request()->get('filter_value', null);

             if ($column && $operator || $value) {
                if (in_array($column, ['tier_price_1', 'tier_price_2', 'tier_price_3','tier_price_4'])) {
                    $priceGroupId = [
                        'tier_price_1' => 68,
                        'tier_price_2' => 69,
                        'tier_price_3' => 70,
                        'tier_price_4' => 80,
                    ][$column];

                    $query->leftJoin('variation_group_prices as pv', 'v.id', '=', 'pv.variation_id')
                        ->where('pv.price_group_id', $priceGroupId);

                    if ($operator === 'LIKE') {
                        $query->where('pv.price_inc_tax', 'LIKE', '%' . $value . '%');
                    } elseif ($operator === 'NOT LIKE') {
                        $query->where('pv.price_inc_tax', 'NOT LIKE', '%' . $value . '%');
                    } elseif ($operator === 'IN') {
                        $query->whereIn('pv.price_inc_tax', explode(',', $value));
                    } elseif ($operator === 'NOT IN') {
                        $query->whereNotIn('pv.price_inc_tax', explode(',', $value));
                    } elseif ($operator === 'BETWEEN') {
                        $values = explode(',', $value);
                        $query->whereBetween('pv.price_inc_tax', [$values[0], $values[1]]);
                    } elseif ($operator === 'NOT BETWEEN') {
                        $values = explode(',', $value);
                        $query->whereNotBetween('pv.price_inc_tax', [$values[0], $values[1]]);
                    } elseif ($operator === 'IS NULL') {
                        $query->whereNull('pv.price_inc_tax')
                            ->orWhere('pv.price_inc_tax', ' ')
                            ->orWhereRaw('pv.price_inc_tax REGEXP ?', ['^0+$']);
                    } elseif ($operator === 'IS NOT NULL') {
                        $query->whereNotNull('pv.price_inc_tax');
                    } else {
                        $query->where('pv.price_inc_tax', $operator, $value);
                    }
                } else {
                    if ($operator === 'LIKE') {
                        $query->where($column, 'LIKE', '%' . $value . '%');
                    } elseif ($operator === 'NOT LIKE') {
                        $query->where($column, 'NOT LIKE', '%' . $value . '%');
                    } elseif ($operator === 'IN') {
                        $query->whereIn($column, explode(',', $value));
                    } elseif ($operator === 'NOT IN') {
                        $query->whereNotIn($column, explode(',', $value));
                    } elseif ($operator === 'BETWEEN') {
                        $values = explode(',', $value);
                        $query->whereBetween($column, [$values[0], $values[1]]);
                    } elseif ($operator === 'NOT BETWEEN') {
                        $values = explode(',', $value);
                        $query->whereNotBetween($column, [$values[0], $values[1]]);
                    } elseif ($operator === 'IS NULL') {
                        $query->whereNull($column)
                            ->orWhere($column, ' ')
                            ->orWhereRaw($column . ' REGEXP ?', ['^0+$']);
                    } elseif ($operator === 'IS NOT NULL') {
                        $query->whereNotNull($column);
                    } else {
                        $query->where($column, $operator, $value);
                    }
                }
            }

            $woocommerce_enabled = request()->get('woocommerce_enabled', 0);
            if ($woocommerce_enabled == 1) {
                $products->where('products.woocommerce_disable_sync', 0);
            }

            if (!empty(request()->get('repair_model_id'))) {
                $products->where('products.repair_model_id', request()->get('repair_model_id'));
            }

            // $search = request()->get('search', []);
            // if (!empty($search['value'])) {
            //     $searchable = $search['value'];
            //     $searchable = str_replace(" ","%",$searchable);
            //     $products->where(function($query) use($searchable){
            //         $query->where("products.item_code", "like","%" . $searchable . "%")
            //         ->orWhere("products.name", "like","%" . $searchable . "%")
            //         ->orWhere("products.sku", "like","%" . $searchable . "%")
            //         ->orWhere("products.sku2", "like","%" . $searchable . "%")
            //         ->orWhere("products.sku3", "like","%" . $searchable . "%")
            //         ->orWhere("c1.name", "like","%" . $searchable . "%")
            //         ->orWhere("c2.name", "like","%" . $searchable . "%")
            //         ->orWhere("brands.name", "like","%" . $searchable . "%");
            //     });
            // }

            $search = request()->get('search', []);
            if (!empty(request()->get('exact_search')) && !empty($search['value'])) {
                $searchable = $search['value'];
                $products->where(function($query) use($searchable){
                    $query->where("products.item_code", $searchable)
                    ->orWhere("products.name", $searchable)
                    ->orWhere("products.sku", $searchable)
                    ->orWhere("products.sku2", $searchable)
                    ->orWhere("products.sku3", $searchable)
                    ->orWhere("c1.name", $searchable)
                    ->orWhere("c2.name", $searchable)
                    ->orWhere("brands.name", $searchable);
                });
            }

            return Datatables::of($products)
                ->addColumn('tier_2',function($row){
                    $t2 = DB::table('variations as v')
                        ->leftJoin('variation_group_prices as vgp','v.id','=','vgp.variation_id')
                        ->where('vgp.price_group_id',69)
                        ->where('v.product_id',$row->id)
                    ->first();

                    if(!empty($t2)){
                        return "$" . number_format($t2->price_inc_tax, 2);
                    }
                    else{
                        return "$" . number_format($row->max_price, 2);
                    }
                })
                ->addColumn('tier_3',function($row){
                    $t2 = DB::table('variations as v')
                        ->leftJoin('variation_group_prices as vgp','v.id','=','vgp.variation_id')
                        ->where('vgp.price_group_id',70)
                        ->where('v.product_id',$row->id)
                    ->first();

                    if(!empty($t2)){
                        return "$" . number_format($t2->price_inc_tax, 2);
                    }
                    else{
                        return "$" . number_format($row->max_price, 2);
                    }
                })
                ->addColumn('tier_4',function($row){
                    $t2 = DB::table('variations as v')
                        ->leftJoin('variation_group_prices as vgp','v.id','=','vgp.variation_id')
                        ->where('vgp.price_group_id',80)
                        ->where('v.product_id',$row->id)
                    ->first();

                    if(!empty($t2)){
                        return "$" . number_format($t2->price_inc_tax, 2);
                    }
                    else{
                        return "$" . number_format($row->max_price, 2);
                    }
                })
                ->addColumn(
                    'product_locations',
                    function ($row) {
                        return $row->product_locations->implode('name', ', ');
                    }
                )
                ->addColumn(
                    'vendor',
                    '<div style="white-space: nowrap;"><span class="">{{$vendor}}</span> </div>'
                )
                ->editColumn('category', '{{$category}} @if(!empty($sub_category))<br/> -- {{$sub_category}}@endif')
                ->addColumn(
                    'action',
                    function ($row) use ($selling_price_group_count) {
                        $html =
                        '<div class="btn-group"><button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">'. __("messages.actions") . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu"><li><a href="' . action('LabelsController@show') . '?product_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fa fa-barcode"></i> ' . __('barcode.labels') . '</a></li>';

                        if (auth()->user()->can('product.view')) {
                            $html .=
                            '<li><a href="' . action('ProductController@view', [$row->id]) . '" class="view-product"><i class="fa fa-eye"></i> ' . __("messages.view") . '</a></li>';
                        }

                        if (auth()->user()->can('product.update')) {
                            $html .=
                            '<li><a href="' . action('ProductController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';
                        }

                        if (auth()->user()->can('product.delete')) {
                            $html .=
                            '<li><a href="' . action('ProductController@destroy', [$row->id]) . '" class="delete-product"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                        }

                        if ($row->is_inactive == 1) {
                            $html .=
                            '<li><a href="' . action('ProductController@activate', [$row->id]) . '" class="activate-product"><i class="fas fa-check-circle"></i> ' . __("lang_v1.reactivate") . '</a></li>';
                        }

                        $html .= '<li class="divider"></li>';

                        if ($row->enable_stock == 1 && auth()->user()->can('product.opening_stock')) {
                            $html .=
                            '<li><a href="#" data-href="' . action('OpeningStockController@add', ['product_id' => $row->id]) . '" class="add-opening-stock"><i class="fa fa-database"></i> ' . __("lang_v1.add_edit_opening_stock") . '</a></li>';
                        }

                        if (auth()->user()->can('product.view')) {
                            $html .=
                            '<li><a href="' . action('ProductController@productStockHistory', [$row->id]) . '"><i class="fas fa-history"></i> ' . __("lang_v1.product_stock_history") . '</a></li>';
                        }

                        if (auth()->user()->can('product.create')) {

                            if ($selling_price_group_count > 0) {
                                $html .=
                                '<li><a href="' . action('ProductController@addSellingPrices', [$row->id]) . '"><i class="fas fa-money-bill-alt"></i> ' . __("lang_v1.add_selling_price_group_prices") . '</a></li>';
                            }

                            $html .=
                                '<li><a href="' . action('ProductController@create', ["d" => $row->id]) . '"><i class="fa fa-copy"></i> ' . __("lang_v1.duplicate_product") . '</a></li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->editColumn('product', function ($row) {
                    $product = $row->is_inactive == 1 ? $row->product . ' <span class="label bg-gray">' . __("lang_v1.inactive") .'</span>' : $row->product;

                    // $product = $row->not_for_selling == 1 ? $product . ' <span class="label bg-gray">' . __("lang_v1.not_for_selling") .
                    //     '</span>' : $product;

                    if(!empty($row->web_reg_price) && $row->web_reg_price > 0){
                        if(!empty($row->web_sale_price) && $row->web_sale_price > 0){
$product .= '<br/><img src="/uploads/media/OnSale.png" style="width:80px; height:auto;">';
                        }
                    }

                    return $product;
                })
                ->editColumn('notforselling', function ($row) {
                    $notforselling = $row->not_for_selling == 1 ? 'Yes' : 'No';
                    return $notforselling;
                })
                // ->editColumn('image', function ($row) {
                //     $imgs = explode("," , $row->image_url);
                //     return '<div style="display: flex;"><img src="' . $imgs[0] . '" alt="Product image" class="product-thumbnail-small"></div>';
                // })
                // ->editColumn('image', function ($row) {
                //     $filePath = public_path('/uploads' .$row->main_image);
                //     $is_greater_than_limit = false;
                //     if (File::exists($filePath)) {
                //         $fileSize = File::size($filePath);
                //         $maxSize = 1024 * 1024;
                //         $is_greater_than_limit = ($fileSize > $maxSize);
                //     }
                //     if($is_greater_than_limit){
                //         return "";
                //     }
                //     return '<div style="display: flex;"><img src="' . asset('/uploads' .$row->main_image) . '" alt="Product image" class="product-thumbnail-small"></div>';
                // })
                ->editColumn('image', function ($row) {
                    if (!empty($row->compressed_image)) {
                        $imageSrc = asset($row->compressed_image);
                    }else{
                        $filePath = public_path('/uploads' . $row->main_image);
                        $is_greater_than_limit = false;

                        if (File::exists($filePath)) {
                            $fileSize = File::size($filePath);
                            $maxSize = 1024 * 1024;
                            $is_greater_than_limit = ($fileSize > $maxSize);
                        }

                        if ($is_greater_than_limit) {
                            return "";
                        }

                        $imageSrc = asset('/uploads' . $row->main_image); // default image source


                    }


                    return '<div style="display: flex;"><img src="' . $imageSrc . '" alt="Product image" class="product-thumbnail-small"></div>';
                })


                ->editColumn('type', '@lang("lang_v1." . $type)')
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id .'">' ;
                })
                // ->editColumn('current_stock', '@if($enable_stock == 1) {{@number_format($current_stock)}} @else -- @endif {{$unit}}')
                ->editColumn('current_stock', '@if($enable_stock == 1) {{@number_format($current_stock)}} @else -- @endif ')
                ->addColumn(
                    'purchase_price',
                    '<div style="white-space: nowrap;"><span class="display_currency" data-currency_symbol="true">{{$min_purchase_price}}</span> @if($max_purchase_price != $min_purchase_price && $type == "variable") -  <span class="display_currency" data-currency_symbol="true">{{$max_purchase_price}}</span>@endif </div>'
                )
                ->addColumn(
                    'selling_price',
                    '<div style="white-space: nowrap;"><span class="display_currency" data-currency_symbol="true">{{$min_price}}</span> @if($max_price != $min_price && $type == "variable") -  <span class="display_currency" data-currency_symbol="true">{{$max_price}}</span>@endif </div>'
                )
                 ->addColumn(
                    'profit_percent',
                   function($row){
                        $profit_percent = 0;
                        $cost = $row->max_purchase_price;
                        $sell_price = $row->max_price;

                        if($cost > 0 && $sell_price > 0){
                            $new_profit_percent =(1 - ($cost / $sell_price))*100;
                            if($new_profit_percent && $new_profit_percent > 0){
                                $new_profit_percent = number_format($new_profit_percent, 2, ".", "");
                                $profit_percent = $new_profit_percent;
                            }
                        } else {
                            $profit_percent = 'NA';
                        }
                       return  '<div style="white-space: nowrap;"><span class="" >'. $profit_percent.' %</span></div>';
                    }
                )
                ->addColumn(
                    'item_location',
                    '<div style="white-space: nowrap;"><ul>@if($aisle == null)<li>A-0</li>@else<li>A-{{$aisle}}</li>@endif
                    @if($rack == null)<li>R-0</li>@else<li>R-{{$rack}}</li>@endif
                    @if($shelf == null)<li>S-0</li>@else<li>S-{{$shelf}}</li>@endif
                    @if($bin == null)<li>B-0</li>@else<li>B-{{$bin}}</li>@endif
                    </ul></div>'
                )
                ->filterColumn('products.sku', function ($query, $keyword) {
                    $query->whereHas('variations', function($q) use($keyword){
                            $q->where('sub_sku', 'like', "%{$keyword}%");
                        })
                    ->orWhere('products.sku', 'like', "%{$keyword}%");
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("product.view")) {
                            return  action('ProductController@view', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['action', 'image', 'mass_delete', 'product', 'selling_price', 'purchase_price', 'category','profit_percent','item_location','notforselling','vendor'])
                ->make(true);
        }

        $rack_enabled = (request()->session()->get('business.enable_racks') || request()->session()->get('business.enable_row') || request()->session()->get('business.enable_position'));

         $categoriesdata = Category::forDropdown($business_id, 'product');
        $categories = Category::where('business_id',$business_id)->where('category_type','product')->get();
        $brand_data = Brands::where('business_id',$business_id)->get();
        $brands = Brands::forDropdown($business_id);

        $units = Unit::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, false);
        $taxes = $tax_dropdown['tax_rates'];

        $business_locations = BusinessLocation::forDropdown($business_id);
        $business_locations->prepend(__('lang_v1.none'), 'none');

        if ($this->moduleUtil->isModuleInstalled('Manufacturing') && (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            $show_manufacturing_data = true;
        } else {
            $show_manufacturing_data = false;
        }

        //list product screen filter from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_filters_for_list_product_screen');

        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

        $sync_url = Session::get('sync_url','');
        Session::forget('sync_url');

        //added by developer1
        $price_groups_bulks = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        $remaining_credits = SmartSyncValue::getSmartValue('clipdrop_remaining_credits');

        $selectFields = [
            'products.item_code',
            'products.name',
            'products.sku',
            'products.sku2',
            'products.sku3',
            // 'tax_rates.name as tax',
            'vld.qty_available as current_stock',
            DB::raw('MIN(v.default_sell_price) as sell_price'),
            DB::raw('MIN(v.dpp_inc_tax) as purchase_price'),
        ];

        $customNames = [
            'products.item_code' => 'Item Code',
            'products.name' => 'Item Name',
            'products.sku' => 'Barcode 1',
            'products.sku2' => 'Barcode 2',
            'products.sku3' => 'Barcode 3',
            // 'tax_rates.name as tax' => 'Tax',
            'vld.qty_available' => 'Current Stock',
        ];

        $rawFields = [
            'v.default_sell_price' => 'Selling Price',
            'v.dpp_inc_tax' => 'Purchase Price',
        ];

        return view('product.index')
            ->with(compact(
                'sync_url',
                'rack_enabled',
                'categories',
                'categoriesdata',
                'brands',
                'brand_data',
                'units',
                'taxes',
                'business_locations',
                'show_manufacturing_data',
                'pos_module_data',
                'is_woocommerce',
                'price_groups_bulks',
                'remaining_credits',
                'customNames',
                'rawFields'
            ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $this->GenerateFinalBarcode();
        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for products quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('products', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action('ProductController@index'));
        }

        $categories = Category::forDropdown($business_id, 'product');

        $brands = Brands::forDropdown($business_id);
        $units = Unit::forDropdown($business_id, true);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;
        $barcode_default =  $this->productUtil->barcode_default();

        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        //Duplicate product
        $duplicate_product = null;
        $rack_details = null;

        $sub_categories = [];
        if (!empty(request()->input('d'))) {
            $duplicate_product = Product::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_product->name .= ' (copy)';

            if (!empty($duplicate_product->category_id)) {
                $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $duplicate_product->category_id)
                        ->pluck('name', 'id')
                        ->toArray();
            }

            //Rack details
            if (!empty($duplicate_product->id)) {
                $rack_details = $this->productUtil->getRackDetails($business_id, $duplicate_product->id);
            }
        }

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        $contacts = Contact::where('business_id',$business_id)->where('type','supplier')->get();
        // return $contacts;

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');
        $action ='add';
        return view('product.create')
            ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'barcode_default', 'business_locations', 'duplicate_product', 'sub_categories', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data','contacts','action'));
    }

    private function product_types()
    {
        //Product types also includes modifier.
        return ['single' => __('lang_v1.single'),
                'variable' => __('lang_v1.variable'),
                'combo' => __('lang_v1.combo')
            ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // return $request->all();
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $form_fields = ['name','brand_id', 'unit_id', 'category_id', 'tax', 'type', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_description', 'sub_unit_ids','qty_box','case_qty','aisle','rack','shelf','bin','srp','sales_price','weight','sync_ecom','supplier_id','note','sku2','sku3'];

            $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
            if (!empty($module_form_fields)) {
                $form_fields = array_merge($form_fields, $module_form_fields);
            }

            $product_details = $request->only($form_fields);

            if(($request->images)){
                $paths = [];
                $i = 0;
                foreach($request->images as $image){

                    $path = $image->store('img', 'local');
                    $paths[$i++] = '/'.$path;

                }
                $product_details['image'] = implode(",",$paths);
            }else{
                $product_details['image'] = "";
            }

            if(($request->main_image)){
                $mainImagePath = $request->main_image->store('img', 'local');
                $product_details['main_image'] = '/'.$mainImagePath;
            }else{
                $product_details['image'] = "";
            }

             if ($request->has('compressedFile')) {
                // Decode the Base64-encoded image data
                $base64Image = $request->input('compressedFile');
                $base64Image = str_replace('data:image/jpeg;base64,', '', $base64Image);
                $base64Image = str_replace(' ', '+', $base64Image);
                $decodedImageData = base64_decode($base64Image);

                // Determine the original file extension
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $decodedImageData);
                finfo_close($finfo);

                // Extract file extension from MIME type
                $fileExtension = explode('/', $mimeType)[1];

                // Generate a unique filename with the original file extension
                $fileName = uniqid() . '.' . $fileExtension;

                // Save the decoded image data to the public folder
                $filePath = 'compressed_images/' . $fileName;
                file_put_contents(public_path($filePath), $decodedImageData);
                $product_details['compressed_image'] = '/' . $filePath;
            }
            $product_details['name'] = strtoupper($request->input('name'));
            $product_details['ml'] = strtoupper($request->input('ml'));
            $product_details['case_qty'] = strtoupper($request->input('case_qty'));


            $product_details['sync'] = $request->input('sync');

            $product_details['item_code'] = strtoupper($request->input('item_code',''));
            $product_details['business_id'] = $business_id;
            $product_details['created_by'] = $request->session()->get('user.id');

            $product_details['enable_stock'] = (!empty($request->input('enable_stock')) &&  $request->input('enable_stock') == 1) ? 1 : 0;
            $product_details['not_for_selling'] = (!empty($request->input('not_for_selling')) &&  $request->input('not_for_selling') == 1) ? 1 : 0;

            $product_details['out_of_stock'] = (!empty($request->input('outofstock')) &&  $request->input('outofstock') == 1) ? 1 : 0;

            if (!empty($request->input('sub_category_id'))) {
                $product_details['sub_category_id'] = $request->input('sub_category_id') ;
            }

           $barcodesetting = BarcodeSetting::first();

            if(empty($request->sku)){

                if(empty($barcodesetting->last_barcode)){
                    $last_barcode = random_int(100000000000, 999999999999);

                    $substr =  substr($last_barcode, -6);
                    $substr = $substr + 1;
                    $c = 6;
                    $len = strlen($substr);
                    // $random = random_int(100000, 999999);
                    $random = 100000;
                    if($len < $c){

                        if($len == 5){
                            $barcode =  $random.'0'.$substr;
                        }
                        if($len == 4){
                            $barcode = $random.'00'.$substr;
                        }
                        if($len == 3){
                            $barcode = $random.'000'.$substr;
                        }
                        if($len == 2){
                            $barcode = $random.'0000'.$substr;
                        }
                        if($len == 1){
                            $barcode = $random.'00000'.$substr;
                        }
                    }else{
                        $new_barcode = $random.$substr;
                        $product_details['sku'] = $random.$substr;
                    }

                }else{
                    $new_barcode = $barcodesetting->last_barcode + 1;
                    $product_details['sku'] = $barcodesetting->last_barcode + 1;
                }

                addBarcode:
                if(!empty($request->sku)) $new_barcode = $request->sku;

                if(empty($barcodesetting)){
                    $barcode_setting = new BarcodeSetting();
                    $barcode_setting->last_barcode = $new_barcode;
                    $barcode_setting->save();
                }else{
                    $id = $barcodesetting->id;

                    $barcode_setting =  BarcodeSetting::find($id);
                    $barcode_setting->last_barcode = $new_barcode;
                    $barcode_setting->save();
                }
            }else{
                $sku_prefix = substr($request->sku,0,6);
                if($sku_prefix == 100000) goto addBarcode;
                $product_details['sku'] = $request->sku;
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && !empty($expiry_enabled) && ($product_details['enable_stock'] == 1)) {
                $product_details['expiry_period_type'] = $request->input('expiry_period_type');
                $product_details['expiry_period'] = $this->productUtil->num_uf($request->input('expiry_period'));
            }

            if (!empty($request->input('enable_sr_no')) &&  $request->input('enable_sr_no') == 1) {
                $product_details['enable_sr_no'] = 1 ;
            }

            //upload document
            // $product_details['image'] = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            // $product_details['image'] = implode(",",$paths);

            $common_settings = session()->get('business.common_settings');

            $product_details['warranty_id'] = !empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;

            DB::beginTransaction();
            //sku2 and sku3
            $product_details['sku2'] = !empty($request->sku2) ? $request->sku2 : "";
            $product_details['sku3'] = !empty($request->sku3) ? $request->sku3 : "";
            $product_details['item_code'] = !empty($product_details['item_code']) ? $product_details['item_code'] : $product_details['sku'];
            $product = Product::create($product_details);



            // if (empty(trim($request->input('sku')))) {
            //     $sku = $this->productUtil->generateProductSku($product->id);
            //     $product->sku = $sku;
            //     $product->save();
            // }


            //Add product locations
            //by default
            DB::table('product_locations')
                ->insert([
                    'product_id' => $product->id,
                    'location_id' => '4',
                ]);
            // $product_locations = $request->input('product_locations');
            // if (!empty($product_locations)) {
            //     $product->product_locations()->sync($product_locations);
            // }
            $user_id = auth()->user()->id;
            $product = Product::latest('id')->first();
            $product_id = $product->id;
            $this->productUtil->ProductActivityLog('added',$user_id,$product_id);
            $this->productUtil->ProductActivitiesLog('added',$user_id,$product_id,$request);


             $location_id = 0;
             $combo_variations = [];
            if(!empty($request->product_locations)){
                $location_id = $request->product_locations[0];
            }
            if ($product->type == 'single') {
                // $this->productUtil->createSingleProductVariation($product->id, $product->sku, $request->input('single_dpp'), $request->input('single_dpp_inc_tax'), $request->input('profit_percent'), $request->input('single_dsp'), $request->input('stock'), $request->input('single_dsp_inc_tax'));
                 $this->productUtil->createSingleProductVariation($product->id, $product->sku, $request->input('single_dpp_inc_tax'), $request->input('single_dpp_inc_tax'), $request->input('profit_percent'), $request->input('single_dsp'), $request->input('single_dsp_inc_tax'), $combo_variations,$request->input('stock'),$location_id,$request->input('single_dsp_tier1'),$request->input('single_dsp_tier2'),$request->input('single_dsp_tier3'),$request->input('single_dsp_tier4'));

            } elseif ($product->type == 'variable') {
                if (!empty($request->input('product_variation'))) {
                    $input_variations = $request->input('product_variation');
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations, $location_id);
                }
            } elseif ($product->type == 'combo') {

                //Create combo_variations array by combining variation_id and quantity.

                if (!empty($request->input('composition_variation_id'))) {
                    $composition_variation_id = $request->input('composition_variation_id');
                    $quantity = $request->input('quantity');
                    $unit = $request->input('unit');

                    foreach ($composition_variation_id as $key => $value) {
                        $combo_variations[] = [
                                'variation_id' => $value,
                                'quantity' => $this->productUtil->num_uf($quantity[$key]),
                                'unit_id' => $unit[$key]
                            ];
                    }
                }

                $this->productUtil->createSingleProductVariation($product->id, $product->sku, $request->input('item_level_purchase_price_total'), $request->input('purchase_price_inc_tax'), $request->input('profit_percent'), $request->input('selling_price'), $request->input('selling_price_inc_tax'), $combo_variations,$request->input('stock'));
            }

            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (!empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }

            //Set Module fields
            if (!empty($request->input('has_module_data'))) {
                $this->moduleUtil->getModuleData('after_product_saved', ['product' => $product, 'request' => $request]);
            }

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('product.product_added_success')
            ];

            //sync
            if ($request->input('submit_type') == 'save_n_sync') {
                Session::put('sync_url',$product->id ?? 0);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
            //return redirect('products')->with('status', $output);
        }

        if (request()->ajax()) {
            return $output;
        }

        if ($request->input('submit_type') == 'submit_n_add_opening_stock') {
            return redirect()->action(
                'OpeningStockController@add',
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action(
                'ProductController@addSellingPrices',
                [$product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                'ProductController@create'
            )->with('status', $output);
        }

        return redirect('products')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $details = $this->productUtil->getRackDetails($business_id, $id, true);

        return view('product.show')->with(compact('details'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        $this->GenerateFinalBarcode();
        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;

        $product = Product::where('business_id', $business_id)
                            ->with(['product_locations'])
                            ->where('id', $id)
                            ->firstOrFail();

        //Sub-category
        $sub_categories = [];
        $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $product->category_id)
                        ->pluck('name', 'id')
                        ->toArray();
        $sub_categories = [ "" => "None"] + $sub_categories;

        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        //Get units.
        $units = Unit::forDropdown($business_id, true);
        $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, true);

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);
        //Rack details
        $rack_details = $this->productUtil->getRackDetails($business_id, $id);

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();
        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        $contacts = Contact::where('business_id',$business_id)->where('type','supplier')->get();

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');
        //echo "<pre>"; print_r($product);exit;
        $action = 'edit';
        return view('product.edit')
                ->with(compact('categories', 'brands', 'units', 'sub_units', 'taxes', 'tax_attributes', 'barcode_types', 'product', 'sub_categories', 'default_profit_percent', 'business_locations', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data','contacts','action'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        // return $request->all();
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        // return $request->supplier_id;


        try {
            $business_id = $request->session()->get('user.business_id');
            $product_details = $request->only(['name', 'brand_id', 'unit_id', 'category_id', 'tax', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_description', 'sub_unit_ids','qty_box','case_qty','bin','srp','sales_price','weight','sync_ecom','supplier_id','note','sku2','sku3']);


            DB::beginTransaction();

            $product = Product::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(['product_variations','variations'])
                                ->first();
            $product_id = $id;
            $pro='';

            $BrandName = "";
            $CatName = "";
            if($product->brand_id){
               $BrandName = $this->getBrandNameById($product->brand_id);
            }
            if($product->category_id){
                $CatName = $this->getCategoryNameById($product->category_id);
            }
            $UnitName = $this->getUnitNameById($product->unit_id);


            // if($product->name != $request->input('name')){
            //     $pro = 'Item name, ';
            // }
            // if($product->ml != $request->input('ml')){
            //     $pro .= 'ML,';
            // }
            // if($product->item_code != $request->input('code')){
            //     $pro .= 'Item code,';
            // }
            // if($product->sku != $request->input('sku')){
            //     $pro .= 'Barcode1,';
            // }
            // if($product->sku2 != $request->input('sku2')){
            //     $pro .= 'Barcode2,';
            // }
            // if($product->sku3 != $request->input('sku3')){
            //     $pro .= 'Barcode3,';
            // }
            // if($product->unit_id != $request->input('unit_id')){
            //     $pro .= 'Unit,';
            // }
            // if($product->sub_unit_ids != $request->input('sub_unit_ids')){
            //     $pro .= 'Sub unit,';
            // }
            // if($product->brand_id != $request->input('brand_id')){
            //     $pro .= 'Brand,';
            // }
            // if($product->category_id != $request->input('category_id')){
            //     $pro .= 'Category,';
            // }
            // if($product->sub_category_id != $request->input('sub_category_id')){
            //     $pro .= 'Subcategory,';
            // }
            // if($product->not_for_selling != $request->input('not_for_selling')){
            //     $pro .= 'Not for selling,';
            // }
            // if($product->enable_stock != $request->input('enable_stock')){
            //     $pro .= 'Manage Stock,';
            // }
            // if($product->alert_quantity != $request->input('alert_quantity')){
            //     $pro .= 'Item Minimum,';
            // }
            // if($product->qty_box != $request->input('qty_box')){
            //     $pro .= 'Qty in box,';
            // }
            // if($product->case_qty != $request->input('case_qty')){
            //     $pro .= 'Case Qty,';
            // }
            // if($product->supplier_id != $request->input('supplier_id')){
            //     $pro .= 'Vendor,';
            // }
            // foreach($product->variations as $variations){
            //      if(number_format($variations->dpp_inc_tax,2) != $request->input('single_dpp_inc_tax')){
            //          $pro .= 'Cost,';
            //      }
            //      if(number_format($variations->sell_price_inc_tax,2) != $request->input('single_dsp')){
            //         $pro .= 'Selling Price,';
            //      }
            //      if(!empty($request->reset_last_prices)){
            //         $pro .= "Sell Price Reset,";
            //         $variations->sell_updated_at = date('Y-m-d H:i:s');
            //         $variations->save();
            //     }
            // }
            // // if(number_format($product->dpp_inc_tax,2) != $request->input('single_dpp_inc_tax')){
            // //     $pro .= 'Cost,';
            // // }
            // // if(number_format($product->sell_price_inc_tax,2) != $request->input('single_dsp')){
            // //     $pro .= 'Selling Price,';
            // // }
            // // $cost = $product->dpp_inc_tax;
            // // $sell_price = $product->default_sell_price;
            // // if(isset($cost) && $cost != null && $sell_price > 0)
            // // $profit_percent =(1 - ($cost / $sell_price))*100;

            // // // return number_format($profit_percent,2).' '.$request->input('profit_percent');

            // // if(number_format($profit_percent,2) != rtrim($request->input('profit_percent'), '0')){
            // //     $pro .= 'Gross Profit,';
            // // }
            // if($product->aisle != $request->input('aisle')){
            //     $pro .= 'Aisle,';
            // }
            // if($product->rack != $request->input('rack')){
            //     $pro .= 'Rack,';
            // }
            // if($product->shelf != $request->input('shelf')){
            //     $pro .= 'Shelf,';
            // }
            // if($product->bin != $request->input('bin')){
            //     $pro .= 'Bin,';
            // }
            // if($product->out_of_stock != $request->input('outofstock')){
            //     $pro .= 'Out of stock for website,';
            // }
            // if($product->woocommerce_disable_sync != $request->input('woocommerce_disable_sync')){
            //     $pro .= 'Do not sync with website,';
            // }
            // if($product->product_description != $request->input('product_description')){
            //     $pro .= 'Product description,';
            // }
            // if($product->srp != $request->input('srp')){
            //     $pro .= 'SRP,';
            // }
            // if($product->sales_price != $request->input('saleprice')){
            //     $pro .= 'Sale price,';
            // }
            // if($product->weight != $request->input('weight')){
            //     $pro .= 'Weight,';
            // }
            // // if($product->main_image != $request->main_image ){
            // //     $pro .= 'Main Image,';
            // // }
            // if($product->sync_ecom  != $request->input('sync_ecom')){
            //     $pro .= 'Do not sync with Ecomm,';
            // }
            // if($product->note != $request->input('note')){
            //     $pro .= 'Note';
            // }

            // if(!empty($request->t1_last_prices)){
            //     $pro .= "Tier 1 Price Reset,";
            // }
            // if(!empty($request->t2_last_prices)){
            //     $pro .= "Tier 2 Price Reset,";
            // }
            // if(!empty($request->t3_last_prices)){
            //     $pro .= "Tier 3 Price Reset,";
            // }

            if($product->name != $request->input('name')){
                $pro = 'Item name ('. strtoupper($product->name) .'  --> '. strtoupper($request->input('name')) .'), ';
            }
            if($product->ml != $request->input('ml')){
                $pro .= 'ML ('. $product->ml .'  --> '. $request->input('ml') .'), ';
            }
            if($product->item_code != $request->input('code')){
                $pro .= 'Item code ('. strtoupper($product->item_code) .'  --> '. strtoupper($request->input('code')) .'), ';
            }
            if($product->sku != $request->input('sku')){
                $pro .= 'Barcode1 ('. $product->sku .'  --> '. $request->input('sku') .'), ';
            }
            if($product->sku2 != $request->input('sku2')){
                $pro .= 'Barcode2 ('. $product->sku2 .'  --> '. $request->input('sku2') .'), ';
            }
            if($product->sku3 != $request->input('sku3')){
                $pro .= 'Barcode3 ('. $product->sku3 .'  --> '. $request->input('sku3') .'), ';
            }
            if($product->unit_id != $request->input('unit_id')){
                $pro .= 'Unit (' . $UnitName . ' --> ' . $this->getUnitNameById($request->input('unit_id')) . '), ';
            }
            if($product->sub_unit_ids != $request->input('sub_unit_ids')){
                $pro .= 'Sub unit ('. $product->sub_unit_ids .'  --> '. $request->input('sub_unit_ids') .'), ';
            }
            if($product->brand_id != $request->input('brand_id')){
                $pro .= 'Brand (' . $BrandName . ' --> ' . $this->getBrandNameById($request->input('brand_id')) . '), ';
            }
            if($product->category_id != $request->input('category_id')){
                $pro .= 'Category (' . $CatName . ' --> ' . $this->getCategoryNameById($request->input('category_id')) . '), ';
            }
            if($product->sub_category_id != $request->input('sub_category_id')){
                $pro .= 'Subcategory ('. $product->sub_category_id .'  --> '. $request->input('sub_category_id') .'), ';
            }
            if($product->not_for_selling != $request->input('not_for_selling')){
                // $pro .= 'Not for selling ('. $product->not_for_selling .'  --> '. $request->input('not_for_selling') .'), ';
                $pro .= 'Not for selling (' . ($product->not_for_selling ? 'checked' : 'unchecked') . ' --> ' . ($request->input('not_for_selling') ? 'checked' : 'unchecked') . '), ';
            }
            if($product->enable_stock != $request->input('enable_stock')){
                // $pro .= 'Manage Stock ('. $product->enable_stock .'  --> '. $request->input('enable_stock') .'), ';
                $pro .= 'Manage Stock (' . ($product->enable_stock ? 'checked' : 'unchecked') . ' --> ' . ($request->input('enable_stock') ? 'checked' : 'unchecked') . '), ';
            }
            if($product->alert_quantity != $request->input('alert_quantity')){
                $pro .= 'Item Minimum ('. $product->alert_quantity .'  --> '. $request->input('alert_quantity') .'), ';
            }
            if($product->qty_box != $request->input('qty_box')){
                $pro .= 'Qty in box ('. $product->qty_box .'  --> '. $request->input('qty_box') .'), ';
            }
            if($product->case_qty != $request->input('case_qty')){
                $pro .= 'Case Qty ('. $product->case_qty .'  --> '. $request->input('case_qty') .'), ';
            }
            if($product->supplier_id != $request->input('supplier_id')){
                $pro .= 'Vendor ('. $product->supplier_id .'  --> '. $request->input('supplier_id') .'), ';
            }
            foreach($product->variations as $variations){
                 if(number_format($variations->dpp_inc_tax,2) != $request->input('single_dpp_inc_tax')){
                     $pro .= 'Cost ('. number_format($variations->dpp_inc_tax,2) .'  --> '.number_format($request->input('single_dpp_inc_tax'),2) .'), ';
                 }
                 if(number_format($variations->sell_price_inc_tax,2) != $request->input('single_dsp')){
                    $pro .= 'Selling Price ('.number_format($variations->sell_price_inc_tax,2) .'  -->  '.number_format($request->input('single_dsp'),2) .'), ';
                 }
                 if(!empty($request->reset_last_prices)){
                    $pro .= "Sell Price Reset (" . $request->reset_last_prices . "),";
                    $variations->sell_updated_at = date('Y-m-d H:i:s');
                    $variations->save();
                }

                $tier1 = VariationGroupPrice::where('price_group_id', 68)->where('variation_id',$variations->id)->first();
                if(!empty($tier1->price_inc_tax) && round($tier1->price_inc_tax, 2) != round($request->input('single_dsp_tier1'),2)){
                    $pro .= 'TIER 1: Price ('.round($tier1->price_inc_tax,2) .'  -->  '.round($request->input('single_dsp_tier1'),2) .'), ';
                }
                elseif(empty($tier1->price_inc_tax)){
                    $pro .= 'TIER 1: Price ( -->  '.round($request->input('single_dsp_tier1'),2) .'), ';
                }

                $tier2 = VariationGroupPrice::where('price_group_id', 69)->where('variation_id',$variations->id)->first();
                if(!empty($tier2->price_inc_tax) && round($tier2->price_inc_tax, 2) != round($request->input('single_dsp_tier2'),2)){
                    $pro .= 'TIER 2: Price ('.round($tier2->price_inc_tax,2) .'  -->  '.round($request->input('single_dsp_tier2'),2) .'), ';
                }
                elseif(empty($tier2->price_inc_tax)){
                    $pro .= 'TIER 2: Price ( -->  '.round($request->input('single_dsp_tier2'),2) .'), ';
                }

                $tier3 = VariationGroupPrice::where('price_group_id', 70)->where('variation_id',$variations->id)->first();
                if(!empty($tier3->price_inc_tax) && round($tier3->price_inc_tax, 2) != round($request->input('single_dsp_tier3'),2)){
                    $pro .= 'TIER 3: Price ('.round($tier3->price_inc_tax,2) .'  -->  '.round($request->input('single_dsp_tier3'),2) .'), ';
                }
                elseif(empty($tier3->price_inc_tax)){
                    $pro .= 'TIER 3: Price ( -->  '.round($request->input('single_dsp_tier3'),2) .'), ';
                }


                $tier4 = VariationGroupPrice::where('price_group_id', 80)->where('variation_id',$variations->id)->first();
                if(!empty($tier4->price_inc_tax) && round($tier4->price_inc_tax, 2) != round($request->input('single_dsp_tier4'),2)){
                    $pro .= 'TIER 4: Price ('.round($tier4->price_inc_tax,2) .'  -->  '.round($request->input('single_dsp_tier4'),2) .'), ';
                }
                elseif(empty($tier4->price_inc_tax)){
                    $pro .= 'TIER 4: Price ( -->  '.round($request->input('single_dsp_tier4'),2) .'), ';
                }

            }
            if($product->aisle != $request->input('aisle')){
                $pro .= 'Aisle ('. $product->aisle .'  --> '. $request->input('aisle') .'), ';
            }
            if($product->rack != $request->input('rack')){
                $pro .= 'Rack ('. $product->rack .'  --> '. $request->input('rack') .'), ';
            }
            if($product->shelf != $request->input('shelf')){
                $pro .= 'Shelf ('. $product->shelf .'  --> '. $request->input('shelf') .'), ';
            }
            if($product->bin != $request->input('bin')){
                $pro .= 'Bin ('. $product->bin .'  --> '. $request->input('bin') .'), ';
            }
            if($product->out_of_stock != $request->input('outofstock')){
                $pro .= 'Out of stock for website (' . ($product->out_of_stock ? 'checked' : 'unchecked') . ' --> ' . ($request->input('outofstock') ? 'checked' : 'unchecked') . '), ';
            }
            if($product->woocommerce_disable_sync != $request->input('woocommerce_disable_sync')){
                $pro .= 'Do not sync with website (' . ($product->woocommerce_disable_sync ? 'checked' : 'unchecked') . ' --> ' . ($request->input('woocommerce_disable_sync') ? 'checked' : 'unchecked') . '), ';
            }
            if($product->product_description != $request->input('product_description')){
                $pro .= 'Product description,';
            }
            if($product->srp != $request->input('srp')){
                $pro .= 'SRP ('. $product->srp .'  --> '. $request->input('srp') .'), ';
            }
            if($product->sales_price != $request->input('saleprice')){
                $pro .= 'Sale price ('. $product->sales_price .'  --> '. $request->input('saleprice') .'), ';
            }
            if($product->weight != $request->input('weight')){
                $pro .= 'Weight ('. $product->weight .'  --> '. $request->input('weight') .'), ';
            }
            if($product->sync_ecom  != $request->input('sync_ecom')){
                $pro .= 'Do not sync with Ecomm (' . ($product->sync_ecom ? 'checked' : 'unchecked') . ' --> ' . ($request->input('sync_ecom') ? 'checked' : 'unchecked') . '), ';
            }
            if($product->note != $request->input('note')){
                $pro .= 'Note';
            }

            if(!empty($request->t1_last_prices)){
                $pro .= "Tier 1 Price Reset(" . $request->t1_last_prices . "),";
            }
            if(!empty($request->t2_last_prices)){
                $pro .= "Tier 2 Price Reset(" . $request->t2_last_prices . "),";
            }
            if(!empty($request->t3_last_prices)){
                $pro .= "Tier 3 Price Reset(" . $request->t3_last_prices . "),";
            }
            // return $pro;


            $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
            if (!empty($module_form_fields)) {
                foreach ($module_form_fields as $column) {
                    $product->$column = $request->input($column);
                }
            }

            // if ($request->hasFile('image')) {
            //     $image = $request->file('image');
            //     $imagename = uniqid().$image->getClientOriginalName();
            //     $uploadPath = 'public/product/image/';
            //     $image->move($uploadPath,$imagename);
            //     $imageUrl = $uploadPath.$imagename;
            // } else {
            //     $imageUrl = null;
            // }
            if ($request->hasFile('images')) {
                if(($request->images)){
                    $paths = [];
                    $i = 0;
                    foreach($request->images as $image){

                        $path = $image->store('img', 'local');
                        $paths[$i++] = '/'.$path;

                    }
                    $product_details['image'] = implode(",",$paths);
                }
                else{
                    $product_details['image'] = "";
                }
            }

            if($request->hasFile('main_image')){
                $mainImagePath = $request->main_image->store('img', 'local');
                $product_details['main_image'] = '/'.$mainImagePath;
                $product->main_image = $product_details['main_image'];
                $product->woocommerce_media_id = null;
            }
            else{
                $product->woocommerce_media_id = null;
            }
            if ($request->has('compressedFile')) {
                // Decode the Base64-encoded image data
                $base64Image = $request->input('compressedFile');
                $base64Image = str_replace('data:image/jpeg;base64,', '', $base64Image);
                $base64Image = str_replace(' ', '+', $base64Image);
                $decodedImageData = base64_decode($base64Image);

                // Determine the original file extension
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $decodedImageData);
                finfo_close($finfo);

                // Extract file extension from MIME type
                $fileExtension = explode('/', $mimeType)[1];

                // Generate a unique filename with the original file extension
                $fileName = uniqid() . '.' . $fileExtension;

                // Save the decoded image data to the public folder
                $filePath = '/compressed_images/' . $fileName;
                file_put_contents(public_path($filePath), $decodedImageData);
                // $product->compressed_image = $filePath;
                $product_details['compressed_image'] =  $filePath;
                $product->compressed_image = $product_details['compressed_image'];
            }
            $product->srp = $request->input('srp');
            $product->sales_price = $request->input('saleprice');

            $product->sync = $request->input('sync');
            $product->ml = $request->input('ml');
            $product->item_code = strtoupper($request->input('code',''));
            $product->sync_ecom = $request->input('sync_ecom');
            $product->qty_box = $request->input('qty_box');
            $product->case_qty = strtoupper($request->input('case_qty'));
            $product->name = strtoupper($product_details['name']);
            $product->brand_id = $product_details['brand_id'];
            $product->unit_id = $product_details['unit_id'];
            $product->category_id = $product_details['category_id'];
            // $product->tax = $product_details['tax'];
            // $product->barcode_type = $product_details['barcode_type'];
           $barcodesetting = BarcodeSetting::first();

            if(empty($request->sku)){

                if(empty($barcodesetting->last_barcode)){
                    $last_barcode = random_int(100000000000, 999999999999);

                    $substr =  substr($last_barcode, -6);
                    $substr = $substr + 1;
                    $c = 6;
                    $len = strlen($substr);
                    // $random = random_int(100000, 999999);
                    $random = 100000;
                    if($len < $c){

                        if($len == 5){
                            $barcode =  $random.'0'.$substr;
                        }
                        if($len == 4){
                            $barcode = $random.'00'.$substr;
                        }
                        if($len == 3){
                            $barcode = $random.'000'.$substr;
                        }
                        if($len == 2){
                            $barcode = $random.'0000'.$substr;
                        }
                        if($len == 1){
                            $barcode = $random.'00000'.$substr;
                        }
                    }else{
                        $new_barcode = $random.$substr;
                        $product_details['sku'] = $random.$substr;
                    }

                }else{
                    $new_barcode = $barcodesetting->last_barcode + 1;
                    $product_details['sku'] = $barcodesetting->last_barcode + 1;
                }

                addBarcode:
                if(!empty($request->sku)) $new_barcode = $request->sku;

                if(empty($barcodesetting)){
                    $barcode_setting = new BarcodeSetting();
                    $barcode_setting->last_barcode = $new_barcode;
                    $barcode_setting->save();
                }else{
                    $id = $barcodesetting->id;

                    $barcode_setting =  BarcodeSetting::find($id);
                    $barcode_setting->last_barcode = $new_barcode;
                    $barcode_setting->save();
                }
            }else{
                $sku_prefix = substr($request->sku,0,6);
                if($sku_prefix == 100000) goto addBarcode;
                $product_details['sku'] = $request->sku;
            }



            $product->sku = $product_details['sku'];
            //sku2 and sku3
            $product->sku2 = !empty($request->sku2) ? $request->sku2 : "";
            $product->sku3 = !empty($request->sku3) ? $request->sku3 : "";
            $product->alert_quantity = $product_details['alert_quantity'];
            // $product->tax_type = $product_details['tax_type'];
            $product->weight = $product_details['weight'];
            // $product->product_custom_field1 = $product_details['product_custom_field1'];
            // $product->product_custom_field2 = $product_details['product_custom_field2'];
            // $product->product_custom_field3 = $product_details['product_custom_field3'];
            // $product->product_custom_field4 = $product_details['product_custom_field4'];
            $product->product_description = $product_details['product_description'];
            $product->note = $product_details['note'];
            $product->sub_unit_ids = !empty($product_details['sub_unit_ids']) ? $product_details['sub_unit_ids'] : null;
            $product->warranty_id = !empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;
            $product->aisle = $request->input('aisle');
            $product->rack = $request->input('rack');
            $product->shelf = $request->input('shelf');
            $product->bin = $request->input('bin');
                $product->enable_vendor       = $request->input('enable_vendor') == 1 ? 1 : 0;
            $product->supplier_id = $request->input('supplier_id');

            $user_id = auth()->user()->id;
            $this->productUtil->ProductActivityLog('edited',$user_id,$product_id,$pro);


            if (!empty($request->input('enable_stock')) &&  $request->input('enable_stock') == 1) {
                $product->enable_stock = 1;
            } else {
                $product->enable_stock = 0;
            }

            $product->not_for_selling = (!empty($request->input('not_for_selling')) &&  $request->input('not_for_selling') == 1) ? 1 : 0;

            $product->out_of_stock = (!empty($request->input('outofstock')) &&  $request->input('outofstock') == 1) ? 1 : 0;

            if (!empty($request->input('sub_category_id'))) {
                $product->sub_category_id = $request->input('sub_category_id');
            } else {
                $product->sub_category_id = null;
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($expiry_enabled)) {
                if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && ($product->enable_stock == 1)) {
                    $product->expiry_period_type = $request->input('expiry_period_type');
                    $product->expiry_period = $this->productUtil->num_uf($request->input('expiry_period'));
                } else {
                    $product->expiry_period_type = null;
                    $product->expiry_period = null;
                }
            }

            if (!empty($request->input('enable_sr_no')) &&  $request->input('enable_sr_no') == 1) {
                $product->enable_sr_no = 1;
            } else {
                $product->enable_sr_no = 0;
            }

            //upload document
            // $file_name = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            // if (!empty($file_name)) {

            //     //If previous image found then remove
            //     if (!empty($product->image_path) && file_exists($product->image_path)) {
            //         unlink($product->image_path);
            //     }

            //     $product->image = $file_name;
            //     //If product image is updated update woocommerce media id
            //     if (!empty($product->woocommerce_media_id)) {
            //         $product->woocommerce_media_id = null;
            //     }
            // }
            $product->item_code = !empty($product->item_code) ? $product->item_code : $product->sku;
            $this->productUtil->ProductActivitiesLog('edited',$user_id,$product_id,$product);
            $product->save();
            $product->touch();

            //Add product locations
            // $product_locations = !empty($request->input('product_locations')) ?
            //                     $request->input('product_locations') : [];
            // $product->product_locations()->sync($product_locations);
            // return $request->single_variation_id;

            if ($product->type == 'single') {
                $single_data = $request->only(['single_variation_id', 'single_dpp', 'single_dpp_inc_tax', 'profit_percent', 'single_dsp']);

                $variation = Variation::find($single_data['single_variation_id']);

                $variation->sub_sku = $product->sku;
                $variation->default_purchase_price = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']);
                $variation->dpp_inc_tax = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']);
                $variation->profit_percent = $this->productUtil->num_uf($single_data['profit_percent']);
                $variation->default_sell_price = $this->productUtil->num_uf($single_data['single_dsp']);
                $variation->sell_price_inc_tax = $this->productUtil->num_uf($single_data['single_dsp']);
                $variation->save();

                // product tier price start
                if(isset($variation))
                {
                    $this->productUtil->AddOrUpdateProductTiersPrice(
                        $variation,
                        $request->input('single_dsp_tier1'),
                        $request->input('single_dsp_tier2'),
                        $request->input('single_dsp_tier3'),
                        $request->input('single_dsp_tier4'),
                        !empty($request->t1_last_prices),
                        !empty($request->t2_last_prices),
                        !empty($request->t3_last_prices),
                        !empty($request->t4_last_prices)
                    );
                }
                // product tier price end

                Media::uploadMedia($product->business_id, $variation, $request, 'variation_images');
            } elseif ($product->type == 'variable') {
                //Update existing variations
                $location_id = 0;
                    if(!empty($request->product_locations)){
                        $location_id = $request->product_locations[0];
                    }
                $input_variations_edit = $request->get('product_variation_edit');
                if (!empty($input_variations_edit)) {
                    $this->productUtil->updateVariableProductVariations($product->id, $input_variations_edit,$location_id);
                }

                //Add new variations created.
                $location_id = 0;
                if(!empty($request->input('product_locations'))){
                    $product_locations = $request->input('product_locations');
                    $location_id = $product_locations[0];
                }
                $input_variations = $request->input('product_variation');
                if (!empty($input_variations)) {
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations,$location_id);
                }
            } elseif ($product->type == 'combo') {

                //Create combo_variations array by combining variation_id and quantity.
                $combo_variations = [];
                if (!empty($request->input('composition_variation_id'))) {
                    $composition_variation_id = $request->input('composition_variation_id');
                    $quantity = $request->input('quantity');
                    $unit = $request->input('unit');

                    foreach ($composition_variation_id as $key => $value) {
                        $combo_variations[] = [
                                'variation_id' => $value,
                                'quantity' => $quantity[$key],
                                'unit_id' => $unit[$key]
                            ];
                    }
                }

                $variation = Variation::find($request->input('combo_variation_id'));
                $variation->sub_sku = $product->sku;
                $variation->default_purchase_price = $this->productUtil->num_uf($request->input('item_level_purchase_price_total'));
                $variation->dpp_inc_tax = $this->productUtil->num_uf($request->input('purchase_price_inc_tax'));
                $variation->profit_percent = $this->productUtil->num_uf($request->input('profit_percent'));
                $variation->default_sell_price = $this->productUtil->num_uf($request->input('selling_price'));
                $variation->sell_price_inc_tax = $this->productUtil->num_uf($request->input('selling_price_inc_tax'));
                $variation->combo_variations = $combo_variations;
                $variation->save();
            }

            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (!empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }

            $product_racks_update = $request->get('product_racks_update', null);
            if (!empty($product_racks_update)) {
                $this->productUtil->updateRackDetails($business_id, $product->id, $product_racks_update);
            }

            //Set Module fields
            if (!empty($request->input('has_module_data'))) {
                $this->moduleUtil->getModuleData('after_product_saved', ['product' => $product, 'request' => $request]);
            }

            //update selling price
            $this->updateSellingPrice($this->productUtil->num_uf($single_data['single_dsp']), $product);

            DB::commit();
            $output = ['success' => 1,
                            'msg' => __('product.product_updated_success')
                        ];

            //sync
            if ($request->input('syncsave') == 'Update & Sync with Website') {
                Session::put('sync_url',$product->id ?? 0);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }

        if ($request->input('submit_type') == 'update_n_edit_opening_stock') {
            return redirect()->action(
                'OpeningStockController@add',
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action(
                'ProductController@addSellingPrices',
                [$product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                'ProductController@create'
            )->with('status', $output);
        }

        return redirect('products')->with('status', $output);
    }

    private function getBrandNameById($Brandid)
    {
        $Brands = Brands::find($Brandid);
        return !empty($Brands->name) ? $Brands->name : '';
    }
    private function getCategoryNameById($Categoryid)
    {
        $Category = Category::find($Categoryid);
        return !empty($Category->name) ? $Category->name : '';
    }
    private function getUnitNameById($Unitid)
    {
        $unit = Unit::find($Unitid);
        if(!empty($unit)){
            return $unit->actual_name . ' ' . '(' . $unit->short_name . ')';
        }
        else{
            return '';
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $can_be_deleted = true;
                $error_msg = '';

                //Check if any purchase or transfer exists
                $count = PurchaseLine::join(
                    'transactions as T',
                    'purchase_lines.transaction_id',
                    '=',
                    'T.id'
                )
                                    ->whereIn('T.type', ['purchase'])
                                    ->where('T.business_id', $business_id)
                                    ->where('purchase_lines.product_id', $id)
                                    ->count();
                if ($count > 0) {
                    $can_be_deleted = false;
                    $error_msg = __('lang_v1.purchase_already_exist');
                } else {
                    //Check if any opening stock sold
                    $count = PurchaseLine::join(
                        'transactions as T',
                        'purchase_lines.transaction_id',
                        '=',
                        'T.id'
                     )
                                    ->where('T.type', 'opening_stock')
                                    ->where('T.business_id', $business_id)
                                    ->where('purchase_lines.product_id', $id)
                                    ->where('purchase_lines.quantity_sold', '>', 0)
                                    ->count();
                    if ($count > 0) {
                        $can_be_deleted = false;
                        $error_msg = __('lang_v1.opening_stock_sold');
                    } else {
                        //Check if any stock is adjusted
                        $count = PurchaseLine::join(
                            'transactions as T',
                            'purchase_lines.transaction_id',
                            '=',
                            'T.id'
                        )
                                    ->where('T.business_id', $business_id)
                                    ->where('purchase_lines.product_id', $id)
                                    ->where('purchase_lines.quantity_adjusted', '>', 0)
                                    ->count();
                        if ($count > 0) {
                            $can_be_deleted = false;
                            $error_msg = __('lang_v1.stock_adjusted');
                        }
                    }
                }

                $product = Product::where('id', $id)
                                ->where('business_id', $business_id)
                                ->with('variations')
                                ->first();

                //Check if product is added as an ingredient of any recipe
                if ($this->moduleUtil->isModuleInstalled('Manufacturing')) {
                    $variation_ids = $product->variations->pluck('id');

                    $exists_as_ingredient = \Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)
                        ->exists();
                        if ($exists_as_ingredient) {
                            $can_be_deleted = false;
                            $error_msg = __('manufacturing::lang.added_as_ingredient');
                        }
                }

                if ($can_be_deleted) {
                    if (!empty($product)) {
                        // $res = SmartSyncHelper::deleteSelectedProducts([$product->id]);

                        DB::beginTransaction();
                        //Delete variation location details
                        VariationLocationDetails::where('product_id', $id)
                                                ->delete();
                        $product->delete();

                        DB::commit();

                        $user_id = auth()->user()->id;
                        $product_id = $id;

                        $this->productUtil->ProductActivityLog('deleted',$user_id,$product_id);
                    }

                    $output = ['success' => true,
                                'msg' => __("lang_v1.product_delete_success")
                            ];
                } else {
                    $output = ['success' => false,
                                'msg' => $error_msg
                            ];
                }
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                            ];
            }

            return $output;
        }
    }

    /**
     * Get subcategories list for a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSubCategories(Request $request)
    {

        if (!empty($request->input('cat_id'))){
            $category_id = $request->input('cat_id');
            $business_id = $request->session()->get('user.business_id');
            $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $category_id)
                        ->select(['name', 'id'])
                        ->get();
            $html = '<option value="">None</option>';
            if (!empty($sub_categories)) {
                foreach ($sub_categories as $sub_category) {
                    $html .= '<option value="' . $sub_category->id .'">' .$sub_category->name . '</option>';
                }
            }
            return $html;
            // echo $html;
            // exit;
        }
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductVariationFormPart(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $action = $request->input('action');
        if ($request->input('action') == "add") {
            if ($request->input('type') == 'single') {
                return view('product.partials.single_product_form_part')
                        ->with(['profit_percent' => $profit_percent]);
            } elseif ($request->input('type') == 'variable') {
                $variation_templates = VariationTemplate::where('business_id', $business_id)->pluck('name', 'id')->toArray();
                $variation_templates = [ "" => __('messages.please_select')] + $variation_templates;

                return view('product.partials.variable_product_form_part')
                        ->with(compact('variation_templates', 'profit_percent', 'action'));
            } elseif ($request->input('type') == 'combo') {
                return view('product.partials.combo_product_form_part')
                ->with(compact('profit_percent', 'action'));
            }
        } elseif ($request->input('action') == "edit" || $request->input('action') == "duplicate") {
            $product_id = $request->input('product_id');
            $action = $request->input('action');
            $purchase_line = PurchaseLine::where('product_id',$product_id)->get();
            // return $purchase_line;

            if ($request->input('type') == 'single') {
                 $product_deatails = ProductVariation::where('product_id', $product_id)
                    ->with(['variations', 'variations.media','variations.variation_location_details','variations.group_prices'])
                    ->first();
                $qty=0.00;

                // product tier price start
                $variation_tier_prices = [];
                // product tier price end
                if(count($product_deatails->variations) > 0){
                    // return $product->variations;
                    foreach($product_deatails->variations as $variation){
                        if(count($variation->variation_location_details) > 0){
                            foreach($variation->variation_location_details as $var){
                                if($var->location_id == 4){
                                    $qty = $var->qty_available;
                                }
                            }
                        }
                        // product tier price start
                        if(count($variation->group_prices) > 0){
                            foreach ($variation->group_prices as $group_price) {
                                if($group_price->price_group_id == 68)
                                {
                                    $variation_tier_prices['single_dsp_tier1'] = $group_price->price_inc_tax;
                                }
                                if($group_price->price_group_id == 69)
                                {
                                    $variation_tier_prices['single_dsp_tier2'] = $group_price->price_inc_tax;
                                }
                                if($group_price->price_group_id == 70)
                                {
                                    $variation_tier_prices['single_dsp_tier3'] = $group_price->price_inc_tax;
                                }
                                if($group_price->price_group_id == 80)
                                {
                                    $variation_tier_prices['single_dsp_tier4'] = $group_price->price_inc_tax;
                                }
                            }
                        }
                        // product tier price end
                    }
                }

                if($request->input('action') == "duplicate"){
                    $qty = 0;
                }
                /*echo "<pre>";
                print_r($variation_tier_prices);
                echo "</pre>";
                exit;*/
                // return $product_deatails->variations;
                // return  ProductVariation::where('product_id', $product_id)->get();
                // product tier price start
                return view('product.partials.edit_single_product_form_part')
                            ->with(compact('product_deatails', 'action', 'purchase_line','qty','variation_tier_prices'));
                // product tier price end
            } elseif ($request->input('type') == 'variable') {
                $product_variations = ProductVariation::where('product_id', $product_id)
                        ->with(['variations', 'variations.media'])
                        ->get();
                return view('product.partials.variable_product_form_part')
                        ->with(compact('product_variations', 'profit_percent', 'action', 'purchase_line'));
            } elseif ($request->input('type') == 'combo') {
                $product_deatails = ProductVariation::where('product_id', $product_id)
                    ->with(['variations', 'variations.media'])
                    ->first();
                $combo_variations = $this->productUtil->__getComboProductDetails($product_deatails['variations'][0]->combo_variations, $business_id);

                $variation_id = $product_deatails['variations'][0]->id;
                $profit_percent = $product_deatails['variations'][0]->profit_percent;
                return view('product.partials.combo_product_form_part')
                ->with(compact('combo_variations', 'profit_percent', 'action', 'variation_id'));
            }
        }
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVariationValueRow(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $variation_index = $request->input('variation_row_index');
        $value_index = $request->input('value_index') + 1;

        $row_type = $request->input('row_type', 'add');

        return view('product.partials.variation_value_row')
                ->with(compact('profit_percent', 'variation_index', 'value_index', 'row_type'));
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getProductVariationRow(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $variation_templates = VariationTemplate::where('business_id', $business_id)
                                                ->pluck('name', 'id')->toArray();
        $variation_templates = [ "" => __('messages.please_select')] + $variation_templates;

        $row_index = $request->input('row_index', 0);
        $action = $request->input('action');

        return view('product.partials.product_variation_row')
                    ->with(compact('variation_templates', 'row_index', 'action', 'profit_percent'));
    }

    /**
     * Get product form parts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVariationTemplate(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;

        $template = VariationTemplate::where('id', $request->input('template_id'))
                                                ->with(['values'])
                                                ->first();
        $row_index = $request->input('row_index');
        return view('product.partials.product_variation_template')
                    ->with(compact('template', 'row_index', 'profit_percent'));
    }

    /**
     * Return the view for combo product row
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getComboProductEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = $request->session()->get('user.business_id');

            if (!empty($product_id)) {
                $product = Product::where('id', $product_id)
                        ->with(['unit'])
                        ->first();

                $query = Variation::where('product_id', $product_id)
                        ->with(['product_variation']);

                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }
                $variations =  $query->get();

                $sub_units = $this->productUtil->getSubUnits($business_id, $product['unit']->id);

                return view('product.partials.combo_product_entry_row')
                ->with(compact('product', 'variations', 'sub_units'));
            }
        }
    }

    /**
     * Retrieves products list.
     *
     * @param  string  $q
     * @param  boolean  $check_qty
     *
     * @return JSON
     */
    public function getProducts()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku', 'item_code']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
                $search_fields[] = 'sku2';
                $search_fields[] = 'sku3';
            }

            $search_term = str_replace(' ','%',$search_term);

           $result = $this->productUtil->filterPosProduct($business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);
           foreach ($result as $key => $value) {
                $C = ' | C: $'.  number_format($value['dpp_inc_tax'], 2);
                $SP = ' | SP: $'.  number_format($value['sell_price_inc_tax'], 2);
                $OH = ' | OH: '. (integer)VariationLocationDetails::where('variation_id', $value['variation_id'])->value('qty_available') ?? 0;

                $notSell = '';
                $active = '';
                if($value['is_inactive'] === 1)
                {
                    $active = ' | (Item <span class="label bg-gray">' . __("lang_v1.inactive") .'</span>)';
                }else{
                    $active = ' | (Item <span class="label bg-gray">Active</span>)';
                }
                if($value['not_for_selling'] === 1)
                {
                    $notSell = ' | <span class="label bg-gray">' . __("lang_v1.not_for_selling") .
                '</span>';
                }
                $result[$key]['text'] = $C .$SP .$OH .$active .$notSell;
           }

            return $result;
        }
    }

    /**
     * Retrieves products list.
     *
     * @param  string  $q
     * @param  boolean  $check_qty
     *
     * @return JSON
     */
    public function getPosProducts1()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }

            $result = $this->productUtil->filterProduct($business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);

            return $result;
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPosProducts()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
                $search_fields[] = 'sku2';
                $search_fields[] = 'sku3';
            }

            $search_term = str_replace(' ','%',$search_term);
            $products = $this->productUtil->filterPosProduct($business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);

            return DataTables::of($products)
            // ->addColumn('item_code', function ($products) {
            //     return $products->item_code;
            // })
            ->addColumn('name', function ($products) {
                $redColorStyle = "";
                if($products->qty_available<0)
                {
                    $redColorStyle = 'style="color:red;"';
                }
                $item_name = "";
                if(strlen($products->name)>60)
                {
                    $item_name = wordwrap($products->name,60,"</br>",TRUE);
                }
                else
                {
                    $item_name = $products->name;
                }
                if(!empty($products->web_reg_price) && $products->web_reg_price > 0){
                    if(!empty($products->web_sale_price) && $products->web_sale_price > 0){
                        $item_name .= '<?xml version="1.0" encoding="utf-8"?>
<svg viewBox="200 110.714 153.571 41.071" width="76.7855" height="20.5355" xmlns="http://www.w3.org/2000/svg">
  <rect x="200" y="110.714" width="153.571" height="41.071" style="stroke: rgb(0, 0, 0); fill: rgb(0, 231, 148); stroke-width: 0px;" rx="20.535" ry="20.535"/>
  <text style="fill: rgb(255, 255, 255); font-family: Arial, sans-serif; font-size: 28px; white-space: pre;" x="224.107" y="140.178" transform="matrix(1.1765029430389404, 0, 0, 1.1428569555282593, -45.80556869506836, -19.132572174072266)">On Sale</text>
</svg>';
                    }
                }
                return '<div class="ellipsis" tabindex="-1" id="'.$products->variation_id.'"><a '.$redColorStyle.' href="javascript:void(0);" class="product-link" onclick="addToDataTableOne('.$products->variation_id.')">'.$item_name.'</a></div>';
                // return $products->name;
            })->addColumn('dpp_inc_tax', function ($products) {
                return $products->dpp_inc_tax;
            })->addColumn('default_sell_price', function ($products) {
                return "$". round($products->default_sell_price, 2);
            })->addColumn('qty_available', function ($products) {
                return round($products->qty_available);
            })
            ->escapeColumns('name')
            ->make(true);
        }
    }

    public function getsellReturnProducts()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku', 'item_code']);

            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }


           $result = $this->productUtil->filterProductForReturns($business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);
           foreach ($result as $key => $value) {
                $C = ' | C: $'.  number_format($value['dpp_inc_tax'], 2);
                $SP = ' | SP: $'.  number_format($value['sell_price_inc_tax'], 2);
                $OH = ' | OH: '. (integer)VariationLocationDetails::where('variation_id', $value['variation_id'])->value('qty_available') ?? 0;

                $notSell = '';
                $active = '';
                if($value['is_inactive'] === 1)
                {
                    $active = ' | (Item <span class="label bg-gray">' . __("lang_v1.inactive") .'</span>)';
                }else{
                    $active = ' | (Item <span class="label bg-gray">Active</span>)';
                }
                if($value['not_for_selling'] === 1)
                {
                    $notSell = ' | <span class="label bg-gray">' . __("lang_v1.not_for_selling") .
                '</span>';
                }
                $result[$key]['text'] = $C .$SP .$OH .$active .$notSell;
                // return $result;
           }

            return $result;
        }
    }


    /**
     * Retrieves products list without variation list
     *
     * @param  string  $q
     * @param  boolean  $check_qty
     *
     * @return JSON
     */
    public function getProductsWithoutVariations()
    {
        if (request()->ajax()) {
            $term = request()->input('term', '');
            //$location_id = request()->input('location_id', '');

            //$check_qty = request()->input('check_qty', false);

            $business_id = request()->session()->get('user.business_id');

            $products = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                });
            }

            //Include check for quantity
            // if($check_qty){
            //     $products->where('VLD.qty_available', '>', 0);
            // }

            $products = $products->groupBy('products.id')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    'products.enable_stock',
                    'products.sku'
                )
                    ->orderBy('products.name')
                    ->get();
            return json_encode($products);
        }
    }

    /**
     * Checks if product sku already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkProductSku(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $sku = $request->input('sku');
        $product_id = $request->input('product_id');

        //check in products table
        $query = Product::where('business_id', $business_id)
                        ->where('sku', $sku);
        if (!empty($product_id)) {
            $query->where('id', '!=', $product_id);
        }
        $count = $query->count();

        //check in variation table if $count = 0
        if ($count == 0) {
            $count = Variation::where('sub_sku', $sku)
                            ->join('products', 'variations.product_id', '=', 'products.id')
                            ->where('product_id', '!=', $product_id)
                            ->where('business_id', $business_id)
                            ->count();
        }
        // if ($count == 0) {
        //     echo "true";
        //     exit;
        // } else {
        //     echo "false";
        //     exit;
        // }
    }

    /**
     * Loads quick add product modal.
     *
     * @return \Illuminate\Http\Response
     */
    public function quickAdd_20_05_2021()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $product_name = !empty(request()->input('product_name'))? request()->input('product_name') : '';

        $product_for = !empty(request()->input('product_for'))? request()->input('product_for') : null;

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::forDropdown($business_id, true);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;

        $default_profit_percent = Business::where('id', $business_id)->value('default_profit_percent');

        $locations = BusinessLocation::forDropdown($business_id);

        $enable_expiry = request()->session()->get('business.enable_product_expiry');
        $enable_lot = request()->session()->get('business.enable_lot_number');

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        return view('product.partials.quick_add_product')
                ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'product_name', 'locations', 'product_for', 'enable_expiry', 'enable_lot', 'module_form_parts', 'business_locations', 'common_settings', 'warranties'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveQuickProduct(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $form_fields = ['name', 'brand_id', 'unit_id', 'category_id', 'tax', 'barcode_type','tax_type', 'sku',
                'alert_quantity', 'type', 'sub_unit_ids', 'sub_category_id', 'weight', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_description'];

            $module_form_fields = $this->moduleUtil->getModuleData('product_form_fields');
            if (!empty($module_form_fields)) {
                foreach ($module_form_fields as $key => $value) {
                    if (!empty($value) && is_array($value)) {
                        $form_fields = array_merge($form_fields, $value);
                    }
                }
            }
            $product_details = $request->only($form_fields);

            $product_details['type'] = empty($product_details['type']) ? 'single' : $product_details['type'];
            $product_details['business_id'] = $business_id;
            $product_details['created_by'] = $request->session()->get('user.id');
            if (!empty($request->input('enable_stock')) &&  $request->input('enable_stock') == 1) {
                $product_details['enable_stock'] = 1 ;
                //TODO: Save total qty
                //$product_details['total_qty_available'] = 0;
            }
            if (!empty($request->input('not_for_selling')) &&  $request->input('not_for_selling') == 1) {
                $product_details['not_for_selling'] = 1 ;
            }
            if (empty($product_details['sku'])) {
                $product_details['sku'] = ' ';
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && !empty($expiry_enabled)) {
                $product_details['expiry_period_type'] = $request->input('expiry_period_type');
                $product_details['expiry_period'] = $this->productUtil->num_uf($request->input('expiry_period'));
            }

            if (!empty($request->input('enable_sr_no')) &&  $request->input('enable_sr_no') == 1) {
                $product_details['enable_sr_no'] = 1 ;
            }

            $product_details['warranty_id'] = !empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;

            DB::beginTransaction();

            $product = Product::create($product_details);

            if (empty(trim($request->input('sku')))) {
                $sku = $this->productUtil->generateProductSku($product->id);
                $product->sku = $sku;
                $product->save();
            }

            $this->productUtil->createSingleProductVariation(
                $product->id,
                $product->sku,
                // $request->input('single_dpp'),
                $request->input('single_dpp_inc_tax'),
                $request->input('profit_percent'),
                $request->input('single_dsp'),
                $request->input('single_dsp_inc_tax')
            );

            if ($product->enable_stock == 1 && !empty($request->input('opening_stock'))) {
                $user_id = $request->session()->get('user.id');

                $transaction_date = $request->session()->get("financial_year.start");
                $transaction_date = \Carbon::createFromFormat('Y-m-d', $transaction_date)->toDateTimeString();

                $this->productUtil->addSingleProductOpeningStock($business_id, $product, $request->input('opening_stock'), $transaction_date, $user_id);
            }

            //Add product locations
            $product_locations = $request->input('product_locations');
            if (!empty($product_locations)) {
                $product->product_locations()->sync($product_locations);
            }

            DB::commit();

            $output = ['success' => 1,
                            'msg' => __('product.product_added_success'),
                            'product' => $product,
                            'variation' => $product->variations->first(),
                            'locations' => $product_locations
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $product = Product::where('business_id', $business_id)
                        ->with(['brand', 'unit', 'category', 'sub_category', 'product_tax', 'variations', 'variations.product_variation', 'variations.group_prices', 'variations.media', 'product_locations', 'warranty','variations.variation_location_details'])

                        ->findOrFail($id);

            $productlog = ProductActivityLog::join('users','users.id','=','product_activity_log.user_id')
              ->where('product_id',$id)
                ->select('product_activity_log.created_at as datetime',
                    'product_activity_log.message as message',
                    'product_activity_log.description as description',
                    'users.first_name as first_name'
                )
                ->get();


            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

            $allowed_group_prices = [];
            foreach ($price_groups as $key => $value) {
                if (auth()->user()->can('selling_price_group.' . $key)) {
                    $allowed_group_prices[$key] = $value;
                }
            }

            $group_price_details = [];

            foreach ($product->variations as $variation) {
                foreach ($variation->group_prices as $group_price) {
                    $group_price_details[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
                }
            }

            $qty='';
            if(count($product->variations) > 0){
                // return $product->variations;
                foreach($product->variations as $variation){
                    if($variation->variation_location_details){
                        foreach($variation->variation_location_details as $var){
                            if($var->location_id == 4){
                                $qty = $var->qty_available;
                            }
                        }
                    }
                }
            }

            // return $qty;

            $rack_details = $this->productUtil->getRackDetails($business_id, $id, true);

            $combo_variations = [];
            if ($product->type == 'combo') {
                $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $business_id);
            }

            return view('product.view-modal')->with(compact(
                'product',
                'rack_details',
                'allowed_group_prices',
                'group_price_details',
                'combo_variations',
                'qty',
                'productlog'
            ));
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }
    }

    /**
     * Mass deletes products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(Request $request)
    {
        if (!auth()->user()->can('product.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $purchase_exist = false;

            if (!empty($request->input('selected_rows'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_rows = explode(',', $request->input('selected_rows'));

                $products = Product::where('business_id', $business_id)
                                    ->whereIn('id', $selected_rows)
                                    ->with(['purchase_lines', 'variations'])
                                    ->get();
                $deletable_products = [];

                $is_mfg_installed = $this->moduleUtil->isModuleInstalled('Manufacturing');

                DB::beginTransaction();

                foreach ($products as $product) {
                    $can_be_deleted = true;
                    //Check if product is added as an ingredient of any recipe
                    if ($is_mfg_installed) {
                        $variation_ids = $product->variations->pluck('id');

                        $exists_as_ingredient = \Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)
                            ->exists();
                        $can_be_deleted = !$exists_as_ingredient;
                    }

                    //Delete if no purchase found
                    if (empty($product->purchase_lines->toArray()) && $can_be_deleted) {
                        $res = SmartSyncHelper::deleteSelectedProducts([$product->id]);
                        //Delete variation location details
                        VariationLocationDetails::where('product_id', $product->id)
                                                    ->delete();
                        $product->delete();
                    } else {
                        $purchase_exist = true;
                    }
                }

                DB::commit();
            }

            if (!$purchase_exist) {
                $output = ['success' => 1,
                            'msg' => __('lang_v1.deleted_success')
                        ];
            } else {
                $output = ['success' => 0,
                            'msg' => __('lang_v1.products_could_not_be_deleted')
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Shows form to add selling price group prices for a product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addSellingPrices($id)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $product = Product::where('business_id', $business_id)
                    ->with(['variations', 'variations.group_prices', 'variations.product_variation'])
                            ->findOrFail($id);

        $price_groups = SellingPriceGroup::where('business_id', $business_id)
                                            ->active()
                                            ->get();
        $variation_prices = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $variation_prices[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
            }
        }
        return view('product.add-selling-prices')->with(compact('product', 'price_groups', 'variation_prices'));
    }

    /**
     * Saves selling price group prices for a product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveSellingPrices(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');
            $product = Product::where('business_id', $business_id)
                            ->with(['variations'])
                            ->findOrFail($request->input('product_id'));
            DB::beginTransaction();
            foreach ($product->variations as $variation) {
                $variation_group_prices = [];
                foreach ($request->input('group_prices') as $key => $value) {
                    if (isset($value[$variation->id])) {
                        $variation_group_price =
                        VariationGroupPrice::where('variation_id', $variation->id)
                                            ->where('price_group_id', $key)
                                            ->first();
                        if (empty($variation_group_price)) {
                            $variation_group_price = new VariationGroupPrice([
                                    'variation_id' => $variation->id,
                                    'price_group_id' => $key
                                ]);
                        }

                        $variation_group_price->price_inc_tax = $this->productUtil->num_uf($value[$variation->id]);
                        $variation_group_prices[] = $variation_group_price;
                    }
                }

                if (!empty($variation_group_prices)) {
                    $variation->group_prices()->saveMany($variation_group_prices);
                }
            }
            //Update product updated_at timestamp
            $product->touch();

            DB::commit();
            $output = ['success' => 1,
                            'msg' => __("lang_v1.updated_success")
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        if ($request->input('submit_type') == 'submit_n_add_opening_stock') {
            return redirect()->action(
                'OpeningStockController@add',
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                'ProductController@create'
            )->with('status', $output);
        }

        return redirect('products')->with('status', $output);
    }

    public function viewGroupPrice($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
                            ->where('id', $id)
                            ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
                            ->first();

        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        $allowed_group_prices = [];
        foreach ($price_groups as $key => $value) {
            if (auth()->user()->can('selling_price_group.' . $key)) {
                $allowed_group_prices[$key] = $value;
            }
        }

        $group_price_details = [];

        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $group_price_details[$variation->id][$group_price->price_group_id] = $group_price->price_inc_tax;
            }
        }

        return view('product.view-product-group-prices')->with(compact('product', 'allowed_group_prices', 'group_price_details'));
    }

    /**
     * Mass deactivates products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDeactivate(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if (!empty($request->input('selected_products'))) {
                $business_id = $request->session()->get('user.business_id');

                $selected_products = explode(',', $request->input('selected_products'));

                DB::beginTransaction();

                $products = Product::where('business_id', $business_id)
                                    ->whereIn('id', $selected_products)
                                    ->update(['is_inactive' => 1]);

                DB::commit();
            }

            $output = ['success' => 1,
                            'msg' => __('lang_v1.products_deactivated_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Activates the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $product = Product::where('id', $id)
                                ->where('business_id', $business_id)
                                ->update(['is_inactive' => 0]);

                $output = ['success' => true,
                                'msg' => __("lang_v1.updated_success")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                            ];
            }

            return $output;
        }
    }

    /**
     * Deletes a media file from storage and database.
     *
     * @param  int  $media_id
     * @return json
     */
    public function deleteMedia($media_id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                Media::deleteMedia($business_id, $media_id);

                $output = ['success' => true,
                                'msg' => __("lang_v1.file_deleted_successfully")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                            ];
            }

            return $output;
        }
    }

    public function getProductsApi($id = null)
    {
        try {
            $api_token = request()->header('API-TOKEN');
            $filter_string = request()->header('FILTERS');
            $order_by = request()->header('ORDER-BY');

            parse_str($filter_string, $filters);

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $limit = !empty(request()->input('limit')) ? request()->input('limit') : 10;

            $location_id = $api_settings->location_id;

            $query = Product::where('business_id', $api_settings->business_id)
                            ->active()
                            ->with(['brand', 'unit', 'category', 'sub_category',
                                'product_variations', 'product_variations.variations', 'product_variations.variations.media',
                                'product_variations.variations.variation_location_details' => function ($q) use ($location_id) {
                                    $q->where('location_id', $location_id);
                                }]);

            if (!empty($filters['categories'])) {
                $query->whereIn('category_id', $filters['categories']);
            }

            if (!empty($filters['brands'])) {
                $query->whereIn('brand_id', $filters['brands']);
            }

            if (!empty($filters['category'])) {
                $query->where('category_id', $filters['category']);
            }

            if (!empty($filters['sub_category'])) {
                $query->where('sub_category_id', $filters['sub_category']);
            }

            if ($order_by == 'name') {
                $query->orderBy('name', 'asc');
            } elseif ($order_by == 'date') {
                $query->orderBy('created_at', 'desc');
            }

            if (empty($id)) {
                $products = $query->paginate($limit);
            } else {
                $products = $query->find($id);
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($products);
    }

    public function getVariationsApi()
    {
        try {
            $api_token = request()->header('API-TOKEN');
            $variations_string = request()->header('VARIATIONS');

            if (is_numeric($variations_string)) {
                $variation_ids = intval($variations_string);
            } else {
                parse_str($variations_string, $variation_ids);
            }

            $api_settings = $this->moduleUtil->getApiSettings($api_token);
            $location_id = $api_settings->location_id;
            $business_id = $api_settings->business_id;

            $query = Variation::with([
                                'product_variation',
                                'product' => function ($q) use ($business_id) {
                                    $q->where('business_id', $business_id);
                                },
                                'product.unit',
                                'variation_location_details' => function ($q) use ($location_id) {
                                    $q->where('location_id', $location_id);
                                }
                            ]);

            $variations = is_array($variation_ids) ? $query->whereIn('id', $variation_ids)->get() : $query->where('id', $variation_ids)->first();
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($variations);
    }

    /**
     * Shows form to edit multiple products at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkEdit(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $selected_products_string = $request->input('selected_products');
        // return $selected_products_string;
        if (!empty($selected_products_string)) {
            $selected_products = explode(',', $selected_products_string);
            $business_id = $request->session()->get('user.business_id');

            $products = Product::where('business_id', $business_id)
                                ->whereIn('id', $selected_products)
                                ->with(['variations', 'variations.product_variation', 'variations.group_prices', 'product_locations'])
                                ->get();

            $all_categories = Category::catAndSubCategories($business_id);

            $categories = [];
            $sub_categories = [];
            foreach ($all_categories as $category) {
                $categories[$category['id']] = $category['name'];

                if (!empty($category['sub_categories'])) {
                    foreach ($category['sub_categories'] as $sub_category) {
                        $sub_categories[$category['id']][$sub_category['id']] = $sub_category['name'];
                    }
                }
            }

            $brands = Brands::forDropdown($business_id);

            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
            $taxes = $tax_dropdown['tax_rates'];
            $tax_attributes = $tax_dropdown['attributes'];

            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');
            $business_locations = BusinessLocation::forDropdown($business_id);
            $default_profit_percent = request()->session()->get('business.default_profit_percent');

            return view('product.bulk-edit')->with(compact(
                'products',
                'categories',
                'brands',
                'taxes',
                'tax_attributes',
                'sub_categories',
                'price_groups',
                'business_locations',
                'selected_products_string',
                'default_profit_percent'
            ));
        }
    }

    /**
     * Updates multiple products at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $products = $request->input('products');
            $business_id = $request->session()->get('user.business_id');

            DB::beginTransaction();
            foreach ($products as $id => $product_data) {
                $update_data = [
                    'category_id' => $product_data['category_id'],
                    'sub_category_id' => $product_data['sub_category_id'],
                    'brand_id' => $product_data['brand_id'],
                    'tax' => $product_data['tax'],
                ];

                //Update product
                $product = Product::where('business_id', $business_id)
                                ->findOrFail($id);

                $product->update($update_data);

                //Add product locations
                $product_locations = !empty($product_data['product_locations']) ?
                                    $product_data['product_locations'] : [];
                $product->product_locations()->sync($product_locations);

                $variations_data = [];

                //Format variations data
                foreach ($product_data['variations'] as $key => $value) {
                    $variation = Variation::where('product_id', $product->id)->findOrFail($key);
                    $variation->default_purchase_price = $this->productUtil->num_uf($value['default_purchase_price']);
                    $variation->dpp_inc_tax = $this->productUtil->num_uf($value['dpp_inc_tax']);
                    $variation->profit_percent = $this->productUtil->num_uf($value['profit_percent']);
                    $variation->default_sell_price = $this->productUtil->num_uf($value['default_sell_price']);
                    $variation->sell_price_inc_tax = $this->productUtil->num_uf($value['sell_price_inc_tax']);
                    $variations_data[] = $variation;

                    //Update price groups
                    if (!empty($value['group_prices'])) {
                        foreach ($value['group_prices'] as $k => $v) {
                            VariationGroupPrice::updateOrCreate(
                                ['price_group_id' => $k, 'variation_id' => $variation->id],
                                ['price_inc_tax' => $this->productUtil->num_uf($v)]
                            );
                        }
                    }
                }
                $product->variations()->saveMany($variations_data);
            }
            DB::commit();

            $output = ['success' => 1,
                            'msg' => __("lang_v1.updated_success")
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect('products')->with('status', $output);
    }

    /**
     * Adds product row to edit in bulk edit product form
     *
     * @param  int  $product_id
     * @return \Illuminate\Http\Response
     */
    public function getProductToEdit($product_id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $product = Product::where('business_id', $business_id)
                            ->with(['variations', 'variations.product_variation', 'variations.group_prices'])
                            ->findOrFail($product_id);
        $all_categories = Category::catAndSubCategories($business_id);

        $categories = [];
        $sub_categories = [];
        foreach ($all_categories as $category) {
            $categories[$category['id']] = $category['name'];

            if (!empty($category['sub_categories'])) {
                foreach ($category['sub_categories'] as $sub_category) {
                    $sub_categories[$category['id']][$sub_category['id']] = $sub_category['name'];
                }
            }
        }

        $brands = Brands::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

        return view('product.partials.bulk_edit_product_row')->with(compact(
            'product',
            'categories',
            'brands',
            'taxes',
            'tax_attributes',
            'sub_categories',
            'price_groups'
        ));
    }

    /**
     * Gets the sub units for the given unit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $unit_id
     * @return \Illuminate\Http\Response
     */
    public function getSubUnits(Request $request)
    {
        if (!empty($request->input('unit_id'))) {
            $unit_id = $request->input('unit_id');
            $business_id = $request->session()->get('user.business_id');
            $sub_units = $this->productUtil->getSubUnits($business_id, $unit_id, true);

            //$html = '<option value="">' . __('lang_v1.all') . '</option>';
            $html = '';
            if (!empty($sub_units)) {
                foreach ($sub_units as $id => $sub_unit) {
                    $html .= '<option value="' . $id .'">' .$sub_unit['name'] . '</option>';
                }
            }

            return $html;
        }
    }

    public function updateProductLocation(Request $request)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $selected_products = $request->input('products');
            $update_type = $request->input('update_type');
            $location_ids = $request->input('product_location');

            $business_id = $request->session()->get('user.business_id');

            $product_ids = explode(',', $selected_products);

            $products = Product::where('business_id', $business_id)
                                ->whereIn('id', $product_ids)
                                ->with(['product_locations'])
                                ->get();
            DB::beginTransaction();
            foreach ($products as $product) {
                $product_locations = $product->product_locations->pluck('id')->toArray();

                if ($update_type == 'add') {
                    $product_locations = array_unique(array_merge($location_ids, $product_locations));
                    $product->product_locations()->sync($product_locations);
                } elseif ($update_type == 'remove') {
                    foreach ($product_locations as $key => $value) {
                        if (in_array($value, $location_ids)) {
                            unset($product_locations[$key]);
                        }
                    }
                    $product->product_locations()->sync($product_locations);
                }
            }
            DB::commit();
            $output = ['success' => 1,
                            'msg' => __("lang_v1.updated_success")
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    public function productStockHistory($id)
    {
        if (!auth()->user()->can('product.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type', null);

        if (request()->ajax()) {

            $stock_details = $this->productUtil->getVariationStockDetails($business_id, $id, request()->input('location_id'));
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $id, request()->input('location_id'),request()->get('type', null));
            // return $stock_history;

            return view('product.stock_history_details')
                ->with(compact('stock_details', 'stock_history','type'));
        }

        $product = Product::where('business_id', $business_id)
                            ->with(['variations', 'variations.product_variation'])
                            ->findOrFail($id);

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);


        return view('product.stock_history')
                ->with(compact('product', 'business_locations','type'));
    }

    // new product item create  20-05-2021
    public function quickAdd()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $action = request()->input('action');
        //Check if subscribed or not, then check for products quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('products', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action('ProductController@index'));
        }

        $categories = Category::forDropdown($business_id, 'product');

        $brands = Brands::forDropdown($business_id);
        $units = Unit::forDropdown($business_id, true);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;
        $barcode_default =  $this->productUtil->barcode_default();

        $default_profit_percent = request()->session()->get('business.default_profit_percent');;

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        //Duplicate product
        $duplicate_product = null;
        $rack_details = null;

        $sub_categories = [];
        if (!empty(request()->input('d'))) {
            $duplicate_product = Product::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_product->name .= ' (copy)';

            if (!empty($duplicate_product->category_id)) {
                $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $duplicate_product->category_id)
                        ->pluck('name', 'id')
                        ->toArray();
            }

            //Rack details
            if (!empty($duplicate_product->id)) {
                $rack_details = $this->productUtil->getRackDetails($business_id, $duplicate_product->id);
            }
        }

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        $contacts = Contact::where('business_id',$business_id)->where('type','supplier')->get();
        // return $contacts;

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');

        return view('product.partials.quick_add_product')
            ->with(compact('action', 'categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'barcode_default', 'business_locations', 'duplicate_product', 'sub_categories', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data','contacts'));
    }


    public function removeMainItemImage(Request $request){
        $product = Product::find($request->product_id);
        $product->main_image = '';
        $product->update();
        return response()->json('success');
    }

    public function checkbarcode(Request $request){


         $barcode = $request->barcode;
         $product = Product::where('sku',$barcode)->orWhere('sku2',$barcode)->orWhere('sku3',$barcode)->get();


         $li = '';
        foreach($product as $pro){
            $li .= $pro->name.',';

        }
         $li = rtrim($li, ',');

            if(!empty($li)){
                $data = [
                    'success' => true,
                    'message'=> 'Barcode Already assigned to '.$li
                ];
            }
            else{
                $data = [
                    'success' =>true,
                    'message'=> ''
                ];
            }
         return response()->json($data);
    }

    public function checkbarcodeedit(Request $request){


        $barcode = $request->barcode;
        $product_id = $request->product_id;
        $query = Product::where('id','!=',$product_id);

        $query->where(function($query) use ($barcode)
                {
                    $query->where('sku',$barcode)->orWhere('sku2',$barcode)->orWhere('sku3',$barcode);
                });

        $product = $query->get();

        $li = '';
         foreach($product as $pro){
             $li .= $pro->name.',';

         }
         $li = rtrim($li, ',');

        if(!empty($li)){
            $data = [
                'success' => true,
                'message'=> 'Barcode Already assigned to '.$li
              ];
         }
         else{
            $data = [
                'success' =>true,
                'message'=> ''
            ];
         }
         return response()->json($data);

    }
    public function checkitemcode(Request $request){
        $item_code = $request->item_code;
        $product = Product::where('item_code',$item_code)->get();

        $li = '';
       foreach($product as $pro){
           $li .= $pro->name.',';

       }
        $li = rtrim($li, ',');

           if(!empty($li)  && $item_code != ''){
               $data = [
                   'success' => true,
                   'message'=> 'Item Code Already assigned to '.$li
               ];
           }
           else{
               $data = [
                   'success' =>false,
                   'message'=> ''
               ];
           }
        return response()->json($data);
    }

    public function checkitemcodeedit(Request $request){
        $item_code = $request->item_code;
        $product_id = $request->product_id;
        $product = Product::where('id','!=',$product_id)->where('item_code',$item_code)->get();
        $li = '';
         foreach($product as $pro){
             $li .= $pro->name.',';

         }
         $li = rtrim($li, ',');

        if(!empty($li) && $item_code != ''){
            $data = [
                'success' => true,
                'message'=> 'Item Code Already assigned to '.$li
              ];
         }
         else{
            $data = [
                'success' =>false,
                'message'=> ''
            ];
         }
         return response()->json($data);
    }

    public function editbulk_old(Request $request){

        $cost_price = 0;
        $sell_price = 0;
        $gross_profit = 0;
        $woocommerce_disable_sync = $request->woocommerce_disable_sync;
        $not_for_selling = $request->not_for_selling;
        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;
        $outofstock = $request->outofstock;
        $product_id = $request->product_id;
        $brand_id = $request->brand_id;
        $forselling = $request->forselling;
        $sync_with_woocommerce = $request->sync_with_woocommerce;
        $instock = $request->instock;
        $case_qty = $request->case_qty;

          $pro_id = explode(',',$product_id);

        $product = Product::find($pro_id);

        if(count($product) > 0){

            foreach($product as $proid){
                $product1 = Product::findOrFail($proid->id);
                if($category_id){
                    $product1->category_id = $category_id;
                    if($sub_category_id){
                        $product1->sub_category_id = $sub_category_id;
                    }
                    else
                    {
                        $product1->sub_category_id = NULL;
                    }
                }
                if($brand_id){
                    $product1->brand_id = $brand_id;
                }
                if(empty($woocommerce_disable_sync) && $sync_with_woocommerce){
                    $product1->woocommerce_disable_sync = $sync_with_woocommerce;
                }else{
                    $product1->woocommerce_disable_sync = $woocommerce_disable_sync;
                }
                if(empty($not_for_selling) && $forselling){
                   $product1->not_for_selling = $forselling;
                }else{
                    $product1->not_for_selling = $not_for_selling;
                }
                if($outofstock){
                    $product1->out_of_stock = $outofstock;
                }
                if(empty($outofstock) && $instock){
                    $product1->out_of_stock = $instock;
                }else{
                    $product1->out_of_stock = $outofstock;
                }
                if($case_qty){
                    $product1->case_qty = $case_qty;
                }
                $product1->save();

                $variation_id = Variation::where('product_id', $proid->id)->first();
                $id = $variation_id->id;
                $variation = Variation::findOrFail($id);

                if(isset($request->gross_profit) && $request->gross_profit > 0){
                    $gross_profit = $request->gross_profit;
                    if($cost_price){
                        $sell_price = $cost_price/((100-$gross_profit)/100);

                    }else{
                        $sell_price = $variation->default_purchase_price/((100-$gross_profit)/100);
                    }
                }

                if(isset($request->sell_price) && $request->sell_price > 0){
                    $sell_price = $request->sell_price;
                    if($variation->default_purchase_price > $sell_price){

                        $output = [
                            'success' =>false,
                            'message'=> 'Sell Price should be greater than cost price'
                        ];
                        return $output;

                    }else{
                        $gross_profit = (1 - ($variation->default_purchase_price / $sell_price))*100;
                    }
                }

                if(isset($request->cost_price) && $request->cost_price > 0){
                    $cost_price = $request->cost_price;
                    if($cost_price > $variation->default_sell_price){

                        $output = ['success' => 0,
                            'msg' => __("Cost price should be less than sell price")
                        ];
                        return $output;
                    }else{
                        $gross_profit =(1 - ($cost_price / $variation->default_sell_price))*100;
                    }
                }

                if($cost_price > 0){
                    $variation->default_purchase_price = $cost_price;
                    $variation->dpp_inc_tax = $cost_price;
                }
                if($gross_profit > 0){
                    $variation->profit_percent = $gross_profit;
                }
                if($sell_price > 0){
                    $variation->default_sell_price = $sell_price;
                    $variation->sell_price_inc_tax = $sell_price;
                }
                $variation->save();
            }
        }


        $output = ['success' => 1,
            'msg' => __('Updated Successfully')
        ];
        return $output;
    }

    public function editbulk(Request $request){

        // true = on, false = off
        $COST_SELL_CHECK = (auth()->user()->id != 6);

        $business_id = request()->session()->get('user.business_id');
        $cost_price = 0;
        $sell_price = 0;
        $gross_profit = 0;
        $tier_price_bulk = 0;
        $tier1_price_bulk = 0;
        $tier2_price_bulk = 0;
        $tier3_price_bulk = 0;
        $tier4_price_bulk = 0;
        $md_price_bulk = 0;
        $woocommerce_disable_sync = $request->woocommerce_disable_sync;
        $not_for_selling = $request->not_for_selling;
        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;
        $outofstock = $request->outofstock;
        $product_id = $request->product_id;
        $brand_id = $request->brand_id;
        $forselling = $request->forselling;
        $sync_with_woocommerce = $request->sync_with_woocommerce;
        $instock = $request->instock;
        $case_qty = $request->case_qty;
        $ml = $request->ml;
          $srp = $request->srp;
        $sales_price = $request->sales_price;

          $pro_id = explode(',',$product_id);

        $product = Product::find($pro_id);

        if(count($product) > 0){

            foreach($product as $proid){

                $pro = "";

                $product1 = Product::findOrFail($proid->id);
                if($category_id){
                    $product1->category_id = $category_id;
                    $pro .= "Category,";
                    if($sub_category_id){
                        $pro .= "Sub Category,";
                        $product1->sub_category_id = $sub_category_id;
                    }
                    else
                    {
                        $product1->sub_category_id = NULL;
                    }
                }
                if($brand_id){
                    $pro .= "Category,";
                    $product1->brand_id = $brand_id;
                }
                 if($ml){
                    $pro .= "ml,";
                    $product1->ml = $ml;
                }

                if(!isset($woocommerce_disable_sync) && isset($sync_with_woocommerce)){
                    $pro .= "set Woocommerce Enabled,";
                    $product1->woocommerce_disable_sync = $sync_with_woocommerce;
                }
                if(!isset($sync_with_woocommerce) && isset($woocommerce_disable_sync)){
                    $pro .= "set Woocommerce Disabled,";
                    $product1->woocommerce_disable_sync = $woocommerce_disable_sync;
                }

                // if(empty($woocommerce_disable_sync) && $sync_with_woocommerce){
                //     $product1->woocommerce_disable_sync = $sync_with_woocommerce;
                // }else{
                //     $product1->woocommerce_disable_sync = $woocommerce_disable_sync;
                // }

                // if(empty($not_for_selling) && $forselling){
                //     $pro .= "set Available For Selling,";
                //     $product1->not_for_selling = $forselling;
                // }
                // if(empty($forselling) && $not_for_selling){
                //     $pro .= "set Not For Selling,";
                //     $product1->not_for_selling = $not_for_selling;
                // }

                if(!isset($not_for_selling) && isset($forselling)){
                    $pro .= "set Available For Selling,";
                    $product1->not_for_selling = $forselling;
                }
                if(!isset($forselling) && isset($not_for_selling)){
                    $pro .= "set Not For Selling,";
                    $product1->not_for_selling = $not_for_selling;
                }


                // if(empty($not_for_selling) && $forselling){
                //    $product1->not_for_selling = $forselling;
                // }else{
                //     $product1->not_for_selling = $not_for_selling;
                // }

                if(!isset($outofstock) && isset($instock)){
                    $pro .= "set Instock,";
                    $product1->out_of_stock = $instock;
                }
                if(!isset($instock) && isset($outofstock)){
                    $pro .= "set Out of Stock,";
                    $product1->out_of_stock = $outofstock;
                }

                // if($outofstock){
                //     $product1->out_of_stock = $outofstock;
                // }
                // if(empty($outofstock) && $instock){
                //     $product1->out_of_stock = $instock;
                // }else{
                //     $product1->out_of_stock = $outofstock;
                // }

                if($case_qty){
                    $pro .= "Case QTY,";
                    $product1->case_qty = $case_qty;
                }
                 if($srp){
                    $pro .= 'Reg. Price ('. $product1->srp .'  --> '. $request->input('srp') .'), ';
                    $product1->srp = $srp;
                }
                if($sales_price){
                    $pro .= 'Sale price ('. $product1->sales_price .'  --> '. $request->input('sales_price') .'), ';
                    $product1->sales_price = $sales_price;
                }

                $user_id = auth()->user()->id;
                $this->productUtil->ProductActivitiesLog('edited',$user_id,$product1->id,$request);
                $product1->save();

                $variation_id = Variation::where('product_id', $proid->id)->first();
                $id = $variation_id->id;
                $variation = Variation::findOrFail($id);

                if(isset($request->gross_profit) && $request->gross_profit > 0){
                    $pro .= "GP,";
                    $gross_profit = $request->gross_profit;
                    if($cost_price){
                        $sell_price = $cost_price/((100-$gross_profit)/100);

                    }else{
                        $sell_price = $variation->default_purchase_price/((100-$gross_profit)/100);
                    }
                }

                if(isset($request->sell_price) && $request->sell_price > 0){
                    $sell_price = $request->sell_price;
                    if($variation->default_purchase_price > $sell_price && $COST_SELL_CHECK){

                        $output = [
                            'success' =>false,
                            'message'=> 'Sell Price should be greater than cost price'
                        ];
                        return $output;

                    }else{
                        $pro .= "Selling Price,";
                        $gross_profit = (1 - ($variation->default_purchase_price / $sell_price))*100;
                    }
                }

                if(isset($request->md_price_bulk) && $request->md_price_bulk > 0){
                    $md_price_bulk = $request->md_price_bulk;
                    // $price_groups_detail = SellingPriceGroup::where('business_id', $business_id)->where('id','79')->active()->first();
                    $price_groups_detail = SellingPriceGroup::find(79);
                    if(!empty($price_groups_detail)){
                        $variationGroupPriceDetailForProduct = VariationGroupPrice::where('variation_id', $variation_id->id)->where('price_group_id', $price_groups_detail->id)->first();
                    }

                    if($variation->default_purchase_price > $md_price_bulk){

                        $output = [
                            'success' =>0,
                            'msg'=> $price_groups_detail->name.' Price should be greater than cost price'
                        ];
                        return $output;

                    }else{

                        if(!empty($variationGroupPriceDetailForProduct))
                        {
                            $pro .= $price_groups_detail->name." Price updated from $".$this->productUtil->num_uf($variationGroupPriceDetailForProduct->price_inc_tax)." to $".$this->productUtil->num_uf($md_price_bulk)." for ".$proid->name.",";
                        }
                        else
                        {
                            $pro .= "MD Group Price,";
                        }

                        $gross_profit = (1 - ($variation->default_purchase_price / $md_price_bulk))*100;
                    }
                }

                if(isset($request->tier_price_bulk) && $request->tier_price_bulk > 0){
                    $tier_price_bulk = $request->tier_price_bulk;
                    $price_groups_detail = SellingPriceGroup::where('business_id', $business_id)->where('id','71')->active()->first();

                    $variationGroupPriceDetailForProduct = VariationGroupPrice::where('variation_id', $variation_id->id)->where('price_group_id', $price_groups_detail->id)->first();

                    if($variation->default_purchase_price > $tier_price_bulk){

                        $output = [
                            'success' =>0,
                            'msg'=> $price_groups_detail->name.' Price should be greater than cost price'
                        ];
                        return $output;

                    }else{

                        if(!empty($variationGroupPriceDetailForProduct))
                        {
                            $pro .= $price_groups_detail->name." Price updated from $".$this->productUtil->num_uf($variationGroupPriceDetailForProduct->price_inc_tax)." to $".$this->productUtil->num_uf($tier_price_bulk)." for ".$proid->name.",";
                        }
                        else
                        {
                            $pro .= $price_groups_detail->name." Price,";
                        }

                        $gross_profit = (1 - ($variation->default_purchase_price / $tier_price_bulk))*100;
                    }
                }

                if(isset($request->tier1_price_bulk) && $request->tier1_price_bulk > 0){
                    $tier1_price_bulk = $request->tier1_price_bulk;
                    $price_groups_detail = SellingPriceGroup::where('business_id', $business_id)->where('id','68')->active()->first();

                    $variationGroupPriceDetailForProduct = VariationGroupPrice::where('variation_id', $variation_id->id)->where('price_group_id', $price_groups_detail->id)->first();

                    if($variation->default_purchase_price > $tier1_price_bulk && $COST_SELL_CHECK){

                        $output = [
                            'success' =>0,
                            'msg'=> $price_groups_detail->name.' Price should be greater than cost price'
                        ];
                        return $output;

                    }else{

                        if(!empty($variationGroupPriceDetailForProduct))
                        {
                            $pro .= $price_groups_detail->name." Price updated from $".$this->productUtil->num_uf($variationGroupPriceDetailForProduct->price_inc_tax)." to $".$this->productUtil->num_uf($tier1_price_bulk)." for ".$proid->name.",";
                        }
                        else
                        {
                            $pro .= $price_groups_detail->name." Price,";
                        }

                        $gross_profit = (1 - ($variation->default_purchase_price / $tier1_price_bulk))*100;
                    }
                }

                if(isset($request->tier2_price_bulk) && $request->tier2_price_bulk > 0){
                    $tier2_price_bulk = $request->tier2_price_bulk;
                    $price_groups_detail = SellingPriceGroup::where('business_id', $business_id)->where('id','69')->active()->first();

                    $variationGroupPriceDetailForProduct = VariationGroupPrice::where('variation_id', $variation_id->id)->where('price_group_id', $price_groups_detail->id)->first();

                    if($variation->default_purchase_price > $tier2_price_bulk && $COST_SELL_CHECK){

                        $output = [
                            'success' =>0,
                            'msg'=> $price_groups_detail->name.' Price should be greater than cost price'
                        ];
                        return $output;

                    }else{

                        if(!empty($variationGroupPriceDetailForProduct))
                        {
                            $pro .= $price_groups_detail->name." Price updated from $".$this->productUtil->num_uf($variationGroupPriceDetailForProduct->price_inc_tax)." to $".$this->productUtil->num_uf($tier2_price_bulk)." for ".$proid->name.",";
                        }
                        else
                        {
                            $pro .= $price_groups_detail->name." Price,";
                        }

                        $gross_profit = (1 - ($variation->default_purchase_price / $tier2_price_bulk))*100;
                    }
                }

                if(isset($request->tier3_price_bulk) && $request->tier3_price_bulk > 0){
                    $tier3_price_bulk = $request->tier3_price_bulk;
                    $price_groups_detail = SellingPriceGroup::where('business_id', $business_id)->where('id','70')->active()->first();

                    $variationGroupPriceDetailForProduct = VariationGroupPrice::where('variation_id', $variation_id->id)->where('price_group_id', $price_groups_detail->id)->first();

                    if($variation->default_purchase_price > $tier3_price_bulk && $COST_SELL_CHECK){

                        $output = [
                            'success' =>0,
                            'msg'=> $price_groups_detail->name.' Price should be greater than cost price'
                        ];
                        return $output;

                    }else{

                        if(!empty($variationGroupPriceDetailForProduct))
                        {
                            $pro .= $price_groups_detail->name." Price updated from $".$this->productUtil->num_uf($variationGroupPriceDetailForProduct->price_inc_tax)." to $".$this->productUtil->num_uf($tier3_price_bulk)." for ".$proid->name.",";
                        }
                        else
                        {
                            $pro .= $price_groups_detail->name." Price,";
                        }

                        $gross_profit = (1 - ($variation->default_purchase_price / $tier3_price_bulk))*100;
                    }
                }

                if(isset($request->tier4_price_bulk) && $request->tier4_price_bulk > 0){
                    $tier4_price_bulk = $request->tier4_price_bulk;
                    $price_groups_detail = SellingPriceGroup::where('business_id', $business_id)->where('id','80')->active()->first();

                    $variationGroupPriceDetailForProduct = VariationGroupPrice::where('variation_id', $variation_id->id)->where('price_group_id', $price_groups_detail->id)->first();

                    if($variation->default_purchase_price > $tier4_price_bulk && $COST_SELL_CHECK){

                        $output = [
                            'success' =>0,
                            'msg'=> $price_groups_detail->name.' Price should be greater than cost price'
                        ];
                        return $output;

                    }else{

                        if(!empty($variationGroupPriceDetailForProduct))
                        {
                            $pro .= $price_groups_detail->name." Price updated from $".$this->productUtil->num_uf($variationGroupPriceDetailForProduct->price_inc_tax)." to $".$this->productUtil->num_uf($tier4_price_bulk)." for ".$proid->name.",";
                        }
                        else
                        {
                            $pro .= $price_groups_detail->name." Price,";
                        }

                        $gross_profit = (1 - ($variation->default_purchase_price / $tier4_price_bulk))*100;
                    }
                }

                if(isset($request->cost_price) && $request->cost_price > 0){
                    $cost_price = $request->cost_price;
                    if($cost_price > $variation->default_sell_price){

                        $output = ['success' => 0,
                            'msg' => __("Cost price should be less than sell price")
                        ];
                        return $output;
                    }else{
                        $pro .= "Cost Price,";
                        $gross_profit =(1 - ($cost_price / $variation->default_sell_price))*100;
                    }
                }

                if($cost_price > 0){
                    $variation->default_purchase_price = $cost_price;
                    $variation->dpp_inc_tax = $cost_price;
                }
                if($gross_profit > 0){
                    $variation->profit_percent = $gross_profit;
                }
                if($sell_price > 0){
                    $variation->default_sell_price = $sell_price;
                    $variation->sell_price_inc_tax = $sell_price;
                }

                if(!empty($request->sell_reset)){
                    $pro .= "Sell Price Reset,";
                    $variation->sell_updated_at = date('Y-m-d H:i:s');
                }
                if(!empty($request->t1_reset)){
                    $pro .= "Tier 1 Price Reset,";
                    $variation->t1_updated_at = date('Y-m-d H:i:s');
                }
                if(!empty($request->t2_reset)){
                    $pro .= "Tier 2 Price Reset,";
                    $variation->t2_updated_at = date('Y-m-d H:i:s');
                }
                if(!empty($request->t3_reset)){
                    $pro .= "Tier 3 Price Reset,";
                    $variation->t3_updated_at = date('Y-m-d H:i:s');
                }

                $variation->save();

                if($md_price_bulk > 0){

                    $MDBulkUpdateSellingPrices = $this->BulkUpdateSellingPrices($proid->id,$md_price_bulk,'79');

                    if($MDBulkUpdateSellingPrices == "false")
                    {
                        $output = ['success' => 0,
                                'msg' => __("messages.something_went_wrong")
                            ];
                        return $output;
                    }
                }

                if($tier_price_bulk > 0){

                    $TierBulkUpdateSellingPrices = $this->BulkUpdateSellingPrices($proid->id,$tier_price_bulk,'71');

                    if($TierBulkUpdateSellingPrices == "false")
                    {
                        $output = ['success' => 0,
                                'msg' => __("messages.something_went_wrong")
                            ];
                        return $output;
                    }
                }

                if($tier1_price_bulk > 0){

                    if(!empty($request->t1_reset)){
                        $pro .= "Tier 1 Price Reset,";
                    }

                    $Tier1BulkUpdateSellingPrices = $this->BulkUpdateSellingPrices($proid->id,$tier1_price_bulk,'68',!empty($request->t1_reset));

                    if($Tier1BulkUpdateSellingPrices == "false")
                    {
                        $output = ['success' => 0,
                                'msg' => __("messages.something_went_wrong")
                            ];
                        return $output;
                    }
                }

                if($tier2_price_bulk > 0){

                    if(!empty($request->t2_reset)){
                        $pro .= "Tier 2 Price Reset,";
                    }

                    $Tier2BulkUpdateSellingPrices = $this->BulkUpdateSellingPrices($proid->id,$tier2_price_bulk,'69',!empty($request->t2_reset));

                    if($Tier2BulkUpdateSellingPrices == "false")
                    {
                        $output = ['success' => 0,
                                'msg' => __("messages.something_went_wrong")
                            ];
                        return $output;
                    }
                }

                if($tier3_price_bulk > 0){

                    if(!empty($request->t3_reset)){
                        $pro .= "Tier 3 Price Reset,";
                    }

                    $Tier3BulkUpdateSellingPrices = $this->BulkUpdateSellingPrices($proid->id,$tier3_price_bulk,'70',!empty($request->t3_reset));

                    if($Tier3BulkUpdateSellingPrices == "false")
                    {
                        $output = ['success' => 0,
                                'msg' => __("messages.something_went_wrong")
                            ];
                        return $output;
                    }
                }

                 if($tier4_price_bulk > 0){

                    if(!empty($request->t4_reset)){
                        $pro .= "Tier LI Price Reset,";
                    }

                    $Tier4BulkUpdateSellingPrices = $this->BulkUpdateSellingPrices($proid->id,$tier4_price_bulk,'80',!empty($request->t4_reset));

                    if($Tier4BulkUpdateSellingPrices == "false")
                    {
                        $output = ['success' => 0,
                                'msg' => __("messages.something_went_wrong")
                            ];
                        return $output;
                    }
                }

                if($pro!="")
                {
                    $user_id = auth()->user()->id;
                    $this->productUtil->ProductActivityLog('edited',$user_id,$product1->id,$pro);

                }
            }
        }


        $output = ['success' => 1,
            'msg' => __('Updated Successfully')
        ];
        return $output;
    }

    // bulk price group
    public function BulkUpdateSellingPrices($product_id,$group_price_bulk=0,$price_group_bulk_id=0,$reset_prices = false)
    {
        try {

            $business_id = request()->session()->get('user.business_id');

            $product = Product::where('business_id', $business_id)
                            ->with(['variations'])
                            ->findOrFail($product_id);

            if($price_group_bulk_id!=0 && !empty($price_group_bulk_id))
            {
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->where('id',$price_group_bulk_id)->active()->get();
            }
            else
            {
                $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->get();
            }

            if(!empty($product) && count($price_groups)>0)
            {
                DB::beginTransaction();
                foreach ($product->variations as $variation) {
                    $variation_group_prices = [];
                    foreach ($price_groups as $price_group) {
                            $variation_group_price =
                            VariationGroupPrice::where('variation_id', $variation->id)
                                                ->where('price_group_id', $price_group->id)
                                                ->first();
                            if (empty($variation_group_price)) {
                                $variation_group_price = new VariationGroupPrice([
                                        'variation_id' => $variation->id,
                                        'price_group_id' => $price_group->id
                                    ]);
                            }

                            if($reset_prices && ($variation_group_price->price_inc_tax != $this->productUtil->num_uf($group_price_bulk))){
                                $reset_col_name = '';
                                if($price_group_bulk_id == 68){
                                    $reset_col_name = 't1_updated_at';
                                }
                                elseif($price_group_bulk_id == 69){
                                    $reset_col_name = 't2_updated_at';
                                }
                                elseif($price_group_bulk_id == 70){
                                    $reset_col_name = 't3_updated_at';
                                }
                                elseif($price_group_bulk_id == 80){
                                    $reset_col_name = 't4_updated_at';
                                }
                                if(!empty($reset_col_name)){
                                    Variation::where('id',$variation->id)->update([$reset_col_name => date('Y-m-d H:i:s')]);
                                }
                            }

                            $variation_group_price->price_inc_tax = $this->productUtil->num_uf($group_price_bulk);
                            $variation_group_prices[] = $variation_group_price;
                    }

                    if (!empty($variation_group_prices)) {
                        $variation->group_prices()->saveMany($variation_group_prices);
                    }
                }
                //Update product updated_at timestamp
                $product->touch();

                DB::commit();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return "false";
        }
    }

    public function autogeneratebarcode(Request $request){

        // if(isset($request->old_barcode)) {
        //     $last_barcode = $request->old_barcode;
        // } else {
        //     $product =  BarcodeSetting::latest()->limit(1)->first();
        //     if(empty($product->last_barcode)){
        //         $last_barcode = random_int(100000000000, 999999999999);
        //     }else{
        //         $last_barcode = $product->last_barcode;
        //     }
        // }

        $old_barcode = $request->old_barcode;
        if(empty($last_barcode))
        {
             $product =  BarcodeSetting::first();
             if(empty($product)){
                $last_barcode = random_int(100000000000, 999999999999);
             }else{
                $last_barcode = $product->last_barcode;
             }
        }
        // else{
        //     $last_barcode = $request->old_barcode;
        // }
        // return $last_barcode;

        $substr =  substr($last_barcode, -6);
        $substr = $substr + 1;
        $c = 6;
        $len = strlen($substr);
        // $random = random_int(100000, 999999);
        $random = 100000;
        if($len < $c){

            if($len == 5){
                $barcode =  $random.'0'.$substr;
            }
            if($len == 4){
                $barcode = $random.'00'.$substr;
            }
            if($len == 3){
                $barcode = $random.'000'.$substr;
            }
            if($len == 2){
                $barcode = $random.'0000'.$substr;
            }
            if($len == 1){
                $barcode = $random.'00000'.$substr;
            }
        }else{
            $barcode = $random.$substr;
        }

        // return $barcode;

        // $barcodesetting = BarcodeSetting::first();
        // if(empty($barcodesetting->last_barcode)){
        //     $barcode_last =  $last_barcode;
        // }
        // else{
        //     $barcode_last = $barcodesetting->last_barcode;
        // }

        //  $exist_substr =  substr($barcode_last, -6);

        // if(($exist_substr +1) == $substr){

        //     $barcode = $barcode;

        // }

       return response()->json($barcode);


    }

   // public function updateProductStockHistory(){
        // $business_id = request()->session()->get('user.business_id');
     //   $business_id = 4;
       // $products = Product::join('variations', 'variations.product_id', '=', 'products.id')->select('products.id as p_id', 'variations.id as variation_id')->whereIn('products.category_id',[367,368,381])->whereNotIn('is_qty_updated',[5,6])->where('business_id',$business_id)->skip(0)->take(3)->get();
      //  foreach($products as $product){
        //    echo $product->p_id;
         //   echo "==";
          //  $stock_history = $this->productUtil->getVariationStockHistory($business_id, $product->variation_id, 4,request()->get('type', null));
        //    if(count($stock_history)){
         //       echo $stock_history[0]['stock'];
          //      VariationLocationDetails::where('product_id',$product->p_id)->update(['qty_available'=>$stock_history[0]['stock']]);
        //        Product::where('id',$product->p_id)->update(['is_qty_updated'=>5]);
         //   }else{
          //      Product::where('id',$product->p_id)->update(['is_qty_updated'=>6]);
        //    }
    //    echo "<br/>";
      //  }
//    }
    public function updateProductStockHistory($id){
        // dd(1);
        //         die();
        // if(count($products) > 1){
        //     return redirect()->back()->with('success' => 0,
        //         'msg' => __('More than 1 Variation Found')
        //     );
        // }

        // $business_id = request()->session()->get('user.business_id');
        $business_id = 4;
        $products = Product::join('variations', 'variations.product_id', '=', 'products.id')->select('products.id as p_id', 'variations.id as variation_id')->where('products.id',$id)->where('business_id',$business_id)->skip(0)->take(2)->get();

        if(count($products) > 1){
            return redirect()->back()->with(['success' => 0,
                'msg' => __('More than 1 Variation Found')
            ]);
        }

        foreach($products as $product){
            // echo $product->p_id;
            // echo "==";
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $product->variation_id, 4,request()->get('type', null));
            if(count($stock_history)){
                // echo $stock_history[0]['stock'];
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
                // $total_sold = 0;
                // $adjustment_stock = 0;
                // foreach($stock_history as $history){
                //     if($history->type == 'sell') $total_sold += $history->quantity_change;
                //     elseif($history->type == 'stock_adjustment') $adjustment_stock += $history->quantity_change;
                // }
            }
            else{
                 Product::where('id',$product->p_id)->update(['is_qty_updated'=>2]);
            }
            // echo "<br/>";
            return redirect()->route('product.stockhistory',$id)->with(['success' => 1,
                'msg' => __('Stock History Updated Successfully')
            ]);
            // $id=$id+1;
        }
    }
    public function updateProductStockHistorycat($id){
        // $business_id = request()->session()->get('user.business_id');
        $business_id = 4;
        $products = Product::join('variations', 'variations.product_id', '=', 'products.id')->select('products.id as p_id', 'variations.id as variation_id')->where('products.category_id',$id)->where('business_id',$business_id)->skip(0)->take(10)->get();
        foreach($products as $product){
            echo $product->p_id;
            echo "==";
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $product->variation_id, 4,request()->get('type', null));
            if(count($stock_history)){
                echo $stock_history[0]['stock'];
                VariationLocationDetails::where('product_id',$product->p_id)->update(['qty_available'=>$stock_history[0]['stock']]);
                Product::where('id',$product->p_id)->update(['is_qty_updated'=>1]);
                // $total_sold = 0;
                // $adjustment_stock = 0;
                // foreach($stock_history as $history){
                //     if($history->type == 'sell') $total_sold += $history->quantity_change;
                //     elseif($history->type == 'stock_adjustment') $adjustment_stock += $history->quantity_change;
                // }
            }else{
                 Product::where('id',$product->p_id)->update(['is_qty_updated'=>2]);
            }
        echo "<br/>";
        }
    }
    public function updateProductStockHistorycatskip($id,$skip=0){
        // $business_id = request()->session()->get('user.business_id');
        $business_id = 4;
        $products = Product::join('variations', 'variations.product_id', '=', 'products.id')->select('products.id as p_id', 'variations.id as variation_id')->where('products.category_id',$id)->where('business_id',$business_id)->skip($skip)->take(10)->get();
        foreach($products as $product){
            echo $product->p_id;
            echo "==";
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $product->variation_id, 4,request()->get('type', null));
            if(count($stock_history)){
                echo $stock_history[0]['stock'];
                VariationLocationDetails::where('product_id',$product->p_id)->update(['qty_available'=>$stock_history[0]['stock']]);
                Product::where('id',$product->p_id)->update(['is_qty_updated'=>1]);
                // $total_sold = 0;
                // $adjustment_stock = 0;
                // foreach($stock_history as $history){
                //     if($history->type == 'sell') $total_sold += $history->quantity_change;
                //     elseif($history->type == 'stock_adjustment') $adjustment_stock += $history->quantity_change;
                // }
            }else{
                 Product::where('id',$product->p_id)->update(['is_qty_updated'=>2]);
            }
            $skip=$skip+1;

        echo "<br/>";


        }
    }
    public function checkcategory(Request $request){
        $customer_id =  $request->customer_id;
        $varation_id = $request->id;

       $variation =  Variation::join('products','variations.product_id','=','products.id')->where('variations.id',$varation_id)->first();
       $category_id = $variation->category_id;

       $cat = Category::where('id',$category_id)->first();
        if($cat && ($cat->name == 'CIGAR' || $cat->parent_id == $category_id))
        {
            $customer = Contact::where('id',$customer_id)->first();
            if(empty($customer->tobacco_license_no) || empty($customer->tax_number)){
                $data = [
                    'success' => false,
                    'message'=> 'You cannot sell cigars to this customer, tax id or tobaco license is missing'
                ];
            }else{
                $data = [
                    'success' => true,
                    'message'=> ''
                ];
            }
        } else{
            $data = [
                'success' => true,
                'message'=> ''
            ];
        }
        return response()->json($data);
    }

       public function productOnhand()
    {

      \Log::info('Cronjob run at '.date('Y-m-d'));

        $current_date = date('Y-m-d');
        $dt1          = strtotime($current_date);
        $dt2          = date("l", $dt1);
        $day          = strtolower($dt2);

        // if($day == 'saturday')
        // {
            $week =date('W');

            $business_id = request()->session()->get('user.business_id');
            $products    = Product::where('business_id', $business_id)->get();

            $thisYearEntry = OnhandItem::where('year', date('Y'))->first();

            if(empty($thisYearEntry) || is_null($thisYearEntry))
            {
                $data = [];
                foreach($products as $product)
                {
                    $data['product_id'] = $product->id;

                    $on_hand = 0;
                    if(count($product->variations) > 0)
                    {

                        foreach($product->variations as $variation)
                        {
                            if($variation->variation_location_details)
                            {
                                foreach($variation->variation_location_details as $var)
                                {
                                    if($var->location_id == 4)
                                    {
                                        $on_hand = $var->qty_available;
                                    }
                                }
                            }
                        }
                    }
                    $db_week            = 'week_' . $week;
                    $data[$db_week]     = $on_hand;
                    $data['year']       = date('Y');
                    $data['created_at'] = date('Y-m-d h:i');
                    $data['updated_at'] = date('Y-m-d h:i');
                    $response           = OnhandItem::insert($data);


                }
               echo'Crom successfully runed';

            }
            else
            {
                foreach($products as $product)
                {

                    $on_hand = 0;
                    if(count($product->variations) > 0)
                    {
                        foreach($product->variations as $variation)
                        {
                            if($variation->variation_location_details)
                            {
                                foreach($variation->variation_location_details as $var)
                                {
                                    if($var->location_id == 4)
                                    {
                                        $on_hand = $var->qty_available;
                                    }
                                }
                            }
                        }
                    }
                    $db_week = 'week_' . $week;

                    $response = OnhandItem::where('product_id', $product->id)->where('year', date('Y'))->update([$db_week => $on_hand]);
                }
                echo'Cron successfully runed';
            }
        // }
        // else
        // {
        //     echo 'Today is ' . $day . ',Your cron run at Saturday';
        // }

    }

    function weekOfMonth($date)
    {
        $firstOfMonth = strtotime(date("Y-m-01", $date));

        return $this->weekOfYear($date) - $this->weekOfYear($firstOfMonth) + 1;
    }

    function weekOfYear($date)
    {
        $weekOfYear = intval(date("W", $date));
        if(date('n', $date) == "1" && $weekOfYear > 51)
        {
            return 0;
        }
        else if(date('n', $date) == "12" && $weekOfYear == 1)
        {
            return 53;
        }
        else
        {
            return $weekOfYear;
        }
    }

      function updateSellingPrice($selling_price, $product){

      $group_price_details = [];

      foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                // $group_price_details[$variation->id][$group_price->price_group_id] = $selling_price;
                //update product selling prices
                VariationGroupPrice::where('variation_id', $variation->id)->where('price_group_id', $group_price->price_group_id)->whereNotIn('price_group_id',[68,69,70,80])->update(['price_inc_tax'=> $selling_price]);
            }
        }
    }

    // Jadoo Products Form
    public function jadooCreateSave(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $jadoo_products = JadooProduct::jaddoProductsDropdown();

        if($request->ajax())
        {
            $jadoo_product_id = $request->post('jadoo_product_id');
            $a_name = trim($request->post('a_name'));
            $name = trim($request->post('name'));
            $barcode = trim($request->post('barcode'));
            $itemcode = trim($request->post('itemcode'));
            if($name=="")
            {
                $data = array(
                   'result' => 'error',
                   'msg'  => 'Please enter required input!!',
                  );
                  echo json_encode($data);
            }
            else if($a_name=="")
            {
                $data = array(
                   'result' => 'error',
                   'msg'  => 'Please enter required input!!',
                  );
                  echo json_encode($data);
            }
            else
            {
                try
                {
                    DB::beginTransaction();

                        $products =  JadooProduct::find($jadoo_product_id);
                        $products->name = $a_name;
                        $products->jadoo_name = $name;
                        $products->barcode = $barcode;
                        $products->itemcode = $itemcode;
                        $products->save();

                    DB::commit();

                    $data = array(
                       'result' => 'success',
                       'msg'  => 'Data updated successfully',
                      );

                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                    $data = array(
                       'result' => 'error',
                       'msg'  => 'Something went wrong!!',
                      );
                }

                echo json_encode($data);
            }
        }
        else
        {
            return view('product.jadoo-products-form')
            ->with(compact('jadoo_products'));
        }
    }
    public function GetJadooProduct(Request $request)
    {
        $jadoo_products = "";
        if ($request->jadoo_id) {
            $jadoo_products = JadooProduct::where('id', $request->jadoo_id)->first();

            if ($jadoo_products != null) {
                return response()->json($jadoo_products);
            } else {
                return response()->json(0);
            }
        }
    }

    public function getJadooProductsList(Request $request)
    {
            $jadoo_query =  JadooProduct::orderBy('jadoo_products.name','ASC')->get();
            //$jadoo_query = $jadoo_query->toArray();
            $arrayMG =$jadoo_query;
            $itemArr = [];
            $i = 1;
            foreach($arrayMG as $val)
            {
                $jadooArr['loop']              = $i;
                $jadooArr['name']              = $val->name;
                $jadooArr['jadoo_name']        = $val->jadoo_name;
                $jadooArr['barcode']           = $val->barcode;
                $jadooArr['itemcode']          = $val->itemcode;
                if($val->status == 1)
                {
                    $jadooArr['status'] = 'Active';
                }
                else
                {
                    $jadooArr['status'] = 'Inactive';
                }

                $itemArr[] = $jadooArr;
                $i++;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)->make(true);
            return $datatable;
    }
    // Jadoo Products Form

    public function GenerateFinalBarcode()
    {
        $random = 100000;
        $result = Product::where('sku', 'like', "{$random}%")
                            ->select('sku')
                            ->orderBy('sku', 'desc')
                            ->limit(1)
                            ->first();

        if(!empty($result))
        {
            $new_barcode = $result->sku;
            $barcodesetting = BarcodeSetting::first();
            if(!empty($barcodesetting))
            {

                $id = $barcodesetting->id;
                $barcode_setting =  BarcodeSetting::find($id);
                $barcode_setting->last_barcode = $new_barcode;
                $barcode_setting->save();
            }
        }
    }

    public function downloadExcel()
    {
        if (auth()->user()->can('product.export')) {
            $filename = 'products-export-' . \Carbon::now()->format('Y-m-d') . '.xlsx';
            return Excel::download(new ProductsExport, $filename);
        }
        return redirect()->back();
    }

}
