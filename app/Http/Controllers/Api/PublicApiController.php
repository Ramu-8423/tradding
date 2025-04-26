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
class PublicApiController extends Controller
{ 
	
       private function generateUniqueCode(){
        do{
            $code = Str::upper(Str::random(2)) . rand(0, 9) . Str::upper(Str::random(2)) . rand(0, 9);
        } while (User::where('referral_code', $code)->exists());
        return $code;
     }
     
     public function commission_details(Request $request){
      $validator = Validator::make($request->all(), [
        'userid' => 'required|integer',
        'typeid' => 'required|integer',
        'date' => 'nullable|date' // Date is optional
      ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $userid = $request->userid;
    $typeid = $request->typeid;
    $date = $request->date;

    // Base query
    $query = "SELECT * FROM `wallet_histories` WHERE `user_id` = ? AND `type_id` = ?";
    $params = [$userid, $typeid];

    // Add date condition if provided
    if (!is_null($date)) {
        $query .= " AND `created_at` LIKE ?";
        $params[] = "%$date%";
    }

    $commission = DB::select($query, $params);

    $data = [];

    foreach ($commission as $item) {
        $data[] = [
            'number_of_bettors' => $item->description_2 ?? '',
            'bet_amount' => $item->description ?? '',
            'commission_payout' => $item->amount ?? 0,
            'date' => $item->created_at ?? '',
            'settlement_date' => $item->updated_at ?? ''
        ];
    }

    if (!empty($data)) {
        return response()->json([
            'message' => 'commission_details',
            'status' => 200,
            'data' => $data,
        ]);
    } else {
        return response()->json([
            'message' => 'Not found..!',
            'status' => 400,
            'data' => []
        ], 400);
    }
}

     
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric'
        ])->stopOnFirstFailure();
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 200);
        }
        $user = User::where('mobile', $request->mobile)->first(); 
        if ($user){
              $active_check = $user->status;
               if($active_check != 1){
                    return response()->json([
                        'status' => 400,
                        'message' => 'User is disabled by admin.',
                    ], 200);
                }else{
                      return response()->json([
                        'status' => 200,
                        'message' => 'Login successful',
                        'user_id' => $user->id
                    ], 200);
                }
                } else {
                    return response()->json([
                        'status' => 400,
                         'type' => 1,
                        'message' => 'user not  found'
                    ], 200);
                }
    }
    
    
    public function login_password(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
            'password' => 'required'
        ])->stopOnFirstFailure();
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 200);
        }
        $user = User::where('mobile', $request->mobile)->first(); 
        if($user){
           $matchpassword = User::where('mobile', $request->mobile)->where('password', $request->password)->first();
           if($matchpassword){
                return response()->json([
                'status' => 200,
                'message' => 'Login successful',
                'user_id' => $user->id
            ], 200);
           }else{
                 return response()->json([
                    'status' => 400,
                    'message' => 'Incurrect password',
                    ], 200); 
           }
        }else{
            return response()->json([
                'status' => 400,
                'message' => 'mobile number is not register',
            ], 200);  
        }     
    }
    
	
	
	public function getVendorId($referrerId){
		while ($referrerId) {
			$user = DB::table('users')->where('id', $referrerId)->first();
			if (!$user) {
				return null; // Referrer not found
			}
			if ($user->role_id == 3) {
				return $user->id; // This is the vendor
			}
			// Go to the next level up
			$referrerId = $user->referrer_id;
		}
		return null; // Vendor not found
	}
	
	
    
 public function profile($id){
    $data = DB::table('users')->where('id', $id)->first();

    // Wallet me bonus add kar do, but bonus field ko waise hi rehne do
    $data->wallet = ($data->wallet ?? 0) + ($data->bonus ?? 0);
    $user = DB::table('users')
        ->where('id', $id)
        ->select('name', 'email', 'address', 'dob')
        ->first();

    $count = count(array_filter((array) $user)) ?? 0;
    $presentdata = 40 + $count * 15;
    $data->presentdata = $presentdata; 

    $referrer_id = $data->referrer_id;
	//dd($referrer_id);
	 
	 $vendorId = $this->getVendorId($referrer_id) ?? 2;  // if null then admin vendor id 2 set for admin vendor
	// dd($vendorId);
	 //dd($vendorId);
	 
    $vendor_info = DB::table('users')->where('id', $vendorId)->first();

    $vendor_upi_id = $vendor_info->vendor_upi_id ?? null;
    $vendor_qr  = $vendor_info->vendor_qr ?? null;
    $vendor_role  = $vendor_info->role_id ?? null;
    $vendor_id  = $vendor_info->id ?? null;

    $data->vendor_upi_id = $vendor_upi_id;
    $data->vendor_qr = url('/') . '/' . $vendor_qr;
    $data->vendor_id = $vendor_id;
    $data->vendor_role = $vendor_role;
	 

    if($data){
        return response()->json([
            'status' => 200,
            'data' => $data,
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'no data'
        ], 200);  
    }
}

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|unique:users,mobile|',
            'password' => 'required|min:6|max:16',
            'state' => 'required|'
        ]);
        $validator->stopOnFirstFailure();
         if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 200);
        }

        $unique_code = $this->generateUniqueCode();
        $referrerId = null;
        if ($request->referral_code){
            $referrer = DB::table('users')->where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $referrerId = $referrer->id;
                $role_id = $referrer->role_id;
				
				 $fivePercent   = (50 * 10) / 100; // Referrer gets 5% bonus for each referral (up to 10 people)
				
				$count = DB::table('users')->where('referrer_id', $referrerId)->count(); 
					if($count <= 10){
						DB::table('users')->where('id', $referrerId)->increment('bonus', $fivePercent);
						 DB::table('wallet_histories')->insert([
							"user_id"     => $referrerId,
							"amount"      => $fivePercent,
							"type_id"     => "31",
							"description" => "Referral Bonus"
						]);
					}
				
            }
        }else{
			$referrerId = 2;
		}
     
        $insertedId = DB::table('users')->insertGetId([
			'name' => $request->name,
			'email' => $request->email,
			'role_id' => 4,
			'mobile' => $request->mobile,
			'status' => 1,
			'wallet' => 0,
			'bonus' => 50,
			'password' => $request->password,
			'state' => $request->state,
			'referrer_id' => $referrerId,
			'referral_code' => $unique_code,
		]);
		 $inserted = DB::table('wallet_histories')->insert([
            'user_id' => $insertedId,
			'amount' => 50,
			'type_id' => 28,
			'description' => "Register Bonus",
           
        ]);
        if ($inserted) {
            $userId = DB::getPdo()->lastInsertId();
            $response = [
                'message' => 'Registered successfully',
                'status' => 200,
                'user_id' => $insertedId 
            ];
        } else {
            $response = [
                'message' => 'Failed to insert record',
                'status' => 400,
                'data' => []
            ];
        }
        return response()->json($response);
    }
   
    public function  statelist(){
     $data =DB::table('states')->get();
     if($data){
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
       }else{
           return response()->json([
                'status' => 400,
                    'message' => 'no data'
            ], 200);  
       }
}
    public function slidelist(){
    $data = DB::table('slider')->select('id', 'images')->get();
    if($data){
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
       }else{
           return response()->json([
                'status' => 400,
                'data' => []
            ], 200);  
       }
   }
   
  public function profileupdate(Request $request)
{
    $id = $request->user_id;
    $existingUser = DB::table('users')->where('id', $id)->first();

    if (!$existingUser) {
        return response()->json([
            'status' => 404,
            'message' => 'User not found'
        ], 404);
    }

    // Use existing values if not provided
    $name = $request->name ?? $existingUser->name;
    $email = $request->email ?? $existingUser->email;
    $address = $request->address ?? $existingUser->address;
    $dob = $request->dob ?? $existingUser->dob;
    $imageURL = $existingUser->image;

    // If new base64 image is given, process and save it
    if ($request->has('image_base64') && $request->image_base64 != null) {
        $imageData = base64_decode($request->image_base64);

        if ($imageData === false) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid base64 image data'
            ], 400);
        }

        $imageName = 'image_' . time() . '.png';
        $folderPath = public_path('sliderimage');

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

        $imageURL = url('sliderimage/' . $imageName);
    }

    // Perform update
    DB::table('users')->where('id', $id)->update([
        'name' => $name,
        'email' => $email,
        'address' => $address,
        'dob' => $dob,
        'image' => $imageURL
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'Profile updated successfully'
    ], 200);
}


    public function  popup_modal(){
        $data = DB::table('settings')->where('id', 4)->select('description')->first();
        if($data){
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
       }else{
           return response()->json([
                'status' => 400,
                'data' => []
            ], 200);  
       }
    }
    
     public function  how_to_play(){
        $data = DB::table('settings')->where('id', 6)->select('description')->first();
        if($data){
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
       }else{
           return response()->json([
                'status' => 400,
                'data' => []
            ], 200);  
           }
        }
	
	public function  salary(){
        $data = DB::table('settings')->where('id', 7)->select('description')->first();
        if($data){
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
       }else{
           return response()->json([
                'status' => 400,
                'data' => []
            ], 200);  
           }
        }
    
        public function  notifications(){
        $data = DB::table('notifications')->get();
        if($data){
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
        }else{
           return response()->json([
                'status' => 400,
                'data' => []
            ], 200);  
           }
        }
    
    
    public function feedback(Request $request){
         $validator = Validator::make($request->all(), [
            'user_id' => 'required|',
            'feedback' => 'required|'
        ]);
        $validator->stopOnFirstFailure();
         if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 200);
        }
        $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i:s');
        $data = DB::table('feedback')->insert([
            "user_id" => $request->user_id,
            "feedback" => $request->feedback,
            "created_at" => $currentDate,
            ]);
        if($data){
            return response()->json([
                'status' => 200,
                'message' =>  'Feedback send successfully'
            ], 200);    
        }else{
           return response()->json([
                'status' => 400,
                'message' =>  'Somthing went wrong'
            ], 200);    
        }
    }
    
    public function support(){
            $data = DB::table('support')->get();
            if($data){
                return response()->json([
                    'status' => 200,
                    'data' =>  $data
                ], 200);    
            }else{
               return response()->json([
                    'status' => 400,
                    'message' =>  'no data'
                ], 200); 
            
        }
    }
    
	
	 public function chat_reply($id){
			$data = DB::table('feedback')
					  ->where('user_id', $id)
					  ->orderBy('id', 'desc')
					  ->get();

			if ($data->isNotEmpty()) {
				return response()->json([
					'status' => 200,
					'data' => $data
				], 200);
			} else {
				return response()->json([
					'status' => 400,
					'message' => 'no data'
				], 200);
			}
		}

	     public function wallet_histories($id){
			$data = DB::table('wallet_histories')
					  ->where('user_id', $id)->select('id','description','amount','created_at')
					  ->orderBy('id', 'desc')
					  ->get();

			if ($data->isNotEmpty()) {
				return response()->json([
					'status' => 200,
					'data' => $data
				], 200);
			} else {
				return response()->json([
					'status' => 400,
					'message' => 'no data'
				], 200);
			}
		}

	
	   public function change_password(Request $request){
		    $validator = Validator::make($request->all(), [
            'user_id' => 'required|',
			'old_password' => 'required|',
			'new_password' => 'required|min:6|max:16',
			'confirm_password' => 'required|min:6|max:16',
        ]);
        $validator->stopOnFirstFailure();
         if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 200);
	   }
		  $userid = $request->user_id;
		  $new_password = $request->new_password;
		  $confirm_password = $request->confirm_password;
		  // dd($confirm_password);
	    	if ($new_password !== $confirm_password) {
				return response()->json([
					'status' => 400,
					'message' => 'Confirm Password does not match.'
					], 400); 
				}
		  $change_old_password = $request->old_password;
		  $userinfo = DB::table('users')->where('id', $userid)->first();
		  $oldpassword = $userinfo->password;
		   if($oldpassword == $change_old_password){
			  DB::table('users')->where('id', $userid)->update([
			   "password" => $new_password
			  ]);
			   return response()->json([
					'status' => 200,
					'message' => "Password update successfully"
				], 200);
		   }
		 return response()->json([
			'status' => 400,
			'message' => 'Password does not match.'
		   ], 200);
	   }

}
