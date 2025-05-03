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
class TraddingResultController extends Controller{
    
// public function index(Request $request)
// {
    
//       $validator = Validator::make($request->all(), [
//         'game_type' => 'required|integer'
//     ]);
//     $validator->stopOnFirstFailure();
//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ], 200);
//     }
//      // Get the game_type parameter (validated)
//     $gameType = $request->input('game_type');

//     // Data fetch with game_type filter applied
//     $data = DB::select(
//         "SELECT `id`, `single`, `double`, `created_at`, `game_type` FROM `tradding_chart_result` WHERE game_type = ?", 
//         [$gameType]
//     );

//     if ($data) {
//         foreach ($data as $item) {

//             // SINGLE LIST Generation if single is not null
//             if ($item->single !== null) {
//                 $single = (int)$item->single;
//                 $single_start = max(0, $single - 2);
//                 $single_end = min(99, $single + 2);

//                 if ($single_end - $single_start < 4) {
//                     if ($single_start == 0) {
//                         $single_end = min(99, $single_start + 4);
//                     } elseif ($single_end == 99) {
//                         $single_start = max(0, $single_end - 4);
//                     }
//                 }
//                 $item->single_list = range($single_start, $single_end);
//             } else {
//                 $item->single_list = []; // Empty array if null
//             }

//             // DOUBLE LIST Generation if double is not null
//             if ($item->double !== null) {
//                 $double = (int)$item->double;
//                 $double_start = max(0, $double - 2);
//                 $double_end = min(99, $double + 2);

//                 if ($double_end - $double_start < 4) {
//                     if ($double_start == 0) {
//                         $double_end = min(99, $double_start + 4);
//                     } elseif ($double_end == 99) {
//                         $double_start = max(0, $double_end - 4);
//                     }
//                 }
//                 $temp_list = range($double_start, $double_end);
//                 // Convert each element to two-digit formatted string
//                 $item->double_list = array_map(function ($n) {
//                     return sprintf("%02d", $n);
//                 }, $temp_list);
//             } else {
//                 $item->double_list = []; // Empty array if null
//             }
            
//             // Conditional removal of list based on game_type
//             // For game_type = 2, single_list ki jarurat nahi
//             if ($item->game_type == 2) {
//                 unset($item->single_list);
//             }
//             // For game_type = 1, double_list ki jarurat nahi
//             if ($item->game_type == 1) {
//                 unset($item->double_list);
//             }
//         }

//         return response()->json([
//             'status' => 200,
//             'data'   => $data,
//         ], 200);
//     } else {
//         return response()->json([
//             'status' => 400,
//             'data'   => []
//         ], 200);
//     }
// }


public function index(Request $request)
{
    // Validate request parameters
    $request->validate([
        'game_type' => 'required',
        'game_id'   => 'required'
    ]);

    $gameType = $request->input('game_type');
    $gameId = $request->input('game_id');

    $data = DB::select(
        "SELECT `id`, `single`, `double`, `created_at`, `game_type` 
         FROM `tradding_chart_result` 
         WHERE game_type = ? AND game_id = ? 
         ORDER BY id DESC", 
        [$gameType, $gameId]
    );

    if ($data) {
        foreach ($data as $item) {

            // Game Type 1: Single
            if ($gameType == 1) {
                $item->number = $item->single !== null ? (int)$item->single : null;

                if ($item->single !== null) {
                    $single = (int)$item->single;

                    $randPos = rand(0, 4);
                    $start = max(0, $single - 10);
                    $end = min(99, $single + 10);

                    $available = range($start, $end);
                    $available = array_diff($available, [$single]);

                    shuffle($available);
                    $selected = array_slice($available, 0, 4);
                    array_splice($selected, $randPos, 0, $single);

                    $item->list = array_values($selected);
                } else {
                    $item->list = [];
                }

                unset($item->single, $item->single_list, $item->double, $item->double_list);
            }

            // Game Type 2: Double
            if ($gameType == 2) {
                $item->number = $item->double !== null ? $item->double : null;

                if ($item->double !== null) {
                    $double = (int)$item->double;

                    $randPos = rand(0, 4);
                    $start = max(0, $double - 10);
                    $end = min(99, $double + 10);

                    $available = range($start, $end);
                    $available = array_diff($available, [$double]);

                    shuffle($available);
                    $selected = array_slice($available, 0, 4);
                    array_splice($selected, $randPos, 0, $double);

                    $item->list = array_map(function ($n) {
                        return sprintf("%02d", $n);
                    }, $selected);
                } else {
                    $item->list = [];
                }

                unset($item->double, $item->double_list, $item->single, $item->single_list);
            }
        }

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'data'   => []
        ], 200);
    }
}


	
	   public function tradding_result_api($gameId){
		//dd($gameId);
		$date = now()->setTimezone('Asia/Kolkata')->toDateString();
		$datetime = now()->setTimezone('Asia/Kolkata');
       
		   
		  $currentDay = now()->setTimezone('Asia/Kolkata')->dayOfWeek;
		  
			if (in_array($currentDay, [6, 0])) {
				return response()->json([
					'status'  => 400,
					'message' => 'Result processing is not allowed on Saturday and Sunday.'
				]);
			}
		   
		// SINGLE RESULT CHECK
		$singleRow = DB::table('tradding_chart_result')
			->whereDate('created_at', $date)
			->where('game_id', $gameId)
			->where('game_type', 1)
			->first();

    if (!$singleRow) {
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

    if ($single_winnumber == '--' || $double_winnumber == '--') {
        return response()->json([
            'status' => false,
            'message' => 'Please check that you’ve added a number for both Single and Double.'
        ]);
    }

    $statusCount = DB::table('tradding_chart_result')
        ->whereDate('created_at', $date)
        ->where('game_id', $gameId)
        ->whereIn('game_type', [1, 2])
        ->where('status', 1)
        ->count();

    if ($statusCount == 2) {
        return response()->json([
            'status' => false,
            'message' => 'Result already processed for today.'
        ]);
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

    return response()->json([
        'status' => true,
        'message' => 'Result processed successfully!'
    ]);
}

	
	     
   

}