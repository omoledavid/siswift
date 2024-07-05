<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Deposit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Models\GatewayCurrency;
use App\Rules\FileTypeValidate;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    private $activeTemplate = null;

    public function __construct()
    {
        return $this->activeTemplate = activeTemplate();
    }

    public function deposit()
    {
        $order = Order::where('order_number', session('order_number'))->first();

        if ($order) {
            if ($order->payment_status == 1)
                return redirect('/');
        }
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->with('method')->orderby('method_code')->get();
        $pageTitle = 'Payment Methods';
        return view($this->activeTemplate . 'user.payment.deposit', compact('gatewayCurrency', 'pageTitle'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'method_code' => 'required',
            'currency' => 'required',
        ]);

        $user = auth()->user();

        $order = Order::where('order_number', session('order_number'))->first();

        if ($order->payment_status == 1) {
            $notify[] = ['error', 'Payment is already completed'];
            return redirect('/')->withNotify($notify);
        }

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', 1);
        })->where('method_code', $request->method_code)->where('currency', $request->currency)->first();

        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }
        $charge     = $gate->fixed_charge + ($order->total_amount * $gate->percent_charge / 100);
        $payable    = $order->total_amount + $charge;
        $final_amo  = $payable * $gate->rate;
        $deposit    = Deposit::where('order_id', $order->id)->first();

        if (!$deposit) {
            $deposit               = new Deposit();
        }

        $deposit->user_id          = $user->id;
        $deposit->method_code      = $gate->method_code;
        $deposit->method_currency  = strtoupper($gate->currency);
        $deposit->amount           = $order->total_amount;
        $deposit->charge           = $charge;
        $deposit->rate             = $gate->rate;
        $deposit->final_amo        = $final_amo;
        $deposit->btc_amo          = 0;
        $deposit->btc_wallet       = "";
        $deposit->trx              = getTrx();
        $deposit->try              = 0;
        $deposit->status           = 0;
        $deposit->order_id         = $order->id;
        $deposit->save();
        session()->put('Track', $deposit->trx);
        return redirect()->route('user.deposit.preview');
    }

    public function depositPreview()
    {
        $track      = session()->get('Track');
        $data       = Deposit::where('trx', $track)->where('status', 0)->orderBy('id', 'DESC')->firstOrFail();
        $pageTitle  = 'Payment Preview';
        return view($this->activeTemplate . 'user.payment.preview', compact('data', 'pageTitle'));
    }


    public function depositConfirm()
    {
        $track      = session()->get('Track');
        $deposit    = Deposit::where('trx', $track)
            ->where('status', 0)
            ->orderBy('id', 'DESC')
            ->with('gateway')
            ->firstOrFail();

        if ($deposit->method_code >= 1000) {
            $this->userDataUpdate($deposit);
            $notify[] = ['success', 'Your order request is queued for approval.'];
            return back()->withNotify($notify);
        }

        $dirName = $deposit->gateway->alias;
        $new = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);

        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return redirect()->route(gatewayRedirectUrl())->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view($this->activeTemplate . $data->view, compact('data', 'pageTitle', 'deposit'));
    }


    public static function userDataUpdate($trx)
    {
        $general = GeneralSetting::first();
        $data = Deposit::where('trx', $trx)->first();
        Cart::where('user_id', auth()->id() ?? null)->delete();
        if ($data->status == 0) {
            $data->status = 1;
            $data->save();
            $user           = User::find($data->user_id);
            $transaction = new Transaction();
            $transaction->user_id = $data->user_id;
            $transaction->amount = $data->amount;
            $transaction->charge = $data->charge;
            $transaction->trx_type = '+';
            $transaction->details = 'Payment Via ' . $data->gatewayCurrency()->name;
            $transaction->trx = $data->trx;
            $transaction->save();

            $order = Order::where('id', $data->order_id)->first();
            $order->payment_status = 1;
            $order->save();

            $adminNotification = new AdminNotification();
            $adminNotification->user_id = $user->id;
            $adminNotification->title = 'Payment successful via ' . $data->gatewayCurrency()->name;
            $adminNotification->click_url = urlPath('admin.deposit.successful');
            $adminNotification->save();

            notify($user, 'DEPOSIT_COMPLETE', [
                'method_name' => $data->gatewayCurrency()->name,
                'method_currency' => $data->method_currency,
                'method_amount' => showAmount($data->final_amo),
                'amount' => showAmount($data->amount),
                'charge' => showAmount($data->charge),
                'currency' => $general->cur_text,
                'rate' => showAmount($data->rate),
                'trx' => $data->trx,
                'order_id' => $order->order_number
            ]);
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', 0)->where('trx', $track)->first();
        if (!$data) {
            return redirect()->route(gatewayRedirectUrl());
        }
        if ($data->method_code > 999) {

            $pageTitle = 'Deposit Confirm';
            $method = $data->gatewayCurrency();
            return view($this->activeTemplate . 'user.manual_payment.manual_confirm', compact('data', 'pageTitle', 'method'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', 0)->where('trx', $track)->first();
        if (!$data) {
            return redirect()->route(gatewayRedirectUrl());
        }

        $params = json_decode($data->gatewayCurrency()->gateway_parameter);

        $rules = [];
        $inputField = [];
        $verifyImages = [];

        if ($params != null) {
            foreach ($params as $key => $custom) {
                $rules[$key] = [$custom->validation];
                if ($custom->type == 'file') {
                    array_push($rules[$key], 'image');
                    array_push($rules[$key], new FileTypeValidate(['jpg', 'jpeg', 'png']));
                    array_push($rules[$key], 'max:2048');

                    array_push($verifyImages, $key);
                }
                if ($custom->type == 'text') {
                    array_push($rules[$key], 'max:191');
                }
                if ($custom->type == 'textarea') {
                    array_push($rules[$key], 'max:300');
                }
                $inputField[] = $key;
            }
        }
        $this->validate($request, $rules);


        $directory = date("Y") . "/" . date("m") . "/" . date("d");
        $path = imagePath()['verify']['deposit']['path'] . '/' . $directory;
        $collection = collect($request);
        $reqField = [];
        if ($params != null) {
            foreach ($collection as $k => $v) {
                foreach ($params as $inKey => $inVal) {
                    if ($k != $inKey) {
                        continue;
                    } else {
                        if ($inVal->type == 'file') {
                            if ($request->hasFile($inKey)) {
                                try {
                                    $reqField[$inKey] = [
                                        'field_name' => $directory . '/' . uploadImage($request[$inKey], $path),
                                        'type' => $inVal->type,
                                    ];
                                } catch (\Exception $exp) {
                                    $notify[] = ['error', 'Could not upload your ' . $inKey];
                                    return back()->withNotify($notify)->withInput();
                                }
                            }
                        } else {
                            $reqField[$inKey] = $v;
                            $reqField[$inKey] = [
                                'field_name' => $v,
                                'type' => $inVal->type,
                            ];
                        }
                    }
                }
            }
            $data->detail = $reqField;
        } else {
            $data->detail = null;
        }



        $data->status = 2; // pending
        $data->save();

        Cart::where('user_id', auth()->user()->id ?? null)->delete();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $data->user->id;
        $adminNotification->title = 'Deposit request from ' . $data->user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        $general = GeneralSetting::first();

        notify($data->user, 'DEPOSIT_REQUEST', [
            'method_name' => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount' => showAmount($data->final_amo),
            'amount' => showAmount($data->amount),
            'charge' => showAmount($data->charge),
            'currency' => $general->cur_text,
            'rate' => showAmount($data->rate),
            'trx' => $data->trx,
            'order_id' => $data->order->order_number
        ]);

        $notify[] = ['success', 'You have order request has been taken.'];
        return redirect()->route('user.deposit.history')->withNotify($notify);
    }
}
