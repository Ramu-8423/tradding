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
use Illuminate\Support\Facades\Log; 


class TraddingController extends Controller{
	
	
       public function add_result(Request $request){
        $todayStart = now()->setTimezone('Asia/Kolkata')->startOfDay()->format('Y-m-d H:i:s');
		$current_time =  now()->setTimezone('Asia/Kolkata')->format('H:i:s');
  
        $result = DB::table('tradding_chart_result as tcr')
			->join('tradding_games as tg', 'tcr.game_id', '=', 'tg.id')
			->where('tcr.created_at', '>=', $todayStart)
			->orderByRaw('TIME(tg.game_result_time) DESC')
			 ->select('tcr.*', DB::raw('TIME(tg.game_result_time) as result_time'))
			->get();
		 //  dd($result, $current_time);
		$tradding_games = DB::table('tradding_games')->select('id','live_game', 'game_close_time','game_result_time')
			
			->orderByRaw('TIME(game_result_time) ASC')->get();
		   
        return view('TraddingResult.TraddingResult')
			->with('result', $result)
				->with('tradding_games', $tradding_games)
				->with('current_time', $current_time);
		}
	
	    public function edit($id){
		// Result fetch karo DB se
		$result = DB::table('tradding_chart_result')->where('id', $id)->first();
		$ststus = $result->status;
			if($ststus ==1){
				return redirect()->back()->with('error', 'You can’t update — result already placed');
			}
				
		$tradding_games = DB::table('tradding_games')->get();
		if (!$result) {
			abort(404); // Not found error
		}
			
		return view('TraddingResult.Tradding_update', compact('result', 'tradding_games'));
	}

	
	
	   public function update(Request $request, $id){
		$request->validate([
			'game_type' => 'required|in:1,2',
			'game_id' => 'required|integer',
		]);
		$number = $request->game_type == 1 ? $request->single_number : $request->double_number;
		DB::table('tradding_chart_result')->where('id', $id)->update([
			'game_type' => $request->game_type,
			'game_id' => $request->game_id,
			'single' => $request->game_type == 1 ? $number : null,
			'double' => $request->game_type == 2 ? $number : null,
			'updated_at' => now(),
		]);
		return redirect()->route('add_result')->with('success', 'Result updated successfully!');
		}
	
