@extends('layouts.app')
@section('title', __('product.add_new_product'))
@section('content')
    <section class="content-header">
        <h1>Import from MagicSnap</h1>
    </section>
        <link rel="stylesheet" href="{{ asset('css/magicsnap.css') }}">

<style>
    .input-upper-case{
        text-transform: uppercase;
    }
    #loadingOverlay {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: none;
        justify-content: center; /* Center horizontally */
        align-items: center; /* Center vertically */
    }
    
    .spinner-border {
        top: 50%;
        position: relative;
        left: 50%;
        width: 3rem;
        height: 3rem;
    }
    .table-sort {
        text-transform: inherit;
        letter-spacing: inherit;
        background: inherit;
        width: 100%;
        text-align: inherit;
        transition: color .3s;
        margin: -.5rem -.75rem;
        padding: .5rem .75rem;
    }
    .fixedHeader-floating{
        display:none;
    }
    .dt-buttons, .btn-group{
            display:none;
    }
</style>
     <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            <form>
                                <label>ERP:</label>
                                <select id="erp_dropdown" class="form-control select2" style="width:100%" placeholder="All"></select>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <form>
                                <label>Brand:</label>
                                <select id="brand_dropdown" class="form-control select2" style="width:100%" placeholder="All"></select>
                            </form>
                        </div>
                    </div>
                   <div class="col-md-3">
                    <div class="form-group">
                        <label for="category_dropdown">Category:</label>
                        <select id="category_dropdown" class="form-control select2" style="width:100%">
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                </div>
                @endcomponent
            </div>
        </div>
    
<div class="row">
    <div class="container-xl">
        <div class="col-md-12"><!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#product_list_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fa fa-cubes" aria-hidden="true"></i> @lang('lang_v1.all_products')
                        </a>
                    </li>
                </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="product_list_tab">
                @php
                    $colspan = 14;
                    //$custom_labels = json_decode(session('business.custom_labels'), true);
                @endphp
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view hide-footer" id="products_table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="check-all"></th>
                        <th class="table-sort">ERP </th>
                        <th class="table-sort">Product Image </th>
                        <th class="table-sort">Product Name </th>
                        <th class="table-sort">SKU </th>
                        <th class="table-sort">Brand Name </th>
                        <th class="table-sort">Category Name</th>
                        <th class="table-sort">Sub Category Name</th>
                        <th class="table-sort" style="display:none;">Image Name</th>
                        <th class="table-sort" style="display:none;">Purchase Price</th>
                        <th class="table-sort">Sell Price </th>
                        <th class="table-sort"  style="display:none;">Profit Percent </th>
                        <th class="table-sort">Qty in Box </th>
                        <th class="table-sort">Case Qty </th>
                        <th class="table-sort">Product Description </th>
                        <th class="table-sort">Note </th>
                        <th class="table-sort">sku2 </th>
                        <th class="table-sort">sku3 </th>
                        <th class="table-sort">sku4 </th>
                        <th class="table-sort">sku5 </th>
                        <th class="table-sort">sku6 </th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot></tfoot>
            </table>
                <button class="btn btn-primary" style="margin: 0 10px" id="submit-selected">Import Selected Products</button>
        </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal HTML -->
<div class="modal fade" id="skuWarningModal" style="overflow-y:hidden !important; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" tabindex="-1" aria-labelledby="skuWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); border-radius:8px;">
            <div class="modal-header" style="border-bottom: none;">
                <h5 class="modal-title" style="margin-bottom:0; font-size:2rem; line-height:1.4285714286;" id="skuWarningModalLabel">Are You Sure?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
    <div class="modal-body" style="overflow-y: auto; max-height: 450px;">
            <p>The following Products to be imported:</p>
            <ul id="newSkusList"></ul>
            <p>The following Products already exist. Are you sure you want to import these products as well?</p>
            <ul id="existingSkusList"></ul>
    </div>
            <div class="modal-footer" style="border-top: none;">
                <button type="button" class="btn btn-secondary-new" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-new" id="confirmImport">Confirm</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->


<div class="modal fade" id="responseModal" style="overflow-y:hidden !important; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" tabindex="-1" aria-labelledby="skuWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); border-radius:8px;">
            <div class="modal-header" style="border-bottom: none;">
                <h5 class="modal-title" style="margin-bottom:0; font-size:2rem; line-height:1.4285714286;" id="responseModalLabel">Import Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="responseModalBody" style="overflow-y: auto; max-height: 450px;">
            </div>
            <div class="modal-footer" style="border-top: none;">
                       <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div>
    </div>
