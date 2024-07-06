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

    protected $username;

    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {
        $this->username = $this->findUsername();
    }

    public function login(Request $request)
    {

        $validator = $this->validateLogin($request);

        if ($validator->fails()) {
            return response()->json([
                'code' => 200,
                'status' => 'ok',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $credentials = request([$this->username, 'password']);
        if (!Auth::guard('api')->attempt($credentials)) {
            $response[] = 'Unauthorized user';
            return response()->json([
                'code' => 401,
                'status' => 'unauthorized',
                'message' => ['error' => $response],
            ]);
        }

        $user = auth()->guard('api')->user();
        $tokenResult = $user->createToken('auth_token')->plainTextToken;
        $this->authenticated($request, $user);
        $response[] = 'Login Succesfull';
        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'message' => ['success' => $response],
            'data' => [
                'user' => auth()->user(),
                'access_token' => $tokenResult,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function findUsername()
    {
        $login = request()->input('username');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }

    public function username()
    {
        return $this->username;
    }

    protected function validateLogin(Request $request)
    {
        $validation_rule = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        $validate = Validator::make($request->all(), $validation_rule);
        return $validate;
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        $notify[] = 'Logout Succesfull';
        return response()->json([
            'code' => 200,
            'status' => 'ok',
            'message' => ['success' => $notify],
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
