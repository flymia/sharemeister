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

    public function generateapikey(Request $request) {
        $user = $request->user();

        // check if the user already has a token
        $existingToken = $user->tokens()->where('name', 'uploadkey')->first();

        if ($existingToken) {
            return redirect('/account/settings')->with('message', 'You already have an API key!');
        }

        // create new token if none exists
        $token = $user->createToken('uploadkey');

        return redirect('/account/settings')->with('apikey', $token->plainTextToken);
    }

    public function deleteapikey(Request $request) {

    }

}
