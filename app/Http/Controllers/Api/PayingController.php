<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;


class PayingController extends Controller{
	
  private function generateUniqueRandomEmail()
    {
        do {
            $randomString = Str::random(10);  // 10 random characters
            $email = $randomString . '@example.com';
        } while (DB::table('users')->where('email', $email)->exists());  // Check if the email exists in the users table
        
        return $email;
    }
	
 public function payin(Request $request){
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'cash' => 'required|numeric|',  
        'type' => 'required|in:1', // only INR supported
    ]);
    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }
   
    $cash = $request->cash;
    $type = $request->type;
    $userid = $request->user_id;

    $date = date('YmdHis');
    $rand = rand(11111, 99999);
    $orderid = $date . $rand;
    $datetime = now();

    $user = DB::table('users')->where('id', $userid)->first();
      $email = $user->email ?? $this->generateUniqueRandomEmail();
  //  dd($email);
    if (!$user) {
        return response()->json([
            'status' => 400,
            'message' => 'Internal error: User not found'
        ]);
    }

    if ($type != 1) {
        return response()->json([
            'status' => 400,
            'message' => 'USDT is not supported!'
        ]);
    }
	  $role_id = $user->role_id;
	 $vendor_amount = DB::table('business_settings')->where('id',20)->value('longtext');
	 $mindeposite = DB::table('business_settings')->where('id',17)->value('longtext');
	 if ($role_id == 4) {
	     if($cash < $mindeposite){
				 return response()->json([
                        'status' => 200,
                        'message' => "'Minimum deposit  must be $mindeposite  or more.",  
                    ], 200); 
			}
	}
	 
	 if ($role_id == 2 || $role_id == 3) {
		if ($cash < $vendor_amount) {
			return response()->json([
				'status' => 200,
		     	'message' => "Minimum recharge is $vendor_amount for vendors.",
			],200);
		}
	}
      
	 
	 
	 
	 
	 
    $redirect_url = "https://root.mahajong.club/api/checkPayment?order_id=$orderid";
    // config('payment.indianpay.redirect_base_url') . $orderid;

    $insert = DB::table('payins')->insert([
        'user_id' => $userid,
        'amount' => $cash,
        'type' => $type,
        'order_id' => $orderid,
        'redirect_url' => $redirect_url,
        'status' => 1,
        'typeimages' => "https://root.winzy.app/uploads/fastpay_image.png",
        'created_at' => $datetime,
        'updated_at' => $datetime,
    ]);

    if (!$insert) {
        return response()->json([
            'status' => 400,
            'message' => 'Failed to store record in payin history!'
        ]);
    }

    $postData = [
        'merchantid' => "INDIANPAY00INDIANPAY00129",
        'orderid' => "$orderid",
        'amount' => $cash,
        'name' => $user->u_id ?? 'user',
        'email' => $email,
        'mobile' => $user->mobile,
        'remark' => 'payIn',
        'type' => "$type",
        'redirect_url' => "$redirect_url",
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
        // config('payment.indianpay.payin_url')
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83'
        ],
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);

    curl_close($curl);
  
    // Optional: Debug log
    if ($curlError) {
        Log::error("IndianPay API Error: " . $curlError);
        return response()->json([
            'status' => 500,
            'message' => 'Payment gateway error',
        ]);
    }

    // You can return JSON response to frontend:
    return response($response, 200)->header('Content-Type', 'application/json');
}

	public function failed() {
    return view('failed');
}

	public function checkPayment(Request $request){
	   
    // ✅ Validate request data
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:payins,order_id',
    ]);

    $validator->stopOnFirstFailure();
    
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $orderid = $request->order_id;
    $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');


	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://indianpay.co.in/admin/payinstatus?order_id='."$orderid",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	));

	//$response = curl_exec($curl);
//$status=$response->status;
	$response = curl_exec($curl);

// Close the cURL session
    curl_close($curl);

// Decode the JSON string into an object
$response = json_decode($response);
$status = $response->status ?? null; // Use null coalescing to avoid errors if 'status' is missing

