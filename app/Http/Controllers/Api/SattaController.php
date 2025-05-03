<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use KubAT\PhpSimple\HtmlDomParser;
use Carbon\Carbon;
use DB;

class SattaController extends Controller{
   public function chart_result($id){
		  $allowedGames = [
				"MOHALI", "ROYAL CHALLENGE", "GHAZIABAD",
				"GURGAON", "DHAN KUBER", "DELHI BAZAR", "SHRI GANESH",
				"FARIDABAD", "GALI", "DESAWAR"
			];

			$url = "https://satta-king-fast.com/index.php";
			$context = stream_context_create(["http" => ["timeout" => 10]]);
			$html = @file_get_contents($url, false, $context);

			if (!$html) {
				return response()->json(['status' => false, 'message' => 'Website not accessible']);
			}

			$dom = HtmlDomParser::str_get_html($html);
			if (!$dom) {
				return response()->json(['status' => false, 'message' => 'Failed to parse HTML']);
			}

			$date = now()->setTimezone('Asia/Kolkata')->toDateString();
			$insertedData = [];

			foreach ($dom->find(".game-result") as $gameResult) {
				$gameNameElement = $gameResult->find(".game-name", 0);
				$gameTimeElement = $gameResult->find(".game-time", 0);
				$todayNumberElement = $gameResult->find(".today-number", 0);
				$yesterdayNumberElement = $gameResult->find(".yesterday-number", 0);

				if (!$gameNameElement || !$gameTimeElement || !$todayNumberElement || !$yesterdayNumberElement) {
					continue;
				}

				$gameName = trim($gameNameElement->plaintext);
				$gameTime = trim($gameTimeElement->plaintext);
				$todayNumber = trim($todayNumberElement->plaintext);
				$yesterdayNumber = trim($yesterdayNumberElement->plaintext);

				if(in_array($gameName, $allowedGames)) {
					$existingEntry = DB::table('chart_results')
						->where('gamename', $gameName)
						->whereDate('date', $date)
						->first();

					if ($existingEntry) {
						$existingResult = $existingEntry->result;
						// ✅ Sirf tabhi update hoga jab pehle se 'XX' ho
						if ($existingResult === 'XX') {
							DB::table('chart_results')
								->where('id', $existingEntry->id)
								->update([
									'result' => $todayNumber,
									'updated_at' => Carbon::now(),
								]);
						}

						// ❌ Agar pehle '--' ya number hai, toh kuch nahi hoga (no update)
					} else {
						// ✅ Pehle baar insert sab kuch ho jayega (chahe XX ho, -- ho, ya number)
						DB::table('chart_results')->insert([
							'gamename' => $gameName,
							'date' => $date,
							'result_time' => $gameTime,
							'yesterday_number' => $yesterdayNumber,
							'result' => $todayNumber,
							'rds' => 1,
							'created_at' => $date,
							'updated_at' => $date,
						]);
					}

					$insertedData[] = ['game' => $gameName, 'result' => $todayNumber];
				}
			}


	   
	   
	       $gameNamess = [
                            1 => "MOHALI",
                            2 => "ROYAL CHALLENGE",
                            3 => "GHAZIABAD",
                            4 => "GURGAON",
                            5 => "DHAN KUBER",
                            6 => "DELHI BAZAR",
                            7 => "SHRI GANESH",
                            8 => "FARIDABAD",
                            9 => "GALI",
                            10 => "DESAWAR"
                        ];
                        $gameNameID = $gameNamess[$id] ?? "Unknown Game";
		
          
            $numberwins =  DB::table('chart_results')->whereDate('date', $date)->where('gamename', $gameNameID)->value('result');
	        $adminnumber = DB::table('chart_results')->whereDate('date', $date)->where('gamename', $gameNameID)->value('modify_result');
	   
             if (in_array($numberwins, ['--', 'XX']) && is_null($adminnumber)){ 
				 DB::table('chart_results')
						->whereDate('date', $date)
						->where('gamename', $gameNameID)
						->update([
							"comment" => "Result not found on website during bet processing"
						]);
					return response()->json([
						'status' => false,
						'message' => 'Result not available yet. Please try again later.',
					]);
				
				}
	   
            $winningNumber = $adminnumber ?? $numberwins;
	   
	   
            $currentSerialNo = DB::table('betlog')->where('game_id', $id)->value('game_serial_no') ?? 1;
            $newSerialNo = $currentSerialNo + 1;
            $bets = DB::table('bets')->where('game_id', $id)->where('game_serial_no', $currentSerialNo)->get();
           
            foreach ($bets as $bet) {
                $isWinner = ($bet->number == $winningNumber);
                $winAmount = $isWinner ? ($bet->amount * 9) : 0;
        
                DB::table('bets')
                    ->where('id', $bet->id)
                    ->update([
                        'win_amount'  => $winAmount,
                        'win_number'  => $winningNumber,
                        'status'      => $isWinner ? 2 : 3, // 2 = Win, 3 = Loss
                        'updated_at'  => now()
                    ]);
                    
                if ($isWinner && $winAmount > 0) {
                    DB::table('users')->where('id', $bet->user_id)->increment('winning_wallet', $winAmount);
                }
            }
            DB::table('betlog')
                ->where('game_id', $id)
                ->update([
                    'amount' => 0,
                    'game_serial_no' => $newSerialNo
                ]);
       
            // cross beting for game
            $currentSerialNo = DB::table('crossing_betlog')->where('game_id', $id)->value('game_serial_no') ?? 1;
            $newSerialNo = $currentSerialNo + 1;
            $cross_bets = DB::table('cross_bets')->where('game_id', $id)->where('game_serial_no', $currentSerialNo)->get();
            $numberwins =  DB::table('chart_results')->whereDate('date', $date)->where('gamename', $gameNameID)->value('result');
    
            $winningNumber = $adminnumber ?? $numberwins;
           // dd($cross_bets);
            foreach ($cross_bets as $betss) {
                $isWinners = ($betss->number == $winningNumber);
                $winAmounts = $isWinners ? ($betss->amount * 9) : 0;
                DB::table('cross_bets')
                    ->where('id', $betss->id)
                    ->update([
                        'win_amount'  => $winAmounts,
                        'win_number'  => $winningNumber,
                        'status'      => $isWinners ? 2 : 3, // 2 = Win, 3 = Loss
                        'updated_at'  => now()
                    ]);
                    
                if ($isWinners && $winAmounts > 0) {
                    DB::table('users')->where('id', $betss->user_id)->increment('winning_wallet', $winAmounts);
                }
            }
            DB::table('crossing_betlog')
                ->where('game_id', $id)
                ->update([
                    'amount' => 0,
                    'game_serial_no' => $newSerialNo
                ]);
                
         
            $currentSerialNo = DB::table('andar_bahar_betlog')->where('game_id', $id)->value('game_serial_no') ?? 1;
            $newSerialNo = $currentSerialNo + 1;
            $andarbahar_bets = DB::table('andarbahar_bets')->where('game_id', $id)->where('game_serial_no', $currentSerialNo)->get();
            $numberwins =  DB::table('chart_results')->whereDate('date', $date)->where('gamename', $gameNameID)->value('result');
    
    
            $winningNumber = $adminnumber ?? $numberwins;
           // dd($cross_bets);
            foreach ($andarbahar_bets as $betss) {
                $isWinners = ($betss->number == $winningNumber);
                $winAmounts = $isWinners ? ($betss->amount * 9) : 0;
                DB::table('andarbahar_bets')
                    ->where('id', $betss->id)
                    ->update([
                        'win_amount'  => $winAmounts,
                        'win_number'  => $winningNumber,
                        'status'      => $isWinners ? 2 : 3, // 2 = Win, 3 = Loss
                        'updated_at'  => now()
                    ]);
                    
                if ($isWinners && $winAmounts > 0) {
                    DB::table('users')->where('id', $betss->user_id)->increment('winning_wallet', $winAmounts);
                }
            }
            DB::table('andar_bahar_betlog')
                ->where('game_id', $id)
                ->update([
                    'amount' => 0,
                    'game_serial_no' => $newSerialNo
                ]);
            return response()->json([
                'status' => 200,
                'message' => "Winners decided successfully",
                'game_id' => $id,
                'winning_number' => $winningNumber,
                'new_game_serial_no' => $newSerialNo
				]);

	}   
	
