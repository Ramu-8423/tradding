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
    // Validate request parameter: game_type is required
    $request->validate([
        'game_type' => 'required',
		'game_id' => 'required'
    ]);

    // Get the validated game_type value from request
    $gameType = $request->input('game_type');
    $gameId = $request->input('game_id');
    // Data fetch with game_type filter applied
	
	
	
	
	
 //   $data = DB::select(
//        "SELECT `id`, `single`, `double`, `created_at`, `game_type` FROM `tradding_chart_result` WHERE game_type = ?", 
//        [$gameType]
 //   );

	
	$data = DB::select(
    "SELECT `id`, `single`, `double`, `created_at`, `game_type` 
     FROM `tradding_chart_result` 
     WHERE game_type = ? AND game_id = ?", 
    [$gameType, $gameId]
);
	
	
	
	
	
	
	
    if ($data) {
        foreach ($data as $item) {

            // Initialize new keys "number" and "list"
            // Agar game_type 1 hai, to single related data use karenge.
            if ($gameType == 1) {
                // number field: single data
                $item->number = $item->single !== null ? (int)$item->single : null;
                // single_list calculation only if single has a value
                if ($item->single !== null) {
                    $single = (int)$item->single;
                    $start = max(0, $single - 2);
                    $end = min(99, $single + 2);
                    if ($end - $start < 4) {
                        if ($start == 0) {
                            $end = min(99, $start + 4);
                        } elseif ($end == 99) {
                            $start = max(0, $end - 4);
                        }
                    }
                    $item->list = range($start, $end);
                } else {
                    $item->list = [];
                }
                // Unset fields not required for game_type 1
                unset($item->single);
                unset($item->single_list);
                unset($item->double);
                unset($item->double_list);
            }

            // Agar game_type 2 hai, to double related data use karenge.
            if ($gameType == 2) {
                // number field: double data
                // Yahan, agar double null hai to null, otherwise use ussi value
                // (Agar DB me "00" jaise value aa rahi ho, use string format me preserve karenge)
                $item->number = $item->double !== null ? $item->double : null;
                if ($item->double !== null) {
                    $double = (int)$item->double;
                    $start = max(0, $double - 2);
                    $end = min(99, $double + 2);
                    if ($end - $start < 4) {
                        if ($start == 0) {
                            $end = min(99, $start + 4);
                        } elseif ($end == 99) {
                            $start = max(0, $end - 4);
                        }
                    }
                    $tempList = range($start, $end);
                    // Format each value to 2-digit string
                    $item->list = array_map(function ($n) {
                        return sprintf("%02d", $n);
                    }, $tempList);
                } else {
                    $item->list = [];
                }
                // Unset fields not required for game_type 2
                unset($item->double);
                unset($item->double_list);
                unset($item->single);
                unset($item->single_list);
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

   

}