	 public function result_announce(Request $request){
		 
		 $currentDay = now()->setTimezone('Asia/Kolkata')->dayOfWeek;
			if (in_array($currentDay, [6, 0])) {
				return redirect()->back()->with('error', 'Result processing is not allowed on Saturday and Sunday.');
			} 
		 
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
		  return redirect()->back()->with('success', 'Result add successfully!');
}

   
	  public function tradding_result_cron($gameId){
		  
		$date = now()->setTimezone('Asia/Kolkata')->toDateString();
		$datetime = now()->setTimezone('Asia/Kolkata');
        if($datetime->isWeekend()){
			return view('your-view-name')->with('message', 'Saturday & Sunday Bet Closed');
		}
    // SINGLE RESULT CHECK
    $singleRow = DB::table('tradding_chart_result')
        ->whereDate('created_at', $date)
        ->where('game_id', $gameId)
        ->where('game_type', 1)
        ->first();
  
    if (!$singleRow) {
        // Insert only if row doesn't exist
        DB::table('tradding_chart_result')->insert([
            'game_id' => $gameId,
            'game_type' => 1,
            'single' => '--',
            'comment' => 'No Result Proceed Manually',
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);
        $single_winnumber = '--';
    } else {
        $single_winnumber = $singleRow->single;
    }

    // DOUBLE RESULT CHECK
    $doubleRow = DB::table('tradding_chart_result')
        ->whereDate('created_at', $date)
        ->where('game_id', $gameId)
        ->where('game_type', 2)
        ->first();

    if (!$doubleRow) {
        // Insert only if row doesn't exist
        DB::table('tradding_chart_result')->insert([
            'game_id' => $gameId,
            'game_type' => 2,
            'double' => '--',
            'comment' => 'No Result Proceed Manually',
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);
        $double_winnumber = '--';
    } else {
        $double_winnumber = $doubleRow->double;
    }
		


    // If either value is still '--', return error
    if ($single_winnumber == '--' || $double_winnumber == '--') {
		 return redirect()->back()->with('error', 'Please check that you’ve added a number for both Single and Double.');
    }
		  
		  $statusCount = DB::table('tradding_chart_result')
			->whereDate('created_at', $date)
			->where('game_id', $gameId)
			->whereIn('game_type', [1, 2])
			->where('status', 1)
			->count();
		  
		  if ($statusCount == 2) {
			   return redirect()->back()->with('error', 'Result already processed For today.');
			}
		  DB::table('tradding_chart_result')
			->whereDate('created_at', $date)
			->where('game_id', $gameId)
			->whereIn('game_type', [1, 2])
			->update([
				'status' => 1,
				'comment' => null,
				'created_at' => $datetime,
                'updated_at' => $datetime,
		    	]);
		  
		$amount_multiplier_single = 8;
		$amount_multiplier_double = 80;
  
    $single_currentSerialNo = DB::table('tradding_bets')
        ->where('game_id', $gameId)
        ->where('game_type', 1)
        ->max('game_serial_no');
    
    $single_bets = DB::table('tradding_bets')
        ->where('game_id', $gameId)
        ->where('game_type', 1)
        ->where('game_serial_no', $single_currentSerialNo)
        ->get();

    foreach ($single_bets as $bet) {
        $isWinner = ($bet->number == $single_winnumber);
        $winAmount = $isWinner ? ($bet->amount * $amount_multiplier_single) : 0;

        DB::table('tradding_bets')
            ->where('id', $bet->id)
            ->update([
                'win_amount' => $winAmount,
                'win_number' => $single_winnumber,
                'status'     => $isWinner ? 2 : 3,
                'updated_at' => now()
            ]);

        if ($isWinner && $winAmount > 0) {
            DB::table('users')
                ->where('id', $bet->user_id)
                ->increment('winning_wallet', $winAmount);
        }
    }

    // Double bets
    $double_currentSerialNo = DB::table('tradding_bets')
        ->where('game_id', $gameId)
        ->where('game_type', 2)
        ->max('game_serial_no');
    
    $double_bets = DB::table('tradding_bets')
        ->where('game_id', $gameId)
        ->where('game_type', 2)
        ->where('game_serial_no', $double_currentSerialNo)
        ->get();

    foreach ($double_bets as $bet) {
        $isWinner = ($bet->number == $double_winnumber);
        $winAmount = $isWinner ? ($bet->amount * $amount_multiplier_double) : 0;

        DB::table('tradding_bets')
            ->where('id', $bet->id)
            ->update([
                'win_amount' => $winAmount,
                'win_number' => $double_winnumber,
                'status'     => $isWinner ? 2 : 3,
                'updated_at' => now()
            ]);

        if ($isWinner && $winAmount > 0) {
            DB::table('users')
                ->where('id', $bet->user_id)
                ->increment('winning_wallet', $winAmount);
        }
    }

    // Betlog update karna for next round
    DB::table('tradding_betlog')
        ->where('game_id', $gameId)
        ->where('game_type', 1)
        ->update([
            'amount' => 0,
            'game_serial_no' => $single_currentSerialNo + 1
        ]);

    DB::table('tradding_betlog')
        ->where('game_id', $gameId)
        ->where('game_type', 2)
        ->update([
            'amount' => 0,
            'game_serial_no' => $double_currentSerialNo + 1
        ]);
     return redirect()->back()->with('message', 'Result processed successfully!');
}

	
	
       public function tradding_betlog(Request $request, $game_type){
           // dd($game_type);
                $query = DB::table('tradding_betlog')->where('game_type', $game_type)->where('amount', '>', 0);
            if ($request->has('game_id')) {
                $query->where('game_id', $request->game_id);
            }
        
            $betlogs = $query->orderBy('amount', 'desc')->get();
            return view('TraddingResult.Tradding_betlog', [
                'betlogs' => $betlogs,
                'game_type' => $game_type,
            ]);
        }
	
	
	
	
	   
	
	
	
	
	
	
	

}