<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('regStatus')->except('registrationNotAllowed');
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $general = GeneralSetting::first();
        $password_validation = Password::min(6);
        if ($general->secure_password) {
            $password_validation = $password_validation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $validate = Validator::make($data, [
            'fullname' => 'sometimes|required|string|max:50',
            'email' => 'required|string|email|max:90|unique:users',
            'mobile' => 'required|string|max:50|unique:users',
            'password' => ['required','confirmed',$password_validation],
            'address' => 'required|max:50|string'
        ]);
        return $validate;
    }


    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'code'=>401,
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $exist = User::where('mobile',$request->mobile)->first();
        if ($exist) {
            $response[] = 'The mobile number already exists';
            return response()->json([
                'code'=>409,
                'status'=>'conflict',
                'message'=>['error'=>$response],
            ]);
        }


        $user = $this->create($request->all());

        $response['access_token'] =  $user->createToken('auth_token')->plainTextToken;
        $response['user'] = $user;
        $response['token_type'] = 'Bearer';
        $notify[] = 'Registration successfull';
        return response()->json([
            'code'=>202,
            'status'=>'created',
            'message'=>['success'=>$notify],
            'data'=>$response
        ]);

    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */
    protected function create(array $data)
    {

        $general = GeneralSetting::first();

        //User Create
        $user = new User();
        $user->fullname = $data['fullname'];
        $user->email = strtolower(trim($data['email']));
        $user->password = Hash::make($data['password']);
        $user->mobile = $data['mobile'];
        $user->address = $data['address'];
        $user->status = 1;
        $user->ev = $general->ev ? 0 : 1;
        $user->sv = $general->sv ? 0 : 1;
        $user->ts = 0;
        $user->tv = 1;
        $user->save();


        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail',$user->id);
        $adminNotification->save();
        return $user;
    }

}
