<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;
use App\Models\User;

class SalaryController extends Controller
{
    public function dailySalary(Request $request)
    {
        $yesterday = Carbon::now('Asia/Kolkata')->subDay();
        $today = Carbon::now('Asia/Kolkata');

        // Fetch users with referrer_id and active status
        $users = User::whereNotNull('referrer_id')
            ->where('status', 1)
            ->get();

        foreach ($users as $user) {
            $userId = $user->id;

            // ✅ Self Recharge Check
            $selfRecharge = DB::table('payins')
                ->where('user_id', $userId)
                ->whereDate('created_at', $yesterday->toDateString())
                ->where('status', 2)
                ->sum('amount');
            
            // Skip if self recharge is less than 300
            if ($selfRecharge < 300) {
                continue;
            }

            // ✅ Get Direct (Level 1)
            $level1 = User::where('referrer_id', $userId)->pluck('id')->toArray();
            if (count($level1) < 1) {
                continue; // At least 1 Level 1 is required
            }

            // ✅ Active Referrals Count (Level 1 only, status = 1)
            $activeReferrals = User::whereIn('id', $level1)->where('status', 1)->count();

            // ✅ Calculate team deposit with level percentage logic
            $teamDeposit = 0;

            // Level percentage logic
            $levels = [
                1 => ['ids' => $level1, 'percent' => 1.0],
                2 => ['percent' => 0.5],
                3 => ['percent' => 0.3],
                4 => ['percent' => 0.2],
            ];

            // Level 2-4 loop
            foreach (range(2, 4) as $level) {
                $parentIds = $levels[$level - 1]['ids'] ?? [];
                if (empty($parentIds)) break;

                $ids = User::whereIn('referrer_id', $parentIds)->pluck('id')->toArray();
                $levels[$level]['ids'] = $ids;

                // Deposit for this level
                $deposit = DB::table('payins')
                    ->whereIn('user_id', $ids)
                    ->whereDate('created_at', $yesterday->toDateString())
                    ->where('status', 2)
                    ->sum('amount');

                $teamDeposit += $deposit * $levels[$level]['percent'];
            }

            // Level 5 to 10 (10% weight)
            $levelNIds = $levels[4]['ids'] ?? [];
            for ($l = 5; $l <= 10; $l++) {
                if (empty($levelNIds)) break;

                $nextLevel = User::whereIn('referrer_id', $levelNIds)->pluck('id')->toArray();
                $deposit = DB::table('payins')
                    ->whereIn('user_id', $nextLevel)
                    ->whereDate('created_at', $yesterday->toDateString())
                    ->where('status', 2)
                    ->sum('cash');

                $teamDeposit += $deposit * 0.1;
                $levelNIds = $nextLevel;
            }

            // ✅ Level 1 deposit (100%)
            $level1Deposit = DB::table('payins')
                ->whereIn('user_id', $level1)
                ->whereDate('created_at', $yesterday->toDateString())
                ->where('status', 2)
                ->sum('amount');

            $teamDeposit += $level1Deposit;

            // ✅ Get bonus
            $bonus = DB::table('daily_salaries')
                ->where('active_player', '<=', $activeReferrals)
                ->where('deposite_amount', '<=', $teamDeposit)
                ->orderByDesc('active_player')
                ->orderByDesc('deposite_amount')
                ->value('salary_amount');

            // Insert salary into the database if bonus exists
            if ($bonus) {
                DB::table('salaries')->insert([
                    'user_id' => $userId,
                    'salary' => $bonus,
                    'salary_type' => "Daily Salary",
                    'created_at' => $today,
                    'updated_at' => $today,
                ]);
            }
        }

        return response()->json(['message' => 'Daily bonus distributed based on team performance.']);
    }
    
//     public function dailySalary_old(Request $request)
// {
//     $yesterday = Carbon::now('Asia/Kolkata')->subDay();
//     $today = Carbon::now('Asia/Kolkata');

//     $users = User::whereNotNull('referrer_id')
//         ->where('status', 1)
//         ->get();
        
//         //dd($users);

//     foreach ($users as $user) {
//         $userId = $user->id;

//         // ✅ Self Recharge Check
//         $selfRecharge = DB::table('payins')
//             ->where('user_id', $userId)
//             ->whereDate('created_at', $yesterday->toDateString())
//             ->where('status', 2)
//             ->sum('amount');
//         //dd($selfRecharge,$userId);
//         if ($selfRecharge < 300) {
//             continue;
//         }

//         // ✅ Get Direct (Level 1)
//         $level1 = User::where('referrer_id', $userId)->pluck('id')->toArray();
//         //dd($level1);
//         if (count($level1) < 1) {
//             continue; // At least 1 Level 1 is required
//         }

//         // ✅ Active Referrals Count (Level 1 only, status = 1)
//         $activeReferrals = User::whereIn('id', $level1)->where('status', 1)->count();
//          //dd($activeReferrals);
//         // ✅ Calculate team deposit with level percentage logic
//         $teamDeposit = 0;

//         $levels = [
//             1 => ['ids' => $level1, 'percent' => 1.0],
//             2 => ['percent' => 0.5],
//             3 => ['percent' => 0.3],
//             4 => ['percent' => 0.2],
//         ];
//         //dd($levels);

//         // Level 2-4 loop
//         foreach (range(2, 4) as $level) {
//             $parentIds = $levels[$level - 1]['ids'] ?? [];
//             //dd($parentIds);
//             if (empty($parentIds)) break;

//             $ids = User::whereIn('referrer_id', $parentIds)->pluck('id')->toArray();
//             //dd($ids);
//             $levels[$level]['ids'] = $ids;
//             //dd($levels,$ids);
//             $deposit = DB::table('payins')
//                 ->whereIn('user_id', $ids)
//                 ->whereDate('created_at', $yesterday->toDateString())
//                 ->where('status', 2)
//                 ->sum('amount');
//             //dd($deposit);
//             $teamDeposit += $deposit * $levels[$level]['percent'];
//             //dd($teamDeposit);
//         }

//         // Level 5 to 10 (10% weight)
//         $levelNIds = $levels[4]['ids'] ?? [];
//         //dd($levelNIds);
//         for ($l = 5; $l <= 10; $l++) {
//             if (empty($levelNIds)) break;

//             $nextLevel = User::whereIn('referrer_id', $levelNIds)->pluck('id')->toArray();
//             $deposit = DB::table('payins')
//                 ->whereIn('user_id', $nextLevel)
//                 ->whereDate('created_at', $yesterday->toDateString())
//                 ->where('status', 2)
//                 ->sum('cash');

//             $teamDeposit += $deposit * 0.1;
//             $levelNIds = $nextLevel;
//         }

//         // ✅ Level 1 deposit (100%)
//         $level1Deposit = DB::table('payins')
//             ->whereIn('user_id', $level1)
//             ->whereDate('created_at', $yesterday->toDateString())
//             ->where('status', 2)
//             ->sum('amount');

//         $teamDeposit += $level1Deposit;

//         // ✅ Get bonus
//         $bonus = DB::table('daily_salaries')
//             ->where('active_player', '<=', $activeReferrals)
//             ->where('deposite_amount', '<=', $teamDeposit)
//             ->orderByDesc('active_player')
//             ->orderByDesc('deposite_amount')
//             ->value('salary_amount');
// //dd($bonus,$userId);
//         if ($bonus) {
//             DB::table('salaries')->insert([
//                 'user_id' => $userId,
//                 'salary' => $bonus,
//                 'salary_type' => "Daily Salary",
//                 'created_at' => $today,
//                 'updated_at' => $today,
//             ]);
//         }
//     }

//     return response()->json(['message' => 'Daily bonus distributed based on team performance.']);
// }

