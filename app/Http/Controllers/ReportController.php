<?php

namespace App\Http\Controllers;

use App\Model\Audit;

use App\Brands;
use App\BusinessLocation;
use App\CashRegister;
use App\Category;
use App\Charts\CommonChart;
use App\Contact;
use App\CustomerGroup;
use App\ExpenseCategory;
use App\Product;
use App\PurchaseLine;
use App\Restaurant\ResTable;
use App\SellingPriceGroup;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\TransactionSellLinesPurchaseLines;
use App\Unit;
use App\User;
use App\DocumentAndNote;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\ContactUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\VariationLocationDetails;
use Datatables;
use DB;
use Illuminate\Http\Request;

use App\TaxRate;
use App\OnhandItem;
//added by developer1
use Illuminate\Support\Str;
use App\PurchaseActivityLog;
use App\Delinvoicelog;
use Modules\SmartCRM\Models\FollowUp;
use DateTime;
use Twilio\Http\GuzzleClient;
use GuzzleHttp\Client;
use App\Console\Commands\CustbalanceReportCron;
use App\ProductActivitiesLog;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ARExport;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $transactionUtil;
    protected $productUtil;
    protected $moduleUtil;
    protected $businessUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ContactUtil $contactUtil, BusinessUtil $businessUtil, ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->businessUtil = $businessUtil;
        $this->contactUtil = $contactUtil;
    }

    /**
     * Shows profit\loss of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getProfitLoss(Request $request)
    {
        if (!auth()->user()->can('profit_loss_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        //Set maximum php execution time
        ini_set('max_execution_time', 0);

        $business_id = $request->session()->get('user.business_id');

                    //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $location_id = $request->get('location_id');

             $data = $this->transactionUtil->getProfitLossDetails($business_id, $location_id, $start_date, $end_date);

            // $data['closing_stock'] = $data['closing_stock'] - $data['total_sell_return'];

            return view('report.partials.profit_loss_details', compact('data'))->render();
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);
        return view('report.profit_loss', compact('business_locations'));
    }

    /**
     * Shows product report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchaseSell(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $location_id = $request->get('location_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start_date, $end_date, $location_id);

            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            $transaction_types = [
                'purchase_return', 'sell_return'
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start_date,
                $end_date,
                $location_id
            );

            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];
            $total_sell_return_inc_tax = $transaction_totals['total_sell_return_inc_tax'];

            $difference = [
                'total' => $sell_details['total_sell_inc_tax'] + $total_sell_return_inc_tax - $purchase_details['total_purchase_inc_tax'] - $total_purchase_return_inc_tax,
                'due' => $sell_details['invoice_due'] - $purchase_details['purchase_due']
            ];
            $paymentDue = $this->getPaymentDue($business_id,$start_date,$end_date);
            $paymentVendor = $this->getVendorPayment($business_id,$start_date,$end_date);
            $purchaseOrder = $this->getPurchaseOrder($business_id,$start_date,$end_date);
            $saleData = $this->getSaleData($business_id,$start_date,$end_date);

            return ['purchase' => $purchase_details,
                    'sell' => $sell_details,
                    'total_purchase_return' => $total_purchase_return_inc_tax,
                    'total_sell_return' => $total_sell_return_inc_tax,
                    'difference' => $difference,
                    'final_sale_amount' => $saleData,
                    'final_po_amount' => $purchaseOrder
                ];
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.purchase_sell')
                    ->with(compact('business_locations'));
    }

    /**
     * Shows report for Supplier
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomerSuppliers(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $contacts = Contact::where('contacts.business_id', $business_id)
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->active()
                ->groupBy('contacts.id')
                ->select(
                    DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                    DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                    'contacts.supplier_business_name',
                    'contacts.name',
                    'contacts.id',
                    'contacts.type as contact_type'
                );
            $permitted_locations = auth()->user()->permitted_locations();

            if ($permitted_locations != 'all') {
                $contacts->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->input('customer_group_id'))) {
                $contacts->where('contacts.customer_group_id', $request->input('customer_group_id'));
            }

            if (!empty($request->input('contact_type'))) {
                $contacts->whereIn('contacts.type', [$request->input('contact_type'), 'both']);
            }

            return Datatables::of($contacts)
                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    if (!empty($row->supplier_business_name)) {
                        $name .= ', ' . $row->supplier_business_name;
                    }
                    return '<a href="' . action('ContactController@show', [$row->id]) . '" target="_blank" class="no-print">' .
                            $name .
                        '</a><span class="print_section">' . $name . '</span>';
                })
                ->editColumn('total_purchase', function ($row) {
                    return '<span class="display_currency total_purchase" data-orig-value="' . $row->total_purchase . '" data-currency_symbol = true>' . $row->total_purchase . '</span>';
                })
                ->editColumn('total_purchase_return', function ($row) {
                    return '<span class="display_currency total_purchase_return" data-orig-value="' . $row->total_purchase_return . '" data-currency_symbol = true>' . $row->total_purchase_return . '</span>';
                })
                ->editColumn('total_sell_return', function ($row) {
                    return '<span class="display_currency total_sell_return" data-orig-value="' . $row->total_sell_return . '" data-currency_symbol = true>' . $row->total_sell_return . '</span>';
                })
                ->editColumn('total_invoice', function ($row) {
                    return '<span class="display_currency total_invoice" data-orig-value="' . $row->total_invoice . '" data-currency_symbol = true>' . $row->total_invoice . '</span>';
                })
                ->addColumn('due', function ($row) {
                    $due = ($row->total_invoice - $row->invoice_received - $row->total_sell_return + $row->sell_return_paid) - ($row->total_purchase - $row->total_purchase_return + $row->purchase_return_received - $row->purchase_paid);

                    if ($row->contact_type == 'supplier') {
                        $due -= $row->opening_balance - $row->opening_balance_paid;
                    } else {
                        $due += $row->opening_balance - $row->opening_balance_paid;
                    }

                    if($due > 0){
                        return '<span class="display_currency total_due text-success" data-orig-value="' . $due . '" data-currency_symbol=true >' . $due .'</span>';
                    }
                    if($due < 0){
                        return '<span class="display_currency total_due text-danger" data-orig-value="' . $due . '" data-currency_symbol=true >' . $due .'</span>';
                    }
                    if($due == 0){
                        return '<span class="display_currency total_due" data-orig-value="' . $due . '" data-currency_symbol=true>' . $due .'</span>';
                    }
                })
                ->addColumn(
                    'opening_balance_due',
                    '<span class="display_currency opening_balance_due" data-currency_symbol=true data-orig-value="{{$opening_balance - $opening_balance_paid}}">{{$opening_balance - $opening_balance_paid}}</span>'
                )
                ->removeColumn('supplier_business_name')
                ->removeColumn('invoice_received')
                ->removeColumn('purchase_paid')
                ->removeColumn('id')
                ->rawColumns(['total_purchase', 'total_invoice', 'due', 'name', 'total_purchase_return', 'total_sell_return', 'opening_balance_due'])
                ->make(true);
        }

        $customer_group = CustomerGroup::forDropdown($business_id, false, true);
        $types = [
            '' => __('lang_v1.all'),
            'customer' => __('report.customer'),
            'supplier' => __('report.supplier')
        ];

        return view('report.contact')
        ->with(compact('customer_group', 'types'));
    }

    /**
     * Shows product stock report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
                                                ->get();
        $allowed_selling_price_group = false;
        foreach ($selling_price_groups as $selling_price_group) {
            if (auth()->user()->can('selling_price_group.' . $selling_price_group->id)) {
                $allowed_selling_price_group = true;
                break;
            }
        }
        if ($this->moduleUtil->isModuleInstalled('Manufacturing') && (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module'))) {
            $show_manufacturing_data = 1;
        } else {
            $show_manufacturing_data = 0;
        }
        if ($request->ajax()) {

            $filters = request()->only(['location_id', 'category_id', 'sub_category_id', 'brand_id', 'unit_id', 'tax_id', 'type',
                'only_mfg_products', 'active_state',  'not_for_selling', 'repair_model_id', 'product_id', 'active_state']);

            $filters['not_for_selling'] = isset($filters['not_for_selling']) && $filters['not_for_selling'] == 'true' ? 1 : 0;

            $filters['show_manufacturing_data'] = $show_manufacturing_data;

            //Return the details in ajax call
            $for = request()->input('for') == 'view_product' ? 'view_product' :'datatables';

            $products = $this->productUtil->getProductStockDetails($business_id, $filters, $for);
            //To show stock details on view product modal
            if ($for == 'view_product' && !empty(request()->input('product_id'))) {
                $product_stock_details = $products;

                return view('product.partials.product_stock_details')->with(compact('product_stock_details'));
            }

            $datatable =  Datatables::of($products)
                ->editColumn('stock', function ($row) {
                    if ($row->enable_stock) {
                        $stock = $row->stock ? $row->stock : 0 ;
                        return  '<span data-is_quantity="true" class="current_stock display_currency" data-orig-value="' . (float)$stock . '" data-unit="' . $row->unit . '" data-currency_symbol=false > ' . (float)$stock . '</span>' ;
                    } else {
                        return 'N/A';
                    }
                })
                ->editColumn('product', function ($row) {
                    $name = $row->product;
                    if ($row->type == 'variable') {
                        $name .= ' - ' . $row->product_variation . '-' . $row->variation_name;
                    }
                    return $name;
                })
                ->editColumn('total_sold', function ($row) {
                    $total_sold = 0;
                    if ($row->total_sold) {
                        $total_sold =  (float)$row->total_sold;
                    }

                    return '<span data-is_quantity="true" class="display_currency total_sold" data-currency_symbol=false data-orig-value="' . $total_sold . '" data-unit="' . $row->unit . '" >' . $total_sold . '</span> ' . $row->unit;
                })
                ->editColumn('total_transfered', function ($row) {
                    $total_transfered = 0;
                    if ($row->total_transfered) {
                        $total_transfered =  (float)$row->total_transfered;
                    }

                    return '<span data-is_quantity="true" class="display_currency total_transfered" data-currency_symbol=false data-orig-value="' . $total_transfered . '" data-unit="' . $row->unit . '" >' . $total_transfered . '</span> ' . $row->unit;
                })

                ->editColumn('total_adjusted', function ($row) {
                    $total_adjusted = 0;
                    if ($row->total_adjusted) {
                        $total_adjusted =  (float)$row->total_adjusted;
                    }

                    return '<span data-is_quantity="true" class="display_currency total_adjusted" data-currency_symbol=false  data-orig-value="' . $total_adjusted . '" data-unit="' . $row->unit . '" >' . $total_adjusted . '</span> ' . $row->unit;
                })
                ->editColumn('unit_price', function ($row) use ($allowed_selling_price_group) {
                    $html = '';
                    if (auth()->user()->can('access_default_selling_price')) {
                        $html .= '<span class="display_currency" data-currency_symbol=true >'
                        . $row->unit_price . '</span>';
                    }

                    if ($allowed_selling_price_group) {
                        $html .= ' <button type="button" class="btn btn-primary btn-xs btn-modal no-print" data-container=".view_modal" data-href="' . action('ProductController@viewGroupPrice', [$row->product_id]) .'">' . __('lang_v1.view_group_prices') . '</button>';
                    }

                    return $html;
                })
                ->editColumn('stock_price', function ($row) {
                    $html = '<span class="display_currency total_stock_price" data-currency_symbol=true data-orig-value="'
                        . $row->stock_price . '">'
                        . $row->stock_price . '</span>';

                    return $html;
                })
                ->editColumn('stock_value_by_sale_price', function ($row) {
                    $stock = $row->stock ? $row->stock : 0 ;
                    $unit_selling_price = (float)$row->group_price > 0 ? $row->group_price : $row->unit_price;
                    $stock_price = $stock * $unit_selling_price;
                    return  '<span class="stock_value_by_sale_price display_currency" data-orig-value="' . (float)$stock_price . '" data-currency_symbol=true > ' . (float)$stock_price . '</span>';
                })
                ->addColumn('potential_profit', function ($row) {
                    $stock = $row->stock ? $row->stock : 0 ;
                    $unit_selling_price = (float)$row->group_price > 0 ? $row->group_price : $row->unit_price;
                    $stock_price_by_sp = $stock * $unit_selling_price;
                    $potential_profit = $stock_price_by_sp - $row->stock_price;

                    return  '<span class="potential_profit display_currency" data-orig-value="' . (float)$potential_profit . '" data-currency_symbol=true > ' . (float)$potential_profit . '</span>';
                })
                ->removeColumn('enable_stock')
                ->removeColumn('unit')
                ->removeColumn('id');

            $raw_columns  = ['unit_price', 'total_transfered', 'total_sold',
                    'total_adjusted', 'stock', 'stock_price', 'stock_value_by_sale_price', 'potential_profit'];

            if ($show_manufacturing_data) {
                $datatable->editColumn('total_mfg_stock', function ($row) {
                    $total_mfg_stock = 0;
                    if ($row->total_mfg_stock) {
                        $total_mfg_stock =  (float)$row->total_mfg_stock;
                    }

                    return '<span data-is_quantity="true" class="display_currency total_mfg_stock" data-currency_symbol=false  data-orig-value="' . $total_mfg_stock . '" data-unit="' . $row->unit . '" >' . $total_mfg_stock . '</span> ' . $row->unit;
                });
                $raw_columns[] = 'total_mfg_stock';
            }

            return $datatable->rawColumns($raw_columns)->make(true);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.stock_report')
            ->with(compact('categories', 'brands', 'units', 'business_locations', 'show_manufacturing_data'));
    }


    /**
     * Shows product stock NEW report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockReportNew(Request $request)
    {

        if(!auth()->user()->can('stock_report.view'))
        {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $selling_price_groups        = SellingPriceGroup::where('business_id', $business_id)->get();
        $allowed_selling_price_group = false;
        foreach($selling_price_groups as $selling_price_group)
        {
            if(auth()->user()->can('selling_price_group.' . $selling_price_group->id))
            {
                $allowed_selling_price_group = true;
                break;
            }
        }
        if($this->moduleUtil->isModuleInstalled('Manufacturing') && (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')))
        {
            $show_manufacturing_data = 1;
        }
        else
        {
            $show_manufacturing_data = 0;
        }
        if($request->ajax())
        {

            $filters = request()->only(
                [
                    'location_id',
                    'category_id',
                    'sub_category_id',
                    'brand_id',
                    'unit_id',
                    'tax_id',
                    'type',
                    'only_mfg_products',
                    'active_state',
                    'not_for_selling',
                    'repair_model_id',
                    'product_id',
                    'active_state',
                    'start_date',
                    'end_date',
                    'supplier_id'
                ]
            );

            $filters['not_for_selling'] = isset($filters['not_for_selling']) && $filters['not_for_selling'] == 'true' ? 1 : 0;

            $filters['show_manufacturing_data'] = $show_manufacturing_data;

            $filters['start_date'] = request()->input('start_date');
            $filters['end_date']   = request()->input('end_date');

            if(empty(request()->input('include_nfs'))){
                $filters['only_for_selling'] = 1;
            }

            // Return the details in ajax call
            $for = request()->input('for') == 'view_product' ? 'view_product' : 'datatables';

            $products = $this->productUtil->getProductStockDetailsForStockReport($business_id, $filters, $for);


            // To show stock details on view product modal
            if($for == 'view_product' && !empty(request()->input('product_id')))
            {
                $product_stock_details = $products;

                return view('product.partials.product_stock_details')->with(compact('product_stock_details'));
            }

            $datatable = Datatables::of($products)->editColumn(
                'stock', function ($row){
                if($row->enable_stock)
                {
                    // return 'Total Purchase : '.$row->total_purchase. ' Total Sell : '.$row->total_sell. ' Total Stock :'.$row->stock;
                    $stock = $row->stock ? $row->stock : 0;

                    return '<span data-is_quantity="true" class="current_stock display_currency" data-orig-value="' . (float)$stock . '" data-unit="' . $row->unit . '" data-currency_symbol=false > ' . (float)$stock . '</span>';
                }
                else
                {
                    return 'N/A';
                }
            }
            )->editColumn(
                'total_purchase', function ($row){
                if($row->enable_stock)
                {
                    $stock = $row->total_purchase ? $row->total_purchase : 0;

                    return (float)$stock;
                }
                else
                {
                    return 'N/A';
                }
            }
            )->editColumn(
                'total_sell', function ($row){
                if($row->enable_stock)
                {
                    $stock = $row->total_sell ? $row->total_sell : 0;

                    return (float)$stock;
                }
                else
                {
                    return 'N/A';
                }
            }
            )->editColumn(
                'consignment_stock', function ($row){
                $consignment_stock = 0;
                if($row->consignment_stock)
                {
                    $consignment_stock = (float)$row->consignment_stock;
                    return (float)$consignment_stock;
                } else {
                    return $consignment_stock;
                }

            }
            )->editColumn(
                'product', function ($row){
                $name = $row->product;
                if($row->type == 'variable')
                {
                    $name .= ' - ' . $row->product_variation . '-' . $row->variation_name;
                }

                return $name;
            }
            )->editColumn(
                'total_sold', function ($row){
                $total_sold = 0;
                if($row->total_sold_new)
                {
                    // $total_sold = (float)$row->total_sold;
                    $total_sold = (float)$row->total_sold_new;

                }

                return '<span data-is_quantity="true" class="display_currency total_sold" data-currency_symbol=false data-orig-value="' . $total_sold . '" data-unit="' . $row->unit . '" >' . $total_sold . '</span> ';
            }
            )->editColumn(
                'total_purchased', function ($row){
                $total_purchased = 0;
                if($row->total_purchased)
                {
                    $total_purchased = (float)$row->total_purchased;
                }

                return '<span data-is_quantity="true" class="display_currency total_purchased" data-currency_symbol=false data-orig-value="' . $total_purchased . '" data-unit="' . $row->unit . '" >' . $total_purchased . '</span> ' . $row->unit;
            }
            )->editColumn(
                'total_transfered', function ($row){
                $total_transfered = 0;
                if($row->total_transfered)
                {
                    $total_transfered = (float)$row->total_transfered;
                }

                return '<span data-is_quantity="true" class="display_currency total_transfered" data-currency_symbol=false data-orig-value="' . $total_transfered . '" data-unit="' . $row->unit . '" >' . $total_transfered . '</span> ' . $row->unit;
            }
            )->editColumn(
                'total_adjusted', function ($row){
                $total_adjusted = 0;
                if($row->total_adjusted)
                {
                    $total_adjusted = (float)$row->total_adjusted;
                }

                return '<span data-is_quantity="true" class="display_currency total_adjusted" data-currency_symbol=false  data-orig-value="' . $total_adjusted . '" data-unit="' . $row->unit . '" >' . $total_adjusted . '</span> ' . $row->unit;
            }
            )->editColumn(
                'unit_price', function ($row) use ($allowed_selling_price_group){
                $html = '';
                if(auth()->user()->can('access_default_selling_price'))
                {
                    $html .= '<span class="display_currency" data-currency_symbol=true >' . $row->unit_price . '</span>';
                }

                if($allowed_selling_price_group)
                {
                    $html .= ' <button type="button" class="btn btn-primary btn-xs btn-modal no-print" data-container=".view_modal" data-href="' . action('ProductController@viewGroupPrice', [$row->product_id]) . '">' . __('lang_v1.view_group_prices') . '</button>';
                }

                return $html;
            }
            // )->editColumn(
            //     'stock_price', function ($row){
            //     $html = '<span class="display_currency total_stock_price" data-currency_symbol=true data-orig-value="' . $row->stock_price . '">' . $row->stock_price . '</span>';

            //     return $html;
            // }
            )->editColumn('stock_price', function ($row) {

                    $stock = $row->stock ? $row->stock : 0 ;
                    $unit_purchase_price = (float)$row->group_price > 0 ? $row->group_price : $row->unit_purchase_price;
                    $stock_price = $stock * $unit_purchase_price;
                    return  '<span class="stock_value_by_sale_price display_currency" data-orig-value="' . (float)$stock_price . '" data-currency_symbol=true > ' . (float)$stock_price . '</span>';

            }
            )->editColumn(
                'stock_value_by_sale_price', function ($row){
                $stock              = $row->stock ? $row->stock : 0;
                $unit_selling_price = (float)$row->group_price > 0 ? $row->group_price : $row->unit_price;
                $stock_price        = $stock * $unit_selling_price;

                return '<span class="stock_value_by_sale_price display_currency" data-orig-value="' . (float)$stock_price . '" data-currency_symbol=true > ' . (float)$stock_price . '</span>';
            }
            )->addColumn(
                'potential_profit', function ($row){
                $stock              = $row->stock ? $row->stock : 0;
                $unit_selling_price = (float)$row->group_price > 0 ? $row->group_price : $row->unit_price;
                $stock_price_by_sp  = $stock * $unit_selling_price;

                $unit_purchase_price = (float)$row->group_price > 0 ? $row->group_price : $row->unit_purchase_price;
                $stock_price = $stock * $unit_purchase_price;

                $potential_profit   = $stock_price_by_sp - $stock_price;

                return '<span class="potential_profit display_currency" data-orig-value="' . (float)$potential_profit . '" data-currency_symbol=true > ' . (float)$potential_profit . '</span>';
            }
            )->removeColumn('enable_stock')->removeColumn('unit')->removeColumn('id');

            $raw_columns = [
                'unit_price',
                'total_transfered',
                'total_sold',
                'consignment_stock',
                'total_purchased',
                'total_adjusted',
                'stock',
                'stock_price',
                'stock_value_by_sale_price',
                'potential_profit',
            ];

            if($show_manufacturing_data)
            {
                $datatable->editColumn(
                    'total_mfg_stock', function ($row){
                    $total_mfg_stock = 0;
                    if($row->total_mfg_stock)
                    {
                        $total_mfg_stock = (float)$row->total_mfg_stock;
                    }

                    return '<span data-is_quantity="true" class="display_currency total_mfg_stock" data-currency_symbol=false  data-orig-value="' . $total_mfg_stock . '" data-unit="' . $row->unit . '" >' . $total_mfg_stock . '</span> ' . $row->unit;
                }
                );
                $raw_columns[] = 'total_mfg_stock';
            }
            return $datatable->rawColumns($raw_columns)->make(true);
        }

        $categories         = Category::forDropdown($business_id, 'product');
        $brands             = Brands::forDropdown($business_id);
        $units              = Unit::where('business_id', $business_id)->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        $contacts = Contact::where('business_id',$business_id)->where('type','supplier')->get();


        return view('report.stock_report_new')->with(compact('contacts','categories', 'brands', 'units', 'business_locations', 'show_manufacturing_data'));
    }

    /**
     * Shows product stock details
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockDetails(Request $request)
    {
        //Return the details in ajax call
        if ($request->ajax()) {
            $business_id = $request->session()->get('user.business_id');
            $product_id = $request->input('product_id');
            $query = Product::leftjoin('units as u', 'products.unit_id', '=', 'u.id')
                ->join('variations as v', 'products.id', '=', 'v.product_id')
                ->join('product_variations as pv', 'pv.id', '=', 'v.product_variation_id')
                ->leftjoin('variation_location_details as vld', 'v.id', '=', 'vld.variation_id')
                ->where('products.business_id', $business_id)
                ->where('products.id', $product_id)
                ->whereNull('v.deleted_at');

            $permitted_locations = auth()->user()->permitted_locations();
            $location_filter = '';
            if ($permitted_locations != 'all') {
                $query->whereIn('vld.location_id', $permitted_locations);
                $locations_imploded = implode(', ', $permitted_locations);
                $location_filter .= "AND transactions.location_id IN ($locations_imploded) ";
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');

                $query->where('vld.location_id', $location_id);

                $location_filter .= "AND transactions.location_id=$location_id";
            }

            $product_details =  $query->select(
                'products.name as product',
                'u.short_name as unit',
                'pv.name as product_variation',
                'v.name as variation',
                'v.sub_sku as sub_sku',
                'v.sell_price_inc_tax',
                DB::raw("SUM(vld.qty_available) as stock"),
                DB::raw("(SELECT SUM(IF(transactions.type='sell', TSL.quantity - TSL.quantity_returned, -1* TPL.quantity) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id

                        LEFT JOIN purchase_lines AS TPL ON transactions.id=TPL.transaction_id

                        WHERE transactions.status='final' AND transactions.type='sell' $location_filter
                        AND (TSL.variation_id=v.id OR TPL.variation_id=v.id)) as total_sold"),
                DB::raw("(SELECT SUM(IF(transactions.type='sell_transfer', TSL.quantity, 0) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='sell_transfer' $location_filter
                        AND (TSL.variation_id=v.id)) as total_transfered"),
                DB::raw("(SELECT SUM(IF(transactions.type='stock_adjustment', SAL.quantity, 0) ) FROM transactions
                        LEFT JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='stock_adjustment' $location_filter
                        AND (SAL.variation_id=v.id)) as total_adjusted")
                // DB::raw("(SELECT SUM(quantity) FROM transaction_sell_lines LEFT JOIN transactions ON transaction_sell_lines.transaction_id=transactions.id WHERE transactions.status='final' $location_filter AND
                //     transaction_sell_lines.variation_id=v.id) as total_sold")
            )
                        ->groupBy('v.id')
                        ->get();

            return view('report.stock_details')
                        ->with(compact('product_details'));
        }
    }

    /**
     * Shows tax report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getTaxDetails(Request $request)
    {
        if (!auth()->user()->can('tax_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {

            $business_id = $request->session()->get('user.business_id');
            $taxes = TaxRate::forBusiness($business_id);
            $type = $request->input('type');

            $sells = Transaction::leftJoin('tax_rates as tr', 'transactions.tax_id', '=', 'tr.id')
                            ->leftJoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
                ->where('transactions.business_id', $business_id)
                ->select('c.name as contact_name',
                        'c.tax_number',
                        'transactions.ref_no',
                        'transactions.invoice_no',
                        'transactions.transaction_date',
                        'transactions.total_before_tax',
                        'transactions.tax_id',
                        'transactions.tax_amount',
                        'transactions.id',
                        'transactions.type',
                        'transactions.discount_type',
                        'transactions.discount_amount'
                    );
                if ($type == 'sell') {
                    $sells->where('transactions.type', 'sell')
                    ->where('transactions.status', 'final')
                    ->where( function($query){
                        $query->whereHas('sell_lines',function($q){
                            $q->whereNotNull('transaction_sell_lines.tax_id');
                        })->orWhereNotNull('transactions.tax_id');
                    })
                    ->with(['sell_lines' => function($q){
                        $q->whereNotNull('transaction_sell_lines.tax_id');
                    }, 'sell_lines.line_tax']);
                }
                if ($type == 'purchase') {
                    $sells->where('transactions.type', 'purchase')
                    ->where('transactions.status', 'received')
                    ->where( function($query){
                        $query->whereHas('purchase_lines', function($q){
                            $q->whereNotNull('purchase_lines.tax_id');
                        })->orWhereNotNull('transactions.tax_id');
                    })
                    ->with(['purchase_lines' => function($q){
                        $q->whereNotNull('purchase_lines.tax_id');
                    }, 'purchase_lines.line_tax']);
                }

                if ($type == 'expense') {
                    $sells->where('transactions.type', 'expense')
                        ->whereNotNull('transactions.tax_id');
                }

                if (request()->has('location_id')) {
                    $location_id = request()->get('location_id');
                    if (!empty($location_id)) {
                        $sells->where('transactions.location_id', $location_id);
                    }
                }
                if (!empty(request()->start_date) && !empty(request()->end_date)) {
                    $start = request()->start_date;
                    $end =  request()->end_date;
                    $sells->whereDate('transactions.transaction_date', '>=', $start)
                                ->whereDate('transactions.transaction_date', '<=', $end);
                }
                $datatable = Datatables::of($sells);
                $raw_cols = ['total_before_tax', 'discount_amount'];
                $group_taxes_array = TaxRate::groupTaxes($business_id);
                $group_taxes = [];
                foreach ($group_taxes_array as $group_tax) {
                   foreach ($group_tax['sub_taxes'] as $sub_tax) {
                       $group_taxes[$group_tax->id]['sub_taxes'][$sub_tax->id] = $sub_tax;
                   }
                }
                foreach ($taxes as $tax) {
                    $col = 'tax_' . $tax['id'];
                    $raw_cols[] = $col;
                    $datatable->addColumn($col, function($row) use($tax, $type, $col, $group_taxes) {
                        $tax_amount = 0;
                        if ($type == 'sell') {
                            foreach ($row->sell_lines as $sell_line) {
                                if ($sell_line->tax_id == $tax['id']) {
                                    $tax_amount += ($sell_line->item_tax * ($sell_line->quantity - $sell_line->quantity_returned) );
                                }

                                //break group tax
                                if ($sell_line->line_tax->is_tax_group == 1 && array_key_exists($tax['id'], $group_taxes[$sell_line->tax_id]['sub_taxes'])) {

                                    $group_tax_details = $this->transactionUtil->groupTaxDetails($sell_line->line_tax, $sell_line->item_tax);

                                    $sub_tax_share = 0;
                                    foreach ($group_tax_details as $sub_tax_details) {
                                        if ($sub_tax_details['id'] == $tax['id']) {
                                            $sub_tax_share = $sub_tax_details['calculated_tax'];
                                        }
                                    }

                                    $tax_amount += ($sub_tax_share * ($sell_line->quantity - $sell_line->quantity_returned) );
                                }
                            }
                        } elseif ($type == 'purchase') {
                            foreach ($row->purchase_lines as $purchase_line) {
                                if ($purchase_line->tax_id == $tax['id']) {
                                    $tax_amount += ($purchase_line->item_tax * ($purchase_line->quantity - $purchase_line->quantity_returned));
                                }

                                //break group tax
                                if ($purchase_line->line_tax->is_tax_group == 1 && array_key_exists($tax['id'], $group_taxes[$purchase_line->tax_id]['sub_taxes'])) {

                                    $group_tax_details = $this->transactionUtil->groupTaxDetails($purchase_line->line_tax, $purchase_line->item_tax);

                                    $sub_tax_share = 0;
                                    foreach ($group_tax_details as $sub_tax_details) {
                                        if ($sub_tax_details['id'] == $tax['id']) {
                                            $sub_tax_share = $sub_tax_details['calculated_tax'];
                                        }
                                    }

                                    $tax_amount += ($sub_tax_share * ($purchase_line->quantity - $purchase_line->quantity_returned) );
                                }
                            }
                        }
                        if ($row->tax_id == $tax['id']) {
                            $tax_amount += $row->tax_amount;
                        }

                        //break group tax
                        if (!empty($group_taxes[$row->tax_id]) && array_key_exists($tax['id'], $group_taxes[$row->tax_id]['sub_taxes'])) {

                            $group_tax_details = $this->transactionUtil->groupTaxDetails($row->tax_id, $row->tax_amount);

                            $sub_tax_share = 0;
                            foreach ($group_tax_details as $sub_tax_details) {
                                if ($sub_tax_details['id'] == $tax['id']) {
                                    $sub_tax_share = $sub_tax_details['calculated_tax'];
                                }
                            }

                            $tax_amount += $sub_tax_share;
                        }

                        if ($tax_amount > 0) {
                            return '<span class="display_currency ' . $col . '" data-currency_symbol="true" data-orig-value="' . $tax_amount . '">' . $tax_amount . '</span>';
                        } else {
                            return '';
                        }
                    });
                }

                $datatable->editColumn(
                    'total_before_tax',
                    '<span class="display_currency total_before_tax" data-currency_symbol="true" data-orig-value="{{$total_before_tax}}">{{$total_before_tax}}</span>'
                )->editColumn('discount_amount', '@if($discount_amount != 0)<span class="display_currency" data-currency_symbol="true">{{$discount_amount}}</span>@if($discount_type == "percentage")% @endif @endif')
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}');

                return $datatable->rawColumns($raw_cols)
                            ->make(true);
        }
    }

    /**
     * Shows tax report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getTaxReport(Request $request)
    {
        if (!auth()->user()->can('tax_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $location_id = $request->get('location_id');

            $input_tax_details = $this->transactionUtil->getInputTax($business_id, $start_date, $end_date, $location_id);

            $output_tax_details = $this->transactionUtil->getOutputTax($business_id, $start_date, $end_date, $location_id);

            $expense_tax_details = $this->transactionUtil->getExpenseTax($business_id, $start_date, $end_date, $location_id);

            $module_output_taxes = $this->moduleUtil->getModuleData('getModuleOutputTax', ['start_date' => $start_date, 'end_date' => $end_date]);

            $total_module_output_tax = 0;
            foreach ($module_output_taxes as $key => $module_output_tax) {
                $total_module_output_tax += $module_output_tax;
            }

            $total_output_tax = $output_tax_details['total_tax'] + $total_module_output_tax;

            $tax_diff = $total_output_tax - $input_tax_details['total_tax'] - $expense_tax_details['total_tax'];

            return [
                    'tax_diff' => $tax_diff
                ];
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $taxes = TaxRate::forBusiness($business_id);

        $tax_report_tabs = $this->moduleUtil->getModuleData('getTaxReportViewTabs');

        return view('report.tax_report')
            ->with(compact('business_locations', 'taxes', 'tax_report_tabs'));
    }

    /**
     * Shows trending products
     *
     * @return \Illuminate\Http\Response
     */
    public function getTrendingProducts(Request $request)
    {
        if (!auth()->user()->can('trending_product_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $filters = request()->only(['category', 'sub_category', 'brand', 'unit', 'limit', 'location_id', 'product_type']);

        $date_range = request()->input('date_range');

        if (!empty($date_range)) {
            $date_range_array = explode('~', $date_range);
            $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
            $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
        }

        $products = $this->productUtil->getTrendingProducts($business_id, $filters);

        $values = [];
        $labels = [];
        foreach ($products as $product) {
            $values[] = (float) $product->total_unit_sold;
            $labels[] = $product->product . ' (' . $product->unit . ')';
        }

        $chart = new CommonChart;
        $chart->labels($labels)
            ->dataset(__('report.total_unit_sold'), 'column', $values);

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.trending_products')
                    ->with(compact('chart', 'categories', 'brands', 'units', 'business_locations'));
    }

    public function getTrendingProductsAjax()
    {
        $business_id = request()->session()->get('user.business_id');
    }
    /**
     * Shows expense report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getExpenseReport(Request $request)
    {
        if (!auth()->user()->can('expense_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $filters = $request->only(['category', 'location_id']);

        $date_range = $request->input('date_range');

        if (!empty($date_range)) {
            $date_range_array = explode('~', $date_range);
            $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
            $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
        } else {
            $filters['start_date'] = \Carbon::now()->startOfMonth()->format('Y-m-d');
            $filters['end_date'] = \Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $expenses = $this->transactionUtil->getExpenseReport($business_id, $filters);

        $values = [];
        $labels = [];
        foreach ($expenses as $expense) {
            $values[] = (float) $expense->total_expense;
            $labels[] = !empty($expense->category) ? $expense->category : __('report.others');
        }

        $chart = new CommonChart;
        $chart->labels($labels)
            ->title(__('report.expense_report'))
            ->dataset(__('report.total_expense'), 'column', $values);

        $categories = ExpenseCategory::where('business_id', $business_id)
                            ->pluck('name', 'id');

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.expense_report')
                    ->with(compact('chart', 'categories', 'business_locations', 'expenses'));
    }

    /**
     * Shows stock adjustment report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockAdjustmentReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $query =  Transaction::where('business_id', $business_id)
                            ->where('type', 'stock_adjustment');

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('location_id', $permitted_locations);
            }

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }
            $location_id = $request->get('location_id');
            if (!empty($location_id)) {
                $query->where('location_id', $location_id);
            }

            $stock_adjustment_details = $query->select(
                DB::raw("SUM(final_total) as total_amount"),
                DB::raw("SUM(total_amount_recovered) as total_recovered"),
                DB::raw("SUM(IF(adjustment_type = 'normal', final_total, 0)) as total_normal"),
                DB::raw("SUM(IF(adjustment_type = 'abnormal', final_total, 0)) as total_abnormal")
            )->first();
            return $stock_adjustment_details;
        }
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.stock_adjustment_report')
                    ->with(compact('business_locations'));
    }

    /**
     * Shows register report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegisterReport(Request $request)
    {
        if (!auth()->user()->can('register_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $registers = CashRegister::join(
                'users as u',
                'u.id',
                '=',
                'cash_registers.user_id'
                )
                ->leftJoin(
                    'business_locations as bl',
                    'bl.id',
                    '=',
                    'cash_registers.location_id'
                )
                ->where('cash_registers.business_id', $business_id)
                ->select(
                    'cash_registers.*',
                    DB::raw(
                        "CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), '<br>', COALESCE(u.email, '')) as user_name"
                    ),
                    'bl.name as location_name'
                );

            if (!empty($request->input('user_id'))) {
                $registers->where('cash_registers.user_id', $request->input('user_id'));
            }
            if (!empty($request->input('status'))) {
                $registers->where('cash_registers.status', $request->input('status'));
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $registers->whereDate('cash_registers.created_at', '>=', $start_date)
                        ->whereDate('cash_registers.created_at', '<=', $end_date);
            }
            return Datatables::of($registers)
                ->editColumn('total_card_slips', function ($row) {
                    if ($row->status == 'close') {
                        return $row->total_card_slips;
                    } else {
                        return '';
                    }
                })
                ->editColumn('total_cheques', function ($row) {
                    if ($row->status == 'close') {
                        return $row->total_cheques;
                    } else {
                        return '';
                    }
                })
                ->editColumn('closed_at', function ($row) {
                    if ($row->status == 'close') {
                        return $this->productUtil->format_date($row->closed_at, true);
                    } else {
                        return '';
                    }
                })
                ->editColumn('created_at', function ($row) {
                    return $this->productUtil->format_date($row->created_at, true);
                })
                ->editColumn('closing_amount', function ($row) {
                    if ($row->status == 'close') {
                        return '<span class="display_currency" data-currency_symbol="true">' .
                        $row->closing_amount . '</span>';
                    } else {
                        return '';
                    }
                })
                ->addColumn('action', '<button type="button" data-href="{{action(\'CashRegisterController@show\', [$id])}}" class="btn btn-xs btn-info btn-modal"
                    data-container=".view_register"><i class="fas fa-eye" aria-hidden="true"></i> @lang("messages.view")</button> @if($status != "close" && auth()->user()->can("close_cash_register"))<button type="button" data-href="{{action(\'CashRegisterController@getCloseRegister\', [$id])}}" class="btn btn-xs btn-danger btn-modal"
                        data-container=".view_register"><i class="fas fa-window-close"></i> @lang("messages.close")</button> @endif')
                ->filterColumn('user_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), '<br>', COALESCE(u.email, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['action', 'user_name', 'closing_amount'])
                ->make(true);
        }

        $users = User::forDropdown($business_id, false);

        return view('report.register_report')
                    ->with(compact('users'));
    }

    /**
     * Shows sales representative report
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesRepresentativeReport(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $users = User::allUsersDropdown($business_id, false);
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.sales_representative')
                ->with(compact('users', 'business_locations'));
    }

    /**
     * Shows sales representative total expense
     *
     * @return json
     */
    public function getSalesRepresentativeTotalExpense(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $business_id = $request->session()->get('user.business_id');

            $filters = $request->only(['expense_for', 'location_id', 'start_date', 'end_date']);

            $total_expense = $this->transactionUtil->getExpenseReport($business_id, $filters, 'total');

            return $total_expense;
        }
    }

    /**
     * Shows sales representative total sales
     *
     * @return json
     */
    public function getSalesRepresentativeTotalSell(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $location_id = $request->get('location_id');
            $created_by = $request->get('created_by');

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start_date, $end_date, $location_id, $created_by);

            //Get Sell Return details
            $transaction_types = [
                'sell_return'
            ];
            $sell_return_details = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start_date,
                $end_date,
                $location_id,
                $created_by
            );

            $total_sell_return = !empty($sell_return_details['total_sell_return_exc_tax']) ? $sell_return_details['total_sell_return_exc_tax'] : 0;
            $total_sell = $sell_details['total_sell_exc_tax'] - $total_sell_return;

            return [
                'total_sell_exc_tax' => $sell_details['total_sell_exc_tax'],
                'total_sell_return_exc_tax' => $total_sell_return,
                'total_sell' => $total_sell
            ];
        }
    }

    /**
     * Shows sales representative total commission
     *
     * @return json
     */
    public function getSalesRepresentativeTotalCommission(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $location_id = $request->get('location_id');
            $commission_agent = $request->get('commission_agent');

            $sell_details = $this->transactionUtil->getTotalSellCommission($business_id, $start_date, $end_date, $location_id, $commission_agent);

            //Get Commision
            $commission_percentage = User::find($commission_agent)->cmmsn_percent;
            $total_commission = $commission_percentage * $sell_details['total_sales_with_commission'] / 100;

            return ['total_sales_with_commission' =>
                        $sell_details['total_sales_with_commission'],
                    'total_commission' => $total_commission,
                    'commission_percentage' => $commission_percentage
                ];
        }
    }

    /**
     * Shows product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockExpiryReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //TODO:: Need to display reference number and edit expiry date button

        //Return the details in ajax call
        if ($request->ajax()) {
            $query = PurchaseLine::leftjoin(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
            )
                            ->leftjoin(
                                'products as p',
                                'purchase_lines.product_id',
                                '=',
                                'p.id'
                            )
                            ->leftjoin(
                                'variations as v',
                                'purchase_lines.variation_id',
                                '=',
                                'v.id'
                            )
                            ->leftjoin(
                                'product_variations as pv',
                                'v.product_variation_id',
                                '=',
                                'pv.id'
                            )
                            ->leftjoin('business_locations as l', 't.location_id', '=', 'l.id')
                            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                            ->where('t.business_id', $business_id)
                            //->whereNotNull('p.expiry_period')
                            //->whereNotNull('p.expiry_period_type')
                            //->whereNotNull('exp_date')
                            ->where('p.enable_stock', 1);
            // ->whereRaw('purchase_lines.quantity > purchase_lines.quantity_sold + quantity_adjusted + quantity_returned');

            $permitted_locations = auth()->user()->permitted_locations();

            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');
                $query->where('t.location_id', $location_id)
                        //If filter by location then hide products not available in that location
                        ->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                        ->where(function ($q) use ($location_id) {
                            $q->where('pl.location_id', $location_id);
                        });
            }

            if (!empty($request->input('category_id'))) {
                $query->where('p.category_id', $request->input('category_id'));
            }
            if (!empty($request->input('sub_category_id'))) {
                $query->where('p.sub_category_id', $request->input('sub_category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $query->where('p.brand_id', $request->input('brand_id'));
            }
            if (!empty($request->input('unit_id'))) {
                $query->where('p.unit_id', $request->input('unit_id'));
            }
            if (!empty($request->input('exp_date_filter'))) {
                $query->whereDate('exp_date', '<=', $request->input('exp_date_filter'));
            }

            $only_mfg_products = request()->get('only_mfg_products', 0);
            if (!empty($only_mfg_products)) {
                $query->where('t.type', 'production_purchase');
            }

            $report = $query->select(
                'p.name as product',
                'p.sku',
                'p.type as product_type',
                'v.name as variation',
                'pv.name as product_variation',
                'l.name as location',
                'mfg_date',
                'exp_date',
                'u.short_name as unit',
                DB::raw("SUM(COALESCE(quantity, 0) - COALESCE(quantity_sold, 0) - COALESCE(quantity_adjusted, 0) - COALESCE(quantity_returned, 0)) as stock_left"),
                't.ref_no',
                't.id as transaction_id',
                'purchase_lines.id as purchase_line_id',
                'purchase_lines.lot_number'
            )
            ->having('stock_left', '>', 0)
            ->groupBy('purchase_lines.exp_date')
            ->groupBy('purchase_lines.lot_number');

            return Datatables::of($report)
                ->editColumn('name', function ($row) {
                    if ($row->product_type == 'variable') {
                        return $row->product . ' - ' .
                        $row->product_variation . ' - ' . $row->variation;
                    } else {
                        return $row->product;
                    }
                })
                ->editColumn('mfg_date', function ($row) {
                    if (!empty($row->mfg_date)) {
                        return $this->productUtil->format_date($row->mfg_date);
                    } else {
                        return '--';
                    }
                })
                // ->editColumn('exp_date', function ($row) {
                //     if (!empty($row->exp_date)) {
                //         $carbon_exp = \Carbon::createFromFormat('Y-m-d', $row->exp_date);
                //         $carbon_now = \Carbon::now();
                //         if ($carbon_now->diffInDays($carbon_exp, false) >= 0) {
                //             return $this->productUtil->format_date($row->exp_date) . '<br><small>( <span class="time-to-now">' . $row->exp_date . '</span> )</small>';
                //         } else {
                //             return $this->productUtil->format_date($row->exp_date) . ' &nbsp; <span class="label label-danger no-print">' . __('report.expired') . '</span><span class="print_section">' . __('report.expired') . '</span><br><small>( <span class="time-from-now">' . $row->exp_date . '</span> )</small>';
                //         }
                //     } else {
                //         return '--';
                //     }
                // })
                ->editColumn('ref_no', function ($row) {
                    return '<button type="button" data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" class="btn btn-link btn-modal" data-container=".view_modal"  >' . $row->ref_no . '</button>';
                })
                ->editColumn('stock_left', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency stock_left" data-currency_symbol=false data-orig-value="' . $row->stock_left . '" data-unit="' . $row->unit . '" >' . $row->stock_left . '</span> ' . $row->unit;
                })
                ->addColumn('edit', function ($row) {
                    $html =  '<button type="button" class="btn btn-primary btn-xs stock_expiry_edit_btn" data-transaction_id="' . $row->transaction_id . '" data-purchase_line_id="' . $row->purchase_line_id . '"> <i class="fa fa-edit"></i> ' . __("messages.edit") .
                    '</button>';

                    if (!empty($row->exp_date)) {
                        $carbon_exp = \Carbon::createFromFormat('Y-m-d', $row->exp_date);
                        $carbon_now = \Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) < 0) {
                            $html .=  ' <button type="button" class="btn btn-warning btn-xs remove_from_stock_btn" data-href="' . action('StockAdjustmentController@removeExpiredStock', [$row->purchase_line_id]) . '"> <i class="fa fa-trash"></i> ' . __("lang_v1.remove_from_stock") .
                            '</button>';
                        }
                    }

                    return $html;
                })
                ->rawColumns(['exp_date', 'ref_no', 'edit', 'stock_left'])
                ->make(true);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        $view_stock_filter = [
            \Carbon::now()->subDay()->format('Y-m-d') => __('report.expired'),
            \Carbon::now()->addWeek()->format('Y-m-d') => __('report.expiring_in_1_week'),
            \Carbon::now()->addDays(15)->format('Y-m-d') => __('report.expiring_in_15_days'),
            \Carbon::now()->addMonth()->format('Y-m-d') => __('report.expiring_in_1_month'),
            \Carbon::now()->addMonths(3)->format('Y-m-d') => __('report.expiring_in_3_months'),
            \Carbon::now()->addMonths(6)->format('Y-m-d') => __('report.expiring_in_6_months'),
            \Carbon::now()->addYear()->format('Y-m-d') => __('report.expiring_in_1_year')
        ];

        return view('report.stock_expiry_report')
                ->with(compact('categories', 'brands', 'units', 'business_locations', 'view_stock_filter'));
    }

    /**
     * Shows product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockExpiryReportEditModal(Request $request, $purchase_line_id)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $purchase_line = PurchaseLine::join(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
            )
                                ->join(
                                    'products as p',
                                    'purchase_lines.product_id',
                                    '=',
                                    'p.id'
                                )
                                ->where('purchase_lines.id', $purchase_line_id)
                                ->where('t.business_id', $business_id)
                                ->select(['purchase_lines.*', 'p.name', 't.ref_no'])
                                ->first();

            if (!empty($purchase_line)) {
                if (!empty($purchase_line->exp_date)) {
                    $purchase_line->exp_date = date('m/d/Y', strtotime($purchase_line->exp_date));
                }
            }

            return view('report.partials.stock_expiry_edit_modal')
                ->with(compact('purchase_line'));
        }
    }

    /**
     * Update product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStockExpiryReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Return the details in ajax call
            if ($request->ajax()) {
                DB::beginTransaction();

                $input = $request->only(['purchase_line_id', 'exp_date']);

                $purchase_line = PurchaseLine::join(
                    'transactions as t',
                    'purchase_lines.transaction_id',
                    '=',
                    't.id'
                )
                                    ->join(
                                        'products as p',
                                        'purchase_lines.product_id',
                                        '=',
                                        'p.id'
                                    )
                                    ->where('purchase_lines.id', $input['purchase_line_id'])
                                    ->where('t.business_id', $business_id)
                                    ->select(['purchase_lines.*', 'p.name', 't.ref_no'])
                                    ->first();

                if (!empty($purchase_line) && !empty($input['exp_date'])) {
                    $purchase_line->exp_date = $this->productUtil->uf_date($input['exp_date']);
                    $purchase_line->save();
                }

                DB::commit();

                $output = ['success' => 1,
                            'msg' => __('lang_v1.updated_succesfully')
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return $output;
    }

    /**
     * Shows product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomerGroup(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = Transaction::leftjoin('customer_groups AS CG', 'transactions.customer_group_id', '=', 'CG.id')
                        ->where('transactions.business_id', $business_id)
                        ->where('transactions.type', 'sell')
                        ->where('transactions.status', 'final')
                        ->groupBy('transactions.customer_group_id')
                        ->select(DB::raw("SUM(final_total) as total_sell"), 'CG.name');

            $group_id = $request->get('customer_group_id', null);
            if (!empty($group_id)) {
                $query->where('transactions.customer_group_id', $group_id);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('transactions.location_id', $location_id);
            }

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }


            return Datatables::of($query)
                ->editColumn('total_sell', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_sell . '</span>';
                })
                ->rawColumns(['total_sell'])
                ->make(true);
        }

        $customer_group = CustomerGroup::forDropdown($business_id, false, true);
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.customer_group')
            ->with(compact('customer_group', 'business_locations'));
    }

    /**
     * Shows product purchase report
     *
     * @return \Illuminate\Http\Response
     */
    public function getproductPurchaseReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = PurchaseLine::join(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
                    )
                    ->join(
                        'variations as v',
                        'purchase_lines.variation_id',
                        '=',
                        'v.id'
                    )
                    ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                    ->join('contacts as c', 't.contact_id', '=', 'c.id')
                    ->join('products as p', 'pv.product_id', '=', 'p.id')
                    ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                    ->leftjoin('categories as sc', 'p.sub_category_id', '=', 'sc.id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'purchase')
                    ->select(
                        'p.name as product_name',
                        'sc.name as sub_category',
                        'p.type as product_type',
                        'pv.name as product_variation',
                        'v.name as variation_name',
                        'v.sub_sku',
                        'c.name as supplier',
                        't.id as transaction_id',
                        't.ref_no',
                        't.transaction_date as transaction_date',
                        'purchase_lines.purchase_price_inc_tax as unit_purchase_price',
                        DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                        'purchase_lines.quantity_adjusted',
                        'u.short_name as unit',
                        DB::raw('((purchase_lines.quantity - purchase_lines.quantity_returned - purchase_lines.quantity_adjusted) * purchase_lines.purchase_price_inc_tax) as subtotal')
                    )
                    ->groupBy('purchase_lines.id');
            if (!empty($variation_id)) {
                $query->where('purchase_lines.variation_id', $variation_id);
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $supplier_id = $request->get('supplier_id', null);
            if (!empty($supplier_id)) {
                $query->where('t.contact_id', $supplier_id);
            }

            $filters = request()->only(['id']);
            if (!empty($request->input('product_id'))) {
                $query->where('p.id', $request->input('product_id'));
            }

            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                 ->editColumn('ref_no', function ($row) {
                     return '<a data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->ref_no . '</a>';
                 })
                 ->editColumn('purchase_qty', function ($row) {
                     return '<span data-is_quantity="true" class="display_currency purchase_qty" data-currency_symbol=false data-orig-value="' . (float)$row->purchase_qty . '" data-unit="' . $row->unit . '" >' . (float) $row->purchase_qty . '</span> ' . $row->unit;
                 })
                 ->editColumn('quantity_adjusted', function ($row) {
                     return '<span data-is_quantity="true" class="display_currency quantity_adjusted" data-currency_symbol=false data-orig-value="' . (float)$row->quantity_adjusted . '" data-unit="' . $row->unit . '" >' . (float) $row->quantity_adjusted . '</span> ' . $row->unit;
                 })
                 ->editColumn('subtotal', function ($row) {
                     return '<span class="display_currency row_subtotal" data-currency_symbol=true data-orig-value="' . $row->subtotal . '">' . $row->subtotal . '</span>';
                 })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('unit_purchase_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->unit_purchase_price . '</span>';
                })
                ->rawColumns(['ref_no', 'unit_purchase_price', 'subtotal', 'purchase_qty', 'quantity_adjusted'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id);
        $products = Product::forDropdown($business_id);


        return view('report.product_purchase_report')
            ->with(compact('business_locations', 'suppliers','products'));
    }

    /**
     * Shows product purchase report
     *
     * @return \Illuminate\Http\Response
     */
    public function getproductSellReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('contacts as c', 't.contact_id', '=', 'c.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    'c.name as customer',
                    'c.contact_id',
                    'c.address_line_1 as address',
                    'c.city',
                    'c.state',
                    'c.zip_code',
                    't.id as transaction_id',
                    't.invoice_no',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.unit_price_before_discount as unit_price',
                    'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                    DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
                    'transaction_sell_lines.line_discount_type as discount_type',
                    'transaction_sell_lines.line_discount_amount as discount_amount',
                    'transaction_sell_lines.item_tax',
                    'tax_rates.name as tax',
                    'u.short_name as unit',
                    DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal'),
                    DB::raw('IF (p.category_id IS NOT NULL , ( SELECT cat.name FROM categories  AS cat   WHERE p.category_id=cat.id
                            ), NULL )as category'),
                    DB::raw('IF (p.sub_category_id IS NOT NULL , ( SELECT sub_cat.name FROM categories  AS sub_cat   WHERE p.sub_category_id=sub_cat.id
                            ),NULL )as subcategory')
                )
                ->groupBy('transaction_sell_lines.id');

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->where('t.transaction_date', '>=', $start_date)
                    ->where('t.transaction_date', '<=', $end_date);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }
            $filters = request()->only(['id']);
            if (!empty($request->input('product_id'))) {
                $query->where('p.id', $request->input('product_id'));
            }

            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                 ->editColumn('invoice_no', function ($row) {
                     return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                 })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('unit_sale_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->unit_sale_price . '</span>';
                })
                ->editColumn('sell_qty', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency sell_qty" data-currency_symbol=false data-orig-value="' . (float)$row->sell_qty . '" data-unit="' . $row->unit . '" >' . (float) $row->sell_qty . '</span> ' .$row->unit;
                })
                 ->editColumn('subtotal', function ($row) {
                     return '<span class="display_currency row_subtotal" data-currency_symbol = true data-orig-value="' . $row->subtotal . '">' . $row->subtotal . '</span>';
                 })
                ->editColumn('unit_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->unit_price . '</span>';
                })
                ->editColumn('discount_amount', '
                    @if($discount_type == "percentage")
                        {{@number_format($discount_amount)}} %
                    @elseif($discount_type == "fixed")
                        {{@number_format($discount_amount)}}
                    @endif
                    ')
                ->editColumn('tax', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>'.
                            $row->item_tax.
                       '</span>'.'<br>'.'<span class="tax" data-orig-value="'.(float)$row->item_tax.'" data-unit="'.$row->tax.'"><small>('.$row->tax.')</small></span>';
                })
                ->rawColumns(['invoice_no', 'unit_sale_price', 'subtotal', 'sell_qty', 'discount_amount', 'unit_price', 'tax'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id);
        $products = Product::forDropdown($business_id);

        return view('report.product_sell_report')
            ->with(compact('business_locations', 'customers','products'));
    }
    public function getjuulSellReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        $from =date('m-01-Y');
        $to =date('m-t-Y');
        $business_id = $request->session()->get('user.business_id');
        \DB::enableQueryLog();

            $variation_id = $request->get('variation_id', null);
            $Gorupquery = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('contacts as c', 't.contact_id', '=', 'c.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.status','!=', 'draft')
                ->whereDate('t.transaction_date','=', date('Y-m-d'))
                ->where('p.name','like','JUUL%')
                // ->where('p.name','like','JUUL PS%')
                // ->orwhere('p.name','like','JUUL CHARGER OG BOX')

                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'p.qty_box as Box_qty',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    'c.name as customer',
                    'c.contact_id',
                    'c.address_line_1 as address',
                    'c.city',
                    'c.state',
                    'c.zip_code',
                    't.id as transaction_id',
                    't.invoice_no',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.unit_price_before_discount as unit_price',
                    'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                    DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),

                    'transaction_sell_lines.line_discount_type as discount_type',
                    'transaction_sell_lines.line_discount_amount as discount_amount',
                    'transaction_sell_lines.item_tax',
                    'tax_rates.name as tax',
                    'u.short_name as unit',
                    DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
                // )->get()->groupBy('customer');
                 )->orderBy('customer', 'ASC')->get()->groupBy('customer','ASC');
        // dd(\DB::getQueryLog());
        \Log::info(DB::getQueryLog());
        //dd($Gorupquery);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id);

        return view('report.juul_sell_report')
            ->with(compact('business_locations', 'customers','Gorupquery','from','to'));
    }

     public function searchjuulSellReport(Request $request)
    {
        // $from =  $request->input('fromDate');
        // $to = $request->input('toDate');

        $startDate = $request->get('fromDate');
        $endDate = $request->get('toDate');

        $myArray = explode('-', $startDate);
        $fr1 = $myArray[0];
        $to2 = $myArray[1];
        $from = str_replace(' ', '', $fr1);
        $to = str_replace(' ', '', $to2);

        $new_fromdate = date('Y-m-d',strtotime($from));
        $new_todate = date('Y-m-d',strtotime($to));
        //dd($newformat);


        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

            $variation_id = $request->get('variation_id', null);
            $Gorupquery = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('contacts as c', 't.contact_id', '=', 'c.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                //->where('t.type','!=', 'sell_return')
                ->where('t.status','!=', 'draft')
                ->where('t.business_id', $business_id)
                ->where('p.name','like','JUUL%')
                // ->orwhere('p.name','like','JUUL CHARGER OG BOX')
                ->whereDate('t.transaction_date', '>=', $new_fromdate)
                ->whereDate('t.transaction_date', '<=', $new_todate)
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'p.qty_box as Box_qty',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    'c.name as customer',
                    'c.contact_id',
                    'c.address_line_1 as address',
                    'c.city',
                    'c.state',
                    'c.zip_code',
                    't.id as transaction_id',
                    't.invoice_no',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.unit_price_before_discount as unit_price',
                    'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                    DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),

                    'transaction_sell_lines.line_discount_type as discount_type',
                    'transaction_sell_lines.line_discount_amount as discount_amount',
                    'transaction_sell_lines.item_tax',
                    'tax_rates.name as tax',
                    'u.short_name as unit',
                    DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
                )
                // ->get()->groupBy('customer');
                 ->orderBy('customer', 'ASC')->get()->groupBy('customer','ASC');
        //dd($Gorupquery);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id);

        return view('report.juul_sell_report')
            ->with(compact('business_locations', 'customers','Gorupquery','from','to'));
    }



  public function getNonjuulSellReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        $from =date("m-d-Y");
        $to =date('m-d-Y');

        \DB::enableQueryLog();

        $business_id = $request->session()->get('user.business_id');
            $variation_id = $request->get('variation_id', null);
            $Gorupquery = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
            ->join(
                'variations as v',
                'transaction_sell_lines.variation_id',
                '=',
                'v.id'
            )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('contacts as c', 't.contact_id', '=', 'c.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                //->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
                //->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                //->where('t.type','!=', 'sell_return')
                ->where('t.status','!=', 'draft')
                ->whereDate('t.transaction_date','=', date('Y-m-d'))
                ->whereIn('p.name',array('ROGUE NICOTINE POUCHES 6MG SPEARMINT PACK OF 5','MYBLU PS 3.6% RICH TOBACCO -BOX OF 5','MYBLU DEVICE KIT GRAPHITE BLACK BOX OF 5','MYBLU DEVICE KIT MIDNIGHT BLUE BOX OF 5','MYBLU PS 4.0% TOBACCO ICE-BOX OF 5','MYBLU PS 2.4% RICH TOBACCO -BOX OF 5','ZYN NICOTINE POUCHES CHILL 3MG-PACK OF 5','ZYN NICOTINE POUCHES CHILL 6MG-PACK OF 5','ZYN NICOTINE POUCHES CINNAMON 3MG-PACK OF 5','ZYN NICOTINE POUCHES CINNAMON 6MG-PACK OF 5','ZYN NICOTINE POUCHES CITRUS 3MG-PACK OF 5','ZYN NICOTINE POUCHES CITRUS 6MG-PACK OF 5','ZYN NICOTINE POUCHES COFFEE 3MG-PACK OF 5','ZYN NICOTINE POUCHES COFFEE 6MG-PACK OF 5','ZYN NICOTINE POUCHES COOL MINT 3MG-PACK OF 5','ZYN NICOTINE POUCHES COOL MINT 6MG-PACK OF 5','ZYN NICOTINE POUCHES MENTHOL 3MG-PACK OF 5','ZYN NICOTINE POUCHES MENTHOL 6MG-PACK OF 5','ZYN NICOTINE POUCHES PEPPERMINT 3MG-PACK OF 5','ZYN NICOTINE POUCHES PEPPERMINT 6MG-PACK OF 5','ZYN NICOTINE POUCHES SMOOTH 3MG-PACK OF 5','ZYN NICOTINE POUCHES SMOOTH 6MG-PACK OF 5','ZYN NICOTINE POUCHES SPEARMINT 3MG-PACK OF 5','ZYN NICOTINE POUCHES SPEARMINT 6MG-PACK OF 5','ZYN NICOTINE POUCHES WINTERGREEN 3MG-PACK OF 5','ZYN NICOTINE POUCHES WINTERGREEN 6MG-PACK OF 5','VUSE KIT VIBE POWER UNIT - BOX OF 5','ERFL VUSE VIBE TANK ORIG 3.0% 2CART/PK','ERFL VUSE VIBE TANK MEN 3.0% 2CART/PK','VUSE CART ORIG 5MG 4.8%','VUSE CART MEN 5MG 4.8%','EACC VUSE SOLO - BOX OF 5','823999*ALTOGLDBUN*$8.49*FO','ROGUE NICOTINE POUCHES 6MG CINNAMON APPLE COMBO PACK OF 10','LOGIC POWER CARTRIDGES 27MG TOBACCO-BOX OF 10','LOGIC POWER KIT 27MG TOBACCO-BOX OF 5','LOGIC PRO CAPSULES 20MG TOBACCO-BOX OF 10','LOGIC PRO KIT CAPSULE TANK SYSTEM-BOX OF 5','BLU DISPOSABLE 2.4% CHERRY CRUSH- BOX OF 5','BLU DISPOSABLE 2.4% MAGINFICENT MENTHOL- BOX OF 5','BLU DISPOSABLE 2.4% TOBACCO CLASSIC- BOX OF 5','BLU PLUS TANK 2.4% MENTHOL-BOX OF 5','BLU PLUS TANK 2.4% TOBACCO-BOX OF 5','BLU PLUS XPRESS KIT -BOX OF 5','MYBLU INTENSE PS 2.4% TOBACCO-BOX OF 5','MYBLU INTENSE PS 2.5% TOBACCO CHILL-BOX OF 5','MYBLU INTENSE PS 3.6% TOBACCO-BOX OF 5','MYBLU INTENSE PS 4.0% TOBACCO CHILL-BOX OF 5','MYBLU PS 2.4% GOLD LEAF-BOX OF 5','MYBLU PS 2.4% MENTHOL-BOX OF 5','ROGUE NICOTINE POUCHES 3MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 3MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 3MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 3MG WINTERGREEN-PACK OF 5','ROGUE NICOTINE POUCHES 6MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 6MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 6MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 6MG WINTERGREEN-PACK OF 5','E CIG KIT VUSE ALTO BLUE','E CIG KIT VUSE ALTO GOLD','E CIG KIT VUSE ALTO RED','E CIG KIT VUSE ALTO TEAL','VUSE KIT ALTO SLATE','EKIT VUSE ALTO ROSE GOLD','EKIT VUSE ALTO SILVER','VUSE ALTO GOLDEN TOBACCO 2.4% 1CT','VUSE ALTO GOLDEN TOBACCO 2.4% 4COUNT','VUSE ALTO GOLDEN TOBACCO 5% 1CT','VUSE ALTO GOLDEN TOBACCO 5% 4COUNT','VUSE ALTO MENTHOL 2.4% 1CT','VUSE ALTO MENTHOL 2.4% 4COUNT','VUSE ALTO MENTHOL 5% 1CT','VUSE ALTO MENTHOL 5% 4COUNT','VUSE ALTO POD GOLDEN TOBACCO 1.8 0.2M','VUSE ALTO POD GOLDEN TOBACCO 2.4 0.2M','VUSE ALTO POD GOLDEN TOBACCO 5.0 0.2M','VUSE ALTO POD MENTHOL 1.8 0.2M','VUSE ALTO POD MENTHOL 2.4 0.2M','VUSE ALTO POD MENTHOL 5.0 0.2M','VUSE ALTO POD RICH TOBACCO 1.8 0.2M','VUSE ALTO POD RICH TOBACCO 2.4 0.2M','VUSE ALTO POD RICH TOBACCO 5.0 0.2M','VELO HARD BERRY LOZENGE 12 COUNT','VELO HARD CREMA LOZENGE 12 COUNT','VELO HARD DARK MINT LOZENGE 12 COUNT','VELO POUCH CITRUS 2 MG 15 COUNT','VELO POUCH MINT 2 MG 15 COUNT','VELO LARGE BLACK CHERRY POUCH 4MG 20CT','VELO CINNAMON POUCHES 4MG 20COUNT','VELO CITRUS BURST POUCHES 4MG 20COUNT','VELO LARGE POUCH CITRUS 4 MG 15 COUNT','VELO DRAGON FRUIT POUCHES 4MG 20COUNT','VELO LARGE POUCH MINT 4 MG 15 COUNT','VELO LARGE PEPPERMINT POUCH 4MG 20CT','VELO SPEARMINT POUCHES 4MG 20COUNT','VELO WINTERGREEN POUCHES 4MG 20COUNT','VELO LARGE BLACK CHERRY POUCH 7MG 20CT','VELO CINNAMON POUCHES 7MG 20COUNT','VELO CITRUS BURST POUCHES 7MG 20COUNT','VELO DRAGON FRUIT POUCHES 7MG 20COUNT','VELO LARGE PEPPERMINT POUCH 7MG 20CT','VELO SPEARMINT POUCHES 7MG 20COUNT','VELO WINTERGREEN POUCHES 7MG 20COUNT','BLU DISPOSABLE 2.4% POLAR MINT- BOX OF 5','VELO HARD MINT LOZENGE 12 COUNT','VELO POUCH COFFEE 4MG 20CT','VELO POUCH COFFEE 7MG 20CT'))
                //->whereIn('p.name',array('VUSE KIT VIBE POWER UNIT - BOX OF 5','ERFL VUSE VIBE TANK ORIG 3.0% 2CART/PK','ERFL VUSE VIBE TANK MEN 3.0% 2CART/PK','VUSE CART ORIG 5MG 4.8%','VUSE CART MEN 5MG 4.8%','EACC VUSE SOLO - BOX OF 5','823999*ALTOGLDBUN*$8.49*FO','ROGUE NICOTINE POUCHES 6MG CINNAMON APPLE COMBO PACK OF 10','LOGIC POWER CARTRIDGES 27MG TOBACCO-BOX OF 10','LOGIC POWER KIT 27MG TOBACCO-BOX OF 5','LOGIC PRO CAPSULES 20MG TOBACCO-BOX OF 10','LOGIC PRO KIT CAPSULE TANK SYSTEM-BOX OF 5','BLU DISPOSABLE 2.4% CHERRY CRUSH- BOX OF 5','BLU DISPOSABLE 2.4% MAGINFICENT MENTHOL- BOX OF 5','BLU DISPOSABLE 2.4% TOBACCO CLASSIC- BOX OF 5','BLU PLUS TANK 2.4% MENTHOL-BOX OF 5','BLU PLUS TANK 2.4% TOBACCO-BOX OF 5','BLU PLUS XPRESS KIT -BOX OF 5','MYBLU DEVICE KIT BOX OF 5','MYBLU INTENSE PS 2.4% TOBACCO-BOX OF 5','MYBLU INTENSE PS 2.5% TOBACCO CHILL-BOX OF 5','MYBLU INTENSE PS 3.6% TOBACCO-BOX OF 5','MYBLU INTENSE PS 4.0% TOBACCO CHILL-BOX OF 5','MYBLU PS 2.4% GOLD LEAF-BOX OF 5','MYBLU PS 2.4% MENTHOL-BOX OF 5','ROGUE NICOTINE POUCHES 3MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 3MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 3MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 3MG WINTERGREEN-PACK OF 5','ROGUE NICOTINE POUCHES 6MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 6MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 6MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 6MG WINTERGREEN-PACK OF 5','E CIG KIT VUSE ALTO BLUE','E CIG KIT VUSE ALTO GOLD','E CIG KIT VUSE ALTO RED','E CIG KIT VUSE ALTO TEAL','VUSE KIT ALTO SLATE','EKIT VUSE ALTO ROSE GOLD','EKIT VUSE ALTO SILVER','VUSE ALTO GOLDEN TOBACCO 2.4% 1CT','VUSE ALTO GOLDEN TOBACCO 2.4% 4COUNT','VUSE ALTO GOLDEN TOBACCO 5% 1CT','VUSE ALTO GOLDEN TOBACCO 5% 4COUNT','VUSE ALTO MENTHOL 2.4% 1CT','VUSE ALTO MENTHOL 2.4% 4COUNT','VUSE ALTO MENTHOL 5% 1CT','VUSE ALTO MENTHOL 5% 4COUNT','VUSE ALTO POD GOLDEN TOBACCO 1.8 0.2M','VUSE ALTO POD GOLDEN TOBACCO 2.4 0.2M','VUSE ALTO POD GOLDEN TOBACCO 5.0 0.2M','VUSE ALTO POD MENTHOL 1.8 0.2M','VUSE ALTO POD MENTHOL 2.4 0.2M','VUSE ALTO POD MENTHOL 5.0 0.2M','VUSE ALTO POD RICH TOBACCO 1.8 0.2M','VUSE ALTO POD RICH TOBACCO 2.4 0.2M','VUSE ALTO POD RICH TOBACCO 5.0 0.2M','VELO HARD BERRY LOZENGE 12 COUNT','VELO HARD CREMA LOZENGE 12 COUNT','VELO HARD DARK MINT LOZENGE 12 COUNT','VELO POUCH CITRUS 2 MG 15 COUNT','VELO POUCH MINT 2 MG 15 COUNT','VELO LARGE BLACK CHERRY POUCH 4MG 20CT','VELO CINNAMON POUCHES 4MG 20COUNT','VELO CITRUS BURST POUCHES 4MG 20COUNT','VELO LARGE POUCH CITRUS 4 MG 15 COUNT','VELO DRAGON FRUIT POUCHES 4MG 20COUNT','VELO LARGE POUCH MINT 4 MG 15 COUNT','VELO LARGE PEPPERMINT POUCH 4MG 20CT','VELO SPEARMINT POUCHES 4MG 20COUNT','VELO WINTERGREEN POUCHES 4MG 20COUNT','VELO LARGE BLACK CHERRY POUCH 7MG 20CT','VELO CINNAMON POUCHES 7MG 20COUNT','VELO CITRUS BURST POUCHES 7MG 20COUNT','VELO DRAGON FRUIT POUCHES 7MG 20COUNT','VELO LARGE PEPPERMINT POUCH 7MG 20CT','VELO SPEARMINT POUCHES 7MG 20COUNT','VELO WINTERGREEN POUCHES 7MG 20COUNT','BLU DISPOSABLE 2.4% POLAR MINT- BOX OF 5','VELO HARD MINT LOZENGE 12 COUNT','VELO POUCH COFFEE 4MG 20CT','VELO POUCH COFFEE 7MG 20CT'))
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'p.qty_box as Box_qty',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    'c.name as customer',
                    'c.contact_id',
                    'c.address_line_1 as address',
                    'c.city',
                    'c.state',
                    'c.zip_code',
                    't.id as transaction_id',
                    't.invoice_no',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.unit_price_before_discount as unit_price',
                    'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                    DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
                    'transaction_sell_lines.line_discount_type as discount_type',
                    'transaction_sell_lines.line_discount_amount as discount_amount',
                    'transaction_sell_lines.item_tax',
                    //'tax_rates.name as tax',
                    //'u.short_name as unit',
                    DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal'),
                )->orderBy('customer', 'ASC')->get()->groupBy('customer','ASC');
        //dd($Gorupquery);
        //   dd(\DB::getQueryLog());
        \Log::info(DB::getQueryLog());

        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id);
        return view('report.non_juul_msa_report')
            ->with(compact('business_locations', 'customers','Gorupquery','from','to'));
    }

     public function searchNonjuulSellReport(Request $request)
    {
        // $from =  $request->input('fromDate');
        // $to = $request->input('toDate');
        $startDate = $request->get('fromDate');
        // $endDate = $request->get('toDate');
        $myArray = explode('-', $startDate);
        $fr1 = $myArray[0];
        $to2 = $myArray[1];
        $from = str_replace(' ', '', $fr1);
        $to = str_replace(' ', '', $to2);
        $new_fromdate = date('Y-m-d',strtotime($from));
        $new_todate = date('Y-m-d',strtotime($to));

        //echo $new_todate; echo $new_fromdate; exit;

        //dd($new_todate);
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
            $variation_id = $request->get('variation_id', null);
            $Gorupquery = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
            ->join(
                'variations as v',
                'transaction_sell_lines.variation_id',
                '=',
                'v.id'
            )
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            //->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
            //->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            //->where('t.type','!=', 'sell_return')
            ->where('t.status','!=', 'draft')
            ->whereDate('t.transaction_date', '>=', $new_fromdate)
            ->whereDate('t.transaction_date', '<=', $new_todate)
            ->whereIn('p.name',array('ROGUE NICOTINE POUCHES 6MG SPEARMINT PACK OF 5','MYBLU PS 3.6% RICH TOBACCO -BOX OF 5','MYBLU DEVICE KIT GRAPHITE BLACK BOX OF 5','MYBLU DEVICE KIT MIDNIGHT BLUE BOX OF 5','MYBLU PS 4.0% TOBACCO ICE-BOX OF 5','MYBLU PS 2.4% RICH TOBACCO -BOX OF 5','ZYN NICOTINE POUCHES CHILL 3MG-PACK OF 5','ZYN NICOTINE POUCHES CHILL 6MG-PACK OF 5','ZYN NICOTINE POUCHES CINNAMON 3MG-PACK OF 5','ZYN NICOTINE POUCHES CINNAMON 6MG-PACK OF 5','ZYN NICOTINE POUCHES CITRUS 3MG-PACK OF 5','ZYN NICOTINE POUCHES CITRUS 6MG-PACK OF 5','ZYN NICOTINE POUCHES COFFEE 3MG-PACK OF 5','ZYN NICOTINE POUCHES COFFEE 6MG-PACK OF 5','ZYN NICOTINE POUCHES COOL MINT 3MG-PACK OF 5','ZYN NICOTINE POUCHES COOL MINT 6MG-PACK OF 5','ZYN NICOTINE POUCHES MENTHOL 3MG-PACK OF 5','ZYN NICOTINE POUCHES MENTHOL 6MG-PACK OF 5','ZYN NICOTINE POUCHES PEPPERMINT 3MG-PACK OF 5','ZYN NICOTINE POUCHES PEPPERMINT 6MG-PACK OF 5','ZYN NICOTINE POUCHES SMOOTH 3MG-PACK OF 5','ZYN NICOTINE POUCHES SMOOTH 6MG-PACK OF 5','ZYN NICOTINE POUCHES SPEARMINT 3MG-PACK OF 5','ZYN NICOTINE POUCHES SPEARMINT 6MG-PACK OF 5','ZYN NICOTINE POUCHES WINTERGREEN 3MG-PACK OF 5','ZYN NICOTINE POUCHES WINTERGREEN 6MG-PACK OF 5','VUSE KIT VIBE POWER UNIT - BOX OF 5','ERFL VUSE VIBE TANK ORIG 3.0% 2CART/PK','ERFL VUSE VIBE TANK MEN 3.0% 2CART/PK','VUSE CART ORIG 5MG 4.8%','VUSE CART MEN 5MG 4.8%','EACC VUSE SOLO - BOX OF 5','823999*ALTOGLDBUN*$8.49*FO','ROGUE NICOTINE POUCHES 6MG CINNAMON APPLE COMBO PACK OF 10','LOGIC POWER CARTRIDGES 27MG TOBACCO-BOX OF 10','LOGIC POWER KIT 27MG TOBACCO-BOX OF 5','LOGIC PRO CAPSULES 20MG TOBACCO-BOX OF 10','LOGIC PRO KIT CAPSULE TANK SYSTEM-BOX OF 5','BLU DISPOSABLE 2.4% CHERRY CRUSH- BOX OF 5','BLU DISPOSABLE 2.4% MAGINFICENT MENTHOL- BOX OF 5','BLU DISPOSABLE 2.4% TOBACCO CLASSIC- BOX OF 5','BLU PLUS TANK 2.4% MENTHOL-BOX OF 5','BLU PLUS TANK 2.4% TOBACCO-BOX OF 5','BLU PLUS XPRESS KIT -BOX OF 5','MYBLU INTENSE PS 2.4% TOBACCO-BOX OF 5','MYBLU INTENSE PS 2.5% TOBACCO CHILL-BOX OF 5','MYBLU INTENSE PS 3.6% TOBACCO-BOX OF 5','MYBLU INTENSE PS 4.0% TOBACCO CHILL-BOX OF 5','MYBLU PS 2.4% GOLD LEAF-BOX OF 5','MYBLU PS 2.4% MENTHOL-BOX OF 5','ROGUE NICOTINE POUCHES 3MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 3MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 3MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 3MG WINTERGREEN-PACK OF 5','ROGUE NICOTINE POUCHES 6MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 6MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 6MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 6MG WINTERGREEN-PACK OF 5','E CIG KIT VUSE ALTO BLUE','E CIG KIT VUSE ALTO GOLD','E CIG KIT VUSE ALTO RED','E CIG KIT VUSE ALTO TEAL','VUSE KIT ALTO SLATE','EKIT VUSE ALTO ROSE GOLD','EKIT VUSE ALTO SILVER','VUSE ALTO GOLDEN TOBACCO 2.4% 1CT','VUSE ALTO GOLDEN TOBACCO 2.4% 4COUNT','VUSE ALTO GOLDEN TOBACCO 5% 1CT','VUSE ALTO GOLDEN TOBACCO 5% 4COUNT','VUSE ALTO MENTHOL 2.4% 1CT','VUSE ALTO MENTHOL 2.4% 4COUNT','VUSE ALTO MENTHOL 5% 1CT','VUSE ALTO MENTHOL 5% 4COUNT','VUSE ALTO POD GOLDEN TOBACCO 1.8 0.2M','VUSE ALTO POD GOLDEN TOBACCO 2.4 0.2M','VUSE ALTO POD GOLDEN TOBACCO 5.0 0.2M','VUSE ALTO POD MENTHOL 1.8 0.2M','VUSE ALTO POD MENTHOL 2.4 0.2M','VUSE ALTO POD MENTHOL 5.0 0.2M','VUSE ALTO POD RICH TOBACCO 1.8 0.2M','VUSE ALTO POD RICH TOBACCO 2.4 0.2M','VUSE ALTO POD RICH TOBACCO 5.0 0.2M','VELO HARD BERRY LOZENGE 12 COUNT','VELO HARD CREMA LOZENGE 12 COUNT','VELO HARD DARK MINT LOZENGE 12 COUNT','VELO POUCH CITRUS 2 MG 15 COUNT','VELO POUCH MINT 2 MG 15 COUNT','VELO LARGE BLACK CHERRY POUCH 4MG 20CT','VELO CINNAMON POUCHES 4MG 20COUNT','VELO CITRUS BURST POUCHES 4MG 20COUNT','VELO LARGE POUCH CITRUS 4 MG 15 COUNT','VELO DRAGON FRUIT POUCHES 4MG 20COUNT','VELO LARGE POUCH MINT 4 MG 15 COUNT','VELO LARGE PEPPERMINT POUCH 4MG 20CT','VELO SPEARMINT POUCHES 4MG 20COUNT','VELO WINTERGREEN POUCHES 4MG 20COUNT','VELO LARGE BLACK CHERRY POUCH 7MG 20CT','VELO CINNAMON POUCHES 7MG 20COUNT','VELO CITRUS BURST POUCHES 7MG 20COUNT','VELO DRAGON FRUIT POUCHES 7MG 20COUNT','VELO LARGE PEPPERMINT POUCH 7MG 20CT','VELO SPEARMINT POUCHES 7MG 20COUNT','VELO WINTERGREEN POUCHES 7MG 20COUNT','BLU DISPOSABLE 2.4% POLAR MINT- BOX OF 5','VELO HARD MINT LOZENGE 12 COUNT','VELO POUCH COFFEE 4MG 20CT','VELO POUCH COFFEE 7MG 20CT'))
            //->whereIn('p.name',array('VUSE KIT VIBE POWER UNIT - BOX OF 5','ERFL VUSE VIBE TANK ORIG 3.0% 2CART/PK','ERFL VUSE VIBE TANK MEN 3.0% 2CART/PK','VUSE CART ORIG 5MG 4.8%','VUSE CART MEN 5MG 4.8%','EACC VUSE SOLO - BOX OF 5','823999*ALTOGLDBUN*$8.49*FO','ROGUE NICOTINE POUCHES 6MG CINNAMON APPLE COMBO PACK OF 10','LOGIC POWER CARTRIDGES 27MG TOBACCO-BOX OF 10','LOGIC POWER KIT 27MG TOBACCO-BOX OF 5','LOGIC PRO CAPSULES 20MG TOBACCO-BOX OF 10','LOGIC PRO KIT CAPSULE TANK SYSTEM-BOX OF 5','BLU DISPOSABLE 2.4% CHERRY CRUSH- BOX OF 5','BLU DISPOSABLE 2.4% MAGINFICENT MENTHOL- BOX OF 5','BLU DISPOSABLE 2.4% TOBACCO CLASSIC- BOX OF 5','BLU PLUS TANK 2.4% MENTHOL-BOX OF 5','BLU PLUS TANK 2.4% TOBACCO-BOX OF 5','BLU PLUS XPRESS KIT -BOX OF 5','MYBLU DEVICE KIT BOX OF 5','MYBLU INTENSE PS 2.4% TOBACCO-BOX OF 5','MYBLU INTENSE PS 2.5% TOBACCO CHILL-BOX OF 5','MYBLU INTENSE PS 3.6% TOBACCO-BOX OF 5','MYBLU INTENSE PS 4.0% TOBACCO CHILL-BOX OF 5','MYBLU PS 2.4% GOLD LEAF-BOX OF 5','MYBLU PS 2.4% MENTHOL-BOX OF 5','ROGUE NICOTINE POUCHES 3MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 3MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 3MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 3MG WINTERGREEN-PACK OF 5','ROGUE NICOTINE POUCHES 6MG HONEY LEMON-PACK OF 5','ROGUE NICOTINE POUCHES 6MG MANGO-PACK OF 5','ROGUE NICOTINE POUCHES 6MG PEPPERMINT-PACK OF 5','ROGUE NICOTINE POUCHES 6MG WINTERGREEN-PACK OF 5','E CIG KIT VUSE ALTO BLUE','E CIG KIT VUSE ALTO GOLD','E CIG KIT VUSE ALTO RED','E CIG KIT VUSE ALTO TEAL','VUSE KIT ALTO SLATE','EKIT VUSE ALTO ROSE GOLD','EKIT VUSE ALTO SILVER','VUSE ALTO GOLDEN TOBACCO 2.4% 1CT','VUSE ALTO GOLDEN TOBACCO 2.4% 4COUNT','VUSE ALTO GOLDEN TOBACCO 5% 1CT','VUSE ALTO GOLDEN TOBACCO 5% 4COUNT','VUSE ALTO MENTHOL 2.4% 1CT','VUSE ALTO MENTHOL 2.4% 4COUNT','VUSE ALTO MENTHOL 5% 1CT','VUSE ALTO MENTHOL 5% 4COUNT','VUSE ALTO POD GOLDEN TOBACCO 1.8 0.2M','VUSE ALTO POD GOLDEN TOBACCO 2.4 0.2M','VUSE ALTO POD GOLDEN TOBACCO 5.0 0.2M','VUSE ALTO POD MENTHOL 1.8 0.2M','VUSE ALTO POD MENTHOL 2.4 0.2M','VUSE ALTO POD MENTHOL 5.0 0.2M','VUSE ALTO POD RICH TOBACCO 1.8 0.2M','VUSE ALTO POD RICH TOBACCO 2.4 0.2M','VUSE ALTO POD RICH TOBACCO 5.0 0.2M','VELO HARD BERRY LOZENGE 12 COUNT','VELO HARD CREMA LOZENGE 12 COUNT','VELO HARD DARK MINT LOZENGE 12 COUNT','VELO POUCH CITRUS 2 MG 15 COUNT','VELO POUCH MINT 2 MG 15 COUNT','VELO LARGE BLACK CHERRY POUCH 4MG 20CT','VELO CINNAMON POUCHES 4MG 20COUNT','VELO CITRUS BURST POUCHES 4MG 20COUNT','VELO LARGE POUCH CITRUS 4 MG 15 COUNT','VELO DRAGON FRUIT POUCHES 4MG 20COUNT','VELO LARGE POUCH MINT 4 MG 15 COUNT','VELO LARGE PEPPERMINT POUCH 4MG 20CT','VELO SPEARMINT POUCHES 4MG 20COUNT','VELO WINTERGREEN POUCHES 4MG 20COUNT','VELO LARGE BLACK CHERRY POUCH 7MG 20CT','VELO CINNAMON POUCHES 7MG 20COUNT','VELO CITRUS BURST POUCHES 7MG 20COUNT','VELO DRAGON FRUIT POUCHES 7MG 20COUNT','VELO LARGE PEPPERMINT POUCH 7MG 20CT','VELO SPEARMINT POUCHES 7MG 20COUNT','VELO WINTERGREEN POUCHES 7MG 20COUNT','BLU DISPOSABLE 2.4% POLAR MINT- BOX OF 5','VELO HARD MINT LOZENGE 12 COUNT','VELO POUCH COFFEE 4MG 20CT','VELO POUCH COFFEE 7MG 20CT'))
                ->select(
                'p.name as product_name',
                'p.type as product_type',
                'p.qty_box as Box_qty',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                'c.name as customer',
                'c.contact_id',
                'c.address_line_1 as address',
                'c.city',
                'c.state',
                'c.zip_code',
                't.id as transaction_id',
                't.invoice_no',
                't.transaction_date as transaction_date',
                'transaction_sell_lines.unit_price_before_discount as unit_price',
                'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),

                'transaction_sell_lines.line_discount_type as discount_type',
                'transaction_sell_lines.line_discount_amount as discount_amount',
                'transaction_sell_lines.item_tax',
                //'tax_rates.name as tax',
                //'u.short_name as unit',
                DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal'),
            )
            ->orderBy('customer', 'ASC')->get()->groupBy('customer','ASC');

        //dd($Gorupquery);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id);
        //dd($Gorupquery);
        return view('report.non_juul_msa_report')
            ->with(compact('business_locations', 'customers','Gorupquery','from','to'));
    }
    /**
     * Shows product purchase report with purchase details
     *
     * @return \Illuminate\Http\Response
     */
    public function getproductSellReportWithPurchase(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'transaction_sell_lines_purchase_lines as tspl',
                    'transaction_sell_lines.id',
                    '=',
                    'tspl.sell_line_id'
                )
                ->join(
                    'purchase_lines as pl',
                    'tspl.purchase_line_id',
                    '=',
                    'pl.id'
                )
                ->join(
                    'transactions as purchase',
                    'pl.transaction_id',
                    '=',
                    'purchase.id'
                )
                ->leftjoin('contacts as supplier', 'purchase.contact_id', '=', 'supplier.id')
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    'c.name as customer',
                    't.id as transaction_id',
                    't.invoice_no',
                    't.transaction_date as transaction_date',
                    'tspl.quantity as purchase_quantity',
                    'u.short_name as unit',
                    'supplier.name as supplier_name',
                    'purchase.ref_no as ref_no',
                    'purchase.type as purchase_type',
                    'pl.lot_number'
                );

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->where('t.transaction_date', '>=', $start_date)
                    ->where('t.transaction_date', '<=', $end_date);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }
            $filters = request()->only(['id']);
            if (!empty($request->input('product_id'))) {
                $query->where('p.id', $request->input('product_id'));
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                 ->editColumn('invoice_no', function ($row) {
                     return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                 })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('unit_sale_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->unit_sale_price . '</span>';
                })
                ->editColumn('purchase_quantity', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency purchase_quantity" data-currency_symbol=false data-orig-value="' . (float)$row->purchase_quantity . '" data-unit="' . $row->unit . '" >' . (float) $row->purchase_quantity . '</span> ' .$row->unit;
                })
                ->editColumn('ref_no', '
                    @if($purchase_type == "opening_stock")
                        <i><small class="help-block">(@lang("lang_v1.opening_stock"))</small></i>
                    @else
                        {{$ref_no}}
                    @endif
                    ')
                ->rawColumns(['invoice_no', 'purchase_quantity', 'ref_no'])
                ->make(true);
        }
    }

    /**
     * Shows product lot report
     *
     * @return \Illuminate\Http\Response
     */
    public function getLotReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $query = Product::where('products.business_id', $business_id)
                    ->leftjoin('units', 'products.unit_id', '=', 'units.id')
                    ->join('variations as v', 'products.id', '=', 'v.product_id')
                    ->join('purchase_lines as pl', 'v.id', '=', 'pl.variation_id')
                    ->leftjoin(
                        'transaction_sell_lines_purchase_lines as tspl',
                        'pl.id',
                        '=',
                        'tspl.purchase_line_id'
                    )
                    ->join('transactions as t', 'pl.transaction_id', '=', 't.id');

            $permitted_locations = auth()->user()->permitted_locations();
            $location_filter = 'WHERE ';

            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);

                $locations_imploded = implode(', ', $permitted_locations);
                $location_filter = " LEFT JOIN transactions as t2 on pls.transaction_id=t2.id WHERE t2.location_id IN ($locations_imploded) AND ";
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');
                $query->where('t.location_id', $location_id)
                    //If filter by location then hide products not available in that location
                    ->ForLocation($location_id);

                $location_filter = "LEFT JOIN transactions as t2 on pls.transaction_id=t2.id WHERE t2.location_id=$location_id AND ";
            }

            if (!empty($request->input('category_id'))) {
                $query->where('products.category_id', $request->input('category_id'));
            }

            if (!empty($request->input('sub_category_id'))) {
                $query->where('products.sub_category_id', $request->input('sub_category_id'));
            }

            if (!empty($request->input('brand_id'))) {
                $query->where('products.brand_id', $request->input('brand_id'));
            }

            if (!empty($request->input('unit_id'))) {
                $query->where('products.unit_id', $request->input('unit_id'));
            }

            $only_mfg_products = request()->get('only_mfg_products', 0);
            if (!empty($only_mfg_products)) {
                $query->where('t.type', 'production_purchase');
            }

            $products = $query->select(
                'products.name as product',
                'v.name as variation_name',
                'sub_sku',
                'pl.lot_number',
                'pl.exp_date as exp_date',
                DB::raw("( COALESCE((SELECT SUM(quantity - quantity_returned) from purchase_lines as pls $location_filter variation_id = v.id AND lot_number = pl.lot_number), 0) -
                    SUM(COALESCE((tspl.quantity - tspl.qty_returned), 0))) as stock"),
                // DB::raw("(SELECT SUM(IF(transactions.type='sell', TSL.quantity, -1* TPL.quantity) ) FROM transactions
                //         LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id

                //         LEFT JOIN purchase_lines AS TPL ON transactions.id=TPL.transaction_id

                //         WHERE transactions.status='final' AND transactions.type IN ('sell', 'sell_return') $location_filter
                //         AND (TSL.product_id=products.id OR TPL.product_id=products.id)) as total_sold"),

                DB::raw("COALESCE(SUM(IF(tspl.sell_line_id IS NULL, 0, (tspl.quantity - tspl.qty_returned)) ), 0) as total_sold"),
                DB::raw("COALESCE(SUM(IF(tspl.stock_adjustment_line_id IS NULL, 0, tspl.quantity ) ), 0) as total_adjusted"),
                'products.type',
                'units.short_name as unit'
            )
            ->whereNotNull('pl.lot_number')
            ->groupBy('v.id')
            ->groupBy('pl.lot_number');

            return Datatables::of($products)
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0 ;
                    return '<span data-is_quantity="true" class="display_currency total_stock" data-currency_symbol=false data-orig-value="' . (float)$stock . '" data-unit="' . $row->unit . '" >' . (float)$stock . '</span> ' . $row->unit;
                })
                ->editColumn('product', function ($row) {
                    if ($row->variation_name != 'DUMMY') {
                        return $row->product . ' (' . $row->variation_name . ')';
                    } else {
                        return $row->product;
                    }
                })
                ->editColumn('total_sold', function ($row) {
                    if ($row->total_sold) {
                        return '<span data-is_quantity="true" class="display_currency total_sold" data-currency_symbol=false data-orig-value="' . (float)$row->total_sold . '" data-unit="' . $row->unit . '" >' . (float)$row->total_sold . '</span> ' . $row->unit;
                    } else {
                        return '0' . ' ' . $row->unit;
                    }
                })
                ->editColumn('total_adjusted', function ($row) {
                    if ($row->total_adjusted) {
                        return '<span data-is_quantity="true" class="display_currency total_adjusted" data-currency_symbol=false data-orig-value="' . (float)$row->total_adjusted . '" data-unit="' . $row->unit . '" >' . (float)$row->total_adjusted . '</span> ' . $row->unit;
                    } else {
                        return '0' . ' ' . $row->unit;
                    }
                })
                ->editColumn('exp_date', function ($row) {
                    if (!empty($row->exp_date)) {
                        $carbon_exp = \Carbon::createFromFormat('Y-m-d', $row->exp_date);
                        $carbon_now = \Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) >= 0) {
                            return $this->productUtil->format_date($row->exp_date) . '<br><small>( <span class="time-to-now">' . $row->exp_date . '</span> )</small>';
                        } else {
                            return $this->productUtil->format_date($row->exp_date) . ' &nbsp; <span class="label label-danger no-print">' . __('report.expired') . '</span><span class="print_section">' . __('report.expired') . '</span><br><small>( <span class="time-from-now">' . $row->exp_date . '</span> )</small>';
                        }
                    } else {
                        return '--';
                    }
                })
                ->removeColumn('unit')
                ->removeColumn('id')
                ->removeColumn('variation_name')
                ->rawColumns(['exp_date', 'stock', 'total_sold', 'total_adjusted'])
                ->make(true);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.lot_report')
            ->with(compact('categories', 'brands', 'units', 'business_locations'));
    }

    /**
     * Shows purchase payment report
     *
     * @return \Illuminate\Http\Response
     */
    public function purchasePaymentReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $search =  $request->get('search');
            if($search == 'credit memo' || $search == 'Credit Memo' || $search == 'CREDIT MEMO'){
                $search = 'credit_memo';
            }elseif($search == 'bank transfer' || $search == 'Bank Transfer' || $search == 'BANK TRANSFER'){
                $search = 'bank_transfer';
            }elseif($search == 'ach' || $search == 'Ach' || $search == 'ACH'){
                $search = 'custom_pay_1';
            }elseif($search == 'wire' || $search == 'Wire' || $search == 'WIRE'){
                $search = 'custom_pay_2';
            }elseif($search == 'zelle' || $search == 'Zelle' || $search == 'ZELLE'){
                $search = 'custom_pay_3';
            }elseif($search == 'venmo' || $search == 'Venmo' || $search == 'VENMO'){
                $search = 'custom_pay_4';
            }elseif($search == 'cash app' || $search == 'Cash App' || $search == 'CASH APP'){
                $search = 'custom_pay_5';
            }elseif($search == 'bank deposit' || $search == 'Bank Deposit' || $search == 'BANK DEPOSIT'){
                $search = 'custom_pay_6';
            }elseif($search == 'bank mobile deposit' || $search == 'Bank Mobile Deposit' || $search == 'BANK MOBILE DEPOSIT'){
                $search = 'custom_pay_7';
            }elseif($search == 'other' || $search == 'Other' || $search == 'OTHER'){
                $search = 'other';
            }

            $supplier_id = $request->get('supplier_id', null);
            $contact_filter1 = !empty($supplier_id) ? "AND t.contact_id=$supplier_id" : '';
            $contact_filter2 = !empty($supplier_id) ? "AND transactions.contact_id=$supplier_id" : '';

            $location_id = $request->get('location_id', null);

            $parent_payment_query_part = empty($location_id) ? "AND transaction_payments.parent_id IS NULL" : "";

            $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
                $join->on('transaction_payments.transaction_id', '=', 't.id')
                    ->where('t.business_id', $business_id)
                    ->whereIn('t.type', ['purchase', 'opening_balance']);
            })
                ->where('transaction_payments.business_id', $business_id)
                ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
                    $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('purchase', 'opening_balance')  $parent_payment_query_part $contact_filter1)")
                        ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('purchase', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
                })
                ->leftjoin('contacts', 't.contact_id', '=', 'contacts.id')
                ->select(
                    DB::raw("IF(transaction_payments.transaction_id IS NULL,
                                (SELECT c.name FROM transactions as ts
                                JOIN contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=(
                                        SELECT tps.transaction_id FROM transaction_payments as tps
                                        WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                    )
                                ),
                                (SELECT c.name FROM transactions as ts JOIN
                                    contacts as c ON ts.contact_id=c.id
                                    WHERE ts.id=t.id
                                )
                            ) as supplier"),
                    'contacts.name',
                    'transaction_payments.amount',
                    'method',
                    'paid_on',
                    'transaction_payments.payment_ref_no',
                    'transaction_payments.document',
                    't.ref_no',
                    't.id as transaction_id',
                    'cheque_number',
                    'card_transaction_number',
                    'bank_account_number',
                    'transaction_no',
                    'transaction_payments.id as DT_RowId'
                ) ;

                if(!empty($search)){
                    $query->where(function ($query1) use ($search) {
                        $query1->where('cheque_number', 'like', '%'. $search .'%')
                            ->orWhere('method', 'like', '%'. $search .'%')
                            ->orWhere('card_transaction_number', 'like', '%'. $search .'%')
                            ->orWhere('bank_account_number', 'like', '%'. $search .'%')
                            ->orWhere('transaction_no', 'like', '%'. $search .'%')
                            ->orWhere('payment_ref_no', 'like', '%'. $search .'%')
                            ->orWhere('amount', 'like', '%'. $search .'%')
                            ->orWhere('t.ref_no', 'like', '%'. $search .'%')
                            ->orWhere('contacts.name', 'like', '%'. $search .'%')
                            ;
                    });
                }
                $query->groupBy('transaction_payments.id');

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $payment_types = $this->transactionUtil->payment_types(null, true);
            if (!empty($request->get('payment_types'))) {
                $query->where('transaction_payments.method', $request->get('payment_types'));
            }

            return Datatables::of($query)
                 ->editColumn('ref_no', function ($row) {
                     if (!empty($row->ref_no)) {
                         return '<a data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->ref_no . '</a>';
                     } else {
                         return '';
                     }
                 })

                ->editColumn('paid_on', '{{@format_datetime($paid_on)}}')
                ->editColumn('method', function ($row) use ($payment_types) {
                    $method = !empty($payment_types[$row->method]) ? $payment_types[$row->method] : '';
                    if ($row->method == 'cheque') {
                        $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    }
                    return $method;
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency paid-amount" data-currency_symbol = true data-orig-value="' . $row->amount . '">' . $row->amount . '</span>';
                })

                ->addColumn('action', '<button type="button" class="btn btn-primary btn-xs view_payment" data-href="{{ action("TransactionPaymentController@viewPayment", [$DT_RowId]) }}">@lang("messages.view")
                    </button> @if(!empty($document))<a href="{{asset("/uploads/documents/" . $document)}}" class="btn btn-success btn-xs" download=""><i class="fa fa-download"></i> @lang("purchase.download_document")</a>@endif')

                ->rawColumns(['ref_no', 'amount', 'method', 'action'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $payment_types = $this->transactionUtil->payment_types(null, true);

        return view('report.purchase_payment_report')
            ->with(compact('business_locations', 'suppliers','payment_types'));
    }

    /**
     * Shows sell payment report
     *
     * @return \Illuminate\Http\Response
     */
    public function sellPaymentReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $search =  $request->get('search');
            if($search == 'credit memo' || $search == 'Credit Memo' || $search == 'CREDIT MEMO'){
                $search = 'credit_memo';
            }elseif($search == 'bank transfer' || $search == 'Bank Transfer' || $search == 'BANK TRANSFER'){
                $search = 'bank_transfer';
            }elseif($search == 'ach' || $search == 'Ach' || $search == 'ACH'){
                $search = 'custom_pay_1';
            }elseif($search == 'wire' || $search == 'Wire' || $search == 'WIRE'){
                $search = 'custom_pay_2';
            }elseif($search == 'zelle' || $search == 'Zelle' || $search == 'ZELLE'){
                $search = 'custom_pay_3';
            }elseif($search == 'venmo' || $search == 'Venmo' || $search == 'VENMO'){
                $search = 'custom_pay_4';
            }elseif($search == 'cash app' || $search == 'Cash App' || $search == 'CASH APP'){
                $search = 'custom_pay_5';
            }elseif($search == 'bank deposit' || $search == 'Bank Deposit' || $search == 'BANK DEPOSIT'){
                $search = 'custom_pay_6';
            }elseif($search == 'bank mobile deposit' || $search == 'Bank Mobile Deposit' || $search == 'BANK MOBILE DEPOSIT'){
                $search = 'custom_pay_7';
            }elseif($search == 'other' || $search == 'Other' || $search == 'OTHER'){
                $search = 'other';
            }


            $customer_id = $request->get('supplier_id', null);
            $contact_filter1 = !empty($customer_id) ? "AND t.contact_id=$customer_id" : '';
            $contact_filter2 = !empty($customer_id) ? "AND transactions.contact_id=$customer_id" : '';

            $location_id = $request->get('location_id', null);
            $parent_payment_query_part = empty($location_id) ? "AND transaction_payments.parent_id IS NULL" : "";

            $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
                $join->on('transaction_payments.transaction_id', '=', 't.id')
                    ->where('t.business_id', $business_id)
                    ->whereIn('t.type', ['sell', 'opening_balance']);
            })
            // ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftjoin('contacts as c', 'transaction_payments.payment_for', '=', 'c.id')
                ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
                ->where('transaction_payments.business_id', $business_id)
                ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
                    $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('sell', 'opening_balance') $parent_payment_query_part $contact_filter1)")
                        ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('sell', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
                })
                ->select(
                    DB::raw("IF(transaction_payments.transaction_id IS NULL,
                                (SELECT c.name FROM transactions as ts
                                JOIN contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=(
                                        SELECT tps.transaction_id FROM transaction_payments as tps
                                        WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                    )
                                ),
                                (SELECT c.name FROM transactions as ts JOIN
                                    contacts as c ON ts.contact_id=c.id
                                    WHERE ts.id=t.id
                                )
                            ) as customer"),
                    DB::raw("IF(transaction_payments.transaction_id IS NULL,
                                (SELECT c.supplier_business_name FROM transactions as ts
                                JOIN contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=(
                                        SELECT tps.transaction_id FROM transaction_payments as tps
                                        WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                    )
                                ),
                                (SELECT c.supplier_business_name FROM transactions as ts JOIN
                                    contacts as c ON ts.contact_id=c.id
                                    WHERE ts.id=t.id
                                )
                            ) as customer_comp"),
                    'transaction_payments.amount',
                    'transaction_payments.cash_note',
                    'transaction_payments.is_return',
                    'method',
                    'paid_on',
                    'transaction_payments.payment_ref_no',
                    'transaction_payments.document',
                    'transaction_payments.transaction_no',
                    't.invoice_no',
                    't.id as transaction_id',
                    'cheque_number',
                    'card_transaction_number',
                    'bank_account_number',
                    'transaction_payments.id as DT_RowId',
                    'CG.name as customer_group'
                );




            if(!empty($search)){
                // $search = str_replace(' ','%',$search);
                $query->where(function ($query1) use ($search) {
                    $query1->Where('cheque_number', 'like', '%'. $search .'%')
                        ->orWhere('card_transaction_number', 'like', '%'. $search .'%')
                        ->orWhere('bank_account_number', 'like', '%'. $search .'%')
                        ->orWhere('transaction_no', 'like', '%'. $search .'%')
                        ->orWhere('transaction_payments.payment_ref_no', 'like', '%'. $search .'%')
                        ->orWhere('transaction_payments.amount', 'like', '%'. $search .'%')
                        ->orWhere('t.invoice_no', 'like', '%'. $search .'%')
                        ->orWhere('transaction_payments.method', 'like', '%'. $search .'%')
                        ->orWhere('c.name', 'like', '%'. $search .'%')
                        ->orWhere('c.supplier_business_name', 'like', "%{$search}%");
                });
            }
            $query->groupBy('transaction_payments.id');

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->get('customer_group_id'))) {
                $query->where('CG.id', $request->get('customer_group_id'));
            }

            if (empty($request->get('is_return'))) {
                $query->where('transaction_payments.is_return', 0);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            if (!empty($request->get('payment_types'))) {
                $query->where('transaction_payments.method', $request->get('payment_types'));
            }
            $payment_types = $this->transactionUtil->payment_types(null, true);

            return Datatables::of($query)
                 ->editColumn('invoice_no', function ($row) {
                     if (!empty($row->transaction_id)) {
                         return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                     } else {
                         return '';
                     }
                 })
                ->editColumn('paid_on', '{{@format_datetime($paid_on)}}')
                ->editColumn('method', function ($row) use ($payment_types) {
                    $method = !empty($payment_types[$row->method]) ? $payment_types[$row->method] : '';
                    if ($row->method == 'cheque') {
                        $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'cash' ) {
                        $method .= '<br>(' . __('Cash Amount') . ': ' . $row->cash_note . ')';
                    } elseif (in_array('custom',explode('_',$row->method)) && isset($row->transaction_no)) {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    }
                    if ($row->is_return == 1) {
                        $method .= '<br><small>(' . __('lang_v1.change_return') . ')</small>';
                    }
                    return $method;
                })
                ->editColumn('amount', function ($row) {
                    $amount = $row->is_return == 1 ? -1 * $row->amount : $row->amount;
                    return '<span class="display_currency paid-amount" data-orig-value="' . $amount . '" data-currency_symbol = true>' . $amount . '</span>';
                })
                ->addColumn('action', '<button type="button" class="btn btn-primary btn-xs view_payment" data-href="{{ action("TransactionPaymentController@viewPayment", [$DT_RowId]) }}">@lang("messages.view")
                    </button> @if(!empty($document))<a href="{{asset("/uploads/documents/" . $document)}}" class="btn btn-success btn-xs" download=""><i class="fa fa-download"></i> @lang("purchase.download_document")</a>@endif')
                ->rawColumns(['invoice_no', 'amount', 'method', 'action'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        // $customers = Contact::customersDropdown($business_id, false);
        $customers = Contact::customersCompanyDropdown($business_id, false);
        $payment_types = $this->transactionUtil->payment_types(null, true);
        $customer_groups = CustomerGroup::forDropdown($business_id, false, true);

        return view('report.sell_payment_report')
            ->with(compact('business_locations', 'customers', 'payment_types', 'customer_groups'));
    }


    /**
     * Shows tables report
     *
     * @return \Illuminate\Http\Response
     */
    public function getTableReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = ResTable::leftjoin('transactions AS T', 'T.res_table_id', '=', 'res_tables.id')
                        ->where('T.business_id', $business_id)
                        ->where('T.type', 'sell')
                        ->where('T.status', 'final')
                        ->groupBy('res_tables.id')
                        ->select(DB::raw("SUM(final_total) as total_sell"), 'res_tables.name as table');

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('T.location_id', $location_id);
            }

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            return Datatables::of($query)
                ->editColumn('total_sell', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->total_sell . '</span>';
                })
                ->rawColumns(['total_sell'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.table_report')
            ->with(compact('business_locations'));
    }

    /**
     * Shows service staff report
     *
     * @return \Illuminate\Http\Response
     */
    public function getServiceStaffReport(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $waiters = $this->transactionUtil->serviceStaffDropdown($business_id);

        return view('report.service_staff_report')
            ->with(compact('business_locations', 'waiters'));
    }

    /**
     * Shows product sell report grouped by date
     *
     * @return \Illuminate\Http\Response
     */
    public function getproductSellGroupedReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);

        $vld_str = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
        }

        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->select(
                    'p.name as product_name',
                    'p.enable_stock',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    't.id as transaction_id',
                    't.transaction_date as transaction_date',
                    DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m-%d") as formated_date'),
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id $vld_str) as current_stock"),
                    DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as total_qty_sold'),
                    'u.short_name as unit',
                    DB::raw('SUM((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
                )
                ->groupBy('v.id')
                ->groupBy('formated_date');

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->where('t.transaction_date', '>=', $start_date)
                    ->where('t.transaction_date', '<=', $end_date);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }

            $filters = request()->only(['id']);
            if (!empty($request->input('product_id'))) {
                $query->where('p.id', $request->input('product_id'));
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                ->editColumn('transaction_date', '{{@format_date($formated_date)}}')
                ->editColumn('total_qty_sold', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency sell_qty" data-currency_symbol=false data-orig-value="' . (float)$row->total_qty_sold . '" data-unit="' . $row->unit . '" >' . (float) $row->total_qty_sold . '</span> ' .$row->unit;
                })
                ->editColumn('current_stock', function ($row) {
                    if ($row->enable_stock) {
                        return '<span data-is_quantity="true" class="display_currency current_stock" data-currency_symbol=false data-orig-value="' . (float)$row->current_stock . '" data-unit="' . $row->unit . '" >' . (float) $row->current_stock . '</span> ' .$row->unit;
                    } else {
                        return '';
                    }
                })
                 ->editColumn('subtotal', function ($row) {
                     return '<span class="display_currency row_subtotal" data-currency_symbol = true data-orig-value="' . $row->subtotal . '">' . $row->subtotal . '</span>';
                 })

                ->rawColumns(['current_stock', 'subtotal', 'total_qty_sold'])
                ->make(true);
        }
    }

    /**
     * Shows product stock details and allows to adjust mismatch
     *
     * @return \Illuminate\Http\Response
     */
    public function productStockDetails()
    {
        if (!auth()->user()->can('report.stock_details')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $stock_details = [];
        $location = null;
        $total_stock_calculated = 0;
        if (!empty(request()->input('location_id'))) {
            $variation_id = request()->get('variation_id', null);
            $location_id = request()->input('location_id');

            $location = BusinessLocation::where('business_id', $business_id)
                                        ->where('id', $location_id)
                                        ->first();

            $query = Variation::leftjoin('products as p', 'p.id', '=', 'variations.product_id')
                    ->leftjoin('units', 'p.unit_id', '=', 'units.id')
                    ->leftjoin('variation_location_details as vld', 'variations.id', '=', 'vld.variation_id')
                    ->leftjoin('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
                    ->where('p.business_id', $business_id)
                    ->where('vld.location_id', $location_id);
            if (!is_null($variation_id)) {
                $query->where('variations.id', $variation_id);
            }

            $stock_details = $query->select(
                DB::raw("(SELECT SUM(COALESCE(TSL.quantity, 0)) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='sell' AND transactions.location_id=$location_id
                        AND TSL.variation_id=variations.id) as total_sold"),
                DB::raw("(SELECT SUM(COALESCE(TSL.quantity_returned, 0)) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='sell' AND transactions.location_id=$location_id
                        AND TSL.variation_id=variations.id) as total_sell_return"),
                DB::raw("(SELECT SUM(COALESCE(TSL.quantity,0)) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='sell_transfer' AND transactions.location_id=$location_id
                        AND TSL.variation_id=variations.id) as total_sell_transfered"),
                DB::raw("(SELECT SUM(COALESCE(PL.quantity,0)) FROM transactions
                        LEFT JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='purchase_transfer' AND transactions.location_id=$location_id
                        AND PL.variation_id=variations.id) as total_purchase_transfered"),
                DB::raw("(SELECT SUM(COALESCE(SAL.quantity, 0)) FROM transactions
                        LEFT JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                        WHERE transactions.type='stock_adjustment' AND transactions.location_id=$location_id
                        AND SAL.variation_id=variations.id) as total_adjusted"),
                DB::raw("(SELECT SUM(COALESCE(PL.quantity, 0)) FROM transactions
                        LEFT JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='purchase' AND transactions.location_id=$location_id
                        AND PL.variation_id=variations.id) as total_purchased"),
                DB::raw("(SELECT SUM(COALESCE(PL.quantity_returned, 0)) FROM transactions
                        LEFT JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='purchase' AND transactions.location_id=$location_id
                        AND PL.variation_id=variations.id) as total_purchase_return"),
                DB::raw("(SELECT SUM(COALESCE(PL.quantity, 0)) FROM transactions
                        LEFT JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                        WHERE transactions.type='opening_stock' AND transactions.status='received' AND transactions.location_id=$location_id
                        AND PL.variation_id=variations.id) as total_opening_stock"),
                DB::raw("(SELECT SUM(COALESCE(PL.quantity, 0)) FROM transactions
                        LEFT JOIN purchase_lines AS PL ON transactions.id=PL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='production_purchase' AND transactions.location_id=$location_id
                        AND PL.variation_id=variations.id) as total_manufactured"),
                DB::raw("(SELECT SUM(COALESCE(TSL.quantity, 0)) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='production_sell' AND transactions.location_id=$location_id
                        AND TSL.variation_id=variations.id) as total_ingredients_used"),
                DB::raw("SUM(vld.qty_available) as stock"),
                'variations.sub_sku as sub_sku',
                'p.name as product',
                'p.id as product_id',
                'p.type',
                'p.sku as sku',
                'units.short_name as unit',
                'p.enable_stock as enable_stock',
                'variations.sell_price_inc_tax as unit_price',
                'pv.name as product_variation',
                'variations.name as variation_name',
                'variations.id as variation_id'
            )
            ->groupBy('variations.id')
            ->get();

            foreach ($stock_details as $index => $row) {
                $total_sold = $row->total_sold ?: 0;
                $total_sell_return = $row->total_sell_return ?: 0;
                $total_sell_transfered = $row->total_sell_transfered ?: 0;

                $total_purchase_transfered = $row->total_purchase_transfered ?: 0;
                $total_adjusted = $row->total_adjusted ?: 0;
                $total_purchased = $row->total_purchased ?: 0;
                $total_purchase_return = $row->total_purchase_return ?: 0;
                $total_opening_stock = $row->total_opening_stock ?: 0;
                $total_manufactured = $row->total_manufactured ?: 0;
                $total_ingredients_used = $row->total_ingredients_used ?: 0;

                $total_stock_calculated = $total_opening_stock + $total_purchased + $total_purchase_transfered + $total_sell_return + $total_manufactured
                - ($total_sold + $total_sell_transfered + $total_adjusted + $total_purchase_return + $total_ingredients_used);

                $stock_details[$index]->total_stock_calculated = $total_stock_calculated;
            }
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        return view('report.product_stock_details')
            ->with(compact('stock_details', 'business_locations', 'location'));
    }

    /**
     * Adjusts stock availability mismatch if found
     *
     * @return \Illuminate\Http\Response
     */
    public function adjustProductStock()
    {
        if (!auth()->user()->can('report.stock_details')) {
            abort(403, 'Unauthorized action.');
        }

        if (!empty(request()->input('variation_id'))
            && !empty(request()->input('location_id'))
            && request()->has('stock')) {
            $business_id = request()->session()->get('user.business_id');

            $vld = VariationLocationDetails::leftjoin(
                'business_locations as bl',
                'bl.id',
                '=',
                'variation_location_details.location_id'
            )
                    ->where('variation_location_details.location_id', request()->input('location_id'))
                        ->where('variation_id', request()->input('variation_id'))
                        ->where('bl.business_id', $business_id)
                        ->select('variation_location_details.*')
                        ->first();

            if (!empty($vld)) {
                $vld->qty_available = request()->input('stock');
                $vld->save();
            }
        }

        return redirect()->back()->with(['status' => [
                'success' => 1,
                'msg' => __('lang_v1.updated_succesfully')
            ]]);
    }

    /**
     * Retrieves line orders/sales
     *
     * @return obj
     */
    public function serviceStaffLineOrders()
    {
        $business_id = request()->session()->get('user.business_id');

        $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
                ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
                ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
                ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
                ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->leftJoin('users as ss', 'ss.id', '=', 'transaction_sell_lines.res_service_staff_id')
                ->leftjoin(
                    'business_locations AS bl',
                    't.location_id',
                    '=',
                    'bl.id'
                )
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->whereNotNull('transaction_sell_lines.res_service_staff_id');


        if (!empty(request()->service_staff_id)) {
            $query->where('transaction_sell_lines.res_service_staff_id', request()->service_staff_id);
        }

        if (request()->has('location_id')) {
            $location_id = request()->get('location_id');
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }
        }

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
            $query->whereDate('t.transaction_date', '>=', $start)
                        ->whereDate('t.transaction_date', '<=', $end);
        }

        $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            'u.short_name as unit',
            't.id as transaction_id',
            'bl.name as business_location',
            't.transaction_date',
            't.invoice_no',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.unit_price_before_discount',
            'transaction_sell_lines.line_discount_type',
            'transaction_sell_lines.line_discount_amount',
            'transaction_sell_lines.item_tax',
            'transaction_sell_lines.unit_price_inc_tax',
            DB::raw('CONCAT(COALESCE(ss.first_name, ""), COALESCE(ss.last_name, "")) as service_staff')
        );

        $datatable = Datatables::of($query)
            ->editColumn('product_name', function ($row) {
                $name = $row->product_name;
                if ($row->product_type == 'variable') {
                    $name .= ' - ' . $row->product_variation_name . ' - ' . $row->variation_name;
                }
                return $name;
            })
            ->editColumn(
                'unit_price_inc_tax',
                '<span class="display_currency unit_price_inc_tax" data-currency_symbol="true" data-orig-value="{{$unit_price_inc_tax}}">{{$unit_price_inc_tax}}</span>'
            )
            ->editColumn(
                'item_tax',
                '<span class="display_currency item_tax" data-currency_symbol="true" data-orig-value="{{$item_tax}}">{{$item_tax}}</span>'
            )
            ->editColumn(
                'quantity',
                '<span class="display_currency quantity" data-unit="{{$unit}}" data-currency_symbol="false" data-orig-value="{{$quantity}}">{{$quantity}}</span> {{$unit}}'
            )
            ->editColumn(
                'unit_price_before_discount',
                '<span class="display_currency unit_price_before_discount" data-currency_symbol="true" data-orig-value="{{$unit_price_before_discount}}">{{$unit_price_before_discount}}</span>'
            )
            ->addColumn(
                'total',
                '<span class="display_currency total" data-currency_symbol="true" data-orig-value="{{$unit_price_inc_tax * $quantity}}">{{$unit_price_inc_tax * $quantity}}</span>'
            )
            ->editColumn(
                'line_discount_amount',
                function ($row) {
                    $discount = !empty($row->line_discount_amount) ? $row->line_discount_amount : 0;

                    if (!empty($discount) && $row->line_discount_type == 'percentage') {
                        $discount = $row->unit_price_before_discount * ($discount / 100);
                    }

                    return '<span class="display_currency total-discount" data-currency_symbol="true" data-orig-value="' . $discount . '">' . $discount . '</span>';
                }
            )
            ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')

            ->rawColumns(['line_discount_amount', 'unit_price_before_discount', 'item_tax', 'unit_price_inc_tax', 'item_tax', 'quantity', 'total'])
                  ->make(true);

        return $datatable;
    }

    /**
     * Lists profit by product, category, brand, location, invoice and date
     *
     * @return string $by = null
     */
    public function getProfit($by = null)
    {
        $business_id = request()->session()->get('user.business_id');

        $query = TransactionSellLine
            ::join('transactions as sale', 'transaction_sell_lines.transaction_id', '=', 'sale.id')
            ->leftjoin('transaction_sell_lines_purchase_lines as TSPL', 'transaction_sell_lines.id', '=', 'TSPL.sell_line_id')
            ->leftjoin(
                'purchase_lines as PL',
                'TSPL.purchase_line_id',
                '=',
                'PL.id'
            )
            ->join('products as P', 'transaction_sell_lines.product_id', '=', 'P.id')
            ->where('sale.business_id', $business_id)
            ->where('transaction_sell_lines.children_type', '!=', 'combo');
        //If type combo: find childrens, sale price parent - get PP of childrens
        $query->select(DB::raw('SUM(IF (TSPL.id IS NULL AND P.type="combo", (
    SELECT Sum((tspl2.quantity - tspl2.qty_returned) * (tsl.unit_price_inc_tax - pl2.purchase_price_inc_tax)) AS total
        FROM transaction_sell_lines AS tsl
            JOIN transaction_sell_lines_purchase_lines AS tspl2
        ON tsl.id=tspl2.sell_line_id
        JOIN purchase_lines AS pl2
        ON tspl2.purchase_line_id = pl2.id
        WHERE tsl.parent_sell_line_id = transaction_sell_lines.id), IF(P.enable_stock=0,(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax,
        (TSPL.quantity - TSPL.qty_returned) * (transaction_sell_lines.unit_price_inc_tax - PL.purchase_price_inc_tax)) )) AS gross_profit'));

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
            $query->whereDate('sale.transaction_date', '>=', $start)
                        ->whereDate('sale.transaction_date', '<=', $end);
        }

        if ($by == 'product') {
            $query->join('variations as V', 'transaction_sell_lines.variation_id', '=', 'V.id')
                ->leftJoin('product_variations as PV', 'PV.id', '=', 'V.product_variation_id')
                ->addSelect(DB::raw("IF(P.type='variable', CONCAT(P.name, ' - ', PV.name, ' - ', V.name, ' (', V.sub_sku, ')'), CONCAT(P.name, ' (', P.sku, ')')) as product"))
                ->groupBy('V.id');
        }

        if ($by == 'category') {
            $query->join('variations as V', 'transaction_sell_lines.variation_id', '=', 'V.id')
                ->leftJoin('categories as C', 'C.id', '=', 'P.category_id')
                ->addSelect("C.name as category")
                ->groupBy('C.id');
        }

        if ($by == 'brand') {
            $query->join('variations as V', 'transaction_sell_lines.variation_id', '=', 'V.id')
                ->leftJoin('brands as B', 'B.id', '=', 'P.brand_id')
                ->addSelect("B.name as brand")
                ->groupBy('B.id');
        }

        if ($by == 'location') {
            $query->join('business_locations as L', 'sale.location_id', '=', 'L.id')
                ->addSelect("L.name as location")
                ->groupBy('L.id');
        }

        if ($by == 'invoice') {
            $query->addSelect('sale.invoice_no', 'sale.id as transaction_id')
                ->groupBy('sale.invoice_no');
        }

        if ($by == 'date') {
            $query->addSelect("sale.transaction_date")
                ->groupBy(DB::raw('DATE(sale.transaction_date)'));
        }

        if ($by == 'day') {
            $results = $query->addSelect(DB::raw("DAYNAME(sale.transaction_date) as day"))
                ->groupBy(DB::raw('DAYOFWEEK(sale.transaction_date)'))
                ->get();

            $profits = [];
            foreach ($results as $result) {
                $profits[strtolower($result->day)] = $result->gross_profit;
            }
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            return view('report.partials.profit_by_day')->with(compact('profits', 'days'));
        }

        if ($by == 'customer') {
            $query->join('contacts as CU', 'sale.contact_id', '=', 'CU.id')
            ->addSelect("CU.name as customer")
                ->groupBy('sale.contact_id');
        }

        $datatable = Datatables::of($query)
            ->editColumn(
                'gross_profit',
                '<span class="display_currency gross-profit" data-currency_symbol="true" data-orig-value="{{$gross_profit}}">{{$gross_profit}}</span>'
            );

        if ($by == 'category') {
            $datatable->editColumn(
                'category',
                '{{$category ?? __("lang_v1.uncategorized")}}'
            );
        }
        if ($by == 'brand') {
            $datatable->editColumn(
                'brand',
                '{{$brand ?? __("report.others")}}'
            );
        }

        if ($by == 'date') {
            $datatable->editColumn('transaction_date', '{{@format_date($transaction_date)}}');
        }

        $raw_columns = ['gross_profit'];
        if ($by == 'invoice') {
            $datatable->editColumn('invoice_no', function ($row) {
                return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
            });
            $raw_columns[] = 'invoice_no';
        }
        return $datatable->rawColumns($raw_columns)
                  ->make(true);
    }

    /**
     * Shows items report from sell purchase mapping table
     *
     * @return \Illuminate\Http\Response
     */
    public function itemsReport()
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $query = TransactionSellLinesPurchaseLines::leftJoin('transaction_sell_lines
                    as SL', 'SL.id', '=', 'transaction_sell_lines_purchase_lines.sell_line_id')
                ->leftJoin('stock_adjustment_lines
                    as SAL', 'SAL.id', '=', 'transaction_sell_lines_purchase_lines.stock_adjustment_line_id')
                ->leftJoin('transactions as sale', 'SL.transaction_id', '=', 'sale.id')
                ->leftJoin('transactions as stock_adjustment', 'SAL.transaction_id', '=', 'stock_adjustment.id')
                ->join('purchase_lines as PL', 'PL.id', '=', 'transaction_sell_lines_purchase_lines.purchase_line_id')
                ->join('transactions as purchase', 'PL.transaction_id', '=', 'purchase.id')
                ->join('business_locations as bl', 'purchase.location_id', '=', 'bl.id')
                ->join(
                    'variations as v',
                    'PL.variation_id',
                    '=',
                    'v.id'
                    )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'PL.product_id', '=', 'p.id')
                ->join('units as u', 'p.unit_id', '=', 'u.id')
                ->leftJoin('contacts as suppliers', 'purchase.contact_id', '=', 'suppliers.id')
                ->leftJoin('contacts as customers', 'sale.contact_id', '=', 'customers.id')
                ->where('purchase.business_id', $business_id)
                ->select(
                    'v.sub_sku as sku',
                    'p.type as product_type',
                    'p.name as product_name',
                    'v.name as variation_name',
                    'pv.name as product_variation',
                    'u.short_name as unit',
                    'purchase.transaction_date as purchase_date',
                    'purchase.ref_no as purchase_ref_no',
                    'purchase.type as purchase_type',
                    'suppliers.name as supplier',
                    'PL.purchase_price_inc_tax as purchase_price',
                    'sale.transaction_date as sell_date',
                    'stock_adjustment.transaction_date as stock_adjustment_date',
                    'sale.invoice_no as sale_invoice_no',
                    'stock_adjustment.ref_no as stock_adjustment_ref_no',
                    'customers.name as customer',
                    'transaction_sell_lines_purchase_lines.quantity as quantity',
                    'SL.unit_price_inc_tax as selling_price',
                    'SAL.unit_price as stock_adjustment_price',
                    'transaction_sell_lines_purchase_lines.stock_adjustment_line_id',
                    'transaction_sell_lines_purchase_lines.sell_line_id',
                    'purchase.id as transaction_id',
                    'transaction_sell_lines_purchase_lines.purchase_line_id',
                    'transaction_sell_lines_purchase_lines.qty_returned',
                    'bl.name as location'
                );

            if (!empty(request()->purchase_start) && !empty(request()->purchase_end)) {
                $start = request()->purchase_start;
                $end =  request()->purchase_end;
                $query->whereDate('purchase.transaction_date', '>=', $start)
                            ->whereDate('purchase.transaction_date', '<=', $end);
            }
            if (!empty(request()->sale_start) && !empty(request()->sale_end)) {
                $start = request()->sale_start;
                $end =  request()->sale_end;
                $query->where(function ($q) use ($start, $end) {
                    $q->where(function ($qr) use ($start, $end) {
                        $qr->whereDate('sale.transaction_date', '>=', $start)
                           ->whereDate('sale.transaction_date', '<=', $end);
                    })->orWhere(function ($qr) use ($start, $end) {
                        $qr->whereDate('stock_adjustment.transaction_date', '>=', $start)
                           ->whereDate('stock_adjustment.transaction_date', '<=', $end);
                    });
                });
            }

            $supplier_id = request()->get('supplier_id', null);
            if (!empty($supplier_id)) {
                $query->where('suppliers.id', $supplier_id);
            }

            $customer_id = request()->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('customers.id', $customer_id);
            }

            $location_id = request()->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('purchase.location_id', $location_id);
            }

            $only_mfg_products = request()->get('only_mfg_products', 0);
            if (!empty($only_mfg_products)) {
                $query->where('purchase.type', 'production_purchase');
            }

            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                ->editColumn('purchase_date', '{{@format_datetime($purchase_date)}}')
                ->editColumn('purchase_ref_no', function ($row) {
                    // $html = $row->purchase_type == 'purchase' ? '<a data-href="' . action('PurchaseController@show', [$row->purchase_line_id])
                    //         . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->purchase_ref_no . '</a>' : $row->purchase_ref_no;

                    $html = $row->purchase_type == 'purchase' ? '<a data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->purchase_ref_no . '</a>' : $row->purchase_ref_no;

                    if ($row->purchase_type == 'opening_stock') {
                        $html .= '(' . __('lang_v1.opening_stock') . ')';
                    }
                    return $html;
                })
                ->editColumn('purchase_price', function ($row) {
                    return '<span class="display_currency purchase_price" data-currency_symbol=true data-orig-value="' . $row->purchase_price . '">' . $row->purchase_price . '</span>';
                })
                ->editColumn('sell_date', '@if(!empty($sell_line_id)) {{@format_datetime($sell_date)}} @else {{@format_datetime($stock_adjustment_date)}} @endif')

                ->editColumn('sale_invoice_no', function ($row) {
                    $invoice_no = !empty($row->sell_line_id) ? $row->sale_invoice_no : $row->stock_adjustment_ref_no . '<br><small>(' . __('stock_adjustment.stock_adjustment') . '</small>' ;

                    return $invoice_no;
                })
                ->editColumn('quantity', function ($row) {
                    $html = '<span data-is_quantity="true" class="display_currency quantity" data-currency_symbol=false data-orig-value="' . (float)$row->quantity . '" data-unit="' . $row->unit . '" >' . (float) $row->quantity . '</span> ' . $row->unit;
                    if ($row->qty_returned > 0) {
                        $html .= '<small><i>(<span data-is_quantity="true" class="display_currency" data-currency_symbol=false>' . (float) $row->quantity . '</span> ' . $row->unit . ' ' . __('lang_v1.returned') . ')</i></small>';
                    }

                    return $html;
                })
                 ->editColumn('selling_price', function ($row) {
                     $selling_price = !empty($row->sell_line_id) ? $row->selling_price : $row->stock_adjustment_price;

                     return '<span class="display_currency row_selling_price" data-currency_symbol=true data-orig-value="' . $selling_price . '">' . $selling_price . '</span>';
                 })

                 ->addColumn('subtotal', function ($row) {
                     $selling_price = !empty($row->sell_line_id) ? $row->selling_price : $row->stock_adjustment_price;
                     $subtotal = $selling_price * $row->quantity;
                     return '<span class="display_currency row_subtotal" data-currency_symbol=true data-orig-value="' . $subtotal . '">' . $subtotal . '</span>';
                 })

                ->filterColumn('sale_invoice_no', function ($query, $keyword) {
                    $query->where('sale.invoice_no', 'like', ["%{$keyword}%"])
                          ->orWhere('stock_adjustment.ref_no', 'like', ["%{$keyword}%"]);
                })

                ->rawColumns(['subtotal', 'selling_price', 'quantity', 'purchase_price', 'sale_invoice_no', 'purchase_ref_no'])
                ->make(true);
        }

        $suppliers = Contact::suppliersDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $business_locations = BusinessLocation::forDropdown($business_id);
        return view('report.items_report')->with(compact('suppliers', 'customers', 'business_locations'));
    }

    /**
     * Shows purchase report
     *
     * @return \Illuminate\Http\Response
     */
    public function purchaseReport()
    {
        if ((!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) || empty(config('constants.show_report_606'))) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true);
            $purchases = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->join(
                        'business_locations AS BS',
                        'transactions.location_id',
                        '=',
                        'BS.id'
                    )
                    ->leftJoin(
                        'transaction_payments AS TP',
                        'transactions.id',
                        '=',
                        'TP.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase')
                    ->with(['payment_lines'])
                    ->select(
                        'transactions.id',
                        'transactions.ref_no',
                        'contacts.name',
                        'contacts.contact_id',
                        'final_total',
                        'total_before_tax',
                        'discount_amount',
                        'discount_type',
                        'tax_amount',
                        DB::raw('DATE_FORMAT(transaction_date, "%Y/%m") as purchase_year_month'),
                        DB::raw('DATE_FORMAT(transaction_date, "%d") as purchase_day')
                    )
                    ->groupBy('transactions.id');

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchases->where('transactions.location_id', request()->location_id);
            }
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $purchases->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $purchases->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            if (!empty(request()->status)) {
                $purchases->where('transactions.status', request()->status);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchases->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions.created_by', request()->session()->get('user.id'));
            }

            return Datatables::of($purchases)
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn(
                    'total_before_tax',
                    '<span class="display_currency total_before_tax" data-currency_symbol="true" data-orig-value="{{$total_before_tax}}">{{$total_before_tax}}</span>'
                )
                ->editColumn(
                    'tax_amount',
                    '<span class="display_currency tax_amount" data-currency_symbol="true" data-orig-value="{{$tax_amount}}">{{$tax_amount}}</span>'
                )
                ->editColumn(
                    'discount_amount',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        return '<span class="display_currency total-discount" data-currency_symbol="true" data-orig-value="' . $discount . '">' . $discount . '</span>';
                    }
                )
                ->addColumn('payment_year_month', function ($row) {
                    $year_month = '';
                    if (!empty($row->payment_lines->first())) {
                        $year_month = \Carbon::parse($row->payment_lines->first()->paid_on)->format('Y/m');
                    }
                    return $year_month;
                })
                ->addColumn('payment_day', function ($row) {
                    $payment_day = '';
                    if (!empty($row->payment_lines->first())) {
                        $payment_day = \Carbon::parse($row->payment_lines->first()->paid_on)->format('d');
                    }
                    return $payment_day;
                })
                ->addColumn('payment_method', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]];
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

                    return $html;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("purchase.view")) {
                            return  action('PurchaseController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['final_total', 'total_before_tax', 'tax_amount', 'discount_amount', 'payment_method'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();

        return view('report.purchase_report')
            ->with(compact('business_locations', 'suppliers', 'orderStatuses'));
    }

    /**
     * Shows sale report
     *
     * @return \Illuminate\Http\Response
     */
    public function saleReport()
    {
        if ((!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) ||empty(config('constants.show_report_607'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        return view('report.sale_report')
            ->with(compact('business_locations', 'customers'));
    }

    /**
     * Calculates stock values
     *
     * @return array
     */
    public function getStockValue()
    {
        $business_id = request()->session()->get('user.business_id');
        $end_date = \Carbon::now()->format('Y-m-d');
        $location_id = request()->input('location_id');
        $filters = request()->only(['category_id', 'sub_category_id', 'brand_id', 'unit_id']);
        //Get Closing stock
        $closing_stock_by_pp = $this->transactionUtil->getOpeningClosingStock(
            $business_id,
            $end_date,
            $location_id,
            false,
            false,
            $filters
        );
        $closing_stock_by_sp = $this->transactionUtil->getOpeningClosingStock(
            $business_id,
            $end_date,
            $location_id,
            false,
            true,
            $filters
        );
        $potential_profit = $closing_stock_by_sp - $closing_stock_by_pp;
        $profit_margin = empty($closing_stock_by_sp) ? 0 : ($potential_profit / $closing_stock_by_sp) * 100;

        return [
            'closing_stock_by_pp' => $closing_stock_by_pp,
            'closing_stock_by_sp' => $closing_stock_by_sp,
            'potential_profit' => $potential_profit,
            'profit_margin' => $profit_margin
        ];
    }

    public function getBalanceReport()
    {
        // code...
        $business_id = request()->session()->get('user.business_id');
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        if (request()->ajax()) {
           $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();
            $sells = $this->transactionUtil->getListSells($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            //Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
                $sells->where('transactions.created_by', request()->session()->get('user.id'));
            }

            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $sells->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('br_account_rep')) {
                $br_account_rep = request()->get('br_account_rep');
                if (!empty($br_account_rep)) {
                    $sells->where('contacts.account_rep', $br_account_rep);
                }
            }

            if (!empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                    ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            // if (!empty(request()->customer_id)) {
            //     $customer_id = request()->customer_id;
            //     $sells->where('contacts.id', $customer_id);
            // }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $sells->where('transactions.is_direct_sale', 0);
                    $sells->whereNull('transactions.sub_type');
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if ($is_woocommerce) {
                $sells->addSelect('transactions.woocommerce_order_id');
                if (request()->only_woocommerce_sells) {
                    $sells->whereNotNull('transactions.woocommerce_order_id');
                }
            }

            if (request()->do_not_show_paid_invoices) {
                $sells->where('transactions.payment_status', '!=', 'paid');
            }

            $sells->groupBy('transactions.id');

            $with[] = 'payment_lines';
            if (!empty($with)) {
                $sells->with($with);
            }

            //$business_details = $this->businessUtil->getDetails($business_id);
            if ($this->businessUtil->isModuleEnabled('subscription')) {
                $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            }

            if(empty(request()->input('product_category')) || request()->input('product_category') == 'all'){
                $filtered_sells = $sells;
            } else {
                $category_id = request()->input('product_category');
                $filtered_sells = [];
                $sells_arr = $sells->get();
                foreach ($sells_arr as  $value) {
                    $transaction_id = $value->id;
                    $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                    foreach ($transaction_sells as $sell) {
                        if($sell->product_single->category_single && $sell->product_single->category_single->id == $category_id){
                            array_push($filtered_sells, $value);
                            break;
                        }
                    }
                }
            }
            return $datatable = Datatables::of($filtered_sells)
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                 ->editColumn('additional_notes', function ($row) {
                    $additional_notes =  $row->additional_notes;
                    $additional_notes_html = '<span class="" d data-orig-value="' . $additional_notes . '">' . $additional_notes . '</span>';
                    return $additional_notes_html;
                    //return $additional_notes;
                })
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') .'"><i class="fas fa-undo"></i></small>';
                    }
                    if (!empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    return $invoice_no;
                })
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining =  $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $total_remaining . '">' . $total_remaining . '</span>';
                    return $total_remaining_html;
                })

                // ->setRowAttr([
                // 'data-href' => function ($row) {
                //     if (auth()->user()->can("sell.view") || auth()->user()->can("view_own_sell_only")) {
                //         return  action('SellController@show', [$row->id]) ;
                //     } else {
                //         return '';
                //     }
                // }])
                ->rawColumns(['transaction_date','additional_notes','invoice_no', 'total_remaining'])
                ->make(true);


        }

        $users_arr = User::select('id','first_name')->get();
        $users = [];
        foreach($users_arr as $user){
            $users[$user->id] = $user->first_name;
        }
        $types = [
            '' => __('lang_v1.all'),
            'customer' => __('report.customer'),
            'supplier' => __('report.supplier')
        ];

        return view('report.balance_report')->with(compact('users', 'types'));
    }

    public function NewbalanceReport()
    {
        // $users_arr = User::select('id','first_name','last_name')->withTrashed()->get();
        $users_arr = User::select('id','first_name','last_name')
            ->where('business_id',4)
            ->where('status','active')
            ->where('allow_login',1)
            ->where('sales_rep',1)
            ->orderBy('first_name')
        ->get();

        // $users = [];
        $users[] =__('lang_v1.all');
        foreach($users_arr as $user){
            $users[$user->id] = $user->first_name . " " . $user->last_name;
        }
        $types = [
            '' => __('lang_v1.all'),
            'customer' => __('report.customer'),
            'supplier' => __('report.supplier')
        ];
        $acc_rep = 0;
        $from = date('m-01-Y');
        $to = date('m-t-Y h:i:s A');
        //$new_todate = date('Y-m-d h:i:s A',strtotime('+23 hour +59 minutes +45 seconds',strtotime($to)));
        // print_r($users);
        if($acc_rep == 0)
        {
            $newbalance = Transaction::join('contacts as c','transactions.contact_id','=','c.id')
            // ->where('c.account_rep','=',$acc_rep)
            ->where('transactions.type','sell')
            ->where('transactions.payment_status','!=','paid')
            ->whereDate('transactions.transaction_date','>=',$from)
            ->whereDate('transactions.transaction_date','<=',$to)
            ->select('c.id','c.name as customer','transactions.id as transaction_id','c.email as email','c.mobile','c.balance','transactions.invoice_no','transactions.transaction_date','transactions.final_total','transactions.additional_notes','transactions.payment_status')
            ->get()->groupBy('customer');

            return view('report.balance_report_new')->with(compact('users', 'types','newbalance','from','to' ,'acc_rep'));

        }
        else{
            $newbalance = Transaction::join('contacts as c','transactions.contact_id','=','c.id')
            ->where('c.account_rep','=',$acc_rep)
            ->where('transactions.type','sell')
            ->where('transactions.payment_status','!=','paid')
            ->whereDate('transactions.transaction_date','>=',$from)
            ->whereDate('transactions.transaction_date','<=',$to)
            ->select('c.id','c.name as customer','transactions.id as transaction_id','c.email as email','c.mobile','c.balance','transactions.invoice_no','transactions.transaction_date','transactions.final_total','transactions.additional_notes','transactions.payment_status')
            ->get()->groupBy('customer');
            return view('report.balance_report_new')->with(compact('users', 'types','newbalance','from','to' ,'acc_rep'));
        }
       // dd($total_paid);

        //return view('report.BalanceReport');
        //return view('')
    }


    public function searchBalanceReport(request $request)
    {
        // $users_arr = User::select('id','first_name','last_name')->withTrashed()->get();
        $users_arr = User::select('id','first_name','last_name')
            ->where('business_id',4)
            ->where('status','active')
            ->where('allow_login',1)
            ->where('sales_rep',1)
            ->orderBy('first_name')
        ->get();

        // $users = [];
        $users[] =__('lang_v1.all');
        foreach($users_arr as $user){
            $users[$user->id] = $user->first_name . " " . $user->last_name;
        }
        $types = [
            '' => __('lang_v1.all'),
            'customer' => __('report.customer'),
            'supplier' => __('report.supplier')
        ];

        $startDate = $request->get('fromDate');
        $endDate = $request->get('toDate');

        $acc_rep = $request->get('br_account_rep');

        //dd($acc_rep);

        $myArray = explode('-', $startDate);
        $fr1 = $myArray[0];
        $to2 = $myArray[1];
        $from = str_replace(' ', '', $fr1);
        $to = str_replace(' ', '', $to2);

        $new_fromdate = date('Y-m-d',strtotime($from));
        $new_todate = date('Y-m-d h:i:s A',strtotime('+23 hour +59 minutes +45 seconds',strtotime($to)));
        //dd($newformat);
        // print_r($acc_rep);

        if($acc_rep == 0){
            $newbalance = Transaction::join('contacts as c','transactions.contact_id','=','c.id')
            // ->where('c.account_rep','=',$acc_rep)
            ->where('transactions.type','sell')
            ->where('transactions.status','final')
            ->where('c.contact_status','active')
            ->where('transactions.payment_status','!=','paid')
            ->whereDate('transactions.transaction_date','>=',$new_fromdate)
            ->whereDate('transactions.transaction_date','<=',$new_todate)
            ->select('c.id','c.name as customer','transactions.id as transaction_id','c.email as email','c.mobile','c.balance','transactions.invoice_no','transactions.transaction_date','transactions.final_total','transactions.additional_notes','transactions.payment_status',
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                            TP.transaction_id=transactions.id) as total_paid'),
            )->get()->groupBy('customer');
            return view('report.balance_report_new')->with(compact('users', 'types','newbalance','from','to','acc_rep'));

        }
        else if($acc_rep == 6){
            $newbalance = Transaction::join('contacts as c','transactions.contact_id','=','c.id')
            ->where(function($q){
                $q->where('c.account_rep','6')
                ->orWhereNull('c.account_rep');
            })
            ->where('transactions.type','sell')
            ->where('transactions.status','final')
            ->where('c.contact_status','active')
            ->where('transactions.payment_status','!=','paid')
            ->whereDate('transactions.transaction_date','>=',$new_fromdate)
            ->whereDate('transactions.transaction_date','<=',$new_todate)
            ->select('c.id','c.name as customer','transactions.id as transaction_id','c.email as email','c.mobile','c.balance','transactions.invoice_no','transactions.transaction_date','transactions.final_total','transactions.additional_notes','transactions.payment_status',
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                            TP.transaction_id=transactions.id) as total_paid'),
            )->get()->groupBy('customer');
            return view('report.balance_report_new')->with(compact('users', 'types','newbalance','from','to','acc_rep'));

        }
        else{
            $newbalance = Transaction::join('contacts as c','transactions.contact_id','=','c.id')
            ->where('c.account_rep','=',$acc_rep)
            ->where('transactions.type','sell')
            ->where('c.contact_status','active')
            ->where('transactions.status','final')
            ->where('transactions.payment_status','!=','paid')
            ->whereDate('transactions.transaction_date','>=',$new_fromdate)
            ->whereDate('transactions.transaction_date','<=',$new_todate)
            ->select('c.id','c.name as customer','transactions.id as transaction_id','c.email as email','c.mobile','c.balance','transactions.invoice_no','transactions.transaction_date','transactions.final_total','transactions.additional_notes','transactions.payment_status',
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                            TP.transaction_id=transactions.id) as total_paid'),
            )->get()->groupBy('customer');
            return view('report.balance_report_new')->with(compact('users', 'types','newbalance','from','to','acc_rep'));
        }
    }

    //////////////// original function
    // public function getCategorywisesalereport(Request $request){

    //   if ($request->ajax()) {
    //         $variation_id = $request->get('variation_id', null);
    //         $start_date = $request->get('start_date');
    //         $end_date = $request->get('end_date');

    //         $start_date = $start_date." 00:00:00";
    //         $end_date = $end_date." 23:59:59";
    //         DB::enableQueryLog();
    //         $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
    //     //   ->join(DB::raw('(select * from  variation_location_details where variation_location_details.created_at >= 2021-01-01 00:00:00) as variation_location_details'),'variation_location_details.variation_id','=','variations.id')

    //         ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
    //         ->join('products','products.id','=','variations.product_id')
    //         ->join('categories','categories.id','=','products.category_id')
    //         ->join('brands','products.brand_id','=','brands.id')
    //         // ->where('products.not_for_selling','=','0')
    //         // ->where('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory','>=','0')
    //         ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
    //             $start_date, $end_date])
    //         // ->whereDate('variation_location_details.created_at', '>=', '2021-01-01'.'00:00:00')
    //         ->select('categories.name as category_name',

    //         DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
    //                 sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory
    //         '),
    //         // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),

    //         // DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),

    //         // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) <=  0 as negative_inventory'),

    //         // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
    //         // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
    //         // DB::raw('sum(variation_location_details.qty_available) as stock'),
    //         // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
    //         'transaction_sell_lines.quant as quantity',
    //         // DB::raw('sum(variation_location_details.qty_available * variations.sell_price_inc_tax) as total_invent')
    //         DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
    //         // DB::raw("(SELECT sum(variations.sell_price_inc_tax * variation_location_details.qty_available) FROM categories where  categories.id = products.category_id) as total_inventory")
    //         // DB::raw("(SELECT SUM(vld.qty_available *variations.sell_price_inc_tax) FROM variation_location_details as vld WHERE vld.variation_id=variations.id $vld_str) as total_inventory"),
    //         DB::raw("(select variation_location_details.qty_available) as total_quantity"),
    //         )
    //         ->groupBy('categories.id')->get();

    //     // \Log::info(DB::getQueryLog());
    //     // dd(DB::getQueryLog());

    //          $total_sale = $query->sum('unit_price');
    //          $total_invent = $query->sum('total_inventory');

    //         //Original Query Start//
    //         $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
    //         // ->join(DB::raw('(select * from  variation_location_details where variation_location_details.created_at >= 2021-01-01 00:00:00) as variation_location_details'),'variation_location_details.variation_id','=','variations.id')

    //         ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
    //         ->join('products','products.id','=','variations.product_id')
    //         ->join('categories','categories.id','=','products.category_id')
    //         ->join('brands','products.brand_id','=','brands.id')
    //         // ->where('products.not_for_selling','=','0')
    //         // ->where('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory','>=','0')
    //         ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
    //             $start_date, $end_date])
    //         // ->whereDate('variation_location_details.created_at', '>=', '2021-01-01'.'00:00:00')
    //         ->select('categories.name as category_name',

    //         DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
    //                 sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory
    //         '),
    //         // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),

    //         // DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),

    //         // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) <=  0 as negative_inventory'),

    //         // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
    //         // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
    //         // DB::raw('sum(variation_location_details.qty_available) as stock'),
    //         // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
    //         'transaction_sell_lines.quant as quantity',
    //         // DB::raw('sum(variation_location_details.qty_available * variations.sell_price_inc_tax) as total_invent')
    //         DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
    //         // DB::raw("(SELECT sum(variations.sell_price_inc_tax * variation_location_details.qty_available) FROM categories where  categories.id = products.category_id) as total_inventory")
    //         // DB::raw("(SELECT SUM(vld.qty_available *variations.sell_price_inc_tax) FROM variation_location_details as vld WHERE vld.variation_id=variations.id $vld_str) as total_inventory"),
    //         DB::raw("(select variation_location_details.qty_available)as total_quantity"),
    //         )
    //         ->groupBy('categories.id')->get();
    //         //Original Query Ends//

    //         //latest change 31/01/2022//
    //         //     $query =             DB::table('categories')
    //         // ->join('products','products.category_id','=','categories.id')
    //         // ->join('variations','products.id','=','variations.product_id')
    //         // ->join('brands','products.brand_id','=','brands.id')
    //         //  ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
    //         //  ->join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
    //         //  ->select('categories.name as category_name',
    //         //         DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
    //         //                 sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),'transaction_sell_lines.quant as quantity',
    //         //         DB::raw("(SELECT sum(case when transaction_sell_lines.created_at BETWEEN '$start_date' AND '$end_date' then transaction_sell_lines.quant *variations.sell_price_inc_tax else 0 end)) as unit_price"),
    //         //         DB::raw("(select variation_location_details.qty_available where transaction_sell_lines.created_at BETWEEN '$start_date' AND '$end_date')as total_quantity"),
    //         //     )

    //         // ->groupBy('categories.id')->get();
    //         //latest change 31/01/2022//

    //         return Datatables::of($query)
    //             ->editColumn('category_name', function ($row) {
    //                 return '<span class=""  data-orig-value="' . $row->category_name . '">'. $row->category_name . '</span>';
    //             })
    //             ->editColumn('total_sold_price', function ($row) {
    //                 // $unit_price = $row->unit_price;
    //                 // $stock = $row->total_sold;
    //                 // $total_stock_price = $stock * $unit_price;
    //                 return '<span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="' . $row->unit_price . '">' . $row->unit_price . '</span>';

    //                 // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
    //             })

    //             ->editColumn('quantity', function ($row) {
    //                 return '<span class="total_quantity"  data-orig-value="' . $row->quantity . '">'. $row->quantity . '</span>';
    //             })

    //             ->editColumn('sale_percentage', function ($row) use($total_sale) {
    //                 $unit_price = $row->unit_price;
    //                 $percent =  ($unit_price*100)/$total_sale;
    //                 $sale_percentage = round($percent,2);
    //                 return '<span class="total-sale-percent" data-orig-value="' . $sale_percentage . '">'. $sale_percentage .'% </span>';
    //             })
    //             ->editColumn('total_stock_price', function ($row) {
    //                 // $unit_price = $row->price;
    //                 // $stock = $row->stock;
    //                 // $total_stock_price = $stock * $unit_price;
    //                 // if($row->total_inventory >= 0){
    //                 return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';
    //                 // }
    //                 // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
    //             })
    //           ->editColumn('total_quantity', function ($row) {
    //                 return '<span class="quantity"  data-orig-value="' . $row->total_quantity . '">'. $row->total_quantity . '</span>';
    //             })

    //             ->editColumn('negative_inventory', function ($row) {
    //                 // if($row->total_inventory < 0){
    //                 return '<span class="display_currency total_negative_inventory" data-currency_symbol="true" data-orig-value="' . $row->negative_inventory . '">' . $row->negative_inventory . '</span>';
    //                 // }
    //             })
    //             ->editColumn('inventory_percentage', function ($row) use($total_invent) {
    //                 // $unit_price = $row->price;
    //                 // $stock = $row->stock;
    //                 // $total_stock_price = $stock * $unit_price;
    //                 $percent =  ($row->total_inventory*100)/$total_invent;
    //                 $inventory_percent = round($percent,2);
    //                 return '<span class="total-inventory-percent" data-orig-value="' . $inventory_percent . '">'. $inventory_percent .'% </span>';
    //             })
    //             ->editColumn('difference', function ($row) use($total_invent,$total_sale) {
    //                 // $unit_price = $row->unit_price;
    //                 // $stock = $row->stock;
    //                 // $total_stock_price = $stock * $unit_price;
    //                 // $percent =  ($total_stock_price*100)/$total_invent;
    //                 // $inventory_percent = round($percent,2);

    //                 // $percent =  ($unit_price*100)/$total_sale;
    //                 // $sale_percentage = round($percent,2);

    //                 $percent =  ($row->unit_price*100)/$total_sale;
    //                 $sale_percentage = round($percent,2);

    //                 $percent =  ($row->total_inventory*100)/$total_invent;
    //                 $inventory_percent = round($percent,2);

    //                 $difference = $inventory_percent - $sale_percentage;

    //                 return '<span class="" data-orig-value="' . $difference . '">'. $difference .'% </span>';
    //             })
    //         //    ->filterColumn('category_name', function ($query, $keyword) {
    //           //      $query->where('categories.name', 'like', "%{$keyword}%");
    //             //})

    //             ->rawColumns(['category_name', 'total_sold_price','negative_inventory','total_quantity','quantity','sale_percentage','total_stock_price','inventory_percentage','difference'])
    //             ->make(true);
    //     }

    //     return view('report.category_wise_sale_report');
    // }

    /**
     * Shows sale-tree report
     *
     * @return \Illuminate\Http\Response
     */
    public function getsaleTreeReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if ($request->ajax()) {
           $variation_id = $request->get('variation_id', null);
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";
            //$start_date = "2022-02-11 00:00:00";
            //$end_date = "2022-02-11 23:59:59";
            DB::enableQueryLog();

        $query =  DB::table('categories')
            ->leftJoin('products','products.category_id','=','categories.id')
            ->leftJoin('variations','variations.product_id','=','products.id')
            ->leftJoin('variation_location_details', function($joins){
                    $joins->on('variation_location_details.product_id', '=', 'products.id')
                    ->where('variation_location_details.id', '=', (
                          DB::raw("(SELECT MAX(id)
                          FROM variation_location_details
                          WHERE variation_id = variations.id)")
                    ));
            })
            ->leftJoin('transaction_sell_lines', function($join) use($start_date, $end_date){
                    $join->on('transaction_sell_lines.product_id', '=', 'products.id')
                    ->whereBetween('transaction_sell_lines.created_at', array($start_date, $end_date));
                    //->whereIn('transaction_sell_lines.id',array(715513,715514,715515,715516);
            })
            //->leftJoin('variation_location_details','variation_location_details.product_id','=','products.id')
            //->leftJoin('transaction_sell_lines','transaction_sell_lines.product_id','=','products.id')
            ->select(
                        'categories.id as category_id',
                        'categories.name as category_name',
                        'variation_location_details.qty_available as total_quantity',
                        'transaction_sell_lines.quantity as quantity',
                        'transaction_sell_lines.*',
                        DB::raw("(SELECT sum(transaction_sell_lines.quantity) )as quant"),
                        DB::raw("(SELECT sum(transaction_sell_lines.quantity *variations.sell_price_inc_tax)  ) as unit_price"),
                        DB::raw("(select
                                    sum(case when vld.qty_available>0 then vld.qty_available *v.sell_price_inc_tax else 0 end)
                                    FROM products as p
                                    left join variations as v on v.product_id = p.id
                                    left join variation_location_details as vld on vld.product_id = p.id
                                    WHERE p.category_id = categories.id)  as total_inventory"),
                        DB::raw("(select
                                    sum(case when vld.qty_available<0 then vld.qty_available * v.sell_price_inc_tax  else 0 end)
                                    FROM products as p
                                    left join variations as v on v.product_id = p.id
                                    left join variation_location_details as vld on vld.product_id = p.id
                                    WHERE p.category_id = categories.id) as negative_inventory"),
                        //DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory'),
                        //DB::raw('sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
                    )
            ->groupBy('categories.id')
            ->get();


            $total_sale = $query->sum('unit_price');
            $total_invent = $query->sum('total_inventory');

            $datatable =  Datatables::of($query)
                ->editColumn('category_id', function ($row) {
                            return ($row->category_id);
                        })
                ->editColumn('category_name', function ($row) {
                    return '<span class=""  data-orig-value="' . $row->category_name . '">'. $row->category_name . '</span>';
                })

                ->editColumn('total_sold_price', function ($row) {
                        return '<span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="' . $row->unit_price . '">' . $row->unit_price . '</span>';
                    })

                   ->editColumn('quantity', function ($row) {
                        return '<span class="total_quantity"  data-orig-value="' . $row->quantity . '">'. $row->quantity . '</span>';
                     })

                    //  ->editColumn('total_stock_price', function ($row) {
                    //     return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';
                    // })

                    ->editColumn('sale_percentage', function ($row) use($total_sale) {
                        $unit_price = $row->unit_price;
                        if($total_sale!==0){
                            $percent =  ($unit_price*100)/$total_sale;
                            $sale_percentage = round($percent,2);
                        }else{
                            $sale_percentage = 0;
                        }
                        return '<span class="total-sale-percent" data-orig-value="' . $sale_percentage . '">'. $sale_percentage .'% </span>';
                    })

                    ->editColumn('total_quantity', function ($row) {
                        return '<span class="quantity"  data-orig-value="' . $row->total_quantity . '">'. $row->total_quantity . '</span>';
                    })

                    ->editColumn('negative_inventory', function ($row) {
                        return '<span class="display_currency total_negative_inventory" data-currency_symbol="true" data-orig-value="' . $row->negative_inventory . '">' . $row->negative_inventory . '</span>';
                    })

                    ->editColumn('inventory_percentage', function ($row) use($total_invent) {
                        if($total_invent!==0){
                            $percent =  ($row->total_inventory*100)/$total_invent;
                            $inventory_percent = round($percent,2);
                        }else{
                            $inventory_percent=0;
                        }
                        return '<span class="total-inventory-percent" data-orig-value="' . $inventory_percent . '">'. $inventory_percent .'% </span>';
                    })

                    ->editColumn('difference', function ($row) use($total_invent,$total_sale) {
                       if($total_sale!==0){
                            $percent =  ($row->unit_price*100)/$total_sale;
                            $sale_percentage = round($percent,2);
                       }else{
                           $sale_percentage = 0;
                       }

                       if($total_invent!==0){
                            $percent =  ($row->total_inventory*100)/$total_invent;
                            $inventory_percent = round($percent,2);
                       }else{
                           $inventory_percent = 0;
                       }

                        $difference = $inventory_percent - $sale_percentage;

                        return '<span class="footer-total-diff" data-orig-value="' . $difference . '">'. $difference .'% </span>';
                    });
            $raw_columns  = ['category_name', 'total_sold_price','negative_inventory','total_quantity','quantity','sale_percentage','inventory_percentage','difference'];


            $datatable->editColumn('total_stock_price', function ($row) {
                            return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';
                        });
            $raw_columns[] = 'total_stock_price';
            return $datatable->rawColumns($raw_columns)->make(true);

        }
        return view('report.sales_tree_report');

    }

    /**
     * Shows sale-tree brand report data
     *
     * @return \Illuminate\Http\Response
     */
    public function getsaleTreeBrandReport(Request $request){
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $categoryId = $request->get('category_id');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            $resultData =  DB::table('brands')
                ->join('products', function ($join) use($categoryId) {
                    $join->on('products.brand_id', '=', 'brands.id')
                        //  ->whereNotNull('products.brand_id');
                         ->where('products.category_id','=',$categoryId);
                })
                // ->join('products','products.brand_id','=','brands.id')
                ->leftJoin('variations','variations.product_id','=','products.id')
                ->leftJoin('variation_location_details', function($joins){
                    $joins->on('variation_location_details.product_id', '=', 'products.id')
                    ->where('variation_location_details.id', '=', (
                          DB::raw("(SELECT MAX(id)
                          FROM variation_location_details
                          WHERE variation_id = variations.id)")
                    ));
                })
                ->leftJoin('transaction_sell_lines', function($join) use($start_date, $end_date){
                        $join->on('transaction_sell_lines.product_id', '=', 'products.id')
                        ->whereBetween('transaction_sell_lines.created_at', array($start_date, $end_date));
                        //->whereIn('transaction_sell_lines.id',array(715513,715514,715515,715516);
                })

                //->leftJoin('variation_location_details','variation_location_details.product_id','=','products.id')
                //->leftJoin('transaction_sell_lines','transaction_sell_lines.product_id','=','products.id')
                ->select(
                            'brands.id as brand_id',
                            'brands.name as brand_name',
                            'variation_location_details.qty_available as total_quantity',
                            'transaction_sell_lines.quantity as quantity',
                            'transaction_sell_lines.*',
                            DB::raw("(SELECT sum(transaction_sell_lines.quantity) )as quant"),
                            DB::raw("(SELECT sum(transaction_sell_lines.quantity *variations.sell_price_inc_tax)  ) as unit_price"),
                            DB::raw("(select
                                        sum(case when vld.qty_available>0 then vld.qty_available *v.sell_price_inc_tax else 0 end)
                                        FROM products as p
                                        left join variations as v on v.product_id = p.id
                                        left join variation_location_details as vld on vld.product_id = p.id
                                        WHERE p.brand_id = brands.id)  as total_inventory"),
                            DB::raw("(select
                                        sum(case when vld.qty_available<0 then vld.qty_available * v.sell_price_inc_tax  else 0 end)
                                        FROM products as p
                                        left join variations as v on v.product_id = p.id
                                        left join variation_location_details as vld on vld.product_id = p.id
                                        WHERE p.brand_id = brands.id) as negative_inventory"),
                            //DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory'),
                            //DB::raw('sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
                        )
                ->groupBy('brands.id')
                ->get();
                $total_sale = $resultData->sum('unit_price');
                $total_invent = $resultData->sum('total_inventory');

            return view('report.show_child_brandreport')
                    ->with(compact('resultData', 'total_sale','total_invent','categoryId'));
        }
    }

    /**
     * Shows sale-tree product report data
     *
     * @return \Illuminate\Http\Response
     */
    public function getsaleTreeProductReport(Request $request){

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {

            $start_date = $request->get('startDate');
            $end_date = $request->get('endDate');

            // $start_date = $start_date." 00:00:00";
            // $end_date = $end_date." 23:59:59";

            $query = TransactionSellLine::join('variations','transaction_sell_lines.variation_id','=','variations.id')
            ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            ->rightJoin('products','products.id','=','variations.product_id')
            ->join('categories','categories.id','=','products.category_id')
            ->leftJoin('brands','products.brand_id','=','brands.id')
            ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                $start_date, $end_date])

            ->select('products.name as product_name',
                // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),
                DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),
                DB::raw('SUM(transaction_sell_lines.quantity) as quantity'),

                // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                // DB::raw('sum(variation_location_details.qty_available) as stock'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                // DB::raw('sum(variation_location_details.qty_available) * sum(variations.sell_price_inc_tax) as total_invent')
                DB::raw("(SELECT (SUM(transaction_sell_lines.quantity) *variations.sell_price_inc_tax)   FROM transactions where type='sell' AND transactions.id = transaction_sell_lines.transaction_id) as unit_price")
            )
            ->groupBy('products.id','variations.product_id','variations.sell_price_inc_tax','variation_location_details.qty_available') //'variations.product_id', 'variations.sell_price_inc_tax', 'variation_location_details.qty_available', 'transaction_sell_lines.transaction_id')
            ->get();

            $total_sale = $query->sum('unit_price');
            $total_invent = $query->sum('total_inventory');


            $query1 =
            TransactionSellLine::join('variations','transaction_sell_lines.variation_id','=','variations.id')
                ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                ->rightJoin('products','products.id','=','variations.product_id')
                ->join('categories','categories.id','=','products.category_id')
                ->leftJoin('brands','products.brand_id','=','brands.id') // we want null brands which is display under non-branded data
                ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                $start_date, $end_date]);


            // if (!empty($start_date) && !empty($end_date) ) {
            //     $start_date = $start_date." 00:00:00";
            //     $end_date = $end_date." 23:59:59";

            //     $query->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
            //         $start_date, $end_date]);
            // }

            if (!empty($request->input()['categoryId'])) {
                $query1->where('products.category_id','=', $request->input()['categoryId']);
            }else{
                //no products > display brand hide
            }


            if (!empty($request->input()['brandId'])) {
                $query1->where('products.brand_id','=', $request->input()['brandId']);
            }
            else{
                if($request->input()['nonBrandFlag'] == 1){

                  $query1->whereNull('products.brand_id');
                }

            }
            $resultData = $query1->select('products.name as product_name',
                DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),
                // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),
                // DB::raw('sum(variation_location_details.qty_available) as stock'),
                // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                // DB::raw('variations.sell_price_inc_tax as price'),
                DB::raw('SUM(transaction_sell_lines.quantity) as quantity'),
                DB::raw("(SELECT (SUM(transaction_sell_lines.quantity) *variations.sell_price_inc_tax)   FROM transactions where type='sell' AND transactions.id = transaction_sell_lines.transaction_id) as unit_price")
                )
                ->groupBy('products.id','variations.product_id','variations.sell_price_inc_tax','variation_location_details.qty_available')// 'variations.product_id', 'variations.sell_price_inc_tax', 'variation_location_details.qty_available', 'transaction_sell_lines.transaction_id')
                ->get();


            return view('report.show_child_productreport')->with(compact('resultData','total_sale','total_invent'));
        }
    }

    // get weekend report
    public function getWeekendReport(Request $request){

        if (request()->ajax()) {
            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }
            // $paymentDue = 0;
            // $paymentVendor = 0;
            // $purchaseOrder = 0;
            // $saleData = 0;
            // $gp = 0;
            //get vendor bill information
            $business_id = request()->session()->get('user.business_id');
            $paymentDue = $this->getPaymentDue($business_id,$start,$end);

            $paymentVendor = $this->getVendorPayment($business_id,$start,$end);
            $purchaseOrder = $this->getPurchaseOrder($business_id,$start,$end);
            $saleData = $this->getSaleData($business_id,$start,$end);
            $gp = $this->getAvgGpData($business_id,$start,$end);
            $saleDiscountData = $this->getSaleDiscountData($business_id,$start,$end);

            $dataArray = [];
            $dataArray['purchase_due'] = $paymentDue;
            $dataArray['vendor_payment'] = $paymentVendor;
            $dataArray['purchase_order'] = $purchaseOrder;
            $dataArray['sale_data'] = $saleData;
            $dataArray['gp'] = $gp;
            $dataArray['discount_data'] = $saleDiscountData;
            return json_encode($dataArray);
        }
        return view('report.weekend_report');
    }
    
    //get weekedn report data for ajax popup
    public function getWeekendReportforPopup(Request $request){

        if (request()->ajax()) {
            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }
            //get vendor bill information
            $business_id = request()->session()->get('user.business_id');
            $paymentDue = $this->getPaymentDue($business_id,$start,$end);

            $paymentVendor = $this->getVendorPayment($business_id,$start,$end);
            $purchaseOrder = $this->getPurchaseOrder($business_id,$start,$end);
            $saleData = $this->getSaleData($business_id,$start,$end);
            $gp = $this->getAvgGpData($business_id,$start,$end);
            $saleDiscountData = $this->getSaleDiscountData($business_id,$start,$end);

            $dataArray = [];
            $dataArray['purchase_due'] = $paymentDue;
            $dataArray['vendor_payment'] = $paymentVendor;
            $dataArray['purchase_order'] = $purchaseOrder;
            $dataArray['sale_data'] = $saleData;
            $dataArray['gp'] = $gp;
            $dataArray['discount_data'] = $saleDiscountData;
            return json_encode($dataArray);
        }
        return view('report.weekend_report');
    }

    //get discount data
    public function getSaleDiscountData($business_id,$start_date,$end_date){

        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $with = [];
        $sells = Transaction::select(
                    'discount_amount',
                    'total_before_tax',
                    'discount_type')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final');

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $sells->where('transactions.created_by', request()->session()->get('user.id'));
        }

        if (!empty($start_date) && !empty($end_date)) {
            $sells->whereDate('transactions.transaction_date', '>=', $start_date)
                        ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if ($is_woocommerce) {
            $sells->addSelect('transactions.woocommerce_order_id');
            if (request()->only_woocommerce_sells) {
                $sells->whereNotNull('transactions.woocommerce_order_id');
            }
        }

        $sells->groupBy('transactions.id');

        $result = $sells->get();
        $discountSum = $result->sum(function($row){
            $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

            if (!empty($discount) && $row->discount_type == 'percentage') {
                $discount = $row->total_before_tax * ($discount / 100);
            }

            return $discount;
        });
        return $discountSum;
    }

    //get payment due from tansaction
    public function getPaymentDue($business_id,$start,$end){

        $purchases = $this->transactionUtil->getListPurchases($business_id);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $purchases->whereIn('transactions.location_id', $permitted_locations);
        }
        if (!empty($start) && !empty($end)) {
            $purchases->whereDate('transactions.transaction_date', '>=', $start)
                        ->whereDate('transactions.transaction_date', '<=', $end);
        }

        if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
            $purchases->where('transactions.created_by', request()->session()->get('user.id'));
        }
        $purchaseResult = $purchases->get();

        $dueTotal = $purchaseResult->sum(function($row){
            if($row->discount_type && $row->discount_amount){
                if($row->discount_type == 'fixed'){
                    // $due = $row->final_total - $row->discount_amount - $row->amount_paid;
                    $due = $row->final_total - $row->amount_paid;
                }
                if($row->discount_type == 'percentage'){
                    $percentage_amt = ($row->amount_paid * $row->discount_amount) / 100;
                    $due = $row->final_total - $percentage_amt - $row->amount_paid;
                }

            }else if($row->is_advance == 1 && $row->payment_status == 'paid'){
                     $due =  $row->amount - $row->final_total - $row->advanceamt;
            }
            else{
                $due = $row->final_total - $row->amount_paid;
            }
            return $due;

        });

        return $dueTotal;
    }

     public function getAvgGpData($business_id,$start_date,$end_date){
        $transactions = Transaction::where('type','sell')
            ->where('status','final')
            ->where('business_id',$business_id)
            ->whereDate('transaction_date','>=',$start_date)
        ->whereDate('transaction_date','<=',$end_date);

        $total_before_tax = $transactions->sum('final_total');
        $discount = $transactions->sum('discount_amount');

        $cost = DB::table('transactions as t')
            ->join('transaction_sell_lines as tsl','t.id','=','tsl.transaction_id')
            ->where('t.type','sell')
            ->where('t.status','final')
            ->where('t.business_id',$business_id)
            ->whereDate('t.transaction_date','>=',$start_date)
            ->whereDate('t.transaction_date','<=',$end_date)
        ->sum(DB::raw('tsl.purchase_price * tsl.quantity'));

        $divisable = $total_before_tax - $cost;
        $divider = $total_before_tax;
        if($divider == 0){
            $divider = 1;
        }
        $new_gp = round((($divisable) * 100)/($divider),2);
        return $new_gp;
    }
    //get average gp data from sales-new
    public function getAvgGpData_old($business_id,$start_date,$end_date){
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
        $with = [];
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $sells = $this->transactionUtil->getListSells_New($business_id);
        $myquery = $this->transactionUtil->getListSells_New1($business_id);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $myquery .= ' AND transactions.location_id = "'.$permitted_locations.'" ';
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $myquery .= ' AND transactions.created_by = "'.request()->session()->get('user.id').'" ';
            $sells->where('transactions.created_by', request()->session()->get('user.id'));
        }

        if (!empty($start_date) && !empty($end_date)) {
            $myquery .= 'AND transactions.transaction_date BETWEEN "'.$start_date.'" AND "'.$end_date.'" ';
            $sells->whereDate('transactions.transaction_date', '>=', $start_date)
                        ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if ($is_woocommerce) {
            $addSelect = 'transactions.woocommerce_order_id';
            $sells->addSelect('transactions.woocommerce_order_id');
            if (request()->only_woocommerce_sells) {
                $myquery .= ' AND transactions.woocommerce_order_id IS NOT NULL ';
                $sells->whereNotNull('transactions.woocommerce_order_id');
            }
        }

        $sells->groupBy('transactions.id');

        $with[] = 'payment_lines';
        if (!empty($with)) {
            $sells->with($with);
        }

        //$business_details = $this->businessUtil->getDetails($business_id);
        if ($this->businessUtil->isModuleEnabled('subscription')) {
            $addSelect = 'transactions.is_recurring, transactions.recur_parent_id ';
            $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
        }

        $myquery .= ' GROUP BY transactions.id,SR.final_total,SR.id';
        $result = $sells->get();

        $dueSum = $result->sum(function($row){
            $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
            $data =  DB::table('transaction_sell_lines')
                        ->select(DB::raw("(1-((sum(purchase_price*quantity))/(sum(unit_price_inc_tax*quantity)-$discount)))*100 as GP"))
                        ->where('transaction_id',$row->id)->first();
            $gross_profit = round($data->GP,2);
                        $gp = $gross_profit.'%';
            return $gross_profit;
        });
        $avg = count($result);
        if($avg!=0){
            return ($dueSum/$avg);
        }
        return $dueSum;

    }

    //get sale data
    public function getSaleData($business_id,$start_date,$end_date){
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $with = [];
        $sells = Transaction::select(DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                    TP.transaction_id=transactions.id) as total_paid'),
                    'discount_amount',
                    'discount_type',
                    'total_before_tax',
                    'final_total')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final');

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $sells->where('transactions.created_by', request()->session()->get('user.id'));
        }

        if (!empty($start_date) && !empty($end_date)) {
            $sells->whereDate('transactions.transaction_date', '>=', $start_date)
                        ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if ($is_woocommerce) {
            $sells->addSelect('transactions.woocommerce_order_id');
            if (request()->only_woocommerce_sells) {
                $sells->whereNotNull('transactions.woocommerce_order_id');
            }
        }

        $sells->groupBy('transactions.id');

        $result = $sells->get();
        $dueSum = $result->sum(function($row){
            $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

            if (!empty($discount) && $row->discount_type == 'percentage') {
                $discount = $row->total_before_tax * ($discount / 100);
            }

            return ($row->final_total + $discount);
        });
        return $dueSum;
    }

    //get purchase order
    public function getPurchaseOrder($business_id,$start_date,$end_date){
        $purchases = $this->transactionUtil->getListPurchasesOrder($business_id);

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $purchases->whereIn('transactions.location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $purchases->whereDate('transactions.transaction_date', '>=', $start_date)
                        ->whereDate('transactions.transaction_date', '<=', $end_date);
        }

        if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
            $purchases->where('transactions.created_by', request()->session()->get('user.id'));
        }
        $result = $purchases->get();

        $dueSum = $result->sum(function($row){
            return $row->final_total;
        });
        return $dueSum;
    }

    //get vendor payment(payment to vendor)
    public function getVendorPayment($business_id,$start_date,$end_date){
            $supplier_id = null;
            $contact_filter1 = '';
            $contact_filter2 = '';

            $location_id = null;

            $parent_payment_query_part = empty($location_id) ? "AND transaction_payments.parent_id IS NULL" : "";

            $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
                $join->on('transaction_payments.transaction_id', '=', 't.id')
                    ->where('t.business_id', $business_id)
                    ->whereIn('t.type', ['purchase', 'opening_balance']);
            })
            ->where('transaction_payments.business_id', $business_id)
            ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
                $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('purchase', 'opening_balance')  $parent_payment_query_part $contact_filter1)")
                    ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('purchase', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
            })
            ->select(
                DB::raw("IF(transaction_payments.transaction_id IS NULL,
                            (SELECT c.name FROM transactions as ts
                            JOIN contacts as c ON ts.contact_id=c.id
                            WHERE ts.id=(
                                    SELECT tps.transaction_id FROM transaction_payments as tps
                                    WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                )
                            ),
                            (SELECT c.name FROM transactions as ts JOIN
                                contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=t.id
                            )
                        ) as supplier"),
                'transaction_payments.is_advance',
                'transaction_payments.advance_amt',
                'transaction_payments.amount',
                'method',
                'paid_on',
                'transaction_payments.payment_ref_no',
                'transaction_payments.document',
                't.ref_no',
                't.id as transaction_id',
                'cheque_number',
                'card_transaction_number',
                'bank_account_number',
                'transaction_no',
                'transaction_payments.id as DT_RowId'
            )
            ->groupBy('transaction_payments.id');


        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
        }

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }
        $result = $query->get();

        $dueSum = $result->sum(function($row){
            return $row->amount;
        });

        $dueSumAdvance = $result->sum(function($row){
            if($row->is_advance === 1){
                return $row->advance_amt;
            }else{
                return 0;
            }
        });

        return ($dueSum-$dueSumAdvance);
    }

    //// New Function
    public function getCategorywisesalereport(Request $request){
            $business_id = request()->session()->get('user.business_id');
       if ($request->ajax()) {
           $variation_id = $request->get('variation_id', null);
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";
            //$start_date = "2022-02-11 00:00:00";
            //$end_date = "2022-02-11 23:59:59";
            DB::enableQueryLog();
            // $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
            // ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            // ->join('products','products.id','=','variations.product_id')
            // ->join('categories','categories.id','=','products.category_id')
            // ->join('brands','products.brand_id','=','brands.id')
            // ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
            //     $start_date, $end_date])
            // ->select('categories.name as category_name',
            //         DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
            //            xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx     sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
            //                 'transaction_sell_lines.quantity as quantity',
            //         DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
            //         DB::raw("(select variation_location_details.qty_available)as total_quantity"),
            //     )
            // ->groupBy('categories.id')->get();

            //$query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')

                    $query =  DB::table('categories')
                        ->leftJoin('products','products.category_id','=','categories.id')
                        ->leftJoin('variations','variations.product_id','=','products.id')
                        ->leftJoin('variation_location_details', function($joins){
                                $joins->on('variation_location_details.product_id', '=', 'products.id')
                                ->where('variation_location_details.id', '=', (
                                      DB::raw("(SELECT MAX(id)
                                      FROM variation_location_details
                                      WHERE variation_id = variations.id)")
                                ));
                        })
                        ->leftJoin('transaction_sell_lines', function($join) use($start_date, $end_date){
                                $join->on('transaction_sell_lines.product_id', '=', 'products.id')
                                ->whereBetween('transaction_sell_lines.created_at', array($start_date, $end_date));
                                //->whereIn('transaction_sell_lines.id',array(715513,715514,715515,715516);
                        })
                        //->leftJoin('variation_location_details','variation_location_details.product_id','=','products.id')
                        //->leftJoin('transaction_sell_lines','transaction_sell_lines.product_id','=','products.id')
                        ->select(
                                    'categories.name as category_name',
                                    'variation_location_details.qty_available as total_quantity',
                                    'transaction_sell_lines.quantity as quantity',
                                    'transaction_sell_lines.*',
                                    DB::raw("(SELECT sum(transaction_sell_lines.quantity) )as quant"),
                                    DB::raw("(SELECT sum(transaction_sell_lines.quantity *variations.sell_price_inc_tax)  ) as unit_price"),
                                    DB::raw("(select
                                                sum(case when vld.qty_available>0 then vld.qty_available *v.sell_price_inc_tax else 0 end)
                                                FROM products as p
                                                left join variations as v on v.product_id = p.id
                                                left join variation_location_details as vld on vld.product_id = p.id
                                                WHERE p.category_id = categories.id)  as total_inventory"),
                                    DB::raw("(select
                                                sum(case when vld.qty_available<0 then vld.qty_available * v.sell_price_inc_tax  else 0 end)
                                                FROM products as p
                                                left join variations as v on v.product_id = p.id
                                                left join variation_location_details as vld on vld.product_id = p.id
                                                WHERE p.category_id = categories.id) as negative_inventory"),
                                    //DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory'),
                                    //DB::raw('sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
                                )
                        ->groupBy('categories.id')
                        ->get();

            /*}else{
                $query =  DB::table('transaction_sell_lines')
                        ->join('variations','variations.id','=','transaction_sell_lines.variation_id')
                        ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                        ->join('products','products.id','=','variations.product_id')
                        ->join('categories','categories.id','=','products.category_id')
                       //->join('brands','products.brand_id','=','brands.id')
                     ->select("transaction_sell_lines.*",
                        DB::raw("(SELECT sum(transaction_sell_lines.quantity) )as quant"),
                        'categories.name as category_name',
                        DB::raw("(SELECT sum(vld.qty_available) as sm FROM products as p
                                left join variation_location_details as vld on vld.product_id =p.id
                                WHERE p.category_id = categories.id) as total_inventory"),
                        DB::raw('sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
                                'transaction_sell_lines.quantity as quantity',
                        DB::raw("(SELECT sum(transaction_sell_lines.quantity *variations.sell_price_inc_tax)  ) as unit_price"),
                        DB::raw("(select variation_location_details.qty_available)as total_quantity"),
                    )
                    ->where('variation_location_details.id', '=', (
                                  DB::raw("(SELECT MAX(id)
                                  FROM variation_location_details
                                  WHERE variation_id = variations.id)")
                               ))
                    //->whereIn('transaction_sell_lines.id',array(727809, 725414, 727424, 728005) )
                    ->whereBetween('transaction_sell_lines.created_at', array($start_date, $end_date))

                ->groupBy('categories.id')
                ->get();
            }*/
            //sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
            //dd($query);
            // $querytotal = DB::select("select

            //             *, c.name as category_name, (tsl.quantity * v.sell_price_inc_tax) as unit_price,
            //               tsl.quantity as quantity, vld.qty_available as total_quantity
            //         from transaction_sell_lines as tsl
            //         left join variations as v on v.id = tsl.variation_id
            //         left join products as p on p.id = tsl.product_id
            //         left join categories as c on c.id = p.category_id
            //         left join variation_location_details as vld on vld.product_id = p.id
            //         where tsl.created_at BETWEEN '".$start_date."' and '".$end_date."'")->first();


            // \Log::info(DB::getQueryLog());



            // sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
           //Original Query Start//
            //  $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
            //         ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            //         ->join('products','products.id','=','variations.product_id')
            //         ->join('categories','categories.id','=','products.category_id')
            //         ->join('brands','products.brand_id','=','brands.id')
            //         ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [$start_date, $end_date])
            //         ->select('categories.name as category_name',
            //             DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
            //                     sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),'transaction_sell_lines.quant as quantity',
            //             DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
            //             DB::raw("(select variation_location_details.qty_available)as total_quantity"),
            //         )
            //         ->groupBy('categories.id')->get();
            //         //Original Query Ends//
            //         $query =             DB::table('categories')
            // ->join('products','products.category_id','=','categories.id')
            // ->join('variations','products.id','=','variations.product_id')
            // ->join('brands','products.brand_id','=','brands.id')
            //  ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            //  ->join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
            //  ->select('categories.name as category_name',
            //         DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
            //                 sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),'transaction_sell_lines.quant as quantity',
            //         DB::raw("(SELECT sum(case when transaction_sell_lines.created_at BETWEEN '$start_date' AND '$end_date' then transaction_sell_lines.quant *variations.sell_price_inc_tax else 0 end)) as unit_price"),
            //          DB::raw("(SELECT sum(case when transaction_sell_lines.created_at BETWEEN '$start_date' AND '$end_date' then transaction_sell_lines.quant *transaction_sell_lines.unit_price else 0 end)) as unit_price"),
            //         DB::raw("(select variation_location_details.qty_available where transaction_sell_lines.created_at BETWEEN '$start_date' AND '$end_date')as total_quantity"),
            //     )
            // ->groupBy('categories.id')->get();

            //  $inventory_query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
            //         ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            //         ->join('products','products.id','=','variations.product_id')
            //         ->join('categories','categories.id','=','products.category_id')
            //         ->join('brands','products.brand_id','=','brands.id')
            //         ->select(DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory'),)
            //         ->groupBy('categories.id')->toSql();

            $total_sale = $query->sum('unit_price');
            //  $total_sale = DB::select("select

            //             sum(tsl.quantity * v.sell_price_inc_tax) as unit_price
            //         from transaction_sell_lines as tsl
            //         left join variations as v on v.id = tsl.variation_id
            //         left join products as p on p.id = tsl.product_id
            //         left join categories as c on c.id = p.category_id
            //         where tsl.created_at BETWEEN '2022-02-11 00:00:00'and '2022-02-12 00:00:00'");

             $total_invent = $query->sum('total_inventory');
          $datatable = Datatables::of($query)

                        ->editColumn('category_name', function ($row) {
                            return '<span class=""  data-orig-value="' . $row->category_name . '">'. $row->category_name . '</span>';
                        })

                        ->editColumn('total_sold_price', function ($row) {
                            return '<span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="' . $row->unit_price . '">' . $row->unit_price . '</span>';
                        })

                       ->editColumn('quantity', function ($row) {
                            return '<span class="total_quantity"  data-orig-value="' . $row->quantity . '">'. $row->quantity . '</span>';
                         })

                        //  ->editColumn('total_stock_price', function ($row) {
                        //     return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';
                        // })

                        ->editColumn('sale_percentage', function ($row) use($total_sale) {
                            $unit_price = $row->unit_price;
                            $percent =  ($unit_price*100)/$total_sale;
                            $sale_percentage = round($percent,2);
                            return '<span class="total-sale-percent" data-orig-value="' . $sale_percentage . '">'. $sale_percentage .'% </span>';
                        })

                        ->editColumn('total_quantity', function ($row) {
                            return '<span class="quantity"  data-orig-value="' . $row->total_quantity . '">'. $row->total_quantity . '</span>';
                        })

                        ->editColumn('negative_inventory', function ($row) {
                            return '<span class="display_currency total_negative_inventory" data-currency_symbol="true" data-orig-value="' . $row->negative_inventory . '">' . $row->negative_inventory . '</span>';
                        })

                        ->editColumn('inventory_percentage', function ($row) use($total_invent) {
                          $percent =  ($row->total_inventory*100)/$total_invent;
                            $inventory_percent = round($percent,2);
                            return '<span class="total-inventory-percent" data-orig-value="' . $inventory_percent . '">'. $inventory_percent .'% </span>';
                        })

                        ->editColumn('difference', function ($row) use($total_invent,$total_sale) {

                            $percent =  ($row->unit_price*100)/$total_sale;
                            $sale_percentage = round($percent,2);

                            $percent =  ($row->total_inventory*100)/$total_invent;
                            $inventory_percent = round($percent,2);

                            $difference = $inventory_percent - $sale_percentage;

                            return '<span class="" data-orig-value="' . $difference . '">'. $difference .'% </span>';
                        });

             $raw_columns  = ['category_name', 'total_sold_price','negative_inventory','total_quantity','quantity','sale_percentage','inventory_percentage','difference'];


            $datatable->editColumn('total_stock_price', function ($row) {
                            return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';
                        });



            $raw_columns[] = 'total_stock_price';


            return $datatable->rawColumns($raw_columns)->make(true);
        }

        return view('report.category_wise_sale_report');
    }

    // public function getBrandwisesalereport(Request $request){

    //     if ($request->ajax()) {

    //       $variation_id = $request->get('variation_id', null);
    //         $start_date = $request->get('start_date');
    //         $end_date = $request->get('end_date');

    //          $start_date = $start_date." 00:00:00";
    //         $end_date = $end_date." 23:59:59";
    //         DB::enableQueryLog();
    //         $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
    //         ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
    //         ->join('products','products.id','=','variations.product_id')
    //         ->join('categories','categories.id','=','products.category_id')
    //         ->join('brands','products.brand_id','=','brands.id')
    //         ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
    //             $start_date, $end_date])
    //         ->select('brands.name as brand_name',
    //                 DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
    //                         sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),'transaction_sell_lines.quant as quantity',
    //                 DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
    //                 DB::raw("(select variation_location_details.qty_available)as total_quantity"),
    //             )
    //         ->groupBy('products.brand_id')->get();

    //          \Log::info(DB::getQueryLog());

    //          $total_sale = $query->sum('unit_price');
    //          $total_invent = $query->sum('total_inventory');

    //         // sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
    //       //Original Query Start//
    //          $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
    //                 ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
    //                 ->join('products','products.id','=','variations.product_id')
    //                 ->join('categories','categories.id','=','products.category_id')
    //                 ->join('brands','products.brand_id','=','brands.id')
    //                 ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [$start_date, $end_date])
    //                 ->select('brands.name as brand_name',
    //                     DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
    //                             sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),'transaction_sell_lines.quant as quantity',
    //                     DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
    //                     DB::raw("(select variation_location_details.qty_available)as total_quantity"),
    //                 )
    //                 ->groupBy('products.brand_id')->get();


    //                 //Original Query Ends//


    //         $query = DB::table('brands')
    //             ->join('products','products.brand_id','=','brands.id')
    //             ->join('variations','products.id','=','variations.product_id')
    //             ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
    //             ->join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
    //             ->select('brands.name as brand_name','transaction_sell_lines.created_at as sell_created_at',
    //                 DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
    //                         sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),'transaction_sell_lines.quant as quantity',
    //                 DB::raw("(SELECT sum(case when transaction_sell_lines.created_at BETWEEN '$start_date' AND '$end_date' then transaction_sell_lines.quant *variations.sell_price_inc_tax else 0 end)) as unit_price"),
    //                 DB::raw("(select variation_location_details.qty_available where transaction_sell_lines.created_at BETWEEN '$start_date' AND '$end_date')as total_quantity"),
    //             )
    //             // ->whereBetween('transaction_sell_lines.created_at', [$start_date, $end_date])
    //         ->groupBy('brands.id')->get();

    //         // $myCount = 0;
    //         foreach($query as $key => $value){

    //             // if($value->sell_created_at <= $start_date || $value->sell_created_at >= $end_date){
    //             // if($value->sell_created_at >= $start_date || $value->sell_created_at <= $end_date){
    //             //     // unset($query[$key]);
    //             //     // echo '<br> '. $value->brand_name.' created at: '.$value->sell_created_at ;
    //             //     $myCount++;
    //             // }

    //             // if($value->unit_price <= 0 ){
    //             //     unset($query[$key]);
    //             // }

    //         }

    //          $inventory_query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
    //                 ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
    //                 ->join('products','products.id','=','variations.product_id')
    //                 ->join('categories','categories.id','=','products.category_id')
    //                 ->join('brands','products.brand_id','=','brands.id')
    //                 ->select(DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory'),)
    //                 ->groupBy('categories.id')->toSql();


    //                     $datatable = Datatables::of($query)

    //                     ->editColumn('brand_name', function ($row) {
    //                         return '<span class=""  data-orig-value="' . $row->brand_name . '">'. $row->brand_name . '</span>';
    //                     })

    //                     ->editColumn('total_sold_price', function ($row) {
    //                         return '<span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="' . $row->unit_price . '">' . $row->unit_price . '</span>';
    //                     })

    //                   ->editColumn('quantity', function ($row) {
    //                         return '<span class="total_quantity"  data-orig-value="' . $row->quantity . '">'. $row->quantity . '</span>';
    //                      })

    //                     //  ->editColumn('total_stock_price', function ($row) {
    //                     //     return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';
    //                     // })

    //                     ->editColumn('sale_percentage', function ($row) use($total_sale) {
    //                         $unit_price = $row->unit_price;
    //                         $percent =  ($unit_price*100)/$total_sale;
    //                         $sale_percentage = round($percent,2);
    //                         return '<span class="total-sale-percent" data-orig-value="' . $sale_percentage . '">'. $sale_percentage .'% </span>';
    //                     })

    //                     ->editColumn('total_quantity', function ($row) {
    //                         return '<span class="quantity"  data-orig-value="' . $row->total_quantity . '">'. $row->total_quantity . '</span>';
    //                     })

    //                     ->editColumn('negative_inventory', function ($row) {
    //                         return '<span class="display_currency total_negative_inventory" data-currency_symbol="true" data-orig-value="' . $row->negative_inventory . '">' . $row->negative_inventory . '</span>';
    //                     })

    //                     ->editColumn('inventory_percentage', function ($row) use($total_invent) {
    //                       $percent =  ($row->total_inventory*100)/$total_invent;
    //                         $inventory_percent = round($percent,2);
    //                         return '<span class="total-inventory-percent" data-orig-value="' . $inventory_percent . '">'. $inventory_percent .'% </span>';
    //                     })

    //                     ->editColumn('difference', function ($row) use($total_invent,$total_sale) {

    //                         $percent =  ($row->unit_price*100)/$total_sale;
    //                         $sale_percentage = round($percent,2);

    //                         $percent =  ($row->total_inventory*100)/$total_invent;
    //                         $inventory_percent = round($percent,2);

    //                         $difference = $inventory_percent - $sale_percentage;

    //                         return '<span class="" data-orig-value="' . $difference . '">'. $difference .'% </span>';
    //                     });

    //          $raw_columns  = ['brand_name', 'total_sold_price','negative_inventory','total_quantity','quantity','sale_percentage','inventory_percentage','difference'];


    //         $datatable->editColumn('total_stock_price', function ($row) {
    //                         return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';
    //                     });

    //         $raw_columns[] = 'total_stock_price';


    //         return $datatable->rawColumns($raw_columns)->make(true);

    //     }

    //   return view('report.brand_wise_sale_report');
    // }

        /////// Original Function
    public function getBrandwisesalereport(Request $request){

        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

             $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";



                // $query =  DB::table('brands')
                //     ->join('products','products.brand_id','=','brands.id')
                //     ->join('variations','variations.product_id','=','products.id')
                //     ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                //     ->join('categories','categories.id','=','products.category_id')
                //     ->join('transaction_sell_lines','transaction_sell_lines.product_id','=','products.id')
                //     ->select("transaction_sell_lines.*",
                //     DB::raw("(SELECT sum(transaction_sell_lines.quantity) )as quant"),
                //     'brands.name as brand_name',
                //     DB::raw("(SELECT sum(vld.qty_available) as sm FROM products as p
                //             left join variation_location_details as vld on vld.product_id =p.id
                //             WHERE p.category_id = categories.id) as total_inventory"),
                //     DB::raw('sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
                //             'transaction_sell_lines.quantity as quantity',
                //     DB::raw("(SELECT sum(transaction_sell_lines.quantity *variations.sell_price_inc_tax)  ) as unit_price"),
                //     DB::raw("(select variation_location_details.qty_available)as total_quantity"),
                //     )
                //     ->where('variation_location_details.id', '=', (
                //               DB::raw("(SELECT MAX(id)
                //               FROM variation_location_details
                //               WHERE variation_id = variations.id)")
                //           ))
                //     //->whereIn('transaction_sell_lines.id',array(715648,715649,715650,715516,715517,715518,715520) )
                //     ->whereBetween('transaction_sell_lines.created_at', array($start_date, $end_date))
                //     ->groupBy('brands.id')
                //     ->get();

                //     $total_sale = $query->sum('unit_price');
                //     $total_invent = $query->sum('total_inventory');

            $query =  DB::table('brands')
                ->leftJoin('products','products.brand_id','=','brands.id')
                ->leftJoin('variations','variations.product_id','=','products.id')
                ->leftJoin('variation_location_details', function($joins){
                        $joins->on('variation_location_details.product_id', '=', 'products.id')
                        ->where('variation_location_details.id', '=', (
                              DB::raw("(SELECT MAX(id)
                              FROM variation_location_details
                              WHERE variation_id = variations.id)")
                        ));
                })
                ->leftJoin('transaction_sell_lines', function($join) use($start_date, $end_date){
                        $join->on('transaction_sell_lines.product_id', '=', 'products.id')
                        ->whereBetween('transaction_sell_lines.created_at', array($start_date, $end_date));
                        //->whereIn('transaction_sell_lines.id',array(715513,715514,715515,715516);
                })
                //->leftJoin('variation_location_details','variation_location_details.product_id','=','products.id')
                //->leftJoin('transaction_sell_lines','transaction_sell_lines.product_id','=','products.id')
                ->select(
                            'brands.name as brand_name',
                            'variation_location_details.qty_available as total_quantity',
                            'transaction_sell_lines.quantity as quantity',
                            //'transaction_sell_lines.*',
                            DB::raw("(SELECT sum(transaction_sell_lines.quantity) )as quant"),
                            DB::raw("(SELECT sum(transaction_sell_lines.quantity *variations.sell_price_inc_tax)  ) as unit_price"),
                            DB::raw("(select
                                        sum(case when vld.qty_available>0 then vld.qty_available *v.sell_price_inc_tax else 0 end)
                                        FROM products as p
                                        left join variations as v on v.product_id = p.id
                                        left join variation_location_details as vld on vld.product_id = p.id
                                        WHERE p.brand_id = brands.id)  as total_inventory"),
                            DB::raw("(select
                                        sum(case when vld.qty_available<0 then vld.qty_available * v.sell_price_inc_tax  else 0 end)
                                        FROM products as p
                                        left join variations as v on v.product_id = p.id
                                        left join variation_location_details as vld on vld.product_id = p.id
                                        WHERE p.brand_id = brands.id) as negative_inventory"),
                            //DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory'),
                            //DB::raw('sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
                        )
                ->groupBy('brands.id')
                ->get();

               /* $query =  DB::table('transaction_sell_lines')
                    ->join('variations','variations.id','=','transaction_sell_lines.variation_id')
                    ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                    ->join('products','products.id','=','variations.product_id')
                    ->join('categories','categories.id','=','products.category_id')
                    ->join('brands','products.brand_id','=','brands.id')
                    ->select("transaction_sell_lines.*",
                    DB::raw("(SELECT sum(transaction_sell_lines.quantity) )as quant"),
                    'brands.name as brand_name',
                    DB::raw("(SELECT sum(vld.qty_available) as sm FROM products as p
                            left join variation_location_details as vld on vld.product_id =p.id
                            WHERE p.category_id = categories.id) as total_inventory"),
                    DB::raw('sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory'),
                            'transaction_sell_lines.quantity as quantity',
                    DB::raw("(SELECT sum(transaction_sell_lines.quantity *variations.sell_price_inc_tax)  ) as unit_price"),
                    DB::raw("(select variation_location_details.qty_available)as total_quantity"),
                    )
                    ->where('variation_location_details.id', '=', (
                              DB::raw("(SELECT MAX(id)
                              FROM variation_location_details
                              WHERE variation_id = variations.id)")
                           ))
                    //->whereIn('transaction_sell_lines.id',array(727809, 725414, 727424, 728005) )
                    ->whereBetween('transaction_sell_lines.created_at', array($start_date, $end_date))

                    ->groupBy('brands.id')
                    ->get(); */

                    $total_sale = $query->sum('unit_price');
                    $total_invent = $query->sum('total_inventory');


                 /*$query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
                ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                ->join('products','products.id','=','variations.product_id')
                ->join('categories','categories.id','=','products.category_id')
                ->join('brands','products.brand_id','=','brands.id')
               // ->where('products.not_for_selling','=','0')
                // ->where('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory','>=','0')
                ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                    $start_date, $end_date])
                ->select('brands.name as brand_name',

                DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
                        sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory
                '),
                // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),

                // DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),

                // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) <=  0 as negative_inventory'),

                // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                // DB::raw('sum(variation_location_details.qty_available) as stock'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                'transaction_sell_lines.quant as quantity',
                // DB::raw('sum(variation_location_details.qty_available * variations.sell_price_inc_tax) as total_invent')
                DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
                // DB::raw("(SELECT sum(variations.sell_price_inc_tax * variation_location_details.qty_available) FROM categories where  categories.id = products.category_id) as total_inventory")
                // DB::raw("(SELECT SUM(vld.qty_available *variations.sell_price_inc_tax) FROM variation_location_details as vld WHERE vld.variation_id=variations.id $vld_str) as total_inventory"),
                DB::raw("(select variation_location_details.qty_available)as total_quantity"),
                )
                ->groupBy('products.brand_id')->get();



                 $total_sale = $query->sum('unit_price');
                 $total_invent = $query->sum('total_inventory');

                $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
                ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                ->join('products','products.id','=','variations.product_id')
                ->join('categories','categories.id','=','products.category_id')
                ->join('brands','products.brand_id','=','brands.id')
               // ->where('products.not_for_selling','=','0')
                // ->where('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory','>=','0')
                ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                    $start_date, $end_date])
                ->select('brands.name as brand_name',

                DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
                        sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory
                '),
                // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),

                // DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),

                // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) <=  0 as negative_inventory'),

                // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                // DB::raw('sum(variation_location_details.qty_available) as stock'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                'transaction_sell_lines.quant as quantity',
                // DB::raw('sum(variation_location_details.qty_available * variations.sell_price_inc_tax) as total_invent')
                DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
                // DB::raw("(SELECT sum(variations.sell_price_inc_tax * variation_location_details.qty_available) FROM categories where  categories.id = products.category_id) as total_inventory")
                // DB::raw("(SELECT SUM(vld.qty_available *variations.sell_price_inc_tax) FROM variation_location_details as vld WHERE vld.variation_id=variations.id $vld_str) as total_inventory"),
                DB::raw("(select variation_location_details.qty_available)as total_quantity"),
                )
                ->groupBy('products.brand_id')->get();


                if (!empty($start_date) && !empty($end_date) ) {
                    $start_date = $start_date." 00:00:00";
                    $end_date = $end_date." 23:59:59";

                    $query->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                        $start_date, $end_date]);
                }*/


            return Datatables::of($query)
                ->editColumn('brand_name', function ($row) {
                    return '<span class=""  data-orig-value="' . $row->brand_name . '">'. $row->brand_name . '</span>';
                })
                ->editColumn('total_sold_price', function ($row) {
                    // $unit_price = $row->unit_price;
                    // $stock = $row->total_sold;
                    // $total_stock_price = $stock * $unit_price;
                    return '<span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="' . $row->unit_price . '">' . $row->unit_price . '</span>';

                    // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
                })
                ->editColumn('sale_percentage', function ($row) use($total_sale) {
                    $unit_price = $row->unit_price;
                    $percent =  ($unit_price*100)/$total_sale;
                    $sale_percentage = round($percent,2);
                    return '<span class="total-sale-percent" data-orig-value="' . $sale_percentage . '">'. $sale_percentage .'% </span>';
                })
                ->editColumn('total_stock_price', function ($row) {
                    // $unit_price = $row->price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';

                    // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
                })

                ->editColumn('negative_inventory', function ($row) {
                    return '<span class="display_currency total_negative_inventory" data-currency_symbol="true" data-orig-value="' . $row->negative_inventory . '">' . $row->negative_inventory . '</span>';
                })
                ->editColumn('inventory_percentage', function ($row) use($total_invent) {
                    // $unit_price = $row->price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    $percent =  ($row->total_inventory*100)/$total_invent;
                    $inventory_percent = round($percent,2);
                    return '<span class="total-inventory-percent" data-orig-value="' . $inventory_percent . '">'. $inventory_percent .'% </span>';
                })
                ->editColumn('difference', function ($row) use($total_invent,$total_sale) {
                    // $unit_price = $row->unit_price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    // $percent =  ($total_stock_price*100)/$total_invent;
                    // $inventory_percent = round($percent,2);

                    // $percent =  ($unit_price*100)/$total_sale;
                    // $sale_percentage = round($percent,2);

                    $percent =  ($row->unit_price*100)/$total_sale;
                    $sale_percentage = round($percent,2);

                    $percent =  ($row->total_inventory*100)/$total_invent;
                    $inventory_percent = round($percent,2);

                    $difference = $inventory_percent - $sale_percentage;

                    return '<span class="" data-orig-value="' . $difference . '">'. $difference .'% </span>';
                })
          //      ->filterColumn('brand_name', function ($query, $keyword) {
            //        $query->where('brands.name', 'like', "%{$keyword}%");
              //  })

                ->rawColumns(['brand_name','negative_inventory', 'total_sold_price','sale_percentage','total_stock_price','inventory_percentage','difference'])
                ->make(true);
        }

       return view('report.brand_wise_sale_report');
    }



    public function getBrandwisesalereportNew(Request $request){

        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

             $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

             $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
            ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            ->join('products','products.id','=','variations.product_id')
            ->join('categories','categories.id','=','products.category_id')
            ->join('brands','products.brand_id','=','brands.id')
          // ->where('products.not_for_selling','=','0')
            // ->where('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory','>=','0')
            ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                $start_date, $end_date])
            ->select('brands.name as brand_name',

            DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
                    sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory
            '),
            // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),

            // DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),

            // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) <=  0 as negative_inventory'),

            // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
            // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
            // DB::raw('sum(variation_location_details.qty_available) as stock'),
            // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
            'transaction_sell_lines.quant as quantity',
            // DB::raw('sum(variation_location_details.qty_available * variations.sell_price_inc_tax) as total_invent')
            DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
            // DB::raw("(SELECT sum(variations.sell_price_inc_tax * variation_location_details.qty_available) FROM categories where  categories.id = products.category_id) as total_inventory")
            // DB::raw("(SELECT SUM(vld.qty_available *variations.sell_price_inc_tax) FROM variation_location_details as vld WHERE vld.variation_id=variations.id $vld_str) as total_inventory"),
            DB::raw("(select variation_location_details.qty_available)as total_quantity"),
            )
            ->groupBy('products.brand_id')->get();



             $total_sale = $query->sum('unit_price');
             $total_invent = $query->sum('total_inventory');

            $query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
            ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            ->join('products','products.id','=','variations.product_id')
            ->join('categories','categories.id','=','products.category_id')
            ->join('brands','products.brand_id','=','brands.id')
          // ->where('products.not_for_selling','=','0')
            // ->where('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory','>=','0')
            ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                $start_date, $end_date])
            ->select('brands.name as brand_name','brands.id as brand_id',

            DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
                    sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory
            '),
            // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),

            // DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),

            // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) <=  0 as negative_inventory'),

            // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
            // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
            // DB::raw('sum(variation_location_details.qty_available) as stock'),
            // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
            'transaction_sell_lines.quant as quantity',
            // DB::raw('sum(variation_location_details.qty_available * variations.sell_price_inc_tax) as total_invent')
            DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
            // DB::raw("(SELECT sum(variations.sell_price_inc_tax * variation_location_details.qty_available) FROM categories where  categories.id = products.category_id) as total_inventory")
            // DB::raw("(SELECT SUM(vld.qty_available *variations.sell_price_inc_tax) FROM variation_location_details as vld WHERE vld.variation_id=variations.id $vld_str) as total_inventory"),
            DB::raw("(select variation_location_details.qty_available)as total_quantity"),
            )
            ->groupBy('products.brand_id')->get();


            if (!empty($start_date) && !empty($end_date) ) {
                $start_date = $start_date." 00:00:00";
                $end_date = $end_date." 23:59:59";

                $query->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                    $start_date, $end_date]);
            }


 //////////////// Query for inventory
            $cur_year = date('Y');
            $inv_start = $cur_year.'-01-01 00:00:00';
            $inv_end = $cur_year.'-12-31 23:59:59';

            $inv_query = Variation::join(DB::raw('(select *,sum(quantity) as quant from transaction_sell_lines group by variation_id) as transaction_sell_lines'),'transaction_sell_lines.variation_id','=','variations.id')
            ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            ->join('products','products.id','=','variations.product_id')
            ->join('categories','categories.id','=','products.category_id')
            ->join('brands','products.brand_id','=','brands.id')
            ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                $inv_start, $inv_end])
            ->select('brands.name as brand_name','brands.id as brand_id',

            DB::raw('sum(case when variation_location_details.qty_available>0 then variation_location_details.qty_available *variations.sell_price_inc_tax else 0 end) as total_inventory,
                    sum(case when variation_location_details.qty_available<0 then variation_location_details.qty_available *variations.sell_price_inc_tax  else 0 end) as negative_inventory
            '),
            'transaction_sell_lines.quant as quantity',
            DB::raw("(SELECT sum(transaction_sell_lines.quant *variations.sell_price_inc_tax)  ) as unit_price"),
            DB::raw("(select variation_location_details.qty_available)as total_quantity"),
            )
            ->groupBy('products.brand_id')->get();


            foreach($query as $val){
                foreach($inv_query as $inv_val){
                    if($val->brand_id == $inv_val->brand_id){
                        $val->total_inventory = $inv_val->total_inventory;

                    }
                }
            }

            // foreach($inv_query as $inv_val){
            //         echo '<pre> ';
            //         // print_r($inv_val);
            //         echo ' <br> '.$inv_val->brand_name .$inv_val->brand_id .' inv : '.$inv_val->total_inventory ;
            // }

            //     echo ' End <br>  ';


            return Datatables::of($query)
                ->editColumn('brand_name', function ($row) {
                    return '<span class=""  data-orig-value="' . $row->brand_name . '">'. $row->brand_name . '</span>';
                })
                ->editColumn('total_sold_price', function ($row) {
                    // $unit_price = $row->unit_price;
                    // $stock = $row->total_sold;
                    // $total_stock_price = $stock * $unit_price;
                    return '<span class="display_currency total_sale" data-currency_symbol="true" data-orig-value="' . $row->unit_price . '">' . $row->unit_price . '</span>';

                    // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
                })
                ->editColumn('sale_percentage', function ($row) use($total_sale) {
                    $unit_price = $row->unit_price;
                    $percent =  ($unit_price*100)/$total_sale;
                    $sale_percentage = round($percent,2);
                    return '<span class="total-sale-percent" data-orig-value="' . $sale_percentage . '">'. $sale_percentage .'% </span>';
                })
                ->editColumn('total_stock_price', function ($row) {
                    // $unit_price = $row->price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';

                    // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
                })

                ->editColumn('negative_inventory', function ($row) {
                    return '<span class="display_currency total_negative_inventory" data-currency_symbol="true" data-orig-value="' . $row->negative_inventory . '">' . $row->negative_inventory . '</span>';
                })
                ->editColumn('inventory_percentage', function ($row) use($total_invent) {
                    // $unit_price = $row->price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    $percent =  ($row->total_inventory*100)/$total_invent;
                    $inventory_percent = round($percent,2);
                    return '<span class="total-inventory-percent" data-orig-value="' . $inventory_percent . '">'. $inventory_percent .'% </span>';
                })
                ->editColumn('difference', function ($row) use($total_invent,$total_sale) {
                    // $unit_price = $row->unit_price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    // $percent =  ($total_stock_price*100)/$total_invent;
                    // $inventory_percent = round($percent,2);

                    // $percent =  ($unit_price*100)/$total_sale;
                    // $sale_percentage = round($percent,2);

                    $percent =  ($row->unit_price*100)/$total_sale;
                    $sale_percentage = round($percent,2);

                    $percent =  ($row->total_inventory*100)/$total_invent;
                    $inventory_percent = round($percent,2);

                    $difference = $inventory_percent - $sale_percentage;

                    return '<span class="" data-orig-value="' . $difference . '">'. $difference .'% </span>';
                })
          //      ->filterColumn('brand_name', function ($query, $keyword) {
            //        $query->where('brands.name', 'like', "%{$keyword}%");
              //  })

                ->rawColumns(['brand_name','negative_inventory', 'total_sold_price','sale_percentage','total_stock_price','inventory_percentage','difference'])
                ->make(true);
        }

      return view('report.brand_wise_sale_report_new');
    }

        public function getProductwisesalereport(Request $request){

        // $product_list = DB::table('products')->select('id', 'name')->get();

        // foreach ($product_list as $product) {
        //     $products[$product->id] = $product->name;
        // }
        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

             $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            $query = TransactionSellLine::join('variations','transaction_sell_lines.variation_id','=','variations.id')
            ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
            ->join('products','products.id','=','variations.product_id')
            ->join('categories','categories.id','=','products.category_id')
            ->leftJoin('brands','products.brand_id','=','brands.id')
            ->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                $start_date, $end_date])
            ->select('products.name as product_name', 'variation_location_details.qty_available as stock',

            // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),

            DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),
            DB::raw('sum(transaction_sell_lines.quantity) as quantity'),

            // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
            // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
            // DB::raw('sum(variation_location_details.qty_available) as stock'),
            // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
            // DB::raw('sum(variation_location_details.qty_available) * sum(variations.sell_price_inc_tax) as total_invent')
            DB::raw("(SELECT (sum(transaction_sell_lines.quantity) *variations.sell_price_inc_tax)   FROM transactions where type='sell' AND transactions.id = transaction_sell_lines.transaction_id) as unit_price")
            )
            ->groupBy('products.id')->get();


            $total_sale = $query->sum('unit_price');
            $total_invent = $query->sum('total_inventory');

            $query =
            TransactionSellLine::join('variations','transaction_sell_lines.variation_id','=','variations.id')
                ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                ->join('products','products.id','=','variations.product_id')
                ->join('categories','categories.id','=','products.category_id')
                ->leftJoin('brands','products.brand_id','=','brands.id')

                ->select('products.name as product_name', 'variation_location_details.qty_available as stock',
                DB::raw('variations.sell_price_inc_tax * variation_location_details.qty_available as total_inventory'),
                // DB::raw('sum(variations.sell_price_inc_tax * variation_location_details.qty_available) as total_inventory'),
                // DB::raw('sum(variation_location_details.qty_available) as stock'),
                // DB::raw('variations.sell_price_inc_tax * transaction_sell_lines.quantity as unit_price'),
                // DB::raw('sum(variations.sell_price_inc_tax) as unit_price'),
                // DB::raw('variations.sell_price_inc_tax as price'),
                DB::raw('sum(transaction_sell_lines.quantity) as quantity'),
                DB::raw("(SELECT (sum(transaction_sell_lines.quantity) *variations.sell_price_inc_tax)   FROM transactions where type='sell' AND transactions.id = transaction_sell_lines.transaction_id) as unit_price")
                )
                ->groupBy('products.id');

            if (!empty($start_date) && !empty($end_date) ) {
                $start_date = $start_date." 00:00:00";
                $end_date = $end_date." 23:59:59";

                $trs = Transaction::whereBetween('transaction_date', [
                    $start_date, $end_date])
                    ->where('type','sell')
                    ->where('status','final')->get()->toArray();

                    $query->whereIn('transaction_id',array_column($trs,'id'));

                // $query->whereBetween(DB::raw('transaction_sell_lines.created_at'), [
                //     $start_date, $end_date]);
            }

            // return $query->get();
            // Search Product
            // $filters = request()->only(['product_id']);

            // if (!empty($request->input('product_id'))) {
            //     $query->where('products.id', $request->input('product_id'));
            // }

            $filters = request()->only(['category_id','brand_id']);
            if (!empty($request->input('category_id'))) {
                $query->where('products.category_id', $request->input('category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $query->where('products.brand_id', $request->input('brand_id'));
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    return '<span class=""  data-orig-value="' . $row->product_name . '">'. $row->product_name . '</span>';
                })
                ->addColumn('total_sold_price', function ($row) {
                    // $unit_price = $row->unit_price;
                    // $stock = $row->total_sold;
                    // $total_stock_price = $stock * $unit_price;
                    return '<span class="display_currency total_sale " data-currency_symbol="true" data-orig-value="' . $row->unit_price . '">' . $row->unit_price . '</span>';

                    // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
                })
                 ->editColumn('quantity', function ($row) {
                    return '<span class="total_quantity"  data-orig-value="' . $row->quantity . '">'. $row->quantity . '</span>';
                })
                ->editColumn('stock', function ($row) {

                    return '<span data-is_quantity="true" class="stock display_currency"  data-orig-value="' . $row->stock . '">'. $row->stock . '</span>';
                })
                ->editColumn('sale_percentage', function ($row) use($total_sale) {
                    $unit_price = $row->unit_price;
                    $percent =  ($unit_price*100)/$total_sale;
                    $sale_percentage = round($percent,2);
                    return '<span class="total-sale-percent" data-orig-value="' . $sale_percentage . '">'. $sale_percentage .'% </span>';
                })
                ->editColumn('total_stock_price', function ($row) {
                    // $unit_price = $row->price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    return '<span class="display_currency total_inventory" data-currency_symbol="true" data-orig-value="' . $row->total_inventory . '">' . $row->total_inventory . '</span>';

                    // return '<span class="display_currency total" data-currency_symbol="true" data-orig-value="' . $row->total . '">' . $row->total . '</span>';
                })
                ->editColumn('inventory_percentage', function ($row) use($total_invent) {
                    // $unit_price = $row->price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    $percent =  ($row->total_inventory*100)/$total_invent;
                    $inventory_percent = round($percent,2);
                    return '<span class="total-inventory-percent" data-orig-value="' . $inventory_percent . '">'. $inventory_percent .'% </span>';
                })
                ->editColumn('difference', function ($row) use($total_invent,$total_sale) {
                    // $unit_price = $row->unit_price;
                    // $stock = $row->stock;
                    // $total_stock_price = $stock * $unit_price;
                    // $percent =  ($total_stock_price*100)/$total_invent;
                    // $inventory_percent = round($percent,2);

                    // $percent =  ($unit_price*100)/$total_sale;
                    // $sale_percentage = round($percent,2);

                    $percent =  ($row->unit_price*100)/$total_sale;
                    $sale_percentage = round($percent,2);

                    $percent =  ($row->total_inventory*100)/$total_invent;
                    $inventory_percent = round($percent,2);

                    $difference = $inventory_percent - $sale_percentage;

                    return '<span class="" data-orig-value="' . $difference . '">'. $difference .'% </span>';
                })
                ->filterColumn('product_name', function ($query, $keyword) {
                    $query->where('products.name', 'like', "%{$keyword}%");
                })

                ->rawColumns(['product_name', 'stock','total_sold_price','quantity','sale_percentage','total_stock_price','inventory_percentage','difference'])
                ->make(true);
        }
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);

       return view('report.product_wise_sale_report',compact('categories','brands'));
    }

    public function getExpiredDocumentAndNoteReport(Request $request)
    {

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $notable_type = "App\Contact";
            $document_note = DocumentAndNote::where('business_id', $business_id)
                ->where(function ($query) use ($user_id) {
                    $query->where('is_private', 0)
                        ->orWhere(function ($q) use ($user_id) {
                            $q->where('is_private', 1)
                            ->where('created_by', $user_id);
                        });
                })
                ->where('notable_type', "App\Contact")
                ->where('expiry_date','NOT LIKE','NULL')
                ->whereBetween(DB::raw('expiry_date'), [
                    $start_date, $end_date])
                ->select('*')
                ->get();


                $document_note = DocumentAndNote::where('business_id', $business_id)
                ->where(function ($query) use ($user_id) {
                    $query->where('is_private', 0)
                        ->orWhere(function ($q) use ($user_id) {
                            $q->where('is_private', 1)
                            ->where('created_by', $user_id);
                        });
                })
                ->where('notable_type', "App\Contact")
                ->where('expiry_date','NOT LIKE','NULL')
                ->select('*');


                if (!empty($start_date) && !empty($end_date) ) {
                    $start_date = $start_date." 00:00:00";
                    $end_date = $end_date." 23:59:59";

                    $document_note->whereBetween(DB::raw('expiry_date'), [
                        $start_date, $end_date]);
                }

                return Datatables::of($document_note)

                    ->addColumn('suppliers', function ($row) {
                        return isset($row->notable->name) ? $row->notable->name : 'NA';
                    })
                    ->editColumn('expiry_date', function ($row) {
                        // $date_now = \Carbon::now()->format('m/d/Y');
                        // $exp_date = date('m/d/Y', strtotime($row->expiry_date));
                        // if($date_now >= $exp_date){
                        //     return '<span class="text-danger">' .  $exp_date .'</span>';
                        // }
                        // return '<span class="">' .  $exp_date .'</span>';

                        $carbon_exp = \Carbon::createFromFormat('Y-m-d', $row->expiry_date);
                        $carbon_now = \Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) >= 0) {
                            return '<span class="">' .  $carbon_exp->format('m/d/Y') .'</span>';
                        } else {
                            return '<span class="text-danger">' .  $carbon_exp->format('m/d/Y') .'</span>';                        }

                    })
                    ->editColumn('created_at', '
                        {{@format_date($created_at)}}
                    ')
                    ->editColumn('updated_at', '
                        {{@format_date($updated_at)}}
                    ')
                    ->addColumn('createdBy', function ($row) {
                        return optional($row->createdBy)->user_full_name;
                    })
                    ->removeColumn('id')
                    ->rawColumns([ 'heading', 'createdBy','expiry_date', 'created_at', 'updated_at'])
                    ->make(true);


        }

        return view('report.expired_document_and_note_report');

    }


     /**
     * Shows report for Supplier
     *
     * @return \Illuminate\Http\Response
     */
    public function getStaleCustomer(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $contacts = Contact::where('contacts.business_id', $business_id)
                // ->join('users AS u', 'contacts.sales_rep', '=', 'u.id')
                ->leftjoin('users as u','u.id','=','contacts.sales_rep')
                ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->active()
                ->groupBy('contacts.id')
                ->select(
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                    DB::raw("DATEDIFF(CURDATE(), MAX(t.transaction_date)) as days"),
                    DB::raw("MAX(t.transaction_date) as transaction_date"),
                    'contacts.supplier_business_name',
                    'contacts.name', 'contacts.email', 'contacts.mobile',
                    'contacts.address_line_1',
                    'contacts.address_line_2',
                    'contacts.city',
                    'contacts.country',
                    'contacts.state',
                    'contacts.zip_code',
                    'contacts.id'
                );

            $permitted_locations = auth()->user()->permitted_locations();

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            if ($permitted_locations != 'all') {
                $contacts->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->input('sr_id'))) {
                $contacts->where('u.id', $request->input('sr_id'));
            }else{
                 $contacts->whereNotNull('t.id');
                  if (!empty($start_date) && !empty($end_date) ) {
                    $start_date = $start_date." 00:00:00";
                    $end_date = $end_date." 23:59:59";

                    $contacts->whereBetween(DB::raw('t.transaction_date'), [$start_date, $end_date]);
                }
            }

            if (!empty($request->input('contact_id'))) {
                $contacts->where('contacts.id', $request->input('contact_id'));
            }



            // if (!empty($request->input('from_date'))) {
            //     $contacts->where('t.transaction_date', '<=', date('Y-m-d', strtotime($request->input('from_date'))) );
            // }

             return Datatables::of($contacts)
                ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')

                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    if (!empty($row->supplier_business_name)) {
                        $name .= ', ' . $row->supplier_business_name;
                    }
                    return '<span class="name" data-orig-value="' . $row->name . '" >' . $row->name . '</span>';
                })
                // ->editColumn('email', function ($row) {
                //     return '<span class="email" data-orig-value="' . $row->email . '" >' . $row->email . '</span>';
                // })
                ->editColumn('mobile', function ($row) {
                    return '<span class="mobile" data-orig-value="' . $row->mobile . '" >' . $row->mobile . '</span>';
                })
                ->editColumn('total_sell_return', function ($row) {
                    return '<span class="display_currency total_invoice" data-orig-value="' . $row->total_invoice . '" data-currency_symbol = true>' . $row->total_invoice . '</span>';
                })
                ->editColumn('user_name', function ($row) {
                    return $row->user_name;
                })
                ->editColumn('transaction_date', function ($row) {
                    return '<span class="transaction_date" data-orig-value="' . $row->transaction_date . '" >' . $row->transaction_date . '</span>';
                })
                 ->editColumn('days', function ($row) {
                    return $row->days;
                })

                ->addColumn('due', function ($row) {
                    $due = ($row->total_invoice - $row->invoice_received - $row->total_sell_return + $row->sell_return_paid) - ($row->total_purchase - $row->total_purchase_return + $row->purchase_return_received - $row->purchase_paid);

                    if ($row->contact_type == 'supplier') {
                        $due -= $row->opening_balance - $row->opening_balance_paid;
                    } else {
                        $due += $row->opening_balance - $row->opening_balance_paid;
                    }

                    if($due > 0){
                        return '<span class="display_currency total_due text-success" data-orig-value="' . $due . '" data-currency_symbol=true >' . $due .'</span>';
                    }
                    if($due < 0){
                        return '<span class="display_currency total_due text-danger" data-orig-value="' . $due . '" data-currency_symbol=true >' . $due .'</span>';
                    }
                    if($due == 0){
                        return '<span class="display_currency total_due" data-orig-value="' . $due . '" data-currency_symbol=true>' . $due .'</span>';
                    }
                })
                ->rawColumns(['name', 'due','address' ,'mobile', 'total_sell_return', 'user_name', 'transaction_date', 'days'])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id);
        //  $users = User::forDropdown($business_id, false);
        $users = User::select('id','first_name','last_name', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))
            ->where('business_id',4)
            ->where('status','active')
            ->where('allow_login',1)
            ->where('sales_rep',1)
            ->orderBy('first_name')
            ->get()
        ->pluck('full_name', 'id');
        return view('report.view_stale_customer')
        ->with(compact('customers', 'users'));
    }

    /**
     * Shows report for Supplier
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesAccountRepresentativeReport(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id);
        // $users = User::forDropdown($business_id, false);
        $users = User::select('id','first_name','last_name')->get();

        return view('report.view_sales_by_account_representative')
        ->with(compact('customers', 'users'));
    }


    public function getSalesAccountRepresentativeShowReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {

            $contacts = Contact::where('contacts.business_id', $business_id)
                ->join('users AS u', 'contacts.sales_rep', '=', 'u.id')
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->join('transaction_sell_lines AS tsl', 't.id', '=', 'tsl.transaction_id')
                ->active()
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->groupBy('transaction_id')
                ->select(
                    DB::raw("(1-((sum(tsl.purchase_price*tsl.quantity))/(sum(tsl.unit_price_inc_tax*tsl.quantity)-tsl.line_discount_amount)))*100 as total_gp"),
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                    'contacts.name',
                    't.invoice_no','t.id as transaction_id',
                    't.total_before_tax',
                    't.discount_amount',
                    't.discount_type',
                    't.shipping_charges',
                    DB::raw("sum(tsl.pos_line_tax_amount) as total_tax_amount"),

                    't.final_total',
                    DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=t.id) as total_paid'),

                    DB::raw('(SELECT SUM(IF(p.name like "JUUL%",transaction_sell_lines.quantity * transaction_sell_lines.unit_price_inc_tax, 0)) FROM products as p , transaction_sell_lines WHERE
                        p.id = transaction_sell_lines.product_id and transaction_sell_lines.transaction_id = t.id group by tsl.transaction_id
                        ) as total_juul'),
                    't.created_at',
                );
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            if (!empty($request->input('sr_id'))) {
                $contacts->where('u.id', $request->input('sr_id'));
            }

            // if (!empty($request->input('contact_id'))) {
            //     $contacts->where('contacts.id', $request->input('contact_id'));
            // }

            if (!empty($start_date) && !empty($end_date) ) {
                $start_date = $start_date." 00:00:00";
                $end_date = $end_date." 23:59:59";

                $contacts->whereBetween(DB::raw('t.created_at'), [$start_date, $end_date]);
            }

             return Datatables::of($contacts)

                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    return '<span class="name" data-orig-value="' . $row->name . '" >' . $row->name . '</span>';
                })

                ->editColumn('invoice_no', function ($row) {
                     return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                 })

                ->editColumn('created_at', function ($row) {
                    return '<span class="created_at" data-orig-value="' . $row->created_at . '" >' . $row->created_at . '</span>';
                })

                 ->editColumn('total_gp', function ($row) {
                      $gross_profit = round($row->total_gp,2);
                       $gp = $gross_profit.'%';
                    // return  '<span class="total_gross_profit" data-orig-value="'. $gross_profit.'">'. $gp.'</span>';
                    return '<span class="gross_profit" data-orig-value="' . $gross_profit . '" >' . $gp . '</span>';
                })
                ->editColumn('total_amount', function ($row) {

                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        $final_total =  $row->final_total + $discount;

                    return '<span class="display_currency total_amount" data-orig-value="' . $final_total . '" data-currency_symbol = true>' . $final_total . '</span>';
                })

                 ->editColumn('final_total', function ($row) {
                    return '<span class="display_currency final_total" data-orig-value="' . $row->total_paid . '" data-currency_symbol = true>' . $row->total_paid . '</span>';
                })

                ->editColumn('total_tax_amount', function ($row) {
                    return '<span class="display_currency total_tax_amount" data-orig-value="' . $row->total_tax_amount . '" data-currency_symbol = true>' . $row->total_tax_amount . '</span>';
                })
                ->editColumn('shipping_charges', function ($row) {
                    return '<span class="display_currency shipping_charges" data-orig-value="' . $row->shipping_charges . '" data-currency_symbol = true>' . $row->shipping_charges . '</span>';
                })
                 ->editColumn('total_juul', function ($row) {
                    return '<span class="display_currency total_juul" data-orig-value="' . $row->total_juul . '" data-currency_symbol = true>' . $row->total_juul . '</span>';
                })

                ->addColumn('total_remaining', function ($row) {
                    $total_remaining =  $row->final_total - $row->total_paid;
                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $total_remaining . '">' . $total_remaining . '</span>';
                })

                ->editColumn('discount_amount', function ($row) {

                    $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                    if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                    return '<span class="display_currency discount_amount" data-currency_symbol="true" data-orig-value="' . $discount . '">' . $discount . '</span>';
                })

                ->editColumn('user_name', function ($row) {
                    return $row->user_name;
                })

                ->addColumn('final_amount', function ($row) {
                    $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;
                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                    $final_total =  $row->final_total - $discount;
                    $final_amount = $final_total -$row->total_tax_amount - $row->shipping_charges - $row->total_juul ;

                    return '<span class="display_currency final_amount" data-currency_symbol="true" data-orig-value="' . $final_amount . '">' . $final_amount . '</span>';

                })

                ->rawColumns(['name','total_juul' ,'final_amount','total_tax_amount','shipping_charges', 'invoice_no','total_amount', 'total_remaining','final_total', 'discount_amount','total_gp','created_at', 'user_name'])
                ->make(true);
        }
    }

    public function getCreditMemoAccountRepresentativeShowReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {

            $contacts = Contact::where('contacts.business_id', $business_id)
                ->join('users AS u', 'contacts.sales_rep', '=', 'u.id')
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->join('transaction_sell_lines AS tsl', 't.id', '=', 'tsl.transaction_id')
                ->active()
                ->where('t.type', 'sell_return')
                ->where('t.status', 'final')
                ->groupBy('transaction_id')
                ->select(
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                    'contacts.name',
                    't.invoice_no','t.id as transaction_id',
                    't.total_before_tax as total_amount',
                    't.discount_amount',
                    't.discount_type',
                    't.final_total',
                    't.created_at',
                );
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            if (!empty($request->input('sr_id'))) {
                $contacts->where('u.id', $request->input('sr_id'));
            }

            // if (!empty($request->input('contact_id'))) {
            //     $contacts->where('contacts.id', $request->input('contact_id'));
            // }

            if (!empty($start_date) && !empty($end_date) ) {
                $start_date = $start_date." 00:00:00";
                $end_date = $end_date." 23:59:59";

                $contacts->whereBetween(DB::raw('t.created_at'), [$start_date, $end_date]);
            }

             return Datatables::of($contacts)

                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    return '<span class="name" data-orig-value="' . $row->name . '" >' . $row->name . '</span>';
                })

                ->editColumn('invoice_no', function ($row) {
                     return '<a data-href="' . action('SellReturnController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                 })

                ->editColumn('created_at', function ($row) {
                    return '<span class="created_at" data-orig-value="' . $row->created_at . '" >' . $row->created_at . '</span>';
                })

                 /*->editColumn('total_gp', function ($row) {
                      $gross_profit = round($row->total_gp,2);
                       $gp = $gross_profit.'%';
                    // return  '<span class="total_gross_profit" data-orig-value="'. $gross_profit.'">'. $gp.'</span>';
                    return '<span class="gross_profit" data-orig-value="' . $gross_profit . '">' . $gp . '</span>';
                })*/
                ->editColumn('total_amount', function ($row) {
                    $total_amount= round($row->total_amount,2);
                    return '<span class="display_currency total_amount" data-orig-value="' . $total_amount . '" data-currency_symbol = true>' . $total_amount . '</span>';
                })

                 ->editColumn('final_total', function ($row) {
                    return '<span class="display_currency final_total" data-orig-value="' . $row->final_total . '" data-currency_symbol = true>' . $row->final_total . '</span>';
                })

                ->editColumn('discount_amount', function ($row) {

                    $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                    if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                    return '<span class="display_currency discount_amount" data-currency_symbol="true" data-orig-value="' . $discount . '">' . $discount . '</span>';
                })

                 ->editColumn('user_name', function ($row) {
                    return $row->user_name;
                })

                ->rawColumns(['name', 'invoice_no','total_amount', 'final_total', 'discount_amount','created_at', 'user_name'])
                ->make(true);
        }
    }

        public function productOnhandList(Request $request)
    {

        $Startyear = date('Y');
        $endYear   = $Startyear - 10;
        $yearArr   = range($Startyear, $endYear);
        $years     = [];
        foreach($yearArr as $year)
        {
            $years[$year] = $year;
        }
        $weekDay = date('W', strtotime(date('Y-m-d')));

        $first_saturday = date('Y-m-d', strtotime('first sat of jan '.date('Y')));

        $weeks          = [];
        for($i = 1; $i <= 52; $i++)
        {

            if($i < 10)
            {
                $weeks['week_0' . $i] = 'Week ' . $i.' ('.$first_saturday.')';
            }
            else
            {
                $weeks['week_' . $i] = 'Week ' . $i.' ('.$first_saturday.')';;
            }
            $first_saturday = date('Y-m-d', strtotime('+1 Week', strtotime($first_saturday)));
        }

        return view('report.onhand_item_report', compact('years', 'weeks'));
    }

    public function getproductOnhand(Request $request)
    {

        $week     = 'onhand_items.' . $request->week;
        $products = OnhandItem::select('onhand_items.id', $week, 'products.name as name')->leftjoin('products', 'onhand_items.product_id', '=', 'products.id');
        $products->where('year', $request->year);

        return Datatables::of($products)->make(true);
    }

    public function itemInventory(Request $request)
    {

        $Startyear = date('Y');
        $endYear   = $Startyear - 10;
        $yearArr   = range($Startyear, $endYear);
        $years     = [];
        foreach($yearArr as $year)
        {
            $years[$year] = $year;
        }
        $weekDay = date('w', strtotime(date('Y-m-d')));

        $weeks = [];
        $first_saturday = date('Y-m-d', strtotime('first sat of jan '.date('Y')));
        for($i = 1; $i <= 52; $i++)
        {
            if($i < 10)
            {
                $weeks['week_0' . $i] = 'Week '  . $i.' ('.$first_saturday.')';
            }
            else
            {
                $weeks['week_' . $i] = 'Week ' . $i.' ('.$first_saturday.')';
            }
            $first_saturday = date('Y-m-d', strtotime('+1 Week', strtotime($first_saturday)));
        }

        return view('report.item_inventory_report', compact('years', 'weeks'));
    }


    public function getitemInventory(Request $request)
    {
        $week           = $request->week;
        $year           = $request->year;
        $inventoryItems = Product::$inventoryItemList;

        $products = Product::select('id', 'name', 'item_code', 'sku', 'barcode_type', 'qty_box')->whereIn('name', $inventoryItems)->get()->toArray();

        $itemArr = [];
        foreach($products as $product)
        {
            $onHand = OnhandItem::select($week)->where('product_id', $product['id'])->where('year', $year)->first();

            $productArr['name']         = $product['name'];
            $productArr['item_code']    = $product['item_code'];
            $productArr['sku']          = $product['sku'];
            $productArr['qty_box']      = $product['qty_box'];
            $productArr['msa_category'] = '3292';
            $productArr['on_hand_item'] = !empty($onHand) ? $onHand[$week] : 0;
            $itemArr[]                  = $productArr;
        }

        return Datatables::of($itemArr)->make(true);

    }


   public function getAuditReport(Request $request){
        if (!auth()->user()->can('profit_loss_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $options = Audit::select('auditable_type')->distinct()->get();
        $modules = [];
        foreach($options as $item){
            $elements = explode("\\",$item->auditable_type);
            array_push($modules,$elements[1]);
        }

        return view('report.audit',compact('modules'));
    }

    public function getAuditReportRaw($type = ""){
        if(strtolower($type) == "all"){
            $type = "";
        }
        $audits = Audit::where('auditable_type','like','%'.$type.'%')->orderBy('created_at','desc')->get();
        foreach($audits as $item){
            $item->visibility = false;
            $item->event = ucwords($item->event);
            $item->timestamp = date_format($item->created_at,'Y/m/d H:i:s');
            $old_arr = explode(",",substr($item->old_values,1,-1));
            $item->old_short = json_decode("{".$old_arr[0]."}");
            $new_arr = explode(",",substr($item->new_values,1,-1));
            $item->new_short = json_decode("{".$new_arr[0]."}");
            $item->old_values = json_decode($item->old_values);
            $item->new_values = json_decode($item->new_values);

        }
        return json_encode($audits);
    }

    /**
     * Shows Daily Items report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getDaliyItemsReport(Request $request)
    {
        /*if (!auth()->user()->can('daily_items_report.view')) {
            abort(403, 'Unauthorized action.');
        }*/

        return view('report.daily_items_report_vtwo');
    }
    public function getItemsPurchasedForToday(Request $request)
    {
        /*if (!auth()->user()->can('daily_items_report.view')) {
            abort(403, 'Unauthorized action.');
        }*/
        $change_format = str_replace('/', '-', $request->select_date);
        $today_date = date('Y-m-d', strtotime($change_format));
        //$today_date = date('Y-m-d');
        //$today_date = date('2022-02-23');
        $business_id = $request->session()->get('user.business_id');

        $query = PurchaseLine::join(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
                    )
                    ->join(
                        'variations as v',
                        'purchase_lines.variation_id',
                        '=',
                        'v.id'
                    )
                    ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                    ->join('contacts as c', 't.contact_id', '=', 'c.id')
                    ->join('products as p', 'pv.product_id', '=', 'p.id')
                    ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                    ->leftJoin('categories as c1', 'p.category_id', '=', 'c1.id')
                    ->leftJoin('categories as c2', 'p.sub_category_id', '=', 'c2.id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'purchase')
                    ->whereDate('t.transaction_date', '=', $today_date)
                    ->select(
                        'p.id',
                        'p.name as name',
                        'p.item_code',
                        'v.sub_sku as sku',
                        DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                        'c1.name as category',
                        'c2.name as sub_category',
                         DB::raw('MAX(v.default_sell_price) as max_price'),
                         DB::raw('MIN(v.default_sell_price) as min_price'),
                        'p.type'
                    )
                    ->groupBy('purchase_lines.id')->orderBy('t.id','DESC');

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }
        $products = $query;

        return Datatables::of($products)
        ->editColumn(
                    'selling_price',
                    '<div style="white-space: nowrap;"><span class="display_currency" data-currency_symbol="true">{{$min_price}}</span> @if($max_price != $min_price && $type == "variable") -  <span class="display_currency" data-currency_symbol="true">{{$max_price}}</span>@endif </div>'
                )
        ->rawColumns(['selling_price'])
        ->make(true);
    }

           public function getItemsPurchasedForTodayVthr(Request $request, ProductUtil $productUtil)
        {
            $change_format = str_replace('/', '-', $request->select_date);
            // dd($change_format);
            // die();
            $today_date = date('Y-m-d', strtotime($change_format));
            $business_id = $request->session()->get('user.business_id');
            $location_id = $request->session()->get('user.location_id'); // Assume location is stored in session
            $query = PurchaseLine::join('transactions as t', 'purchase_lines.transaction_id', '=', 't.id')
                        ->join('variations as v', 'purchase_lines.variation_id', '=', 'v.id')
                        ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                        ->join('contacts as c', 't.contact_id', '=', 'c.id')
                        ->join('products as p', 'pv.product_id', '=', 'p.id')
                        ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
                        ->leftJoin('categories as c1', 'p.category_id', '=', 'c1.id')
                        ->leftJoin('categories as c2', 'p.sub_category_id', '=', 'c2.id')
                        ->where('t.business_id', $business_id)
                        ->where('t.type', 'purchase')
                        ->whereDate('t.transaction_date', '=', $today_date)
                        ->select([
                            'p.id',
                            'p.name as name',
                            'p.item_code',
                            'v.sub_sku as sku',
                            DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                            'v.id as variation_id',
                            'c1.name as category',
                            'c2.name as sub_category',
                            DB::raw('MAX(v.default_sell_price) as max_price'),
                            DB::raw('MIN(v.default_sell_price) as min_price'),
                            'p.type'
                        ])
                        ->groupBy('purchase_lines.id')
                        ->orderBy('t.id', 'DESC')
                        ->get();
                foreach ($query as $item) {
                    $stockHistory = $productUtil->getVariationStockHistoryShortned($business_id, $item->variation_id, $business_id);
                    $todayDateTime = new DateTime($today_date);
                    $todayDateTime->modify('+1 day');
                    $adjustedTodayDate = $todayDateTime->format('Y-m-d');
                    $eoy_index = $this->findMostRecentStockHistoryIndex($stockHistory, $today_date);
                    $eod_index = $this->findMostRecentStockHistoryIndex($stockHistory, $adjustedTodayDate);
                    //$index = $this->findMostRecentStockHistoryIndex($stockHistory, $today_date);

                    if ($eod_index !== -1) {
                        $item->on_hand_qty = $stockHistory[$eod_index]['stock'];  // Use the most recent stock count
                        $item->error = null;
                    } else {
                        $item->on_hand_qty = 0;  // Default if no history found on or before today
                        $item->error = "No appropriate stock history found for this product up to today's date.";
                    }
                    if ($eoy_index !== -1) {
                        $item->ofs_qty = $stockHistory[$eoy_index]['stock'];  // Use the most recent stock count
                        $item->error = null;
                    } else {
                        $item->ofs_qty = 0;  // Default if no history found on or before today
                        $item->error = "No appropriate stock history found for this product up to today's date.";
                    }
                }

                  $filteredQuery = $query->filter(function ($item) {
                    return $item->ofs_qty <= 0 && $item->on_hand_qty > 0;
                });

                    return Datatables::of($filteredQuery)
                        ->editColumn('selling_price', function ($product) {
                            $minPriceDisplay = '<span class="display_currency" data-currency_symbol="true">' . $product->min_price . '</span>';
                            $sellingPriceHtml = '<div style="white-space: nowrap;">' . $minPriceDisplay;
                            if ($product->max_price != $product->min_price && $product->type == "variable") {
                                $maxPriceDisplay = '<span class="display_currency" data-currency_symbol="true">' . $product->max_price . '</span>';
                                $sellingPriceHtml .= ' - ' . $maxPriceDisplay;
                            }
                            $sellingPriceHtml .= '</div>';
                            return $sellingPriceHtml;
                        })
                        ->rawColumns(['selling_price'])
                        ->make(true);
        }
        private function findMostRecentStockHistoryIndex($stockHistory, $today_date)
        {
            $latestDate = strtotime($today_date);
            // Check for exact match
            foreach ($stockHistory as $index => $history) {
                $historyDate = strtotime($history['date']);
                if ($historyDate == $latestDate) {
                    return $index;
                }
            }
            // Exact match not found, search for closest previous date
            foreach ($stockHistory as $index => $history) {
                $historyDate = strtotime($history['date']);
                if ($historyDate < $latestDate) {
                    return $index;
                }
            }
            return -1; // Return -1 if no matching or previous date is found
        }





    public function getItemsAddedForToday(Request $request)
    {
        /*if (!auth()->user()->can('daily_items_report.view')) {
            abort(403, 'Unauthorized action.');
        }*/

        //$today_date = date('Y-m-d');
        $change_format = str_replace('/', '-', $request->select_date);
        $today_date = date('Y-m-d', strtotime($change_format));
        //echo $today_date; exit;
        //$today_date = date('2021-05-31');

        $business_id = request()->session()->get('user.business_id');
        $query = Product::join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->where('products.business_id', $business_id)
                ->whereDate('products.created_at', '=', $today_date);

        $query->ProductForSales();
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereHas('product_locations', function ($query) use ($permitted_locations) {
                $query->whereIn('product_locations.location_id', $permitted_locations);
            });
        } else {
            $query->with('product_locations');
        }

        $products = $query->select(
                'products.id',
                'products.name as name',
                'products.item_code',
                'products.sku',
                'vld.qty_available',
                'products.sales_price',
                'c1.name as category',
                'c2.name as sub_category',
                DB::raw('MAX(v.default_sell_price) as max_price'),
                DB::raw('MIN(v.default_sell_price) as min_price'),
                'products.type'
                )->groupBy('products.id')->orderBy('products.id','DESC');
        /*echo "<pre>";
        print_r($products);
        echo "</pre>";
        exit;*/
        return Datatables::of($products)
        ->editColumn(
                    'selling_price',
                    '<div style="white-space: nowrap;"><span class="display_currency" data-currency_symbol="true">{{$min_price}}</span> @if($max_price != $min_price && $type == "variable") -  <span class="display_currency" data-currency_symbol="true">{{$max_price}}</span>@endif </div>'
                )
        ->rawColumns(['selling_price'])
        ->make(true);
    }

    /**
     * Shows Daily Items report of a business End
     */

    public function transactionReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        return view('report.transaction_report')->with(compact('customers'));
    }


    public function getTransactionReport(Request $request)
    {
        $query =  DB::table('transactions')
            ->leftJoin('contacts','contacts.id','=','transactions.contact_id')
            ->leftJoin('transaction_sell_lines','transaction_sell_lines.transaction_id','=','transactions.id')
            ->leftJoin('tax_rates','tax_rates.id','=','transaction_sell_lines.pos_line_tax_id')
            ->select(
                        'transactions.id as tra_id',
                        'transactions.invoice_no as invoice_no',
                        // 'transaction_sell_lines.pos_line_tax_id as tax_id',
                        DB::raw('DATE_FORMAT(transactions.transaction_date, "%m/%d/%Y") as invoice_date'),
                         DB::raw('SUM(transaction_sell_lines.pos_line_tax_amount) AS tax_amount'),
                       // 'transaction_sell_lines.pos_line_tax_amount as tax_amount',
                        'contacts.name as name',
                        'contacts.address_line_1 as address',
                        'contacts.city as city',
                        'contacts.tax_number as tax_id',
                        'contacts.state as state',
                        'contacts.zip_code as zip_code',
                        'transactions.payment_status as payment_status',
                        'transactions.item_qty as item_qty',
                        'transactions.sub_total as price',
                        'transactions.tax_amount as tax_amount1'
                    )
            ->where('transactions.type','sell')
            ->orderBy('transactions.id','ASC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));
                $query = $query->whereBetween('transactions.transaction_date',array($start, $end));
                // $query = $query->whereBetween('transaction_sell_lines.created_at',array($start, $end));
            }
            if($request->customer_id != null){
                $query = $query->where('transactions.contact_id', $request->customer_id);
            }
            // $query = $query->where('tax_rates.name', 'like', '%MA VAPE%');
            // $query = $query->where('contacts.state', '=', 'Massachusetts')->where('tax_rates.name', 'like', '%MA VAPE%');
            $query->where('tax_rates.state', 'Massachusetts');
                           // $query = $query->where('tax_rates.name', 'like', 'MA VAPE%')
                           // ->orwhere('tax_rates.name', 'like', 'MA Vape%');

            $query = $query->groupBy('transactions.id')->having('tax_amount','>',0)
                        ->get();
            // dd($query);
        $itemArr = [];
        foreach($query as $val)
        {
            // dd($val->tra_id);
            $productArr['invoice_date']     = $val->invoice_date;
            $productArr['invoice_no']       = $val->invoice_no;
            $productArr['tax_id']           = $val->tax_id;
            $productArr['name']             = $val->name;
            $productArr['address']          = $val->address;
            $productArr['city']             = $val->city;
            $productArr['state']            = $val->state;
            $productArr['zip_code']         = $val->zip_code;
            $productArr['payment_status']   = $val->payment_status;
            $productArr['item_qty']         = $val->item_qty;
            $productArr['price']            = $val->price;
            $productArr['order_note']       = 'YES';
            $productArr['tax_amount']       = $val->tax_amount;
            $itemArr[]                  = $productArr;
        }
        return Datatables::of($itemArr)->make(true);

    }

    public function nhVapeReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        return view('report.nhvapereport_report')->with(compact('customers'));
    }


    public function getNhVapeReport(Request $request)
    {
        // $nh_query = $cigar_query = [];
        $nh_query =  DB::table('transactions')
            ->leftJoin('contacts','contacts.id','=','transactions.contact_id')
            ->leftJoin('products','products.business_id','=','transactions.business_id')
            ->leftJoin('transaction_sell_lines','transaction_sell_lines.transaction_id','=','transactions.id')
            ->leftJoin('tax_rates','tax_rates.id','=','transaction_sell_lines.pos_line_tax_id')
            ->select(
                        'contacts.id as contact_id',
                        'contacts.name as name',
                        'contacts.address_line_1 as address',
                        'contacts.city as city',
                        'contacts.tobacco_license_no as tobacco_license_no',
                        'transactions.invoice_no as invoice_no',
                        'products.qty_box as qty_box',
                        'products.case_qty as case_qty',
                        'transactions.sub_total as price',
                        'transaction_sell_lines.unit_price as costing'
                    )
            ->where('transactions.type','sell')
            ->orderBy('transactions.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));
                $nh_query = $nh_query->whereBetween('transactions.transaction_date',array($start, $end));
            }
            if($request->customer_id != null){
                $nh_query = $nh_query->where('transactions.contact_id', $request->customer_id);
            }



            $nh_query = $nh_query->where('tax_rates.name', 'like', 'NH Vape%')->groupBy('contacts.id')->get();
        //   $query->where('contacts.state', '=', 'New Hampshire')->where('transaction_sell_lines.pos_line_tax_id', '!=', '');
            // $cigar_query = $query->where('tax_rates.name', 'like', '%Cigar%')->groupBy('contacts.id')->get();

                  //  $cigar_query = $cigar_query->where('tax_rates.name', 'like', '~%')->groupBy('contacts.id')->get();

            $nh_query = $nh_query->toArray();
            $arrayMG =$nh_query;
        //    $arrayMG = array_merge($nh_query,$cigar_query);
            // dd(count($arrayMG));
            // dd($query);
        $itemArr = [];
        $total_order = 0;
        foreach($arrayMG as $val)
        {
            // dd($val->tra_id);
            $productArr['name']                 = $val->name;
            $productArr['address']              = $val->address .','. $val->city;
            $productArr['tobacco_license_no']   = $val->tobacco_license_no;
            $productArr['invoice_no']           = $val->invoice_no;
            $productArr['qty_box']              = $val->qty_box;
            if($val->case_qty != Null){
                $total = $val->qty_box * $val->case_qty;
                $productArr['case_qty']         = $total;
            } else {
                $productArr['case_qty']         = 0;
            }
            $productArr['price']                = $val->price;
            $productArr['costing']              = $val->costing;

            $itemArr[]                  = $productArr;
        }
        $datatable = Datatables::of($itemArr)
        // ->editColumn('product_name', function ($row) {
        //     $name = $row->product_name;
        //     if ($row->product_type == 'variable') {
        //         $name .= ' - ' . $row->product_variation_name . ' - ' . $row->variation_name;
        //     }
        //     return $name;
        // })
        ->editColumn(
            'total_price',
            '<span class="display_currency total_price" data-currency_symbol="true" data-orig-value="200">200</span>'
        )
        ->editColumn(
            'total_costing',
            '<span class="display_currency total_costing" data-currency_symbol="true" data-orig-value="100">100</span>'
        )
        // ->editColumn(
        //     'quantity',
        //     '<span class="display_currency quantity" data-unit="0" data-currency_symbol="false" data-orig-value="0">0</span>'
        // )
        // ->editColumn(
        //     'unit_price_before_discount',
        //     '<span class="display_currency unit_price_before_discount" data-currency_symbol="true" data-orig-value="0">0</span>'
        // )
        ->addColumn(
            'total_costing',
            '<span class="display_currency total_costing" data-currency_symbol="true" data-orig-value="200">200</span>'
        )
        ->editColumn(
            'total_costing',
            function ($row) {
                $cost_total = 0;
                $cost_total += $row['costing'];
                $all_totla = !empty($cost_total) ? $cost_total : 0;
                // dd($all_totla);
                return '<span class="display_currency total_costing" data-currency_symbol="true" data-orig-value="'. $all_totla .'">'. $all_totla .'</span>';
            }
        )
        ->rawColumns(['total_price', 'total_costing'])
              ->make(true);

        return $datatable;

    }

    /**
     * Shows out of stock report
     *
     * @return \Illuminate\Http\Response
     */
    public function getOutOfStockReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        // $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {

            $start_date = date('Y-m-d',strtotime($request->get('start_date')));
            $end_date = date('Y-m-d',strtotime($request->get('end_date')));

            $query =  TransactionSellLine::
                    select('product_id',
                            DB::raw("SUM(transaction_sell_lines.out_of_stock_qty) as outOfStock"),
                            'p.name as product_name',
                            'p.sku',
                            'transaction_sell_lines.unit_price',
                            't.transaction_date as transaction_date')
                    ->leftJoin('products as p', 'transaction_sell_lines.product_id', '=', 'p.id')
                    ->leftJoin('transactions as t', 'transaction_sell_lines.transaction_id', '=', 't.id')
                    ->where('transaction_sell_lines.out_of_stock', 1);

            //Check for permitted locations of a user
            // $permitted_locations = auth()->user()->permitted_locations();
            // if ($permitted_locations != 'all') {
            //     $query->whereIn('location_id', $permitted_locations);
            // }


            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_sell_lines.created_at)'), [$start_date, $end_date]);
            }

            $outOfStockData = $query->orderBy(DB::raw("SUM(transaction_sell_lines.out_of_stock)"), 'DESC')
                            ->groupBy('transaction_sell_lines.product_id')
                            ->get();

            // $location_id = $request->get('location_id');
            // if (!empty($location_id)) {
            //     $query->where('location_id', $location_id);
            // }



            return Datatables::of($outOfStockData)
                ->removeColumn('id')
                ->addColumn('stock_qty',function($row){
                    $vld = VariationLocationDetails::where('product_id',$row->product_id)->where('location_id',4)->first();
                    if(!empty($vld->qty_available)){
                        return round($vld->qty_available);
                    }
                    else{
                        return 0;
                    }
                })
                ->editColumn(
                    'product_name',
                    '<span class="">{{$product_name}}</span>'
                )
                ->editColumn(
                    'unit_price',
                    '<span class="display_currency" data-currency_symbol="true">{{$unit_price}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('outOfStock', '{{$outOfStock}}')
                // ->editColumn('adjustment_type', function ($row) {
                //     return __('stock_adjustment.' . $row->adjustment_type);
                // })
                // ->setRowAttr([
                // 'data-href' => function ($row) {
                //     return  action('OutOfStockController@show', [$row->id]);
                // }])
                ->rawColumns(['product_name', 'unit_price','outOfStock'])
                ->make(true);

        }
        // $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.out_of_stock_report');

                    // ->with(compact('business_locations'));
    }

    public function nhVapeTransactionReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        return view('report.nhvapetransaction_report')->with(compact('customers'));
    }

    public function getNhVapeTransactionReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        // $nh_query = $cigar_query = [];
        $nh_query =  DB::table('transactions')
            ->join('transaction_sell_lines','transaction_sell_lines.transaction_id','=','transactions.id')
            ->join('products','products.id','=', 'transaction_sell_lines.product_id')
            ->join('contacts','contacts.id','=','transactions.contact_id')
            ->leftJoin('categories as c', 'products.category_id', '=', 'c.id')
            ->leftJoin('tax_rates','tax_rates.id','=','transaction_sell_lines.pos_line_tax_id')
            ->select(
                        //'products.id as pid',
                        //'transaction_sell_lines.id as tranline_id',
                        //'contacts.id as contact_id',
                        DB::raw('DATE_FORMAT(transactions.transaction_date, "%m/%d/%Y") as sale_date'),
                        'transactions.invoice_no as invoice_no',
                        'contacts.tax_number as tax_id',
                        'contacts.name as name',
                        'contacts.address_line_1 as address',
                        'contacts.tobacco_license_no as tobacco_license_no',
                        'contacts.city as city',
                        'contacts.state as state',
                        'contacts.zip_code as zip_code',
                        'transactions.payment_status as payment_status',
                        //'c.id as cid',
                        //'c.name as category',
                        //DB::raw("SUM(transaction_sell_lines.unit_price_inc_tax*transaction_sell_lines.quantity) as totalPrice"),
                        DB::raw("SUM(CASE WHEN (c.name = 'E-LIQUID' or c.name = 'SALT E-LIQUID')
                            THEN ((transaction_sell_lines.unit_price_inc_tax*transaction_sell_lines.quantity)+transaction_sell_lines.pos_line_tax_amount)
                             ELSE 0
                             END
                            ) AS openTotal,
                            SUM(CASE WHEN (c.name = 'DISPOSABLE E-CIGARETTES' or c.name = 'E-CIGARETTE PODS')
                            THEN ((transaction_sell_lines.unit_price_inc_tax*transaction_sell_lines.quantity)+transaction_sell_lines.pos_line_tax_amount)
                             ELSE 0
                             END
                            ) AS closeTotalOld,
                            SUM(CASE WHEN (c.name = 'DISPOSABLE E-CIGARETTES' or c.name = 'E-CIGARETTE PODS')
                            THEN (transaction_sell_lines.pos_line_tax_amount)
                             ELSE 0
                             END
                            ) AS closeTotal")
                    )
            ->where('transactions.type','sell')
            ->orderBy('transactions.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $nh_query = $nh_query->whereDate('transactions.transaction_date', '>=', $start)
                ->whereDate('transactions.transaction_date', '<=', $end);
                //$nh_query = $nh_query->whereBetween('transactions.transaction_date',array($start, $end));
            }
            if($request->customer_id != null){
                $nh_query = $nh_query->where('transactions.contact_id', $request->customer_id);
            }

            $nh_query = $nh_query->where('transactions.type', '=', 'sell')
            ->where('transactions.business_id', '=', $business_id)
            ->where('tax_rates.name', 'like', 'NH Vape%')
            ->where('contacts.state', '=', 'New Hampshire')
            ->where(function($nh_query) {
                $nh_query->whereIn('c.name',['E-LIQUID','SALT E-LIQUID','DISPOSABLE E-CIGARETTES','E-CIGARETTE PODS']);
                //->orWhereIn('c2.name',['E-LIQUID','SALT E-LIQUID','DISPOSABLE E-CIGARETTES','E-CIGARETTE PODS']);
            })
            ->groupBy('invoice_no')->get();

            $nh_query = $nh_query->toArray();
            $arrayMG =$nh_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['name']                 = $val->name;
                $productArr['address']              = $val->address .','. $val->city .','.$val->state;
                $productArr['zip_code']             = $val->zip_code;
                $productArr['payment_status']             = $val->payment_status;
                $productArr['tax_id']   = $val->tax_id;

                $productArr['open_total']           = round($val->openTotal,2);

                $productArr['open_total_tax'] = round($productArr['open_total']  * (8 / 100),2);

                $productArr['close_total_tax'] = round($val->closeTotal,2);

                $productArr['close_total'] = round(($productArr['close_total_tax']   / 0.30),2);

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)
                ->editColumn(
                'open_total',
                        function ($row) {
                            return '<span class="display_currency open_total" data-currency_symbol="true" data-orig-value="'. $row['open_total'] .'">'. $row['open_total'] .'</span>';
                        }
                )
                ->editColumn(
                'close_total',
                        function ($row) {
                            $close_total_ml = $row['close_total'].' ML';
                            return '<span class="close_total" data-orig-value="'. $row['close_total'] .'">'. $close_total_ml .'</span>';
                        }
                )
                ->editColumn(
                'open_total_tax',
                        function ($row) {
                            return '<span class="display_currency open_total_tax" data-currency_symbol="true" data-orig-value="'. $row['open_total_tax'] .'">'. $row['open_total_tax'] .'</span>';
                        }
                )
                ->editColumn(
                'close_total_tax',
                        function ($row) {
                            return '<span class="display_currency close_total_tax" data-currency_symbol="true" data-orig-value="'. $row['close_total_tax'] .'">'. $row['close_total_tax'] .'</span>';
                        }
                )
                ->rawColumns(['open_total', 'open_total_tax', 'close_total', 'close_total_tax'])
            ->make(true);
            return $datatable;
    }

    public function getCollectedPaymentReport(Request $request){

        $business_id = $request->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id);
        $users = User::forDropdown($business_id, false);
        return view('report.collected_payment_report')
        ->with(compact('customers', 'users'));
        // return view('report.collected_payment_report');
    }
    public function getCollectedPaymentReportbyCustomers(Request $request){
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {

            // $contacts = Contact::where('contacts.business_id', $business_id)
            //     ->join('users AS u', 'contacts.sales_rep', '=', 'u.id')
            //     ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
            //     ->join('transaction_sell_lines AS tsl', 't.id', '=', 'tsl.transaction_id')
            //     ->active()
            //     ->where('t.type', 'sell')
            //     ->where('t.status', 'final')
            //     ->groupBy('transaction_id')
            //     ->select(
            //         DB::raw("(1-((sum(tsl.purchase_price*tsl.quantity))/(sum(tsl.unit_price_inc_tax*tsl.quantity)-tsl.line_discount_amount)))*100 as total_gp"),
            //         DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
            //         'contacts.name',
            //         't.invoice_no','t.id as transaction_id',
            //         't.total_before_tax',
            //         't.discount_amount',
            //         't.discount_type',
            //         't.final_total',
            //         DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
            //             TP.transaction_id=t.id) as total_paid'),
            //         't.created_at',
            //     );
            $contacts = Contact::where('contacts.business_id', $business_id)
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->Join('transaction_payments','t.id','=','transaction_payments.transaction_id')
                ->join('users AS u', 'transaction_payments.created_by', '=', 'u.id')
                ->active()
                // ->groupBy('contacts.id')
                ->select(
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                    'contacts.supplier_business_name',
                    'contacts.name',
                    'contacts.id',
                    't.final_total',
                    't.id as transaction_id',
                    'contacts.type as contact_type',
                    'transaction_payments.method as method',
                    'transaction_payments.amount as amount',
                    't.invoice_no',
                    'transaction_payments.paid_on',
                    'transaction_payments.created_at',
                    't.created_at',
                    't.ref_no',
                    'contacts.type as type',
                    'transaction_payments.cheque_number as cheque_number',
                    'transaction_payments.card_transaction_number as card_transaction_number',
                    'transaction_payments.bank_account_number as bank_account_number'
                )
                ->where('t.payment_status','paid')
                ->where('method','!=','advance')
                ->onlyCustomers();

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            // if (!empty($request->input('sr_id'))) {
            //     $contacts->where('u.id', $request->input('sr_id'));
            // }

            // if (!empty($request->input('contact_id'))) {
            //     $contacts->where('contacts.id', $request->input('contact_id'));
            // }
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

            if (!empty($start_date) && !empty($end_date) ) {
                $start_date = $start_date." 00:00:00";
                $end_date = $end_date." 23:59:59";

                $contacts->whereBetween(DB::raw('t.created_at'), [$start_date, $end_date]);
            }

             return Datatables::of($contacts)
                ->editColumn('user_name', function ($row) {
                    return $row->user_name;
                })
                ->editColumn('method', function ($row) use ($payment_types) {
                    $method = !empty($payment_types[$row->method]) ? $payment_types[$row->method] : '';
                    if ($row->method == 'cheque') {
                        $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    }
                    return $method;
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency amount" data-orig-value="' . $row->amount . '" data-currency_symbol = true>' . $row->amount . '</span>';
                })

                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->editColumn('paid_on', '{{@format_datetime($paid_on)}}')
                ->editColumn('invoice_no', function ($row) {
                     return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                })
                ->rawColumns(['invoice_no','user_name','method','created_at','paid_on','amount'])
                ->make(true);
        }

    }
    public function getCollectedPaymentReportbySuppliers(Request $request){
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {

                $contacts = Contact::where('contacts.business_id', $business_id)
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->Join('transaction_payments','t.id','=','transaction_payments.transaction_id')
                ->join('users AS u', 'transaction_payments.created_by', '=', 'u.id')
                ->active()
                // ->groupBy('contacts.id')
                ->select(
                    // DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    // DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    // DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    // DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                    // DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    // DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    // DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                    // DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    // DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                    // DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),

                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                    'contacts.supplier_business_name',
                    'contacts.name',
                    'contacts.id',
                    't.final_total',
                    't.id as transaction_id',
                    'contacts.type as contact_type',
                    'transaction_payments.amount as amount',
                    'transaction_payments.method as method',
                    't.invoice_no',
                    'transaction_payments.paid_on',
                    'transaction_payments.created_at',
                    't.created_at',
                    't.ref_no',
                    'contacts.type as type',
                    'transaction_payments.cheque_number as cheque_number',
                    'transaction_payments.card_transaction_number as card_transaction_number',
                    'transaction_payments.bank_account_number as bank_account_number'
                )
                ->where('method','!=','advance')
                ->where('t.payment_status','paid')
                ->onlySuppliers();

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

            // if (!empty($request->input('sr_id'))) {
            //     $contacts->where('u.id', $request->input('sr_id'));
            // }

            // if (!empty($request->input('contact_id'))) {
            //     $contacts->where('contacts.id', $request->input('contact_id'));
            // }

            if (!empty($start_date) && !empty($end_date) ) {
                $start_date = $start_date." 00:00:00";
                $end_date = $end_date." 23:59:59";

                $contacts->whereBetween(DB::raw('t.created_at'), [$start_date, $end_date]);
            }
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);


             return Datatables::of($contacts)
                ->editColumn('user_name', function ($row) {
                    return $row->user_name;
                })
                ->editColumn('method', function ($row) use ($payment_types) {
                    $method = !empty($payment_types[$row->method]) ? $payment_types[$row->method] : '';
                    if ($row->method == 'cheque') {
                        $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method .= '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    }
                    return $method;
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->editColumn('paid_on', '{{@format_datetime($paid_on)}}')

                ->editColumn('ref_no', function ($row) {
                    return '<button type="button" data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" class="btn btn-link btn-modal" data-container=".view_modal"  >' . $row->ref_no . '</button>';
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency amount" data-orig-value="' . $row->amount . '" data-currency_symbol = true>' . $row->amount . '</span>';
                })

                ->rawColumns(['ref_no','user_name','method','created_at','paid_on','amount'])
                ->make(true);
        }
    }

    // CT VAPE REPORT
    public function ctVapeTransactionReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        return view('report.ctvapetransaction_report')->with(compact('customers'));
    }

    public function getCtVapeTransactionReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        // $nh_query = $cigar_query = [];
        $nh_query =  DB::table('transactions')
            ->join('transaction_sell_lines','transaction_sell_lines.transaction_id','=','transactions.id')
            ->join('products','products.id','=', 'transaction_sell_lines.product_id')
            ->join('contacts','contacts.id','=','transactions.contact_id')
            ->leftJoin('categories as c', 'products.category_id', '=', 'c.id')
            ->leftJoin('tax_rates','tax_rates.id','=','transaction_sell_lines.pos_line_tax_id')
            ->select(
                        //'products.id as pid',
                        //'transaction_sell_lines.id as tranline_id',
                        //'contacts.id as contact_id',
                        DB::raw('DATE_FORMAT(transactions.transaction_date, "%m/%d/%Y") as sale_date'),
                        'transactions.invoice_no as invoice_no',
                        'contacts.tax_number as tax_id',
                        'contacts.name as name',
                        'contacts.address_line_1 as address',
                        'contacts.tobacco_license_no as tobacco_license_no',
                        'contacts.city as city',
                        'contacts.state as state',
                        //'c.id as cid',
                        //'c.name as category',
                        //DB::raw("SUM(transaction_sell_lines.unit_price_inc_tax*transaction_sell_lines.quantity) as totalPrice"),
                        DB::raw("SUM(CASE WHEN (c.name = 'E-LIQUID' or c.name = 'SALT E-LIQUID')
                            THEN ((transaction_sell_lines.unit_price_inc_tax*transaction_sell_lines.quantity)+transaction_sell_lines.pos_line_tax_amount)
                             ELSE 0
                             END
                            ) AS openTotal,
                            SUM(CASE WHEN (c.name = 'DISPOSABLE E-CIGARETTES' or c.name = 'E-CIGARETTE PODS')
                            THEN ((transaction_sell_lines.unit_price_inc_tax*transaction_sell_lines.quantity)+transaction_sell_lines.pos_line_tax_amount)
                             ELSE 0
                             END
                            ) AS closeTotalOld,
                            SUM(CASE WHEN (c.name = 'DISPOSABLE E-CIGARETTES' or c.name = 'E-CIGARETTE PODS')
                            THEN (transaction_sell_lines.pos_line_tax_amount)
                             ELSE 0
                             END
                            ) AS closeTotal,
                            SUM(CASE WHEN (c.name = 'DISPOSABLE E-CIGARETTES' or c.name = 'E-CIGARETTE PODS')
                            THEN (transaction_sell_lines.quantity*10)
                             ELSE 0
                             END
                            ) AS closeTotalml")
                    )
            ->orderBy('transactions.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $nh_query = $nh_query->whereDate('transactions.transaction_date', '>=', $start)
                ->whereDate('transactions.transaction_date', '<=', $end);
                //$nh_query = $nh_query->whereBetween('transactions.transaction_date',array($start, $end));
            }
            if($request->customer_id != null){
                $nh_query = $nh_query->where('transactions.contact_id', $request->customer_id);
            }

            $nh_query = $nh_query->where('transactions.type', '=', 'sell')
            ->where('transactions.business_id', '=', $business_id)
            ->where('tax_rates.name', 'like', 'CT Vape%')
            ->where('contacts.state', '=', 'Connecticut')
            ->where(function($nh_query) {
                $nh_query->whereIn('c.name',['E-LIQUID','SALT E-LIQUID','DISPOSABLE E-CIGARETTES','E-CIGARETTE PODS']);
                //->orWhereIn('c2.name',['E-LIQUID','SALT E-LIQUID','DISPOSABLE E-CIGARETTES','E-CIGARETTE PODS']);
            })
            ->groupBy('invoice_no')->get();

            $nh_query = $nh_query->toArray();
            $arrayMG =$nh_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['name']                 = $val->name;
                $productArr['address']              = $val->address .','. $val->city .','.$val->state;
                $productArr['tax_id']   = $val->tax_id;
                $productArr['open_total']           = round($val->openTotal,2);

                $productArr['open_total_tax'] = round($productArr['open_total']  * (10 / 100),2);

                $productArr['close_total_tax'] = round($val->closeTotal,2);

                //$productArr['close_total'] = round(($productArr['close_total_tax']   / 0.30),2);
                $productArr['close_total'] = round($val->closeTotalml,0);

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)
                ->editColumn(
                'open_total',
                        function ($row) {
                            return '<span class="display_currency open_total" data-currency_symbol="true" data-orig-value="'. $row['open_total'] .'">'. $row['open_total'] .'</span>';
                        }
                )
                ->editColumn(
                'close_total',
                        function ($row) {
                            $close_total_ml = $row['close_total'].' ML';
                            return '<span class="close_total" data-orig-value="'. $row['close_total'] .'">'. $close_total_ml .'</span>';
                        }
                )
                ->editColumn(
                'open_total_tax',
                        function ($row) {
                            return '<span class="display_currency open_total_tax" data-currency_symbol="true" data-orig-value="'. $row['open_total_tax'] .'">'. $row['open_total_tax'] .'</span>';
                        }
                )
                ->editColumn(
                'close_total_tax',
                        function ($row) {
                            return '<span class="display_currency close_total_tax" data-currency_symbol="true" data-orig-value="'. $row['close_total_tax'] .'">'. $row['close_total_tax'] .'</span>';
                        }
                )
                ->rawColumns(['open_total', 'open_total_tax', 'close_total', 'close_total_tax'])
            ->make(true);
            return $datatable;
    }
    // CT VAPRE REPORT

    // PA VAPE REPORT
    public function paVapeTransactionReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        return view('report.pavapetransaction_report')->with(compact('customers'));
    }

    public function getPaVapeTransactionReport(Request $request)
    {
        $query =  DB::table('transactions')
            ->leftJoin('contacts','contacts.id','=','transactions.contact_id')
            ->leftJoin('transaction_sell_lines','transaction_sell_lines.transaction_id','=','transactions.id')
            ->leftJoin('tax_rates','tax_rates.id','=','transaction_sell_lines.pos_line_tax_id')
            ->select(
                        'transactions.id as tra_id',
                        // 'transactions.transaction_date as invoice_date',
                        DB::raw('DATE_FORMAT(transactions.transaction_date, "%m/%d/%Y") as invoice_date'),
                        'transactions.invoice_no as invoice_no',
                        // 'transaction_sell_lines.pos_line_tax_id as tax_id',
                         DB::raw('SUM(transaction_sell_lines.pos_line_tax_amount) AS tax_amount'),
                       // 'transaction_sell_lines.pos_line_tax_amount as tax_amount',
                        'contacts.name as name',
                        'contacts.tax_number as tax_id',
                        'contacts.address_line_1 as address',
                        'contacts.city as city',
                        'contacts.state as state',
                        'contacts.zip_code as zip_code',
                        'transactions.payment_status as payment_status',
                        'transactions.item_qty as item_qty',
                        'transactions.sub_total as price',
                        'transactions.tax_amount as tax_amount1'
                    )
            ->orderBy('transactions.id','ASC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));
                $query = $query->whereBetween('transactions.transaction_date',array($start, $end));
                // $query = $query->whereBetween('transaction_sell_lines.created_at',array($start, $end));
            }
            if($request->customer_id != null){
                $query = $query->where('transactions.contact_id', $request->customer_id);
            }
            // $query = $query->where('tax_rates.name', 'like', '%PA VAPE%');
        //    $query->where('contacts.state', '=', 'Pennsylvania')->where('tax_rates.name', 'like', '%PA VAPE%');
           $query->where('tax_rates.state', 'Pennsylvania');
                           // $query = $query->where('tax_rates.name', 'like', 'MA VAPE%')
                           // ->orwhere('tax_rates.name', 'like', 'MA Vape%');

            $query = $query->groupBy('transactions.id')->having('tax_amount','>',0)
                        ->get();
            // dd($query);
        $itemArr = [];
        foreach($query as $val)
        {
            // dd($val->tra_id);
            $productArr['invoice_date']     = $val->invoice_date;
            $productArr['invoice_no']       = $val->invoice_no;
            $productArr['tax_id']           = $val->tax_id;
            $productArr['name']             = $val->name;
            $productArr['address']          = $val->address;
            $productArr['city']             = $val->city;
            $productArr['state']            = $val->state;
            $productArr['zip_code']         = $val->zip_code;
            $productArr['payment_status']   = $val->payment_status;
            $productArr['item_qty']         = $val->item_qty;
            $productArr['price']            = $val->price;
            $productArr['order_note']       = 'YES';
            $productArr['tax_amount']       = $val->tax_amount;
            $itemArr[]                  = $productArr;
        }
        return Datatables::of($itemArr)->make(true);
    }
    // PA VAPE REPORT

    // Deleted Invoices list
    public function DeletedInvoicesReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        return view('report.deleted_invoices_report')->with(compact('customers'));
    }

    public function getDeletedInvoicesReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('delinvoicelogs')
            ->join('contacts','contacts.id','=','delinvoicelogs.contact_id')
            ->join('users as d','d.id','=','delinvoicelogs.deleted_by')
            ->select(
                        'delinvoicelogs.id as id',
                        DB::raw('DATE_FORMAT(delinvoicelogs.transaction_date, "%m/%d/%Y") as sale_date'),
                        'delinvoicelogs.invoice_no as invoice_no',
                        'contacts.name as name',
                        'delinvoicelogs.reason as reason',
                        'delinvoicelogs.sell_lines as sell_lines',
                        'delinvoicelogs.transaction_line as transaction_line',
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->orderBy('delinvoicelogs.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('delinvoicelogs.created_at', '>=', $start)
                ->whereDate('delinvoicelogs.created_at', '<=', $end);

            }
            if($request->customer_id != null){
                $del_invoice_query = $del_invoice_query->where('delinvoicelogs.contact_id', $request->customer_id);
            }

            $del_invoice_query = $del_invoice_query ->where('delinvoicelogs.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                   = $val->id;
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['name']                 = $val->name;
                $productArr['reason']               = $val->reason;
                $productArr['deleted_by']           = $val->deleted_by;
                $productArr['sell_lines']           = json_decode($val->sell_lines,true);
                $productArr['transaction_line']     = json_decode($val->transaction_line,true);

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)

                ->editColumn('invoice_no', function ($row) {
                    if(!empty($row['sell_lines']) && !empty($row['transaction_line']))
                    {
                    return '<a data-href="' . action('ReportController@getDeletedInvoiceReportDetail', [$row['id']])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row['invoice_no'] . '</a>';
                    }
                    else
                    {
                        return $row['invoice_no'];
                    }
                })

                ->editColumn('download_pdf', function ($row){
                   return  '<button class="btn btn-sm btn-primary"><a href="'. action('SellPosController@download_deleted_invoice', [$row['id']]) . '" target="_blank" style="color:white; ">Download PDF</a></button>';
                })
                ->rawColumns(['invoice_no','download_pdf'])
            ->make(true);
            return $datatable;
    }

    public function getDeletedInvoiceReportDetail($id)
    {
        $contact = "";
        $business = "";
        $extra_document = "";
        $purchase_lines = "";
        $payment_lines = "";
        $sell = Delinvoicelog::where('id', $id)->firstOrFail();
        if(!empty($sell))
        {
            $payment_methods = $this->productUtil->payment_types($sell->location_id, true);
            $payment_types = $this->transactionUtil->payment_types($sell->location_id, true);
            $taxes = TaxRate::where('business_id', $sell->business_id)
                            ->pluck('name', 'id');
            $contact = Contact::where('id', $sell->contact_id)->firstOrFail();
            $business = BusinessLocation::where('id', $sell->location_id)->firstOrFail();

            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
            $shipping_statuses = $this->transactionUtil->shipping_statuses();
            $shipping_status_colors = [
                'ordered' => 'bg-yellow',
                'packed' => 'bg-info',
                'shipped' => 'bg-navy',
                'delivered' => 'bg-green',
                'cancelled' => 'bg-red',
                ];
            $shipping_status_colors = $shipping_status_colors;
            $common_settings = session()->get('business.common_settings');
            $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;

            $t_line = json_decode($sell->transaction_line);
            $payment_lines = json_decode($sell->payment_lines);
            $sell_lines = (array) json_decode($sell->sell_lines,true);
            /*echo "<pre>";
            print_r($sell_lines); exit;*/
            if(count($sell_lines)>0)
            {
                foreach($sell_lines as $key=>$sell_line)
                {
                    $sub_units = "";
                    $products = Product::where('id', $sell_line['product_id'])->with('unit','brand')->firstOrFail()->toArray();
                    $variations = Variation::where('id', $sell_line['variation_id'])->where('product_id', $sell_line['product_id'])->with('product_variation')->firstOrFail()->toArray();
                    if(!empty($sell_line['sub_unit_id']))
                    {
                        $sub_units = Unit::where('id', $sell_line['sub_unit_id'])->firstOrFail()->toArray();
                    }
                    $sell_lines[$key]['product']=$products;
                    $sell_lines[$key]['variations']=$variations;
                    $sell_lines[$key]['sub_unit']=$sub_units;
                }
                /*echo "<pre>";
                print_r($t_line); exit;*/
            }
        }
        return view('report.partials.deleted_invoice_details')->with(compact('sell','contact','business','sell_lines','taxes','payment_lines','payment_methods','payment_types','t_line','pos_settings','shipping_statuses','shipping_status_colors','is_warranty_enabled'));
    }
    // Deleted Invoices list

    // Deleted Transactions Payments list
    public function DeletedTransactionPaymentsReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        return view('report.deleted_transaction_payments_report')->with(compact('customers','suppliers'));
    }

    public function getDeletedTransactionPaymentsReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $payment_types = $this->transactionUtil->payment_types(null, true);
        $del_invoice_query =  DB::table('delpaymentslog')
            ->join('contacts','contacts.id','=','delpaymentslog.contact_id')
            ->join('users as c','c.id','=','delpaymentslog.created_by')
            ->join('users as d','d.id','=','delpaymentslog.deleted_by')
            ->select(
                        'delpaymentslog.*',
                        'delpaymentslog.id as id',
                        DB::raw('DATE_FORMAT(delpaymentslog.created_at, "%m/%d/%Y") as deleted_at'),
                        DB::raw('DATE_FORMAT(delpaymentslog.paid_on, "%m/%d/%Y") as payment_date'),
                        'delpaymentslog.payment_ref_no as ref_no',
                        'contacts.name as name',
                        'delpaymentslog.contact_type as type',
                        'delpaymentslog.amount as amount',
                        'delpaymentslog.reason as reason',
                        'delpaymentslog.method as method',
                        'delpaymentslog.note as note',
                        'delpaymentslog.invoice_no as invoice_no',
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->where('delpaymentslog.description','deleted')
            ->orderBy('delpaymentslog.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('delpaymentslog.created_at', '>=', $start)
                ->whereDate('delpaymentslog.created_at', '<=', $end);

            }
            if($request->customer_id != null){
                $del_invoice_query = $del_invoice_query->where('delpaymentslog.contact_id', $request->customer_id);
            }

            if($request->supplier_id != null){
                $del_invoice_query = $del_invoice_query->where('delpaymentslog.contact_id', $request->supplier_id);
            }

            $del_invoice_query = $del_invoice_query ->where('delpaymentslog.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                   = $val->id;
                $productArr['deleted_at']           = $val->deleted_at;
                $productArr['payment_date']         = $val->payment_date;
                $productArr['ref_no']               = $val->ref_no;
                $productArr['type']                 = $val->type;
                $productArr['name']                 = $val->name;
                $productArr['amount']               = $val->amount;
                $productArr['reason']               = $val->reason;

                if(!empty($val->transaction_no) && $val->method!='credit_memo')
                {
                    $transaction_no = ' ( '.__('lang_v1.transaction_no').': '.$val->transaction_no.')';
                    $productArr['method'] = $payment_types[$val->method].$transaction_no;
                }
                elseif(!empty($val->cheque_number))
                {
                    $cheque_number = ' ( '.__('lang_v1.cheque_no').': '.$val->cheque_number.')';
                    $productArr['method'] = $payment_types[$val->method].$cheque_number;
                }
                elseif(!empty($val->card_transaction_number))
                {
                    $card_transaction_number = ' ( '.__('lang_v1.card_transaction_no').': '.$val->card_transaction_number.')';
                    $productArr['method'] = $payment_types[$val->method].$card_transaction_number;
                }
                elseif(!empty($val->bank_account_number))
                {
                    $bank_account_number = ' ( '.__('lang_v1.bank_account_no').': '.$val->bank_account_number.')';
                    $productArr['method'] = $payment_types[$val->method].$bank_account_number;
                }
                else
                {
                    $productArr['method'] = $payment_types[$val->method];
                }

                $productArr['note']                 = $val->note;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['created_by']           = $val->created_by;
                $productArr['deleted_by']           = $val->deleted_by;

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)

                ->editColumn('amount', '$ {{@number_format($amount,2)}}')
                //->editColumn('deleted_at', '{{@format_datetime($deleted_at)}}')
                //->editColumn('payment_date', '{{@format_datetime($payment_date)}}')
                ->rawColumns(['amount','payment_date'])
                ->make(true);
            return $datatable;
    }
    // Deleted Transactions Payments list

    // Deleted Credit Memo list
    public function DeletedCreditmemoReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        return view('report.deleted_creditmemo_report')->with(compact('customers'));
    }

    public function getDeletedCreditmemoReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('creditmemo_activity_log')
            ->join('contacts','contacts.id','=','creditmemo_activity_log.contact_id')
            ->join('users as c','c.id','=','creditmemo_activity_log.created_by')
            ->join('users as d','d.id','=','creditmemo_activity_log.user_id')
            ->select(
                        'creditmemo_activity_log.id as id',
                        'creditmemo_activity_log.quantity_returned as quantity_returned',
                        'creditmemo_activity_log.payment_status as payment_status',
                        'creditmemo_activity_log.final_total as final_total',
                        'creditmemo_activity_log.tax as tax',
                        'creditmemo_activity_log.amount_paid as amount_paid',
                        'creditmemo_activity_log.discount_amount as discount_amount',
                        'creditmemo_activity_log.box_qty as box_qty',
                        DB::raw('DATE_FORMAT(creditmemo_activity_log.transaction_date, "%m/%d/%Y") as sale_date'),
                        'creditmemo_activity_log.invoice_no as invoice_no',
                        'contacts.name as name',
                        'creditmemo_activity_log.reason as reason',
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->where('creditmemo_activity_log.description','deleted')
            ->orderBy('creditmemo_activity_log.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('creditmemo_activity_log.created_at', '>=', $start)
                ->whereDate('creditmemo_activity_log.created_at', '<=', $end);

            }
            if($request->customer_id != null){
                $del_invoice_query = $del_invoice_query->where('creditmemo_activity_log.contact_id', $request->customer_id);
            }

            $del_invoice_query = $del_invoice_query ->where('creditmemo_activity_log.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                   = $val->id;
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['name']                 = $val->name;
                $productArr['quantity_returned']    = $val->quantity_returned;
                $productArr['payment_status']       = $val->payment_status;
                $productArr['final_total']          = $val->final_total;
                $productArr['amount_paid']          = $val->amount_paid;
                $productArr['tax']                  = $val->tax;
                $productArr['discount_amount']      = $val->discount_amount;
                $productArr['box_qty']              = $val->box_qty;
                $productArr['reason']               = $val->reason;
                $productArr['created_by']           = $val->created_by;
                $productArr['deleted_by']           = $val->deleted_by;

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)
                ->editColumn('final_total', '$ {{@number_format($final_total,2)}}')
                ->editColumn('amount_paid', '$ {{@number_format($amount_paid,2)}}')
                ->editColumn('tax', '$ {{@number_format($tax,2)}}')
                ->editColumn('discount_amount', '$ {{@number_format($discount_amount,2)}}')
                ->editColumn(
                    'payment_status',
                    '<a class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}</span></a>'
                )
                ->rawColumns(['final_total','amount_paid','tax','discount_amount','payment_status'])
            ->make(true);
            return $datatable;
    }
    // Deleted Credit Memo list

    // Deleted VendorCredit Memo list
    public function DeletedVendorCreditmemoReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::suppliersDropdown($business_id, false);
        return view('report.deleted_vendorcreditmemo_report')->with(compact('customers'));
    }

    public function getDeletedVendorCreditmemoReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('vendorcreditmemo_activity_log')
            ->join('contacts','contacts.id','=','vendorcreditmemo_activity_log.contact_id')
            ->join('users as c','c.id','=','vendorcreditmemo_activity_log.created_by')
            ->join('users as d','d.id','=','vendorcreditmemo_activity_log.user_id')
            ->select(
                        'vendorcreditmemo_activity_log.id as id',
                        'vendorcreditmemo_activity_log.quantity_returned as quantity_returned',
                        'vendorcreditmemo_activity_log.total_unit_price as total_unit_price',
                        'vendorcreditmemo_activity_log.total_loose_qty as total_loose_qty',
                        'vendorcreditmemo_activity_log.total_loose_price as total_loose_price',

                        'vendorcreditmemo_activity_log.payment_status as payment_status',
                        'vendorcreditmemo_activity_log.final_total as final_total',
                        'vendorcreditmemo_activity_log.tax as tax',
                        'vendorcreditmemo_activity_log.amount_paid as amount_paid',
                        'vendorcreditmemo_activity_log.discount_amount as discount_amount',
                        'vendorcreditmemo_activity_log.shipping_charges as shipping_charges',
                        'vendorcreditmemo_activity_log.box_qty as box_qty',
                        DB::raw('DATE_FORMAT(vendorcreditmemo_activity_log.transaction_date, "%m/%d/%Y") as sale_date'),
                        'vendorcreditmemo_activity_log.invoice_no as invoice_no',
                        'contacts.name as name',
                        'vendorcreditmemo_activity_log.reason as reason',
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->where('vendorcreditmemo_activity_log.description','deleted')
            ->orderBy('vendorcreditmemo_activity_log.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('vendorcreditmemo_activity_log.created_at', '>=', $start)
                ->whereDate('vendorcreditmemo_activity_log.created_at', '<=', $end);

            }
            if($request->customer_id != null){
                $del_invoice_query = $del_invoice_query->where('vendorcreditmemo_activity_log.contact_id', $request->customer_id);
            }

            $del_invoice_query = $del_invoice_query ->where('vendorcreditmemo_activity_log.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                   = $val->id;
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['name']                 = $val->name;
                $productArr['quantity_returned']    = $val->quantity_returned;
                $productArr['total_unit_price']     = $val->total_unit_price;
                $productArr['total_loose_qty']      = $val->total_loose_qty;
                $productArr['total_loose_price']    = $val->total_loose_price;
                $productArr['payment_status']       = $val->payment_status;
                $productArr['final_total']          = $val->final_total;
                $productArr['amount_paid']          = $val->amount_paid;
                $productArr['tax']                  = $val->tax;
                $productArr['discount_amount']      = $val->discount_amount;
                $productArr['shipping_charges']     = $val->shipping_charges;
                $productArr['box_qty']              = $val->box_qty;
                $productArr['reason']               = $val->reason;
                $productArr['created_by']           = $val->created_by;
                $productArr['deleted_by']           = $val->deleted_by;

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)
                ->editColumn('final_total', '$ {{@number_format($final_total,2)}}')
                ->editColumn('amount_paid', '$ {{@number_format($amount_paid,2)}}')
                ->editColumn('total_unit_price', '$ {{@number_format($total_unit_price,2)}}')
                ->editColumn('total_loose_price', '$ {{@number_format($total_loose_price,2)}}')
                ->editColumn('tax', '$ {{@number_format($tax,2)}}')
                ->editColumn('discount_amount', '$ {{@number_format($discount_amount,2)}}')
                ->editColumn('shipping_charges', '$ {{@number_format($shipping_charges,2)}}')
                ->editColumn(
                    'payment_status',
                    '<a class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}</span></a>'
                )
                ->rawColumns(['final_total','amount_paid','tax','discount_amount','payment_status','shipping_charges','total_unit_price','total_loose_price'])
            ->make(true);
            return $datatable;
    }
    // Deleted VendorCredit Memo list

    // Deleted Expenses Memo list
    public function DeletedExpensesReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::contactDropdown($business_id, false,false);
        return view('report.deleted_expenses_report')->with(compact('customers'));
    }

    public function getDeletedExpensesReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('expenses_activity_log')
            ->join('contacts','contacts.id','=','expenses_activity_log.contact_id')
            ->join('users as c','c.id','=','expenses_activity_log.created_by')
            ->join('users as d','d.id','=','expenses_activity_log.user_id')
            ->leftJoin('users AS U', 'expenses_activity_log.expense_for', '=', 'U.id')
            ->leftJoin('tax_rates as tr', 'expenses_activity_log.tax_id', '=', 'tr.id')
            ->leftJoin('expense_categories AS ec', 'expenses_activity_log.expense_category', '=', 'ec.id')
            ->select(
                        'expenses_activity_log.id as id',
                        'expenses_activity_log.payment_status as payment_status',
                        'expenses_activity_log.final_total as final_total',
                        'expenses_activity_log.tax as tax',
                        'expenses_activity_log.amount_paid as amount_paid',
                        'expenses_activity_log.is_recurring',
                        'expenses_activity_log.recur_interval',
                        'expenses_activity_log.recur_interval_type',
                        'expenses_activity_log.recur_repetitions',
                        'expenses_activity_log.subscription_repeat_on',
                        'expenses_activity_log.recur_parent_id',
                        'expenses_activity_log.type',
                        DB::raw('DATE_FORMAT(expenses_activity_log.transaction_date, "%m/%d/%Y") as sale_date'),
                        'expenses_activity_log.invoice_no as invoice_no',
                        'ec.name as category',
                        'contacts.name as name',
                        'expenses_activity_log.reason as reason',
                        DB::raw("CONCAT(tr.name ,' (', tr.amount ,' )') as tax"),
                        DB::raw("CONCAT(COALESCE(U.surname, ''),' ',COALESCE(U.first_name, ''),' ',COALESCE(U.last_name,'')) as expense_for"),
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->where('expenses_activity_log.description','deleted')
            ->orderBy('expenses_activity_log.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('expenses_activity_log.created_at', '>=', $start)
                ->whereDate('expenses_activity_log.created_at', '<=', $end);

            }
            if($request->customer_id != null){
                $del_invoice_query = $del_invoice_query->where('expenses_activity_log.contact_id', $request->customer_id);
            }

            $del_invoice_query = $del_invoice_query ->where('expenses_activity_log.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                   = $val->id;
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['category']             = $val->category;
                $productArr['name']                 = $val->name;

                $productArr['payment_status']       = $val->payment_status;
                $productArr['final_total']          = $val->final_total;
                $productArr['amount_paid']          = $val->amount_paid;
                $productArr['tax']                  = $val->tax;

                $productArr['reason']               = $val->reason;

                $productArr['expense_for']          = $val->expense_for;
                $productArr['created_by']           = $val->created_by;
                $productArr['deleted_by']           = $val->deleted_by;

                $productArr['is_recurring']         = $val->is_recurring;
                $productArr['recur_interval']       = $val->recur_interval;
                $productArr['recur_interval_type']  = $val->recur_interval_type;
                $productArr['recur_repetitions']    = $val->recur_repetitions;
                $productArr['subscription_repeat_on'] = $val->subscription_repeat_on;
                $productArr['recur_parent_id']      = $val->recur_parent_id;
                $productArr['type']                 = $val->type;

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)
                ->editColumn('final_total', '$ {{@number_format($final_total,2)}}')
                ->editColumn('amount_paid', '$ {{@number_format($amount_paid,2)}}')
                ->editColumn('invoice_no', function($row){
                    $ref_no = $row['invoice_no'];
                    if (!empty($row['is_recurring'])) {
                        $ref_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.recurring_expense') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row['recur_parent_id'])) {
                        $ref_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.generated_recurring_expense') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if ($row['type'] == 'expense_refund') {
                        $ref_no .= ' &nbsp;<small class="label bg-gray">' . __('lang_v1.refund') . '</small>';
                    }

                    return $ref_no;
                })
                ->editColumn(
                    'payment_status',
                    '<a class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}</span></a>'
                )
                ->addColumn('recur_details', function($row){
                    $details = '<small>';
                    if ($row['is_recurring'] == 1) {
                        $type = $row['recur_interval'] == 1 ? Str::singular(__('lang_v1.' . $row['recur_interval_type'])) : __('lang_v1.' . $row['recur_interval_type']);
                        $recur_interval = $row['recur_interval'] . $type;

                        $details .= __('lang_v1.recur_interval') . ': ' . $recur_interval;
                        if (!empty($row['recur_repetitions'])) {
                            $details .= ', ' .__('lang_v1.no_of_repetitions') . ': ' . $row['recur_repetitions'];
                        }
                        if ($row['recur_interval_type'] == 'months' && !empty($row['subscription_repeat_on'])) {
                            $details .= '<br><small class="text-muted">' .
                            __('lang_v1.repeat_on') . ': ' . str_ordinal($row['subscription_repeat_on']) ;
                        }
                    } elseif (!empty($row['recur_parent_id'])) {
                        //$details .= __('lang_v1.recurred_from') . ': ' . $row->recurring_parent->ref_no;
                    }
                    $details .= '</small>';
                    return $details;
                })
                ->rawColumns(['invoice_no','recur_details','final_total','amount_paid','payment_status'])
            ->make(true);
            return $datatable;
    }
    // Deleted Expenses Memo list

    // Deleted Purchases list
    public function DeletedPurchasesReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::suppliersDropdown($business_id, false);
        return view('report.deleted_purchases_report')->with(compact('customers'));
    }

    public function getDeletedPurchasesReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('purchase_activity_log')
            ->join('contacts','contacts.id','=','purchase_activity_log.contact_id')
            ->join('users as c','c.id','=','purchase_activity_log.created_by')
            ->join('users as d','d.id','=','purchase_activity_log.user_id')
            ->select(
                        'purchase_activity_log.id as id',
                        'purchase_activity_log.payment_status as payment_status',
                        'purchase_activity_log.final_total as final_total',
                        'purchase_activity_log.amount_paid as amount_paid',
                        'purchase_activity_log.discount_amount as discount_amount',
                        'purchase_activity_log.shipping_charges as shipping_charges',
                        'purchase_activity_log.box_qty as box_qty',
                        'purchase_activity_log.purchase_lines as purchase_lines',
                        DB::raw('DATE_FORMAT(purchase_activity_log.received_date, "%m/%d/%Y") as received_date'),
                        DB::raw('DATE_FORMAT(purchase_activity_log.transaction_date, "%m/%d/%Y") as sale_date'),
                        'purchase_activity_log.invoice_no as invoice_no',
                        'contacts.name as name',
                        'purchase_activity_log.reason as reason',
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->where('purchase_activity_log.description','deleted')
            ->where('purchase_activity_log.type','purchase')
            ->orderBy('purchase_activity_log.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('purchase_activity_log.created_at', '>=', $start)
                ->whereDate('purchase_activity_log.created_at', '<=', $end);

            }
            if($request->customer_id != null){
                $del_invoice_query = $del_invoice_query->where('purchase_activity_log.contact_id', $request->customer_id);
            }

            $del_invoice_query = $del_invoice_query ->where('purchase_activity_log.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                   = $val->id;
                $productArr['received_date']        = $val->received_date;
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['name']                 = $val->name;
                $productArr['payment_status']       = $val->payment_status;
                $productArr['final_total']          = $val->final_total;
                $productArr['amount_paid']          = $val->amount_paid;
                $productArr['discount_amount']      = $val->discount_amount;
                $productArr['shipping_charges']     = $val->shipping_charges;
                $productArr['box_qty']              = $val->box_qty;
                $productArr['reason']               = $val->reason;
                $productArr['created_by']           = $val->created_by;
                $productArr['deleted_by']           = $val->deleted_by;
                $productArr['purchase_lines']       = json_decode($val->purchase_lines,true);

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)

                ->editColumn('invoice_no', function ($row) {
                    if(!empty($row['purchase_lines']))
                    {
                    return '<a data-href="' . action('ReportController@getDeletedPurchasesReportDetail', [$row['id']])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row['invoice_no'] . '</a>';
                    }
                    else
                    {
                        return '<a>' . $row['invoice_no'] . '</a>';
                    }
                })

                ->editColumn('final_total', '$ {{@number_format($final_total,2)}}')
                ->editColumn('amount_paid', '$ {{@number_format($amount_paid,2)}}')
                ->editColumn('discount_amount', '$ {{@number_format($discount_amount,2)}}')
                ->editColumn('shipping_charges', '$ {{@number_format($shipping_charges,2)}}')
                ->editColumn(
                    'payment_status',
                    '<a class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}</span></a>'
                )
                ->rawColumns(['invoice_no','final_total','amount_paid','discount_amount','payment_status','shipping_charges'])
            ->make(true);
            return $datatable;
    }

    public function getDeletedPurchasesReportDetail($id)
    {
        $contact = "";
        $business = "";
        $extra_document = "";
        $purchase_lines = "";
        $payment_lines = "";
        $purchase = PurchaseActivityLog::where('id', $id)->firstOrFail();
        if(!empty($purchase))
        {
            $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);
            $taxes = TaxRate::where('business_id', $purchase->business_id)
                            ->pluck('name', 'id');
            $contact = Contact::where('id', $purchase->contact_id)->firstOrFail();
            $business = BusinessLocation::where('id', $purchase->location_id)->firstOrFail();
            $extra_document = json_decode($purchase->extra_document);
            $payment_lines = json_decode($purchase->payment_lines);
            $purchase_lines = (array) json_decode($purchase->purchase_lines,true);
            /*echo "<pre>";
            print_r($purchase_lines); exit;*/
            if(count($purchase_lines)>0)
            {
                foreach($purchase_lines as $key=>$purchase_line)
                {
                    $sub_units = "";
                    $products = Product::where('id', $purchase_line['product_id'])->with('unit')->firstOrFail()->toArray();
                    $variations = Variation::where('id', $purchase_line['variation_id'])->where('product_id', $purchase_line['product_id'])->with('product_variation')->firstOrFail()->toArray();
                    if(!empty($purchase_line['sub_unit_id']))
                    {
                        $sub_units = Unit::where('id', $purchase_line['sub_unit_id'])->firstOrFail()->toArray();
                    }

                    $purchase_lines[$key]['product']=$products;
                    $purchase_lines[$key]['variations']=$variations;
                    $purchase_lines[$key]['sub_unit']=$sub_units;
                }
                //echo "<pre>";
                //print_r($purchase_lines); exit;
            }
        }
        return view('report.partials.deleted_purchase_details')->with(compact('purchase','contact','business','extra_document','purchase_lines','taxes','payment_lines','payment_methods'));
    }
    // Deleted Purchases list

    // Deleted Purchases Order list
    public function DeletedPurchasesOrderReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::suppliersDropdown($business_id, false);
        return view('report.deleted_purchases_order_report')->with(compact('customers'));
    }

    public function getDeletedPurchasesOrderReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('purchase_activity_log')
            ->join('contacts','contacts.id','=','purchase_activity_log.contact_id')
            ->join('users as c','c.id','=','purchase_activity_log.created_by')
            ->join('users as d','d.id','=','purchase_activity_log.user_id')
            ->select(
                        'purchase_activity_log.id as id',
                        'purchase_activity_log.status as purchase_status',
                        'purchase_activity_log.payment_status as payment_status',
                        'purchase_activity_log.final_total as final_total',
                        'purchase_activity_log.amount_paid as amount_paid',
                        'purchase_activity_log.discount_amount as discount_amount',
                        'purchase_activity_log.shipping_charges as shipping_charges',
                        DB::raw('DATE_FORMAT(purchase_activity_log.transaction_date, "%m/%d/%Y") as sale_date'),
                        'purchase_activity_log.invoice_no as invoice_no',
                        'contacts.name as name',
                        'purchase_activity_log.reason as reason',
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->where('purchase_activity_log.description','deleted')
            ->where('purchase_activity_log.type','purchase_order')
            ->orderBy('purchase_activity_log.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('purchase_activity_log.created_at', '>=', $start)
                ->whereDate('purchase_activity_log.created_at', '<=', $end);

            }
            if($request->customer_id != null){
                $del_invoice_query = $del_invoice_query->where('purchase_activity_log.contact_id', $request->customer_id);
            }

            $del_invoice_query = $del_invoice_query ->where('purchase_activity_log.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                   = $val->id;
                $productArr['sale_date']            = $val->sale_date;
                $productArr['invoice_no']           = $val->invoice_no;
                $productArr['name']                 = $val->name;
                $productArr['purchase_status']      = $val->purchase_status;
                $productArr['payment_status']       = $val->payment_status;
                $productArr['final_total']          = $val->final_total;
                $productArr['amount_paid']          = $val->amount_paid;
                $productArr['discount_amount']      = $val->discount_amount;
                $productArr['shipping_charges']     = $val->shipping_charges;
                $productArr['reason']               = $val->reason;
                $productArr['created_by']           = $val->created_by;
                $productArr['deleted_by']           = $val->deleted_by;

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)
                ->editColumn('final_total', '$ {{@number_format($final_total,2)}}')
                ->editColumn('amount_paid', '$ {{@number_format($amount_paid,2)}}')
                ->editColumn('discount_amount', '$ {{@number_format($discount_amount,2)}}')
                ->editColumn('shipping_charges', '$ {{@number_format($shipping_charges,2)}}')
                ->editColumn(
                    'purchase_status',
                    '<a ><span class="label @transaction_status($purchase_status) status-label" data-status-name="{{__(\'lang_v1.\' . $purchase_status)}}" data-orig-value="{{$purchase_status}}">{{__(\'lang_v1.\' . $purchase_status)}}
                        </span></a>'
                )
                ->editColumn(
                    'payment_status',
                    '<a class="view_payment_modal payment-status payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}</span></a>'
                )
                ->rawColumns(['final_total','amount_paid','discount_amount','purchase_status','payment_status','shipping_charges'])
            ->make(true);
            return $datatable;
    }
    // Deleted Purchases Order list

    // Deleted Stock Adjustments list
    public function DeletedStockAdjustmentsReport(Request $request)
    {
        return view('report.deleted_stock_adjustments');
    }

    public function getDeletedStockAdjustmentsReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('stockadjustment_activity_log')
            ->join('users as c','c.id','=','stockadjustment_activity_log.created_by')
            ->join('users as d','d.id','=','stockadjustment_activity_log.user_id')
            ->select(
                        'stockadjustment_activity_log.id as id',
                        'stockadjustment_activity_log.adjustment_type as adjustment_type',
                        'stockadjustment_activity_log.final_total as final_total',
                        'stockadjustment_activity_log.total_amount_recovered as total_amount_recovered',
                        DB::raw('DATE_FORMAT(stockadjustment_activity_log.transaction_date, "%m/%d/%Y") as sale_date'),
                        'stockadjustment_activity_log.invoice_no as invoice_no',
                        'stockadjustment_activity_log.reason as reason',
                        'stockadjustment_activity_log.additional_notes as additional_notes',
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        DB::raw("CONCAT(COALESCE(d.surname, ''), ' ', COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as deleted_by")
                    )
            ->where('stockadjustment_activity_log.description','deleted')
            ->orderBy('stockadjustment_activity_log.id','DESC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('stockadjustment_activity_log.created_at', '>=', $start)
                ->whereDate('stockadjustment_activity_log.created_at', '<=', $end);

            }

            $del_invoice_query = $del_invoice_query ->where('stockadjustment_activity_log.business_id', '=', $business_id)->get();

            $del_invoice_query = $del_invoice_query->toArray();
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            $total_order = 0;
            foreach($arrayMG as $val)
            {
                $productArr['id']                     = $val->id;
                $productArr['sale_date']              = $val->sale_date;
                $productArr['invoice_no']             = $val->invoice_no;
                $productArr['adjustment_type']        = $val->adjustment_type;
                $productArr['final_total']            = $val->final_total;
                $productArr['total_amount_recovered'] = $val->total_amount_recovered;
                $productArr['reason']                 = $val->reason;
                $productArr['additional_notes']       = $val->additional_notes;
                $productArr['created_by']             = $val->created_by;
                $productArr['deleted_by']             = $val->deleted_by;

                $itemArr[] = $productArr;
            }
            /*echo "<pre>";
            print_r($itemArr);
            exit;*/
            $datatable = Datatables::of($itemArr)
                ->editColumn('final_total', '$ {{@number_format($final_total,2)}}')
                ->editColumn('total_amount_recovered', '$ {{@number_format($total_amount_recovered,2)}}')
                ->editColumn('adjustment_type', function ($row) {
                    return __('stock_adjustment.' . $row['adjustment_type']);
                })
                ->rawColumns(['final_total','total_amount_recovered','adjustment_type'])
            ->make(true);
            return $datatable;
    }
    // Deleted Stock Adjustments list

    // Payables and receivables report start
    public function getPayablesReceivablesReport(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id);
        //$users = User::forDropdown($business_id, false);
        return view('report.view_payables_receivables')
        ->with(compact('customers', 'suppliers'));
    }
    public function getReceivablesShowReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {

            $contacts = Contact::where('contacts.business_id', $business_id)
                ->active()
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->groupBy('contacts.id')
                ->select(
                    'contacts.id',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.email',
                    'contacts.balance',
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                     DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                      DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                      DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                      DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                      DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                      DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                      DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                      DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received")
                )
                ->onlyCustomers();

            if (!empty($request->input('contact_id'))) {
                $contacts->where('contacts.id', $request->input('contact_id'));
            }

            $contacts_data = $contacts->get();

            $filtered_contats = [];
            foreach($contacts_data as $row)
            {
                //  $prev_payments_sum = TransactionPayment::leftJoin(
                //                             'transactions as t',
                //                             'transaction_payments.transaction_id',
                //                             '=',
                //                             't.id'
                //                         )
                //                             ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
                //                             ->where('transaction_payments.payment_for', $row->id)
                //                             ->whereDate('paid_on', '<', '2021-01-01')->select(DB::raw("SUM(transaction_payments.amount) as total_paid"))
                //                             ->first();

                //     $total_prev_invoice = number_format (($row->total_purchase + $row->total_invoice - $row->invoice_received -  $row->total_sell_return -  $row->total_purchase_return), 2, '.', '') - $row->balance;

                //     $balance_due=  $row->opening_balance - $row->opening_balance_paid + $total_prev_invoice - $prev_payments_sum->total_paid;

                    // $ledger_details = $this->transactionUtil->getLedgerDetails($row->id,'2021-01-01 00:00:00',date('Y-m-d H:i:s'),$row->balance);
                    // $balance_due = $ledger_details['balance_due'];

                    $total_final_invoice = 0;
                    $balance_due = 0;
                    $total_final_invoice = number_format (($row->total_invoice - $row->invoice_received -  $row->total_sell_return + $row->sell_return_paid), 2, '.', '') - $row->balance;

                    $balance_due=  ($row->opening_balance - $row->opening_balance_paid) + $total_final_invoice;
                    // return $balance_due;
                    //echo $balance_due."-----".$row->name."<br>";
                    if(!empty($balance_due))
                    {
                        $row->balance_due = $balance_due;
                        array_push($filtered_contats, $row);
                    }
            }
            //echo "<pre>";
            //print_r($filtered_contats); exit;

             return Datatables::of($filtered_contats)

                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    return '<span class="name" data-orig-value="' . $row->name . '" >' . $row->name . '</span>';
                })

                ->editColumn('mobile', function ($row) {
                    $mobile = $row->mobile;
                    return '<span class="mobile" data-orig-value="' . $row->mobile . '" >' . $row->mobile . '</span>';
                })

                ->editColumn('email', function ($row) {
                    $email = $row->email;
                    return '<span class="email" data-orig-value="' . $row->email . '" >' . $row->email . '</span>';
                })

                ->editColumn('open_balance', function ($row) {

                    $open_color = 'green';
                    if($row->balance_due > 0){
                        $open_color = 'red';
                    }
                    return  '<span class="display_currency open_balance" style="white-space:nowrap;font-weight:bolder;color:'.$open_color.';" data-currency_symbol="true" data-orig-value="'. $row->balance_due.'">'.'$ '. $row->balance_due.'</span>';

                })

                ->editColumn('last_order_date', function ($row) {
                    $last_order_date = Transaction::where('contact_id', $row->id)
                        ->select(
                              'transaction_date'
                        )
                        ->where('type', 'sell')
                        ->orderBy('transaction_date','desc')
                        ->first();
                    if(!empty($last_order_date))
                    {
                        $transaction_date = $this->transactionUtil->format_date($last_order_date['transaction_date'], true);
                        return '<span class="order_date" data-orig-value="' . $transaction_date . '" >' .$transaction_date. '</span>';
                    }

                })
                ->rawColumns(['name','mobile','email','open_balance','last_order_date'])
                ->make(true);
        }
    }

    public function getPayablesShowReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {

            $contacts = Contact::where('contacts.business_id', $business_id)
                ->active()
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->groupBy('contacts.id')
                ->select(
                    'contacts.id',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.email',
                    'contacts.balance',
                     DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                      DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                      DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                      DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                      DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                      DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                      DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                      DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received")
                )
                ->onlySuppliers();

            if (!empty($request->input('supplier_id'))) {
                $contacts->where('contacts.id', $request->input('supplier_id'));
            }
            $contacts_data = $contacts->get();

            $filtered_contats = [];
            foreach($contacts_data as $row)
            {
                //  $prev_payments_sum = TransactionPayment::leftJoin(
                //                             'transactions as t',
                //                             'transaction_payments.transaction_id',
                //                             '=',
                //                             't.id'
                //                         )
                //                             ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
                //                             ->where('transaction_payments.payment_for', $row->id)
                //                             ->whereDate('paid_on', '<', '2021-01-01')->select(DB::raw("SUM(transaction_payments.amount) as total_paid"))
                //                             ->first();

                //     $total_prev_invoice = number_format (($row->total_purchase - $row->purchase_paid + $row->total_invoice - $row->invoice_received -  $row->total_sell_return -  $row->total_purchase_return), 2, '.', '') - $row->balance;

                //     $balance_due = $row->opening_balance - $row->opening_balance_paid + $total_prev_invoice - $prev_payments_sum->total_paid;

                    $ledger_details = $this->transactionUtil->getLedgerDetails($row->id,'2021-01-01 00:00:00',date('Y-m-d H:i:s'),$row->balance);
                    $balance_due = $ledger_details['balance_due'];

                    //echo $balance_due."-----".$row->name."<br>";
                    if(!empty($balance_due))
                    {
                        $row->balance_due = $balance_due;
                        array_push($filtered_contats, $row);
                    }
            }
            //echo "<pre>";
            //print_r($filtered_contats); exit;

             return Datatables::of($filtered_contats)

                ->editColumn('name', function ($row) {
                    return '<span class="name" data-orig-value="' . $row->name . '" >' . $row->name . '</span>';
                })

                ->editColumn('mobile', function ($row) {
                    return '<span class="mobile" data-orig-value="' . $row->mobile . '" >' . $row->mobile . '</span>';
                })

                ->editColumn('email', function ($row) {
                    return '<span class="email" data-orig-value="' . $row->email . '" >' . $row->email . '</span>';
                })

                ->editColumn('open_balance', function ($row) {

                    $open_color = 'green';
                    if($row->balance_due > 0){
                        $open_color = 'red';
                    }

                    return  '<span class="display_currency open_balance" style="white-space:nowrap;font-weight:bolder;color:'.$open_color.';" data-currency_symbol="true" data-orig-value="'. $row->balance_due.'">'.'$ '. $row->balance_due.'</span>';


                })

                ->editColumn('last_order_date', function ($row) {
                    $last_order_date = Transaction::where('contact_id', $row->id)
                        ->select(
                              'transaction_date'
                        )
                        ->where('type', 'purchase')
                        ->orderBy('transaction_date','desc')
                        ->first();
                    if(!empty($last_order_date))
                    {
                        $transaction_date = $this->transactionUtil->format_date($last_order_date['transaction_date'], true);
                        return '<span class="order_date" data-orig-value="' . $transaction_date . '" >' .$transaction_date. '</span>';
                    }

                })
                ->rawColumns(['name','mobile','email','open_balance','last_order_date'])
                ->make(true);
        }
    }
    // Payables and receivables report end

    public function stateTaxReport(Request $request){
        $business_id = request()->session()->get('user.business_id');
        $customers = Contact::customersDropdown($business_id, false);
        $states = TaxRate::select('state')->whereNotNull('state')->where('state','<>','')->groupBy('state')->get();
        return view('report.state_tax_report')->with(compact('customers','states'));
    }

    public function getStateTaxReport(Request $request){
        $query =  DB::table('transactions')
            ->leftJoin('contacts','contacts.id','=','transactions.contact_id')
            ->leftJoin('transaction_sell_lines','transaction_sell_lines.transaction_id','=','transactions.id')
            ->leftJoin('tax_rates','tax_rates.id','=','transaction_sell_lines.pos_line_tax_id')
            ->select(
                        'transactions.id as tra_id',
                        // 'transactions.transaction_date as invoice_date',
                        DB::raw('DATE_FORMAT(transactions.transaction_date, "%m/%d/%Y") as invoice_date'),
                        'transactions.invoice_no as invoice_no',
                        // 'transaction_sell_lines.pos_line_tax_id as tax_id',
                         DB::raw('SUM(transaction_sell_lines.pos_line_tax_amount) AS tax_amount'),
                       // 'transaction_sell_lines.pos_line_tax_amount as tax_amount',
                        'contacts.name as name',
                        'contacts.tax_number as tax_id',
                        'contacts.address_line_1 as address',
                        'contacts.city as city',
                        'contacts.state as state',
                        'contacts.zip_code as zip_code',
                        'transactions.payment_status as payment_status',
                        'transactions.item_qty as item_qty',
                        'transactions.sub_total as price',
                        'transactions.tax_amount as tax_amount1'
                    )
            ->orderBy('transactions.id','ASC');
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));
                $query = $query->whereBetween('transactions.transaction_date',array($start, $end));
                // $query = $query->whereBetween('transaction_sell_lines.created_at',array($start, $end));
            }
            if($request->customer_id != null){
                $query = $query->where('transactions.contact_id', $request->customer_id);
            }
            // $query = $query->where('tax_rates.name', 'like', '%PA VAPE%');
        //    $query->where('contacts.state', '=', 'Pennsylvania')->where('tax_rates.name', 'like', '%PA VAPE%');
           $query->where('tax_rates.state', $request->state);
                           // $query = $query->where('tax_rates.name', 'like', 'MA VAPE%')
                           // ->orwhere('tax_rates.name', 'like', 'MA Vape%');

            $query = $query->groupBy('transactions.id')->having('tax_amount','>',0)
                        ->get();
            // dd($query);
        $itemArr = [];
        foreach($query as $val)
        {
            // dd($val->tra_id);
            $productArr['invoice_date']     = $val->invoice_date;
            $productArr['invoice_no']       = $val->invoice_no;
            $productArr['tax_id']           = $val->tax_id;
            $productArr['name']             = $val->name;
            $productArr['address']          = $val->address;
            $productArr['city']             = $val->city;
            $productArr['state']            = $val->state;
            $productArr['zip_code']         = $val->zip_code;
            $productArr['payment_status']   = $val->payment_status;
            $productArr['item_qty']         = $val->item_qty;
            $productArr['price']            = $val->price;
            $productArr['order_note']       = 'YES';
            $productArr['tax_amount']       = $val->tax_amount;
            $itemArr[]                  = $productArr;
        }
        return Datatables::of($itemArr)->make(true);
    }

    // Packed Order Items list
    public function PackedOrderItemsReport(Request $request)
    {
        return view('report.packed_order_items');
    }

    public function getPackedOrderItemsReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $del_invoice_query =  DB::table('orderpackinglogs')
            ->leftjoin(
                                'products as p',
                                'orderpackinglogs.product_id',
                                '=',
                                'p.id'
                            )
            ->join('users as c','c.id','=','orderpackinglogs.scanned_by')
            ->select(
                        DB::raw('orderpackinglogs.created_at as scanned_date'),
                        DB::raw('COUNT(orderpackinglogs.transaction_sell_line_id) as total_count'),
                        'orderpackinglogs.invoice_no as invoice_no',
                        DB::raw("CONCAT(COALESCE(c.surname, ''), ' ', COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) as created_by"),
                        'orderpackinglogs.scan_status as scan_status',
                        'p.name as product_name'
                    )
            ->groupBy([DB::raw("DATE(orderpackinglogs.created_at)"), 'orderpackinglogs.transaction_sell_line_id' , 'orderpackinglogs.scanned_by','orderpackinglogs.scan_status']);
            if($request->date_filter != null){
                $setDate = str_replace("~","-",$request->date_filter);
                $fullDate = explode('-', $setDate);
                $startDate = $fullDate[0];
                $endDate = $fullDate[1];
                $start = date("Y-m-d", strtotime($startDate));
                $end = date("Y-m-d", strtotime($endDate));

                $del_invoice_query = $del_invoice_query->whereDate('orderpackinglogs.created_at', '>=', $start)
                ->whereDate('orderpackinglogs.created_at', '<=', $end);

            }

            $del_invoice_query = $del_invoice_query->get();

            $del_invoice_query = $del_invoice_query->toArray();

            // echo "<pre>";
            // print_r($del_invoice_query);
            // exit;
            $arrayMG =$del_invoice_query;
            $itemArr = [];
            foreach($arrayMG as $val)
            {
                $productArr['scanned_date']           =  \Carbon::parse($val->scanned_date)->format('m/d/Y H:i:s A'); ;
                $productArr['product_name']           = $val->product_name;
                $productArr['total_count']            = $val->total_count;
                $productArr['invoice_no']             = $val->invoice_no;
                $productArr['created_by']             = $val->created_by;
                $productArr['scan_status']            = $val->scan_status;

                $itemArr[] = $productArr;
            }
            // echo "<pre>";
            // print_r($itemArr);
            // exit;
            $datatable = Datatables::of($itemArr)->make(true);
            return $datatable;
    }
    // Packed Order Items list




    public function jullReport(Request $request){
        $business_id = $request->session()->get('user.business_id');

        // if ($request->ajax()) {


            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";

        $br_account_rep = request()->br_account_rep;
        $br_sales_rep = request()->br_sales_rep;

            $query =//Transaction::
                // join(
                //     'transaction_sell_lines',
                //     'transactions.id',
                //     '=',
                //     'transaction_sell_lines.transaction_id'
                // )
            TransactionSellLine::leftjoin(
                    'transactions as t',
                    'transaction_sell_lines.transaction_id',
                    '=',
                    't.id'
                )
                ->join('contacts as c','t.contact_id','=','c.id')
                ->join('variations','transaction_sell_lines.variation_id','=','variations.id')

                ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                ->join('products','products.id','=','variations.product_id')
                ->join('categories','categories.id','=','products.category_id')
                ->select('categories.id',
                        'categories.name as category_name',
                        DB::raw('sum(transaction_sell_lines.quantity) as total_qty'),
                        DB::raw('sum(transaction_sell_lines.quantity *  transaction_sell_lines.unit_price_inc_tax) as total_sell_price'),
                        DB::raw('sum(transaction_sell_lines.quantity *  variations.dpp_inc_tax) as total_dpp_price'),
                        DB::raw('sum(transaction_sell_lines.quantity * (transaction_sell_lines.unit_price_inc_tax -  variations.dpp_inc_tax)) as gross_profit'),
                        DB::raw("(1-((sum(variations.dpp_inc_tax * transaction_sell_lines.quantity)) / (sum(transaction_sell_lines.unit_price_inc_tax * transaction_sell_lines.quantity))))*100 as gp")
                        // 'transaction_sell_lines.id'
                )
                ->whereBetween(DB::raw('date(transaction_sell_lines.created_at)'), [ $start_date, $end_date])
                ->where('c.account_rep','=',$br_account_rep)
                ->where('c.sales_rep','=',$br_sales_rep)

                ->groupBy('categories.id')->get()
                ;


            // return Datatables::of($query)
            //         ->editColumn('total_qty', function ($row) {
            //               return '<span data-is_quantity="true" class="display_currency pull-right mr-5total_qty" data-currency_symbol=false data-orig-value="' . (float)$row->total_qty . '">'. round($row->total_qty,2) . '</span>';

            //                 // return '<span class="total_negative_inventory pull-right total_qty" data-orig-value="' . $row->total_qty . '">' . round($row->total_qty,2) . '</span>';
            //         })
            //         ->editColumn('total_sell_price', function ($row) {
            //                 return '<span class="display_currency total_negative_inventory pull-right mr-5 total_sell_price" data-currency_symbol="true" data-orig-value="' . $row->total_sell_price . '">' . $row->total_sell_price . '</span>';
            //         })
            //         ->addColumn('profit', function ($row) {
            //                 $profit = $row->total_sell_price - $row->total_dpp_price ;
            //                 return '<span class="display_currency total_negative_inventory pull-right mr-5 profit" data-currency_symbol="true" data-orig-value="' . $profit . '">' . $profit. '</span>';
            //         })
            //         ->editColumn('total_dpp_price', function ($row) {
            //             return '<span class="display_currency total_negative_inventory pull-right mr-5 total_dpp_price" data-currency_symbol="true" data-orig-value="' . $row->total_dpp_price . '">' . $row->total_dpp_price . '</span>';
            //         })
            //         ->editColumn('gp', function ($row) {
            //             return '<span class=" gross-profit pull-right mr-5" >' . number_format($row->gp,2 ) ." %"  . '</span>';
            //         })
            //     ->removeColumn('id')
            //     ->rawColumns(['category_name','total_qty','profit' ,'total_sell_price','total_dpp_price','gp'])
            //     ->make(true);
                // foreach ($query as $data) {

                    echo '<pre>';
                    print_r($query);
                // }
                die;
        // }

        $account_rep = User::forDropdown($business_id, false);
        $sales_rep = User::forDropdown($business_id, false);
        // return view('report.report')->with(compact('account_rep', 'sales_rep'));

    }


    public function   productSellingReport(Request $request){
        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {


        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $start_date = $start_date." 00:00:00";
        $end_date = $end_date." 23:59:59";

        $br_account_rep = request()->br_account_rep;
        $br_sales_rep = request()->br_sales_rep;

            $query =
            //Transaction::
                // join(
                //     'transaction_sell_lines',
                //     'transactions.id',
                //     '=',
                //     'transaction_sell_lines.transaction_id'
                // )
            TransactionSellLine::leftjoin(
                    'transactions as t',
                    'transaction_sell_lines.transaction_id',
                    '=',
                    't.id'
                )
                ->join('contacts as c','t.contact_id','=','c.id')
                ->join('variations','transaction_sell_lines.variation_id','=','variations.id')

                ->join('variation_location_details','variation_location_details.variation_id','=','variations.id')
                ->join('products','products.id','=','variations.product_id')
                ->join('brands','products.brand_id','=','brands.id')
                ->join('categories','categories.id','=','products.category_id')
                ->where('t.type','like','sell')
                ->where('t.status','like','final')

                ->select('categories.id',
                        'categories.name as category_name',
                        'products.name as product_name',
                        'brands.name as brand_name',
                        't.invoice_no',
                        'transaction_sell_lines.quantity',
                        'c.name as customer_name',
                        'c.address_line_1',
                        'c.address_line_2',
                        'c.state',
                        'c.city',
                        'c.country',
                        'c.zip_code',
                        't.transaction_date',
                        DB::raw('sum(transaction_sell_lines.quantity) as total_qty'),
                        DB::raw('sum(transaction_sell_lines.quantity *  transaction_sell_lines.unit_price_inc_tax) as total_sell_price'),
                        DB::raw('sum(transaction_sell_lines.quantity *  variations.dpp_inc_tax) as total_dpp_price'),
                        DB::raw('sum(transaction_sell_lines.quantity * (transaction_sell_lines.unit_price_inc_tax -  variations.dpp_inc_tax)) as gross_profit'),
                        DB::raw("(1-((sum(variations.dpp_inc_tax * transaction_sell_lines.quantity)) / (sum(transaction_sell_lines.unit_price_inc_tax * transaction_sell_lines.quantity))))*100 as gp")
                        // 'transaction_sell_lines.id'
                )
                ->whereBetween(DB::raw('date(transaction_sell_lines.created_at)'), [ $start_date, $end_date])
                // ->where('c.account_rep','=',$br_account_rep)
                // ->where('c.sales_rep','=',$br_sales_rep)

                ->groupBy('transaction_sell_lines.id')
                ;

            if (request()->has('br_account_rep')) {
                $br_account_rep = request()->get('br_account_rep');
                if (!empty($br_account_rep)) {
                    $query->where('c.account_rep', $br_account_rep);
                }
            }
            if (request()->has('br_sales_rep')) {
                $br_sales_rep = request()->get('br_sales_rep');
                if (!empty($br_sales_rep)) {
                    $query->where('c.account_rep', $br_sales_rep);
                }
            }


                return Datatables::of($query)
                        ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
                        ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')

                        ->editColumn('quantity', function ($row) {
                              return '<span data-is_quantity="true" class="display_currency pull-right mr-5 total_qty" data-currency_symbol=false data-orig-value="' . (float)$row->total_qty . '">'. round($row->total_qty,2) . '</span>';

                                // return '<span class="total_negative_inventory pull-right total_qty" data-orig-value="' . $row->total_qty . '">' . round($row->total_qty,2) . '</span>';
                        })
                        ->editColumn('sell_price', function ($row) {
                                return '<span class="display_currency total_negative_inventory pull-right mr-5 total_sell_price" data-currency_symbol="true" data-orig-value="' . $row->total_sell_price . '">' . $row->total_sell_price . '</span>';
                        })
                        ->addColumn('profit', function ($row) {
                                $profit = $row->total_sell_price - $row->total_dpp_price ;
                                return '<span class="display_currency total_negative_inventory pull-right mr-5 profit" data-currency_symbol="true" data-orig-value="' . $profit . '">' . $profit. '</span>';
                        })
                        ->editColumn('cost_price', function ($row) {
                            return '<span class="display_currency total_negative_inventory pull-right mr-5 total_dpp_price" data-currency_symbol="true" data-orig-value="' . $row->total_dpp_price . '">' . $row->total_dpp_price . '</span>';
                        })
                        ->editColumn('gp', function ($row) {
                            return '<span class=" gross-profit pull-right mr-5" >' . number_format($row->gp,2 ) ." %"  . '</span>';
                        })
                    ->removeColumn('id')
                    ->rawColumns(['address','transaction_date','category_name','quantity','profit' ,'sell_price','cost_price','gp'])
                    ->make(true);
                // foreach ($query as $data) {

                    // echo '<pre>';
                    // print_r($query);
                // }
                // die;
        }

        $account_rep = User::forDropdown($business_id, false);
        $sales_rep = User::forDropdown($business_id, false);
        $categories = Category::forDropdown($business_id, 'product');
                $brands = Brands::forDropdown($business_id);

        return view('report.report')->with(compact('account_rep', 'sales_rep', 'categories','brands'));

    }

    public function getAllTaxReport(Request $request){

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date." 00:00:00";
            $end_date = $end_date." 23:59:59";
            $query =  DB::table('transactions')
                ->leftJoin('contacts','contacts.id','=','transactions.contact_id')
                ->leftJoin('transaction_sell_lines','transaction_sell_lines.transaction_id','=','transactions.id')
                ->leftJoin('tax_rates','tax_rates.id','=','transaction_sell_lines.pos_line_tax_id')

                ->join('products','products.id','=', 'transaction_sell_lines.product_id')
                ->leftJoin('categories as c', 'products.category_id', '=', 'c.id')

                ->select(
                        'transactions.id as tra_id',
                        DB::raw('DATE_FORMAT(transactions.transaction_date, "%m/%d/%Y") as invoice_date'),
                        'transactions.invoice_no as invoice_no',
                        DB::raw('SUM(transaction_sell_lines.pos_line_tax_amount) AS tax_amount'),
                        DB::raw('sum(transaction_sell_lines.quantity * transaction_sell_lines.unit_price_inc_tax) AS total_before_tax'),

                         DB::raw("COUNT(IF(transaction_sell_lines.pos_line_tax_amount >= 0 , transaction_sell_lines.pos_line_tax_amount, 0)) as total_taxed_products"),

                        'contacts.name as name',
                        'contacts.tax_number as tax_id',
                        'contacts.address_line_1 as address',
                        'contacts.city as city',
                        'contacts.state as state',
                        'contacts.zip_code as zip_code',
                        'transactions.payment_status as payment_status',
                        'transactions.item_qty as item_qty',
                        'transactions.sub_total as price',
                        'transactions.tax_amount as tax_amount1',
                        'transactions.final_total',
                        'transactions.shipping_charges',
                        //  DB::raw("SUM(IF(c.name = 'E-LIQUID' or c.name = 'SALT E-LIQUID', round(transaction_sell_lines.pos_line_tax_amount,2), 0)) AS openTotal"),
                        //  DB::raw("SUM(IF(c.name = 'DISPOSABLE E-CIGARETTES' or c.name =  'E-CIGARETTE PODS', round(transaction_sell_lines.pos_line_tax_amount,2), 0)) AS closeTotal")
                         DB::raw("SUM(IF(c.id in(391,420), round(transaction_sell_lines.pos_line_tax_amount,2), 0)) AS openTotal"),
                         DB::raw("SUM(IF(c.id in(390,399,390,433,604,638,639,644,670,671,674,676,678,683,699,701,721,722,642,374), round(transaction_sell_lines.pos_line_tax_amount,2), 0)) AS closeTotal")


                    )
                    ->whereBetween(DB::raw('date(transactions.transaction_date)'), [ $start_date, $end_date])
                    ->orderBy('transactions.id','ASC');

                    if($request->customer_id != null){
                        $query = $query->where('transactions.contact_id', $request->customer_id);
                    }
                    if($request->state != 'All'){
                       $query->where('contacts.state', $request->state);
                    }
                    $query = $query->groupBy('transactions.id')->having('tax_amount','>',0);
                        // ->get();

                        // foreach($query as $data){
                        //     print_r($data);
                        // }
                        // die;



                return Datatables::of($query)
                        ->editColumn('tax_amount', function ($row) {
                            return '<span class="display_currency tax_amount pull-right mr-5" data-currency_symbol="true" data-orig-value="' . $row->tax_amount . '">' . round($row->tax_amount ,2) . '</span>';
                        })
                        ->editColumn('final_total', function ($row) {
                            return '<span class="display_currency final_total pull-right mr-5" data-currency_symbol="true" data-orig-value="' . $row->final_total . '">' . round($row->final_total ,2) . '</span>';
                        })
                        ->editColumn('shipping_charges', function ($row) {
                            return '<span class="display_currency shipping_charges pull-right mr-5" data-currency_symbol="true" data-orig-value="' . $row->shipping_charges . '">' . round($row->shipping_charges ,2) . '</span>';
                        })
                        ->editColumn('total_before_tax', function ($row) {
                            return '<span class="display_currency total_before_tax pull-right mr-5" data-currency_symbol="true" data-orig-value="' . $row->total_before_tax . '">' . round($row->total_before_tax ,2) . '</span>';
                        })
                        ->editColumn('invoice_no', function ($row) {
                            return '<a data-href="' . action('SellController@show', [$row->tra_id])
                                    . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                        })
                         ->editColumn('total_taxed_products', function ($row) {
                            return '<span>' . $row->total_taxed_products . '</span>';
                        })
                        ->addColumn('order_note', function ($row) {
                            return 'Yes';
                        })

                        ->addColumn('open_amount', function ($row) {
                            return '<span class=" openTotal pull-right mr-5" >' . number_format($row->openTotal,2 )  . '</span>';
                        })
                         ->addColumn('close_amount', function ($row) {
                            return '<span class=" closeTotal pull-right mr-5" >' . number_format($row->closeTotal,2 )  . '</span>';
                        })
                    ->removeColumn('id')
                    ->rawColumns(['shipping_charges','invoice_no','order_note','tax_amount','final_total','category_name','quantity','profit' ,'sell_price','cost_price','gp','total_before_tax','total_taxed_products','open_amount','close_amount'])
                    ->make(true);
        }

        $customers = Contact::customersDropdown($business_id, false);
        $states = TaxRate::select('state')->whereNotNull('state')->where('state','<>','')->groupBy('state')->get();

        return view('report.tax_report_new')->with(compact('customers', 'states'));


    }

    public function FollowUpgetStaleCustomer(Request $request){
        if (!auth()->user()->can('contacts_report.view') && !auth()->user()->can('smartcrm.view')) {
            abort(403, 'Unauthorized action.');
        }
        $view_own_customers_only = FollowUp::viewOwnCustomersOnly();
        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $contacts = Contact::where('contacts.business_id', $business_id)
                ->leftjoin('users as u', 'u.id', '=', 'contacts.sales_rep')
                ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->leftjoin('follow_ups AS fu', 'contacts.id', '=', 'fu.contact_id')
                ->select(
                    'contacts.id',
                    'contacts.supplier_business_name',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.address_line_1',
                    'contacts.address_line_2',
                    'contacts.city',
                    'contacts.state',
                    'contacts.zip_code',
                    'contacts.country',
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                    DB::raw("MAX(IF(t.type = 'sell' AND t.status = 'final', t.transaction_date, null)) as transaction_date"),
                    DB::raw("DATEDIFF(CURDATE(), MAX(IF(t.type = 'sell' AND t.status = 'final', t.transaction_date, null))) as days"),
                    DB::raw('MAX(fu.created_at) as followup_date')
                )
                ->active()
                ->where('contacts.type', 'customer')
            ->groupBy('contacts.id');

            if ($view_own_customers_only) {
                $contacts->where('u.id', auth()->user()->id);
            }

            if (!empty($request->input('sr_id'))) {
                $contacts->where('u.id', $request->input('sr_id'));
            }

            if (!empty($request->input('contact_id'))) {
                $contacts->where('contacts.id', $request->input('contact_id'));
            }

            return Datatables::of($contacts)
                ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    if (!empty($row->supplier_business_name)) {
                        $name .= ', ' . $row->supplier_business_name;
                    }
                    return '<span class="name" data-orig-value="' . $row->name . '" >' . $row->name . '</span>';
                })
                ->editColumn('mobile', function ($row) {
                    return '<span class="mobile" data-orig-value="' . $row->mobile . '" >' . $row->mobile . '</span>';
                })
                ->editColumn('total_sell_return', function ($row) {
                    return '<span class="display_currency total_invoice" data-orig-value="' . $row->total_invoice . '" data-currency_symbol = true>' . $row->total_invoice . '</span>';
                })
                ->editColumn('user_name', function ($row) {
                    return $row->user_name;
                })
                ->editColumn('transaction_date', function ($row) {
                    if($row->transaction_date){
                        $row->transaction_date = \Carbon\Carbon::parse($row->transaction_date)->format('m/d/Y H:i A');
                    }
                    return '<span class="transaction_date" data-orig-value="' . $row->transaction_date . '" >' . $row->transaction_date . '</span>';
                })

                ->editColumn('days', function ($row) {
                    return $row->days;
                })

                ->addColumn('due', function ($row) {
                    $qry = $this->contactUtil->getContactInfo(4, $row->id);
                    $balance_due=  $qry->opening_balance - $qry->opening_balance_paid + $qry->total_invoice - $qry->invoice_received - $qry->balance - $qry->total_sell_return + $qry->sell_return_paid;
                    return '<span class="display_currency contact_due" data-currency_symbol="true"  data-highlight=true  data-orig-value="' .($balance_due). '" >' .($balance_due). '</span>';
                })
                ->editColumn('followup_date', function ($row) {
                    if($row->followup_date){
                        return \Carbon\Carbon::parse($row->followup_date)->format('m/d/Y H:i A');
                    }
                })
                ->rawColumns(['name', 'due', 'address', 'mobile', 'total_sell_return', 'user_name', 'transaction_date', 'days'])
                ->make(true);
        }

        // $customers = Contact::customersDropdown($business_id);
        $customers = Contact::customersCompanyDropdown($business_id, false, true, $view_own_customers_only);

        // $users = User::forDropdown($business_id, false);
        $users = User::select('id','first_name','last_name', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))
            ->where('business_id',4)
            ->where('status','active')
            ->where('allow_login',1)
            ->where('sales_rep',1)
            ->orderBy('first_name')
            ->get()
        ->pluck('full_name', 'id');

        $status = FollowUp::keyValueMap(FollowUp::STATUS);
        $priorities = FollowUp::keyValueMap(FollowUp::PRIORITIES);
        $channel = FollowUp::keyValueMap(FollowUp::CHANNEL);

        // return view('smartcrm::index');
        return view('report.view_foolowup_stale_customer')
            ->with(compact('customers', 'users', 'status', 'priorities', 'channel'));
    }

    public function FollowUpgetStaleCustomer_old(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $contacts = Contact::where('contacts.business_id', $business_id)
                // ->join('users AS u', 'contacts.sales_rep', '=', 'u.id')
                ->leftjoin('users as u', 'u.id', '=', 'contacts.sales_rep')
                ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                // ->leftjoin('follow_ups As fu', 'contacts.id', '=', 'fu.contact_id')
                ->leftjoin('follow_ups AS fu', function ($join) {
                    $join->on('contacts.id', '=', 'fu.contact_id')
                        ->whereRaw('fu.created_at = (SELECT MAX(fu2.created_at) FROM follow_ups AS fu2 WHERE fu2.contact_id = contacts.id)');
                })
                ->active()
                // ->where(function($que){
                //     $que->where('contacts.city','like','%new%york%')
                //     ->orwhere('contacts.city','like','%NYC%');
                // })
                ->groupBy('contacts.id')
                ->select(
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                    DB::raw("DATEDIFF(CURDATE(), MAX(t.transaction_date)) as days"),
                    DB::raw("MAX(t.transaction_date) as transaction_date"),
                    'contacts.supplier_business_name',
                    'contacts.name',
                    'contacts.email',
                    'contacts.mobile',
                    'contacts.address_line_1',
                    'contacts.address_line_2',
                    'contacts.city',
                    'contacts.country',
                    'contacts.state',
                    'contacts.zip_code',
                    'contacts.id',

                    DB::raw('fu.created_at as followup_date')
                );

            $permitted_locations = auth()->user()->permitted_locations();

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $start_date = $start_date . " 00:00:00";
            $end_date = $end_date . " 23:59:59";

            if ($permitted_locations != 'all') {
                $contacts->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->input('sr_id'))) {
                $contacts->where('u.id', $request->input('sr_id'));
            } else {
                $contacts->whereNotNull('t.id');
                if (!empty($start_date) && !empty($end_date)) {
                    $start_date = $start_date . " 00:00:00";
                    $end_date = $end_date . " 23:59:59";

                    $contacts->whereBetween(DB::raw('t.transaction_date'), [$start_date, $end_date]);
                }
            }

            if (!empty($request->input('contact_id'))) {
                $contacts->where('contacts.id', $request->input('contact_id'));
            }



            // if (!empty($request->input('from_date'))) {
            //     $contacts->where('t.transaction_date', '<=', date('Y-m-d', strtotime($request->input('from_date'))) );
            // }

            return Datatables::of($contacts)
                ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')

                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    if (!empty($row->supplier_business_name)) {
                        $name .= ', ' . $row->supplier_business_name;
                    }
                    return '<span class="name" data-orig-value="' . $row->name . '" >' . $row->name . '</span>';
                })
                // ->editColumn('email', function ($row) {
                //     return '<span class="email" data-orig-value="' . $row->email . '" >' . $row->email . '</span>';
                // })
                ->editColumn('mobile', function ($row) {
                    return '<span class="mobile" data-orig-value="' . $row->mobile . '" >' . $row->mobile . '</span>';
                })
                ->editColumn('total_sell_return', function ($row) {
                    return '<span class="display_currency total_invoice" data-orig-value="' . $row->total_invoice . '" data-currency_symbol = true>' . $row->total_invoice . '</span>';
                })
                ->editColumn('user_name', function ($row) {
                    return $row->user_name;
                })
                ->editColumn('transaction_date', function ($row) {
                    return '<span class="transaction_date" data-orig-value="' . $row->transaction_date . '" >' . $row->transaction_date . '</span>';
                })

                ->editColumn('days', function ($row) {
                    return $row->days;
                })

                ->addColumn('due', function ($row) {
                    $due = ($row->total_invoice - $row->invoice_received - $row->total_sell_return + $row->sell_return_paid) - ($row->total_purchase - $row->total_purchase_return + $row->purchase_return_received - $row->purchase_paid);

                    if ($row->contact_type == 'supplier') {
                        $due -= $row->opening_balance - $row->opening_balance_paid;
                    } else {
                        $due += $row->opening_balance - $row->opening_balance_paid;
                    }

                    if ($due > 0) {
                        return '<span class="display_currency total_due text-success" data-orig-value="' . $due . '" data-currency_symbol=true >' . $due . '</span>';
                    }
                    if ($due < 0) {
                        return '<span class="display_currency total_due text-danger" data-orig-value="' . $due . '" data-currency_symbol=true >' . $due . '</span>';
                    }
                    if ($due == 0) {
                        return '<span class="display_currency total_due" data-orig-value="' . $due . '" data-currency_symbol=true>' . $due . '</span>';
                    }
                })
                ->editColumn('followup_date', function ($row) {
                    if($row->followup_date){
                        return \Carbon\Carbon::parse($row->followup_date)->format('Y-m-d H:i A');
                    }
                })
                ->rawColumns(['name', 'due', 'address', 'mobile', 'total_sell_return', 'user_name', 'transaction_date', 'days'])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id);
        $users = User::forDropdown($business_id, false);
        $status = FollowUp::keyValueMap(FollowUp::STATUS);
        $priorities = FollowUp::keyValueMap(FollowUp::PRIORITIES);
        $channel = FollowUp::keyValueMap(FollowUp::CHANNEL);
        return view('report.view_foolowup_stale_customer')
            ->with(compact('customers', 'users', 'status', 'priorities', 'channel'));
    }

      public function CustomerAnalytics(Request $request, $id = 0)
    {
        $business_id = request()->session()->get('user.business_id');

        // $customers = Contact::customersDropdown($business_id, false);
        $view_own_customers_only = !auth()->user()->can('smartcrm.customer_view_all') && auth()->user()->can('smartcrm.customer_view_own');
        $customers = Contact::customersCompanyDropdown($business_id, false, true, $view_own_customers_only);

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $date_filters['this_fy'] = $fy;
        $date_filters['this_month']['start'] = date('Y-m-01');
        $date_filters['this_month']['end'] = date('Y-m-t');
        $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));
        $date_filters['last_30_days']['start'] = date('Y-m-d', strtotime('-30 days'));
        $date_filters['last_30_days']['end'] = date('Y-m-d');



        if ($request->ajax()) {
            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);

            $business_id = request()->session()->get('user.business_id');
            $contact = $this->contactUtil->getContactInfo($business_id, $id);

            $contact_id = $contact->id;
            $advance_balance  =  $contact->balance;

            $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start, $end, $advance_balance);

            $startDate = '2021-01-01';
            $endDate = date("Y-m-d");

            $total_fixed_data = $this->transactionUtil->getLedgerDetails($contact_id, $startDate, $endDate, $advance_balance);

            $discount = DB::table('contacts as c')
                ->leftjoin('transactions as t', 't.contact_id', '=', 'c.id')
                ->select(
                    DB::raw('SUM(t.discount_amount) as discount_amount')
                )
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('c.id', $id)
                ->get();

            $total_invoices = $contact->total_invoice + $discount[0]->discount_amount;

            $business_id = $request->session()->get('user.business_id');


            $sell = DB::table('transactions as t')
                ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->select(
                    DB::raw('DATE(t.transaction_date) as trdate'),
                    // DB::raw('SUM(tr.unit_price * tr.quantity) as sales')
                    DB::raw('SUM(t.final_total) as total_purchase')
                )
                ->where('c.id', $id)
                // ->leftJoin('transaction_sell_lines as tr', 't.id', '=', 'tr.transaction_id')
                ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get();

            $sell_return =  DB::table('transactions as t')
                ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->where('t.type', 'sell_return')
                ->where('c.id', $id)
                ->select(
                    DB::raw("SUM(t.final_total) as total_purchase_return"),
                    DB::raw('DATE(t.transaction_date) as trdate')
                )
                ->whereDate('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->whereDate('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get();



            $dates = [];
            for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
                array_push($dates, $i->format('Y-m-d'));
            }

            $all_sell = [];
            $n = 0;
            $m = 0;
            $all_sell_return = [];
            foreach ($dates as $date) {

                if (in_array($date, array_column($sell->toArray(), 'trdate'))) {
                    array_push($all_sell, $sell[$n]->total_purchase ?? 0);
                    $n++;
                } else {
                    array_push($all_sell, 0);
                }

                if (in_array($date, array_column($sell_return->toArray(), 'trdate'))) {
                    array_push($all_sell_return, $sell_return[$m]->total_purchase_return ?? 0);
                    $m++;
                } else {
                    array_push($all_sell_return, 0);
                }
            }

            $mainChart = [
                $all_sell,
                $all_sell_return,
                $dates
            ];

            $avg_month_spend = DB::table('transactions as t')
                ->leftJoin('transaction_sell_lines as tl', 'tl.transaction_id', '=', 't.id')
                ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->select(
                    DB::raw('COUNT(DISTINCT DATE_FORMAT(tl.created_at, "%Y-%m")) AS months_active'),
                    DB::raw('SUM(tl.unit_price * tl.quantity) / COUNT(DISTINCT DATE_FORMAT(tl.created_at, "%Y-%m")) AS avg_monthly_purchase')
                )
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('c.id', $id) // Replace $id with the desired contact_id
                ->get();

            $avg_qua = DB::table('transaction_sell_lines AS ts')
                ->select(
                    't.contact_id',
                    'c.name',
                    DB::raw('YEAR(ts.created_at) AS year'),
                    DB::raw('QUARTER(ts.created_at) AS quarter'),
                    DB::raw('COUNT(DISTINCT CONCAT(YEAR(ts.created_at), "-", QUARTER(ts.created_at))) AS quarters_active'),
                    DB::raw('SUM(ts.unit_price * ts.quantity) / COUNT(DISTINCT CONCAT(YEAR(ts.created_at), "-", QUARTER(ts.created_at))) AS avg_quarterly_purchase')
                )
                ->leftjoin('transactions AS t', 't.id', '=', 'ts.transaction_id')
                ->leftjoin('contacts AS c', 'c.id', '=', 't.contact_id')
                ->where('t.type', '=', 'sell')
                ->where('t.status', '=', 'final')
                ->where('c.id', $id)
                ->get();

            $avg_yearly =  DB::table('transaction_sell_lines AS ts')
                ->select(
                    DB::raw('YEAR(ts.created_at) AS year'),
                    DB::raw('COUNT(DISTINCT YEAR(ts.created_at)) AS years_active'),
                    DB::raw('SUM(ts.unit_price * ts.quantity) / COUNT(DISTINCT YEAR(ts.created_at)) AS avg_yearly_purchase')
                )
                ->leftjoin('transactions AS t', 't.id', '=', 'ts.transaction_id')
                ->leftjoin('contacts AS c', 'c.id', '=', 't.contact_id')
                ->where('t.type', '=', 'sell')
                ->where('t.status', '=', 'final')
                ->where('c.id', $id)
                ->get();

            $total_paids =  DB::table('transactions as t')
            ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->select(
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                TP.transaction_id=t.id) as total_paid'),
                't.final_total'
            )
            ->where('c.id', $id)
            // ->whereDate('t.transaction_date', '>=', $start)
            // ->whereDate('t.transaction_date', '<=', $end)
            ->get();

            $totalSum = $total_paids->sum('total_paid');

            $duesum = 0; // Initialize duesum variable

            foreach ($total_paids as $total_paid) {
                // Calculate duesum for each row
                $duesum += $total_paid->final_total - $total_paid->total_paid;
            }

            // $totalduesum = DB::table('transactions as t')
            // ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
            // ->where('t.type', 'sell')
            // ->where('t.status', 'final')
            // ->where('c.id', $id)
            // ->selectRaw('SUM(IF(TP.is_return = 1, -1 * TP.amount, TP.amount)) AS total_paid')
            // ->selectRaw('SUM(t.final_total) - SUM(IF(TP.is_return = 1, -1 * TP.amount, TP.amount)) AS duesum')
            // ->leftJoin('transaction_payments AS TP', 'TP.transaction_id', '=', 't.id')
            // ->first();

            // $total_duesum = $totalduesum->duesum;

            $total_duesum_query_result = DB::table('transactions as t')
            ->leftJoin('contacts as c', 'c.id', '=', 't.contact_id')
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->select(
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id=t.id) as total_paid'),
                't.final_total'
            )
            ->where('c.id', $id)
            ->get();

            $total_duesum = 0; // Initialize total_duesum variable

            foreach ($total_duesum_query_result as $result) {
                // Calculate duesum for each row
                $total_duesum += $result->final_total - $result->total_paid;
            }

            // $due_percentage = $total_duesum / $total_invoices * 100;

            // $purchase_return_percentage = $total_fixed_data['total_return'] / $total_invoices * 100;
            // $due_percentage = ($total_invoices > 0) ? ($total_duesum / $total_invoices * 100) : 0;
            $due_percentage = ($total_fixed_data['total_invoice'] > 0) ? ($total_fixed_data['balance_due'] / $total_fixed_data['total_invoice'] * 100) : 0;
            $purchase_return_percentage = ($total_fixed_data['total_invoice'] > 0) ? ($total_fixed_data['total_return'] / $total_fixed_data['total_invoice'] * 100) : 0;

            $total_data = [];

            $total_data['months_active'] = $avg_month_spend[0]->months_active;
            $total_data['avg_monthly_purchase'] = $this->format_money($avg_month_spend[0]->avg_monthly_purchase);
            $total_data['quarters_active'] = $avg_qua[0]->quarters_active;
            $total_data['avg_qua'] = $this->format_money($avg_qua[0]->avg_quarterly_purchase);
            $total_data['avg_yearly_purchase'] = $this->format_money($avg_yearly[0]->avg_yearly_purchase);
            $total_data['total_paid'] = $this->format_money($totalSum);
            $total_data['total_due'] = $this->format_money($duesum);
            $total_data['total_duesum'] = $this->format_money($total_duesum);

            // Ranking
            $query = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->join(
                'business_locations AS bl',
                'transactions.location_id',
                '=',
                'bl.id'
            )
            ->where('transactions.business_id', $business_id)
            ->select(
                'contacts.id as contact_id',
                'contacts.name',
                DB::raw("SUM(IF(transactions.type = 'sell' AND transactions.status = 'final', final_total, 0)) - SUM(IF(transactions.type = 'sell_return' AND transactions.status = 'final', transactions.final_total, 0)) as total_amount")
            )
            ->groupBy('contacts.name')
            ->orderBy('total_amount', 'DESC')
            ->get();
            $rank = null;

            $query = $query->map(function ($item, $key) {
                $item->rank = $key + 1;
                return $item;
            });

            foreach ($query as $result) {
                if ($result->contact_id == $id) {
                    $rank = $result->rank;
                    break;
                }
            }

            // $total_data['total_duesum'] = $this->format_money($total_duesum);
            return response([
                $mainChart,
                $contact,
                $ledger_details,
                $total_data,
                $total_invoices,
                $total_fixed_data,
                $due_percentage,
                $purchase_return_percentage,
                $contact->created_at,
                $rank
            ]);
        }
        // return view('report.customer_analytics', compact('customers'));

        // return view('smartcrm::index');

        return view('report.customer_analytics', [
            'customers' => $customers,
            'customer_id' => $id,
            'date_filters' => $date_filters,
        ]);


    }
    public function CustomerSell(Request $request, $id = 0)
    {
        if (request()->ajax()) {
            $start = '';
            $end = '';
            $business_id = $request->session()->get('user.business_id');
            $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();
            $sells = $this->transactionUtil->getListSells($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
                if ($permitted_locations != 'all') {
                    $sells->whereIn('transactions.location_id', $permitted_locations);
                }
            if (!auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
                $sells->where('transactions.created_by', request()->session()->get('user.id'));
            }
            if (!empty($id)) {
                $sells->where('contacts.id', $id);
            }
            if ($is_woocommerce) {
                $sells->addSelect('transactions.woocommerce_order_id');
                if (request()->only_woocommerce_sells) {
                    $sells->whereNotNull('transactions.woocommerce_order_id');
                }
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            $sells->groupBy('transactions.id');

            if(empty(request()->input('product_category')) || request()->input('product_category') == 'all'){
                $filtered_sells = $sells;
            } else {
                $category_id = request()->input('product_category');
                $filtered_sells = [];
                $sells_arr = $sells->get();
                foreach ($sells_arr as  $value) {
                    $transaction_id = $value->id;
                    $transaction_sells = TransactionSellLine::with('product_single.category_single')->where('transaction_id',$transaction_id)->get();
                    foreach ($transaction_sells as $sell) {
                        if($sell->product_single->category_single && $sell->product_single->category_single->id == $category_id){
                            array_push($filtered_sells, $value);
                            break;
                        }
                    }
                }
            }
            $datatable = Datatables::of($filtered_sells)
            ->addColumn(
                'action',
                function ($row) {

                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                        data-toggle="dropdown" aria-expanded="false">' .
                                        __("messages.actions") .
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-left my-dropdown-1" role="menu">' ;


                    if (auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access") || auth()->user()->can("view_own_sell_only")) {
                            $html .= '<li><a href="#" data-href="' . action("SellController@show", [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __("messages.view") . '</a></li>';
                        }

                        if (auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access")) {
                            $html .= '<li><a target="_blank" href="' . action('SellPosController@open_invoice', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Open Invoice") . '</a></li>
			                       	<li><a target="_blank" href="' . action('SellPosController@packing_slip', [$row->id]) . '"><i class="fas fa-eye"></i> ' . __("Packing Slip") . '</a></li>

                                <li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id,"template_for" => "new_sale"]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("Email Invoice") . '</a></li>';
                        }

                        if (auth()->user()->can("access_shipping")) {
                            // $html .= '<li><a href="#" data-href="' . action('SellController@editShipping', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-truck" aria-hidden="true"></i>' . __("lang_v1.edit_shipping") . '</a></li>';
                        }

            //             if (!$only_shipments) {
            //                 $html .= '<li class="divider"></li>';

            //                 if ($row->payment_status != "paid" && (auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) && auth()->user()->can("sell.payments")) {
            //                     $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __("purchase.add_payment") . '</a></li>';
            //                 }

            //                 $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __("purchase.view_payments") . '</a></li>';

            //                 if (auth()->user()->can("sell.create")) {
            //                     $html .= '<li><a href="' . action('SellController@duplicateSellCreate', [$row->id]) . '"><i class="fas fa-copy"></i> ' . __("lang_v1.duplicate_sell") . '</a></li>

            //                     <li style="display:none;" ><a href="' . action('SellReturnController@add', [$row->id]) . '"><i class="fas fa-undo"></i> ' . __("lang_v1.sell_return") . '</a></li>

            //                     <li><a href="' . action('SellPosController@showInvoiceUrl', [$row->id]) . '" class="view_invoice_url"><i class="fas fa-eye"></i> ' . __("lang_v1.view_invoice_url") . '</a></li>

            //                     ';
            //                 }

            //                 $html .= '';
				        //     $html .= ' <li><a target="_blank" href="'.action('SellPosController@driverinvoice', [$row->id]).'" ><i class="fa fa-file-invoice"></i> ' . __("Driver Invoice") . '</a></li>';
				        //     if (auth()->user()->can("access_blank_invoice")){
            //                   $html .= '<li><a target="_blank" href="' . action('SellPosController@invoice_blank', [$row->id]) . '"><i class="fa fa-trash" aria-hidden="true" style="color:#d9534f"></i> ' . __("Delete All Invoice") . '</a></li>';
            //                 }
				        //   if (auth()->user()->can("access_print_invoice")){
				        //         $html .= '<li><a target="_blank" href="' . action('SellPosController@invoicegen', [$row->id]) . '"><i class="fas fa-trash" aria-hidden="true" style="color:#d9534f"></i> ' . __(" Delete Invoice") . '</a></li>';
				        //   }
				        //     $html .= ' <li><a target="_blank" href="' . action('SellPosController@allInvoice') . '"><i class="fas fa-eye"></i> ' . __("All Invoice") . '</a></li>';

            //             }
                        if (auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access") || auth()->user()->can("view_own_sell_only")) {
                            $html .= '<li><a href="#" data-href="' . action("SellController@show", [$row->id]) . '?downloadsell=1" class="btn-modal" data-container=".view_modal"><i class="fas fa-file-pdf" aria-hidden="true"></i> Download PDF</a></li>';
                        }
                    $html .= '</ul></div>';

                    return $html;
                }
            )
         ->removeColumn('id')
                 ->editColumn(
                    'final_total',
                    function($row){
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        $final_total =  $row->final_total + $discount;
                        return  '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="'. $final_total.'">'. $final_total.'</span>';
                    }
                )
                ->addColumn(
                    'customer_name',
                    function($row){
                        $note = '';
                        if(!empty($row->ask_for_check)){
                            $note = '(ASK FOR CHECK)';
                        }

                        return  $row->name .' '. $note;
                    }
                )
                ->editColumn(
                    'tax_amount',
                    '<span class="display_currency total-tax" data-currency_symbol="true" data-orig-value="{{$tax_amount}}">{{$tax_amount}}</span>'
                )
                ->addColumn(
                    'total_gp',
                     function($row){
                         $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        try{
                            $check_data =  DB::table('transaction_sell_lines')
                                    ->select(DB::raw("sum(purchase_price*quantity) as cost"))
                                    ->where('transaction_id',$row->id)->first();

                            $divisable = $row->total_before_tax - $discount - $check_data->cost;
                            $divider = $row->total_before_tax - $discount;
                            if($divider == 0){
                                $divider = 1;
                            }
                            $new_gp = round((($divisable) * 100)/($divider),2)."%";

                            return  '<span class="total_gross_profit" data-orig-value="'.$new_gp.'">'.$new_gp.'</span>';
                        }
                        catch(Exception $e){
                            if (!empty($discount) && $row->discount_type == 'percentage') {
                                $discount = $row->total_before_tax * ($discount / 100);
                            }
                            $total_sell = $row->total_sell - $discount;
                            $total_cost = $row->total_cost;
                            if($total_cost > 0 && $total_sell > 0){
                                $gross_profit = (1 - ($total_cost / $total_sell))*100;
                                $gross_profit = round($gross_profit,2);
                                $gp = $gross_profit.'%';
                            }else{
                                $gross_profit = 0;
                                $gp = '0%';
                            }

                            return  '<span class="total_gross_profit" data-orig-value="'. $gross_profit.'">'. $gp.'</span>';
                        }
                    }
                )
                ->editColumn(
                    'total_paid',
                    '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="{{$total_paid}}">{{$total_paid}}</span>'
                )
                ->editColumn(
                    'shipping_charges',
                    '<span class="display_currency shipping-charges" data-currency_symbol="true" data-orig-value="{{$shipping_charges}}">{{$shipping_charges}}</span>'
                )
                ->editColumn(
                    'total_before_tax',
                    '<span class="display_currency total_before_tax" data-currency_symbol="true" data-orig-value="{{$total_before_tax}}">{{$total_before_tax}}</span>'
                )

                ->editColumn(
                    'discount_amount',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        return '<span class="display_currency total-discount" data-currency_symbol="true" data-orig-value="' . $discount . '">' . $discount . '</span>';
                    }
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
                    }
                )
                ->editColumn(
                    'types_of_service_name',
                    '<span class="service-type-label" data-orig-value="{{$types_of_service_name}}" data-status-name="{{$types_of_service_name}}">{{$types_of_service_name}}</span>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining =  $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $total_remaining . '">' . $total_remaining . '</span>';


                    return $total_remaining_html;
                })
                ->addColumn('return_due', function ($row) {
                    $return_due_html = '';
                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action("TransactionPaymentController@show", [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="display_currency sell_return_due" data-currency_symbol="true" data-orig-value="' . $return_due . '">' . $return_due . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->editColumn('balance_due', function ($row) {
                    $contact = Contact::where('contacts.id', $row->contact_id_new)
                        ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                        ->leftjoin('users as sale','sale.id','=','contacts.sales_rep')
                        ->leftjoin('users as account','account.id','=','contacts.account_rep')
                        ->leftjoin('users as createdfor','createdfor.id','=','contacts.created_by')
                        ->with(['business'])
                        ->select(
                            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                            'contacts.*'
                        )->first();
                    $balance_due= $contact->total_invoice - $contact->invoice_received - $contact->balance;
                    $open_color = 'green';
                    if($balance_due > 0){
                        $open_color = 'red';
                    }
                    return  '<span class="display_currency " style="white-space:nowrap;font-weight:bolder;color:'.$open_color.';" data-currency_symbol="true" data-orig-value="'. $balance_due.'">'.'$ '. $balance_due.'</span>';

                })
                ->addColumn('picked_by', function ($row) {
                    return $row->picked_by;
                })
                ->addColumn('packed_by', function ($row) {
                    return $row->packed_by;
                })
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    $edited_log = Delinvoicelog::where('transaction_id',$row->id)
                                ->where('description', 'edited')->first();
                    if(!empty($edited_log))
                    {
                        $invoice_no .= ' <i class="fa fa-edit text-primary no-print">logs</i>';
                    }
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') .'"><i class="fas fa-undo"></i></small>';
                    }
                    if (!empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') .'"><i class="fas fa-recycle"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = !empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action('SellController@editShipping', [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color .'">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

                    return $status;
                })
                ->editColumn('order_status', function ($row) {

                    if($row->order_picking_status == '2' && $row->order_packing_status == '0'){
                        $order_status = 'Picking Completed';
                        $bg_color = 'bg-green';
                    }elseif($row->order_packing_status == '2' && $row->order_picking_status == '2'){
                        $order_status = 'Packing Completed';
                        $bg_color = 'bg-green';
                    }elseif($row->order_packing_status == '1' && $row->order_picking_status == '2'){
                        $order_status = 'Packing Started';
                        $bg_color = 'bg-orange';
                    }elseif($row->order_picking_status == '0'){
                        $order_status = 'Received';
                        $bg_color = 'bg-blue';
                    }elseif($row->order_picking_status == '1'){
                        $order_status = 'Picking Started';
                        $bg_color = 'bg-orange';
                    }elseif($row->order_picking_status == '3'){
                        $order_status = 'Cancel';
                        $bg_color = 'bg-red';
                    }else{
                        $order_status = 'Received';
                        $bg_color = 'bg-blue';
                    }

                    $status = '<a href="#"><span class="label ' . $bg_color .'">' . $order_status . '</span></a>';

                    return $status;
                })
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]];
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

                    return $html;
                })

                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view") || auth()->user()->can("view_own_sell_only")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }]);

            $rawColumns = ['customer_name','final_total', 'shipping_charges', 'action', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status','order_status', 'types_of_service_name', 'payment_methods','balance_due', 'return_due', 'picked_by','total_gp'];
        return $datatable->rawColumns($rawColumns)
                  ->make(true);
        }
    }

    public function TopFavoriteProduct(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            // $start = '';
            // $end = '';
            // if (!empty(request()->start_date) && !empty(request()->end_date)) {
            //     $start = request()->start_date;
            //     $end =  request()->end_date;
            // }

            // $dtcurr = new DateTime($end);
            // $dtcurr->modify('+1 day');
            // $dtbefore = new DateTime($start);
            $dtcurr = new DateTime(); // Use the current date as the end date
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime('-30 days'); // Calculate the date 30 days ago

            $favoriteProduct = DB::table('transactions as t')
                ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->leftjoin('transaction_sell_lines as tl', 'tl.transaction_id', '=', 't.id')
                ->leftjoin('products as p', 'p.id', '=', 'tl.product_id')
                ->select(
                    'p.id as product_id',
                    'c.id as contact_id',
                    'p.name as product_name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) AS purchase'),
                    DB::raw('MAX(CASE WHEN c.id = "'. $id .'" THEN t.transaction_date ELSE NULL END) AS latest_purchase_date')
                )
                ->where('c.id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                // ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                // ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('p.name')
                ->orderBy('purchase', 'DESC')
                ->take(10)
                ->get();

             $contact = Contact::findOrFail($id);
            // $contact = DB::table('contacts')->where('id', $id)->first();

            if(isset($contact)){
                $city_wise = DB::table('transactions as t')
                ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->leftjoin('transaction_sell_lines as tl', 'tl.transaction_id', '=', 't.id')
                ->leftjoin('products as p', 'p.id', '=', 'tl.product_id')
                ->select(
                    'p.id as product_id',
                    'c.id as contact_id',
                    'p.name as product_name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) AS purchase'),
                    // DB::raw('MAX(t.transaction_date) AS latest_purchase_date_city')
                    DB::raw('MAX(CASE WHEN c.id = "'. $id .'" THEN t.transaction_date ELSE NULL END) AS latest_purchase_date_city'),

                )
                ->where('c.city', $contact->city)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('p.name')
                ->orderBy('purchase', 'DESC')
                ->take(10)
                ->get();
            }
            return response()->json(['products' => $favoriteProduct, 'city_products' => $city_wise ]);
        }
    }

    public function FavoriteCategory(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            // $start = '';
            // $end = '';
            // if (!empty(request()->start_date) && !empty(request()->end_date)) {
            //     $start = request()->start_date;
            //     $end =  request()->end_date;
            // }

            // $dtcurr = new DateTime($end);
            // $dtcurr->modify('+1 day');
            // $dtbefore = new DateTime($start);

            $dtcurr = new DateTime(); // Use the current date as the end date
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime('-30 days'); // Calculate the date 30 days ago

            $category = DB::table('transactions as t')
                ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->leftjoin('transaction_sell_lines as tl', 'tl.transaction_id', '=', 't.id')
                ->leftjoin('products as p', 'p.id', '=', 'tl.product_id')
                ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                ->select(
                    'categories.id as category_id',
                    'c.id as contact_id',
                    'categories.name as category_name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) as high_purchase'),
                    DB::raw('MAX(CASE WHEN c.id = "'. $id .'" THEN t.transaction_date ELSE NULL END) AS latest_purchase_date')
                )
                ->where('c.id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                // ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                // ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('categories.name')
                ->orderBy('high_purchase', 'DESC')
                ->take(10)
                ->get();

             $contact = Contact::findOrFail($id);

            if(isset($contact)){
                $city_category = DB::table('transactions as t')
                ->leftjoin('contacts as c', 'c.id', '=', 't.contact_id')
                ->leftjoin('transaction_sell_lines as tl', 'tl.transaction_id', '=', 't.id')
                ->leftjoin('products as p', 'p.id', '=', 'tl.product_id')
                ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
                ->select(
                    'categories.id as category_id',
                    'c.id as contact_id',
                    'categories.name as category_name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) as high_purchase'),
                    DB::raw('MAX(CASE WHEN c.id = "'. $id .'" THEN t.transaction_date ELSE NULL END) AS latest_purchase_date')
                )
                ->where('c.city', $contact->city)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('categories.name')
                ->orderBy('high_purchase', 'DESC')
                ->take(10)
                ->get();
            }
            return response()->json(['category' => $category, 'city_category' => $city_category]);
        }
    }


    public function CustomerData(Request $request)
    {
        if ($request->ajax()) {
            $start = '2023-05-01';
            $end = '2023-05-31';

            $avg_month_spend = DB::table('contacts as c')
            ->select(
                'c.name as customer_name',
                DB::raw('SUM(tl.unit_price * tl.quantity) / COUNT(DISTINCT DATE_FORMAT(tl.created_at, "%Y-%m")) AS avg_monthly_spend')
            )
            ->leftJoin('transactions as t', 'c.id', '=', 't.contact_id')
            ->leftJoin('transaction_sell_lines as tl', 't.id', '=', 'tl.transaction_id')
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->groupBy('c.id')
            ->get();

        $month_purchase = DB::table('contacts as c')
            ->select(
                // 't.transaction_date',
                'c.name as customer_name',
                DB::raw('SUM(t.final_total) AS monthly_purchase')
            )
            ->leftJoin('transactions as t', 'c.id', '=', 't.contact_id')
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>=', $start)
            ->whereDate('t.transaction_date', '<=', $end)
            ->groupBy('c.id')
            ->get();

        // Combine the results
        $results = [];
        foreach ($avg_month_spend as $avg) {
            $customerName = $avg->customer_name;
            $avgSpend = $avg->avg_monthly_spend;
            // Find the corresponding monthly purchase record
            $monthlyPurchase = $month_purchase->firstWhere('customer_name', $customerName);

            // Check if monthlyPurchase exists and has a monthly_purchase property
            if (!$monthlyPurchase || $monthlyPurchase->monthly_purchase < $avgSpend) {
                $results[] = [
                    'customer_name' => $customerName,
                    'avg_monthly_spend' => $avgSpend,
                    'monthly_purchase' => $monthlyPurchase ? $monthlyPurchase->monthly_purchase : 0,
                ];
            }
        }

        return Datatables::of($results)
        ->make(true);
        }
    }
    function format_money($value)
    {
        $str_out = "";
        if ($value < 0) {
            $str_out = "" . number_format(($value * -1), 1, '.', '');
        } else {
            $str_out = "" . number_format($value, 1, '.', ',');
        }
        return "$ " . $str_out;
    }

    function format_number($value)
    {
        return number_format($value, 0, '', '');
    }

     public function ProductAnalytics(Request $request, $id = 0)
    {

        if ($request->ajax()) {

            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);

            $mainChart_sales = DB::table('transactions as t')
                ->select(DB::raw('DATE(t.transaction_date) as trdate'), DB::raw('SUM(tr.unit_price * tr.quantity) as sales'))
                ->leftJoin('transaction_sell_lines as tr', 't.id', '=', 'tr.transaction_id')
                ->where('tr.product_id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get()->toArray();

            $mainChart_purchase = DB::table('transactions as t')
                ->select(DB::raw('DATE(t.transaction_date) as trdate'), DB::raw('SUM(pl.purchase_price * pl.quantity) as purchases'))
                ->leftJoin('purchase_lines as pl', 't.id', '=', 'pl.transaction_id')
                ->where('pl.product_id', $id)
                ->where('t.type', 'purchase')
                ->where('t.status', 'received')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get()->toArray();

            $n = 0;
            $m = 0;
            $sales = [];
            $purchases = [];
            $dates = [];

            for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
                array_push($dates, $i->format('Y-m-d'));
            }

            foreach ($dates as $date) {

                if (in_array($date, array_column($mainChart_sales, 'trdate'))) {
                    array_push($sales, $mainChart_sales[$n]->sales ?? 0);
                    $n++;
                } else {
                    array_push($sales, 0);
                }

                if (in_array($date, array_column($mainChart_purchase, 'trdate'))) {
                    array_push($purchases, $mainChart_purchase[$m]->purchases ?? 0);
                    $m++;
                } else {
                    array_push($purchases, 0);
                }
            }

            $mainChartResult = [
                $sales,
                $purchases,
                $dates
            ];



            $total_data1 = DB::table('transactions as t')
                ->select(
                    DB::raw('SUM(tr.quantity) as sales_qty'),
                    DB::raw('SUM(tr.unit_price * tr.quantity) as sales_amt')
                )
                ->leftJoin('transaction_sell_lines as tr', 't.id', '=', 'tr.transaction_id')
                ->where('tr.product_id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->get();

            $total_data2 = DB::table('transactions as t')
                ->select(
                    DB::raw('SUM(pr.quantity) as purchase_qty'),
                    DB::raw('SUM(pr.purchase_price * pr.quantity) as purchase_amt')
                )
                ->leftJoin('purchase_lines as pr', 't.id', '=', 'pr.transaction_id')
                ->where('pr.product_id', $id)
                ->where('t.type', 'purchase')
                ->where('t.status', 'received')
                ->where('pr.quantity_returned', 0)
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->get();

            $purchase_price = Product::join('variations as v', 'v.product_id', '=', 'products.id')
                ->where('products.id', $id)
                ->select('v.default_purchase_price')->orderBy('products.created_at', 'desc')->first();

            $avg_sell = DB::table('transactions as t')
                ->select(DB::raw('AVG(unit_price) as avg_price'))
                ->leftJoin('transaction_sell_lines as tr', 't.id', '=', 'tr.transaction_id')
                ->where('tr.product_id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->get();

            $avg_margin_per = DB::table('transactions as t')
                ->where('tl.product_id', $id)
                ->select(
                    DB::raw('(SUM(tl.unit_price - tl.purchase_price) * 100 / SUM(tl.purchase_price))  as avg_price_difference_percentage')
                )
                ->leftJoin('transaction_sell_lines as tl', 't.id', '=', 'tl.transaction_id')
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->get();

            $product_deatils = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->leftjoin('variation_location_details as vld', 'variations.id', '=', 'vld.variation_id')
                ->leftjoin('categories', 'products.category_id', '=', 'categories.id')
                ->where('products.id', $id)
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.item_code',
                    'vld.qty_available',
                    'products.main_image as product_image',
                    'categories.name as category_name',
                    'variations.sell_price_inc_tax',
                )
                ->first();

            $total_data = [];
            $total_data['sales_qty'] = $this->format_number($total_data1[0]->sales_qty);
            $total_data['sales_amt'] = $this->format_money($total_data1[0]->sales_amt);
            $total_data['purchase_qty'] = $this->format_number($total_data2[0]->purchase_qty);
            $total_data['purchase_amt'] = $this->format_money($total_data2[0]->purchase_amt);
            $total_data['purchase_price'] = $this->format_money($purchase_price->default_purchase_price);
            $total_data['avg_price'] = $this->format_money($avg_sell[0]->avg_price);
            $total_data['avg_margin_per'] = $avg_margin_per[0]->avg_price_difference_percentage;

            $top_customer = Transaction::join('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->Select(
                    'contacts.id as customer_id',
                    'contacts.name as name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) as high_purchase')
                    // DB::raw('SUM(final_total) as high_purchase')
                )
                ->leftJoin('transaction_sell_lines as tl', 'transactions.id', '=', 'tl.transaction_id')
                ->where('tl.product_id', $id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.transaction_date', '>=', $start)
                ->where('transactions.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->groupBy('contacts.id')->take(10)
                ->orderBy('high_purchase', 'DESC')
                ->get();


            return response([
                $mainChartResult,
                $total_data,
                $top_customer,
                $product_deatils
            ]);
        }

        return view('report.product_analytics', [
            'product_id' => $id, // Pass the product_id to the view
        ]);
    }

     public function GetMarginChart(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);

            $ChartResult = DB::table('transactions as t')
                ->where('tl.product_id', $id)
                ->select(
                    DB::raw('DATE(t.transaction_date) as trdate'),
                    DB::raw('SUM(tl.unit_price - tl.purchase_price) * 100 / SUM(tl.purchase_price) as price_difference_percentage'),
                    // DB::raw('(SUM(tl.unit_price_inc_tax - tl.purchase_price) * 100 / tl.purchase_price)  / COUNT(tl.transaction_id) as avg_price_difference_percentage')
                )
                ->leftJoin('transaction_sell_lines as tl', 't.id', '=', 'tl.transaction_id')
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get()
                ->toArray();

            $k = 0;
            // $l = 0;
            $price_diff = [];
            // $avg_price_diff = [];
            $dates = [];
            for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
                array_push($dates, $i->format('Y-m-d'));
            }

            foreach ($dates as $date) {
                if (in_array($date, array_column($ChartResult, 'trdate'))) {
                    array_push($price_diff, $ChartResult[$k]->price_difference_percentage ?? 0);
                    $k++;
                } else {
                    array_push($price_diff, 0);
                }

                // if(in_array($date, array_column($ChartResult, 'trdate'))){
                //     array_push($avg_price_diff, $ChartResult[$l]->avg_price_difference_percentage ?? 0);
                //     $l++;
                // }else {
                //     array_push($avg_price_diff, 0);
                // }
            }
            $ChartResult1 = [
                $price_diff,
                // $avg_price_diff,
                $dates
            ];

            return $ChartResult1;
        }
    }

    public function StateChart(Request $request, $id = 0)
    {
        $start = '';
        $end = '';
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
        }

        $dtcurr = new DateTime($end);
        $dtcurr->modify('+1 day');
        $dtbefore = new DateTime($start);


        $data = DB::table('transactions as t')
            ->selectRaw('contacts.state as state, SUM(tl.quantity) as product_count')
            ->leftJoin('contacts', 'contacts.id', '=', 't.contact_id')
            ->leftJoin('transaction_sell_lines as tl', 't.id', '=', 'tl.transaction_id')
            ->where('tl.product_id', $id)
            ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->groupBy('contacts.state')
            ->orderBy('state')
            ->get();

        $chartData = [];
        foreach ($data as $item) {
            $chartData[] = [
                'x' => $item->state,
                'y' => $item->product_count,
            ];
        }

        $tier_data  = DB::table('transactions as t')
            // ->selectRaw('t.selling_price_group_id as tier, COUNT(tl.product_id) as product_count')
            ->selectRaw('t.selling_price_group_id as tier, SUM(tl.quantity) as product_count')
            ->leftJoin('transaction_sell_lines as tl', 't.id', '=', 'tl.transaction_id')
            ->leftJoin('variations as v', 'v.id', '=', 'tl.variation_id')
            ->leftjoin('variation_group_prices as vgp', 'vgp.variation_id', '=', 'v.id')
            ->whereIn('t.selling_price_group_id', ['68', '69', '70'])
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('tl.product_id', $id)
            ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('tier')
            ->get();

        $tierchartData = [];
        foreach ($tier_data as $item) {
            $tierName = $this->getTierName($item->tier); // Replace this with your logic to get tier names
            $tierchartData[] = [
                'a' => $tierName,
                'b' => $item->product_count,
            ];
        }

        return $chart = [
            $chartData,
            $tierchartData
        ];
    }

    private function getTierName($priceGroupId)
    {
        if ($priceGroupId == 68) {
            return 'Tier 1';
        } elseif ($priceGroupId == 69) {
            return 'Tier 2';
        } elseif ($priceGroupId == 70) {
            return 'Tier 3';
        } else {
            return 'Unknown Tier';
        }
    }

     public function RankTable(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->get('start_date');
            $end = $request->get('end_date');

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);

            $query = DB::table('transactions as t')
                ->select(
                    'tl.product_id as product_id',
                    'p.name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) as high_purchase')
                    // DB::raw('SUM(final_total) as high_purchase')
                )
                ->leftJoin('transaction_sell_lines as tl', 't.id', '=', 'tl.transaction_id')
                ->leftjoin('products as p', 'p.id', '=', 'tl.product_id')
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('p.name')
                ->orderBy('high_purchase', 'DESC')
                ->get();

            // Assign ranks
            $rank = 1;
            $previousPurchase = null;

            foreach ($query as $row) {
                if ($previousPurchase !== null && $row->high_purchase < $previousPurchase) {
                    $rank++;
                }

                $row->rank = $rank;
                $previousPurchase = $row->high_purchase;
            }

            return response()->json(['products' => $query]);
        }
    }

    public function CategoryAnalytics(Request $request, $id = 0)
    {
        $business_id = request()->session()->get('user.business_id');

        $categoriesdata = Category::forDropdown($business_id, 'product');

        if ($request->ajax()) {
            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);


            $product_list = Product::where('category_id', $id)->get();

            $category = DB::table('categories as c')
                ->select(
                    'c.name as category_name',
                    DB::raw('count(products.id) as product_qty'),
                    // DB::raw('sum(variation_location_details.qty_available) as qty_available')
                )
                ->leftJoin('products', 'products.category_id', '=', 'c.id')
                // ->leftJoin('variation_location_details', 'variation_location_details.product_id', '=', 'products.id')
                ->where('c.id', $id)
                ->where('products.not_for_selling', '0')
                ->get();



            $mainChart_sales = DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tr', 'tr.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tr.transaction_id')
                ->select(DB::raw('DATE(t.transaction_date) as trdate'), DB::raw('SUM(tr.unit_price * tr.quantity) as sales'))
                ->where('c.id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get()->toArray();

            $mainChart_purchase = DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tsl', 'tsl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tsl.transaction_id')
                ->leftJoin('purchase_lines as pl', 't.id', '=', 'pl.transaction_id')
                ->select(DB::raw('DATE(t.transaction_date) as trdate'), DB::raw('SUM(pl.purchase_price * pl.quantity) as purchases'))
                ->where('c.id', $id)
                ->where('t.type', 'purchase')
                ->where('t.status', 'received')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get()->toArray();

            $n = 0;
            $m = 0;
            $sales = [];
            $purchases = [];
            $dates = [];

            for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
                array_push($dates, $i->format('Y-m-d'));
            }

            foreach ($dates as $date) {

                if (in_array($date, array_column($mainChart_sales, 'trdate'))) {
                    array_push($sales, $mainChart_sales[$n]->sales ?? 0);
                    $n++;
                } else {
                    array_push($sales, 0);
                }

                if (in_array($date, array_column($mainChart_purchase, 'trdate'))) {
                    array_push($purchases, $mainChart_purchase[$m]->purchases ?? 0);
                    $m++;
                } else {
                    array_push($purchases, 0);
                }
            }

            $mainChartResult = [
                $sales,
                $purchases,
                $dates
            ];


            $total_amt = DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tsl', 'tsl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tsl.transaction_id')
                ->select(
                    DB::raw('SUM(tsl.quantity) as sales_qty'),
                    DB::raw('SUM(tsl.quantity * tsl.unit_price) as sales_amt')
                )
                ->where('c.id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->get();

            $total_data2 = DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tsl', 'tsl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tsl.transaction_id')
                ->select(
                    DB::raw('SUM(pr.quantity) as purchase_qty'),
                    DB::raw('SUM(pr.purchase_price * pr.quantity) as purchase_amt')
                )
                ->leftJoin('purchase_lines as pr', 't.id', '=', 'pr.transaction_id')
                ->where('c.id', $id)
                ->where('t.type', 'purchase')
                ->where('t.status', 'received')
                ->where('pr.quantity_returned', 0)
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->get();

            $top_customer =  DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tl', 'tl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tl.transaction_id')
                ->leftjoin('contacts', 't.contact_id', '=', 'contacts.id')
                ->Select(
                    'contacts.id as customer_id',
                    'contacts.name as name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) as high_purchase')
                )
                ->where('c.id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->groupBy('contacts.id')->take(10)
                ->orderBy('high_purchase', 'DESC')
                ->get();


            $avg_margin_per =  DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tl', 'tl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tl.transaction_id')
                ->select(
                    DB::raw('(SUM(tl.unit_price_inc_tax - tl.purchase_price) * 100 / SUM(tl.purchase_price))  as avg_price_difference_percentage')
                )
                ->where('c.id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $start)
                ->where('t.transaction_date', '<=', date('Y-m-d', strtotime($end . ' +1 day')))
                ->get();

            $top_category =

                $total_data = [];
            $total_data['category_name'] = $category[0]->category_name;
            $total_data['product_qty'] = $this->format_number($category[0]->product_qty);
            $total_data['sales_qty'] = $this->format_number($total_amt[0]->sales_qty);
            $total_data['sales_amt'] = $this->format_money($total_amt[0]->sales_amt);
            $total_data['purchase_qty'] = $this->format_number($total_data2[0]->purchase_qty);
            $total_data['purchase_amt'] = $this->format_money($total_data2[0]->purchase_amt);
            $total_data['avg_margin_per'] = $avg_margin_per[0]->avg_price_difference_percentage;
            // $total_data['qty_available'] = $category[0]->qty_available;


            return response([
                $product_list,
                $mainChartResult,
                $total_data,
                $top_customer
            ]);
        }

        // return view('report.category_analytics', compact('categoriesdata'));
        return view('report.category_analytics', [
            'categoriesdata' => $categoriesdata,
            'category_id' => $id, // Pass the category_id to the view
        ]);
    }

    public function CatMarginChart(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            $start = '';
            $end = '';
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
            }

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);

            $ChartResult = DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tl', 'tl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tl.transaction_id')
                ->where('c.id', $id)
                ->select(
                    DB::raw('DATE(t.transaction_date) as trdate'),
                    DB::raw('SUM(tl.unit_price_inc_tax - tl.purchase_price) * 100 / SUM(tl.purchase_price) as price_difference_percentage')
                )
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('trdate')
                ->orderBy('trdate')
                ->get()
                ->toArray();

            $k = 0;
            $price_diff = [];
            $dates = [];
            for ($i = $dtbefore; $i <= $dtcurr; $i->modify('+1 day')) {
                array_push($dates, $i->format('Y-m-d'));
            }

            foreach ($dates as $date) {
                if (in_array($date, array_column($ChartResult, 'trdate'))) {
                    array_push($price_diff, $ChartResult[$k]->price_difference_percentage ?? 0);
                    $k++;
                } else {
                    array_push($price_diff, 0);
                }
            }
            $ChartResult1 = [
                $price_diff,
                $dates
            ];

            return $ChartResult1;
        }
    }

    public function State_Tier_Chart(Request $request, $id = 0)
    {
        $start = '';
        $end = '';
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
        }

        $dtcurr = new DateTime($end);
        $dtcurr->modify('+1 day');
        $dtbefore = new DateTime($start);

        $data  = DB::table('categories as c')
            ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
            ->leftJoin('transaction_sell_lines as tl', 'tl.product_id', '=', 'p.id')
            ->leftJoin('transactions as t', 't.id', '=', 'tl.transaction_id')
            ->leftJoin('contacts', 'contacts.id', '=', 't.contact_id')
            ->selectRaw('contacts.state as state, SUM(tl.quantity) as product_count')
            ->where('c.id', $id)
            ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->groupBy('contacts.state')
            ->orderBy('state')
            ->get();

        $chartData = [];
        foreach ($data as $item) {
            $chartData[] = [
                'x' => $item->state,
                'y' => $item->product_count,
            ];
        }

        $tier_data  = DB::table('categories as c')
            ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
            ->leftJoin('transaction_sell_lines as tl', 'tl.product_id', '=', 'p.id')
            ->leftJoin('transactions as t', 't.id', '=', 'tl.transaction_id')
            // ->selectRaw('t.selling_price_group_id as tier, COUNT(tl.product_id) as product_count')
            ->selectRaw('t.selling_price_group_id as tier, SUM(tl.quantity) as product_count')
            ->whereIn('t.selling_price_group_id', ['68', '69', '70'])
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('c.id', $id)
            ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
            ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
            ->groupBy('tier')
            ->get();

        $tierchartData = [];
        foreach ($tier_data as $item) {
            $tierName = $this->getTierName($item->tier); // Replace this with your logic to get tier names
            $tierchartData[] = [
                'a' => $tierName,
                'b' => $item->product_count,
            ];
        }

        return $chart = [
            $chartData,
            $tierchartData
        ];
    }

    public function CatWiseRankTable(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            $start = $request->get('start_date');
            $end = $request->get('end_date');

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);

            $query = DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tl', 'tl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tl.transaction_id')
                ->select(
                    'p.id as product_id',
                    'c.id as cat_id',
                    'p.name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) as high_purchase')
                )
                ->where('c.id', $id)
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('p.name')
                ->orderBy('high_purchase', 'DESC')
                // ->limit(10)
                ->take(10)
                ->get();

            // Assign ranks
            $rank = 1;
            $previousPurchase = null;

            foreach ($query as $row) {
                if ($previousPurchase !== null && $row->high_purchase < $previousPurchase) {
                    $rank++;
                }

                $row->rank = $rank;
                $previousPurchase = $row->high_purchase;
            }

            return response()->json(['products' => $query]);
        }
    }

    public function CategoryRankTable(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->get('start_date');
            $end = $request->get('end_date');

            $dtcurr = new DateTime($end);
            $dtcurr->modify('+1 day');
            $dtbefore = new DateTime($start);

            $query = DB::table('categories as c')
                ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
                ->leftJoin('transaction_sell_lines as tl', 'tl.product_id', '=', 'p.id')
                ->leftJoin('transactions as t', 't.id', '=', 'tl.transaction_id')
                ->select(
                    'c.id as cat_id',
                    'c.name',
                    DB::raw('SUM(tl.unit_price * tl.quantity) as high_purchase')
                )
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('t.transaction_date', '>=', $dtbefore->format('Y-m-d'))
                ->where('t.transaction_date', '<=', $dtcurr->format('Y-m-d'))
                ->groupBy('c.name')
                ->orderBy('high_purchase', 'DESC')
                ->get();

            // Assign ranks
            $rank = 1;
            $previousPurchase = null;

            foreach ($query as $row) {
                if ($previousPurchase !== null && $row->high_purchase < $previousPurchase) {
                    $rank++;
                }

                $row->rank = $rank;
                $previousPurchase = $row->high_purchase;
            }

            return response()->json(['products' => $query]);
        }
    }

     public function ShowLat_LongPage()
    {
        $address = DB::table('contacts')->select('id', DB::Raw("CONCAT(address_line_1,',',city,',',state,',',country,',',zip_code) AS name"))

            ->where('assign', '0')
            ->orderBy('id', 'asc')
            ->get();


        return view('report.lat_long', compact('address'));
    }
    public function Lat_LongStore(Request $request)
    {
        try {

            $ids = $request->id;
            $contacts = Contact::query()->where('assign', 0)->get();

            // foreach ($contacts as  $contact) {


                $address = DB::table('contacts')->select('id',
                DB::Raw("CONCAT(address_line_1,',',city,',',state,',',country,',',zip_code) AS name")
                )
                ->whereNotIn('address_line_1',  ['TEST LINE 1','TEST LINE 1 33', 'TEST SYNC 1'])
                    // ->where('id', $contact->id)
                    ->where('id',$ids)
                    ->first();

                if (isset($address->name) && !empty($address->name )) {

                    $lat_long = json_encode($address->name);
                    $ids = json_encode($address->id);
                    // dd($lat_long,$ids);
                    // die;
                    $guzzle = new \GuzzleHttp\Client();

                    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($lat_long) . "+&sensor=false+CA&key=AIzaSyC8Jc4HBUsp9w_I9-rUTBS3t7v0atcBzWc";
                    $geo = $guzzle->request('post', $url);
                    $geo_result = json_decode($geo->getBody());

                    // dd($geo_result);
                    // die;
                    $latitude = $geo_result->results[0]->geometry->location->lat;

                    $longitude = $geo_result->results[0]->geometry->location->lng;

                    $data = array('lat' => $latitude, "lgn" => $longitude, 'assign' => 1);

                    DB::table('contacts')->where('id', $ids)->update($data);
                }
            // }

            $output = [
                'success' => 1,
                'msg' => __("Lat Long Added")
            ];

            return back()->with('status', $output);
        } catch (\Throwable $th) {
            throw $th;
            // return redirect('/sells')->with('error','er');
        }
    }

     public function inactiveItemsReport(Request $request)
     {

        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            // $query = ProductActivitiesLog::join('products', 'products.id', '=', 'product_activities.product_id')
            //     // join('products','product_activities.product_id','products.id')
            // // Product::where('products.business_id', $business_id)
            //         ->leftjoin('units', 'products.unit_id', '=', 'units.id')
            //         ->join('variations as v', 'products.id', '=', 'v.product_id')
            //         ->leftjoin('categories', 'products.category_id', '=', 'categories.id')
            //         ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')

            //         ;


            // if (!empty($request->input('category_id'))) {
            //     $query->where('products.category_id', $request->input('category_id'));
            // }
            // if (!empty($request->input('sub_category_id'))) {
            //     $query->where('products.sub_category_id', $request->input('sub_category_id'));
            // }
            // if (!empty($request->input('brand_id'))) {
            //     $query->where('products.brand_id', $request->input('brand_id'));
            // }
            // if (!empty($request->input('unit_id'))) {
            //     $query->where('products.unit_id', $request->input('unit_id'));
            // }
            // $products = $query->select(
            //     'products.name as product',
            //     'products.item_code',
            //     'v.name as variation_name',
            //     'sub_sku',
            //     'products.type',
            //     'units.short_name as unit',
            //     'categories.name as cat_name',
            //     'brands.name as brand_name'
            // )
            // // ->where('products.location_id',4)
            // ->where('products.is_inactive' ,1)
            // ->where('products.not_for_selling' ,1)
            // ->whereIn('product_activities.id', function ($query) {
            //         $query->select(DB::raw('MAX(id)'))
            //           ->from('product_activities')
            //           ->groupBy('product_id');
            // })
            // // ->groupBy('product_activities_log.product_id')
            // // ->groupBy('v.id')
            // // ->groupBy('products.id')
            // ->get();

        $start_date = $request->input('start_date');
        $end_date =  $request->input('end_date');

        $qry = ProductActivitiesLog::join('products', 'products.id', '=', 'product_activities.product_id')
                ->leftjoin('units', 'products.unit_id', '=', 'units.id')
                ->join('variations as v', 'products.id', '=', 'v.product_id')
                ->leftjoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftjoin('users', 'product_activities.user_id', '=', 'users.id')
                ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
                ->whereIn('product_activities.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('product_activities')
                      ->where('product_activities.new_not_for_selling' ,1)
                      ->groupBy('product_id');
                })
                ->whereBetween(DB::raw('date(product_activities.created_at)'), [$start_date .' 00:00:00', $end_date.' 23:59:59'])
                ->where('products.not_for_selling' ,1)
                ->select(//'product_activities.*',
                'products.name as product',
                DB::raw('CONCAT(COALESCE(users.first_name, ""), COALESCE(users.last_name, "")) as user_name'),
                'products.item_code',
                // 'products.sku',
                // 'products.name as product',
                'v.name as variation_name',
                'sub_sku',
                'products.type',
                'units.short_name as unit',
                'categories.name as cat_name',
                'brands.name as brand_name',
                'product_activities.created_at as last_updated'
                );
                // ->get();

            if (!empty($request->input('category_id'))) {
                $qry->where('products.category_id', $request->input('category_id'));
            }
            if (!empty($request->input('sub_category_id'))) {
                $qry->where('products.sub_category_id', $request->input('sub_category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $qry->where('products.brand_id', $request->input('brand_id'));
            }
            if (!empty($request->input('unit_id'))) {
                $qry->where('products.unit_id', $request->input('unit_id'));
            }

            $products = $qry->get();

            return Datatables::of($products)

                ->editColumn('product', function ($row) {
                    return $row->product;
                })
                ->editColumn('item_code', function ($row) {
                    return $row->item_code;
                })
                 ->editColumn('cat_name', function ($row) {
                    return $row->cat_name;
                })
                 ->editColumn('brand_name', function ($row) {
                    return $row->brand_name;
                })
                ->editColumn('Last_updated', '{{@format_datetime($last_updated)}}')
                ->removeColumn('unit')
                ->removeColumn('id')
                ->removeColumn('variation_name')
                ->rawColumns(['product','item_code','cat_name','brand_name','last_updated'])
                ->make(true);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        // $results = ProductActivitiesLog::select('product_activities.*', 'products.*')
        // ->join('products', 'products.id', '=', 'product_activities.product_id')
        // ->whereIn('product_activities.id', function ($query) {
        //         $query->select(DB::raw('MAX(id)'))
        //               ->from('product_activities')
        //               ->groupBy('product_id');
        //     })
        //     ->get();

        // foreach($results as$data){
        //     echo "<pre>";
        //     print_r($data);
        // }
        // die;

        return view('report.inactive_products_report')
            ->with(compact('categories', 'brands', 'units', 'business_locations'));

        //  dd('1');

     }



     public function activeItemsReport(Request $request)
     {

        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {


            $start_date = $request->input('start_date');
            $end_date =  $request->input('end_date');

            $qry = ProductActivitiesLog::join('products', 'products.id', '=', 'product_activities.product_id')
                ->leftjoin('units', 'products.unit_id', '=', 'units.id')
                ->join('variations as v', 'products.id', '=', 'v.product_id')
                ->leftjoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftjoin('users', 'product_activities.user_id', '=', 'users.id')
                ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
                ->whereIn('product_activities.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('product_activities')
                      ->where('product_activities.new_not_for_selling' ,0)
                      ->groupBy('product_id');
                })
                ->whereBetween(DB::raw('date(product_activities.created_at)'), [$start_date .' 00:00:00', $end_date.' 23:59:59'])
                ->where('products.not_for_selling' ,0)
                ->select(//'product_activities.*',
                'products.name as product',
                DB::raw('CONCAT(COALESCE(users.first_name, ""), COALESCE(users.last_name, "")) as user_name'),
                'products.item_code',
                // 'products.sku',
                // 'products.name as product',
                'v.name as variation_name',
                'sub_sku',
                'products.type',
                'units.short_name as unit',
                'categories.name as cat_name',
                'brands.name as brand_name',
                'product_activities.created_at as last_updated'
                );
                // ->get();

            if (!empty($request->input('category_id'))) {
                $qry->where('products.category_id', $request->input('category_id'));
            }
            if (!empty($request->input('sub_category_id'))) {
                $qry->where('products.sub_category_id', $request->input('sub_category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $qry->where('products.brand_id', $request->input('brand_id'));
            }
            if (!empty($request->input('unit_id'))) {
                $qry->where('products.unit_id', $request->input('unit_id'));
            }

            $products = $qry->get();

            return Datatables::of($products)

                ->editColumn('product', function ($row) {
                    return $row->product;
                })
                ->editColumn('item_code', function ($row) {
                    return $row->item_code;
                })
                 ->editColumn('cat_name', function ($row) {
                    return $row->cat_name;
                })
                 ->editColumn('brand_name', function ($row) {
                    return $row->brand_name;
                })
                ->editColumn('Last_updated', '{{@format_datetime($last_updated)}}')
                ->removeColumn('unit')
                ->removeColumn('id')
                ->removeColumn('variation_name')
                ->rawColumns(['product','item_code','cat_name','brand_name','last_updated'])
                ->make(true);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.active_products_report')
            ->with(compact('categories', 'brands', 'units', 'business_locations'));

     }





    // public function PickPackMismatch($id)
    // {
    //     $business_id = request()->session()->get('user.business_id');

    //     $query = TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
    //     ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
    //     ->leftJoin('contacts as cs', 'c.sales_rep', '=', 'cs.id')
    //     ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
    //     ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
    //     ->leftjoin('categories', 'p.category_id', '=', 'categories.id')
    //     ->leftJoin('purchase_lines as pl', 'p.id', '=', 'pl.product_id')
    //     ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
    //     ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
    //     ->leftJoin('variation_location_details as vld', 'pv.id', '=', 'vld.product_variation_id')
    //     ->leftjoin(
    //         'business_locations AS bl',
    //         't.location_id',
    //         '=',
    //         'bl.id'
    //     )
    //     ->leftjoin(
    //         'res_tables AS rt',
    //         't.res_table_id',
    //         '=',
    //         'rt.id'
    //     )
    //         // ->where('transaction_sell_lines.picking_status', '!=', 2)
    //         // ->where('transaction_sell_lines.packing_status', '!=', 2)
    //         ->where('t.order_picking_status', '!=', '2')
    //         ->where('t.order_packing_status', '!=', '2')
    //         ->where('t.id', $id)
    //         ->where('t.business_id', $business_id)
    //         ->where('t.type', 'sell')
    //         ->where('t.status', 'final');

    //     $products = $query->select(
    //         'p.name as product_name',
    //         'categories.name as category_name',
    //         'p.sku as sku',
    //         DB::raw('ROUND(vld.qty_available) as stock_qty'),
    //         DB::raw('ROUND(transaction_sell_lines.quantity) as order_quantity'),
    //         DB::raw('ROUND(transaction_sell_lines.unit_price, 2) as unit_price'),
    //         DB::raw('ROUND((transaction_sell_lines.quantity * transaction_sell_lines.unit_price), 2) as total')
    //         // DB::raw('(transaction_sell_lines.quantity * transaction_sell_lines.unit_price) as total')
    //     )
    //         ->groupBy('p.name')
    //         ->get();

    //     $formattedProducts = $products->map(function ($product) {
    //         $product->total = number_format($product->total, 2);
    //         return $product;
    //     });

    //     if ($products->isEmpty()) {
    //         return response()->json(['message' => 'No data found'], 404);
    //     }

    //     return response()->json($formattedProducts);
    // }



    public function CustoersBalanceReports(){
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $today = new DateTime();
        $from = (clone $today)->modify('last Friday');

        // If today is Friday, go back one week
        if ($today->format('N') == 5) {
            $from->modify('-1 week');
        }
        $from->setTime(0, 0, 0);
        $to = (clone $from)->modify('+1 week next Saturday');
        $to->setTime(23, 59, 59);
        $from = $from->format('Y-m-d H:i:s');
        $to = $to->format('Y-m-d H:i:s');
        $fromDaydate = date('Y/m/d', strtotime($from));
        $Todaydate = date('Y/m/d', strtotime($to));

        return view('report.contact_reports');
    }

    // public function customersBalanceReportsFinal(Request $request)
    // {
    //     try {
    //         if (!auth()->user()->can('contacts_report.view')) {
    //             abort(403, 'Unauthorized action.');
    //         }

    //         $business_id = $request->session()->get('user.business_id');
    //         // Set start and end dates (example dates)
    //         $start_date = '2021-01-01';
    //         $end_date = '2025-06-28';

    //         // Fetch all contacts
    //         $AllContactsData = Contact::where('contact_status', 'active')
    //               ->get();

    //         // Initialize an array to store results
    //         $contactsBalance = [];

    //         foreach ($AllContactsData as $cData) {
    //             $contact_id = $cData->id;
    //             // Initialize variables
    //             $advance_balance = 0; // You can fetch or initialize as needed
    //             $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start_date, $end_date, $advance_balance);

    //             $balance = 0;
    //             $total_due_balace = 0;

    //             // Process ledger details
    //             for ($i = count($ledger_details['ledger']) - 1; $i >= 0; $i--) {

    //                 if ($cData->type == 'supplier') { // Assuming $cData holds the current contact data
    //                     if ($ledger_details['ledger'][$i]['total'] > 0 && ($ledger_details['ledger'][$i]['type'] == 'expense' || $ledger_details['ledger'][$i]['type'] == 'Purchase')) {
    //                         $balance += $ledger_details['ledger'][$i]['total'];
    //                         $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'];
    //                     } elseif ($ledger_details['ledger'][$i]['total'] > 0 && $ledger_details['ledger'][$i]['type'] == 'Purchase Return') {
    //                         $balance -= $ledger_details['ledger'][$i]['total'];
    //                         $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
    //                     } elseif ($ledger_details['ledger'][$i]['payment_method'] == 'Advance') {
    //                         // Do nothing
    //                     } elseif ($ledger_details['ledger'][$i]['type'] == 'Payment') {
    //                         $tr_type = $ledger_details['ledger'][$i]['transaction_type'] ?? '';
    //                         if ($tr_type == 'purchase_return') {
    //                             $balance += (float)$ledger_details['ledger'][$i]['credit'];
    //                         } else {
    //                             if ($ledger_details['ledger'][$i]['debit'] <= 0) {
    //                                 $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['credit'];
    //                             }
    //                             $balance -= (float)$ledger_details['ledger'][$i]['debit'];
    //                             $ledger_details['ledger'][$i]['credit'] = '';
    //                         }
    //                     }
    //                 } else { // Assuming the contact type is not 'supplier'
    //                     if ($ledger_details['ledger'][$i]['type'] == 'Payment' && $ledger_details['ledger'][$i]['transaction_type'] == 'expense') {
    //                         $ledger_details['ledger'][$i]['debit'] = "";
    //                     }
    //                     if ($ledger_details['ledger'][$i]['type'] == 'Sell' || $ledger_details['ledger'][$i]['type'] == 'Opening Balance') {
    //                         $balance += $ledger_details['ledger'][$i]['total'];
    //                     } else {
    //                         if ($ledger_details['ledger'][$i]['ref_no'] && $ledger_details['ledger'][$i]['is_advance'] == 1 && $ledger_details['ledger'][$i]['advance_amt'] > 0 && $i != count($ledger_details['ledger']) - 1) {
    //                             if ($ledger_details['ledger'][$i]['transaction_id'] == $ledger_details['ledger'][$i + 1]['transaction_id'] && $ledger_details['ledger'][$i + 1]['payment_status'] == '') {
    //                                 $ledger_details['ledger'][$i + 1]['others'] = $ledger_details['ledger'][$i]['others'];
    //                             }
    //                         } elseif ($ledger_details['ledger'][$i]['payment_method'] == 'Advance' || $ledger_details['ledger'][$i]['payment_method'] == 'Credit') {
    //                             // Do nothing
    //                         } else {
    //                             if (($ledger_details['ledger'][$i]['total'] > 0 && $ledger_details['ledger'][$i]['type'] == 'expense') || $ledger_details['ledger'][$i]['type'] == 'Sell Return') {
    //                                 // Do nothing
    //                             } elseif ($ledger_details['ledger'][$i]['total'] > 0) {
    //                                 $balance -= $ledger_details['ledger'][$i]['total'];
    //                             } elseif ($ledger_details['ledger'][$i]['credit'] > 0) {
    //                                 $balance -= $ledger_details['ledger'][$i]['credit'];
    //                             } elseif ($ledger_details['ledger'][$i]['credit'] < 0) {
    //                                 $balance -= $ledger_details['ledger'][$i]['credit'];
    //                             } elseif ($ledger_details['ledger'][$i]['type'] == 'Purchase' && $ledger_details['ledger'][$i]['total'] < 0) {
    //                                 $balance -= $ledger_details['ledger'][$i]['total'];
    //                             }
    //                         }
    //                     }

    //                     if ($ledger_details['ledger'][$i]['type'] == 'Sell Return' && $ledger_details['ledger'][$i]['transaction_type'] == 'not_payment') {
    //                         $balance -= $ledger_details['ledger'][$i]['total'];
    //                     }

    //                     if ($ledger_details['ledger'][$i]['type'] == 'Payment' && $ledger_details['ledger'][$i]['transaction_type'] == 'sell_return') {
    //                         $balance += $ledger_details['ledger'][$i]['debit'];
    //                     }

    //                     if ($ledger_details['ledger'][$i]['type'] == 'Payment') {
    //                         $ledger_details['ledger'][$i]['total'] = $ledger_details['ledger'][$i]['credit'];
    //                     }

    //                     if ($ledger_details['ledger'][$i]['type'] == 'Sell' || $ledger_details['ledger'][$i]['type'] == 'Opening Balance') {
    //                         $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
    //                     } elseif ($ledger_details['ledger'][$i]['type'] == 'Sell Return') {
    //                         $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'];
    //                     } else {
    //                         $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['credit'];
    //                     }

    //                     if ($ledger_details['ledger'][$i]['type'] == 'expense') {
    //                         $balance += $ledger_details['ledger'][$i]['total'];
    //                         if ($ledger_details['ledger'][$i]['total'] >= 0) {
    //                             $ledger_details['ledger'][$i]['debit'] = $ledger_details['ledger'][$i]['total'];
    //                         } else {
    //                             $ledger_details['ledger'][$i]['credit'] = $ledger_details['ledger'][$i]['total'] * -1;
    //                         }
    //                         $ledger_details['ledger'][$i]['type'] = "Adjustment";
    //                     }
    //                 }

    //                 $ledger_details['ledger'][$i]['balance'] = $balance;
    //             }

    //             $total_balance = 0;
    //             $total_credit_balance = 0;
    //             $difference = 0;
    //             $DifferenceData = 0;
    //             $balance_due_total = 0;

    //             //below loop for calculatign Total Credit Data, Total Balance
    //             foreach ($ledger_details['ledger'] as $data) {
    //                 if (($data['payment_status'] == 'Due' && $data['type'] == 'Sell') || $data['type'] == 'Sell Return') {
    //                     if ($data['type'] == 'Sell Return' && $data['payment_status'] == 'Paid') {
    //                         // Do nothing
    //                     } else {
    //                         if ($data['credit'] > 0) {
    //                             $total_credit_balance += $data['credit'];
    //                         } else {
    //                             $total_credit_balance += $data['total'];
    //                         }
    //                     }
    //                 }
    //                 $total_balance += $data['balance'];
    //             }

    //             $total_due_balace = $ledger_details['balance_due'];
    //             // Prepare data for this contact
    //             $contactData = [
    //                 'Name' => $cData->name,
    //                 // 'Mobile' => $cData->mobile,
    //                 'total_balance' => $total_balance,
    //                 'total_credit_balance' => $total_credit_balance,
    //                 'differenceData' => $total_balance - $total_credit_balance,
    //                 'due_total_balance' => $total_due_balace,
    //             ];

    //             // Add contact data to results array
    //             if ($total_balance - $total_credit_balance > 0) {
    //                 $contactsBalance[] = $contactData;
    //             }
    //         }

    //         // Return JSON response with all contact data
    //         return response()->json($contactsBalance);

    //     } catch (\Exception $e) {
    //         // Log the main method error
    //         Log::error('Error in customersBalanceReportsFinal: ' . $e->getMessage());

    //         // Return error response
    //         return response()->json([
    //             'error' => 'An error occurred. Please try again later.'
    //         ], 500);
    //     }
    // }

     public function customersBalanceReportsFinal(Request $request)
    {
        $command = new CustbalanceReportCron(app('App\Utils\TransactionUtil'));
        $result = $command->handle();

        return response()->json($result);
    }

     public function GetARreports(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');


        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftjoin('users AS u', 'contacts.sales_rep', '=', 'u.id')
            ->leftJoin(DB::raw('(SELECT TP.transaction_id, SUM(IF(TP.is_return = 1, -1 * TP.amount, TP.amount)) as total_paid
                                FROM transaction_payments TP
                                GROUP BY TP.transaction_id) as payments'), 'transactions.id', '=', 'payments.transaction_id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('transactions.payment_status', '!=', 'paid')
            ->where('contacts.contact_status', 'active')
            ->select(
                'contacts.name as customer_name',

                'contacts.contact_id',
                'contacts.id as contact_id_new',

                 DB::raw('SUM(CASE
                    WHEN transactions.discount_type = "percentage" THEN transactions.total_before_tax - (transactions.total_before_tax * transactions.discount_amount / 100)
                    WHEN transactions.discount_type = "fixed" THEN transactions.total_before_tax - transactions.discount_amount
                    ELSE transactions.total_before_tax
                END) as final_total1'),
                DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                DB::raw('SUM(transactions.total_before_tax) as total_before_tax'),
                DB::raw('SUM(transactions.final_total) as final_total'),
                DB::raw('SUM(IFNULL(payments.total_paid, 0)) as total_paid'),
                DB::raw('SUM(transactions.final_total - IFNULL(payments.total_paid, 0)) as total_remaining'),
                DB::raw('SUM(CASE WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 1 AND 15 THEN (transactions.final_total - IFNULL(payments.total_paid, 0)) ELSE 0 END) as days_1_15'),
                DB::raw('SUM(CASE WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 16 AND 30 THEN (transactions.final_total - IFNULL(payments.total_paid, 0)) ELSE 0 END) as days_16_30'),
                DB::raw('SUM(CASE WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 31 AND 45 THEN (transactions.final_total - IFNULL(payments.total_paid, 0)) ELSE 0 END) as days_31_45'),
                DB::raw('SUM(CASE WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 46 AND 60 THEN (transactions.final_total - IFNULL(payments.total_paid, 0)) ELSE 0 END) as days_46_60'),
                DB::raw('SUM(CASE WHEN DATEDIFF(CURDATE(), transactions.transaction_date) > 60 THEN (transactions.final_total - IFNULL(payments.total_paid, 0)) ELSE 0 END) as days_61_plus')
            )
            ->groupBy('contacts.id')
            ->orderBy('contacts.name');

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }

            if (!empty($request->input('sr_id'))) {
                $sells->where('u.id', $request->input('sr_id'));
            }

            $datatable = Datatables::of($sells)
                ->editColumn(
                    'final_total',
                    function ($row) {
                        // $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        // if (!empty($discount) && $row->discount_type == 'percentage') {
                        //     $discount = $row->total_before_tax * ($discount / 100);
                        // }

                        // $final_total =  $row->final_total1 + $discount;
                        return  '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="' . $row->final_total  . '">' . $row->final_total  . '</span>';
                    }
                )

                ->editColumn(
                    'total_paid',
                    '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="{{$total_paid}}">{{$total_paid}}</span>'
                )


                ->addColumn('total_remaining', function ($row) {
                    $total_remaining =  $row->final_total - $row->total_paid;
                    if($total_remaining != 0){
                        $total_remaining_html = '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $total_remaining . '">' . $total_remaining . '</span>';
                        return $total_remaining_html;
                    }

                })
                ->addColumn('days_1_15', function ($row) {
                    if($row->days_1_15 != 0){
                        return '<span class="display_currency days_1_15" data-currency_symbol="true" data-orig-value="' . $row->days_1_15 . '">' . $row->days_1_15 . '</span>';
                    }
                })
                ->addColumn('days_16_30', function ($row) {
                    if($row->days_16_30 != 0){
                        return '<span class="display_currency days_16_30" data-currency_symbol="true" data-orig-value="' . $row->days_16_30 . '">' . $row->days_16_30 . '</span>';
                    }
                })
                ->addColumn('days_31_45', function ($row) {
                    if($row->days_31_45 != 0){
                        return '<span class="display_currency days_31_45" data-currency_symbol="true" data-orig-value="' . $row->days_31_45 . '">' . $row->days_31_45 . '</span>';
                    }
                })
                ->addColumn('days_46_60', function ($row) {
                    if($row->days_46_60 != 0){
                        return '<span class="display_currency days_46_60" data-currency_symbol="true" data-orig-value="' . $row->days_46_60 . '">' . $row->days_46_60 . '</span>';
                    }
                })
                ->addColumn('days_61_plus', function ($row) {
                    if($row->days_61_plus != 0){
                        return '<span class="display_currency days_61_plus" data-currency_symbol="true" data-orig-value="' . $row->days_61_plus . '">' . $row->days_61_plus . '</span>';
                    }
                })
                ->filterColumn('user_name', function($query, $keyword) {
                    $sql = "CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))  like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->where('contacts.name', 'like', "%{$keyword}%");
                })
                ->setRowAttr([
                    'data-contact_id' => function ($row) {
                            return $row->contact_id_new;
                    }]);


            $rawColumns = ['customer_name', 'final_total', 'total_paid', 'total_remaining', 'invoice_no', 'balance_due', 'days_1_15', 'days_16_30', 'days_31_45', 'days_46_60', 'days_61_plus'];



            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }
        $users = User::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        return view('report.AR_report', compact('customers', 'users'));
    }


    public function GetARcustomerReport($id, Request $request)
    {
        $business_id = request()->session()->get('user.business_id');


        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftjoin('users AS u', 'contacts.sales_rep', '=', 'u.id')
            ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
            ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
            ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('transactions.payment_status', '!=', 'paid')
            ->where('contacts.id', $id)
            ->select(
                'transactions.id',
                'transactions.transaction_date',
                'transactions.invoice_no',
                'transactions.invoice_no as invoice_no_text',
                'contacts.name',
                'contacts.mobile',
                'contacts.contact_id',
                'contacts.id as contact_id_new',

                'transactions.payment_status',
                'transactions.final_total',
                'transactions.tax_amount',
                'transactions.discount_amount',
                'transactions.discount_type',
                'transactions.total_before_tax',
                DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date'),
                DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_name"),
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id) as total_paid'),
                'bl.name as business_location',
                DB::raw('COUNT(SR.id) as return_exists'),
                DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE TP2.transaction_id = SR.id ) as return_paid'),
                DB::raw('(SELECT tsr.final_total FROM transactions as tsr WHERE tsr.id = SR.id) as amount_return'),
                DB::raw('sum(1) as total_sell'),
                DB::raw('DATEDIFF(CURDATE(), transactions.transaction_date) as days_diff'),

                                DB::raw('CASE
                    WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 0 AND 15 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                    ELSE 0
                END as days_1_15'),
                                DB::raw('CASE
                    WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 16 AND 30 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                    ELSE 0
                END as days_16_30'),
                                DB::raw('CASE
                    WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 31 AND 45 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                    ELSE 0
                END as days_31_45'),
                                DB::raw('CASE
                    WHEN DATEDIFF(CURDATE(), transactions.transaction_date) BETWEEN 46 AND 60 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                    ELSE 0
                END as days_46_60'),
                                DB::raw('CASE
                    WHEN DATEDIFF(CURDATE(), transactions.transaction_date) > 60 THEN transactions.final_total - COALESCE((SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE TP.transaction_id = transactions.id), 0)
                    ELSE 0
                END as days_61_plus')
            )
            ->whereDate('transactions.transaction_date', '>=', $start_date)
            ->whereDate('transactions.transaction_date', '<=', $end_date)
            ->groupBy('transactions.id');

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            // if (!empty($request->input('sr_id'))) {
            //     $sells->where('u.id', $request->input('sr_id'));
            // }
            if (!empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }


            $datatable = Datatables::of($sells->get())
                ->editColumn(
                    'final_total',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        $final_total =  $row->final_total + $discount;
                        return  '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="' . $final_total . '">' . $final_total . '</span>';
                    }
                )
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        $note = '';
                        if (!empty($row->ask_for_check)) {
                            $note = '(ASK FOR CHECK)';
                        }

                        return  $row->name . ' ' . $note;
                    }
                )
                ->editColumn(
                    'total_paid',
                    '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="{{$total_paid}}">{{$total_paid}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining =  $row->final_total - $row->total_paid;
                    if($total_remaining != 0){
                        $total_remaining_html = '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $total_remaining . '">' . $total_remaining . '</span>';
                        return $total_remaining_html;
                    }

                })
                ->addColumn('days_1_15', function ($row) {
                    if($row->days_1_15 != 0){
                        return '<span class="display_currency days_1_15" data-currency_symbol="true" data-orig-value="' . $row->days_1_15 . '">' . $row->days_1_15 . '</span>';
                    }
                })
                ->addColumn('days_16_30', function ($row) {
                    if($row->days_16_30 != 0){
                        return '<span class="display_currency days_16_30" data-currency_symbol="true" data-orig-value="' . $row->days_16_30 . '">' . $row->days_16_30 . '</span>';
                    }
                })
                ->addColumn('days_31_45', function ($row) {
                    if($row->days_31_45 != 0){
                        return '<span class="display_currency days_31_45" data-currency_symbol="true" data-orig-value="' . $row->days_31_45 . '">' . $row->days_31_45 . '</span>';
                    }
                })
                ->addColumn('days_46_60', function ($row) {
                    if($row->days_46_60 != 0){
                        return '<span class="display_currency days_46_60" data-currency_symbol="true" data-orig-value="' . $row->days_46_60 . '">' . $row->days_46_60 . '</span>';
                    }
                })
                ->addColumn('days_61_plus', function ($row) {
                    if($row->days_61_plus != 0){
                        return '<span class="display_currency days_61_plus" data-currency_symbol="true" data-orig-value="' . $row->days_61_plus . '">' . $row->days_61_plus . '</span>';
                    }
                })
                 ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view") || auth()->user()->can("view_own_sell_only")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                }]);


            $rawColumns = ['customer_name', 'final_total', 'total_paid', 'total_remaining', 'invoice_no', 'balance_due', 'days_1_15', 'days_16_30', 'days_31_45', 'days_46_60', 'days_61_plus'];



            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }
        $users = User::forDropdown($business_id, false);
        // $customers = Contact::customersDropdown($business_id, false);
        $contact = Contact::findOrFail($id);

        return view('report.AR_customer_report', compact('contact', 'users', 'id'));
    }

    public function downloadARExcel()
    {

        $filename = 'AR-export-' . \Carbon::now()->format('m-d-Y') . '.xlsx';
        return Excel::download(new ARExport, $filename);

    }
}