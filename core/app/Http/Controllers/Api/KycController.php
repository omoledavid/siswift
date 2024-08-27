<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kyc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    // Display the user's KYC data or return an empty array if none is found
    public function index(): JsonResponse
    {
        $kyc = Kyc::query()->where('user_id', Auth::id())->first();
        $progress = $this->calculateProgress($kyc);

        if (!$kyc) {
            return response()->json([
                'kyc' => [],
                'progress' => 0,
            ]);
        }

        return response()->json([
            'kyc' => $kyc,
            'progress' => $progress,
        ]);
    }

    // Store the KYC selfie or ID files
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'selfie' => 'nullable|image|max:2048',
            'id_card' => 'nullable|image|max:2048',
        ]);

        $kyc = Kyc::firstOrCreate(['user_id' => Auth::id()]);

        if ($request->hasFile('selfie')) {
            $kyc->selfie = $request->file('selfie')->store('kyc/selfies', 'public');
        }
        if ($request->hasFile('id_front')) {
            $kyc->id_front = $request->file('id_front')->store('kyc/ids', 'public');
        }
        if ($request->hasFile('id_back')) {
            $kyc->id_back = $request->file('id_back')->store('kyc/ids', 'public');
        }

        $kyc->save();

        $progress = $this->calculateProgress($kyc);

        return response()->json([
            'message' => 'KYC data uploaded successfully!',
            'kyc' => $kyc,
            'progress' => $progress,
        ]);
    }

    // Display the specific KYC data or return an empty array if not found
    public function show($id): JsonResponse
    {
        $kyc = Kyc::query()->where('user_id', Auth::id())->find($id);

        if (!$kyc) {
            return response()->json([
                'kyc' => [],
                'progress' => 0,
            ]);
        }

        $progress = $this->calculateProgress($kyc);

        return response()->json([
            'kyc' => $kyc,
            'progress' => $progress,
        ]);
    }

    // Update the KYC data (similar to store)
    public function update(Request $request, $id)
    {
        $kyc = Kyc::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'selfie' => 'nullable|image|max:2048',
            'id_front' => 'nullable|image|max:2048',
            'id_back' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('selfie')) {
            $kyc->selfie = $request->file('selfie')->store('kyc/selfies', 'public');
        }
        if ($request->hasFile('id_front')) {
            $kyc->id_front = $request->file('id_front')->store('kyc/ids', 'public');
        }
        if ($request->hasFile('id_back')) {
            $kyc->id_back = $request->file('id_back')->store('kyc/ids', 'public');
        }

        $kyc->save();

        $progress = $this->calculateProgress($kyc);

        return response()->json([
            'message' => 'KYC data updated successfully!',
            'kyc' => $kyc,
            'progress' => $progress,
        ]);
    }

    // Delete KYC data (optional, depending on your use case)
    public function destroy($id)
    {
        $kyc = Kyc::where('user_id', Auth::id())->findOrFail($id);
        $kyc->delete();

        return response()->json([
            'message' => 'KYC data deleted successfully!',
        ]);
    }

    // Calculate the progress of KYC completion
    private function calculateProgress($kyc): float|int
    {
        $steps = 2;
        $completed = 0;

        if ($kyc && $kyc->selfie) $completed++;
        if ($kyc && $kyc->id_card) $completed++;

        return ($completed / $steps) * 100;
    }
}
