<?php

namespace App\Livewire\Residents;

use App\Models\Resident;
use Livewire\Component;
use Livewire\WithPagination;

class ExpiredToday extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 20;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // دریافت تاریخ امروز به صورت شمسی
        $todayJalali = null;
        if (class_exists(\Morilog\Jalali\Jalalian::class)) {
            $todayJalali = \Morilog\Jalali\Jalalian::fromCarbon(now())->format('Y/m/d');
        } else {
            // Fallback: تبدیل دستی به شمسی
            $todayJalali = now()->format('Y/m/d');
        }

        $query = Resident::where('contract_payment_date_jalali', $todayJalali)
            ->whereNotNull('contract_payment_date_jalali')
            ->where('contract_payment_date_jalali', '!=', '');

        // جستجو در نام، تلفن
        if ($this->search) {
            $query->where(function($q) {
                $q->where('resident_full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('resident_phone', 'like', '%' . $this->search . '%');
            });
        }

        $residents = $query->orderBy('resident_full_name', 'asc')
            ->paginate($this->perPage);

        return view('livewire.residents.expired-today', [
            'residents' => $residents,
        ]);
    }
}


