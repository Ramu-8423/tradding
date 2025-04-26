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
class TraddingController extends Controller{
	
	public function tradding_list(){
    $now = \Carbon\Carbon::now('Asia/Kolkata');
    $currentTime = $now->format('H:i');
    $today = \Carbon\Carbon::today('Asia/Kolkata')->toDateString();

    $data = DB::table('tradding_games')->get();
		
    if ($data->isNotEmpty()) {
        foreach ($data as $game) {
            $game->game_image = url($game->game_image);

            $closeTime = date('H:i', strtotime($game->game_close_time));
            $resultTime = date('H:i', strtotime($game->game_result_time));

            // Default null
            $game->single_result = null;
            $game->double_result = null;

            // 🟡 Between close and result time → show "--"
            if ($currentTime >= $closeTime && $currentTime < $resultTime) {
                $game->single_result = "--";
                $game->double_result = "--";
            }

            // 🟢 After result time → try to fetch result from table
            elseif ($currentTime >= $resultTime) {
                $singleResult = DB::table('tradding_chart_result')
                    ->where('game_id', $game->id)
                    ->where('game_type', 1)
                    ->whereDate('created_at', $today)
                    ->orderByDesc('id')
                    ->value('single');

                $doubleResult = DB::table('tradding_chart_result')
                    ->where('game_id', $game->id)
                    ->where('game_type', 2)
                    ->whereDate('created_at', $today)
                    ->orderByDesc('id')
                    ->value('double');

                $game->single_result = $singleResult; // null if not found
                $game->double_result = $doubleResult;
            }

            // 🔴 Before close time → both results null (default)
        }

        return response()->json([
            'status' => 200,
            'data' => $data,
        ]);
    } else {
        return response()->json([
            'status' => 400,
            'data' => [],
        ]);
    }
}




