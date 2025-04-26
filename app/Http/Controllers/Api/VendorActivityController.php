<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\{User,MlmLevel,Payin};
use Illuminate\Support\Facades\File;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


 
class VendorActivityController extends Controller
{
   public function request_amount(Request $request){
       
    $validator = Validator::make($request->all(), [
        'vendor_id' => 'required',
        'user_id' => 'required',
        'screenshot' => 'required',
        'amount' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 200);
    }
   $imageBase64 = $request->screenshot;
    $imageData = base64_decode($imageBase64);
    if ($imageData === false) {
        return response()->json([
            'status' => 400,
            'message' => 'Invalid base64 image data'
        ], 400);
    }
    
    $imageName = 'image_' . time() . '.png';
    $folderName = 'ToVendorPayment';
    $folderPath = public_path($folderName);
    
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

 $imageToStore = $folderName . '/' . $imageName;

  // dd($imageToStore);
    
    $vendorid = $request->vendor_id;
    $userid = $request->user_id;
    $amount = $request->amount;
    $nowInKolkata = Carbon::now('Asia/Kolkata');
    
    $check_vendor = DB::table('users')->where('id', $vendorid)->first();

    if (!$check_vendor || $check_vendor->status != 1) {
        return response()->json([
            'status' => 400,
            'message' => 'The selected vendor is not active for receiving payments.'
        ], 200);
    }

    DB::table('vendor_request')->insert([
        'vendor_id'      => $vendorid,
        'user_id'        => $userid,
        'request_amount' => $amount,
        'status'         => 1, // Assuming 1 = pending
        'description'    => null, // Or provide if available
        'screeshot'     => $imageToStore,
        'created_at'     => $nowInKolkata,
        'updated_at'     => $nowInKolkata
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'Vendor request submitted successfully.',
    ]);
}
     
	  
	public function vendor_payment_request($vendor_id){
    $data = DB::table('vendor_request')
        ->join('users', 'vendor_request.user_id', '=', 'users.id')
        ->where('vendor_request.vendor_id', $vendor_id)
        ->where('vendor_request.status', 1)
		->orderBy('vendor_request.id', 'desc')
        ->select(
            'vendor_request.id',
            'vendor_request.request_amount',
            'vendor_request.status',
            'vendor_request.screeshot',
            'vendor_request.user_id',
            'vendor_request.created_at',
            'users.name as user_name'
        )
        ->get();

    $modifiedData = $data->map(function ($item) {
        return [
            'request_id' => $item->id,
            'request_amount' => $item->request_amount,
            'status' => $item->status,
            'screenshot_url' => url($item->screeshot),
            'user_id' => $item->user_id,
            'user_name' => $item->user_name,
            'created_at' => $item->created_at,
        ];
    });
       if($modifiedData){
		     return response()->json([
				'status' => 200,
				'data' => $modifiedData,
			],200);
	   }else{
		      return response()->json([
			'status' => 400,
			'message' => 'No Request found',
		],200);
	   }   
	 }
	
	
	public function request_action(Request $request){
		$validator = Validator::make($request->all(), [
			'request_id' => 'required',
			'status' => 'required',
			'comment' => 'required_if:status,3|max:40',
		]);
			if($validator->fails()) {
				return response()->json([
					'status' => 400,
					'message' => $validator->errors()->first(),
				], 200);
			}
		$request_id = $request->request_id;
		$status = $request->status;
		$request_info =DB::table('vendor_request')->where('id', $request_id)->first();
		$vendor_id = $request_info->vendor_id;
		$user_id = $request_info->user_id;
		$now_status = $request_info->status;
		$amount = $request_info->request_amount;
		$vendor_info =DB::table('users')->where('id', $vendor_id)->first();
		$vendor_current_wallet = $vendor_info->wallet;
		$commision = ($amount * 8) / 100;
		if($status ==2){
			if($vendor_current_wallet < $amount){
						  return response()->json([
							'status' => 200,
							'message' => 'Insuficant balance plsease recharge',
				],200);
			}
			//dd($now_status);
				if ($now_status == 2) {
					return response()->json([
						'status' => 400,
						'message' => 'The amount has already been transferred.',
					], 200);
				}
               $vendor_wallet = DB::table('users')->where('id', $vendor_id)->value('wallet');
			
				if ($vendor_wallet >= $amount) {
					$deduct = DB::table('users')->where('id', $vendor_id)->decrement('wallet', $amount);
					if ($deduct) {
						DB::table('users')->where('id', $vendor_id)->increment('commission', $commision);
						DB::table('users')->where('id', $user_id)->increment('wallet', $amount);

						DB::table('wallet_histories')->insert([
							"user_id" => $vendor_id,
							"amount"  => $amount,
							"type_id" => 31,
							"description" => "Transferred to the User",
						]);

						DB::table('wallet_histories')->insert([
							"user_id" => $vendor_id,
							"amount"  => $commision,
							"type_id" => 34,
							"description" => "User transfer commission",
						]);

						DB::table('wallet_histories')->insert([
							"user_id" => $user_id,
							"amount"  => $amount,
							"type_id" => 33,
							"description" => "Amount received from vendor",
						]);

						DB::table('vendor_request')->where('id', $request_id)->update([
							"status" => 2,
						]);

						return response()->json([
							'status' => 200,
							'message' => 'Transfer Successfully',
						], 200);
					}
				}


		if($status ==3){
			$description = $request->comment;
			DB::table('vendor_request')->where('id', $request_id)->update([
					"status" => 3,
				    "description" => $description,
				 ]);
		}
		
		  return response()->json([
				'status' => 200,
				'message' => 'Rejectted Successfully',
				],200);
	
   }
	}
	
	public function user_request_history($user_id) {
    $data = DB::table('vendor_request')
        ->where('user_id', $user_id)
        ->orderBy('id', 'desc')
        ->select('id', 'request_amount', 'status', 'created_at', 'description')
        ->get();
		
    if ($data->isEmpty()) {
        return response()->json([
            'status' => 400,
            'message' => 'No request history found.',
            'data' => [],
        ], 200);
    }
    return response()->json([
        'status' => 200,
        'data' => $data,
    ], 200);
}
		
	
	    public function getUserRequestsForVendor($vendor_id){
		   $data = DB::table('vendor_request')
			->join('users', 'vendor_request.user_id', '=', 'users.id')
			->where('vendor_request.vendor_id', $vendor_id)
			->orderBy('vendor_request.id', 'desc')
			->select(
				'vendor_request.id',
				'vendor_request.request_amount',
				'vendor_request.status',
			    'vendor_request.description',
				'vendor_request.created_at',
				'users.name as user_name'
			)
			->get();
			
			if($data->isEmpty()) {
				return response()->json([
					'status' => 400,
					'message' => 'No request history found.',
					'data' => [],
				], 200);
			}
			
			return response()->json([
				'status' => 200,
				'data' => $data,
			], 200);
		}
		
		
		
	}
