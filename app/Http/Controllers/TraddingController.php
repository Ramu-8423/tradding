<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class TraddingController extends Controller{
	
	
       public function add_result(Request $request){
        $todayStart = now()->setTimezone('Asia/Kolkata')->startOfDay()->format('Y-m-d H:i:s');
        $result = DB::table('tradding_chart_result')
                    ->where('created_at', '>=', $todayStart)
                    ->get();
		$tradding_games = DB::table('tradding_games')->select('id','live_game', 'game_close_time','game_result_time')->get();
		  // dd($tradding_games);
        return view('TraddingResult.TraddingResult')->with('result', $result)->with('tradding_games', $tradding_games);
    }
  
 public function result_announce(Request $request){

    $gameType = $request->game_type;
    $gameId = $request->game_id;
    $number = $request->number;
    
    $insert_date =  now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');
    $date = now()->setTimezone('Asia/Kolkata')->toDateString();
  
  $exists = DB::table('tradding_chart_result')
        ->where('game_type', $gameType)
        ->where('game_id', $gameId)
        ->whereDate('created_at', $date)
        ->exists();
        
		if ($exists) {
			return redirect()->back()->with('error', 'Result already placed for today!');
		}
   
		$data = [
				'game_type' => $gameType,
				'game_id' => $gameId,
				'single' => $gameType == 1 ? $number : null,
				'double' => $gameType == 2 ? $number : null,
				'created_at' => $insert_date,
				'updated_at' => $insert_date,
			];
			//dd($number, $data);
     DB::table('tradding_chart_result')->insert($data);
    if($gameType == 1){
         $winnumber = DB::table('tradding_chart_result')->whereDate('created_at', $date)
			 ->where('game_id', $gameId)
			 ->where('game_type', $gameType)
			 ->value('single');
         $amount_multiplayer = 7;
    }
    if($gameType == 2){
        $winnumber = DB::table('tradding_chart_result')->whereDate('created_at', $date)
			->where('game_id', $gameId)
			->where('game_type', $gameType)
			->value('double');
        $amount_multiplayer = 70;
    }
	// dd($winnumber, $gameId);
     $currentSerialNo = DB::table('tradding_bets')
    ->where('game_id', $gameId)
    ->where('game_type', $gameType)
    ->max('game_serial_no');
     $newSerialNo = $currentSerialNo + 1;
    //dd($currentSerialNo);
     $bets = DB::table('tradding_bets')->where('game_id', $gameId)->where('game_type', $gameType)->where('game_serial_no', $currentSerialNo)->get();
	 //dd($bets);
       //dd($winnumber);
       foreach ($bets as $bet) {
                $isWinner = ($bet->number == $winnumber);
                $winAmount = $isWinner ? ($bet->amount * $amount_multiplayer) : 0;
                //dd($winAmount);
                DB::table('tradding_bets')
                    ->where('id', $bet->id)
                    ->update([
                        'win_amount'  => $winAmount,
                        'win_number'  => $winnumber,
                        'status'      => $isWinner ? 2 : 3, // 2 = Win, 3 = Loss
                        'updated_at'  => now()
                    ]);
                    
                if ($isWinner && $winAmount > 0) {
                    DB::table('users')->where('id', $bet->user_id)->increment('winning_wallet', $winAmount);
                }
            }
            
           DB::table('tradding_betlog')
                ->where('game_id', $gameId)->where('game_type', $gameType)
                ->update([
                    'amount' => 0,
                    'game_serial_no' => $newSerialNo
                ]);
     
     
    return redirect()->back()->with('success', 'Result added successfully!');
}



       public function tradding_betlog(Request $request, $game_type){
           // dd($game_type);
                $query = DB::table('tradding_betlog')->where('game_type', $game_type)->where('amount', '>', 0);
            if ($request->has('game_id')) {
                $query->where('game_id', $request->game_id);
            }
        
            $betlogs = $query->orderBy('amount', 'desc')->get();
              // dd($betlogs);
           // dd($betlogs);
            return view('TraddingResult.Tradding_betlog', [
                'betlogs' => $betlogs,
                'game_type' => $game_type,
            ]);
        }

}