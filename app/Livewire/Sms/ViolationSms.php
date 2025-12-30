<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SmsMessageResident;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Pattern;

class ViolationSms extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $reportFilter = '';
    public $patternFilter = '';
    public $dateFilter = '';
    public $selectedSms = null;
    public $showModal = false;

    public function mount()
    {
        //
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingReportFilter()
    {
        $this->resetPage();
    }

    public function updatingPatternFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function openModal($smsId)
    {
        $this->selectedSms = SmsMessageResident::with(['resident', 'report', 'pattern'])
            ->find($smsId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->selectedSms = null;
        $this->showModal = false;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->reportFilter = '';
        $this->patternFilter = '';
        $this->dateFilter = '';
        $this->resetPage();
    }

    public function getReportsListProperty()
    {
        return Report::orderBy('title')->get();
    }

    public function getPatternsListProperty()
    {
        return Pattern::orderBy('title')->get();
    }

    public function render()
    {
        $query = SmsMessageResident::with(['resident', 'report', 'pattern'])
            ->where('is_pattern', true)
            ->whereNotNull('report_id')
            ->orderBy('created_at', 'desc');

        // فیلتر جستجو
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('resident_name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('resident', function ($q) {
                      $q->where('full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('report', function ($q) {
                      $q->where('title', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('pattern', function ($q) {
                      $q->where('title', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // فیلتر وضعیت
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // فیلتر گزارش
        if ($this->reportFilter) {
            $query->where('report_id', $this->reportFilter);
        }

        // فیلتر الگو
        if ($this->patternFilter) {
            $query->where('pattern_id', $this->patternFilter);
        }

        // فیلتر تاریخ
        if ($this->dateFilter) {
            $query->whereDate('created_at', $this->dateFilter);
        }

        $smsList = $query->paginate(20);

        return view('livewire.sms.violation-sms', [
            'smsList' => $smsList,
        ]);
    }
}

