<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use App\Models\Resident;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 20;
    
    protected $paginationTheme = 'bootstrap';
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function syncResidents()
    {
        try {
            // اجرای Job همگام‌سازی
            $job = new \App\Jobs\SyncResidentsFromApi();
            $job->handle();
            
            // نمایش پیام موفقیت
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'message' => 'داده‌ها با موفقیت همگام‌سازی شدند.'
            ]);
            
        } catch (\Exception $e) {
            // نمایش پیام خطا
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا!',
                'message' => 'خطا در همگام‌سازی: ' . $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        $residents = Resident::query()
            ->when($this->search, function ($query) {
                $query->where('resident_full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('resident_phone', 'like', '%' . $this->search . '%')
                    ->orWhere('unit_name', 'like', '%' . $this->search . '%')
                    ->orWhere('room_name', 'like', '%' . $this->search . '%')
                    ->orWhere('bed_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('resident_id', 'desc')
            ->paginate($this->perPage);
        
        return view('livewire.residents.index', [
            'residents' => $residents
        ]);
    }
}
