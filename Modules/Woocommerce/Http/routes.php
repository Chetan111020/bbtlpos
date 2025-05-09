<?php

Route::post(
    '/webhook/order-created/{business_id}',
    'Modules\Woocommerce\Http\Controllers\WoocommerceWebhookController@orderCreated'
);
Route::post(
    '/webhook/order-updated/{business_id}',
    'Modules\Woocommerce\Http\Controllers\WoocommerceWebhookController@orderUpdated'
);
Route::post(
    '/webhook/order-deleted/{business_id}',
    'Modules\Woocommerce\Http\Controllers\WoocommerceWebhookController@orderDeleted'
);
Route::post(
    '/webhook/order-restored/{business_id}',
    'Modules\Woocommerce\Http\Controllers\WoocommerceWebhookController@orderRestored'
);

Route::group(['middleware' => ['web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'], 'prefix' => 'woocommerce', 'namespace' => 'Modules\Woocommerce\Http\Controllers'], function () {
    Route::get('/install', 'InstallController@index');
    Route::get('/install/update', 'InstallController@update');
    Route::get('/install/uninstall', 'InstallController@uninstall');

    Route::get('/', 'WoocommerceController@index');
    Route::get('/api-settings', 'WoocommerceController@apiSettings');
    Route::post('/update-api-settings', 'WoocommerceController@updateSettings');

    Route::get('/sync-customer/{id}', 'WoocommerceController@syncCustomer');
    Route::get('/customer/reset/password/{id}', 'WoocommerceController@customerResetPassword');
    Route::get('/customer/check/{id}', 'WoocommerceController@checkWooCustomerExistOrNot');

    Route::get('/sync-brands', 'WoocommerceController@syncBrands');
    Route::get('/sync-categories', 'WoocommerceController@syncCategories');
    Route::get('/sync-products', 'WoocommerceController@syncProducts');
    Route::post('/sync-select-products', 'WoocommerceController@syncProductsSelect');
    Route::get('/sync-delete-products', 'WoocommerceController@syncProductsDelete');
    Route::get('/sync-log', 'WoocommerceController@getSyncLog');
    Route::get('/sync-orders', 'WoocommerceController@syncOrders');
    Route::post('/sync-select-orders', 'WoocommerceController@syncOrdersSelect');

    Route::post('/map-taxrates', 'WoocommerceController@mapTaxRates');
    Route::get('/view-sync-log', 'WoocommerceController@viewSyncLog');
    Route::get('/get-log-details/{id}', 'WoocommerceController@getLogDetails');
    Route::get('/reset-brands', 'WoocommerceController@resetBrands');
    Route::get('/reset-categories', 'WoocommerceController@resetCategories');
    Route::get('/reset-products', 'WoocommerceController@resetProducts');
});
