<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ScreenshotController extends Controller
{
    /**
     * Private helper to handle the core upload logic.
     * Centralizes logic for Web, API, and RAW uploads.
     * Now implements per-user directory structure.
     */
    private function handleUpload($file)
    {
        $user = auth()->user();
        $fileSizeKb = round($file->getSize() / 1024);
        $fileSizeMb = $fileSizeKb / 1024;

        // 1. Calculate current storage consumption
        $currentUsageKb = Screenshot::where('uploader_id', $user->id)->sum('file_size_kb');
        $currentUsageMb = $currentUsageKb / 1024;

        // 2. Check storage limits (skip if limit is -1)
        if ($user->storage_limit_mb != -1) {
            if (($currentUsageMb + $fileSizeMb) > $user->storage_limit_mb) {
                abort(403, 'Storage limit reached. Delete some screenshots or upgrade your plan.');
            }
        }

        // 3. Define path with User ID subfolder for better filesystem organization
        // Structure: screenshots/{user_id}/YYYY/MM/DD/
        $folderPath = 'screenshots/' . $user->id . '/' . date('Y/m/d') . '/';
        $imageName = str()->random(8) . '.' . $file->extension();
        
        $file->storeAs($folderPath, $imageName, 'public');

        return Screenshot::create([
            'image' => $folderPath . $imageName,
            'uploader_id' => $user->id,
            'file_size_kb' => $fileSizeKb,
        ]);
    }

    /**
     * Display a list of all screenshots belonging to the user.
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');

        $query = Screenshot::where('uploader_id', Auth::id());
        
        switch ($sort) {
            case 'created_at_desc':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $screenshots = $query->paginate(12)->appends(['sort' => $sort]);

        return view('screenshot.list', ['screenshots' => $screenshots, 'sort' => $sort]);
    }

    /**
     * Show the upload form (Web).
     */
    public function create()
    {
        return view('screenshot.upload');
    }

    /**
     * Handle multi-upload via web interface.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|array',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $files = $request->file('image');
        foreach ($files as $file) {
            $this->handleUpload($file);
        }

        return redirect()->route('screenshot.upload')->with([
            'success' => count($files) . " screenshots uploaded successfully.",
        ]);
    }

    /**
     * Display screenshot detail page.
     */
    public function show(Request $request)
    {
        $screenshot = Screenshot::where('id', $request->id)
            ->where('uploader_id', Auth::id())
            ->firstOrFail();

        return view('screenshot.detail', ['screenshot' => $screenshot]);
    }

    /**
     * Serve the raw image file.
     */
    public function rawShow($filename) {
        $screenshot = Screenshot::where('image', 'like', '%' . $filename)->firstOrFail();
        
        // Instead of letting PHP read the file, we tell Nginx to do it
        // This is much faster and bypasses PHP's memory limit
        return response()->file(storage_path('app/public/' . $screenshot->image), [
            'Content-Type' => 'image/png', // or dynamic
        ]);
    }

    /**
     * Delete a screenshot (Database & Filesystem).
     */
    public function destroy(Request $request)
    {
        $toDelete = Screenshot::findOrFail($request->id);

        // Security check: Only the owner can delete
        if ($toDelete->uploader_id != Auth::id()) {
            abort(403);
        }

        // Delete from storage disk using Laravel Storage facade for abstraction
        if (Storage::disk('public')->exists($toDelete->image)) {
            Storage::disk('public')->delete($toDelete->image);
        }

        $toDelete->delete();

        return redirect()->route('screenshot.list')->with('message', 'Screenshot deleted successfully.');
    }

    /**
     * Main dashboard view with statistics and storage visualization.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        $screenshots = Screenshot::where('uploader_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        $totalCount = Screenshot::where('uploader_id', $user->id)->count();
        $totalSizeKb = Screenshot::where('uploader_id', $user->id)->sum('file_size_kb');
        $totalSizeMb = round($totalSizeKb / 1024, 2);
        
        $limit = $user->storage_limit_mb;
        $usagePercent = 0;
        
        if ($limit > 0) {
            $usagePercent = round(($totalSizeMb / $limit) * 100, 2);
            // Cap the progress bar at 100%
            if ($usagePercent > 100) $usagePercent = 100;
        }

        return view('dashboard.dashboard', [
            'screenshots' => $screenshots,
            'totalCount' => $totalCount,
            'totalSize' => $totalSizeMb,
            'limit' => $limit,
            'usagePercent' => $usagePercent
        ]);
    }

    /**
     * API Upload: Returns JSON response for tools like ShareX.
     */
    public function apiUpload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $screenshot = $this->handleUpload($request->file('image'));

        return response()->json([
            'success' => true,
            'public_link' => $screenshot->publicURL,
            'message' => 'Upload successful'
        ]);
    }

    /**
     * API Upload RAW: Returns public link as plain text.
     */
    public function apiUploadRaw(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $screenshot = $this->handleUpload($request->file('image'));

        return response($screenshot->publicURL, 201)
            ->header('Content-Type', 'text/plain');
    }
}