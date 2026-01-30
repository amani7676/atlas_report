<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Resident;
use App\Models\Category;

class CreateResidentReport extends Component
{
    public $resident_id;
    public $report_id;
    public $notes;
    public $description;
    public $reports = [];
    public $categories = [];
    public $selectedCategory = null;

    public function mount($residentId = null)
    {
        $this->resident_id = $residentId;
        $this->loadReports();
    }

    public function loadReports()
    {
        $this->categories = Category::all();
        $this->reports = Report::all();
    }

    public function updatedSelectedCategory()
    {
        $this->reports = $this->selectedCategory 
            ? Report::where('category_id', $this->selectedCategory)->get()
            : Report::all();
    }

    protected $rules = [
        'resident_id' => 'required|exists:residents,resident_id',
        'report_id' => 'required|exists:reports,id',
        'description' => 'nullable|string|max:1000',
    ];

    public function save()
    {
        \Log::info('CreateResidentReport save() called', [
            'resident_id' => $this->resident_id,
            'report_id' => $this->report_id,
            'description' => $this->description,
            'notes' => $this->notes
        ]);
        
        $this->validate();

        // بررسی اینکه آیا این گزارش قبلاً برای این اقامت‌گر ثبت شده است
        $existingReport = ResidentReport::where('resident_id', $this->resident_id)
            ->where('report_id', $this->report_id)
            ->first();

        if ($existingReport) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'این گزارش قبلاً برای این اقامت‌گر ثبت شده است.'
            ]);
            return;
        }

        // دریافت اطلاعات اقامت‌گر
        $resident = Resident::where('resident_id', $this->resident_id)->first();
        
        // ایجاد گزارش جدید
        $newReport = ResidentReport::create([
            'resident_id' => $this->resident_id,
            'report_id' => $this->report_id,
            'unit_id' => $resident->unit_id ?? null,
            'room_id' => $resident->room_id ?? null,
            'bed_id' => $resident->bed_id ?? null,
            'notes' => $this->notes,
            'description' => $this->description,
            'has_been_sent' => false,
            'is_checked' => false,
        ]);
        
        \Log::info('ResidentReport created successfully', [
            'id' => $newReport->id,
            'description' => $newReport->description,
            'notes' => $newReport->notes
        ]);

        // ریست فرم
        $this->reset(['report_id', 'notes', 'description']);

        // ارسال رویداد برای به‌روزرسانی لیست
        $this->dispatch('reportCreated');

        // نمایش پیام موفقیت
        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => 'گزارش با موفقیت ثبت شد.'
        ]);
    }

    public function render()
    {
        return view('livewire.residents.create-resident-report');
    }
}
