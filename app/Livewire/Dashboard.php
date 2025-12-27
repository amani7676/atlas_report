<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;

class Dashboard extends Component
{
    public $totalReports;
    public $totalCategories;
    public $recentReports;

    public function mount()
    {
        $this->totalReports = Report::count();
        $this->totalCategories = Category::count();
        $this->recentReports = Report::with('category')
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
