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
class GameApiController extends Controller{
  public function live_game() {
    $now = \Carbon\Carbon::now('Asia/Kolkata');
    $currentTime = $now->format('H:i'); // HH:mm format
    $today = \Carbon\Carbon::today('Asia/Kolkata')->toDateString();
    $data = DB::table('game')->get();
    if ($data->isNotEmpty()){
        foreach ($data as $game) {
            $game->game_image = url($game->game_image);
            $game->today_result = null; // Default

            $closeTime = date('H:i', strtotime($game->game_close_time));
            $resultTime = date('H:i', strtotime($game->game_result_time));

            if ($currentTime < $closeTime){
                $game->today_result = null;
            } elseif ($currentTime >= $closeTime && $currentTime < $resultTime) {
                // ⏳ Between close & result time — show placeholder
                $game->today_result = "--";
            } elseif ($currentTime >= $resultTime) {
                // 🕒 After result time — fetch result from DB
                $resultData = DB::table('chart_results')
                    ->where('gamename', $game->live_game)
                    ->whereDate('created_at', $today)
                    ->orderBy('id')
                    ->first();

                if ($resultData) {
                    if ($resultData->modify_result && $resultData->modify_result !== 'XX') {
                        $game->today_result = $resultData->modify_result;
                    } elseif ($resultData->result && $resultData->result !== 'XX') {
                        $game->today_result = $resultData->result;
                    } else {
                        $game->today_result = null;
                    }
                } else {
                    $game->today_result = null;
                }
            }
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

   public function game_list(){
        $data = DB::table('game')->get();
        if ($data->isNotEmpty()) {
            foreach ($data as $game) {
                $game->game_image = url($game->game_image); // Dynamically generate full URL
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
 private function update_betlogs($newSerialNo, $gameid) {
    $list = DB::select("SELECT `number`, `amount` FROM `bets` WHERE `game_id` = :gameid AND `game_serial_no` = :newSerialNo", [
        'gameid' => $gameid,
        'newSerialNo' => $newSerialNo
    ]);

    foreach ($list as $item) {
        $amt = $item->amount;
        $number = $item->number;

        // Use parameterized queries to avoid SQL injection
       DB::update("UPDATE `betlog` SET `amount` = :amount WHERE `game_id` = :gameid AND `game_serial_no` = :newSerialNo AND `number` = :number", [
        'amount' => $amt,
        'gameid' => $gameid,
        'newSerialNo' => $newSerialNo,
        'number' => $number
    ]);

}
}


public function bets(Request $request){
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
        'game_id' => 'required|integer',
        'game_type' => 'required|integer',
        'json' => 'required|string'
    ]);
    $validator->stopOnFirstFailure();
    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $gameid = $request->game_id;
    $game_type = $request->game_type;
    $user_id = $request->user_id;
    $currentTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');

    $betExists = DB::table('game')
        ->where('id', $gameid)
        ->whereRaw("TIME(game_close_time) <= ? AND TIME(game_result_time) >= ?", [$currentTime, $currentTime])
        ->exists();

    if ($betExists) {
        return response()->json([
            'status' => 400,
            'message' => 'Bet Closed Time Out'
        ], 200);
    }

    $bets = json_decode($request->json, true);
    if (!is_array($bets)) {
        return response()->json([
            'status' => 400,
            'message' => "Invalid JSON format"
        ], 200);
    }

    $betsData = [];
    $totalAmount = array_sum(array_column($bets, 'amount'));
    $user = DB::table('users')->where('id', $user_id)->first();
    $walletBalance = $user->wallet ?? 0;
    $bonusBalance = $user->bonus ?? 0;

    if (($walletBalance + $bonusBalance) < $totalAmount) {
        return response()->json([
            'status' => 400,
            'message' => "Insufficient balance"
        ], 200);
    }

    // Deduct from bonus first, then wallet
    $remainingAmount = $totalAmount;
    $bonusUsed = min($bonusBalance, $remainingAmount);
    $remainingAmount -= $bonusUsed;
    $walletUsed = $remainingAmount;

    DB::table('users')->where('id', $user_id)->update([
        'bonus' => $bonusBalance - $bonusUsed,
        'wallet' => $walletBalance - $walletUsed
    ]);

    if ($game_type == 1) {
        $newSerialNo = DB::table('betlog')->where('game_id', $gameid)->max('game_serial_no') ?? 0;
        foreach ($bets as $bet) {
            $number = $bet['number'];
            $amount = $bet['amount'];

            $existingBet = DB::table('betlog')
                ->where('game_id', $gameid)
                ->where('number', $number)
                ->where('game_serial_no', $newSerialNo)
                ->first();

            if ($existingBet) {
                DB::table('betlog')
                    ->where('id', $existingBet->id)
                    ->increment('amount', $amount);
            } else {
                DB::table('betlog')->insert([
                    'game_id' => $gameid,
                    'game_serial_no' => $newSerialNo,
                    'amount' => $amount,
                    'number' => $number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $betsData[] = [
                'user_id' => $user_id,
                'game_id' => $gameid,
                'game_serial_no' => $newSerialNo,
                'amount' => $amount,
                'number' => $number,
                'status' => 1,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
        }

        DB::table('bets')->insert($betsData);
        return response()->json([
            'status' => 200,
            'message' => "Bets placed successfully",
            'last_game_serial_no' => $newSerialNo
        ], 200);
    }

    if ($game_type == 2) {
        $newSerialNo = DB::table('crossing_betlog')->where('game_id', $gameid)->max('game_serial_no') ?? 0;
        foreach ($bets as $bet) {
            $number = $bet['number'];
            $amount = $bet['amount'];

            $existingBet = DB::table('crossing_betlog')
                ->where('game_id', $gameid)
                ->where('number', $number)
                ->first();

            if ($existingBet) {
                DB::table('crossing_betlog')
                    ->where('game_id', $gameid)
                    ->where('number', $number)
                    ->increment('amount', $amount);
            } else {
                DB::table('crossing_betlog')->insert([
                    'game_id' => $gameid,
                    'game_serial_no' => $newSerialNo,
                    'amount' => $amount,
                    'number' => $number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $betsData[] = [
                'user_id' => $user_id,
                'game_id' => $gameid,
                'game_serial_no' => $newSerialNo,
                'amount' => $amount,
                'number' => $number,
                'status' => 1,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
        }

        DB::table('cross_bets')->insert($betsData);
        return response()->json([
            'status' => 200,
            'message' => "Cross bets placed successfully",
            'last_game_serial_no' => $newSerialNo
        ], 200);
    }

    if ($game_type == 3) {
        $newSerialNo = DB::table('andar_bahar_betlog')->where('game_id', $gameid)->max('game_serial_no') ?? 0;
        foreach ($bets as $bet) {
            $number = $bet['number'];
            $amount = $bet['amount'];

            $existingBet = DB::table('andarbahar_bets')
                ->where('game_id', $gameid)
                ->where('number', $number)
                ->first();

            if ($existingBet) {
                DB::table('andar_bahar_betlog')
                    ->where('game_id', $gameid)
                    ->where('number', $number)
                    ->increment('amount', $amount);
            } else {
                DB::table('andar_bahar_betlog')->insert([
                    'game_id' => $gameid,
                    'game_serial_no' => $newSerialNo,
                    'amount' => $amount,
                    'number' => $number,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $betsData[] = [
                'user_id' => $user_id,
                'game_id' => $gameid,
                'game_serial_no' => $newSerialNo,
                'amount' => $amount,
                'number' => $number,
                'status' => 1,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ];
        }

        DB::table('andarbahar_bets')->insert($betsData);
        return response()->json([
            'status' => 200,
            'message' => "Andar bahar bets placed successfully",
            'last_game_serial_no' => $newSerialNo
        ], 200);
    }
}

		public function gameresult(Request $request) {
			$currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d');
			$date = $request->has('date') ? $request->input('date') : $currentDate;

			$data = DB::table('chart_results')
				->select('result', 'gamename', 'yesterday_number', 'updated_at')
				->whereDate('updated_at', $date)
				->whereNotIn('result', ['--', 'XX'])
				->orderBy('updated_at', 'desc')
				->get();

			$gameNames = [
				1 => "MOHALI",
				2 => "ROYAL CHALLENGE",
				3 => "GHAZIABAD",
				4 => "GURGAON",
				5 => "DHAN KUBER",
				6 => "DELHI BAZAR",
				7 => "SHRI GANESH",
				8 => "FARIDABAD",
				9 => "GALI",
				10 => "DESAWAR",
			];

			$gameImages = [
				1 => asset('gamelistimage/Mohali.png'),
				2 => asset('gamelistimage/Royal_Challenge.png'),
				3 => asset('gamelistimage/Ghaziabad.png'),
				4 => asset('gamelistimage/Gurgaon.png'),
				5 => asset('gamelistimage/dhankuber.png'),
				6 => asset('gamelistimage/delhi.png'),
				7 => asset('gamelistimage/shreeganesh.png'),
				8 => asset('gamelistimage/faridabad.png'),
				9 => asset('gamelistimage/gali.png'),
				10 => asset('gamelistimage/Disawar.png'),
			];

			$formattedData = $data->map(function ($item) use ($gameNames, $gameImages) {
				$gameId = array_search($item->gamename, $gameNames) ?: null;
				$image = $gameImages[$gameId] ?? null;
				return array_merge((array)$item, [
					'game_id' => $gameId,
					'image' => $image
				]);
			});

			return response()->json([
				'status' => 200,
				'message' => 'Game results fetched successfully',
				'data' => $formattedData
			]);
		}

   
    public function bethistory(Request $request){
    $data = null;
    if ($request->game_type == 1) {
        $data = DB::table('bets')->where('user_id', $request->user_id)->get();
    } elseif ($request->game_type == 2) {
        $data = DB::table('cross_bets')->where('user_id', $request->user_id)->get();
    } elseif ($request->game_type == 3) {
        $data = DB::table('andarbahar_bets')->where('user_id', $request->user_id)->get();
    }
    if ($data && !$data->isEmpty()) {
        // Add game_type and live_game
        $data = $data->map(function ($item) use ($request) {
            $item->game_type = $request->game_type;
            // Get live_game from games table
            $liveGame = DB::table('game')->where('id', $item->game_id)->value('live_game');
            $item->live_game = $liveGame;
            return $item;
        });
        return response()->json([
            'status' => 200,
            'data' => $data
        ], 200);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'No bet history found.',
            'data' => null
        ], 200);
    }
 }

}



