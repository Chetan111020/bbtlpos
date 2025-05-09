<?php

namespace App\Http\Controllers;

use App\TaxRate;
use App\GroupSubTax;
use App\Category;
use App\Product;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

use App\Utils\TaxUtil;

class TaxRateController extends Controller
{

    /**
     * All Utils instance.
     *
     */
    protected $taxUtil;

    /**
     * Constructor
     *
     * @param TaxUtil $taxUtil
     * @return void
     */
    public function __construct(TaxUtil $taxUtil)
    {
        $this->taxUtil = $taxUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('tax_rate.view') && !auth()->user()->can('tax_rate.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $data = TaxRate::where('business_id', $business_id)
                ->where('is_tax_group', '0')
                ->select('name', 'begining_date', 'end_date', 'state', 'category', 'sub_category', 'inactive', 'tax', 'taxvalue', 'every', 'tax_percent', 'city_tax_value', 'everycity', 'first_item_value', 'second_item_value', 'note', 'id', 'for_tax_group')
                ->get();

            // return Datatables::of($tax_rates)
            //     ->addColumn(
            //         'action',
            //         '@can("tax_rate.update")
            //         <button data-href="{{action(\'TaxRateController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_tax_rate_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
            //             &nbsp;
            //         @endcan
            //         @can("tax_rate.delete")
            //             <button data-href="{{action(\'TaxRateController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_tax_rate_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
            //         @endcan'
            //     )
            //     ->editColumn('name', '@if($for_tax_group == 1) {{$name}} <small>(@lang("lang_v1.for_tax_group_only"))</small> @else {{$name}} @endif')
            //     ->removeColumn('for_tax_group')
            //     ->removeColumn('id')
            //     ->rawColumns([0, 2])
            //     ->make(false);
            $currency = DB::table('business')
                ->join('currencies','currencies.id','=','business.currency_id')
                ->where('business.id',request()->session()->get('user.business_id'))
                ->first();


            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('category',function ($data) use($currency){
                    if ($data->category != null){
                        return $category = Category::where('id', $data->category)->value('name').' ('.$data->category .')';
                    }else{
                        return $data->category;
                    }
                })->addColumn('sub_category',function ($data) use($currency){
                    if ($data->sub_category != null){
                       return Category::where('id', $data->sub_category)->where('parent_id', $data->category)->whereNotIn('parent_id', [0])->value('name').' ('.$data->sub_category.')';
                    }
                })
                ->addColumn('tax',function ($data){
                    return $data->tax."%";
                })
                ->addColumn('taxvalue',function ($data) use($currency){
                    if ($data->taxvalue != null){
                        return $currency->symbol.$data->taxvalue;
                    }
                })
                ->addColumn('tax_percent',function ($data){
                    return $data->tax_percents."%";
                })
                ->addColumn('city_tax_value',function ($data) use ($currency){
                    if ($data->city_tax_value != null) {
                        return $currency->symbol . $data->city_tax_value;
                    }
                })
                ->addColumn('first_item_value',function ($data) use ($currency){
                    if ($data->first_item_value != null) {
                        return $currency->symbol . $data->first_item_value;
                    }
                })
                ->addColumn('second_item_value',function ($data) use ($currency){
                    if ($data->second_item_value != null) {
                        return $currency->symbol . $data->second_item_value;
                    }
                })
                ->addColumn(
                    'action',
                    '@can("tax_rate.update")
                    <button data-href="{{action(\'TaxRateController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_tax_rate_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    @endcan
                    @can("tax_rate.delete")
                        <button data-href="{{action(\'TaxRateController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_tax_rate_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->rawColumns(['action'])
                ->make(true);
        }
        // $alldata=TaxRate::all()->toArray();

        return view('tax_rate.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('tax_rate.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $sub_categories = [];
        return view('tax_rate.create',compact('categories','sub_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//         dd($request->all());
        if (!auth()->user()->can('tax_rate.create')) {
            abort(403, 'Unauthorized action.');
        }
        $categoryData = "";
        $categories = $request->input('category');
        $catLength = count($categories);
        foreach ($categories as $key => $category){
            if ($key == $catLength-1){
                $categoryData .= $category;
            }else{
                $categoryData .=  $category.",";
            }
        }
        $subCategoryData = "";
        $subCategories = $request->input('subcategory');
        if($subCategories != null) {
            $subCatLength = count($subCategories);
            foreach ($subCategories as $key => $subCategory){
                if ($key == $subCatLength-1){
                    $subCategoryData .= $subCategory;
                }else{
                    $subCategoryData .=  $subCategory.",";
                }
            }
        }

        try {
            $input = $request->only(['name', 'amount']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');
            // $input['amount'] = $this->taxUtil->num_uf($input['amount']);
            $input['begining_date'] = $request->input('begining_date');
            $input['end_date'] = $request->input('end_date');
            $input['state'] = $request->input('state');
            $input['category'] = $categoryData;
            $input['sub_category'] = $subCategoryData;
            $input['inactive'] = $request->input('inactive');
            $input['tax'] = $request->input('tax');
            $input['taxvalue'] = $request->input('taxvalue');
            $input['every'] = $request->input('every');
            $input['tax_percent'] = $request->input('tax_percent');
            $input['city_tax_value'] = $request->input('city_tax_value');
            $input['everycity'] = $request->input('everycity');
            $input['first_item_value'] = $request->input('first_item_value');
            $input['second_item_value'] = $request->input('second_item_value');
            $input['note'] = $request->input('note');
            $input['for_tax_group'] = !empty($request->for_tax_group) ? 1 : 0;
            $input['tax_type'] = $request->tax_type;

            if(!empty($request->input('taxvalue_ml'))){
                $input['taxvalue'] = $request->input('taxvalue_ml');
                $input['is_ml'] = 1;
            }

            $tax_rate = TaxRate::create($input);
            $output = ['success' => true,
                'data' => $tax_rate,
                'msg' => __("tax_rate.added_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = ['success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('tax_rate.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $tax_rate = TaxRate::where('business_id', $business_id)->find($id);

            $business_id = request()->session()->get('user.business_id');
            $categories = Category::forDropdown($business_id, 'product');
            $sub_categories = [];
            return view('tax_rate.edit')
                ->with(compact('tax_rate','categories','sub_categories'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('tax_rate.update')) {
            abort(403, 'Unauthorized action.');
        }

        $categoryData = "";
        $categories = $request->input('category');
        $catLength = count($categories);
        foreach ($categories as $key => $category){
            if ($key == $catLength-1){
                $categoryData .= $category;
            }else{
                $categoryData .=  $category.",";
            }
        }
        $subCategoryData = "";
        $subCategories = $request->input('subcategory');
        if($subCategories != null) {
            $subCatLength = count($subCategories);
            foreach ($subCategories as $key => $subCategory){
                if ($key == $subCatLength-1){
                    $subCategoryData .= $subCategory;
                }else{
                    $subCategoryData .=  $subCategory.",";
                }
            }
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'begining_date', 'end_date', 'state', 'category', 'sub_category', 'inactive', 'tax', 'taxvalue', 'every', 'tax_percent', 'city_tax_value', 'everycity', 'first_item_value', 'second_item_value', 'note']);
                $business_id = $request->session()->get('user.business_id');

                $tax_rate = TaxRate::where('business_id', $business_id)->findOrFail($id);
                $tax_rate->name = $input['name'];
                // $tax_rate->amount = $this->taxUtil->num_uf($input['amount']);

                $tax_rate->begining_date = $request->input('begining_date');
                $tax_rate->end_date = $request->input('end_date');
                $tax_rate->state = $request->input('state');
                $tax_rate->category =$categoryData;
                $tax_rate->sub_category = $subCategoryData;
                $tax_rate->inactive = $request->input('inactive');
                $tax_rate->tax = $request->input('tax');
                $tax_rate->taxvalue = $request->input('taxvalue');
                $tax_rate->every = $request->input('every');
                $tax_rate->tax_percent = $request->input('tax_percent');
                $tax_rate->city_tax_value = $request->input('city_tax_value');
                $tax_rate->everycity = $request->input('everycity');
                $tax_rate->first_item_value = $request->input('first_item_value');
                $tax_rate->second_item_value = $request->input('second_item_value');
                $tax_rate->note = $request->input('note');
                $tax_rate->tax_type = $request->input('tax_type');

                $tax_rate->for_tax_group = !empty($request->for_tax_group) ? 1 : 0;

                if(!empty($request->input('taxvalue_ml'))){
                    $tax_rate->taxvalue = $request->input('taxvalue_ml');
                    $tax_rate->is_ml = 1;
                }
                else{
                    $tax_rate->is_ml = 0;
                }

                $tax_rate->save();

                //update group tax amount
                $group_taxes = GroupSubTax::where('tax_id', $id)
                    ->get();

                foreach ($group_taxes as $group_tax) {
                    $this->taxUtil->updateGroupTaxAmount($group_tax->group_tax_id);
                }

                $output = ['success' => true,
                    'msg' => __("tax_rate.updated_success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('tax_rate.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                //update group tax amount
                $group_taxes = GroupSubTax::where('tax_id', $id)
                    ->get();
                if ($group_taxes->isEmpty()) {
                    $business_id = request()->user()->business_id;

                    $tax_rate = TaxRate::where('business_id', $business_id)->findOrFail($id);
                    $tax_rate->delete();

                    $output = ['success' => true,
                        'msg' => __("tax_rate.deleted_success")
                    ];
                } else {
                    $output = ['success' => false,
                        'msg' => __("tax_rate.can_not_be_deleted")
                    ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }
}
