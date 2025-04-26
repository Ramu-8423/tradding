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

class DmatkaGameController extends Controller
{
   public function betlive_result(Request $request){
    $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d');
    $date = $request->has('date') ? $request->input('date') : $currentDate;

    $data = DB::table('chart_results')
        ->select('id','result','modify_result','result_time','date','gamename', 'yesterday_number')
        ->whereDate('date', $date)->get();

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
        10 => "DESAWAR"
    ];

    $formattedData = $data->map(function ($item) use ($gameNames) {
        $gameId = array_search($item->gamename, $gameNames) ?: null;
        return array_merge((array)$item, ['game_id' => $gameId]);
    });
    //dd($formattedData);
    return view('dmatka.live_game_result')->with('formattedData', $formattedData);
   }
   public function live_modify_result(Request $request){
        $request->validate([
        'modify_result' => 'nullable|digits_between:1,2|integer',
        'id' => 'required|integer',
    ]);
      $record = DB::table('chart_results')->where('id', $request->id)->first();
    if ($record){
        DB::table('chart_results')->where('id', $request->id)->update(['modify_result' => $request->modify_result]);
        return back()->with('success', 'Modify result updated successfully!');
    } else {
        return back()->with('error', 'Record not found.');
    } 
   }
   
  public function dmtka_betlog(Request $request, $game_type){
    if ($game_type == 1) {
        $query = DB::table('betlog')->where('amount', '>', 0);
    } elseif ($game_type == 2) {
        $query = DB::table('crossing_betlog')->where('amount', '>', 0);
    } elseif ($game_type == 3) {
        $query = DB::table('andar_bahar_betlog')->where('amount', '>', 0);
    } else {
        abort(404, 'Invalid game type.');
    }

    if ($request->has('game_id')) {
        $query->where('game_id', $request->game_id);
    }

    $betlogs = $query->orderBy('amount', 'desc')->get();

    return view('dmatka.betlog', [
        'betlogs' => $betlogs,
        'game_type' => $game_type,
    ]);
}
}