		public function manual_result(Request $request)
		{
			$id = $request->id;
			$result = $request->result;
             if (!is_numeric($result) || strlen($result) !== 2) {
				  return back()->with('error', 'The result must be a 2-digit number.');
			}
			$game_info = DB::table('chart_results')->where('id', $id)->first();
			$gamename = $game_info->gamename;

			$gameNamess = [
				1 => "MOHALI",
				2 => "ROYAL CHALLENGE",
				3 => "GHAZIABAD",
				4 => "GURGAON",
				5 => "DHAN KUBER",
				6 => "DELHI BAZAR",
				7 => "SHRI GANESH",
				8 => "FARIDABAD",
				9 => "GALI",
				10 => "DESAWAR"
			];

			$date = now()->setTimezone('Asia/Kolkata')->toDateTimeString();
			$gameId = array_flip($gameNamess)[$gamename] ?? "Unknown ID";

			$insert = DB::table('chart_results')->where('id', $id)->update([
				"result" => $result,
				"updated_at" => $date
			]);

			if ($insert) {
				$winningNumber = DB::table('chart_results')->where('id', $id)->value('result');
				$serialNo1 = DB::table('betlog')->where('game_id', $gameId)->max('game_serial_no');
				$newSerialNo1 = $serialNo1 + 1;
				$bets = DB::table('bets')->where('game_id', $gameId)->where('game_serial_no', $serialNo1)->get();
				foreach ($bets as $bet) {
					$isWinner = ($bet->number == $winningNumber);
					$winAmount = $isWinner ? ($bet->amount * 9) : 0;
					DB::table('bets')->where('id', $bet->id)->update([
						'win_amount' => $winAmount,
						'win_number' => $winningNumber,
						'status' => $isWinner ? 2 : 3,
						'updated_at' => now()
					]);

					if ($isWinner && $winAmount > 0) {
						DB::table('users')->where('id', $bet->user_id)->increment('winning_wallet', $winAmount);
					}
				}

				DB::table('betlog')->where('game_id', $id)->update([
					'amount' => 0,
					'game_serial_no' => $newSerialNo1
				]);

				$serialNo2 = DB::table('crossing_betlog')->where('game_id', $gameId)->max('game_serial_no');
				$newSerialNo2 = $serialNo2 + 1;

				$cross_bets = DB::table('cross_bets')->where('game_id', $gameId)->where('game_serial_no', $serialNo2)->get();
				//dd($serialNo2,$cross_bets);
				foreach ($cross_bets as $betss) {
					$isWinners = ($betss->number == $winningNumber);
					$winAmounts = $isWinners ? ($betss->amount * 9) : 0;
					DB::table('cross_bets')->where('id', $betss->id)->update([
						'win_amount' => $winAmounts,
						'win_number' => $winningNumber,
						'status' => $isWinners ? 2 : 3,
						'updated_at' => now()
					]);

					if ($isWinners && $winAmounts > 0) {
						DB::table('users')->where('id', $betss->user_id)->increment('winning_wallet', $winAmounts);
					}
				}

				DB::table('crossing_betlog')->where('game_id', $gameId)->update([
					'amount' => 0,
					'game_serial_no' => $newSerialNo2
				]);

				$serialNo3 = DB::table('andar_bahar_betlog')->where('game_id', $gameId)->max('game_serial_no');
				$newSerialNo3 = $serialNo3 + 1;

				$andarbahar_bets = DB::table('andarbahar_bets')->where('game_id', $gameId)->where('game_serial_no', $serialNo3)->get();
				//dd($serialNo3, $andarbahar_bets);
				foreach ($andarbahar_bets as $betss) {
					$isWinners = ($betss->number == $winningNumber);
					$winAmounts = $isWinners ? ($betss->amount * 9) : 0;
					DB::table('andarbahar_bets')->where('id', $betss->id)->update([
						'win_amount' => $winAmounts,
						'win_number' => $winningNumber,
						'status' => $isWinners ? 2 : 3,
						'updated_at' => now()
					]);

					if ($isWinners && $winAmounts > 0) {
						DB::table('users')->where('id', $betss->user_id)->increment('winning_wallet', $winAmounts);
					}
				}

				DB::table('andar_bahar_betlog')->where('game_id', $id)->update([
					'amount' => 0,
					'game_serial_no' => $newSerialNo3
				]);

				return redirect()->back()->with([
						'success' => 'Winners decided successfully',
						'game_id' => $id,
						'winning_number' => $winningNumber,
						'new_game_serial_no_bets' => $newSerialNo1,
						'new_game_serial_no_cross_bets' => $newSerialNo2,
						'new_game_serial_no_andarbahar' => $newSerialNo3
					]);

			}
		}

			}


