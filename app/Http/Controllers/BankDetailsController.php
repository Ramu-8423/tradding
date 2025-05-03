<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class  BankDetailsController extends Controller
{
	  public function find_bank(){
			$data = DB::table('bank_details')->select('id','userid','upi_id','name','created_at')->orderBy('created_at', 'desc')->get();
			return view('bankdetails.bank_details')->with('data', $data);
		}
	
		public function update_bank_detail(Request $request){
		$request->validate([
			'id' => 'required|integer|exists:bank_details,id',
			'upi_id' => 'required|string|max:255',
		]);

		DB::table('bank_details')
			->where('id', $request->id)
			->update([
				'upi_id' => $request->upi_id,
				'updated_at' =>  now('Asia/Kolkata'),
			]);

		return redirect()->back()->with('success', 'Bank UPI ID updated successfully.');
	}

}