if($status == "pending"){
	return redirect()->route('payin.failed');
        	
	}else{
    // ✅ Fetch payment record using DB facade
    $payment = DB::table('payins')
                ->where('order_id', $orderid)
                ->where('status', 1)
                ->first();
     
    if (!$payment) {
        return response()->json([
            'status' => 404,
            'message' => 'Payment not found or already processed.',
        ], 200);
    }

    $userid = $payment->user_id;
    $amount = $payment->amount;
   
   
    $user = DB::table('users')->where('id', $userid)->where('status', 1)->first();

    if (!$user) {
        return response()->json([
            'status' => 404,
            'message' => 'User not found or inactive.',
        ], 200);
    }
	   
			$sixPercent   = ($amount * 6) / 100;  // When a user joins via referral, the referrer gets 6%
			$tenPercent   = ($amount * 10) / 100; // When the referred user makes their first recharge, referrer gets 10%
			$threePercent = ($amount * 3) / 100;  // Lifetime bonus on every recharge 

			$user = DB::table('users')->where('id', $userid)->first();
			if (!$user) return;

			$referrer_id    = $user->referrer_id;
			$first_recharge = $user->first_recharge;
	
            
	
			if ($first_recharge == 1) {
				if ($referrer_id) {
					DB::table('users')->where('id', $referrer_id)->increment('bonus', $sixPercent);
					DB::table('wallet_histories')->insert([
						"user_id"     => $referrer_id,
						"amount"      => $sixPercent,
						"type_id"     => "30",
						"description" => "Referral Bonus (Referrer)"
					]);
                      
			
					
					DB::table('users')->where('id', $userid)->increment('bonus', $tenPercent);
					DB::table('wallet_histories')->insert([
						"user_id"     => $userid,
						"amount"      => $tenPercent,
						"type_id"     => "30",
						"description" => "Referral Bonus (User - First Recharge)"
					]);
				} else {
					DB::table('users')->where('id', $userid)->increment('bonus', $threePercent);
					DB::table('wallet_histories')->insert([
						"user_id"     => $userid,
						"amount"      => $threePercent,
						"type_id"     => "30",
						"description" => "First Recharge Bonus (No Referrer)"
					]);
				}

				DB::table('users')->where('id', $userid)->update([
					"first_recharge" => 0
				]);
			} else {
				DB::table('users')->where('id', $userid)->increment('bonus', $threePercent);
				DB::table('wallet_histories')->insert([
					"user_id"     => $userid,
					"amount"      => $threePercent,
					"type_id"     => "30",
					"description" => "Lifetime Bonus"
				]);
			}

		  
    DB::table('users')->where('id', $userid)
        ->update([
            'wallet' => DB::raw("wallet + $amount"),
            'recharge' => DB::raw("recharge + $amount"),
			'total_payin' => DB::raw("total_payin + $amount"),
        ]);

    // ✅ Update payment status
    DB::table('payins')
        ->where('order_id', $orderid)
        ->update(['status' => 2]);
    return redirect()->route('payin.successfully');
	
}
	}

	  public function redirect_success(){
            return view('success');
        }
	
	
    public function business_settings(){
    		$data = DB::table('business_settings')
    		->whereIn('id', [15, 16, 17, 18])
    		->select('title', 'longtext')
    		->get();
    		if($data){
    			 return response()->json([
    				'status' => 200,
    				'data'  => $data
    			],200);
    		}else{
    			 return response()->json([
    				'status' => 400,
    				'data'  => []
    			],200);
    		}
    	}
   public function admin_bank_details(){
    $data = DB::table('admin_bank_details')->get();

    if ($data->isNotEmpty()) {
        $data = $data->map(function ($item) {
            $item->qrimage = url($item->qrimage); // Laravel ka `url()` helper use kiya
            return $item;
        });

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

     // function paying now not implemented
      public function paying(Request $request){
      $validator = Validator::make($request->all(), [
        'user_id' => 'required',
		'screenshot' => 'required',
        'amount' => 'required|numeric',
        'transaction_id' => 'required|'
       ]);
			if ($validator->fails()) {
				return response()->json([
					'status' => 400,
					'message' => $validator->errors()->first(),
				], 200);
			}
	    	$mindeposite = DB::table('business_settings')->where('id',17)->value('longtext');
	    	$maxdeposite = DB::table('business_settings')->where('id',18)->value('longtext');
	    	if($request->amount < $mindeposite){
				 return response()->json([
                        'status' => 400,
                        'message' => "'Minimum deposit  must be $mindeposite  or more.",  
                    ], 200); 
			}
    		if($request->amount > $maxdeposite){
    			 return response()->json([
                            'status' => 400,
                            'message' => "Maximum allowed deposit amount is $maxdeposite .",  
                        ], 200); 
    		}
            $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i:s');
            $imageBase64 = $request->screenshot;
            $imageData = base64_decode($imageBase64);
               if ($imageData === false) {
                   return response()->json([
                       'status' => 400,
                       'message' => 'Invalid base64 image data'
                   ], 400);
               }
                    $imageName = 'image_' . time() . '.png';
                    $folderPath = public_path('paymentimage');
                    if (!File::exists($folderPath)) {
                        File::makeDirectory($folderPath, 0755, true, true);
                    }
                    $imagePath = $folderPath . '/' . $imageName;
                    if (file_put_contents($imagePath, $imageData) === false) {
                        return response()->json([
                            'status' => 500,
                            'message' => 'Failed to save image'
                        ], 500);
                    }
                      $imageURL = url('paymentimage/' . $imageName);
                      if($imageBase64 == null){
                          $imageURL = DB::table('users')->where('id', $id)->select('image')->first();
                          $imageURL = $imageURL ? url('paymentimage/' . $imageURL->image) : null;
                      }
            $screenshotId = DB::table('payins')->insertGetId([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'transaction_id' => $request->transaction_id, // ab user image ki jagah apna orderid insert kr raha hai
            'status' => 1,
            'screenshot' => $imageURL,
            'created_at' => $currentDate,
            'updated_at' => $currentDate
				]);
            $wallet_histories = DB::table('wallet_histories')->insert([
            "user_id" =>$request->user_id,
            "amount" => $request->amount,
            "type_id" => 1,
            "description" => "Payin ",
            "created_at" => $currentDate
            ]);
            if(!$screenshotId){
                return response()->json([
                    'status' => 400,
                    'message' => 'Deposit Failed!'
                ], 200);
            }
            return response()->json([
                'status' => 200,
                'message' => 'Deposit request submitted successful!'
            ], 200);
    }
    public function deposite_hostory($user_id){
         $data = DB::table('payins')->where('user_id', $user_id)->select('id','transaction_id','status','amount','created_at')->orderBy('id', 'desc')->get();
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



