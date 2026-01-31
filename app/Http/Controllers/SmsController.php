<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    /**
     * Display SMS dashboard.
     */
    public function index()
    {
        return view('sms.index');
    }

    /**
     * Show manual SMS form.
     */
    public function manual()
    {
        return view('sms.manual');
    }

    /**
     * Show pattern SMS form.
     */
    public function pattern()
    {
        return view('sms.pattern');
    }

    /**
     * Show pattern test form.
     */
    public function patternTest()
    {
        return view('sms.pattern-test');
    }

    /**
     * Show violation SMS form.
     */
    public function violation()
    {
        return view('sms.violation');
    }

    /**
     * Show sent SMS messages.
     */
    public function sent()
    {
        return view('sms.sent');
    }
}
