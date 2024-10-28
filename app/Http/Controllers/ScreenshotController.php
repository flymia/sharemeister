<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScreenshotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('screenshot.list');
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

        # Generate a random file name to store it with.
        $imageName = str()->random(8) . '.' . $request->image->extension();

        # Extremely unlikely edge case: If the random file name is already taken, generate another one:
        while(Screenshot::query()->orderBy('id', 'desc')->where('image', '=', $imageName)->count() >= 1) {
            $imageName = str()->random(8) . '.' . $request->image->extension();
        }

        $request->image->move(public_path('images'), $imageName);
        $screenshot = new Screenshot();
        $screenshot->image = 'images/'.$imageName;
        $screenshot->uploader_id = Auth::user()->id;
        $screenshot->save();
        return redirect()->route('screenshot.upload')->with('success', 'Screenshot uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Screenshot $screenshot)
    {
        //
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
    public function destroy(Screenshot $screenshot)
    {
        //
    }
}
