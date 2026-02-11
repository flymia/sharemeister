<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ScreenshotController extends Controller
{
    /**
     * Private Hilfsmethode, um den eigentlichen Upload-Prozess zu handhaben.
     * Zentralisiert die Logik für Web, API und RAW-Uploads.
     */
    private function handleUpload($file)
    {
        $folderPath = 'screenshots/' . date('Y/m/d') . '/';
        $imageName = str()->random(8) . '.' . $file->extension();

        // Kollisionsprüfung für den Dateinamen
        while (Screenshot::where('image', 'like', "%$imageName")->exists()) {
            $imageName = str()->random(8) . '.' . $file->extension();
        }

        // Speichern auf dem 'public' Disk
        $file->storeAs($folderPath, $imageName, 'public');

        // Datenbankeintrag erstellen
        return Screenshot::create([
            'image' => $folderPath . $imageName,
            'uploader_id' => Auth::id(),
            'file_size_kb' => round($file->getSize() / 1024),
        ]);
    }

    /**
     * Zeigt die Liste aller Screenshots des Users an.
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
     * Zeigt das Upload-Formular (Web).
     */
    public function create()
    {
        return view('screenshot.upload');
    }

    /**
     * Verarbeitet den Multi-Upload über das Web-Interface.
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
     * Zeigt die Detailseite eines Screenshots.
     */
    public function show(Request $request)
    {
        $screenshot = Screenshot::where('id', $request->id)
            ->where('uploader_id', Auth::id())
            ->firstOrFail();

        return view('screenshot.detail', ['screenshot' => $screenshot]);
    }

    /**
     * Liefert das reine Bild aus (Raw Link).
     */
    public function rawShow($filename) {
        // Sucht z.B. nach einem Pfad, der auf 'vMzylDRm.jpg' endet
        $screenshot = Screenshot::where('image', 'like', '%' . $filename)->firstOrFail();

        $path = storage_path('app/public/' . $screenshot->image);

        if (file_exists($path)) {
            return response()->file($path);
        } else {
            abort(404);
        }
    }

    /**
     * Löscht einen Screenshot (Datenbank & Dateisystem).
     */
    public function destroy(Request $request)
    {
        $toDelete = Screenshot::findOrFail($request->id);

        if ($toDelete->uploader_id != Auth::id()) {
            abort(403);
        }

        $toDeletePath = storage_path('app/public/' . $toDelete->image);
        
        if (File::exists($toDeletePath)) {
            File::delete($toDeletePath);
        }

        $toDelete->delete();

        return redirect()->route('screenshot.list')->with('message', 'Screenshot deleted successfully.');
    }

    /**
     * Dashboard-Übersicht mit Statistiken.
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

        return view('dashboard.dashboard', [
            'screenshots' => $screenshots,
            'totalCount' => $totalCount,
            'totalSize' => round($totalSizeKb / 1024, 2)
        ]);
    }

    /**
     * API Upload: Gibt ein JSON-Response für ShareX zurück.
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
     * API Upload RAW: Gibt nur den Public Link als Plain Text zurück.
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