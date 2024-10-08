<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }


    public function sendResetCodeEmail(Request $request)
    {
        if ($request->email) {
            $validationRule = [
                'email'=>'required|email'
            ];
            $validationMessage = [
                'value.required'=>'Email field is required',
                'value.email'=>'Email must be an valide email'
            ];
        }elseif($request->type == 'username'){
            $validationRule = [
                'value'=>'required'
            ];
            $validationMessage = ['value.required'=>'Username field is required'];
        }else{
            return response()->json([
                'code'=>409,
                'status'=>'Failed',
                'message'=>'Invalid request',
            ]);
        }
        $validator = Validator::make($request->all(),$validationRule,$validationMessage);
        if ($validator->fails()) {
            return response()->json([
                'status'=>'Failed',
                'message'=>['error'=>$validator->errors()->all()],
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $notify[] = 'User not found.';
            return response()->json([
                'status'=>'Failed',
                'message'=>$notify,
            ], 400);
        }

        PasswordReset::where('email', $user->email)->delete();
        $code = verificationCode(6);
        $password = new PasswordReset();
        $password->email = $user->email;
        $password->token = $code;
        $password->created_at = \Carbon\Carbon::now();
        $password->save();

        $userIpInfo = getIpInfo();
        $userBrowserInfo = osBrowser();
        sendEmail($user, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => @$userBrowserInfo['os_platform'],
            'browser' => @$userBrowserInfo['browser'],
            'ip' => @$userIpInfo['ip'],
            'time' => @$userIpInfo['time']
        ]);
        $email = $user->email;
        $notify[] = 'Password reset email sent successfully';
        return response()->json([
            'message'=>['success'=>$notify],
            'data'=>['email'=>$email]
        ]);
    }


    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'=>'Failed',
                'message'=>['error'=>$validator->errors()->all()]
            ], 400);
        }

        $code =  $request->code;

        if (PasswordReset::where('token', $code)->where('email', $request->email)->count() != 1) {
            $notify[] = 'Invalid token';
            return response()->json([
                'message'=>['error'=>$notify],
            ], 400);
        }

        $notify[] = 'You can change your password';
        return response()->json([
            'message'=>['success'=>$notify],
            'data'=>[
                'token'=>$code,
                'email'=>$request->email,
            ]
        ]);
    }

}