    public function userSalaryList(Request $request)
    {
        $userId = $request->user_id;
        $fromDate = $request->from_date ? $request->from_date . ' 00:00:00' : null;
        $toDate = $request->to_date ? $request->to_date . ' 23:59:59' : null;

        if (empty($userId)) {
            return response()->json([
                'status' => 400,
                'message' => 'user_id is required',
                'data' => []
            ]);
        }

        // Step 1: Fetch salary data
        $salaryQuery = DB::table('salaries')
            ->select('salary', 'salary_type', 'created_at')
            ->where('user_id', $userId);

        if ($fromDate && $toDate) {
            $salaryQuery->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $salaryData = $salaryQuery->orderBy('created_at', 'desc')->get();

        if ($salaryData->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No data found',
                'data' => []
            ]);
        }

        // Step 2: Loop each salary record, get deposits one day before its created_at
        $detailedData = $salaryData->map(function ($item) {
            $previousDate = Carbon::parse($item->created_at)->subDay()->format('Y-m-d');

            // Payins only for that previous date
            $payins = DB::table('payins')
                ->whereDate('created_at', $previousDate)
                ->get();

            $peopleDepositAmount = $payins->sum('amount');
            $peopleDepositNumber = $payins->pluck('user_id')->unique()->count();

            return [
                'salary' => $item->salary,
                'salary_type' => $item->salary_type,
                'created_at' => $item->created_at,
                'people_deposit_number' => $peopleDepositNumber,
                'people_deposit_amount' => $peopleDepositAmount,
            ];
        });

