<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DmatkaGameController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManualPaymentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VendorActivityController;
use App\Http\Controllers\TraddingController;
use App\Http\Controllers\Api\SattaController;
use App\Http\Middleware\CheckUserSession;
use App\Http\Controllers\BankDetailsController;

// Error Route
Route::get('/error', function () {
    abort(500);
});


Route::get('/', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::get('/view_salary', [SettingsController::class, 'view_salary']);



Route::middleware([CheckUserSession::class])->group(function () {
	
// ======================== AuthController ========================
Route::get('dashboard', [AuthController::class, 'dashboard'])->name('admin.dashboard');

Route::get('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');


// ======================== UserController ========================
Route::get('/users/{status}', [UserController::class, 'users'])->name('users');
Route::get('/vendor/{status}', [UserController::class, 'vendor'])->name('vendor');
Route::any('/users_activity/{userid}', [UserController::class, 'users_activity'])->name('users_activity');
Route::post('/toggle_status', [UserController::class, 'toggle_status'])->name('toggle_status');
Route::post('/wallte_operation', [UserController::class, 'wallte_operation'])->name('wallte_operation');


// ======================== DmatkaGameController ========================
Route::any('/betlive_result', [DmatkaGameController::class, 'betlive_result'])->name('betlive_result');
Route::post('/live_modify_result', [DmatkaGameController::class, 'live_modify_result'])->name('live_modify_result');
Route::any('/dmtka_betlog/{game_type}', [DmatkaGameController::class, 'dmtka_betlog'])->name('dmtka_betlog');


// ======================== ManualPaymentController ========================
Route::any('/m_deposite/{status}', [ManualPaymentController::class, 'm_deposite'])->name('m_deposite');
Route::get('/admin/update-status/{id}/{status}', [ManualPaymentController::class, 'updateStatus'])->name('update.transaction.status');
Route::any('/m_withdraw/{status}', [ManualPaymentController::class, 'm_withdraw'])->name('m_withdraw');
Route::get('/admin/m_withdraw/{id}/{status}', [ManualPaymentController::class, 'updatewithdraw'])->name('update.updatewithdraw');


// ======================== SettingsController ========================
Route::get('/Transaction_limit', [SettingsController::class, 'Transaction_limit'])->name('Transaction_limit');
Route::post('/admin/update-site-setting', [SettingsController::class, 'updateSiteSetting'])->name('update_site_setting');

Route::get('/banner', [SettingsController::class, 'banner'])->name('banner');
Route::post('admin/banner/update', [SettingsController::class, 'updateBanner'])->name('admin.banner.update');
Route::post('admin/banner/add', [SettingsController::class, 'addBanner'])->name('admin.banner.add');
Route::delete('admin/banner/delete/{id}', [SettingsController::class, 'deleteBanner'])->name('admin.banner.delete');

Route::get('getfeedback', [SettingsController::class, 'getfeedback'])->name('getfeedback');
Route::post('/admin/feedback/reply', [SettingsController::class, 'reply'])->name('admin.feedback.reply');
Route::delete('/admin/feedback/delete/{id}', [SettingsController::class, 'delete'])->name('admin.feedback.delete');

Route::get('/Support_Channels', [SettingsController::class, 'Support_Channels'])->name('Support_Channels');
Route::post('/admin/support/update', [SettingsController::class, 'updateSupport'])->name('admin.support.update');

Route::get('/viewUniqueNotification', [SettingsController::class, 'viewUniqueNotification'])->name('viewUniqueNotification');
Route::post('/admin/notification/update', [SettingsController::class, 'updateNotification'])->name('admin.notification.update');


// ======================== VendorActivityController ========================
Route::get('createvendor', [VendorActivityController::class, 'createvendor'])->name('createvendor');
Route::post('addvendor', [VendorActivityController::class, 'addvendor'])->name('addvendor');
Route::get('updatevendor/{id}', [VendorActivityController::class, 'updatevendor'])->name('updatevendor');
Route::put('updatevendor/{id}', [VendorActivityController::class, 'updatevendorPost'])->name('updatevendor.post');

Route::get('userToVendorPayment/{status}', [VendorActivityController::class, 'userToVendorPayment'])->name('userToVendorPayment');
Route::get('/admin/{id}/{status}', [VendorActivityController::class, 'approvePayment'])->name('update.updatewithdraws');
Route::post('/admin/payment/reject', [VendorActivityController::class, 'rejectPayment'])->name('update.rejectWithdraw');


// ======================== TraddingController ========================


Route::any('/tradding_betlog/{game_type}', [TraddingController::class, 'tradding_betlog'])->name('tradding_betlog');

Route::get('/result/edit/{id}', [TraddingController::class, 'edit'])->name('result.edit');
Route::put('/result/update/{id}', [TraddingController::class, 'update'])->name('result.update');
Route::get('/tradding_result_cron/{id}', [TraddingController::class,'tradding_result_cron'])->name('tradding_result_cron');
Route::post('/result_announce', [TraddingController::class, 'result_announce'])->name('result_announce');
Route::any('/add_result', [TraddingController::class, 'add_result'])->name('add_result');

// ======================== SattaController ========================
Route::post('/manual_result', [SattaController::class, 'manual_result'])->name('manual_result');


// ======================== BankDetailsController ========================
    Route::get('/find_bank', [BankDetailsController::class, 'find_bank'])->name('find_bank');
	Route::post('/update_bank_detail', [BankDetailsController::class, 'update_bank_detail'])->name('update_bank_detail');	
	
	
 });
