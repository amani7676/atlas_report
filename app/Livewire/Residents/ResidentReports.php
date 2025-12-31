<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Category;
use App\Models\Constant;
use App\Models\Resident;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str; // اضافه شده
use Illuminate\Support\Facades\DB;

class ResidentReports extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filters = [
        'unit_id' => null,
        'room_id' => null,
        'report_id' => null,
        'category_id' => null,
        'date_from' => null,
        'date_to' => null
    ];
    public $units = [];
    public $rooms = [];
    public $categories = [];
    public $reportsList = [];
    public $showFilters = false;
    public $selectedReports = [];
    public $bulkAction = '';
    public $selectAll = false;

    // پراپرتی‌های جدید برای جستجوی اقامت‌گران
    public $residentSearch = '';
    public $selectedResident = null;
    public $residentReports = [];
    public $showResidentDetails = false;
    public $filterByResidentName = null; // برای فیلتر کردن بر اساس نام اقامت‌گر

    // پراپرتی‌های مربوط به بخشودگی
    public $showGrantForm = false;
    public $grantAmount = '';
    public $grantDescription = '';
    public $grantDate = '';
    public $selectedResidentGrants = [];

    // Propertyهای computed
    public function getTotalScoreProperty()
    {
        $query = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->leftJoin('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->where('reports.category_id', 1) // دسته‌بندی تخلف
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('residents.resident_full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('residents.resident_phone', 'like', '%' . $this->search . '%')
                        ->orWhere('residents.unit_name', 'like', '%' . $this->search . '%')
                        ->orWhere('residents.room_name', 'like', '%' . $this->search . '%')
                        ->orWhere('resident_reports.notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['unit_id'], function ($query) {
                $query->where('resident_reports.unit_id', $this->filters['unit_id']);
            })
            ->when($this->filters['room_id'], function ($query) {
                $query->where('resident_reports.room_id', $this->filters['room_id']);
            })
            ->when($this->filters['report_id'], function ($query) {
                $query->where('resident_reports.report_id', $this->filters['report_id']);
            })
            ->when($this->filters['category_id'], function ($query) {
                $query->where('reports.category_id', $this->filters['category_id']);
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('resident_reports.created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('resident_reports.created_at', '<=', $this->filters['date_to']);
            });

        return $query->sum('reports.negative_score') ?? 0;
    }

    public function getTotalReportsCountProperty()
    {
        return $this->reportsQuery->count();
    }

    public function getDistinctResidentsCountProperty()
    {
        $query = ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 1); // دسته‌بندی تخلف
        })
        ->whereNotNull('resident_id')
        ->when($this->filters['unit_id'], function ($query) {
            $query->where('unit_id', $this->filters['unit_id']);
        })
        ->when($this->filters['room_id'], function ($query) {
            $query->where('room_id', $this->filters['room_id']);
        })
        ->when($this->filters['report_id'], function ($query) {
            $query->where('report_id', $this->filters['report_id']);
        })
        ->when($this->filters['category_id'], function ($query) {
            $query->whereHas('report', function ($q) {
                $q->where('category_id', $this->filters['category_id']);
            });
        })
        ->when($this->filters['date_from'], function ($query) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        })
        ->when($this->filters['date_to'], function ($query) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        });

        return $query->distinct('resident_id')->count('resident_id');
    }

    public function getReportsByUnitProperty()
    {
        $query = ResidentReport::selectRaw('residents.unit_name, COUNT(*) as count, SUM(reports.negative_score) as total_score')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->leftJoin('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->where('reports.category_id', 1) // دسته‌بندی تخلف
            ->whereNotNull('residents.unit_name')
            ->when($this->filters['report_id'], function ($query) {
                $query->where('resident_reports.report_id', $this->filters['report_id']);
            })
            ->when($this->filters['category_id'], function ($query) {
                $query->where('reports.category_id', $this->filters['category_id']);
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('resident_reports.created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('resident_reports.created_at', '<=', $this->filters['date_to']);
            })
            ->groupBy('residents.unit_name')
            ->orderByDesc('count');

        return $query->get();
    }

    public function getTopResidentsProperty()
    {
        // دریافت مقدار ثابت max_violation از جدول constants
        $maxViolation = Constant::where('key', 'max_violation')->first();
        $maxViolationValue = $maxViolation ? (int)$maxViolation->value : 0;

        $query = ResidentReport::selectRaw('
            MAX(residents.resident_full_name) as resident_name,
            MAX(residents.unit_name) as unit_name,
            MAX(residents.room_name) as room_name,
            MAX(residents.resident_phone) as phone,
            COUNT(*) as report_count,
            SUM(reports.negative_score) as total_score,
            resident_reports.resident_id
        ')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->leftJoin('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->where('reports.category_id', 1) // دسته‌بندی تخلف
            ->where('resident_reports.is_checked', false) // فقط تخلف‌های چک نشده
            ->whereNotNull('resident_reports.resident_id')
            ->when($this->filters['report_id'], function ($query) {
                $query->where('resident_reports.report_id', $this->filters['report_id']);
            })
            ->when($this->filters['category_id'], function ($query) {
                $query->where('reports.category_id', $this->filters['category_id']);
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('resident_reports.created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('resident_reports.created_at', '<=', $this->filters['date_to']);
            })
            ->groupBy('resident_reports.resident_id')
            ->havingRaw('SUM(reports.negative_score) >= ?', [$maxViolationValue]) // فیلتر بر اساس مجموع نمرات منفی
            ->orderByDesc('total_score') // مرتب‌سازی بر اساس مجموع نمرات منفی
            ->limit(10);

        return $query->get();
    }

    /**
     * تعداد اقامت‌گرانی که تخلف‌های تکرارای یکسان دارند
     */
    public function getRepeatViolationResidentsCountProperty()
    {
        return $this->repeatViolationResidents->count();
    }

    /**
     * لیست اقامت‌گرانی که تخلف‌های تکرارای یکسان دارند
     */
    public function getRepeatViolationResidentsProperty()
    {
        // دریافت مقدار ثابت repeat_violation از جدول constants
        $repeatViolation = Constant::where('key', 'repeat_violation')->first();
        $repeatViolationValue = $repeatViolation ? (int)$repeatViolation->value : 0;

        if ($repeatViolationValue <= 0) {
            return collect([]);
        }

        // پیدا کردن اقامت‌گرانی که یک نوع گزارش را چند بار داشته‌اند
        $residents = ResidentReport::selectRaw('
            MAX(residents.resident_full_name) as resident_name,
            resident_reports.report_id,
            reports.title as report_name,
            MAX(residents.unit_name) as unit_name,
            MAX(residents.room_name) as room_name,
            MAX(residents.resident_phone) as phone,
            COUNT(*) as repeat_count,
            SUM(reports.negative_score) as total_score,
            resident_reports.resident_id
        ')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->leftJoin('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->where('reports.category_id', 1) // دسته‌بندی تخلف
            ->where('resident_reports.is_checked', false) // فقط تخلف‌های چک نشده
            ->whereNotNull('resident_reports.resident_id')
            ->when($this->filters['report_id'], function ($query) {
                $query->where('resident_reports.report_id', $this->filters['report_id']);
            })
            ->when($this->filters['category_id'], function ($query) {
                $query->where('reports.category_id', $this->filters['category_id']);
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('resident_reports.created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('resident_reports.created_at', '<=', $this->filters['date_to']);
            })
            ->groupBy('resident_reports.resident_id', 'resident_reports.report_id', 'reports.title')
            ->havingRaw('COUNT(*) >= ?', [$repeatViolationValue])
            ->orderByDesc('repeat_count')
            ->get();

        return $residents;
    }

    /**
     * تعداد اقامت‌گرانی که تعداد گزارش‌هایشان از count_violation بیشتر یا مساوی است
     */
    public function getCountViolationResidentsCountProperty()
    {
        return $this->countViolationResidents->count();
    }

    /**
     * لیست اقامت‌گرانی که تعداد گزارش‌هایشان از count_violation بیشتر یا مساوی است
     */
    public function getCountViolationResidentsProperty()
    {
        // دریافت مقدار ثابت count_violation از جدول constants
        $countViolation = Constant::where('key', 'count_violation')->first();
        $countViolationValue = $countViolation ? (int)$countViolation->value : 0;

        if ($countViolationValue <= 0) {
            return collect([]);
        }

        $query = ResidentReport::selectRaw('
            MAX(residents.resident_full_name) as resident_name,
            MAX(residents.unit_name) as unit_name,
            MAX(residents.room_name) as room_name,
            MAX(residents.resident_phone) as phone,
            COUNT(*) as report_count,
            SUM(reports.negative_score) as total_score,
            resident_reports.resident_id
        ')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->leftJoin('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->where('reports.category_id', 1) // دسته‌بندی تخلف
            ->where('resident_reports.is_checked', false) // فقط تخلف‌های چک نشده
            ->whereNotNull('resident_reports.resident_id')
            ->when($this->filters['report_id'], function ($query) {
                $query->where('resident_reports.report_id', $this->filters['report_id']);
            })
            ->when($this->filters['category_id'], function ($query) {
                $query->where('reports.category_id', $this->filters['category_id']);
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('resident_reports.created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('resident_reports.created_at', '<=', $this->filters['date_to']);
            })
            ->groupBy('resident_reports.resident_id')
            ->havingRaw('COUNT(*) >= ?', [$countViolationValue])
            ->orderByDesc('report_count');

        return $query->get();
    }

    public function getReportsQueryProperty(): Builder
    {
        $query = ResidentReport::with(['report', 'report.category', 'resident'])
            ->whereHas('report', function ($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('resident', function ($residentQuery) {
                        $residentQuery->where('resident_full_name', 'like', '%' . $this->search . '%')
                            ->orWhere('resident_phone', 'like', '%' . $this->search . '%')
                            ->orWhere('unit_name', 'like', '%' . $this->search . '%')
                            ->orWhere('room_name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterByResidentName, function ($query) {
                $query->whereHas('resident', function ($q) {
                    $q->where('resident_full_name', $this->filterByResidentName);
                });
            })
            ->when($this->filters['unit_id'], function ($query) {
                $query->where('unit_id', $this->filters['unit_id']);
            })
            ->when($this->filters['room_id'], function ($query) {
                $query->where('room_id', $this->filters['room_id']);
            })
            ->when($this->filters['report_id'], function ($query) {
                $query->where('report_id', $this->filters['report_id']);
            })
            ->when($this->filters['category_id'], function ($query) {
                $query->whereHas('report', function ($q) {
                    $q->where('category_id', $this->filters['category_id']);
                });
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('created_at', '<=', $this->filters['date_to']);
            });

        return $query->orderBy($this->sortField, $this->sortDirection);
    }


    public function mount()
    {
        $this->loadFilterData();
    }

    public function loadFilterData()
    {
        // فقط دسته‌بندی تخلف (ID = 1)
        $this->categories = Category::where('id', 1)->get();

        $this->reportsList = Report::where('category_id', 1)->get(); // دسته‌بندی تخلف

        $this->units = ResidentReport::select('residents.unit_id', 'residents.unit_name')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->whereNotNull('residents.unit_id')
            ->distinct()
            ->orderBy('residents.unit_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->unit_id,
                    'name' => $item->unit_name
                ];
            })->toArray();

        $this->rooms = ResidentReport::select('residents.room_id', 'residents.room_name', 'residents.unit_id')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->whereNotNull('residents.room_id')
            ->distinct()
            ->orderBy('residents.room_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->room_id,
                    'name' => $item->room_name,
                    'unit_id' => $item->unit_id
                ];
            })->toArray();
    }

    public function getFilteredRooms()
    {
        if (!$this->filters['unit_id']) {
            return $this->rooms;
        }

        return array_filter($this->rooms, function ($room) {
            return $room['unit_id'] == $this->filters['unit_id'];
        });
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->filters = [
            'unit_id' => null,
            'room_id' => null,
            'report_id' => null,
            'category_id' => null,
            'date_from' => null,
            'date_to' => null
        ];
        $this->search = '';
        $this->filterByResidentName = null;
        $this->gotoPage(1);
    }

    /**
     * فیلتر کردن بر اساس نام اقامت‌گر و اسکرول به پایین
     */
    public function filterByResident($residentName, $reportId = null)
    {
        $this->filterByResidentName = $residentName;
        if ($reportId) {
            $this->filters['report_id'] = $reportId;
        } else {
            // اگر report_id پاس داده نشده، فیلتر report_id را پاک می‌کنیم
            $this->filters['report_id'] = null;
        }
        $this->resetPage();

        // اسکرول به پایین صفحه (لیست گزارش‌ها)
        $this->dispatch('scrollToReports');
    }

    /**
     * پاک کردن فیلتر اقامت‌گر
     */
    public function clearResidentFilter()
    {
        $this->filterByResidentName = null;
        $this->resetPage();
    }

    // متدهای جدید برای جستجوی اقامت‌گران
    public function searchResidents()
    {
        if (empty($this->residentSearch) || strlen($this->residentSearch) < 2) {
            return [];
        }

        return ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 1); // دسته‌بندی تخلف
        })
        ->whereHas('resident', function($q) {
            $q->where('resident_full_name', 'like', '%' . $this->residentSearch . '%');
        })
        ->with('resident')
        ->get()
        ->pluck('resident.resident_full_name')
        ->unique()
        ->sort()
        ->values()
        ->toArray();
    }

    public function selectResident($residentName)
    {
        $this->selectedResident = $residentName;
        $this->showResidentDetails = true;
        $this->residentSearch = $residentName; // برای نمایش نام در اینپوت

        // پیدا کردن resident از جدول residents
        $resident = Resident::where('resident_full_name', $residentName)->first();

        if ($resident) {
            // استفاده از resident_id از جدول residents
            $this->residentReports = ResidentReport::whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->where('resident_id', $resident->id)
            ->with(['report', 'report.category', 'resident'])
            ->orderBy('created_at', 'desc')
            ->get();
        } else {
            $this->residentReports = collect([]);
        }

        // بارگذاری بخشودگی‌های این اقامت‌گر
        $this->loadResidentGrants($residentName);
    }

    /**
     * بارگذاری بخشودگی‌های اقامت‌گر
     */
    private function loadResidentGrants($residentName)
    {
        $resident = Resident::where('resident_full_name', $residentName)->first();
        if ($resident) {
            $this->selectedResidentGrants = \App\Models\ResidentGrant::where('resident_id', $resident->resident_id)
                ->orderBy('grant_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->selectedResidentGrants = [];
        }
    }

    public function closeResidentDetails()
    {
        $this->selectedResident = null;
        $this->residentReports = [];
        $this->showResidentDetails = false;
        $this->residentSearch = '';
        $this->selectedResidentGrants = [];
    }

    /**
     * باز کردن فرم ثبت بخشودگی
     */
    public function openGrantForm()
    {
        $this->grantAmount = '';
        $this->grantDescription = '';
        $this->grantDate = date('Y-m-d');
        $this->showGrantForm = true;
    }

    /**
     * بستن فرم بخشودگی
     */
    public function closeGrantForm()
    {
        $this->showGrantForm = false;
        $this->resetGrantForm();
    }

    /**
     * ریست کردن فرم بخشودگی
     */
    private function resetGrantForm()
    {
        $this->grantAmount = '';
        $this->grantDescription = '';
        $this->grantDate = date('Y-m-d');
    }

    /**
     * ثبت یا به‌روزرسانی بخشودگی
     */
    public function saveGrant()
    {
        $this->validate([
            'grantAmount' => 'required|numeric|min:0',
            'grantDate' => 'nullable|date',
        ], [
            'grantAmount.required' => 'مقدار بخشودگی الزامی است',
            'grantAmount.numeric' => 'مقدار بخشودگی باید عددی باشد',
            'grantAmount.min' => 'مقدار بخشودگی باید بیشتر از صفر باشد',
            'grantDate.date' => 'تاریخ بخشودگی معتبر نیست',
        ]);

        try {
            $resident = Resident::where('resident_full_name', $this->selectedResident)->first();

            if (!$resident) {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'اقامت‌گر یافت نشد'
                ]);
                return;
            }

            // ثبت بخشودگی جدید - حتماً با is_active = 1
            $grant = new \App\Models\ResidentGrant();
            $grant->resident_id = $resident->resident_id;
            $grant->amount = $this->grantAmount;
            $grant->description = $this->grantDescription;
            $grant->grant_date = $this->grantDate ?: now()->toDateString();
            // $grant->is_active = true; // حتماً 1 (true) باشد
            $grant->save();

            $message = 'بخشودگی با موفقیت ثبت شد';

            // بررسی و اعمال منطق غیرفعال کردن بخشودگی و false کردن is_checked
            $this->checkAndDeactivateGrants($resident->resident_id);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفق!',
                'text' => $message
            ]);

            // بارگذاری مجدد بخشودگی‌ها
            $this->loadResidentGrants($this->selectedResident);

            // بستن فرم
            $this->closeGrantForm();
        } catch (\Exception $e) {
            \Log::error('Error saving grant', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ثبت بخشودگی: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * حذف بخشودگی
     */
    public function deleteGrant($grantId)
    {
        try {
            $grant = \App\Models\ResidentGrant::findOrFail($grantId);
            $grant->delete();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفق!',
                'text' => 'بخشودگی با موفقیت حذف شد'
            ]);

            // بارگذاری مجدد بخشودگی‌ها
            $this->loadResidentGrants($this->selectedResident);
        } catch (\Exception $e) {
            \Log::error('Error deleting grant', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در حذف بخشودگی: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteReport($id)
    {
        $report = ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 1); // دسته‌بندی تخلف
        })->findOrFail($id);

        $report->delete();

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش تخلف حذف شد.'
        ]);
    }

    public function deleteMultipleReports()
    {
        if (empty($this->selectedReports)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک گزارش را انتخاب کنید.'
            ]);
            return;
        }

        // فقط گزارش‌های تخلفی را حذف می‌کنیم (دسته‌بندی ID = 1)
        ResidentReport::whereIn('id', $this->selectedReports)
            ->whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->delete();

        $this->selectedReports = [];

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش‌های تخلف انتخاب شده حذف شدند.'
        ]);
    }

    public function executeBulkAction()
    {
        if ($this->bulkAction === 'delete' && !empty($this->selectedReports)) {
            $this->dispatch('confirmBulkDelete', [
                'type' => 'resident_reports',
                'count' => count($this->selectedReports)
            ]);
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedReports = $this->reportsQuery->pluck('id')->toArray();
        } else {
            $this->selectedReports = [];
        }
    }

    /**
     * تغییر وضعیت checked بودن گزارش
     */
    public function toggleChecked($reportId)
    {
        try {
            $report = ResidentReport::find($reportId);
            if ($report) {
                $report->is_checked = !$report->is_checked;
                $report->save();

                // بارگذاری مجدد گزارش‌های این اقامت‌گر
                if ($this->selectedResident) {
                    $this->selectResident($this->selectedResident);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error toggling checked status', [
                'report_id' => $reportId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * چک کردن همه گزارش‌های اقامت‌گر انتخابی
     */
    public function checkAllReports()
    {
        if (!$this->selectedResident) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'لطفاً ابتدا یک اقامت‌گر انتخاب کنید'
            ]);
            return;
        }

        try {
            // پیدا کردن resident از جدول residents
            $resident = Resident::where('resident_full_name', $this->selectedResident)->first();
            if (!$resident) {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'اقامت‌گر یافت نشد'
                ]);
                return;
            }

            // محاسبه مجموع نمرات منفی گزارش‌های چک نشده
            $uncheckedReports = ResidentReport::whereHas('report', function($q) {
                $q->where('category_id', 1);
            })
            ->where('resident_id', $resident->id)
            ->where('is_checked', false)
            ->with('report')
            ->get();

            $uncheckedTotalScore = $uncheckedReports->sum(function ($report) {
                return $report->report->negative_score ?? 0;
            });

            // محاسبه مجموع بخشودگی‌های فعال
            $totalGrants = \App\Models\ResidentGrant::where('resident_id', $resident->resident_id)
                ->where('is_active', true)
                ->sum('amount');

            // اگر مجموع نمرات منفی (چک نشده) >= بخشودگی و بخشودگی وجود دارد، همه را FALSE کن
            if ($totalGrants > 0 && $uncheckedTotalScore >= $totalGrants) {
                // غیرفعال کردن تمام گزارش‌های چک شده
                $updatedCount = ResidentReport::whereHas('report', function($q) {
                    $q->where('category_id', 1);
                })
                ->where('resident_id', $resident->id)
                ->where('is_checked', true)
                ->update(['is_checked' => false]);

                $this->dispatch('showAlert', [
                    'type' => 'warning',
                    'title' => 'هشدار!',
                    'text' => 'به دلیل اینکه مجموع نمرات منفی (چک نشده) بیشتر یا مساوی بخشودگی است، تمام گزارش‌های چک شده، غیرفعال شدند.'
                ]);
            } else {
                // چک کردن همه گزارش‌های چک نشده (TRUE کردن)
                $updatedCount = ResidentReport::whereHas('report', function($q) {
                    $q->where('category_id', 1);
                })
                ->where('resident_id', $resident->id)
                ->where('is_checked', false)
                ->update(['is_checked' => true]);

                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفق!',
                    'text' => "تمام {$updatedCount} گزارش این اقامت‌گر چک شدند"
                ]);
            }

            // بارگذاری مجدد گزارش‌ها برای به‌روزرسانی UI
            $this->selectResident($this->selectedResident);

        } catch (\Exception $e) {
            \Log::error('Error checking all reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در چک کردن گزارش‌ها: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * لغو چک کردن همه گزارش‌های اقامت‌گر انتخابی
     */
    public function uncheckAllReports()
    {
        if (!$this->selectedResident) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'لطفاً ابتدا یک اقامت‌گر انتخاب کنید'
            ]);
            return;
        }

        try {
            $resident = Resident::where('resident_full_name', $this->selectedResident)->first();
            if (!$resident) {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'اقامت‌گر یافت نشد'
                ]);
                return;
            }

            // FALSE کردن همه گزارش‌های اقامت‌گر (استفاده از update برای سرعت بیشتر)
            $updatedCount = ResidentReport::whereHas('report', function($q) {
                $q->where('category_id', 1);
            })
            ->where('resident_id', $resident->id)
            ->where('is_checked', true)
            ->update(['is_checked' => false]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفق!',
                'text' => "تمام {$updatedCount} گزارش این اقامت‌گر لغو چک شدند"
            ]);

            // بارگذاری مجدد گزارش‌ها برای به‌روزرسانی UI
            $this->selectResident($this->selectedResident);
        } catch (\Exception $e) {
            \Log::error('Error unchecking all reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در لغو چک کردن گزارش‌ها: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * محاسبه مجموع نمرات منفی با احتساب بخشودگی
     */
    public function getResidentTotalNegativeScore()
    {
        if (!$this->selectedResident || empty($this->residentReports)) {
            return 0;
        }

        $totalScore = $this->residentReports->sum(function ($report) {
            return $report->report->negative_score ?? 0;
        });

        // محاسبه مجموع بخشودگی‌ها
        $resident = Resident::where(function($q) {
            $q->where('resident_full_name', $this->selectedResident);
        })->first();
        if ($resident) {
            $totalGrants = \App\Models\ResidentGrant::where('resident_id', $resident->resident_id)
                ->where('is_active', true)
                ->sum('amount');

            $totalScore = max(0, $totalScore - $totalGrants);
        }

        return number_format($totalScore, 0);
    }

    /**
     * بررسی و غیرفعال کردن بخشودگی‌ها و false کردن is_checked
     * این متد باید بعد از ثبت بخشودگی جدید یا ثبت تخلف جدید فراخوانی شود
     */
    private function checkAndDeactivateGrants($residentId)
    {
        ResidentReport::checkAndDeactivateGrantsForResident($residentId);
    }

    public function render()
    {
        $reports = $this->reportsQuery->paginate($this->perPage);

        // اطمینان از load شدن relation resident
        $reports->load('resident');

        // همگام‌سازی اطلاعات با API برای رکوردهای نمایش داده شده
        foreach ($reports as $report) {
            if ($report->resident_id) {
                $apiService = new \App\Services\ResidentApiService();
                $apiService->syncResidentData($report);
            }
        }

        // reload relation بعد از sync
        $reports->load('resident');

        $filteredRooms = $this->getFilteredRooms();
        $residentsList = $this->searchResidents();

        return view('livewire.residents.resident-reports', [
            'reports' => $reports,
            'filteredRooms' => $filteredRooms,
            'totalScore' => $this->totalScore,
            'totalReportsCount' => $this->totalReportsCount,
            'distinctResidentsCount' => $this->distinctResidentsCount,
            'reportsByUnit' => $this->reportsByUnit,
            'topResidents' => $this->topResidents,
            'reportsList' => $this->reportsList,
            'residentsList' => $residentsList,
            'repeatViolationResidentsCount' => $this->repeatViolationResidentsCount,
            'countViolationResidentsCount' => $this->countViolationResidentsCount,
            'repeatViolationResidents' => $this->repeatViolationResidents,
            'countViolationResidents' => $this->countViolationResidents,
        ]);
    }
}
