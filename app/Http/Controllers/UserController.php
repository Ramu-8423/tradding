<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class UserController extends Controller
{
    
public function users($status) {
    $data = DB::table('users')
              ->where('status', $status)
              ->orderBy('id', 'desc')
              ->get();

    return view('user.users')
           ->with('data', $data)
           ->with('status', $status)
           ->with('role_id', 4);
}

public function vendor($status) {
    $data = DB::table('users')
              ->where('status', $status)
              ->whereIn('role_id', [2,3])
              ->orderBy('id', 'desc')
              ->get();
 
    return view('user.users')
           ->with('data', $data)
           ->with('status', $status)
           ->with('role_id', 2);
}


public function toggle_status(Request $request) {
    
     $message = ($request->status == 1) ? "User activated successfully!" : "User deactivated successfully!";
     
    $data = DB::table('users')->where('id', $request->id)->update([
        "status" => $request->status
        ]);
    return back()->with([
    'message' => $message,
    'status' => $request->status // Alert color ke liye status bhi pass kar rahe hain
]);
   
}
  public function wallte_operation(Request $request){
    $request->validate([
        'modify_result' => 'required|numeric',
    ]);

    $modify_result = $request->modify_result;
    $type = $request->type;
    $id = $request->id;  // type = 1 means add and 2 means deduct

    if ($type == 1) {
        DB::table('users')->where('id', $id)->increment('wallet', $modify_result);
		DB::table('wallet_histories')->insert([
							"user_id" => $id,
							"amount"  => $modify_result,
							"type_id" => 35,
							"description" => "Added By Admin",
						]);
        $message = "₹ $modify_result added successfully.";
        return back()->with('success', $message);  // or return response()->json(...) if API
    }

    if ($type == 2) {
        $currentWallet = DB::table('users')->where('id', $id)->value('wallet');

        if ($currentWallet < $modify_result) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        DB::table('users')->where('id', $id)->decrement('wallet', $modify_result);
		DB::table('wallet_histories')->insert([
							"user_id" => $id,
							"amount"  => $modify_result,
							"type_id" => 36,
							"description" => "Deducted By Admin",
						]);
        $message = "₹ $modify_result deducted successfully.";
        return back()->with('success', $message);
    }

    return back()->with('error', 'Invalid operation type.');
}

   public function users_activity(Request $request , $userid){
      $game_type = $request->game_type;
      $paying = DB::table('payins')->where('user_id' , $userid)->select('amount','transaction_id','status','created_at')->orderByDesc('created_at')->get();
      $withdraws = DB::table('withdraw_histories')->where('user_id' , $userid)->select('amount','order_id','status','created_at')->orderByDesc('created_at')->get();
      if($request->game_type == 1){
          $bets = DB::table('bets')->where('user_id' , $userid)->select('id','game_id','amount','status','number','win_amount','created_at', DB::raw("'Jodi' as game_type"))->orderByDesc('created_at')->get();
      }
      if($request->game_type == 2){
          $bets = DB::table('cross_bets')->where('user_id' , $userid)->select('id','game_id','amount','status','number','win_amount','created_at',  DB::raw("'Crossing' as game_type"))->orderByDesc('created_at')->get();
      }
      if($request->game_type == 3){
          $bets = DB::table('andarbahar_bets')->where('user_id' , $userid)->select('id','game_id','amount','status','number','win_amount','created_at',  DB::raw("'Andar bahar' as game_type"))->orderByDesc('created_at')->get();
      }
      if($request->game_type == null){
       $b1 = DB::table('bets')
                ->where('user_id', $userid)
                ->select('id', 'game_id', 'amount', 'status', 'number', 'win_amount', 'created_at', DB::raw("'jodi' as game_type"));
            
            $b2 = DB::table('cross_bets')
                ->where('user_id', $userid)
                ->select('id', 'game_id', 'amount', 'status', 'number', 'win_amount', 'created_at', DB::raw("'cross' as game_type"));
            
            $b3 = DB::table('andarbahar_bets')
                ->where('user_id', $userid)
                ->select('id', 'game_id', 'amount', 'status', 'number', 'win_amount', 'created_at', DB::raw("'andarbahar' as game_type"));
            
            $bets = $b1->unionAll($b2)->unionAll($b3)->get();
             $bets = $bets->sortByDesc('created_at')->values();
      }
      //dd($paying,$withdraws,$bets,$cross_bets,$andarbahar_bets);
      return view('user.Activity', compact('paying', 'withdraws', 'bets','userid', 'game_type'));
 
   }

}



