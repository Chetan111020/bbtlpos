<?php
use App\Http\Controllers\__Compute\ReportComputeController;
use App\Http\Controllers\__Compute\AnalyticsController;
use App\Http\Controllers\__Compute\ComputeController;
use App\Http\Controllers\__Compute\InvoiceController;
use App\Http\Controllers\__Compute\MetaController;
use App\Http\Controllers\__Compute\OrderFlowController;
use App\Http\Controllers\__Compute\SmartSyncController;
use App\Http\Controllers\__Compute\MagicsnapController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. Theses
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

include_once('install_r.php');
// Route::get('test12', function(){
//     phpinfo();
// });

Route::middleware(['setData'])->group(function () {
    Route::get('/', function () {
        // return view('welcome');
        return redirect()->to('/login');
    });

    Auth::routes();

    Route::get('/business/register', 'BusinessController@getRegister')->name('business.getRegister');
    Route::post('/business/register', 'BusinessController@postRegister')->name('business.postRegister');
    Route::post('/business/register/check-username', 'BusinessController@postCheckUsername')->name('business.postCheckUsername');
    Route::post('/business/register/check-email', 'BusinessController@postCheckEmail')->name('business.postCheckEmail');

    Route::get('/quote/{token}', 'SellPosController@showInvoice')
        ->name('show_quote');
    Route::get('/invoice/{token}', 'SellPosController@showInvoice')
        ->name('show_invoice');

    Route::get('/ps/{token}', 'SellPosController@packing_slip_blank_dl')->name('show_ps');
    Route::get('/psd/{token}', 'SellPosController@packing_slip_blank_forward')->name('show_psd');

    Route::get('/pswpl/{token}', 'SellPosController@packing_slip_blank_without_price_dl')->name('show_pswpl');
    Route::get('/pswp/{token}', 'SellPosController@packing_slip_blank_without_price_send')->name('show_pswp');


    //added by developer1
    Route::get('/printemailinvoice/{token}/{print}', 'SellPosController@showInvoice')
        ->name('show_print_invoice');

    Route::get('/creditmemo/{token}', 'SellReturnController@getCreditMemo')
    ->name('show_credit_memo');

    // Route::post('/testrr', [ComputeController::class,'test']);
});

Route::get('/products/on-hand', 'ProductController@productOnhand');