    public function live_tradding(){
        $data = DB::table('tradding_games')->get();
        if ($data->isNotEmpty()) {
            foreach ($data as $game) {
                $game->game_image = url($game->game_image); 
            }
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'data' => []
            ], 200);
        }
    }
    
   public function live_tradding_bets(Request $request){
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
        'game_id' => 'required|integer',
        'game_type' => 'required|integer',
        'json' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    $user_id = $request->user_id;
    $gameid = $request->game_id;
    $game_type = $request->game_type;
    $currentTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');

	// ✅ Check if current time is between (game_close_time - 10 mins) and game_result_time
	$isClosed = DB::table('tradding_games')
		->where('id', $gameid)
		->whereRaw("TIME(game_close_time - INTERVAL 10 MINUTE) <= ? AND TIME(game_result_time) >= ?", [$currentTime, $currentTime])
		->exists();

	if ($isClosed) {
		return response()->json([
			'status' => 400,
			'message' => 'Bet Closed Time Out'
		]);
	}

    // ✅ Decode bets JSON
    $bets = json_decode($request->json, true);
    if (!is_array($bets)) {
        return response()->json([
            'status' => 400,
            'message' => "Invalid JSON format"
        ]);
    }

    // 💰 Check Wallet (Modified to include bonus)
    $totalAmount = array_sum(array_column($bets, 'amount'));
    $user = DB::table('users')->where('id', $user_id)->first();

    $wallet = $user->wallet ?? 0;
    $bonus = $user->bonus ?? 0;

    if (($wallet + $bonus) < $totalAmount) {
        return response()->json([
            'status' => 400,
            'message' => "Insufficient balance"
        ]);
    }

    // 💳 Deduct bonus first, then wallet
    $bonusToDeduct = min($bonus, $totalAmount);
    $walletToDeduct = $totalAmount - $bonusToDeduct;

    DB::table('users')->where('id', $user_id)->update([
        'bonus' => DB::raw("bonus - $bonusToDeduct"),
        'wallet' => DB::raw("wallet - $walletToDeduct")
    ]);
                                                                 
    // 🔁 Get latest serial no
    $newSerialNo = DB::table('tradding_betlog')
        ->where('game_id', $gameid)->where('game_type', $game_type)
        ->max('game_serial_no') ?? 0;

    $betsData = [];

    foreach ($bets as $bet) {
        $number = $bet['number'];
        $amount = $bet['amount'];

        // 🔁 Update tradding_betlog
        $existing = DB::table('tradding_betlog')
            ->where('game_id', $gameid)
            ->where('game_type', $game_type)
            ->where('number', $number)
            ->where('game_serial_no', $newSerialNo)
            ->first();

        if ($existing) {
            DB::table('tradding_betlog')
                ->where('id', $existing->id)
                ->increment('amount', $amount);
        } else {
            DB::table('tradding_betlog')->insert([
                'game_id' => $gameid,
                'game_type' => $game_type,
                'number' => $number,
                'game_serial_no' => $newSerialNo,
                'amount' => $amount,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 📥 Insert to tradding_bets
        $betsData[] = [
            'user_id' => $user_id,
            'game_id' => $gameid,
            'game_type' => $game_type,
            'game_serial_no' => $newSerialNo,
            'amount' => $amount,
            'win_amount' => 0,
            'number' => $number,
            'win_number' => null,
            'status' => 1, // 1 = pending
            'created_at' => $currentTime,
            'updated_at' => $currentTime
        ];
    }

    DB::table('tradding_bets')->insert($betsData);

    return response()->json([
        'status' => 200,
        'message' => "Bets placed successfully",
        'last_game_serial_no' => $newSerialNo
    ]);
}

     
        public function tradding_bets_result(Request $request){
            $validator = Validator::make($request->all(), [
                'user_id'   => 'required|integer',
                'game_type' => 'required|integer',
                'game_id'   => 'required|integer',
            ]);
        
            $validator->stopOnFirstFailure();
        
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 400,
                    'message' => $validator->errors()->first()
                ], 200);
            }
        
            // Game ID => Game Name Mapping
            $gameNames = [
                1 => 'Kospi (South Korea)',
                2 => 'Hang Seng (Hongkong)',
                3 => 'Dax (Germany)',
                4 => 'BSE Sensex',
                5 => 'Nifty 50',
                6 => 'Shanghai Stock Exc (SSE)',
                7 => 'Shanghai Stock Exc (SZSE)',
            ];
			
            $data = DB::table('tradding_bets')
					->where('user_id', $request->user_id)
					->where('game_type', $request->game_type)
					->where('game_id', $request->game_id)
					->orderBy('id', 'desc')
					->get();
		  
            if (!$data->isEmpty()) {
                // Append game_name to each record
                $modifiedData = $data->map(function ($item) use ($gameNames) {
                    $item->game_name = $gameNames[$item->game_id] ?? 'Unknown';
                    return $item;
                });
        //dd($modifiedData);
                return response()->json([
                    'status' => 200,
                    'data'   => $modifiedData,
                ], 200);
            } else {
                return response()->json([
                    'status' => 400,
                    'data'   => []
                ], 200);
            }
        }

        public function tradding_all_result($id){
        // Game ID => Game Name Mapping
        $gameNames = [
            1 => 'Kospi (South Korea)',
            2 => 'Hang Seng (Hongkong)',
            3 => 'Dax (Germany)',
            4 => 'BSE Sensex',
            5 => 'Nifty 50',
            6 => 'Shanghai Stock Exc (SSE)',
            7 => 'Shanghai Stock Exc (SZSE)',
        ];
    
        $data = DB::table('tradding_bets')->where('user_id', $id)->get();
    
        if (!$data->isEmpty()) {
            // Append game_name to each record
            $modifiedData = $data->map(function ($item) use ($gameNames) {
                $item->game_name = $gameNames[$item->game_id] ?? 'Unknown';
                return $item;
            });
    
            return response()->json([
                'status' => 200,
                'data'   => $modifiedData,
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'data'   => []
            ], 200);
        }
    }
      
  
  
  
  
 public function every_day_result(Request $request){
    // Set timezone
    $timezone = 'Asia/Kolkata';

    // Get current date if not passed in request
    $dateInput = $request->input('date');
    $start = $dateInput 
        ? Carbon::createFromFormat('Y-m-d', $dateInput, $timezone)->startOfDay()
        : Carbon::now($timezone)->startOfDay();

    $end = $start->copy()->endOfDay();

    // Fetch data between start and end of the day
    $data = DB::table('tradding_chart_result')
        ->select('single', 'double', 'game_id', 'game_type', 'created_at')
        ->whereBetween('created_at', [$start, $end])
        ->get();

    // Game names mapping
    $gameNames = [
        1 => 'Kospi (South Korea)',
        2 => 'Hang Seng (Hongkong)',
        3 => 'Dax (Germany)',
        4 => 'BSE Sensex',
        5 => 'Nifty 50',
        6 => 'Shanghai Stock Exc (SSE)',
        7 => 'Shanghai Stock Exc (SZSE)',
    ];

    // Game image filenames mapping (only path, base handled by asset())
    $gameImagePaths = [
        1 => 'tradding/cospi.png',
        2 => 'tradding/hang.png',
        3 => 'tradding/dax.png',
        4 => 'tradding/bse.png',
        5 => 'tradding/nifty.png',
        6 => 'tradding/sse.png',
        7 => 'tradding/szse.png',
    ];

    // Format response data
    $formattedData = $data->map(function ($item) use ($gameNames, $gameImagePaths) {
        $item->game_name = $gameNames[$item->game_id] ?? 'Unknown';
        $item->game_image = isset($gameImagePaths[$item->game_id]) ? asset($gameImagePaths[$item->game_id]) : null;
        $item->game_type_name = $item->game_type == 1 ? 'Single' : ($item->game_type == 2 ? 'Double' : 'Unknown');
        return $item;
    });

    // Return as JSON response
    return response()->json([
        'status' => 200,
        'message' => 'Game results fetched successfully',
        'data' => $formattedData
    ]);
}


      
      
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  

}