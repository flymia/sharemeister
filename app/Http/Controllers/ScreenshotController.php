<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Tag;
use App\Services\ScreenshotService;

class ScreenshotController extends Controller
{

    public function __construct(protected ScreenshotService $service) {}

    /**
     * Private helper to handle the core upload logic.
     * Centralizes logic for Web, API, and RAW uploads.
     * Now implements per-user directory structure.
     */
    private function handleUpload($file)
    {
        try {
            return $this->service->handleUpload($file, auth()->user());
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }


    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');
        $tagFilter = $request->query('tag'); // Der Slug des Tags

        // Eager loading der Tags für die Badges in der Liste
        $query = Screenshot::with('tags')->where('uploader_id', Auth::id());

        // Wenn ein Tag-Filter aktiv ist, nutzen wir eine Join-Abfrage
        if ($tagFilter) {
            $query->whereHas('tags', function($q) use ($tagFilter) {
                $q->where('slug', $tagFilter);
            });
        }

        // Sortierung
        if ($sort === 'created_at_desc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination (Wichtig: appends sorgt dafür, dass die URL-Parameter beim Umblättern erhalten bleiben)
        $screenshots = $query->paginate(12)->appends([
            'sort' => $sort,
            'tag' => $tagFilter
        ]);

        // Für die Filter-Bar brauchen wir alle Tags des Users
        $allUserTags = \App\Models\Tag::whereHas('screenshots', function($q) {
            $q->where('uploader_id', auth()->id());
        })->orderBy('name')->get();

        return view('screenshot.list', [
            'screenshots' => $screenshots, 
            'sort' => $sort,
            'allTags' => $allUserTags,
            'currentTag' => $tagFilter
        ]);
    }
    /**
     * Show the upload form (Web).
     */
    public function create()
    {
        return view('screenshot.upload', [
                'maxSizeKb' => config('app.max_upload_size')
        ]);
    }

    /**
     * Handle multi-upload via web interface.
     */
    public function store(Request $request)
    {
        // Get the limit directly from config to avoid 'Undefined variable'
        $maxSize = (int) config('app.max_upload_size');

        $request->validate([
            'image' => 'required|array',
            'image.*' => [
                'image',
                'mimes:jpeg,png,jpg,gif',
                "max:{$maxSize}" // The variable must be defined in THIS method
            ],
        ]);

        $files = $request->file('image');
        foreach ($files as $file) {
            // The service handles the internal logic
            $this->handleUpload($file);
        }

        return redirect()->route('screenshot.upload')->with([
            'success' => count($files) . " screenshots uploaded successfully.",
        ]);
    }

    /**
     * Display screenshot detail page.
     */
    public function show(Screenshot $screenshot)
    {
        // Security Check
        $this->authorize('view', $screenshot);

        // Falls du Tags nachladen willst (Eager Loading für die Performance)
        $screenshot->load('tags');

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
    public function destroy(Screenshot $screenshot)
    {
        // 1. Security check: Only the owner can delete
        $this->authorize('delete', $screenshot);

        // 2. Protection check: Prevent accidental deletion
        if ($screenshot->is_permanent) {
            return back()->with('error', 'This screenshot is protected. Disable protection in details before deleting.');
        }

        // Delete from storage
        if (Storage::disk('public')->exists($screenshot->image)) {
            Storage::disk('public')->delete($screenshot->image);
        }

        $screenshot->delete();

        return redirect()->route('screenshot.list')->with('message', 'Screenshot deleted successfully.');
    }

    /**
     * Main dashboard view with statistics and storage visualization.
     */
    public function dashboard(\App\Services\DashboardService $dashboardService)
    {
        $data = $dashboardService->getDashboardData(auth()->user());
        return view('dashboard.dashboard', $data);
    }

    /**
     * API Upload: Returns JSON response for tools like ShareX.
     */
    public function apiUpload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:' . config('app.max_upload_size'),
        ]);

        $screenshot = $this->handleUpload($request->file('image'));

        return response()->json([
            'success' => true,
            'public_link' => $screenshot->public_url,
            'message' => 'Upload successful'
        ]);
    }

    /**
     * API Upload RAW: Returns public link as plain text.
     */
    public function apiUploadRaw(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:' . config('app.max_upload_size'),
        ]);

        $screenshot = $this->handleUpload($request->file('image'));

        return response($screenshot->public_url, 201)
            ->header('Content-Type', 'text/plain');
    }

    public function updateMetadata(Request $request, Screenshot $screenshot)
    {
        // Security Check: Only the uploader can edit
        $this->authorize('update', $screenshot);

        // Validation
        $request->validate([
            'tags' => 'nullable|string',
        ]);

        // Toggle form submit: checkbox field is NOT guaranteed to be in request if unchecked
        // Instead of checking for 'is_permanent', let's check if the request contains the form signature
        if ($request->has('_token') && !$request->has('tags') && count($request->all()) <= 3) {
            $screenshot->update([
                'is_permanent' => $request->boolean('is_permanent')
            ]);
            return back()->with('success', 'Protection status updated.');
        }

        // Tag form submit
        if ($request->has('tags') || $request->has('_token')) {
            $screenshot->update([
                'is_permanent' => $request->boolean('is_permanent')
            ]);
            
            // Handle Tags
            if ($request->filled('tags')) {
                $screenshot->syncTags($request->tags);
            } else {
                $screenshot->tags()->detach();
            }
            return back()->with('success', 'Metadata updated.');
        }

        return back();
    }
}