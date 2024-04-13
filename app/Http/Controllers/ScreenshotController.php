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
    public function show(Screenshot $screenshot)
    {
        return "Hallo";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Screenshot $screenshot)
    {
        //
    }
}
