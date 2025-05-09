<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Modules\SmartCRM\Http\Controllers\FollowUpController;
use Modules\SmartCRM\Http\Controllers\LeadsController;

Route::prefix('smart-crm')->name('smartcrm.')->middleware([
    'setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'
])->group(function() {
    Route::get('/', 'SmartCRMController@index');
    Route::get('/fetch-chart-data', 'SmartCRMController@fetchChartDataRaw');
    Route::get('/fetch-bar-chart-data', 'SmartCRMController@fetchBarChartData');
    Route::get('/fetch-leaderboard-data', 'SmartCRMController@fetchLeaderboardChartData');
    Route::get('/fetch-heatmap-data', 'SmartCRMController@fetchDataForHeatMap');
    Route::prefix('follow-up')->name('followup.')->group(function(){
        Route::get('/', [FollowUpController::class, 'index'])->name('index');
        Route::get('/{id}', [FollowUpController::class, 'show'])->name('show');
        Route::get('/queue/{id}', [FollowUpController::class, 'startQueue'])->name('queue');

        Route::post('/store', [FollowUpController::class, 'store'])->name('store');
        Route::get('/view/{id}',[FollowUpController::class, 'view'])->name('view');
        Route::get('/edit/{id}', [FollowUpController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [FollowUpController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [FollowUpController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('leads')->name('lead.')->group(function(){
        Route::get('/',[LeadsController::class, 'index'])->name('Leads');
        Route::get('/create',[LeadsController::class, 'create'])->name('LeadsCreate');
        Route::post('/StoreLead',[LeadsController::class, 'store'])->name('LeadsStore');
        Route::get('/view/{id}', [LeadsController::class, 'show'])->name('ShowLeads');
        Route::get('/edit/{id}', [LeadsController::class, 'edit'])->name('EditLeads');
        Route::post('/update/{id}', [LeadsController::class, 'update'])->name('UpdateLeads');
        Route::delete('/delete/{id}', [LeadsController::class, 'destroy'])->name('DeleteLeads');
    });
});