</div>

</section>

@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
function populateCategoryDropdown(dropdown, categories) {
    dropdown.empty();
    dropdown.append($('<option>').val('').text('All'));
    const allCategoriesGroup = $('<optgroup>').attr('label', 'All Categories');
    categories.forEach(function(category) {
        allCategoriesGroup.append($('<option>').val(category.ms_name).text(category.ms_name));
    });
    dropdown.append(allCategoriesGroup);
    categories.forEach(function(category) {
        if (category.subCategories && category.subCategories.length > 0) {
            const group = $('<optgroup>').attr('label', category.ms_name);
            category.subCategories.forEach(function(subCategory) {
                group.append($('<option>').val(subCategory.ms_name).text(subCategory.ms_name));
            });
            dropdown.append(group);
        } else {
            dropdown.append($('<option>').val(category.ms_name).text(category.ms_name));
        }
    });
}

function populateDropdown(dropdown, items, textKey, valueKey) {
    dropdown.empty();
    dropdown.append($('<option>').val('').text('All'));
    items.forEach(function(item) {
        dropdown.append($('<option>').val(item[valueKey]).text(item[textKey]));
    });
}

$(document).ready(function () {
   $('#erp_dropdown, #brand_dropdown, #category_dropdown').on('change', function () {
    table.ajax.reload(null, false);
});

    $.ajax({
        url: 'https://magicsnap.bbtl.app/api/dropdown-data',
        method: 'GET',
        contentType: 'application/json', 
        crossDomain: true, 
        credentials: 'include', 
        success: function(data) {
            console.log(data);  
            var erpDropdown = $('#erp_dropdown');
            populateDropdown(erpDropdown, data.erpNames, 'ms_name', 'id');
            $('#erp_dropdown, #brand_dropdown').select2("destroy").select2();
            populateDropdown($('#brand_dropdown'), data.brands, 'ms_name', 'id');
            populateCategoryDropdown($('#category_dropdown'), data.categories, 'ms_name');
            $('#category_dropdown').select2(); // re-initialize select2
        }
    });

    var table = $('#products_table').DataTable({
          processing: true,
          serverSide: true,
          scrollCollapse: true,
          scrollY: 500,
          scrollX: true,
            ajax: {
            url: 'https://magicsnap.bbtl.app/api/fetchproduct',
            type: 'GET',
            data: function (d) {
                d.erp_id = $('#erp_dropdown').val();
                d.brand_name = $('#brand_dropdown').val();  
                d.category_name = $('#category_dropdown').val();  
            },
            contentType: 'application/json',
            crossDomain: true,
            credentials: 'include'},
            columns: [
            {data: null, defaultContent: '', orderable: false, render: function(data, type, row) { return `<input type="checkbox" class="dt-checkbox" value="${row.id}">`; }, className: 'select-checkbox', targets: 0 },
            {data: 'a_erp_name', name: 'a_erp_name'},
            {data: 'uploaded_image',name: 'uploaded_image',render: function(data, type, full, meta) { return '<img src="https://magicsnap.bbtl.app/mainproductimages/' + data + '" height="50" />';}},
            {data: 'product_name', name: 'product_name'},
            {data: 'sku', name: 'sku'},
            {data: 'brand_name', name: 'brand_name'},
            {data: 'categories_name', name: 'category_name'},
            {data: 'sub_cat_name', name: 'sub_cat_name'},
            {data: 'uploaded_image', name: 'uploaded_image', visible: false },
            {data: 'default_purchase_price', name: 'default_purchase_price', visible: false },
            {data: 'default_sell_price', name: 'default_sell_price'},
            {data: 'profit_percent', name: 'profit_percent', visible: false },
            {data: 'qty_in_box', name: 'qty_in_box'},
            {data: 'case_qty', name: 'case_qty'},
            {data: 'product_description', name: 'product_description'},
            {data: 'note', name: 'note'},
            {data: 'sku2', name: 'sku2'},
            {data: 'sku3', name: 'sku3'},
            {data: 'sku4', name: 'sku4'},
            {data: 'sku5', name: 'sku5'},
            {data: 'sku6', name: 'sku6'}
        ],
           order: [[1, 'asc']],
    });
    
    $('#check-all').on('click', function(){
    var rows = table.rows({ 'search': 'applied' }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
});

$('#products_table tbody').on('change', 'input[type="checkbox"]', function(){
    if (!this.checked) {
        var el = $('#check-all').get(0);
        if (el && el.checked && ('indeterminate' in el)) {
            el.indeterminate = true;
        }
    }
});

function updateDataTableSelectAllCtrl(table){
    var $table             = table.table().node();
    var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
    var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
    var chkbox_select_all  = $('thead input[type="checkbox"]', $table).get(0);

    if($chkbox_checked.length === 0){
        chkbox_select_all.checked = false;
        if('indeterminate' in chkbox_select_all){
            chkbox_select_all.indeterminate = false;
        }
    }
    else if ($chkbox_checked.length === $chkbox_all.length){
        chkbox_select_all.checked = true;
        if('indeterminate' in chkbox_select_all){
            chkbox_select_all.indeterminate = false;
        }
    }
    else {
        chkbox_select_all.checked = true;
        if('indeterminate' in chkbox_select_all){
            chkbox_select_all.indeterminate = true;
        }
    }
}
$('#products_table tbody').on('draw.dt', function(){
    updateDataTableSelectAllCtrl(table);
});
function decodeHtmlEntities(str) {
    var txt = document.createElement("textarea");
    txt.innerHTML = str;
    return txt.value;
}

$('#submit-selected').on('click', function() {
    // Clear existing data inside the lists
    $('#existingSkusList').empty();
    $('#newSkusList').empty();
    $('#existingProducts').hide();
    $('#toBeImported').hide();
    $('#skuWarningModal .modal-body').find('p').hide(); // Hide all <p> elements initially

    var selectedData = [];
    table.$('input[type="checkbox"]:checked').each(function() {
        var row = table.row($(this).closest('tr')).data();
        selectedData.push({
            name: decodeHtmlEntities(row.product_name),
            sku: row.sku,
            brand_name: row.brand_name,
            categories_name: row.categories_name,
            sub_cat_name: row.sub_cat_name,
            image: row.uploaded_image,
            default_purchase_price: row.default_purchase_price,
            default_sell_price: row.default_sell_price,
            profit_percent: row.profit_percent
        });
    });

    if (selectedData.length > 0) {
        $.ajax({
            url: '/check-existing-skus',
            type: 'POST',
            data: JSON.stringify(selectedData),
            contentType: 'application/json; charset=utf-8',
            success: function(existingProducts) {
                var newSkus = [];
                if (existingProducts.length > 0) {
                    existingProducts.forEach(function(product) {
                        $('#existingSkusList').append($('<li>').text('(' + product.sku + ') ' + product.name));
                    });
                    $('#existingProducts').show(); // Show the existing products section
                    newSkus = selectedData.filter(product => !existingProducts.some(ep => ep.sku === product.sku));
                } else {
                    $('#toBeImported').show(); // Show the "to be imported" section
                    newSkus = selectedData;
                }
                newSkus.forEach(function(product) {
                    $('#newSkusList').append($('<li>').text('(' + product.sku + ') ' + product.name));
                });

                // Show <p> elements based on content
                if ($('#newSkusList').children().length > 0) {
                    $('#newSkusList').prev('p').show();
                }
                if ($('#existingSkusList').children().length > 0) {
                    $('#existingSkusList').prev('p').show();
                }

                $('#skuWarningModal').modal('show');

                $('#confirmImport').off('click').on('click', function() {
                    $('#skuWarningModal').modal('hide');
                    submitSelectedProducts(selectedData);
                });
            },
            error: function(error) {
                console.error('Error:', error);
            }
        });
    } else {
        alert('No products selected');
    }
});


function submitSelectedProducts(selectedData) {
        $.ajax({
            url: '/submit-selected-products',
            type: 'POST',
            data: JSON.stringify(selectedData),
            contentType: 'application/json; charset=utf-8',
            success: function(response) {
                $('#responseModalBody').html(`<p>${response.message}</p>`);
                $('#responseModal').modal('show');
            },
            error: function(error) {
                console.error('Error:', error);
                $('#responseModalBody').html(`<p>Failed to add products. Please try again.</p>`);
                $('#responseModal').modal('show');
            }
        });
    }
});
</script>
@endsection