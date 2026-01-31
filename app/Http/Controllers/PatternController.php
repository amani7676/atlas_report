<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatternController extends Controller
{
    /**
     * Display a listing of patterns.
     */
    public function index()
    {
        return view('patterns.index');
    }

    /**
     * Show the form for creating a new pattern.
     */
    public function create()
    {
        return view('patterns.create');
    }

    /**
     * Store a newly created pattern in storage.
     */
    public function store(Request $request)
    {
        // Implementation needed
        return redirect()->route('patterns.index')->with('success', 'Pattern created successfully.');
    }

    /**
     * Show the form for editing the specified pattern.
     */
    public function edit($id)
    {
        return view('patterns.edit', compact('id'));
    }

    /**
     * Update the specified pattern in storage.
     */
    public function update(Request $request, $id)
    {
        // Implementation needed
        return redirect()->route('patterns.index')->with('success', 'Pattern updated successfully.');
    }

    /**
     * Remove the specified pattern from storage.
     */
    public function destroy($id)
    {
        // Implementation needed
        return redirect()->route('patterns.index')->with('success', 'Pattern deleted successfully.');
    }
}
