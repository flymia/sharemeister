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
        //
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