//Routes for authenticated users only
Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->group(function () {

    /**
     * Private Routes Area Start
     */

    //Customer Data

    Route::get('/reports/customersBalanaceReport','ReportController@CustoersBalanceReports');
    Route::post('reports.customersBalanceReportsFinal', 'ReportController@customersBalanceReportsFinal')->name('reports.customersBalanceReportsFinal');

    Route::get('/magicsnap/dash', [MagicsnapController::class,'dash'])->name('magicsnap.dash');
    Route::post('/submit-selected-products', [MagicsnapController::class, 'submitSelectedProducts']);
    Route::post('/check-existing-skus', [MagicsnapController::class, 'checkExistingSkus']);

    Route::get('/recalc/group-details/{customer_id}', [ComputeController::class,'getGroupDetails'] )->name('compute.getGroupDetails');
    Route::get('/recalc/{variation_id}/{customer_id}', [ComputeController::class,'getGroupPrice'] )->name('compute.getGroupPrice');

    Route::get('/order-flow', [OrderFlowController::class,'index'])->name('orderflow.index');
    Route::get('/order-flow/view/{id}', [OrderFlowController::class,'view'])->name('orderflow.view');
    Route::get('/order-flow/picking/{id}', [OrderFlowController::class,'picking'])->name('orderflow.picking');
    Route::get('/order-flow/orders', [OrderFlowController::class,'getOrders'])->name('orderflow.getorders');
    Route::get('/order-flow/mylist/add/{id}', [OrderFlowController::class,'addToMylist'])->name('orderflow.addToMylist');

    Route::get('/v2/invoice-manager/print/{id}', [InvoiceController::class,'smartInvoice'])->name('invoice.smart');

    Route::get('/meta/test', [MetaController::class,'whatsappMessage'])->name('meta.test');
    Route::get('/reports/back-order', [ComputeController::class,'backOrderReport'])->name('reports.back_order');
    Route::get('/Analytics/Product/{id?}',[AnalyticsController::class,'productAnalytics'])->name('analytics.product');
    Route::get('/POS/Update/SellPriceGroup/{id}',[ComputeController::class,'updateSalePriceGroup'])->name('pos.update.sellpricegroup');
    Route::get('/reports/understock', [ReportComputeController::class,'underStockReport'])->name('reports.understock');
    Route::get('/reports/overstock', [ReportComputeController::class,'overStockReport'])->name('reports.overstock');
    Route::get('/reports/velocity', [ReportComputeController::class,'velocityReport'])->name('reports.velocity');
    Route::get('/reports/sku-performance', [ReportComputeController::class,'skuReport'])->name('reports.velocity.sku');
    Route::get('/reports/price-update', [ReportComputeController::class,'priceUpdateReport'])->name('reports.price.update');


    Route::get('/reports/pick-pack-mismatch/{id}', [ReportComputeController::class,'PickPackMismatch']);


    Route::post('/purchase-order/Bulk/Delete', 'PurchaseOrderController@bulkDelete')->name('po.bulk.delete');
    Route::get('/purchase-order/auto/create', 'PurchaseOrderController@create')->name('po.auto.create.get');
    Route::post('/purchase-order/auto/create', 'PurchaseOrderController@create')->name('po.auto.create.post');

    // Order Delivery
    Route::get('/order-delivery', 'OrderDeliveryController@index');
    Route::get('/order-delivery/create', 'OrderDeliveryController@create');
    Route::post('/order-delivery/checkorder','OrderDeliveryController@checkOrder');
    Route::post('/order-delivery/store', 'OrderDeliveryController@store');
    Route::get('/order-delivery/view/{id}', 'OrderDeliveryController@show');
    Route::get('/order-delivery/edit/{id}', 'OrderDeliveryController@edit');
    Route::post('/order-delivery/update/{id}', 'OrderDeliveryController@update');
    Route::get('/order-delivery/delete/{id}', 'OrderDeliveryController@delete');

    Route::resource('sells-new-old', 'SellNewOldController')->except(['show']);
    // Route::get('/purchase-return/print/{id}', 'CombinedPurchaseReturnController@printInvoice');
    Route::get('/purchase-return/print/{id}', 'PurchaseReturnController@printInvoice');
    Route::post('/send-mail-with-pdf/{id}', 'SellPosController@sendEmailforInvoiceWithPdf');
    Route::get('/products/download-excel', 'ProductController@downloadExcel');

    Route::get('/send-mail-with-attach-pdf/{id}', 'SellPosController@sendEmailforInvoiceWithPdf1');

    Route::get('/set-key-custom',[ComputeController::class,'setSmartValue_custom'])->name('compute.set.custom');

    Route::get('/smart-sync/products', [SmartSyncController::class,'smartProducts'])->name('smart.products');
    Route::get('/smart-sync/products/list', [SmartSyncController::class,'smartProductsList'])->name('smart.products.list');
    Route::post('/smart-sync/products/data', [SmartSyncController::class,'getSmartProductsData'])->name('smart.products.data');
    Route::post('/smart-sync/task/data', [SmartSyncController::class,'getCurrentTaskDetails'])->name('smart.task.data');
    Route::post('/smart-sync/task/abort/{id}', [SmartSyncController::class,'abortTask'])->name('smart.task.abort');
    Route::post('/smart-sync/task/add', [SmartSyncController::class,'addTask'])->name('smart.task.add');

    Route::get('/reports/state-tax-report', 'ReportController@stateTaxReport')->name('report.state_tax');
    Route::post('/reports/get-state-tax-report', 'ReportController@getStateTaxReport')->name('report.get_state_tax');

    Route::post('/api-clipdrop/remove-bg', [ComputeController::class,'removeBackground'])->name('remove.bg');
    Route::post('/api-textbelt/send', [ComputeController::class,'sendSmsApi'])->name('api.sendsms');
    Route::post('/api-waha', [ComputeController::class,'wahaMessage'])->name('api.waha');
    Route::get('/qr-code/create', [ComputeController::class,'qrCreate'] )->name('qr.create');
    Route::post('/qr-code/generate', [ComputeController::class,'qrGenerate'] )->name('qr.generate');
    Route::get('/qr-code/scan', [ComputeController::class,'qrScan'] )->name('qr.scan');
    Route::post('/qr-code/update', [ComputeController::class,'qrUpdate'] )->name('qr.update');

    Route::get('/transaction/mark-paid/{id}', [ComputeController::class,'markPaid'])->name('transaction.markpaid');
    Route::get('/transaction/mark-due/{id}', [ComputeController::class,'markDue'])->name('transaction.markdue');
    Route::get('/statement-check', [ComputeController::class,'statements']);
    Route::get('/testtt', [ComputeController::class,'test']);

    Route::get('/bt-tr/{id}', [ComputeController::class,'balanceTransfer']);
    Route::get('/bt-contact/{id}', [ComputeController::class,'balanceTransferOnContact']);

    /**
     * Private Routes Area End
     */

    // Route::get('/30data','NewHomeController@GetTopCustomer');
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/home/get-totals', 'HomeController@getTotals');
    Route::get('/home/graph', 'HomeController@getGraph');
    Route::get('/home/product-stock-alert', 'HomeController@getProductStockAlert');
    Route::get('/home/purchase-payment-dues', 'HomeController@getPurchasePaymentDues');
    Route::get('/home/sales-payment-dues', 'HomeController@getSalesPaymentDues');
    Route::get('/calendar', 'HomeController@getCalendar')->name('calendar');
 Route::get('/home/Pick_PackDashboard/pickingchart','HomeController@pickingchart');
    Route::get('/home/Pick_PackDashboard/paymentchart', 'HomeController@TotalPaymentChart');
    //demo home new
    Route::get('/AdminDashboard','NewHomeController@index');
    Route::get('/demohome/get-totals', 'NewHomeController@getTotals');
    Route::get('/demohome/saleschart','NewHomeController@SalesChart');
    Route::get('/demohome/purchaseduechart','NewHomeController@PurchaseDueChart');
    // Route::get('/30data','NewHomeController@GetTopCustomer');


    Route::get('/CashierDashboard','CashierController@Index');
    Route::get('/CashierDashboard/getvalue','CashierController@getFinalTransactions');
    Route::get('/CashierDashboard/quotation','CashierController@GetQuotation');
    Route::get('/CashierDashboard/draft','CashierController@GetDraft');
    Route::get('/CashierDashboard/paid_invoices','CashierController@GetPaidInvoices');
    Route::get('/CashierDashboard/partial_invoices','CashierController@GetPartialInvoices');
    Route::get('/CashierDashboard/due_invoices','CashierController@GetDueInvoices');
    Route::get('/CashierDashboard/gettotals','CashierController@get_totals');
    Route::get('/CashierDashboard/total-sales','CashierController@total_sales');
    Route::get('/CashierDashboard/googlemap', 'CashierController@GoogleMap');

    //Picking & Packing Dashboard
    Route::get('/Pick_PackDashboard','PickingPackingController@Index');
    Route::get('/Pick_PackDashboard/pickingchart','PickingPackingController@pickingchart');
    Route::get('/Pick_PackDashboard/getpickingtotal','PickingPackingController@GetPickingTotal');
    Route::get('/Pick_PackDashboard/orderchart','PickingPackingController@TotalOrderChart');
    Route::get('/Pick_PackDashboard/paymentchart', 'PickingPackingController@TotalPaymentChart');
    Route::get('/Pick_PackDashboard/WeeklyWebsiteOrders', 'PickingPackingController@WeeklyWebsiteOrders');



    //Account Dashboard
    Route::get('AccountDashboard','AccountDashboardController@Index');
    Route::get('/AccountDashboard/getchart','AccountDashboardController@GetPurchaseSell');
    Route::get('/AccountDashboard/get_totals','AccountDashboardController@GetTotal');
    Route::get('/AccountDashboard/getincomeexpense','AccountDashboardController@GetIncomeExpence');
    Route::get('/30data','AccountDashboardController@GetTotal');
    Route::get('/AccountDashboard/getaccount','AccountDashboardController@AccountPay');
    Route::get('/AccountDashboard/getreceivacc','AccountDashboardController@AccountReceiv');

    Route::post('/test-email', 'BusinessController@testEmailConfiguration');
    Route::post('/test-sms', 'BusinessController@testSmsConfiguration');
    Route::get('/business/settings', 'BusinessController@getBusinessSettings')->name('business.getBusinessSettings');
    Route::post('/business/update', 'BusinessController@postBusinessSettings')->name('business.postBusinessSettings');
    Route::get('/user/profile', 'UserController@getProfile')->name('user.getProfile');
    Route::post('/user/update', 'UserController@updateProfile')->name('user.updateProfile');
    Route::post('/user/update-password', 'UserController@updatePassword')->name('user.updatePassword');


    //Account Dashboard
    Route::get('/account-dashboard','AccountDashboardController@index');
    // Route::get('/dashboard/pickingchart','AccountDashboardController@pickingchart');
    Route::get('/account-dashboard/getTotals','AccountDashboardController@getTotals');



    Route::resource('brands', 'BrandController');

    Route::resource('payment-account', 'PaymentAccountController');

    Route::resource('tax-rates', 'TaxRateController');

    Route::resource('units', 'UnitController');

    Route::get('/contacts/payments/{contact_id}', 'ContactController@getContactPayments');
    Route::get('/contacts/followup/{contact_id}', 'ContactController@getFollowUpDetails');
    Route::get('/contacts/map', 'ContactController@contactMap');
        Route::get('/contacts/logs/{contact_id}', 'ContactController@getContactLogs');
    Route::get('/contacts/update-status/{id}', 'ContactController@updateStatus');
    Route::get('/contacts/stock-report/{supplier_id}', 'ContactController@getSupplierStockReport');
    Route::get('/contacts/ledger', 'ContactController@getLedger');
    Route::post('/contacts/send-ledger', 'ContactController@sendLedger');
    Route::get('/contacts/import', 'ContactController@getImportContacts')->name('contacts.import');
    Route::post('/contacts/import', 'ContactController@postImportContacts');
    Route::post('/contacts/check-contact-id', 'ContactController@checkContactId');
    Route::get('/contacts/customers', 'ContactController@getCustomers');
    Route::get('/contacts/get-referral-company', 'ContactController@getReferralCompany')->name('get-referral-company');
    Route::resource('contacts', 'ContactController');
    Route::get('view-contact/{name}','ContactController@showReferral')->name('view_contact');
    Route::get('taxonomies-ajax-index-page', 'TaxonomyController@getTaxonomyIndexPage');
    Route::resource('taxonomies', 'TaxonomyController');

     Route::get('/customer-new', 'NewContactController@index');
    Route::resource('customer-new', 'NewContactController');

    Route::get('/sellreturn', 'SellReturnController@getsellreturn');
    Route::get('/purchasereturn', 'PurchaseReturnController@getpurchasereturn');

    Route::post('/contacts/edit-customer','ContactController@bulkeditcustomer');

    Route::post('contacts/checkemail','ContactController@checkemail');
    Route::post('contacts/checkemail-edit','ContactController@checkemailedit');

    Route::resource('variation-templates', 'VariationTemplateController');

    Route::get('/products/stock-history/{id}', 'ProductController@productStockHistory')->name('product.stockhistory');
    Route::get('/products/update-stock-history', 'ProductController@updateProductStockHistory');
    Route::get('/products/update-stock-history/{id}', 'ProductController@updateProductStockHistory');
    Route::get('/products/update-stock-history-cat/{id}', 'ProductController@updateProductStockHistorycat');
Route::get('/products/update-stock-history-cat-skip/{id}/{skip}', 'ProductController@updateProductStockHistorycatskip');

    Route::get('/delete-media/{media_id}', 'ProductController@deleteMedia');
    Route::post('/products/mass-deactivate', 'ProductController@massDeactivate');
    Route::get('/products/activate/{id}', 'ProductController@activate');
    Route::get('/products/view-product-group-price/{id}', 'ProductController@viewGroupPrice');
    Route::get('/products/add-selling-prices/{id}', 'ProductController@addSellingPrices');
    Route::post('/products/save-selling-prices', 'ProductController@saveSellingPrices');
    Route::post('/products/mass-delete', 'ProductController@massDestroy');
    Route::get('/products/view/{id}', 'ProductController@view');
    Route::get('/products/list', 'ProductController@getProducts');
    Route::get('/products/sell-return-list','ProductController@getsellReturnProducts');
    Route::get('/products/listone', 'ProductController@getPosProducts');
    Route::get('/products/list-no-variation', 'ProductController@getProductsWithoutVariations');
    Route::post('/products/generate-auto-barcode','ProductController@autogeneratebarcode');
    Route::post('/products/bulk-edit', 'ProductController@bulkEdit');
    Route::post('/products/bulk-update', 'ProductController@bulkUpdate');
    Route::post('/products/bulk-update-location', 'ProductController@updateProductLocation');
    Route::get('/products/get-product-to-edit/{product_id}', 'ProductController@getProductToEdit');

    Route::post('/products/get_sub_categories', 'ProductController@getSubCategories');
    Route::get('/products/get_sub_units', 'ProductController@getSubUnits');
    Route::post('/products/product_form_part', 'ProductController@getProductVariationFormPart');
    Route::post('/products/get_product_variation_row', 'ProductController@getProductVariationRow');
    Route::post('/products/get_variation_template', 'ProductController@getVariationTemplate');
    Route::get('/products/get_variation_value_row', 'ProductController@getVariationValueRow');
    Route::post('/products/check_product_sku', 'ProductController@checkProductSku');
    Route::get('/products/quick_add', 'ProductController@quickAdd');
    Route::post('/products/save_quick_product', 'ProductController@saveQuickProduct');
    Route::get('/products/get-combo-product-entry-row', 'ProductController@getComboProductEntryRow');
    Route::post('/products/remove-main-item-image','ProductController@removeMainItemImage');

    Route::post('/products/check_catgeory','ProductController@checkcategory');

    Route::resource('products', 'ProductController');
    Route::post('/products/check-barcode','ProductController@checkbarcode');
    Route::post('/products/check-barcode-edit','ProductController@checkbarcodeedit');

    Route::post('/products/check-itemcode','ProductController@checkitemcode');
    Route::post('/products/check-itemcode-edit','ProductController@checkitemcodeedit');

    Route::post('/products/edit-bulk','ProductController@editbulk');

    Route::post('/purchases/update-status', 'PurchaseController@updateStatus');
    Route::get('/purchases/get_products', 'PurchaseController@getProducts');
    Route::get('/purchases/get_products_one', 'PurchaseController@getProductList');
    Route::get('/purchases/get_suppliers', 'PurchaseController@getSuppliers');
    Route::post('/purchases/get_purchase_entry_row', 'PurchaseController@getPurchaseEntryRow');
    Route::post('/purchases/check_ref_number', 'PurchaseController@checkRefNumber');
     Route::post('/purchases/check_ref_number_edit', 'PurchaseController@checkRefNumberEdit');
    Route::resource('purchases', 'PurchaseController')->except(['show']);

    Route::post('/purchases/update_not_for_selling','PurchaseController@updatenotforselling');
    Route::post('purchases/update_out_of_stock','PurchaseController@updateoutofstock');

    Route::get('/document/{id}/{doc}/{key}', 'PurchaseController@deleteDoc');
    Route::get('/document-first/{id}', 'PurchaseController@deleteDocFirst');

    // Purchase Order
    Route::post('/purchase-order/update-status', 'PurchaseOrderController@updateStatus');
    Route::get('/purchase-order/get_products', 'PurchaseOrderController@getProducts');
    Route::get('/purchase-order/get_supplier_products/{supplier_id}', 'PurchaseOrderController@getSupplierProducts');
    Route::get('/purchase-order/get_total_sold', 'PurchaseOrderController@get_total_sold');
    Route::get('/purchase-order/get_products_one', 'PurchaseOrderController@getProductList');
    Route::get('/purchase-order/get_suppliers', 'PurchaseOrderController@getSuppliers');
    Route::post('/purchase-order/get_purchase_entry_row', 'PurchaseOrderController@getPurchaseEntryRow');
    Route::post('/purchase-order/check_ref_number', 'PurchaseOrderController@checkRefNumber');
    Route::resource('purchase-order', 'PurchaseOrderController')->except(['show']);
    Route::get('/purchase-order/{id}/print', 'PurchaseOrderController@printInvoice')->name('purchase.printInvoice');


    Route::get('/toggle-subscription/{id}', 'SellPosController@toggleRecurringInvoices');
    Route::post('/sells/pos/get-types-of-service-details', 'SellPosController@getTypesOfServiceDetails');
    Route::get('/sells/subscriptions', 'SellPosController@listSubscriptions');
    Route::get('/sells/duplicate/{id}', 'SellController@duplicateSell');
    Route::get('/sells/duplicate/create/{id}', 'SellController@duplicateSellCreate');
    Route::get('/sells/drafts', 'SellController@getDrafts');
    Route::get('/sells/quotations', 'SellController@getQuotations');
    Route::get('/sells/draft-dt', 'SellController@getDraftDatables');

    //added for payment verified
    Route::get('/sells/payment-verified', 'SellController@getPaymentVerified');
    Route::get('/sells/payment-verified-dt', 'SellController@getPaymentVerifiedDatables');

    Route::resource('sells', 'SellController')->except(['show']);
    Route::resource('sells-new', 'SellNewController')->except(['show']);
    Route::resource('sells-amar', 'SellAmarController')->except(['show']);

    //jadoo start
    Route::get('/jadoo-products', 'ProductController@jadooCreateSave')->name('jadoocreatesave');
    Route::get('/jadoo-product-detail', 'ProductController@GetJadooProduct');
    Route::post('import-jadoo-products', 'ImportProductsController@importJadooProducts');
    Route::post('/jadoo-products/list', 'ProductController@getJadooProductsList');
    //jadoo end

    Route::get('/import-sales', 'ImportSalesController@index');
    Route::post('/import-sales/preview', 'ImportSalesController@preview');
    Route::post('/import-sales', 'ImportSalesController@import');
    Route::get('/revert-sale-import/{batch}', 'ImportSalesController@revertSaleImport');

    Route::get('/sells/pos/driverinvoice/{id}', 'SellPosController@driverinvoice');
    Route::get('/sells/pos/open_invoice/{id}', 'SellPosController@open_invoice');
    Route::get('/sells/pos/packing_slip/{id}', 'SellPosController@packing_slip');
    Route::get('/sells/pos/packing_slip_blank/{id}', 'SellPosController@packing_slip_blank');
    Route::get('/sells/pos/packing_slip_blank_without_price/{id}', 'SellPosController@packing_slip_blank_without_price');
    Route::get('/sells/pos/blank_slip/{id}', 'SellPosController@blank_slip');
    Route::get('/sells/pos/invoicegen/{id}', 'SellPosController@invoicegen');
    Route::get('/sells/pos/OBInvoice/{id}', 'SellPosController@OBInvoice');

    Route::get('/sells/pos/All_invoice', 'SellPosController@allInvoice');

    Route::get('/sells/pos/searchinvoice', 'SellPosController@search_Invoice')->name('searchinvoice');

    //export Invoice Tab start
    Route::get('/sells/pos/export_invoice', 'SellPosController@exportInvoice');
    Route::post('/sells/pos/searchinvoiceexport', 'SellPosController@search_export_Invoice')->name('searchexportinvoice');

    Route::post('/sells/pos/searchinvoicecigar', 'SellPosController@search_cigar_invoice')->name('searchcigarinvoice');
    Route::post('/sells/pos/searchinvoicetax', 'SellPosController@search_tax_invoice')->name('searchtaxinvoice');

    Route::get('/sells/pos/download_zip/{foldername}', 'SellPosController@download_invoice_zip');
    //export Invoice Tab end

    //all Invoice at a time start
    Route::get('/sells/pos/all', 'SellPosController@allInvoiceDisplay');
    Route::post('/sells/pos/searchinvoiceall', 'SellPosController@search_all_Invoice')->name('searchinvoiceall');
    Route::get('/sells/pos/all/export', 'SellPosController@export_all_Invoice')->name('exportinvoiceall');
    //all Invoice at a time end

    // Deleted Invoices report start
    Route::get('/reports/deleted-invoices-report', 'ReportController@DeletedInvoicesReport');
    Route::post('/reports/deleted-invoices-report-list', 'ReportController@getDeletedInvoicesReport');
    Route::get('/sells/pos/download-deleted-invoice/{id}', 'SellPosController@download_deleted_invoice');
    Route::get('/reports/deleted-invoices-report-data/{id}', 'ReportController@getDeletedInvoiceReportDetail');
    // Deleted Invoices report end

    // Deleted Payments report start
    Route::get('/reports/deleted-transaction-payments-report', 'ReportController@DeletedTransactionPaymentsReport');
    Route::post('/reports/deleted-transaction-payments-report-list', 'ReportController@getDeletedTransactionPaymentsReport');
    // Deleted Payments report end

    // echeck generator start
    Route::get('/reports/echeck-generator', 'SellPosController@eCheckGenerate');
    Route::post('/reports/echeck-generate-pdf', 'SellPosController@eCheckGeneratePdf');
    // echeck generator end

    // Deleted Credit Memo report start
    Route::get('/reports/deleted-creditmemo-report', 'ReportController@DeletedCreditmemoReport');
    Route::post('/reports/deleted-creditmemo-report-list', 'ReportController@getDeletedCreditmemoReport');
    // Deleted Credit Memo report end

    // Deleted Vendor Credit Memo report start
    Route::get('/reports/deleted-vendorcreditmemo-report', 'ReportController@DeletedVendorCreditmemoReport');
    Route::post('/reports/deleted-vendorcreditmemo-report-list', 'ReportController@getDeletedVendorCreditmemoReport');
    // Deleted Vendor Credit Memo report end

    // Deleted Expenses report start
    Route::get('/reports/deleted-expenses-report', 'ReportController@DeletedExpensesReport');
    Route::post('/reports/deleted-expenses-report-list', 'ReportController@getDeletedExpensesReport');
    // Deleted Expenses report end

    // Deleted Purchases report start
    Route::get('/reports/deleted-purchases-report', 'ReportController@DeletedPurchasesReport');
    Route::post('/reports/deleted-purchases-report-list', 'ReportController@getDeletedPurchasesReport');
    Route::get('/reports/deleted-purchases-report-data/{id}', 'ReportController@getDeletedPurchasesReportDetail');
    // Deleted Purchases report end

    // Deleted Purchases order report start
    Route::get('/reports/deleted-purchases-order-report', 'ReportController@DeletedPurchasesOrderReport');
    Route::post('/reports/deleted-purchases-order-report-list', 'ReportController@getDeletedPurchasesOrderReport');
    // Deleted Purchases order report end

    // Deleted Stock Adjustments report start
    Route::get('/reports/deleted-stock-adjustments-report', 'ReportController@DeletedStockAdjustmentsReport');
    Route::post('/reports/deleted-stock-adjustments-report-list', 'ReportController@getDeletedStockAdjustmentsReport');
    // Deleted Stock Adjustments report end

    // Packed Order Items report start
    Route::get('/reports/packed-order-items-report', 'ReportController@PackedOrderItemsReport');
    Route::post('/reports/packed-order-items-report-list', 'ReportController@getPackedOrderItemsReport');
    // Packed Order Items report end

    Route::get('/sells/pos/get_product_row/{variation_id}/{location_id}', 'SellPosController@getProductRow');
    Route::post('/sells/pos/get_payment_row', 'SellPosController@getPaymentRow');
    Route::post('/sells/pos/get-reward-details', 'SellPosController@getRewardDetails');
    Route::get('/sells/pos/get-recent-transactions', 'SellPosController@getRecentTransactions');
    Route::get('/sells/pos/get-product-suggestion', 'SellPosController@getProductSuggestion');
    Route::get('/sells/pos/get-featured-products/{location_id}', 'SellPosController@getFeaturedProducts');
    Route::get('/sells/pos/get-customer-address/{customer_id}', 'SellPosController@getCustomerAddress');
    Route::get('/sells/pos/calculate-customer-tax', 'SellPosController@calculateCustomerTax');
    Route::resource('pos', 'SellPosController');
    Route::get('sells/pos/product-tax','SellPosController@getProductTax');
    // product history
    Route::get('/sells/pos/get-stock-history', 'SellPosController@getStockHistory');

    Route::get('sells/pos/invoice/transaction/{id}','SellPosController@duplicate_invoice_items');

    Route::resource('roles', 'RoleController');

    Route::resource('users', 'ManageUserController');

    Route::resource('group-taxes', 'GroupTaxController');

    Route::get('/barcodes/set_default/{id}', 'BarcodeController@setDefault');
    Route::resource('barcodes', 'BarcodeController');

    //Invoice schemes..
    Route::get('/invoice-schemes/set_default/{id}', 'InvoiceSchemeController@setDefault');
    Route::resource('invoice-schemes', 'InvoiceSchemeController');

    //Print Labels
    Route::get('/labels/show', 'LabelsController@show');
    Route::get('/labels/add-product-row', 'LabelsController@addProductRow');
    Route::get('/labels/preview', 'LabelsController@preview');

    //Reports...

    //Audit Report
    Route::get('/reports/nh-vape-report', 'ReportController@nhVapeReport');
    Route::post('/reports/nh-vape-report-list', 'ReportController@getNhVapeReport');
    // NH Vape transaction report start
    Route::get('/reports/nh-vape-transaction-report', 'ReportController@nhVapeTransactionReport');
    Route::post('/reports/nh-vape-transaction-report-list', 'ReportController@getNhVapeTransactionReport');
    // NH Vape  transaction report end
    Route::get('/reports/ma-vape-report', 'ReportController@transactionReport');
    Route::post('/reports/ma-vape-report-list', 'ReportController@getTransactionReport');
    // CT VAPE  transaction report start
    Route::get('/reports/ct-vape-transaction-report', 'ReportController@ctVapeTransactionReport');
    Route::post('/reports/ct-vape-transaction-report-list', 'ReportController@getCtVapeTransactionReport');
    // CT VAPE  transaction report end
    // PA VAPE  transaction report start
    Route::get('/reports/pa-vape-transaction-report', 'ReportController@paVapeTransactionReport');
    Route::post('/reports/pa-vape-transaction-report-list', 'ReportController@getPaVapeTransactionReport');
    // PA VAPE  transaction report end
    Route::get('/reports/audit', 'ReportController@getAuditReport')->name('report.audit');
    Route::get('/api/reports/audit/{type?}','ReportController@getAuditReportRaw')->name('api.reports.audit');
    Route::get('/audit/module_history/{contact_id}/{module_name}', 'AuditController@getModuleHistory');

      Route::get('/reports/item-inventory', 'ReportController@itemInventory');
    Route::post('/reports/item-inventory-list', 'ReportController@getitemInventory');
    Route::get('/reports/on-hand-reports', 'ReportController@productOnhandList');
    Route::post('/reports/on-hand-list', 'ReportController@getproductOnhand');

     Route::get('/StoreLat_Long', 'ReportController@ShowLat_LongPage');
    Route::post('/lang','ReportController@Lat_LongStore');


    Route::get('/reports/purchase-report', 'ReportController@purchaseReport');
    Route::get('/reports/sale-report', 'ReportController@saleReport');
    Route::get('/reports/service-staff-report', 'ReportController@getServiceStaffReport');
    Route::get('/reports/service-staff-line-orders', 'ReportController@serviceStaffLineOrders');
    Route::get('/reports/table-report', 'ReportController@getTableReport');
    Route::get('/reports/profit-loss', 'ReportController@getProfitLoss');
    Route::get('/reports/get-opening-stock', 'ReportController@getOpeningStock');
    Route::get('/reports/purchase-sell', 'ReportController@getPurchaseSell');
    Route::get('/reports/customer-supplier', 'ReportController@getCustomerSuppliers');
    Route::get('/reports/stock-report', 'ReportController@getStockReport');
    Route::get('/reports/stock-report/new', 'ReportController@getStockReportNew');
    Route::get('/reports/stock-details', 'ReportController@getStockDetails');
    Route::get('/reports/tax-report', 'ReportController@getTaxReport');
    Route::get('/reports/tax-details', 'ReportController@getTaxDetails');
    Route::get('/reports/trending-products', 'ReportController@getTrendingProducts');
    Route::get('/reports/expense-report', 'ReportController@getExpenseReport');
    Route::get('/reports/stock-adjustment-report', 'ReportController@getStockAdjustmentReport');
    Route::get('/reports/out-of-stock-report', 'ReportController@getOutOfStockReport');
    Route::get('/reports/register-report', 'ReportController@getRegisterReport');
    Route::get('/reports/sales-representative-report', 'ReportController@getSalesRepresentativeReport');
    Route::get('/reports/sales-representative-total-expense', 'ReportController@getSalesRepresentativeTotalExpense');
    Route::get('/reports/sales-representative-total-sell', 'ReportController@getSalesRepresentativeTotalSell');
    Route::get('/reports/sales-representative-total-commission', 'ReportController@getSalesRepresentativeTotalCommission');
    Route::get('/reports/stock-expiry', 'ReportController@getStockExpiryReport');
    Route::get('/reports/stock-expiry-edit-modal/{purchase_line_id}', 'ReportController@getStockExpiryReportEditModal');
    Route::post('/reports/stock-expiry-update', 'ReportController@updateStockExpiryReport')->name('updateStockExpiryReport');
    Route::get('/reports/customer-group', 'ReportController@getCustomerGroup');
    Route::get('/reports/product-purchase-report', 'ReportController@getproductPurchaseReport');
    Route::get('/reports/product-sell-report', 'ReportController@getproductSellReport');
    Route::get('/reports/product-sell-report-with-purchase', 'ReportController@getproductSellReportWithPurchase');
    Route::get('/reports/product-sell-grouped-report', 'ReportController@getproductSellGroupedReport');
    Route::get('/reports/lot-report', 'ReportController@getLotReport');
    Route::get('/reports/purchase-payment-report', 'ReportController@purchasePaymentReport');
    Route::get('/reports/sales-tree-report', 'ReportController@getsaleTreeReport');
    Route::get('/reports/weekend-report', 'ReportController@getWeekendReport');
    Route::get('/reports/weekend-report-forpopup', 'ReportController@getWeekendReportforPopup');
    Route::post('/reports/brand-tree-report', 'ReportController@getsaleTreeBrandReport');
    Route::post('/reports/product-tree-report', 'ReportController@getsaleTreeProductReport');
    Route::get('/reports/sell-payment-report', 'ReportController@sellPaymentReport');
    Route::get('/reports/product-stock-details', 'ReportController@productStockDetails');
    Route::get('/reports/adjust-product-stock', 'ReportController@adjustProductStock');
    Route::get('/reports/get-profit/{by?}', 'ReportController@getProfit');
    Route::get('/reports/items-report', 'ReportController@itemsReport');
    Route::get('/reports/get-stock-value', 'ReportController@getStockValue');
    Route::get('/reports/balance-report', 'ReportController@getBalanceReport');
    Route::get('/reports/category-wise-sale-report','ReportController@getCategorywisesalereport');
    Route::get('/reports/testcategorydata','ReportController@getAllcategories');
    Route::get('/reports/brand-wise-sale-report','ReportController@getBrandwisesalereport');
    Route::get('/reports/brand-wise-sale-report-new','ReportController@getBrandwisesalereportNew');
    Route::get('/reports/product-wise-sale-report', 'ReportController@getProductwisesalereport');
    Route::get('/reports/expired-document-and-note-report', 'ReportController@getExpiredDocumentAndNoteReport');
    Route::get('/reports/stale-customer', 'ReportController@getStaleCustomer');
    Route::get('/reports/followup-stale-customer', 'ReportController@FollowUpgetStaleCustomer');
    Route::get('/reports/store-gp', 'SellNewController@gp_report');
    Route::get('/reports/inactive-items-report', 'ReportController@inactiveItemsReport');
    Route::get('/reports/active-items-report', 'ReportController@activeItemsReport');

    Route::get('/reports/AR-report', 'ReportController@GetARreports');
    Route::get('/reports/customer-AR-report/{id}', 'ReportController@GetARcustomerReport');
    Route::get('/reports/download-AR-excel', 'ReportController@downloadARExcel');



      // product_analytics report start
    Route::get('/reports/product-analytics/{id?}', 'ReportController@ProductAnalytics');
    Route::get('/reports/margin-chart/{id?}', 'ReportController@GetMarginChart');
    Route::get('/reports/statechart/{id?}', 'ReportController@StateChart');
    Route::get('/reports/ranktable/', 'ReportController@RankTable');

    //Category Analytics
    Route::get('/reports/category-analytics/{id?}' , 'ReportController@CategoryAnalytics');
    Route::get('/reports/category-margin-chart/{id?}', 'ReportController@CatMarginChart');
    Route::get('/reports/state_tier_chart/{id?}', 'ReportController@State_Tier_Chart');
    Route::get('/reports/cat-ranktable/{id?}', 'ReportController@CatWiseRankTable');
    Route::get('/reports/category-ranktable', 'ReportController@CategoryRankTable');

     // Customer Analytics
    Route::get('/reports/customer-analytics/{id?}', 'ReportController@CustomerAnalytics');
    Route::get('/reports/customer-fav-products/{id?}', 'ReportController@TopFavoriteProduct');
    Route::get('/reports/customer-fav-category/{id?}', 'ReportController@FavoriteCategory');
    Route::get('/reports/customerdata', 'ReportController@CustomerData');
    Route::get('/reports/customersell/{id?}', 'ReportController@CustomerSell');

    // sale by account start
    Route::get('/reports/sales-by-account-representative', 'ReportController@getSalesAccountRepresentativeReport');
    Route::get('/reports/sales-by-account-representative-show', 'ReportController@getSalesAccountRepresentativeShowReport');
    Route::get('/reports/credit-memo-sales-by-account-representative-show', 'ReportController@getCreditMemoAccountRepresentativeShowReport');
    // sale by account end

    // Payables and receivables report start
    Route::get('/reports/payables-receivables-report', 'ReportController@getPayablesReceivablesReport');
    Route::get('/reports/payables-show', 'ReportController@getPayablesShowReport');
    Route::get('/reports/receivables-show', 'ReportController@getReceivablesShowReport');
    // Payables and receivables report end

    // new balance report
    Route::get('/reports/BalanceReport', 'ReportController@NewBalanceReport');
    Route::post('/reports/searchBalanceReport', 'ReportController@searchBalanceReport')->name('balance.filter');

    Route::get('business-location/activate-deactivate/{location_id}', 'BusinessLocationController@activateDeactivateLocation');

    // Daily Report Start
    Route::get('/reports/daily-items-report', 'ReportController@getDaliyItemsReport');
    Route::get('/reports/purchased-items-list', 'ReportController@getItemsPurchasedForToday');
    Route::get('/reports/added-items-list', 'ReportController@getItemsAddedForToday');
    // Daily report End

    // Daily Report test Start
        Route::get('/reports/purchased-items-list-vthr', 'ReportController@getItemsPurchasedForTodayVthr');
    // Daily report test End


    // new report
    Route::get('/reports/product-selling-report', 'ReportController@productSellingReport');

    Route::get('/reports/all-tax-report', 'ReportController@getAllTaxReport');

    //Business Location Settings...
    Route::prefix('business-location/{location_id}')->name('location.')->group(function () {
        Route::get('settings', 'LocationSettingsController@index')->name('settings');
        Route::post('settings', 'LocationSettingsController@updateSettings')->name('settings_update');
    });

    // Jull sell Report
    Route::get('/reports/Juul-sell-report', 'ReportController@getjuulSellReport')->name('juul.show');
    Route::post('/search/Juul-sell-report', 'ReportController@searchjuulSellReport')->name('juul.filter');

    //Non Sell Report
    Route::get('/reports/Non-Juul-Msa-report', 'ReportController@getNonjuulSellReport')->name('Non_juul.show');
    Route::post('/search/Non-Juul-Msa-report', 'ReportController@searchNonjuulSellReport')->name('Non_juul.filter');

    //Collected Payment Report
    Route::get('/reports/collected-payment-report', 'ReportController@getCollectedPaymentReport');
    // Route::get('/reports/sales-by-account-representative', 'ReportController@getSalesAccountRepresentativeReport');
    Route::get('/reports/collected-payment-by-customers-show', 'ReportController@getCollectedPaymentReportbyCustomers');
    Route::get('/reports/collected-payment-by-suppliers-show', 'ReportController@getCollectedPaymentReportbySuppliers');


    //Business Locations...
    Route::post('business-location/check-location-id', 'BusinessLocationController@checkLocationId');
    Route::resource('business-location', 'BusinessLocationController');

    //Invoice layouts..
    Route::resource('invoice-layouts', 'InvoiceLayoutController');

    //Expense Categories...
    Route::resource('expense-categories', 'ExpenseCategoryController');

    //Expenses...
    Route::resource('expenses', 'ExpenseController');

    //Transaction payments...
    // Route::get('/payments/opening-balance/{contact_id}', 'TransactionPaymentController@getOpeningBalancePayments');
    Route::get('/payments/show-child-payments/{payment_id}', 'TransactionPaymentController@showChildPayments');
    Route::get('/payments/view-payment/{payment_id}', 'TransactionPaymentController@viewPayment');
    Route::get('/payments/add_payment/{transaction_id}', 'TransactionPaymentController@addPayment');
    Route::get('/payments/pay-contact-due/{contact_id}', 'TransactionPaymentController@getPayContactDue');
    Route::post('/payments/pay-contact-due', 'TransactionPaymentController@postPayContactDue');

    //Reconcile payments
    Route::get('/payments/get-reconcile-payments/{contact_id}', 'TransactionPaymentController@getReconcilePayments');
    Route::post('/payments/pay-contact-due', 'TransactionPaymentController@postPayContactDue');

    Route::resource('payments', 'TransactionPaymentController');

    //Printers...
    Route::resource('printers', 'PrinterController');

    Route::get('/stock-adjustments/remove-expired-stock/{purchase_line_id}', 'StockAdjustmentController@removeExpiredStock');
    Route::post('/stock-adjustments/get_product_row', 'StockAdjustmentController@getProductRow');
    Route::resource('stock-adjustments', 'StockAdjustmentController');

    Route::post('/stock-adjustments/update_not_for_selling','StockAdjustmentController@updatenotforselling');
    Route::post('stock-adjustments/update_out_of_stock','StockAdjustmentController@updateoutofstock');

    Route::get('/cash-register/register-details', 'CashRegisterController@getRegisterDetails');
    Route::get('/cash-register/close-register/{id?}', 'CashRegisterController@getCloseRegister');
    Route::post('/cash-register/close-register', 'CashRegisterController@postCloseRegister');
    Route::resource('cash-register', 'CashRegisterController');

    //Import products
    //Route::get('/import-products', 'ImportProductsController@index');
    //Route::post('/import-products/store', 'ImportProductsController@store');
    Route::resource('import-products', 'ImportProductsController');

    //Sales Commission Agent
    Route::resource('sales-commission-agents', 'SalesCommissionAgentController');

    //Stock Transfer
    Route::get('stock-transfers/print/{id}', 'StockTransferController@printInvoice');
    Route::post('stock-transfers/update-status/{id}', 'StockTransferController@updateStatus');
    Route::resource('stock-transfers', 'StockTransferController');

    Route::get('/opening-stock/add/{product_id}', 'OpeningStockController@add');
    Route::post('/opening-stock/save', 'OpeningStockController@save');

    //Customer Groups
    Route::resource('customer-group', 'CustomerGroupController');

    //Import opening stock
    Route::get('/import-opening-stock', 'ImportOpeningStockController@index');
    Route::post('/import-opening-stock/store', 'ImportOpeningStockController@store');

    //Sell return
    Route::resource('sell-return', 'SellReturnController');
    Route::get('sell-return/get-product-row', 'SellReturnController@getProductRow');
    Route::get('/sell-return/customer/invoice', 'SellReturnController@getCustomerInvoice');
    Route::get('sells-return', 'SellReturnController@sellReturn');
    Route::get('/sell-return/print/{id}', 'SellReturnController@printInvoice');
    Route::get('/sell-return/new-print/{id}', 'SellReturnController@printInvoice2');
    Route::get('/sell-return/add/{id}', 'SellReturnController@add');
    Route::get('/sell-return/get/item/forReturn', 'SellReturnController@getItemForReturn');
    Route::get('/sell-return/get/itemlist', 'SellReturnController@getItemForReturnList');

    //Backup
    Route::get('backup/download/{file_name}', 'BackUpController@download');
    Route::get('backup/delete/{file_name}', 'BackUpController@delete');
    Route::resource('backup', 'BackUpController', ['only' => [
        'index', 'create', 'store'
    ]]);

    Route::get('selling-price-group/activate-deactivate/{id}', 'SellingPriceGroupController@activateDeactivate');
    Route::get('export-selling-price-group', 'SellingPriceGroupController@export');
    Route::post('import-selling-price-group', 'SellingPriceGroupController@import');

    Route::resource('selling-price-group', 'SellingPriceGroupController');

    Route::resource('notification-templates', 'NotificationTemplateController')->only(['index', 'store']);
    Route::get('notification/get-template/{transaction_id}/{template_for}', 'NotificationController@getTemplate');
    Route::post('notification/send', 'NotificationController@send');

    // SMS Reminders from new balance report
    Route::post('notification/get-bulk-sms','NotificationController@getBulkSmsTemplate');

    //added  by developer 1
    Route::get('sendemailforinvoice/{id}', 'SellPosController@sendEmailforInvoice');

    Route::post('notification/get-bulk-notification','NotificationController@getBulkNotificationTemplate');
    Route::post('notification/get-bulk-email-notification','NotificationController@getBulkEmailNotificationTemplate');

    Route::post('/purchase-return/update', 'CombinedPurchaseReturnController@update');
    Route::get('/purchase-return/edit/{id}', 'CombinedPurchaseReturnController@edit');
    Route::post('/purchase-return/save', 'CombinedPurchaseReturnController@save');
    Route::post('/purchase-return/get_product_row', 'CombinedPurchaseReturnController@getProductRow');
    Route::get('/purchase-return/create', 'CombinedPurchaseReturnController@create');
    Route::get('/purchase-return/add-consigment/{id}', 'CombinedPurchaseReturnController@returnConsigment');
    Route::get('/purchase-return/add/{id}', 'PurchaseReturnController@add');
    Route::resource('/purchase-return', 'PurchaseReturnController', ['except' => ['create']]);

    Route::get('/discount/activate/{id}', 'DiscountController@activate');
    Route::post('/discount/mass-deactivate', 'DiscountController@massDeactivate');
    Route::resource('discount', 'DiscountController');

    Route::group(['prefix' => 'account'], function () {
        Route::resource('/account', 'AccountController');
        Route::get('/fund-transfer/{id}', 'AccountController@getFundTransfer');
        Route::post('/fund-transfer', 'AccountController@postFundTransfer');
        Route::get('/deposit/{id}', 'AccountController@getDeposit');
        Route::post('/deposit', 'AccountController@postDeposit');
        Route::get('/close/{id}', 'AccountController@close');
        Route::get('/activate/{id}', 'AccountController@activate');
        Route::get('/delete-account-transaction/{id}', 'AccountController@destroyAccountTransaction');
        Route::get('/get-account-balance/{id}', 'AccountController@getAccountBalance');
        Route::get('/balance-sheet', 'AccountReportsController@balanceSheet');
        Route::get('/trial-balance', 'AccountReportsController@trialBalance');
        Route::get('/payment-account-report', 'AccountReportsController@paymentAccountReport');
        Route::get('/link-account/{id}', 'AccountReportsController@getLinkAccount');
        Route::post('/link-account', 'AccountReportsController@postLinkAccount');
        Route::get('/cash-flow', 'AccountController@cashFlow');
    });

    Route::resource('account-types', 'AccountTypeController');

    //Restaurant module
    Route::group(['prefix' => 'modules'], function () {
        Route::resource('tables', 'Restaurant\TableController');
        Route::resource('modifiers', 'Restaurant\ModifierSetsController');

        //Map modifier to products
        Route::get('/product-modifiers/{id}/edit', 'Restaurant\ProductModifierSetController@edit');
        Route::post('/product-modifiers/{id}/update', 'Restaurant\ProductModifierSetController@update');
        Route::get('/product-modifiers/product-row/{product_id}', 'Restaurant\ProductModifierSetController@product_row');

        Route::get('/add-selected-modifiers', 'Restaurant\ProductModifierSetController@add_selected_modifiers');

        Route::get('/kitchen', 'Restaurant\KitchenController@index');
        Route::get('/kitchen/picking/order/{id}', 'Restaurant\KitchenController@startPicking');
        Route::get('/kitchen/pick/product/{id}', 'Restaurant\KitchenController@pickProduct');
        Route::post('/kitchen/selected/pick/product', 'Restaurant\KitchenController@selectedPickProduct');
        Route::get('/kitchen/product/out-of-stock/{id}', 'Restaurant\KitchenController@outOfStock');
        Route::get('/kitchen/product/incorrect-location/{id}', 'Restaurant\KitchenController@incorrectLocation');
        Route::get('/kitchen/order/save-and-hold/{id}', 'Restaurant\KitchenController@saveAndHold');
        Route::get('/kitchen/order/save-and-hold-packing/{id}', 'Restaurant\KitchenController@saveAndHoldPacking');
        Route::get('/kitchen/order/finalize/{id}', 'Restaurant\KitchenController@finalizePickingOrder');
        Route::get('/kitchen/order/finalize/packing/{id}', 'Restaurant\KitchenController@finalizePackingOrder');
        Route::get('/kitchen/order/cancel/{id}', 'Restaurant\KitchenController@cancelPikingOrder');
        Route::get('/kitchen/packing/order/cancel/{id}', 'Restaurant\KitchenController@cancelPakingOrder');
        Route::get('/kitchen/order/queue/{id}', 'Restaurant\KitchenController@orderQueue');
        Route::post('/kitchen/product/quantity/update', 'Restaurant\KitchenController@updateProductQty');
        Route::post('/kitchen/product/quantity/edit', 'Restaurant\KitchenController@editProductQty');
        Route::get('/kitchen/packing/order/{id}', 'Restaurant\KitchenController@startPacking');
        Route::get('/kitchen/picking/undo/{id}', 'Restaurant\KitchenController@undoPicking');
        Route::get('/kitchen/packing/undo/{id}', 'Restaurant\KitchenController@undoPacking');
        Route::get('/kitchen/outofstock/undo/{id}', 'Restaurant\KitchenController@undoOutofStock');
        Route::get('/kitchen/packing/product', 'Restaurant\KitchenController@packProduct');
        // added by developer1 for search of pack product split
        Route::get('/kitchen/packing/product/identify', 'Restaurant\KitchenController@packProductIdentify');

        Route::get('/kitchen/mark-as-cooked/{id}', 'Restaurant\KitchenController@markAsCooked');
        Route::post('/refresh-orders-list', 'Restaurant\KitchenController@refreshOrdersList');
        Route::post('/refresh-line-orders-list', 'Restaurant\KitchenController@refreshLineOrdersList');

        Route::get('/orders', 'Restaurant\OrderController@index');

        Route::get('/orders/mark-as-served/{id}', 'Restaurant\OrderController@markAsServed');
        Route::get('/data/get-pos-details', 'Restaurant\DataController@getPosDetails');
        Route::get('/orders/mark-line-order-as-served/{id}', 'Restaurant\OrderController@markLineOrderAsServed');
    });
    Route::group(['prefix' => 'modules'], function () {
        Route::get('/api/kitchen/pick/product/{id}', 'Restaurant\KitchenApiController@pickProduct');
        Route::get('/api/kitchen/pick/order/{id}', 'Restaurant\KitchenApiController@getOrderData');
        Route::get('/api/kitchen/product/out-of-stock/{id}', 'Restaurant\KitchenApiController@outOfStock');
        Route::post('/api/kitchen/selected/pick/product', 'Restaurant\KitchenApiController@selectedPickProduct');
        Route::get('/api/kitchen/product/incorrect-location/{id}', 'Restaurant\KitchenApiController@incorrectLocation');
        Route::get('/api/kitchen/order/save-and-hold/{id}', 'Restaurant\KitchenApiController@saveAndHold');
        Route::get('/api/kitchen/order/save-and-hold-packing/{id}', 'Restaurant\KitchenApiController@saveAndHoldPacking');
        Route::get('/api/kitchen/order/finalize/{id}', 'Restaurant\KitchenApiController@finalizePickingOrder');
        Route::get('/api/kitchen/order/finalize/packing/{id}', 'Restaurant\KitchenApiController@finalizePackingOrder');
        Route::get('/api/kitchen/order/cancel/{id}', 'Restaurant\KitchenApiController@cancelPikingOrder');
        Route::get('/api/kitchen/packing/order/cancel/{id}', 'Restaurant\KitchenApiController@cancelPakingOrder');
        Route::get('/api/kitchen/order/queue/{id}', 'Restaurant\KitchenApiController@orderQueue');
        Route::post('/api/kitchen/product/quantity/update', 'Restaurant\KitchenApiController@updateProductQty');
        Route::post('/api/kitchen/product/quantity/edit', 'Restaurant\KitchenApiController@editProductQty');
        Route::get('/api/kitchen/packing/order/{id}', 'Restaurant\KitchenApiController@startPacking');
        Route::get('/api/kitchen/picking/undo/{id}', 'Restaurant\KitchenApiController@undoPicking');
        Route::get('/api/kitchen/packing/undo/{id}', 'Restaurant\KitchenApiController@undoPacking');
        Route::get('/api/kitchen/outofstock/undo/{id}', 'Restaurant\KitchenApiController@undoOutofStock');
        Route::get('/api/kitchen/packing/product', 'Restaurant\KitchenApiController@packProduct');
    });
    Route::get('bookings/get-todays-bookings', 'Restaurant\BookingController@getTodaysBookings');
    Route::resource('bookings', 'Restaurant\BookingController');

    Route::resource('types-of-service', 'TypesOfServiceController');
    Route::get('sells/edit-shipping/{id}', 'SellController@editShipping');
    Route::put('sells/update-shipping/{id}', 'SellController@updateShipping');
    Route::get('shipments', 'SellController@shipments');

    Route::post('upload-module', 'Install\ModulesController@uploadModule');
    Route::resource('manage-modules', 'Install\ModulesController')
        ->only(['index', 'destroy', 'update']);
    Route::resource('warranties', 'WarrantyController');

    Route::resource('dashboard-configurator', 'DashboardConfiguratorController')
    ->only(['edit', 'update']);

    //common controller for document & note
    Route::get('get-document-note-page', 'DocumentAndNoteController@getDocAndNoteIndexPage');
    Route::post('post-document-upload', 'DocumentAndNoteController@postMedia');
    Route::resource('note-documents', 'DocumentAndNoteController');
    Route::get('/note-documents/index/reports', 'DocumentAndNoteController@reports')->name('index-reports');
});


