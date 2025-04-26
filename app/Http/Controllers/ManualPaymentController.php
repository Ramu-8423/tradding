<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ManualPaymentController extends Controller
{

    public function m_deposite($status){
      $data = DB::table('payins')
        ->join('users', 'payins.user_id', '=', 'users.id')
        ->where('payins.status', $status)
        ->select(
            'payins.*',
            'users.name as user_name',
            'users.mobile as user_mobile'
        )->orderBy('payins.id', 'desc')->get();
		//dd($data);
        return view('manualpayment.manualpayment')->with('status', $status)->with('data', $data);
        
    }
	
	public function success(Request $request, $id)
{
    $value = $request->session()->has('id');
    
    $pin = 1111;

    $inputPin = $request->input('pin');
    if ($inputPin == $pin) {
    if (!empty($value)) {
       
        $data = DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, 
                            (SELECT admin_settings.longtext FROM admin_settings WHERE id = 13) AS token, 
                            (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid 
                            FROM account_details 
                            LEFT JOIN users ON account_details.user_id = users.id 
                            LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id AND withdraw_histories.account_id = account_details.id 
                            LEFT JOIN admin_settings ON admin_settings.id = 12 
                            WHERE withdraw_histories.id = ?", [$id]);
   
        if (empty($data)) {
           
            return redirect()->route('widthdrawl', '1')->with('error', 'No withdrawal data found for the specified ID.');
        }
       
        // If data exists, proceed with setting up the payout
        $object = $data[0];  // Get the first item from the array (as there should only be one)
        $name = $object->name;
        $ac_no = $object->account_number;
        $ifsc = $object->ifsc_code;
        $bankname = $object->bank_name;
        $email = $object->email;
        $mobile = $object->mobile;
        $amount = $object->amount;
        $mid = $object->mid;
        $token = $object->token;
        $orderid = $object->orderid;

        $rand = rand(11111111111111, 99999999999999);
        $randid = "$rand";

        // Prepare the payout data
        $payoutdata = json_encode([
            "merchant_id" => $mid,
            "merchant_token" => $token,
            "account_no" => $ac_no,
            "ifsccode" => $ifsc,
            "amount" => $amount,
            "bankname" => $bankname,
            "remark" => "payout",
            "orderid" => $randid,
            "name" => $name,
            "contact" => $mobile,
            "email" => $email
        ]);
          //dd($payoutdata);
        // Encode the payout data using base64
        $salt = base64_encode($payoutdata);
        //dd($salt);

        // Prepare the JSON data to send via cURL
        $json = [
            "salt" => $salt
        ];

        // Initialize cURL session
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://indianpay.co.in/admin/single_transaction',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($json),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        // Execute cURL request and get the response
        $response = curl_exec($curl);
        //  echo $response;
        // die;
          $datta=json_decode($response);
         // dd($datta);
           $status = $datta->status;
        $error = $datta->error;
        
        // Check if the status is 400
        if ($status == 400) {
            return redirect()->back()->with('error', $error);
    }

        // Check for errors
        if (curl_errno($curl)) {
            echo 'Error: ' . curl_error($curl);
        } else {
            // Print the response
            echo $response;
        }

        // Close cURL session
        curl_close($curl);
$currentDate = Carbon::now(); // Current Date and Time
//echo $currentDate;

        // Update the withdraw history status with the response
        DB::select("UPDATE `withdraw_histories` SET `status` = '2', `response` = ?, `updated_at` = ? WHERE id = ?", [$response,$currentDate, $id]);
        //dd("$datta");
       
       // $this->upi($request); 
       
        return redirect()->route('widthdrawl', '1')->with('key', 'value');
        } else {
            return redirect()->route('login');
        }
    
    } else {
        // Pin does not match, return an invalid pin message
        return redirect()->route('widthdrawl', '1')
            ->withInput()  // Keep user input in the form
            ->withErrors(['pin' => 'Invalid pin. Please try again.']);
    }
}

    
  public function updateStatus($id, $status){
   //   dd($status);
    if ($status == 2) {
       $info = DB::table('payins')->where('id', $id)->first();
       $userid = $info->user_id;
       $amount = $info->amount;
       
       $data = DB::table('payins')->where('id', $id)->update(['status' => $status]);
       $increment = DB::table('users')->where('id', $userid)->increment('wallet' , $amount);
       return redirect()->back()->with('success', 'Transaction marked as Success.');
       
    } elseif ($status == 3) {
        DB::table('payins')->where('id', $id)->update(['status' => $status]);
        return redirect()->back()->with('error', 'Transaction has been Rejected.');
        
    } else {
        return redirect()->back()->with('info', 'Transaction status updated.');
    }
}
     
  public function m_withdraw($status){
    $data = DB::table('withdraw_histories')
        ->leftJoin('users', 'withdraw_histories.user_id', '=', 'users.id')
        ->where('withdraw_histories.status', $status)
        ->orderBy('withdraw_histories.id', 'desc')
        ->select('withdraw_histories.*', 'users.name', 'users.mobile')
        ->get();

  
    return view('manualpayment.manualwithdrawal')
        ->with('status', $status)
        ->with('data', $data);
}
    
  
public function updatewithdraw($id, $status)
{
    if ($status == 2) {
        $data = DB::select("SELECT bank_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, business_settings.longtext AS mid, 
                            (SELECT business_settings.longtext FROM business_settings WHERE id = 13) AS token, 
                            (SELECT business_settings.longtext FROM business_settings WHERE id = 14 ) AS orderid 
                            FROM bank_details 
                            LEFT JOIN users ON bank_details.userid = users.id 
                            LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id AND withdraw_histories.account_id = bank_details.id 
                            LEFT JOIN business_settings ON business_settings.id = 12 
                            WHERE withdraw_histories.id = ?", [$id]);

        if (empty($data)) {
            return redirect()->route('widthdrawl', '1')->with('error', 'No withdrawal data found for the specified ID.');
        }

        $object = $data[0];
        $upiid = $object->upi_id;
        $amount = $object->amount;
        $mid = $object->mid;
        $token = $object->token;

        $rand = rand(11111111111111, 99999999999999);
        $randid = "$rand";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://indianpay.co.in/admin/PayViaUpi?upiid=$upiid&amount=$amount&merchantId=$mid&token=$token&orderid=$randid",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return redirect()->back()->with('error', 'CURL Error: ' . curl_error($curl));
        }

        curl_close($curl);

        if (empty($response)) {
            return redirect()->back()->with('error', 'Empty response from the server');
        }

        $datta = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'Invalid JSON response');
        }

        if (is_object($datta) && isset($datta->status)) {
            $resStatus = $datta->status;
            $error = $datta->error ?? '';

            if ($resStatus == 400) {
                return redirect()->back()->with('error', $error);
            }

            $currentDate = Carbon::now();
            DB::update("UPDATE `withdraw_histories` SET `status` = ?, `response` = ?, `updated_at` = ?, `remark` = 'by upi' WHERE id = ?", [2, $response, $currentDate, $id]);

            return redirect()->back()->with('success', 'Withdraw marked as Success.');
        } else {
            return redirect()->back()->with('error', 'Unexpected response structure');
        }

    } elseif ($status == 3) {
        $info = DB::table('withdraws')->where('id', $id)->first();
        $userid = $info->user_id;
        $amount = $info->amount;

        DB::table('withdraws')->where('id', $id)->update(['status' => $status]);
        DB::table('users')->where('id', $userid)->increment('winning_wallet', $amount);

        return redirect()->back()->with('error', 'Withdraw has been Rejected.');
    } else {
        return redirect()->back()->with('info', 'Withdraw status updated.');
    }
}
    
}