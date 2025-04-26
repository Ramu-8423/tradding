
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\GameApiController;
use App\Http\Controllers\Api\PayingController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\Api\SattaController;
use App\Http\Controllers\Api\FreeSpeenController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\AgencyPromotionController;
use App\Http\Controllers\Api\VendorActivityController;
use App\Http\Controllers\Api\TraddingController;
use App\Http\Controllers\Api\TraddingResultController;

// IP	145.223.17.195
// Port	65002
// Username	u921379270

Route::post('/register',[PublicApiController::class,'register']);
Route::post('/login',[PublicApiController::class,'login']);
Route::post('/login_password',[PublicApiController::class,'login_password']);


Route::controller(AgencyPromotionController::class)->group(function () {
    Route::get('/agency-promotion-data-{id}', 'promotion_data');
	Route::get('/new-subordinate', 'new_subordinate');
	Route::get('/tier', 'tier');
	Route::post('/subordinate-data','subordinate_data');
	Route::get('/turnovers','turnover_new');
	//Route::get('/turnover','turnover');
});



route::get('/profile/{id}',[PublicApiController::class,'profile']);
route::post('/profile_update/',[PublicApiController::class,'profileupdate']);
route::get('/statelist',[PublicApiController::class,'statelist']);
route::get('/slider_list',[PublicApiController::class,'slidelist']);
route::get('/popup_modal',[PublicApiController::class,'popup_modal']);

route::get('/how_to_play',[PublicApiController::class,'how_to_play']);
route::get('/salary',[PublicApiController::class,'salary']);

route::get('/notifications',[PublicApiController::class,'notifications']);
Route::get('/commission_details',[PublicApiController::class,'commission_details']);

// GameApi(Controller


route::get('/game_list',[GameApiController::class,'game_list']);
route::get('/live_game',[GameApiController::class,'live_game']);
route::post('/bets',[GameApiController::class,'bets']);
route::get('/result',[GameApiController::class,'result']);
Route::post('/place-cross-bet', [GameApiController::class, 'crossBets']); // Cross bets
Route::get('/cross-bet-result', [GameApiController::class, 'crossResult']);

// GameApiController

route::get('/business_settings',[PayingController::class,'business_settings']);

route::post('/paying',[PayingController::class,'paying']);// manul payment 


route::post('/payin',[PayingController::class,'payin']);  //gatway indianpay
Route::get('/checkPayment',[PayingController::class, 'checkPayment']);
Route::get('/payin-successfully',[PayingController::class,'redirect_success'])->name('payin.successfully');
Route::get('/payin-failed', [PayingController::class, 'failed'])->name('payin.failed');


route::get('/admin_bank_details',[PayingController::class,'admin_bank_details']);
route::get('/deposite_hostory/{user_id}',[PayingController::class,'deposite_hostory']);



route::post('/add_account_details',[WithdrawController::class,'add_account_details']);
route::get('/get_account_details/{user_id}',[WithdrawController::class,'get_account_details']);
route::post('/withdrawmanual',[WithdrawController::class,'withdrawmanual']);
route::get('/withdrawmanual_hostory/{user_id}',[WithdrawController::class,'withdrawmanual_hostory']);



// updated codes  jodi 
Route::get('/chart_result/{id}', [SattaController::class, 'chart_result']);
Route::post('/daily_game_result', [GameApiController::class, 'gameresult']);
Route::get('/bethistory', [GameApiController::class, 'bethistory']);


Route::get('/speen_list/{user_id}', [FreeSpeenController::class, 'speen_list']);
Route::post('/spin_opration', [FreeSpeenController::class, 'spin_opration']);
Route::get('daily_salary', [SalaryController::class, 'dailySalary']);
Route::post('feedback', [PublicApiController::class, 'feedback']);
Route::get('user_salary_history', [SalaryController::class, 'userSalaryList']); 
Route::get('/calculate_daily_salary', [SalaryController::class, 'calculateDailySalaryBonus']);

//lkb//
// vendor section
route::post('/request_amount',[VendorActivityController::class,'request_amount']);
route::get('/vendor_payment_request/{vendor_id}',[VendorActivityController::class,'vendor_payment_request']);


route::post('/request_action',[VendorActivityController::class,'request_action']);

route::get('/user_request_history/{user_id}',[VendorActivityController::class,'user_request_history']);

route::get('/getUserRequestsForVendor/{vendor_id}',[VendorActivityController::class,'getUserRequestsForVendor']);







route::get('/support',[PublicApiController::class,'support']);

route::get('/tradding_list',[TraddingController::class,'tradding_list']);

route::get('/live_tradding',[TraddingController::class,'live_tradding']);

route::post('/tradding_bets',[TraddingController::class,'live_tradding_bets']);

route::get('/tradding_result/{id}',[TraddingResultController::class,'tradding_result']);


route::post('/tradding_bets_result',[TraddingController::class,'tradding_bets_result']);


route::get('/result_list',[TraddingResultController::class,'index']);


route::get('/tradding_all_result/{id}',[TraddingController::class,'tradding_all_result']);

route::post('/every_day_result',[TraddingController::class,'every_day_result']);

route::get('/chat_reply/{id}',[PublicApiController::class,'chat_reply']);


route::get('/wallet_histories/{id}',[PublicApiController::class,'wallet_histories']);


route::post('/change_password',[PublicApiController::class,'change_password']);