        $totalSalary = $salaryData->sum('salary');

        return response()->json([
            'status' => 200,
            'total_salary' => $totalSalary,
            'data' => $detailedData,
        ]);
    }
	
	


public function calculateDailySalaryBonus()
{
    $levels = [
        1 => 1.0, 2 => 0.5,
        3 => 0.3, 4 => 0.2,
        5 => 0.1, 6 => 0.1,
        7 => 0.1, 8 => 0.1,
        9 => 0.1, 10 => 0.1,
    ];
    $salarySlabs = [
        ['actives' => 3,  'min_deposit' => 1000,    'max_deposit' => 2999,     'salary' => 150],
        ['actives' => 5,  'min_deposit' => 3000,    'max_deposit' => 9999,     'salary' => 300],
        ['actives' => 5,  'min_deposit' => 10000,   'max_deposit' => 19999,    'salary' => 600],
        ['actives' => 10, 'min_deposit' => 20000,   'max_deposit' => 39999,    'salary' => 1200],
        ['actives' => 10, 'min_deposit' => 40000,   'max_deposit' => 69999,    'salary' => 2000],
        ['actives' => 10, 'min_deposit' => 70000,   'max_deposit' => 119999,   'salary' => 3000],
        ['actives' => 20, 'min_deposit' => 120000,  'max_deposit' => 199999,   'salary' => 5000],
        ['actives' => 20, 'min_deposit' => 200000,  'max_deposit' => 249999,   'salary' => 8000],
        ['actives' => 20, 'min_deposit' => 250000,  'max_deposit' => 349999,   'salary' => 10000],
        ['actives' => 20, 'min_deposit' => 350000,  'max_deposit' => 619999,   'salary' => 15000],
        ['actives' => 30, 'min_deposit' => 620000,  'max_deposit' => 1249999,  'salary' => 25000],
        ['actives' => 30, 'min_deposit' => 1250000, 'max_deposit' => 2999999,  'salary' => 32000],
        ['actives' => 30, 'min_deposit' => 3000000, 'max_deposit' => 5999999,  'salary' => 40000],
        ['actives' => 30, 'min_deposit' => 6000000, 'max_deposit' => 17999999, 'salary' => 80000],
        ['actives' => 30, 'min_deposit' => 18000000,'max_deposit' => PHP_INT_MAX,'salary' => 100000],
    ];

    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();

    $allUsers = DB::table('users')->pluck('id');

    foreach ($allUsers as $userId) {
		//dd($userId);
        // ✅ Step 1: Check today's self recharge (minimum 300)
        $selfRecharge = DB::table('payins')
            ->where('user_id', $userId)
            ->whereDate('created_at', $yesterday)
            ->where('status', 2)
            ->sum('amount');
      		//dd($selfRecharge,$userId,$yesterday);
        if ($selfRecharge < 300) continue;

        // ✅ Step 2: Count Level 1 Active Directs
        $level1Directs = DB::table('users')
            ->where('referrer_id', $userId)
            ->where('status', 1)
            ->count();
				//dd($level1Directs);
        if ($level1Directs == 0) continue;

        // ✅ Step 3: Recursive downline with levels (up to level 10)
        $downlines = DB::select("
            WITH RECURSIVE downline AS (
                SELECT id, referrer_id, 1 AS level
                FROM users
                WHERE referrer_id = ?

                UNION ALL

                SELECT u.id, u.referrer_id, d.level + 1
                FROM users u
                INNER JOIN downline d ON u.referrer_id = d.id
                WHERE d.level < 10
            )
            SELECT id, level FROM downline
        ", [$userId]);
			//dd($downlines);
        // ✅ Step 4: Group by levels
        $levelGroups = [];
        foreach ($downlines as $d) {
            $levelGroups[$d->level][] = $d->id;
        }

        // ✅ Step 5: Total team deposit from downlines (yesterday only)
        $teamDeposit = 0;

        foreach ($levelGroups as $level => $userIds) {
            if (!isset($levels[$level])) continue;

            $deposit = DB::table('payins')
                ->whereIn('user_id', $userIds)
                ->whereDate('created_at', $yesterday)
                ->where('status', 1)
                ->sum('amount');
//dd($deposit,$userIds,$yesterday);
            $teamDeposit += $deposit * $levels[$level];
        }

        // ✅ Step 6: Match salary slab
        $salary = 0;
        foreach ($salarySlabs as $slab) {
            if (
                $level1Directs >= $slab['actives'] &&
                $teamDeposit >= $slab['min_deposit'] &&
                $teamDeposit <= $slab['max_deposit']
            ) {
                $salary = $slab['salary'];
                break;
            }
        }

        if ($salary > 0) {
            DB::table('wallet_histories')->insert([
                'user_id' => $userId,
                'amount' => $salary,
				'type_id' => '32',
                'description' => 'daily_salary',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    echo "✅ Daily salary bonus distributed successfully.\n";
}

	
	//betcommission amount
	

function calculateTeamTradingBonus_old()
{
    $levels = [
        1 => 0.002, 2 => 0.002,
        3 => 0.003, 4 => 0.003,
        5 => 0.004, 6 => 0.004,
        7 => 0.005, 8 => 0.005,
        9 => 0.006, 10 => 0.006,
    ];

    $allUsers = DB::table('users')->pluck('id');

    foreach ($allUsers as $userId) {
        // Step 1: Count active direct users
        $activeDirects = DB::table('users')
            ->where('referrer_id', $userId)
            ->where('status', 1)
            ->count();
		
		
        // Step 2: Limit levels if less than 10 active directs
        $userLevels = $levels;
        if ($activeDirects < 10) {
            $userLevels = array_slice($levels, 0, $activeDirects, true);
			
        }

        // Step 3: Get downlines with levels using CTE
        $downlines = DB::select("
            WITH RECURSIVE downline AS (
                SELECT id, referrer_id, 1 AS level
                FROM users
                WHERE referrer_id = ?

                UNION ALL

                SELECT u.id, u.referrer_id, d.level + 1
                FROM users u
                INNER JOIN downline d ON u.referrer_id = d.id
                WHERE d.level < 10
            )
            SELECT id, level FROM downline
        ", [$userId]);
     	return $downlines;
        // Step 4: Group downlines by level
        $levelGroups = [];
        foreach ($downlines as $downline) {
            $levelGroups[$downline->level][] = $downline->id;
        }

        // Step 5: Calculate total bonus
        $totalBonus = 0;

        foreach ($levelGroups as $level => $userIds) {
            if (!isset($userLevels[$level])) continue;

            $betAmount = DB::table('bets')
                ->whereIn('userid', $userIds)
                ->sum('amount');

            $commission = $betAmount * $userLevels[$level];
            $totalBonus += $commission;
        }

        // Step 6: Insert into wallet_history
        if ($totalBonus > 0) {
            DB::table('wallet_history')->insert([
                'user_id' => $userId,
                'amount' => $totalBonus,
                'type' => 'team_trading_bonus',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    echo "🎯 Team trading bonus done for all users.\n";
}

	
}
