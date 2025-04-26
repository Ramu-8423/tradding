<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
class VendorActivityController extends Controller
{
     private function generateUniqueCode(){
        do{
            $code = Str::upper(Str::random(2)) . rand(0, 9) . Str::upper(Str::random(2)) . rand(0, 9);
        } while (User::where('referral_code', $code)->exists());
        return $code;
    }
    
    public function createvendor(){
        $data = DB::table('states')->get();
        return view('vendor.CreateVendor')->with('data', $data);
    }
    
 public function addvendor(Request $request){
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'vendor_qr' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        'vendor_upi_id' => 'required|string',
        'mobile' => 'required|digits_between:6,15|unique:users,mobile',
        'password' => 'required|string|min:6',
        'state' => 'required|string'
    ]);

    // Upload image
    $qrPath = null;
    if ($request->hasFile('vendor_qr')) {
    $file = $request->file('vendor_qr');
    $extension = $file->getClientOriginalExtension();
    $filename = 'qr_' . Str::random(10) . '.' . $extension;
    $file->move(public_path('vendorQR'), $filename);
    $qrPath = 'vendorQR/' . $filename;
}

    $unique_code = $this->generateUniqueCode();

    DB::table('users')->insert([
        "role_id" => 3,
        "name" => $request->name,
        "status" => 1,
        'referral_code' => $unique_code,
        'vendor_qr' => $qrPath,
        'vendor_upi_id' => $request->vendor_upi_id,
        "email" => $request->email,
        "mobile" => $request->mobile,
        "password" => $request->password,
        "state" => $request->state,
    ]);

    return back()->with('message', 'Vendor added successfully');
}

  public function updatevendor($id){
    $vendor = DB::table('users')->where('id', $id)->first();

    if (!$vendor) {
        return redirect()->back()->with('error', 'Vendor not found.');
    }

    // Assume states are fetched for dropdown
    $states = DB::table('states')->get();

    return view('vendor.vendorupdate', compact('vendor', 'states'));
 }
    
     public function updatevendorPost(Request $request, $id){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required',
            'password' => 'required',
            'state' => 'required',
            'vendor_upi_id' => 'nullable|string',
            'vendor_qr' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $vendor = DB::table('users')->where('id', $id)->first();
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => $request->password,
            'state' => $request->state,
            'vendor_upi_id' => $request->vendor_upi_id,
        ];

       if ($request->hasFile('vendor_qr')) {
     // Delete old QR if it exists
            if ($vendor->vendor_qr && File::exists(public_path($vendor->vendor_qr))) {
                File::delete(public_path($vendor->vendor_qr));
            }
            $file = $request->file('vendor_qr');
            $extension = $file->getClientOriginalExtension();
            $filename = 'qr_' . Str::random(10) . '.' . $extension;
            $file->move(public_path('vendorQR'), $filename);
        
            $data['vendor_qr'] = 'vendorQR/' . $filename;
        }

          DB::table('users')->where('id', $id)->update($data);
          return redirect()->route('updatevendor', $id)->with('success', 'Vendor updated successfully.');
   }


   public function userToVendorPayment($status){
        $user = session('admin_user');
        $role_id =  $user->role_id;
        $v_id =  $user->id;
        $users = DB::table('users')->where('referrer_id', $v_id)->get();
        $referral_ids = [];
          foreach($users as $users){
              $referral_ids[] = $users->id;
        }
        if($role_id == 1){
            $data = DB::table('vendor_request')->where('status', $status)->orderBy('id', 'desc')->get();
        }
        if($role_id == 2 || $role_id == 3){
             $data = DB::table('vendor_request')->where('vendor_id', $v_id)->where('status', $status)->orderBy('id', 'desc')->get();
        }
        return view('vendor.vendor_payment_request')->with('data', $data)->with('status', $status);
    }

    public function approvePayment($id,$ststus){
       $find = DB::table('vendor_request')->where('id', $id)->first();
       $userid = $find->user_id;
	   $vendor_id = $find->vendor_id;
       $userinfo = DB::table('users')->where('id', $userid)->first();
       $request_amount = $find->request_amount;
       $commission = ($request_amount * 8) / 100;
       $vendoramount = DB::table('users')->where('id', $vendor_id)->value('wallet');
     
       if($request_amount < $vendoramount){
          
          $vendordecrement = DB::table('users')->where('id', $vendor_id)->decrement('wallet', $request_amount);
          $userincrement = DB::table('users')->where('id', $userid)->increment('wallet', $request_amount);
          $vendorbonus = DB::table('users')->where('id', $vendor_id)->increment('commission', $commission);
          $update = DB::table('vendor_request')->where('id', $id)->update([
              'status' => $ststus
              ]);
		   
          $Transferred_history = DB::table('wallet_histories')->insert([
							"user_id" => $vendor_id,
							"amount"  => $request_amount,
							"type_id" => 31,
							"description" => "Transferred to the User",
						]);
		   
           $vendor_walltet_history = DB::table('wallet_histories')->insert([
                "user_id" => $vendor_id,
                "amount" => $commission,
                "type_id" => "34",
                "description" => "User Transfer commission"
                   ]);
		   
           $user_walltet_history = DB::table('wallet_histories')->insert([
                "user_id" => $userid,
                "amount" => $request_amount,
                "type_id" => "33",
                "description" => "Amount received from vendor"
                ]);
		   
       }else{
          return back()->with('error', 'Vendor Insufficient balance for the user');  
       }
    }
    
    
  public function rejectPayment(Request $request){
    $request->validate([
        'item_id' => 'required|integer',
        'reason' => 'required|string|max:80', 
    ]);
    
    $itemId = $request->input('item_id');
    $reason = $request->input('reason');
    
    $rejectPayment = DB::table('vendor_request')->where('id', $itemId)->update([
              'status' => 3 ,
              'description' => $request->reason
        ]);
    return back()->with('success', 'Reject successfully..');
  }  
   
}