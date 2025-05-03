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

class SettingsController extends Controller
{
     public function Transaction_limit(){
          $limits = DB::table('business_settings')->whereIN('id', [15,16,17,18,20])->get();
          return view('manualpayment.Transaction_limit')->with('limits', $limits);
      }
      
      public function updateSiteSetting(Request $request){
            $request->validate([
                'id' => 'required|exists:business_settings,id',
                'longtext' => 'required|numeric|min:0',
            ]);
            
            DB::table('business_settings')->where('id', $request->id)->update([
                'longtext' => $request->longtext,
                'updated_at' => now(),
            ]);
        
            return back()->with('success', 'Limit updated successfully.');
        }
      
      public function banner(){
          $data = DB::table('slider')->get();
             return view('Settings.banner')->with('data', $data);
      }
      
    public function updateBanner(Request $request) {
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        'id' => 'required|exists:slider,id',
    ]);

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $filename = time() . '.' . $image->getClientOriginalExtension();

        $destinationPath = public_path('sliderimage');
        $image->move($destinationPath, $filename);

        // Full image URL
        $imagePath = url('sliderimage/' . $filename);
         $currentDate = Carbon::now('Asia/Kolkata')->format('Y-m-d h:i:s');
        // dd($currentTimestamp);
        DB::table('slider')->where('id', $request->id)->update([
            'images' => $imagePath,
            'datetime' => $currentDate,
        ]);
    }

    return redirect()->back()->with('success', 'Banner updated successfully!');
}
   public function addBanner(Request $request) {
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif'
    ]);

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $filename = time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('sliderimage');
        $image->move($destinationPath, $filename);

        DB::table('slider')->insert([
            'images' => url('sliderimage/' . $filename),
            'datetime' => now('Asia/Kolkata')->format('Y-m-d h:i:s'),
        ]);
    }

    return redirect()->back()->with('success', 'Banner added successfully!');
}



public function deleteBanner($id) {
    $banner = DB::table('slider')->where('id', $id)->first();

    if ($banner) {
        $imagePath = public_path(parse_url($banner->images, PHP_URL_PATH));
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        DB::table('slider')->where('id', $id)->delete();
    }

    return redirect()->back()->with('success', 'Banner deleted successfully!');
}

    public function getfeedback(){
       $items = DB::table('feedback')->orderBy('id','desc')->get();
        //dd($items);
         return view('Settings.Feedback')->with('items', $items);
    }
    
	
	
	
	
	
	
	
	public function reply(Request $request){
		
    $request->validate([
        'admin_reply' => 'required|string'
    ]);
    DB::table('feedback')
        ->where('id', $request->feedback_id)
        ->update(['admin_reply' => $request->admin_reply]);

    return redirect()->back()->with('success', 'Reply submitted successfully!');
}

	public function delete($id){
    DB::table('feedback')->where('id', $id)->delete();
    return redirect()->back()->with('success', 'Feedback deleted successfully.');
}
	
	
	
	
	
	
	
	
	
	
	
      public function Support_Channels(){
            $data = DB::table('support')->get();
            //dd($data);
             return view('Settings.SupportChannels ')->with('data', $data);
      }

    public function updateSupport(Request $request){
    $request->validate([
        'id' => 'required|integer|exists:support,id',
        'name' => 'required|string|max:100',
        'link' => 'required|url|max:255',
    ]);

    DB::table('support')
        ->where('id', $request->id)
        ->update([
            'name' => $request->name,
            'link' => $request->link,
            'updated_at' => now(),
        ]);

    return redirect()->back()->with('success', 'Support channel updated successfully!');
   }

    public function viewUniqueNotification(){
		$data = DB::table('notifications')->first();
		//dd($data);
		 return view('Settings.nitification')->with('data', $data);
	}
	
	
	public function updateNotification(Request $request)
{
    $request->validate([
        'id' => 'required|integer|exists:notifications,id',
        'notification' => 'required|string'
    ]);

    DB::table('notifications')->where('id', $request->id)->update([
        'notification' => $request->notification,
        'updated_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Notification updated successfully!');
}

	  public function view_salary(){
	  return view('salary');
	  }
	
}
