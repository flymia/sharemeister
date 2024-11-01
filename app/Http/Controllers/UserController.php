<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loggedUser = auth()->user();

        return view('dashboard.settings',  ['loggedUser' => $loggedUser]);
    }

}
