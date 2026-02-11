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
 * Store multiple resources in storage.
 */
public function store(Request $request)
{
    // 1. Validation for an array of images
    $request->validate([
        'image' => 'required|array',
        'image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $uploadedCount = 0;
    $folderPath = 'screenshots/' . date('Y/m/d') . '/';

    // 2. Loop through each file
    foreach ($request->file('image') as $file) {
        $imageName = str()->random(8) . '.' . $file->extension();

        // Prevent collisions
        while (Screenshot::where('image', 'like', "%$imageName")->exists()) {
            $imageName = str()->random(8) . '.' . $file->extension();
        }

        // 3. Store the file on 'public' disk
        $file->storeAs($folderPath, $imageName, 'public');

        // 4. Create DB record
        Screenshot::create([
            'image' => $folderPath . $imageName,
            'uploader_id' => Auth::id(),
        ]);

        $uploadedCount++;
    }

    return redirect()->route('screenshot.upload')->with([
        'success' => "$uploadedCount screenshots uploaded successfully.",
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

    public function dashboard()
    {
        $user = Auth::user();
        
        // Get latest 5
        $screenshots = Screenshot::where('uploader_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Calculate stats for the user
        $totalCount = Screenshot::where('uploader_id', $user->id)->count();
        $totalSizeKb = Screenshot::where('uploader_id', $user->id)->get()->sum(fn($s) => $s->file_size_kb);

        return view('dashboard.dashboard', [
            'screenshots' => $screenshots,
            'totalCount' => $totalCount,
            'totalSize' => round($totalSizeKb / 1024, 2) // Convert to MB
        ]);
    }

}
