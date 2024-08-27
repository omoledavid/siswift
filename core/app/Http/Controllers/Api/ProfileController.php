<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index(){
        $user = auth()->user();
        return response()->json([
            'status' => 'sucess',
            'profile' => $user
        ]);
    }
    public function update(Request $request) {
        $user = $request->user();

        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'address' => 'sometimes|required|max:80',
            'state' => 'sometimes|required|max:80',
            'zip' => 'sometimes|required|max:40',
            'city' => 'sometimes|required|max:50',
            'image' => ['image', new FileTypeValidate(['jpg','jpeg','png'])]
        ], [
            'firstname.required' => 'First name field is required',
            'lastname.required' => 'Last name field is required'
        ]);

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;

        $user->address = [
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $user->address->country ?? null,
            'city' => $request->city,
        ];

        if ($request->hasFile('image')) {
            $location = imagePath()['profile']['user']['path'];
            $size = imagePath()['profile']['user']['size'];
            $filename = uploadImage($request->image, $location, $size, $user->image);


            // Ensure filename is correctly assigned
            if ($filename) {
                $user->image = $filename;
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Image upload failed.'
                ]);
            }
        }

        $user->save();

        $notify = 'Profile updated successfully.';
        return response()->json([
            'status' => 'true',
            'message' => $notify,
            'profile' => $user
        ]);
    }


}
