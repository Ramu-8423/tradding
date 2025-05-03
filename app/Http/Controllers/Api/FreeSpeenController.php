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
class FreeSpeenController extends Controller{
     
    public function speen_list($user_id){
    $data = DB::table('free_spin')->get();
	$now = Carbon::now('Asia/Kolkata');
	$startOfDay = $now->copy()->startOfDay(); // 00:00:00
	$endOfDay = $now->copy()->endOfDay();     // 23:59:59
	$alreadyClaimed = DB::table('spin_claim')
		->where('user_id', $user_id)
		->whereBetween('created_at', [$startOfDay, $endOfDay])
		->exists();
    if ($alreadyClaimed) {
        return response()->json([
            'status' => 400,
            'message' => 'Better luck next day!',
            'data' => []
        ], 200);
    }
    return response()->json([
        'status' => 200,
        'data' => $data
    ], 200);

}

    public function spin_opration(Request $request){
    $validator = Validator::make($request->all(), [
        'spin_id' => 'required|',
        'user_id' => 'required|'
    ])->stopOnFirstFailure();
     
		 
		
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 200);
    }

		
	
		
		
    $now = Carbon::now('Asia/Kolkata'); // full timestamp

    if ($request->spin_id != 1) {
        $spin_amount = DB::table('free_spin')
            ->where('id', $request->spin_id)
            ->value('amount');

        // Insert into spin_claim
        DB::table('spin_claim')->insert([
            "user_id"    => $request->user_id,
            "spin_id"    => $request->spin_id,
            "amount"     => $spin_amount,
            "created_at" => $now // FULL TIMESTAMP
        ]);

        // Insert into wallet history
        DB::table('wallet_histories')->insert([
            "user_id"    => $request->user_id,
            "type_id"    => 29,
            "amount"     => $spin_amount,
            "description"=> "Spin bonus",
            "created_at" => $now
        ]);

        // Increment bonus wallet
        DB::table('users')->where('id', $request->user_id)
            ->increment('bonus', $spin_amount);
    }

    return response()->json([
        'status' => 200,
        'message' => "Incremented spin win amount"
    ], 200);
}

	
	
}