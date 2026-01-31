<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resident;

class ResidentController extends Controller
{
    /**
     * Display a listing of residents.
     */
    public function index()
    {
        return view('residents.index');
    }

    /**
     * Display residents that expire today.
     */
    public function expiredToday()
    {
        return view('residents.expired-today');
    }
}
