<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        $sort = $request->query('sort', 'newest');
        $tagFilter = $request->query('tag'); // Der Slug des Tags
        $search = trim((string) $request->query('q', ''));

        // Eager loading der Tags für die Badges in der Liste
        $query = Screenshot::with('tags')->where('uploader_id', Auth::id());

        // Wenn ein Tag-Filter aktiv ist, nutzen wir eine Join-Abfrage
        if ($tagFilter) {
            $query->whereHas('tags', function($q) use ($tagFilter) {
                $q->where('slug', $tagFilter);
            });
        }

        // Filename-Suche (basename liegt auf der indizierten `filename`-Spalte)
        if ($search !== '') {
            $query->where('filename', 'like', '%' . $search . '%');
        }

        // Sortierung: standardmäßig neueste zuerst, auf Wunsch älteste zuerst
        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        // Pagination (Wichtig: appends sorgt dafür, dass die URL-Parameter beim Umblättern erhalten bleiben)
        $screenshots = $query->paginate(12)->appends([
            'sort' => $sort,
            'tag' => $tagFilter,
            'q' => $search !== '' ? $search : null,
        ]);

        // Für die Filter-Bar brauchen wir alle Tags des Users
        $allUserTags = \App\Models\Tag::whereHas('screenshots', function($q) {
            $q->where('uploader_id', auth()->id());
        })->orderBy('name')->get();

        return view('screenshot.list', [
            'screenshots' => $screenshots,
            'sort' => $sort,
            'allTags' => $allUserTags,
            'currentTag' => $tagFilter,
            'search' => $search,
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
                'mimes:jpeg,png,jpg,gif,webp',
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
        // Equality lookup on the indexed `filename` (basename) column - the basename is
        // globally unique by design, so this serves the hot image path without a table scan.
        $screenshot = Screenshot::where('filename', $filename)->firstOrFail();

        $contentType = match (strtolower(pathinfo($screenshot->image, PATHINFO_EXTENSION))) {
            'gif'         => 'image/gif',
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default       => 'image/webp',
        };

        // Stored images are immutable (random, content-addressed names) so they can be
        // cached aggressively by browsers and any upstream proxy/CDN.
        $cacheControl = 'public, max-age=31536000, immutable';

        // In production, hand the file off to nginx instead of streaming bytes through
        // PHP-FPM. The DB lookup above stays in PHP; nginx serves from the internal location.
        if (config('app.x_accel_redirect')) {
            return response('', 200, [
                'Content-Type'      => $contentType,
                'Cache-Control'     => $cacheControl,
                'X-Accel-Redirect'  => '/internal-storage/' . $screenshot->image,
            ]);
        }

        return response()->file(storage_path('app/public/' . $screenshot->image), [
            'Content-Type'  => $contentType,
            'Cache-Control' => $cacheControl,
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:' . config('app.max_upload_size'),
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:' . config('app.max_upload_size'),
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
            'form' => 'required|in:protection,tags',
            'tags' => 'nullable|string',
        ]);

        // The submitting form declares itself explicitly so each updates only its own fields.
        if ($request->input('form') === 'protection') {
            $screenshot->update([
                'is_permanent' => $request->boolean('is_permanent')
            ]);
            return back()->with('success', 'Protection status updated.');
        }

        // Tag form submit
        if ($request->filled('tags')) {
            $screenshot->syncTags($request->tags);
        } else {
            $screenshot->tags()->detach();
        }

        return back()->with('success', 'Metadata updated.');
    }
}