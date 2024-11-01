<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ScreenshotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at'); // Default sort to "created_at"

        // Sort logic based on the parameter
        $query = Screenshot::where('uploader_id', Auth::id());
        switch ($sort) {
            case 'created_at_desc':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $screenshots = $query->paginate(10)->appends(['sort' => $sort]); // Paginate with sort parameters

        return view('screenshot.list', ['screenshots' => $screenshots, 'sort' => $sort]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('screenshot.upload');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Directory structure with year/month/day for the filesystem
        $folderPath = 'screenshots/' . date('Y/m/d') . '/';

        // Generate a random filename
        $imageName = str()->random(8) . '.' . $request->image->extension();

        // Check if the filename exists and generate a new one if necessary
        while (Screenshot::query()->where('image', '=', $imageName)->exists()) {
            $imageName = str()->random(8) . '.' . $request->image->extension();
        }

        // Move the image to the `storage/app/public/screenshots/...` folder
        $request->image->storeAs($folderPath, $imageName); // 'public/' removed

        // Store the relative path in the database
        $screenshot = new Screenshot();
        $screenshot->image = $folderPath . $imageName; // Relative path in the DB
        $screenshot->uploader_id = Auth::id();
        $screenshot->save();

        // Generate the public link
        $publicLink = route('screenshot.show', ['filename' => $imageName]);

        // Store the link in the session and redirect
        return redirect()->route('screenshot.upload')->with([
            'success' => 'Screenshot uploaded successfully.',
            'public_link' => $publicLink,
        ]);
    }

    /**
     * Displays the screenshot detail page
     */
    public function show(Request $request)
    {
        $screenshot = Screenshot::where('id', '=', $request->id)->where('uploader_id', Auth::id())->firstOrFail();
        return view('screenshot.detail', ['screenshot' => $screenshot]);
    }

    // Display the screenshot without anything else. Raw link.
    public function rawShow($filename) {
        // Search for the screenshot in the database
        $screenshot = Screenshot::where('image', 'like', '%' . $filename)->firstOrFail();

        // Build the complete path to the file in the storage directory
        $path = storage_path('app/public/' . $screenshot->image);

        // Check if the file exists and return it
        if (file_exists($path)) {
            return response()->file($path);
        } else {
            abort(404); // File not found
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Screenshot $screenshot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Screenshot $screenshot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $toDelete = Screenshot::find($request->id);

        // Abort the process if it is owned by another user.
        if($toDelete->uploader_id != Auth::id()){
            abort('403');
        }

        $toDeletePath = storage_path('app/public/' . $toDelete->image);
        File::delete($toDeletePath);
        $toDelete->delete();
        return redirect('/screenshots/list')->with('message', 'Screenshot deleted successfully.');
    }
}
