<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use Illuminate\Http\Request;

class ScreenshotController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function display(Screenshot $screenshot)
    {
        return "Hallo";
    }

    public function handleUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|image', // Beispiel: nur Bilder erlauben
        ]);

        $filePath = $request->file('file')->store('screenshots');

        $screenshot = new Screenshot();
        $screenshot->file_path = $filePath;
        $screenshot->uploader_id = $request->user()->id; // Annahme: Authentifizierter Benutzer
        $screenshot->save();

        return response()->json(['message' => 'Screenshot erfolgreich hochgeladen'], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Screenshot $screenshot)
    {
        //
    }
}
