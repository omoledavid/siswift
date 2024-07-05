<?php

namespace App\Http\Controllers;
use App\Models\Brand;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Language;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use App\Models\Frontend;
use App\Traits\SupportTicketManager;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    use SupportTicketManager;

    public function __construct(){
        $this->activeTemplate = activeTemplate();
    }

    public function index(){

        $topSellingProducts = Product::topSales(9);
        $featuredProducts   = Product::active()->featured()->where('status', 1)->inRandomOrder()->take(6)->get();
        $latestProducts     = Product::active()->latest()->where('status', 1)->inRandomOrder()->take(12)->get();
        $featuredSeller     = Seller::active()->featured()->whereHas('shop')->with('shop')->inRandomOrder()->take(16)->get();
        $topBrands          = Brand::top()->inRandomOrder()->take(16)->get();
        $pageTitle          = 'Home';
        $offers             = Offer::where('status', 1)->where('end_date', '>', now())
                                ->with(['products'=> function($q){ return $q->whereHas('categories')->whereHas('brand');},
                                    'products.reviews'
                                ])->get();

        return view($this->activeTemplate . 'home', compact('pageTitle', 'offers', 'topSellingProducts','featuredProducts','featuredSeller','topBrands', 'latestProducts'));
    }

    public function contact()
    {
        $pageTitle = "Contact Us";
        return view($this->activeTemplate . 'contact',compact('pageTitle'));
    }

    public function contactSubmit(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:191',
            'email' => 'required|max:191',
            'subject' => 'required|max:100',
            'message' => 'required',
        ]);
        $request->merge(['priority' => 2]);
        $ticket = $this->storeTicket($request, null, null);
        return redirect()->route('ticket.view.guest', $ticket['ticket'])->withNotify($ticket['message']);
    }

    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'en';
        session()->put('lang', $lang);
        return redirect()->back();
    }

    public function addSubscriber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $if_exist = Subscriber::where('email', $request->email)->first();
        if (!$if_exist) {
            Subscriber::create([
                'email' => $request->email
            ]);
            return response()->json(['success' => 'Subscribed Successfully']);
        } else {
            return response()->json(['error' => 'Already Subscribed']);
        }
    }

    public function pageDetails($id,$slug)
    {
        $pageDetails  = Frontend::findOrFail($id);
        $pageTitle = $pageDetails->data_values->pageTitle;
        return view($this->activeTemplate.'page_details',compact('pageTitle','pageDetails'));
    }


    public function cookieAccept(){
        header('Access-Control-Allow-Origin:  *');
        session()->put('cookie_accepted',true);
        return response()->json(['success' => 'Cookie has been accepted']);
    }

    public function placeholderImage($size = null){
        $imgWidth = explode('x',$size)[0];
        $imgHeight = explode('x',$size)[1];
        $text = $imgWidth . '×' . $imgHeight;
        $fontFile = realpath('assets/font') . DIRECTORY_SEPARATOR . 'RobotoMono-Regular.ttf';
        $fontSize = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if($imgHeight < 100 && $fontSize > 30){
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

}
