<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class BasicController extends Controller
{
    public function generalSetting(){
    	$general = GeneralSetting::first();
        $brands = Brand::all();
        $categories = Category::all();
		$notify[] = 'General setting data';
		return response()->json([
			'code'=>200,
			'status'=>'Success',
	        'message'=>$notify,
//	        'data'=>['general_setting'=>$general]
            'brands' => $brands,
            'categories' => $categories,
	    ]);
    }

    public function unauthenticate(){
    	$notify[] = 'Unauthenticated user';
		return response()->json([
			'code'=>403,
			'status'=>'unauthorized',
	        'message'=>['error'=>$notify]
	    ]);
    }

    public function languages(){
    	$languages = Language::get();
    	return response()->json([
			'code'=>200,
			'status'=>'ok',
	        'data'=>[
	        	'languages'=>$languages,
	        	'image_path'=>imagePath()['language']['path']
	        ]
	    ]);
    }

    public function languageData($code){
    	$language = Language::where('code',$code)->first();
    	if (!$language) {
    		$notify[] = 'Language not found';
    		return response()->json([
				'code'=>404,
				'status'=>'error',
		        'message'=>['error'=>$notify]
		    ]);
    	}
    	$jsonFile = strtolower($language->code) . '.json';
    	$fileData = resource_path('lang/').$jsonFile;
    	$languageData = json_decode(file_get_contents($fileData));
		return response()->json([
			'code'=>200,
			'status'=>'ok',
	        'message'=>[
	        	'language_data'=>$languageData
	        ]
	    ]);
    }
    public function plans(){
        $plans = Plan::where('type', 'sub')->get();
        $listing = Plan::where('type', 'listing')->get();
        $boost = Plan::where('type', 'boost')->get();
        return response()->json([
            'code'=>200,
            'status'=>'ok',
            'plans' => $plans,
            'listing' => $listing,
            'booster' => $boost
        ]);
    }
    public function allProducts(){
        return response()->json([Product::where('status',1)->paginate(10)]);
    }
    public function user($id){
        $user = User::find($id);
        if (!$user) {
            $notify[] = 'User not found';
            return response()->json([
                $notify
            ]);
        }
        return response()->json([
            'user' => $user
        ]);
    }
}
