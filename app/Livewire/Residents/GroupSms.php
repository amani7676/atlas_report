<?php

namespace App\Livewire\Residents;

use App\Models\Resident;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

class GroupSms extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // فیلترها
    public $filters = [
        'unit_name' => null,
        'room_name' => null,
        'bed_name' => null,
        'resident_full_name' => null,
        'resident_phone' => null,
        'resident_document' => null, // null, true, false
        'resident_form' => null, // null, true, false
        'contract_state' => null, // active, inactive, etc
        'payment_overdue_days' => null, // تعداد روزهای گذشته از سررسید
        'resident_status' => null, // active, exit (deleted)
        'notes_type' => null, // payment, end_date, exit, demand, other
        'has_debt' => null, // null, true, false
    ];

    public $selectedResidents = [];
    public $selectAll = false;
    public $perPage = 20;
    public $showFilters = true;

    // لیست واحدها، اتاق‌ها و تخت‌ها برای dropdown
    public $unitsList = [];
    public $roomsList = [];
    public $bedsList = [];

    public function mount()
    {
        $this->loadFilterOptions();
    }

    public function loadFilterOptions()
    {
        // بارگذاری لیست واحدها
        $this->unitsList = Resident::whereNotNull('unit_name')
            ->distinct()
            ->orderBy('unit_name', 'asc')
            ->pluck('unit_name')
            ->toArray();

        // بارگذاری لیست اتاق‌ها
        $this->roomsList = Resident::whereNotNull('room_name')
            ->distinct()
            ->orderBy('room_name', 'asc')
            ->pluck('room_name')
            ->toArray();

        // بارگذاری لیست تخت‌ها
        $this->bedsList = Resident::whereNotNull('bed_name')
            ->distinct()
            ->orderBy('bed_name', 'asc')
            ->pluck('bed_name')
            ->toArray();
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->selectedResidents = [];
        $this->selectAll = false;
    }

    public function resetFilters()
    {
        $this->filters = [
            'unit_name' => null,
            'room_name' => null,
            'bed_name' => null,
            'resident_full_name' => null,
            'resident_phone' => null,
            'resident_document' => null,
            'resident_form' => null,
            'contract_state' => null,
            'payment_overdue_days' => null,
            'resident_status' => null,
            'notes_type' => null,
            'has_debt' => null,
        ];
        $this->resetPage();
        $this->selectedResidents = [];
        $this->selectAll = false;
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedResidents = [];
            $this->selectAll = false;
        } else {
            $residents = $this->getFilteredResidentsQuery()->pluck('id')->toArray();
            $this->selectedResidents = $residents;
            $this->selectAll = true;
        }
    }

    public function toggleSelectResident($residentId)
    {
        if (in_array($residentId, $this->selectedResidents)) {
            $this->selectedResidents = array_diff($this->selectedResidents, [$residentId]);
        } else {
            $this->selectedResidents[] = $residentId;
        }
        $this->selectAll = false;
    }

    private function getFilteredResidentsQuery()
    {
        $query = Resident::query();

        // فیلتر واحد
        if (!empty($this->filters['unit_name'])) {
            $query->where('unit_name', $this->filters['unit_name']);
        }

        // فیلتر اتاق
        if (!empty($this->filters['room_name'])) {
            $query->where('room_name', $this->filters['room_name']);
        }

        // فیلتر تخت
        if (!empty($this->filters['bed_name'])) {
            $query->where('bed_name', $this->filters['bed_name']);
        }

        // فیلتر نام
        if (!empty($this->filters['resident_full_name'])) {
            $query->where('resident_full_name', 'like', '%' . $this->filters['resident_full_name'] . '%');
        }

        // فیلتر تلفن
        if (!empty($this->filters['resident_phone'])) {
            $query->where('resident_phone', 'like', '%' . $this->filters['resident_phone'] . '%');
        }

        // فیلتر مدرک
        if ($this->filters['resident_document'] !== null && $this->filters['resident_document'] !== '') {
            $query->where('resident_document', $this->filters['resident_document'] == '1' || $this->filters['resident_document'] === true);
        }

        // فیلتر فرم
        if ($this->filters['resident_form'] !== null && $this->filters['resident_form'] !== '') {
            $query->where('resident_form', $this->filters['resident_form'] == '1' || $this->filters['resident_form'] === true);
        }

        // فیلتر وضعیت قرارداد
        if (!empty($this->filters['contract_state'])) {
            $query->where('contract_state', $this->filters['contract_state']);
        }

        // فیلتر گذشته از سررسید
        // اگر کاربر 7 روز وارد کند، باید اقامت‌گرانی را نشان دهد که تاریخ پرداختشان 7 روز قبل از امروز یا قبل‌تر است
        if (!empty($this->filters['payment_overdue_days']) && is_numeric($this->filters['payment_overdue_days'])) {
            $days = (int)$this->filters['payment_overdue_days'];
            $today = now();
            $targetDate = $today->copy()->subDays($days);
            $targetDateJalali = Jalalian::fromCarbon($targetDate);
            $targetDateStr = $targetDateJalali->format('Y/m/d');
            
            // اقامت‌گرانی که تاریخ پرداختشان قبل یا برابر targetDateStr است (یعنی days روز از سررسیدشان گذشته)
            $query->whereNotNull('contract_payment_date_jalali')
                ->where('contract_payment_date_jalali', '!=', '')
                ->where('contract_payment_date_jalali', '<=', $targetDateStr);
        }

        // فیلتر وضعیت کاربر (active/exit)
        if (!empty($this->filters['resident_status'])) {
            if ($this->filters['resident_status'] === 'active') {
                $query->whereNull('resident_deleted_at');
            } elseif ($this->filters['resident_status'] === 'exit') {
                $query->whereNotNull('resident_deleted_at');
            }
        }

        // فیلتر نوت‌ها بر اساس type
        // notes یک JSON array است که هر آیتم آن یک object با field type دارد
        if (!empty($this->filters['notes_type'])) {
            $notesType = $this->filters['notes_type'];
            // استفاده از JSON_SEARCH برای جستجو در array
            $query->whereRaw('JSON_SEARCH(notes, "one", ?, NULL, "$[*].type") IS NOT NULL', [$notesType]);
        }

        // فیلتر بدهی‌ها - نیاز به بررسی بیشتر داریم، فعلاً placeholder
        // TODO: پیاده‌سازی فیلتر بدهی بر اساس منطق کسب‌وکار

        return $query;
    }

    public function sendSms()
    {
        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار',
                'text' => 'لطفا حداقل یک اقامت‌گر را انتخاب کنید.'
            ]);
            return;
        }

        // ذخیره ID های انتخاب شده در session برای استفاده در صفحه ارسال پیامک
        session(['group_sms_selected_residents' => $this->selectedResidents]);
        
        // هدایت به صفحه ارسال پیامک
        return redirect()->route('sms.index');
    }

    public function render()
    {
        $residents = $this->getFilteredResidentsQuery()
            ->orderBy('resident_full_name', 'asc')
            ->paginate($this->perPage);

        // محاسبه تعداد انتخاب شده
        $selectedCount = count($this->selectedResidents);

        return view('livewire.residents.group-sms', [
            'residents' => $residents,
            'selectedCount' => $selectedCount,
        ]);
    }
}

