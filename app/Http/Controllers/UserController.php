<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
/**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $loggedUser = auth()->user();

        // Keep a freshly generated key alive across the settings render so the
        // ShareX/bash download links (GET requests) can still embed it when clicked.
        if (session()->has('apikey')) {
            session()->keep(['apikey']);
        }

        // 1. Count the screenshots directly in the database (efficient SQL COUNT)
        $totalUploads = $loggedUser->screenshots()->count();

        // 2. Sum up the file size directly via SQL SUM to save container RAM
        $totalStorageKb = $loggedUser->screenshots()->sum('file_size_kb');
        $totalStorageMb = round($totalStorageKb / 1024, 1);

        // Pass the pre-calculated numbers directly to the view
        return view('dashboard.settings', [
            'loggedUser' => $loggedUser,
            'totalUploads' => $totalUploads,
            'totalStorageMb' => $totalStorageMb,
        ]);
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

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            // regex: allow letters, spaces, dots, apostrophes and hyphens
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{M}\s.\'-]+$/u'
            ],
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ], [
            'name.regex' => 'The name contains invalid characters or emojis. Please use text only.',
        ]);

        $emailChanged = $request->email !== $user->email;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Require re-verification when the email address changes.
        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
            $user->sendEmailVerificationNotification();

            return back()->with('message', 'Profile updated. Please verify your new email address.');
        }

        return back()->with('message', 'Profile updated successfully.');
    }

    public function downloadSxcu(Request $request)
    {
        // The plaintext token only exists in the flash session right after generation;
        // Sanctum stores a hash, so it can never be recovered later. Re-flash it so a
        // page reload keeps the download available until the user navigates away.
        $token = session('apikey');

        if (!$token) {
            return back()->with('error', 'Please regenerate your API key to download a ready-to-use config.');
        }

        // Keep the key available for a page reload / the other download link.
        $request->session()->keep(['apikey']);

        $config = [
           'Version' => '15.0.0',
            'Name' => 'Sharemeister (' . config('app.name') . ')',
            'DestinationType' => 'ImageUploader',
            'RequestMethod' => 'POST',
            'RequestURL' => route('api.screenshot.upload'),
            'Headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'Body' => 'MultipartFormData',
            'FileFormName' => 'image', 
            'URL' => '$json:public_link$',
            'ErrorMessage' => '$json:message$'
        ];

        $fileName = strtolower(config('app.name')) . '_config.sxcu';

        return response()->streamDownload(function () use ($config) {
            echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    public function downloadBashScript(Request $request)
    {
        $appName = config('app.name', 'Sharemeister');
        $url = route('api.screenshot.upload.raw');

        // Embed the freshly generated key if it is still in the flash session; otherwise
        // fall back to a placeholder the user fills in manually (the key can't be recovered).
        $apiKey = session('apikey') ?? 'YOUR_API_KEY_HERE';
        if (session()->has('apikey')) {
            $request->session()->keep(['apikey']);
        }

        $script = <<<'BASH'
#!/usr/bin/env bash

# This script was generated by Sharemeister.

# --- Configuration ---
BASH
    // The nowdoc above can't interpolate variables, so the API key, endpoint and app
    // name are concatenated in afterwards.
    . "\nAPI_KEY=\"{$apiKey}\"" .
    "\nENDPOINT=\"{$url}\"\n" .
    <<<'BASH'

# --- Safety Checks ---
if [ -z "$1" ]; then
    echo "Usage: $0 <path_to_file>"
    exit 1
fi

if [ ! -f "$1" ]; then
    echo "Error: File '$1' not found."
    exit 1
fi

RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$ENDPOINT" \
    -H "Authorization: Bearer $API_KEY" \
    -H "Accept: application/json" \
    -F "image=@$1")

HTTP_STATUS=$(echo "$RESPONSE" | tail -n 1)
CONTENT=$(echo "$RESPONSE" | sed '$d')

if [ "$HTTP_STATUS" -eq 200 ] || [ "$HTTP_STATUS" -eq 201 ]; then
    if [[ "$CONTENT" == http* ]]; then
        echo "$CONTENT"
    else
        echo "Error: Success status but no URL returned."
        echo "Response: $CONTENT"
        exit 1
    fi
else
    echo "Upload failed with status $HTTP_STATUS"
    echo "Message: $CONTENT"
    exit 1
fi
BASH;

        $fileName = strtolower($appName) . '_upload.sh';

        return response()->streamDownload(function () use ($script) {
            echo $script;
        }, $fileName, ['Content-Type' => 'application/x-sh']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            // Ensure the user knows their current password
            'current_password' => ['required', 'current_password'],
            // New password requirements (min 8 chars, letters, numbers)
            'new_password' => [
                'required', 
                'confirmed', 
                Password::min(8)->letters()->numbers()
            ],
        ], [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        // Update the password with a secure hash
        $request->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('password_success', 'Your password was changed successfully.');
    }

}
