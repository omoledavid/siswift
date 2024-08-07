<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Deposit;
use App\Models\Product;
use App\Models\SellLog;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function dashboard()
    {
        $pageTitle = 'All Shop\'s Analytics';

        // User Info
        $widget['total_users'] = User::count();
        $widget['verified_users'] = User::where('status', 1)->count();


        $widget['all_orders'] = Order::where('payment_status', '!=', 0)->count();
        $widget['delivered_orders'] = Order::where('payment_status', '!=', 0)->where('status', 3)->count();
        $recent_orders = Order::where('payment_status', '!=', 0)->latest()->take(6)->get();

        $widget['total_product'] = Product::whereHas('brand')->whereHas('categories')->count();

        $widget['last_seven_days'] = Deposit::where('status', 1)->where('created_at', '>=', Carbon::today()->subDays(7))->sum('amount');

        $widget['last_fifteen_days'] = Deposit::where('status', 1)->where('created_at', '>=', Carbon::today()->subDays(15))->sum('amount');

        $widget['last_thirty_days'] = Deposit::where('status', 1)->where('created_at', '>=', Carbon::today()->subDays(30))->sum('amount');

        $widget['top_selling_products'] = Product::topSales(3);
        $widget['total_deposit_amount'] = Deposit::where('status', 1)->sum('amount');
        $widget['total_seller'] = Seller::count();
        $widget['total_active_seller'] = Seller::where('status', 1)->count();

        // Monthly Deposit & Withdraw Report Graph
        $report['months'] = collect([]);
        $report['deposit_month_amount'] = collect([]);
        $report['withdraw_month_amount'] = collect([]);


        $depositsMonth = Deposit::where('created_at', '>=', Carbon::now()->subYear())
            ->where('status', 1)
            ->selectRaw("SUM( CASE WHEN status = 1 THEN amount END) as depositAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $depositsMonth->map(function ($depositData) use ($report) {
            $report['months']->push($depositData->months);
            $report['deposit_month_amount']->push(showAmount($depositData->depositAmount));
        });

        $withdrawalMonth = Withdrawal::where('created_at', '>=', Carbon::now()->subYear())->where('status', 1)
            ->selectRaw("SUM( CASE WHEN status = 1 THEN amount END) as withdrawAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $withdrawalMonth->map(function ($withdrawData) use ($report) {
            if (!in_array($withdrawData->months, $report['months']->toArray())) {
                $report['months']->push($withdrawData->months);
            }
            $report['withdraw_month_amount']->push(showAmount($withdrawData->withdrawAmount));
        });

        $months = $report['months'];

        for ($i = 0; $i < $months->count(); ++$i) {
            $monthVal = Carbon::parse($months[$i]);
            if (isset($months[$i + 1])) {
                $monthValNext = Carbon::parse($months[$i + 1]);
                if ($monthValNext < $monthVal) {
                    $temp = $months[$i];
                    $months[$i] = Carbon::parse($months[$i + 1])->format('F-Y');
                    $months[$i + 1] = Carbon::parse($temp)->format('F-Y');
                } else {
                    $months[$i] = Carbon::parse($months[$i])->format('F-Y');
                }
            }
        }

        // Withdraw Graph
        $withdrawal = Withdrawal::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->where('status', 1)
            ->selectRaw('sum(amount) as totalAmount')
            ->selectRaw('DATE(created_at) day')
            ->groupBy('day')->get();

        $withdrawals['per_day'] = collect([]);
        $withdrawals['per_day_amount'] = collect([]);
        $withdrawal->map(function ($withdrawItem) use ($withdrawals) {
            $withdrawals['per_day']->push(date('d M', strtotime($withdrawItem->day)));
            $withdrawals['per_day_amount']->push($withdrawItem->totalAmount + 0);
        });


        // Deposit Graph
        $deposit = Deposit::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->where('status', 1)
            ->selectRaw('sum(amount) as totalAmount')
            ->selectRaw('DATE(created_at) day')
            ->groupBy('day')->get();
        $deposits['per_day'] = collect([]);
        $deposits['per_day_amount'] = collect([]);
        $deposit->map(function ($depositItem) use ($deposits) {
            $deposits['per_day']->push(date('d M', strtotime($depositItem->day)));
            $deposits['per_day_amount']->push($depositItem->totalAmount + 0);
        });


        // user Browsing, Country, Operating Log
        $userLoginData = UserLogin::where('created_at', '>=', \Carbon\Carbon::now()->subDay(30))->get(['browser', 'os', 'country']);

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_os_counter'] = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);


        $payment['total_deposit_amount'] = Deposit::where('status', 1)->sum('amount');
        $payment['total_deposit_charge'] = Deposit::where('status', 1)->sum('charge');
        $payment['total_deposit_pending'] = Deposit::where('status', 2)->count();

        $paymentWithdraw['total_withdraw_amount'] = Withdrawal::where('status', 1)->sum('amount');
        $paymentWithdraw['total_withdraw_charge'] = Withdrawal::where('status', 1)->sum('charge');
        $paymentWithdraw['total_withdraw_pending'] = Withdrawal::where('status', 2)->count();
        return view('admin.dashboard', compact('pageTitle', 'widget', 'report', 'withdrawals', 'chart', 'payment', 'paymentWithdraw', 'depositsMonth', 'withdrawalMonth', 'months', 'deposits', 'recent_orders'));
    }

    public function dashboardSelf()
    {
        $pageTitle = 'My Shop\'s Analytics';

        $order['all'] = OrderDetail::orders()->where('seller_id', 0)->count();
        $order['pending'] = OrderDetail::pendingOrder()->where('seller_id', 0)->count();
        $order['processing'] = OrderDetail::processingOrder()->where('seller_id', 0)->count();
        $order['dispatched'] = OrderDetail::dispatchedOrder()->where('seller_id', 0)->count();
        $order['delivered'] = OrderDetail::deliveredOrder()->where('seller_id', 0)->count();
        $order['cancelled'] = OrderDetail::cancelledOrder()->where('seller_id', 0)->count();
        $order['cod'] = OrderDetail::cod()->where('seller_id', 0)->count();

        $product['total'] = Product::active()->where('seller_id', 0)->count();
        $product['total_sold'] = Product::active()->where('seller_id', 0)->sum('sold');
        $product['top_selling_products'] = Product::topSales(3);


        $sale['last_seven_days'] = SellLog::where('seller_id', 0)->where('created_at', '>=', Carbon::today()->subDays(7))->sum('product_price');

        $sale['last_fifteen_days'] = SellLog::where('seller_id', 0)->where('created_at', '>=', Carbon::today()->subDays(15))->sum('product_price');

        $sale['last_thirty_days'] = SellLog::where('seller_id', 0)->where('created_at', '>=', Carbon::today()->subDays(30))->sum('product_price');


        $report['months'] = collect([]);
        $report['sell_month_amount'] = collect([]);


        $sellMonth = SellLog::where('seller_id', 0)->where('created_at', '>=', Carbon::now()->subYear())
            ->selectRaw("SUM(product_price) as totalAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $sellMonth->map(function ($depositData) use ($report) {
            $report['months']->push($depositData->months);
            $report['sell_month_amount']->push(showAmount($depositData->depositAmount));
        });

        $months = $report['months'];

        for ($i = 0; $i < $months->count(); ++$i) {
            $monthVal = Carbon::parse($months[$i]);
            if (isset($months[$i + 1])) {
                $monthValNext = Carbon::parse($months[$i + 1]);
                if ($monthValNext < $monthVal) {
                    $temp = $months[$i];
                    $months[$i] = Carbon::parse($months[$i + 1])->format('F-Y');
                    $months[$i + 1] = Carbon::parse($temp)->format('F-Y');
                } else {
                    $months[$i] = Carbon::parse($months[$i])->format('F-Y');
                }
            }
        }


        $latestOrders = OrderDetail::pendingOrder()->where('seller_id', 0)
            ->with(['order', 'order.user', 'order.deposit.gateway'])
            ->orderBy('id', 'DESC')->take(10)->get();

        return view('admin.dashboard_self', compact('pageTitle', 'order', 'sale', 'months', 'report', 'sellMonth', 'product', 'latestOrders'));
    }


    public function profile()
    {
        $pageTitle = 'Profile';
        $admin = Auth::guard('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);
        $user = Auth::guard('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old = $user->image ?: null;
                $user->image = uploadImage($request->image, imagePath()['profile']['admin']['path'], imagePath()['profile']['admin']['size'], $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Your profile has been updated.'];
        return redirect()->route('admin.profile')->withNotify($notify);
    }


    public function password()
    {
        $pageTitle = 'Password Setting';
        $admin = Auth::guard('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|min:5|confirmed',
        ]);

        $user = Auth::guard('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password do not match !!'];
            return back()->withNotify($notify);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return redirect()->route('admin.password')->withNotify($notify);
    }

    public function notifications()
    {
        $notifications = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $pageTitle = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications'));
    }


    public function notificationRead($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->read_status = 1;
        $notification->save();
        return redirect($notification->click_url);
    }

    public function requestReport()
    {
        $pageTitle = 'Your Listed Report & Request';
        $arr['app_name'] = systemDetails()['name'];
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASE_CODE');
        $url = "https://license.siswift.com/issue/get?" . http_build_query($arr);
        $response = json_decode(curlContent($url));
        if ($response->status == 'error') {
            return redirect()->route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:bug,feature',
            'message' => 'required',
        ]);
        $url = 'https://license.siswift.com/issue/add';

        $arr['app_name'] = systemDetails()['name'];
        $arr['app_url'] = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASE_CODE');
        $arr['req_type'] = $request->type;
        $arr['message'] = $request->message;
        $response = json_decode(curlPostContent($url, $arr));
        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }
        $notify[] = ['success', $response->message];
        return back()->withNotify($notify);
    }

    public function systemInfo()
    {
        $laravelVersion = app()->version();
        $serverDetails = $_SERVER;
        $currentPHP = phpversion();
        $timeZone = config('app.timezone');
        $pageTitle = 'System Information';
        return view('admin.info', compact('pageTitle', 'currentPHP', 'laravelVersion', 'serverDetails', 'timeZone'));
    }

    public function readAll()
    {
        AdminNotification::where('read_status', 0)->update([
            'read_status' => 1
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function testCsv()
    {
        return (new UsersExport)->download('users.csv');
    }
}
