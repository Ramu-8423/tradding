<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\{User,MlmLevel,Payin};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Http;


 
class AgencyPromotionController extends Controller
{

public function promotion_data_old($id) 
{
    try {
        $user = User::findOrFail($id);
        $currentDate = Carbon::now()->subDay()->format('Y-m-d');
        //dd($currentDate);
        $directSubordinateCount = $user->referrals()->count();
        $totalCommission = $user->commission;
        $referralCode = $user->referral_code;
        $yesterdayTotalCommission = $user->yesterday_total_commission;

        $teamSubordinateCount = $user->getAllSubordinatesCount();
		
		
        	$teamSubordinateCount =DB::select("
        WITH RECURSIVE subordinates AS (
            SELECT id FROM users WHERE referrer_id = ?
            UNION ALL
            SELECT u.id FROM users u INNER JOIN subordinates s ON u.referrer_id = s.id
        )
        SELECT COUNT(*) as total FROM subordinates
    ",  [$user->id]); 

		
$teamSubordinateCount = $teamSubordinateQuery->total ?? 0;

        $register = User::where('referrer_id', $user->id)
                        ->whereDate('created_at', $currentDate)
                        ->count();


// $depositStats = DB::selectOne("
//     SELECT COUNT(p.id) AS deposit_number, SUM(p.cash) AS deposit_amount
//     FROM payins p
//     WHERE p.user_id IN (
//         SELECT id FROM users WHERE referrer_id = ?
//     )
//     AND DATE(p.created_at) = ?
// ", [$user->id, $currentDate]);


// //dd($depositStats);

// // Agar aapko count aur amount access karna ho:
// $depositNumber = $depositStats->deposit_number;
// $depositAmount = $depositStats->deposit_amount;

$depositStats = DB::selectOne("
    SELECT COUNT(p.id) AS deposit_number, SUM(p.amount) AS deposit_amount
    FROM payins p
    WHERE p.user_id IN (
        SELECT id FROM users WHERE referrer_id = ?
    )
    AND DATE(p.created_at) = ?
    AND p.status = 2
", [$user->id, $currentDate]);

$depositNumber = $depositStats->deposit_number;
$depositAmount = $depositStats->deposit_amount;


           
    
    $firstDepositCount = DB::table('payins')
    ->whereIn('user_id', function($query) use ($user) {
        $query->select('id')->from('users')->where('referrer_id', $user->id);
    })
    ->whereDate('created_at', $currentDate)
    ->distinct('user_id')
    ->count('user_id');

//dd($firstDepositCount);


      $subordinatesRegister = DB::selectOne("
    WITH RECURSIVE Subordinates AS (
        SELECT id, referrer_id, 1 AS level
        FROM users
        WHERE referrer_id = ?  
        AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        UNION ALL
        SELECT u.id, u.referrer_id, s.level + 1
        FROM users u
        INNER JOIN Subordinates s ON u.referrer_id = s.id
        WHERE DATE(u.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    )
    SELECT COUNT(*) AS count FROM Subordinates
", [$user->id]);

 // Subordinate register count on the current day
       $subordinatesRegisters = DB::select("SELECT COUNT(*) as count FROM users WHERE referrer_id IN ( SELECT id FROM users WHERE referrer_id = $user->id ) AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY;");
//dd($subordinatesRegister);
// Direct integer value extract karna
$totalCount = $subordinatesRegister->count;


        // Fetch referred users (subordinates)
        $referUserIds = DB::table('users')
            ->where('referrer_id', $user->id)
            ->pluck('id');  // Get the ids of all referred users


         

// $subordinatesDeposit = DB::select("
//     WITH RECURSIVE subordinates AS (
//         SELECT id, referrer_id, 1 AS level
//         FROM users
//         WHERE referrer_id = ?
//         UNION ALL
//         SELECT u.id, u.referrer_id, s.level + 1
//         FROM users u
//         INNER JOIN subordinates s ON s.id = u.referrer_id
//         WHERE s.level <= 7
//     )
//     SELECT COUNT(p.id) as deposit_number, 
//           SUM(p.cash) as deposit_amount 
//     FROM payins p
//     JOIN subordinates s ON p.user_id = s.id
//     WHERE p.created_at LIKE ?
//     AND p.status = 2
// ", [$user->id, $currentDate . '%']);

// Aggregate deposit data for all referred users (subordinates)
        if ($referUserIds->isNotEmpty()) {
            $subordinatesDeposit = DB::select("WITH RECURSIVE team_members AS (
    SELECT id
    FROM users
    WHERE referrer_id = $user->id  -- starting from the user whose team we're analyzing
    UNION
    SELECT u.id
    FROM users u
    JOIN team_members tm ON tm.id = u.referrer_id  -- recursively include all team members' downlines
)
SELECT COUNT(*) AS deposit_number, SUM(amount) AS deposit_amount
FROM payins
WHERE user_id IN (SELECT id FROM team_members)  -- consider deposits made by the entire team
AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY
AND status = 2;
");
}


$depositNumber = $subordinatesDeposit[0]->deposit_number ?? 0;
$depositAmount = $subordinatesDeposit[0]->deposit_amount ?? 0;
//dd($subordinatesDeposit);

        $subordinatesFirstDepositCount =// Get first deposit count for subordinates
            $totalFirstDepositCount = DB::table('payins')
                ->whereIn('user_id', $referUserIds)
                ->whereDate('created_at', $currentDate)
                ->count();
        
//dd($subordinatesFirstDepositCount);
        // Result array to return
        $result = [
            'yesterday_total_commission' => $yesterdayTotalCommission ?? 0,
            'register' => $register,
            'deposit_number' => $depositStats->deposit_number ?? 0,
            'deposit_amount' => $depositStats->deposit_amount ?? 0,
            'first_deposit' => $firstDepositCount,
            'subordinates_register' => $totalCount,
            'subordinates_deposit_number' => $depositNumber ?? 0,
            'subordinates_deposit_amount' => $depositAmount ?? 0,
            'subordinates_first_deposit' => $subordinatesFirstDepositCount,
            'direct_subordinate' => $directSubordinateCount,
            'total_commission' => $totalCommission,
			'weekly_commission'=>0,
            'team_subordinate' => $teamSubordinateCount,
            'referral_code' => $referralCode,
        ];
        //dd($subordinatesDeposit);
       // dd($depositNumber);

        return response()->json(['status' => 200,'message' => 'data fetch successfully','data' =>$result], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function promotion_data($id) 
{
    try {
        $user = User::findOrFail($id);
        $today = Carbon::now()->format('Y-m-d');

        $referralCode = $user->referral_code;
        $totalCommission = $user->commission ?? 0;

        // Direct Registrations
        $directRegistration = User::where('referrer_id', $user->id)->count();

        // Today Registrations
        $todayRegistration = User::where('referrer_id', $user->id)
                                 ->whereDate('created_at', $today)
                                 ->count();

        // Total Registrations under team
        $totalRegistration = DB::selectOne("
            WITH RECURSIVE team AS (
                SELECT id FROM users WHERE referrer_id = ?
                UNION ALL
                SELECT u.id FROM users u INNER JOIN team t ON u.referrer_id = t.id
            )
            SELECT COUNT(*) AS total FROM team
        ", [$user->id])->total ?? 0;

        // Total Self Deposit
        $totalSelfDeposit = DB::table('payins')
            ->where('user_id', $user->id)
            ->where('status', 2)
            ->sum('amount');

        // Today Self Deposit
        $todaySelfDeposit = DB::table('payins')
            ->where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('status', 2)
            ->sum('amount');

        // Total Self Bet (sum from all 4 bet tables)
        $totalSelfBet = 
            DB::table('bets')->where('user_id', $user->id)->sum('amount') +
            DB::table('cross_bets')->where('user_id', $user->id)->sum('amount') +
            DB::table('andarbahar_bets')->where('user_id', $user->id)->sum('amount') +
            DB::table('tradding_bets')->where('user_id', $user->id)->sum('amount');

        // Today Self Bet (sum from all 4 bet tables with today's date)
        $todaySelfBet = 
            DB::table('bets')->where('user_id', $user->id)->whereDate('created_at', $today)->sum('amount') +
            DB::table('cross_bets')->where('user_id', $user->id)->whereDate('created_at', $today)->sum('amount') +
            DB::table('andarbahar_bets')->where('user_id', $user->id)->whereDate('created_at', $today)->sum('amount') +
            DB::table('tradding_bets')->where('user_id', $user->id)->whereDate('created_at', $today)->sum('amount');

        // Total Team Deposit
        $teamDeposit = DB::selectOne("
            WITH RECURSIVE team AS (
                SELECT id FROM users WHERE referrer_id = ?
                UNION ALL
                SELECT u.id FROM users u INNER JOIN team t ON u.referrer_id = t.id
            )
            SELECT SUM(p.amount) AS total FROM payins p
            WHERE p.user_id IN (SELECT id FROM team)
            AND p.status = 2
        ", [$user->id]);

        $totalTeamDeposit = $teamDeposit->total ?? 0;

        // Today Team Deposit
        $todayTeamDeposit = DB::selectOne("
            WITH RECURSIVE team AS (
                SELECT id FROM users WHERE referrer_id = ?
                UNION ALL
                SELECT u.id FROM users u INNER JOIN team t ON u.referrer_id = t.id
            )
            SELECT SUM(p.amount) AS total FROM payins p
            WHERE p.user_id IN (SELECT id FROM team)
            AND p.status = 2 AND DATE(p.created_at) = ?
        ", [$user->id, $today]);

        $todayTeamDepositAmount = $todayTeamDeposit->total ?? 0;

        // Number of People Making First Deposit - Direct
        $registrationFirstDeposit = DB::table('payins')
            ->select('user_id')
            ->whereIn('user_id', function ($query) use ($user) {
                $query->select('id')->from('users')->where('referrer_id', $user->id);
            })
            ->whereDate('created_at', $today)
            ->where('status', 2)
            ->distinct()
            ->count('user_id');

        // Number of People Making First Deposit - Team
        $teamIds = DB::select("
            WITH RECURSIVE team AS (
                SELECT id FROM users WHERE referrer_id = ?
                UNION ALL
                SELECT u.id FROM users u INNER JOIN team t ON u.referrer_id = t.id
            )
            SELECT id FROM team
        ", [$user->id]);

        $teamUserIds = collect($teamIds)->pluck('id')->toArray();

        $depositFirstPeople = DB::table('payins')
            ->whereIn('user_id', $teamUserIds)
            ->whereDate('created_at', $today)
            ->where('status', 2)
            ->distinct()
            ->count('user_id');

        return response()->json([
            'status' => 200,
            'message' => 'data fetch successfully',
            'data' => [
                'total_commission' => $totalCommission,
                'total_registration' => $totalRegistration,
                'today_registration' => $todayRegistration,
                'direct_registration' => $directRegistration,
                'people_making_first_deposit' => $registrationFirstDeposit,
                'total_self_deposit' => $totalSelfDeposit,
                'today_self_deposit' => $todaySelfDeposit,
                'total_self_bet' => $totalSelfBet,
                'today_self_bet' => $todaySelfBet,
                'total_team_deposit' => $totalTeamDeposit,
                'today_team_deposit' => $todayTeamDepositAmount,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function new_subordinate(Request $request)
{
    try {
        // Validation
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 200);
        }

        // Find the user using Eloquent Model
        $user = User::findOrFail($request->id);

        // Get the current, yesterday's, start and end of month dates
        $currentDate = Carbon::now()->format('Y-m-d');
        $yesterdayDate = Carbon::yesterday()->format('Y-m-d');
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Initialize the query for subordinates
        $query = User::select('mobile','u_id','commission', 'name', 'created_at')
            ->where('referrer_id', $user->id);
            
        switch ($request->type) {
            case 1:
                // Today's subordinates
                $query->whereDate('created_at', $currentDate);
                break;
            case 2:
                // Yesterday's subordinates
                $query->whereDate('created_at', $yesterdayDate);
                break;
            case 3:
                // Subordinates for this month
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                break;
            default:
                return response()->json(['status' => 400, 'message' => 'Invalid type provided'], 200);
        }
        $subordinate_data = $query->get();

        // Return success or error based on whether data exists
        if ($subordinate_data->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved subordinates!',
                'data' => $subordinate_data
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Data not found'
            ], 200);
        }
    } catch (\Exception $e) {
        // Handle any exceptions
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function tier()
{
    try {
        // Fetch all levels using the MlmLevel model
        $tier = MlmLevel::select('id', 'name')->get();

        if ($tier->isNotEmpty()) {
            $response = [
                'status' => 200,
                'message' => 'Successfully..!', 
                'data' => $tier
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'status' => 400, 
                'message' => 'Data not found'
            ];
            return response()->json($response, 400);
        }

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function subordinate_data(Request $request) 
{
    try {
        // Step 1: Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'tier' => 'nullable|integer|min:0',
            'created_at' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }

        // Step 2: Get input parameters
        $userId = $request->id;
        $tier = $request->tier ?? 0;
        $searchUid = $request->u_id;
        $currentDate = $request->created_at ?: Carbon::now()->subDay()->format('Y-m-d');

        // Step 3: Traverse and store users tier-wise
        $allLevels = [];
        $userTierMap = [];
        $currentLevelUsers = User::where('referrer_id', $userId)->get();
        $currentLevel = 1;

        while ($currentLevelUsers->isNotEmpty() && ($tier == 0 || $currentLevel <= $tier)) {
            foreach ($currentLevelUsers as $user) {
                $userTierMap[$user->id] = $currentLevel;
            }
            $allLevels[$currentLevel] = $currentLevelUsers;
            $currentLevelUsers = User::whereIn('referrer_id', $currentLevelUsers->pluck('id'))->get();
            $currentLevel++;
        }

        // Step 4: Pick users from the requested tier
        if ($tier > 0) {
            $targetUsers = $allLevels[$tier] ?? collect();
        } else {
            $targetUsers = collect();
            foreach ($allLevels as $levelUsers) {
                $targetUsers = $targetUsers->merge($levelUsers);
            }
        }

        // Step 5: If no users found at selected tier, return empty response
        if ($targetUsers->isEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Data fetched successfully',
                'data' => [
                    'number_of_deposit' => 0,
                    'payin_amount' => 0,
                    'number_of_bettor' => 0,
                    'bet_amount' => 0,
                    'first_deposit' => 0,
                    'first_deposit_amount' => 0,
                    'subordinates_data' => []
                ]
            ], 200);
        }

        // Step 6: Apply search by u_id if provided
        $subordinateIds = $targetUsers->pluck('id');
        if (!empty($searchUid)) {
            $subordinateIds = User::whereIn('id', $subordinateIds)
                                  ->where('u_id', 'like', $searchUid . '%')
                                  ->pluck('id');
        }

        // Step 7: Fetch mlm_levels into a PHP array: [level => commission]
        $levelCommissionMap = DB::table('mlm_levels')->pluck('commission', 'count')->toArray();

        // Step 8: Fetch subordinate data
        $subordinatesData = DB::table('users')
            ->leftJoin(DB::raw("(
                SELECT user_id, SUM(amount) as total_bet 
                FROM bets 
                WHERE DATE(created_at) = '{$currentDate}' 
                GROUP BY user_id
            ) as bet_data"), 'users.id', '=', 'bet_data.user_id')
            ->leftJoin(DB::raw("(
                SELECT user_id, SUM(amount) as total_payin, COUNT(id) as deposit_count 
                FROM payins 
                WHERE DATE(created_at) = '{$currentDate}' 
                AND status = 2 
                GROUP BY user_id
            ) as payin_data"), 'users.id', '=', 'payin_data.user_id')
            ->leftJoin(DB::raw("(
                SELECT p1.user_id, COUNT(p1.id) as total_first_recharge, SUM(p1.amount) as total_first_deposit_amount 
                FROM payins p1 
                WHERE p1.status = 2 
                AND DATE(p1.created_at) = '{$currentDate}' 
                AND p1.created_at = (
                    SELECT MIN(p2.created_at) 
                    FROM payins p2 
                    WHERE p2.user_id = p1.user_id 
                    AND p2.status = 2
                )
                GROUP BY p1.user_id
            ) as first_deposit_data"), 'users.id', '=', 'first_deposit_data.user_id')
            ->whereIn('users.id', $subordinateIds)
            ->select([
                'users.id',
                'users.u_id',
                'users.name',
                DB::raw('COALESCE(bet_data.total_bet, 0) as bet_amount'),
                DB::raw('COALESCE(payin_data.total_payin, 0) as payin_amount'),
                DB::raw('COALESCE(payin_data.deposit_count, 0) as number_of_deposit'),
                DB::raw('COALESCE(first_deposit_data.total_first_recharge, 0) as total_first_recharge'),
                DB::raw('COALESCE(first_deposit_data.total_first_deposit_amount, 0) as total_first_deposit_amount')
            ])
            ->get();

        // Step 9: Initialize result structure
        $result = [
            'number_of_deposit' => 0,
            'payin_amount' => 0,
            'number_of_bettor' => 0,
            'bet_amount' => 0,
            'first_deposit' => 0,
            'first_deposit_amount' => 0,
            'subordinates_data' => [],
        ];

        // Step 10: Loop through subordinates and aggregate
        foreach ($subordinatesData as $user) {
            $betAmount = $user->bet_amount;
            $payinAmount = $user->payin_amount;
            $depositCount = $user->number_of_deposit;
            $firstDeposit = $user->total_first_recharge;
            $firstDepositAmount = $user->total_first_deposit_amount;

            $actualTier = $userTierMap[$user->id] ?? $tier;
            $commissionPercent = $levelCommissionMap[$actualTier] ?? 0;
            $commission = ($betAmount * $commissionPercent) / 100;

            $result['bet_amount'] += $betAmount;
            $result['payin_amount'] += $payinAmount;
            $result['number_of_deposit'] += $depositCount;
            $result['first_deposit'] += $firstDeposit;
            $result['first_deposit_amount'] += $firstDepositAmount;

            if ($betAmount > 0) {
                $result['number_of_bettor']++;
            }

            $result['subordinates_data'][] = [
                'id' => $user->id,
                'u_id' => $user->u_id,
                'name' => $user->name,
                'bet_amount' => $betAmount,
                'payin_amount' => $payinAmount,
                'number_of_deposit' => $depositCount,
                'commission' => $commission,
                'first_deposit' => $firstDeposit,
                'first_deposit_amount' => $firstDepositAmount,
                'tier' => $actualTier
            ];
        }

        // Step 11: Return final response
        return response()->json([
            'status' => 200,
            'message' => 'Data fetched successfully',
            'data' => $result
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
public function turnover_new()
{
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');

    DB::table('users')->update(['yesterday_total_commission' => 0]);

    $referralUsers = DB::table('users')
        ->select('id')
        ->whereIn('id', function ($query) {
            $query->select('referrer_id')->from('users')->whereNotNull('referrer_id');
        })
        ->get();

    foreach ($referralUsers as $user) {
        $user_id = $user->id;

        $directReferralCount = DB::table('users')->where('referrer_id', $user_id)->count();
        if ($directReferralCount < 1) {
            continue;
        }

        $maxLevel = min($directReferralCount, 10);

        $subordinatesData = DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id, referrer_id, 1 AS level
                FROM users
                WHERE referrer_id = ?
                UNION ALL
                SELECT u.id, u.referrer_id, s.level + 1
                FROM users u
                INNER JOIN subordinates s ON s.id = u.referrer_id
                WHERE s.level + 1 <= ?
            ),
            combined_bets AS (
                SELECT user_id, SUM(amount) AS total_bet_amount
                FROM (
                    SELECT user_id, amount FROM bets WHERE created_at LIKE ?
                    UNION ALL
                    SELECT user_id, amount FROM andarbahar_bets WHERE created_at LIKE ?
                    UNION ALL
                    SELECT user_id, amount FROM cross_bets WHERE created_at LIKE ?
                ) AS all_bets
                GROUP BY user_id
            )
            SELECT 
                s.level,
                cb.total_bet_amount,
                COALESCE(cb.total_bet_amount, 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission
            FROM subordinates s
            LEFT JOIN combined_bets cb ON s.id = cb.user_id
            LEFT JOIN mlm_levels ON s.level = mlm_levels.id
        ", [
            $user_id,
            $maxLevel,
            "$currentDate%",
            "$currentDate%",
            "$currentDate%",
        ]);

        $totalCommission = 0;
        $totalBetAmount = 0;
        $totalBetterCount = 0;

        foreach ($subordinatesData as $row) {
            $betAmount = $row->total_bet_amount ?? 0;
            $commission = $row->commission ?? 0;

            if ($commission > 0) {
                if ($betAmount > 0) {
                    $totalBetterCount++;
                    $totalBetAmount += $betAmount;
                }
                $totalCommission += $commission;
            }
        }
$currentDateTime = now('Asia/Kolkata'); // <-- IST time
        if ($totalCommission > 0) {
            DB::table('users')->where('id', $user_id)->update([
                'wallet' => DB::raw('wallet + ' . $totalCommission),
                'commission' => DB::raw('commission + ' . $totalCommission),
                'yesterday_total_commission' => $totalCommission,
                'updated_at' => $datetime,
            ]);

            DB::table('wallet_histories')->insert([
                'user_id' => $user_id,
                'amount' => $totalCommission,
                'type_id' => 26,
                'description_2' => $totalBetterCount,
                'description' => $totalBetAmount,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ]);
        }
    }

    return response()->json(['message' => 'Turnover and commissions calculated successfully.']);
}

public function turnover_new_recent()
{
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');

    // Reset yesterday's total commission to 0
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    // Get all users who referred at least one person
    $referralUsers = DB::table('users')
        ->select('id')
        ->whereIn('id', function ($query) {
            $query->select('referrer_id')
                ->from('users')
                ->whereNotNull('referrer_id');
        })
        ->get();

    foreach ($referralUsers as $user) {
        $user_id = $user->id;

        // Count direct referrals
        $directReferralCount = DB::table('users')->where('referrer_id', $user_id)->count();

        // Only continue if at least 1 direct referral
        if ($directReferralCount < 1) {
            continue;
        }

        $maxLevel = min($directReferralCount, 10); // Limit to max 10 levels

        // Recursive query to fetch subordinates and their bets
        $subordinatesData = DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id, referrer_id, 1 AS level
                FROM users
                WHERE referrer_id = ?
                UNION ALL
                SELECT u.id, u.referrer_id, s.level + 1
                FROM users u
                INNER JOIN subordinates s ON s.id = u.referrer_id
                WHERE s.level + 1 <= ?
            ),
            combined_bets AS (
                SELECT user_id, SUM(amount) AS total_bet_amount
                FROM (
                    SELECT user_id, amount FROM bets WHERE created_at LIKE ?
                    UNION ALL
                    SELECT user_id, amount FROM andarbahar_bets WHERE created_at LIKE ?
                    UNION ALL
                    SELECT user_id, amount FROM cross_bets WHERE created_at LIKE ?
                ) AS all_bets
                GROUP BY user_id
            )
            SELECT 
                s.level,
                cb.total_bet_amount,
                COALESCE(cb.total_bet_amount, 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission
            FROM subordinates s
            LEFT JOIN combined_bets cb ON s.id = cb.user_id
            LEFT JOIN mlm_levels ON s.level = mlm_levels.id
        ", [
            $user_id,
            $maxLevel,
            "$currentDate%",
            "$currentDate%",
            "$currentDate%",
        ]);

        // Initialize totals
        $totalCommission = 0;
        $totalBetAmount = 0;
        $totalBetterCount = 0;

        foreach ($subordinatesData as $row) {
            $betAmount = $row->total_bet_amount ?? 0;
            $commission = $row->commission ?? 0;

            if ($betAmount > 0) {
                $totalBetterCount++; // User placed a bet
                $totalBetAmount += $betAmount; // Total bet amount
            }

            $totalCommission += $commission; // Commission
        }

        if ($totalCommission > 0) {
            // Update user's wallet
            DB::table('users')->where('id', $user_id)->update([
                'wallet' => DB::raw('wallet + ' . $totalCommission),
                'commission' => DB::raw('commission + ' . $totalCommission),
                'yesterday_total_commission' => $totalCommission,
                'updated_at' => $datetime,
            ]);

            // Insert into wallet history
            DB::table('wallet_histories')->insert([
                'user_id' => $user_id,
                'amount' => $totalCommission,
                'type_id' => 26,
                'description_2' => $totalBetterCount,
                'description' => $totalBetAmount,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }

    return response()->json(['message' => 'Turnover and commissions calculated successfully.']);
}


	
	public function turnover_new_old()
{
    $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');

    // Reset yesterday's total commission to 0
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    // Get all users who referred at least one person
    $referralUsers = DB::table('users')
        ->select('id')
        ->whereIn('id', function ($query) {
            $query->select('referrer_id')->from('users')->whereNotNull('referrer_id');
        })->get();

    foreach ($referralUsers as $user) {
        $user_id = $user->id;

        // Count direct referrals
        $directReferralCount = DB::table('users')->where('referrer_id', $user_id)->count();

        // Only continue if at least 1 direct referral
        if ($directReferralCount < 1) {
            continue;
        }

        $maxLevel = min($directReferralCount, 10); // Limit max level to 10

        // Recursive query to fetch subordinates and their bets
        $subordinatesData = DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id, referrer_id, 1 AS level
                FROM users
                WHERE referrer_id = ?
                UNION ALL
                SELECT u.id, u.referrer_id, s.level + 1
                FROM users u
                INNER JOIN subordinates s ON s.id = u.referrer_id
                WHERE s.level + 1 <= ?
            ),
            combined_bets AS (
                SELECT user_id, SUM(amount) AS total_bet_amount
                FROM (
                    SELECT user_id, amount FROM bets WHERE created_at LIKE ?
                    UNION ALL
                    SELECT user_id, amount FROM andarbahar_bets WHERE created_at LIKE ?
                    UNION ALL
                    SELECT user_id, amount FROM cross_bets WHERE created_at LIKE ?
                ) AS all_bets
                GROUP BY user_id
            )
            SELECT 
                s.level,
                COALESCE(SUM(cb.total_bet_amount), 0) AS bet_amount,
                COALESCE(SUM(cb.total_bet_amount), 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission
            FROM subordinates s
            LEFT JOIN combined_bets cb ON s.id = cb.user_id
            LEFT JOIN mlm_levels ON s.level = mlm_levels.id
            GROUP BY s.level, mlm_levels.commission
        ", [
            $user_id,
            $maxLevel,
            "$currentDate%",
            "$currentDate%",
            "$currentDate%"
        ]);
		//return $subordinatesData;
//dd($user_id, $directReferralCount, $maxLevel);
        // Total commission from all subordinate levels
        $totalCommission = 0;
        foreach ($subordinatesData as $row) {
            $totalCommission += $row->commission;
        }

        if ($totalCommission > 0) {
            DB::table('users')->where('id', $user_id)->update([
                'wallet' => DB::raw('wallet + ' . $totalCommission),
                'commission' => DB::raw('commission + ' . $totalCommission),
                'yesterday_total_commission' => $totalCommission,
                'updated_at' => $datetime,
            ]);

            DB::table('wallet_histories')->insert([
                'user_id' => $user_id,
                'amount' => $totalCommission,
                'type_id' => 26,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }

    return response()->json(['message' => 'Turnover and commissions calculated successfully.']);
}

	
}