Route::middleware(['EcomApi'])->prefix('api/ecom')->group(function () {
    Route::get('products/{id?}', 'ProductController@getProductsApi');
    Route::get('categories', 'CategoryController@getCategoriesApi');
    Route::get('brands', 'BrandController@getBrandsApi');
    Route::post('customers', 'ContactController@postCustomersApi');
    Route::get('settings', 'BusinessController@getEcomSettings');
    Route::get('variations', 'ProductController@getVariationsApi');
    Route::post('orders', 'SellPosController@placeOrdersApi');
});

//common route
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
});

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone'])->group(function () {
    Route::get('/load-more-notifications', 'HomeController@loadMoreNotifications');
    Route::get('/get-total-unread', 'HomeController@getTotalUnreadNotifications');
    Route::get('/purchases/print/{id}', 'PurchaseController@printInvoice');
     Route::get('/getPurchasesData/{id}', 'PurchaseController@getPurchaseData');
    Route::get('/purchases/{id}', 'PurchaseController@show');

    // Purchase Order
    Route::get('/purchase-order/print/{id}', 'PurchaseOrderController@printInvoice');
    Route::get('/purchases-order/{id}', 'PurchaseOrderController@show');

    Route::get('/sells/{id}', 'SellController@show');
    Route::get('/sells/{transaction_id}/print', 'SellPosController@printInvoice')->name('sell.printInvoice');
    Route::get('/sells/quotation/{transaction_id}/print', 'SellPosController@printQuotation')->name('sell.printQuotation');

    Route::get('/sells/invoice-url/{id}', 'SellPosController@showInvoiceUrl');
    Route::get('/show-notification/{id}', 'HomeController@showNotification');
});

Route::get('/sales/print/{id}', 'SellController@printCreditMemoInvoice');
Route::get('/sales/getcreditmemo', 'SellController@customerCreditMemo');
Route::get('/sales/{email}', 'SellController@customerSales');
Route::get('/sales/invoice/{id}', 'SellController@customerSalesInvoice')->name('sales.invoice');

Route::get('/clear-cache', function() {
    Artisan::call('config:cache');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cache is cleared";
})->name('clear.cache');


//Generate Customers Balancs Report
// Route::get('/customersBalanaceReport','ReportController@CustoersBalanceReports');