<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class Navigation extends Component
{
    public $isSidebarOpen = false;

    public function toggleSidebar()
    {
        $this->isSidebarOpen = !$this->isSidebarOpen;
    }

    public function render()
    {
        return view('livewire.layout.navigation');
    }
}
