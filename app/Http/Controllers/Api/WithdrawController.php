<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
class WithdrawController extends Controller
{    

	
	public function withdrawmanual(Request $request){
    $serverDown = false;
    if ($serverDown) {
        return response()->json([
            'status' => 503,
            'message' => 'Withdrawal is temporarily disabled due to server maintenance. Please try again later.'
        ], 503);
    }
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'amount' => 'required|numeric'
    ]);
    $validator->stopOnFirstFailure();
    if($validator->fails()){
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response,400);
    }
    $userid = $request->input('user_id');
    $accountid = DB::table('bank_details')->where('userid', $userid)->value('id');
    $amount = $request->input('amount');
    $type = 1;
    $user_details = DB::table('bank_details')->where('userid', $userid)->first();
    $upiid = $user_details->upi_id;

    if (empty($upiid)) {
        $response = [
            'status' => 500,
            'message' => "upi_id is required"
        ];
        return response()->json($response,400);
    }
   // $lastWithdrawal = DB::table('withdraw_histories')
//     ->where('user_id', $userid)
//     ->orderBy('created_at', 'desc')
//     ->first();
		
// if ($lastWithdrawal && $lastWithdrawal->status == 1) {
//     return response()->json([
//         'status' => 400,
//         'message' => 'You cannot withdraw again until your previous request is approved or rejected.'
//     ], 200);
// }

    $withdrawCount = DB::table('wallet_histories')
        ->where('user_id', $userid)
        ->whereDate('created_at', now())
        ->where('status', 2)
        ->count();

    if ($withdrawCount >= 3) {
        $response = [
            'status' => 400,
            'message' => 'You can only withdraw 3 times in a day.'
        ];
        return response()->json($response, 400);
    }

    $date = date('YmdHis');
    $rand = rand(11111, 99999);
    $orderid = $date . $rand;
		$minnum = DB::table('business_settings')->where('id', 15)->value('longtext');
		$maxnum = DB::table('business_settings')->where('id', 16)->value('longtext');

		if ($amount >= $minnum && $amount <= $maxnum) {
			$wallet = DB::table('users')
				->select('wallet', 'recharge', 'first_recharge', 'winning_wallet', 'commission')
				->where('id', $userid)
				->first();

			$user_winning_wallet = $wallet->winning_wallet;
			$commission = $wallet->commission;

			$total_balance = $user_winning_wallet + $commission;

			if ($total_balance >= $amount) {
				$remaining = $amount;

				if ($commission >= $remaining) {
					DB::table('users')->where('id', $userid)->decrement('commission', $remaining);
					$remaining = 0;
				} else {
					DB::table('users')->where('id', $userid)->decrement('commission', $commission);
					$remaining -= $commission;
				}

				if ($remaining > 0) {
					DB::table('users')->where('id', $userid)->decrement('winning_wallet', $remaining);
				}

				$data = DB::table('withdraw_histories')->insert([
					'user_id' => $userid,
					'amount' => $amount,
					'account_id' => $accountid,
					'type' => $type,
					'order_id' => $orderid,
					'status' => 1,
					'typeimage' => "https://root.mahajong.club/uploads/fastpay_image.png",
					'created_at' => now(),
					'updated_at' => now(),
				]);

				if ($data) {
					return response()->json([
						'status' => 200,
						'message' => 'Withdraw Request Successfully ..!',
					], 200);
				} else {
					return response()->json([
						'status' => 400,
						'message' => 'Internal error..!',
					], 200);
				}
			} else {
				return response()->json([
					'status' => 400,
					'message' => 'Insufficient Balance..!',
				], 200);
			}
		} else {
			return response()->json([
				'status' => 400,
				'message' => "Minimum Withdraw $minnum And Maximum Withdraw $maxnum",
			], 200);
		}

}

	
	
	
    public function add_account_details(Request $request){
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'name' => 'required',
                'upi_id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => $validator->errors()->first(),
                ], 200);
            }
           // dd($request->all());
            $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i:s');
            $bankDetails = DB::table('bank_details')->where('userid', $request->user_id)->first();
            if ($bankDetails) {
                DB::table('bank_details')->where('userid', $request->user_id)->update([
                    'name' => $request->name,
                    'upi_id' => $request->upi_id,
                ]);
                $message = 'Account details updated successfully';
            } else {
                DB::table('bank_details')->insert([
                    'userid' => $request->user_id,
                    'name' => $request->name,
                    'upi_id' => $request->upi_id,
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ]);
                $message = 'Account details added successfully';
            }
            return response()->json([
                'status' => 200,
                'message' => $message,
            ]);
    }
       
     public function get_account_details($user_id){
            $data = DB::table('bank_details')->where('userid', $user_id)->select('name', 'upi_id','updated_at','created_at')->first(); 
            if($data){
                return response()->json([
                    'status' => 200,
                    'data' => $data,
                ], 200);
            } else {
                return response()->json([
                    'status' => 400,
                    'data' => $data,  
                ], 200);
            }
        }
        
     public function withdrawmanuall(Request $request){
            $validator = Validator::make($request->all(), [
              'user_id' => 'required|integer',
              'amount' => 'required|numeric|min:1'
             ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 400,
                        'message' => $validator->errors()->first(),
                    ], 200);
                }
            $userid = $request->user_id;
            $amount = $request->amount;
            $date = date('YmdHis');
            $rand = rand(11111, 99999);
            $orderid = $date . $rand;
            $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i:s');
            $userinfo = DB::table('users')->where('id' , $userid)->first();
            $userwallet = $userinfo->winning_wallet;
            $usermobile = $userinfo->mobile;
            $firstrecharge = DB::table('users')->where('id', $userid)->value('first_recharge');
			$minnum = DB::table('business_settings')->where('id',15)->value('longtext');
			$maxnum = DB::table('business_settings')->where('id',16)->value('longtext');
			$turnover = DB::table('users')->where('id', $userid)->value('recharge');
			
			$bankdata = DB::table('bank_details')->where('userid', $userid)->first(); 
			$account_num = $bankdata->account_num;
			$bank_name = $bankdata->bank_name;
			$ifsc_code = $bankdata->ifsc_code;
			$name = $bankdata->name;
			
			$pendingWithdrawals = DB::table('withdraws')->where('user_id', $userid)->where('status', 1)->count();
			if($pendingWithdrawals > 0){
				return response()->json([
							'status' => 400,
							'message' => "You have a pending withdrawal. Please wait for completion.",  
						], 200);
			}
            if($turnover != 0){
			return response()->json([
							'status' => 400,
							'message' => "Need to be bet amount must be 0 for withdrawal.",  
						], 200); 
			}
               if($amount < $minnum) {
				return response()->json([
					'status' => 400,
					'message' => "Minimum withdrawal is $minnum ",
				], 200);
				}
				if ($amount > $maxnum) {
					return response()->json([
						'status' => 400,
						'message' => "Maximum withdrawal is $maxnum ",
					], 200);
				}
                if($firstrecharge == 1){
                      return response()->json([
                        'status' => 400,
                        'message' => "Withdrawal is not allowed without the first recharge.",  
                    ], 200); 
                }
                if($userwallet < $amount){
                     return response()->json([
                        'status' => 400,
                        'message' => "Sorry! Your balance is insufficient for this withdrawal.",  
                    ], 200);
                }
                    $data = DB::table('withdraws')->insert([
                    'user_id' => $userid,
                    'beneficiary' => $name,
                    'bank_name' => $bank_name,
                    'mobile' => $usermobile,
                    'user_bank_acount' => $account_num,
                    'ifsc_code' => $ifsc_code,
                    'amount' => $amount,
                    'order_id' => $orderid,
                    'status' => 1,
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate
                  ]);
                   $data = DB::table('wallet_histories')->insert([
                    'user_id' => $userid,
                    'amount' => $amount,
                    'type_id' => 2,
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate
                  ]);
                  if($data){
                     $deduct = DB::table('users')->where('id' , $userid)->decrement('winning_wallet' , $amount);
                      return response()->json([
                        'status' => 200,
                        'message' => "Withdrawal Successfully.",  
                    ], 200);
                  }
        }
         public function withdrawmanual_hostory($user_id){
			// dd($user_id);
         $data = DB::table('withdraw_histories')->where('user_id', $user_id)->select('id','order_id','status','amount','created_at')->orderBy('id', 'desc')->get();
			// dd($data);
        if ($data){
            return response()->json([
                'status' => 200,
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'data' => []
            ], 200);
        }
        }

   

}



