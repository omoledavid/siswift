<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Extension;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    protected $email;

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {
        $this->email = $this->findUserEmail();
    }

    public function login(Request $request)
    {

        $validator = $this->validateLogin($request);

        if ($validator->fails()) {
            return response()->json([
                'code' => 409,
                'status' => 'Failed',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $credentials = request([$this->email, 'password']);
        if (!Auth::guard('api')->attempt($credentials)) {
            $response[] = 'Email or Password is not correct';
            return response()->json([
                'code' => 401,
                'status' => 'unauthorized',
                'message' => ['error' => $response],
            ]);
        }

        $user = auth()->guard('api')->user();
        $tokenResult = $user->createToken('auth_token')->plainTextToken;
        $this->authenticated($request, $user);
        $response = 'Login Successfully';
        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'message' => [$response],
            'data' => [
                'user' => $user,
                'access_token' => $tokenResult,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function findUserEmail()
    {
        $login = request()->input('email');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }

    public function email()
    {
        return $this->email;
    }

    protected function validateLogin(Request $request)
    {
        $validation_rule = [
            $this->email() => 'required|string',
            'password' => 'required|string',
        ];

        $validate = Validator::make($request->all(), $validation_rule);
        return $validate;
    }

    public function logout()
    {
        auth('sanctum')->user()->tokens()->delete();

        $notify = 'Logout Succesfull';
        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'message' =>  $notify,
        ]);
    }

    public function authenticated(Request $request, $user)
    {
        if ($user->status == 0) {
            auth()->user()->tokens()->delete();
            $notify[] = 'Your account has been deactivated';
            return response()->json([
                'code' => 200,
                'status' => 'ok',
                'message' => ['success' => $notify],
            ]);
        }


        $user = auth('api')->user();
        $user->tv = $user->ts == 1 ? 0 : 1;
        $user->save();
    }
}
