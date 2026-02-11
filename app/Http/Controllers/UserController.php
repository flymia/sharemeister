<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $loggedUser = auth()->user();

        // check if the user already has a token
        $existingToken = $loggedUser->tokens()->where('name', 'uploadkey')->first();

        if ($existingToken) {
            session([
                'userHasAPIKey' => true,
                'apiKeyCreatedAt' => $existingToken->created_at->format('Y-m-d') // format date
            ]);
        } else {
            session()->forget(['userHasAPIKey', 'apiKeyCreatedAt']); // clear session if no key
        }

        return view('dashboard.settings', ['loggedUser' => $loggedUser]);
    }

    public function generateApiKey(Request $request)
    {
        $user = $request->user();
        
        // 1. Delete existing tokens if you only want one active key
        $user->tokens()->delete();

        // 2. Create new token
        $token = $user->createToken('sharex-api-key')->plainTextToken;

        return back()->with([
            'apikey' => $token,
            'message' => 'New API key generated.'
        ]);
    }

    public function deleteApiKey(Request $request)
    {
        $request->user()->tokens()->delete();
        return back()->with('message', 'API key deleted. Apps using this key will no longer work.');
    }

}
