<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\Form;
use App\Models\Kyc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = auth()->user();
        $kyc = Kyc::where('user_id', $user->id)->first();
        if ($user->kv == 3) {
            $notify[] = 'Your KYC is 50% done';
            return response()->json([
                'remark' => 'under_review',
                'status' => 'error',
                'message' => ['error' => $notify],
                'data' => $kyc
            ]);
        }
        if ($user->kv == 2) {
            $notify[] = 'Your KYC is under review';
            return response()->json([
                'remark' => 'under_review',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        if ($user->kv == 1) {
            $notify[] = 'You are already KYC verified';
            return response()->json([
                'remark' => 'already_verified',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $form = Form::where('act', 'kyc')->first();
        $notify[] = 'KYC field is below';
        return response()->json([
            'remark' => 'kyc_form',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'form' => @$form->form_data,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'selfie' => 'required'
        ]);
        $kyc = Kyc::where('user_id', $user->id)->first();
        if($kyc){
            return response()->json(['kyc in review already']);
        }
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $kyc_selfie = null;
        if ($request->hasFile('selfie')) {
            try {
                $kyc_selfie = uploadImage($request->selfie, imagePath()['kyc']['path'], imagePath()['kyc']['size']);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return response()->json($notify);
            }
        }
        $kyc = Kyc::create([
            'nin' => $kyc_nin ?? NULL,
            'selfie' => $kyc_selfie ?? NULL,
            'user_id' => $user->id,
            'extra' => NULL
        ]);
        $user->kv = 3;
        $user->save();
        if ($kyc) {
            return response()->json(['status' => 'success', 'message' => 'KYC created successfully']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'nin' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $kyc = Kyc::where('user_id', $user->id)->where('id', $id)->first();
        $kyc_nin = null;
        if ($request->hasFile('nin')) {
            try {
                $kyc_nin = uploadImage($request->nin, imagePath()['kyc']['path'], imagePath()['kyc']['size']);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'NIN Image could not be uploaded.'];
                return response()->json($notify);
            }
        }
        $kyc->update([
            'nin' => $kyc_nin,
        ]);
        $user->kv = 2;
        $user->save();
        return response()->json(['status' => 'success', 'message' => 'KYC updated successfully']);
    }

    public function kycData()
    {
        $user = auth()->user();
        $notify[] = 'User KYC Data';
        return response()->json([
            'remark' => 'kyc_data',
            'status' => 'success',
            'message' => ['success' => $notify],
            'user' => $user->kyc_data,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
