<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResidentReportController extends Controller
{
    /**
     * Display a listing of resident reports.
     */
    public function index()
    {
        return view('resident-reports.index');
    }
}
