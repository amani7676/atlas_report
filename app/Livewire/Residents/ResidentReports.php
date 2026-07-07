<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ResidentReport;
use App\Models\Resident;
use App\Models\Report;
use App\Models\Unit;
use App\Models\ResidentGrant;
use App\Models\Category;
use App\Models\Constant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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
    public $selectedResidentData = null; // داده‌های کامل اقامت‌گر انتخاب شده
    public $residentReports = [];
    public $showResidentDetails = false;
    public $showResidentModal = false; // برای نمایش مدال اقامت‌گر
    public $filterByResidentName = null; // برای فیلتر کردن بر اساس نام اقامت‌گر

    // پراپرتی‌های مربوط به بخشودگی
    public $showGrantForm = false;
    public $grantAmount = '';
    public $grantDescription = '';
    public $grantDate = '';
    public $selectedResidentGrants = [];
    public $grantCheckError = null; // پیام خطا برای چک نشدن همه گزارش‌ها
    
    // پراپرتی‌های سیستم کارت‌ها
    public $pendingCards = [];
    public $approvedCards = [];
    public $pendingCardsCount = 0;
    public $approvedCardsCount = 0;
    
    // پراپرتی‌های کارت‌های اقامت‌گران
    public $residentCards = [];
    public $residentCardsCount = 0;
    
    // پراپرتی‌های کارت‌های بررسی نشده (اقامت‌گرانی که به آستانه رسیده‌اند)
    public $pendingThresholdCards = [];
    public $pendingThresholdCardsCount = 0;

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
            resident_reports.resident_id,
            (SELECT COUNT(*) FROM resident_grants WHERE resident_grants.resident_id = residents.resident_id AND resident_grants.is_active = 1) as grants_count,
            (SELECT COALESCE(SUM(amount), 0) FROM resident_grants WHERE resident_grants.resident_id = residents.resident_id AND resident_grants.is_active = 1) as grants_total,
            (SELECT COUNT(*) FROM resident_grants WHERE resident_grants.resident_id = residents.resident_id) as grants_total_count
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
     * تعداد اقامت‌گران با بیشترین تخلف
     */
    public function getTopViolationResidentsCountProperty()
    {
        return $this->topResidents->count();
    }

    /**
     * تعداد گزارش‌های انتخاب شده
     */
    public function getSelectedReportsCountProperty()
    {
        return count($this->selectedReports);
    }

    /**
     * اقامت‌گر فعلی برای فیلتر
     */
    public function getCurrentResidentProperty()
    {
        return $this->filterByResidentName;
    }

    /**
     * لیست اقامت‌گران با بیشترین تخلف (alias برای topResidents)
     */
    public function getTopViolationResidentsProperty()
    {
        return $this->topResidents;
    }





    public function getReportsQueryProperty(): Builder
    {
        try {
            $query = ResidentReport::with(['report', 'report.category', 'resident'])
                ->whereHas('report', function ($q) {
                    $q->where('category_id', 1); // دسته‌بندی تخلف
                })
                ->when($this->search && strlen($this->search) > 0, function ($query) {
                    $query->where(function ($q) {
                        $q->whereHas('report', function ($reportQuery) {
                            $reportQuery->where('title', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('notes', 'like', '%' . $this->search . '%')
                        ->orWhereHas('resident', function ($residentQuery) {
                            $residentQuery->where('resident_full_name', 'like', '%' . $this->search . '%');
                        });
                    });
                })
                ->when($this->filterByResidentName, function ($query) {
                    $residentId = \App\Models\Resident::where('resident_full_name', $this->filterByResidentName)->value('resident_id');
                    if ($residentId) {
                        $query->where('resident_id', $residentId);
                    }
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

            // مرتب‌سازی
            if ($this->sortField && in_array($this->sortField, ['created_at', 'updated_at'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            return $query;
        } catch (\Exception $e) {
            // در صورت خطا، کوئری پایه را برمی‌گردانیم
            return ResidentReport::with(['report', 'report.category', 'resident'])
                ->whereHas('report', function ($q) {
                    $q->where('category_id', 1);
                })
                ->orderBy('created_at', 'desc');
        }
    }

    public function mount()
    {
        $this->loadFilterData();
        // لود کردن کارت‌های اقامت‌گران
        $this->residentCards = $this->residentCards;
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

        // دریافت resident_id های مربوط به جستجو از جدول residents
        $residentIds = Resident::where('resident_full_name', 'like', '%' . $this->residentSearch . '%')
            ->pluck('resident_id')
            ->toArray();

        if (empty($residentIds)) {
            return [];
        }

        return ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 1); // دسته‌بندی تخلف
        })
        ->whereIn('resident_id', $residentIds)
        ->get()
        ->map(function ($report) {
            $resident = $report->getResidentData();
            return $resident ? $resident->resident_full_name : null;
        })
        ->filter()
        ->unique()
        ->sort()
        ->values()
        ->toArray();
    }

    public function selectResident($residentName)
    {
        // پاک کردن جستجو
        $this->search = '';
        
        // تنظیم فیلتر اقامت‌گر
        $this->filterByResidentName = $residentName;
        
        // پیدا کردن اقامت‌گر
        $resident = Resident::where('resident_full_name', $residentName)->first();
        
        if ($resident) {
            // ذخیره داده‌های اقامت‌گر
            $this->selectedResident = $residentName;
            $this->selectedResidentData = $resident;
            
            // دریافت گزارش‌های تخلفی اقامت‌گر
            $this->residentReports = ResidentReport::whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->where('resident_id', $resident->resident_id)
            ->with(['report', 'report.category'])
            ->orderBy('created_at', 'desc')
            ->get();
            
            // بارگذاری بخشودگی‌ها
            $this->loadResidentGrants($residentName);
            
            // باز کردن مدال
            $this->showResidentModal = true;
        } else {
            // اقامت‌گر یافت نشد
            $this->selectedResident = null;
            $this->selectedResidentData = null;
            $this->residentReports = collect([]);
            $this->selectedResidentGrants = [];
        }
    }
    
    /**
     * بستن مدال اقامت‌گر
     */
    public function closeResidentModal()
    {
        $this->showResidentModal = false;
        $this->selectedResident = null;
        $this->selectedResidentData = null;
        $this->residentReports = [];
        $this->selectedResidentGrants = [];
        $this->showGrantForm = false;
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
        $this->closeResidentModal();
    }

    /**
     * باز کردن فرم ثبت بخشودگی
     */
    public function openGrantForm()
    {
        if (!$this->selectedResident) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'لطفاً ابتدا یک اقامت‌گر انتخاب کنید'
            ]);
            return;
        }

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

        // بارگذاری مجدد گزارش‌های اقامت‌گر برای بررسی دقیق
        $reports = ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 1); // دسته‌بندی تخلف
        })
        ->where('resident_id', $resident->resident_id)
        ->with(['report', 'report.category'])
        ->orderBy('created_at', 'desc')
        ->get();

        // بررسی اینکه آیا گزارش‌ای وجود دارد یا نه
        if ($reports->isEmpty()) {
            // اگر گزارش‌ای وجود ندارد، اجازه ثبت بخشودگی را بده
            $this->grantAmount = '';
            $this->grantDescription = '';
            $this->grantDate = date('Y-m-d');
            $this->showGrantForm = true;
            return;
        }

        // بررسی اینکه آیا همه گزارش‌ها چک شده‌اند یا نه
        $totalReports = $reports->count();
        $uncheckedReports = $reports->filter(function($report) {
            return !$report->is_checked || $report->is_checked === false || $report->is_checked === 0;
        });

        if ($uncheckedReports->count() > 0) {
            // نمایش پیام خطا زیر دکمه
            $this->grantCheckError = 'لطفا همه رو چک کنید';
            return;
        }
        
        // اگر همه چک شده‌اند، پیام خطا را پاک کن
        $this->grantCheckError = null;

        // اگر همه گزارش‌ها چک شده‌اند، فرم را باز کن
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
        $this->grantCheckError = null;
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
            $grant->is_active = true; // حتماً 1 (true) باشد
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
            // \Log::error('Error saving grant', [
//     'error' => $e->getMessage(),
//     'trace' => $e->getTraceAsString(),
// ]);

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
            // \Log::error('Error deleting grant', [
//     'error' => $e->getMessage(),
//     'trace' => $e->getTraceAsString(),
// ]);

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

    public function updatedSearch()
    {
        try {
            // جستجو امن با حفظ کارایی
            $this->resetPage();
        } catch (\Exception $e) {
            // در صورت خطا، صفحه را ریست می‌کنیم
            $this->resetPage();
        }
    }

    public function updatedPerPage()
    {
        try {
            // تغییر تعداد آیتم‌ها با حفظ کارایی
            $this->resetPage();
        } catch (\Exception $e) {
            // در صورت خطا، صفحه را ریست می‌کنیم
            $this->resetPage();
        }
    }

    public function updatedFilters()
    {
        try {
            // تغییر فیلترها با حفظ کارایی
            $this->resetPage();
        } catch (\Exception $e) {
            // در صورت خطا، صفحه را ریست می‌کنیم
            $this->resetPage();
        }
    }

    public function updatedSelectAll($value)
    {
        try {
            if ($value) {
                // استفاده از pluck برای بهینه‌وری و جلوگیری از خطا
                $this->selectedReports = $this->reportsQuery->pluck('id')->toArray();
            } else {
                $this->selectedReports = [];
            }
        } catch (\Exception $e) {
            // در صورت خطا، آرایه را خالی کن
            $this->selectedReports = [];
        }
    }

    /**
     * تغییر وضعیت is_checked گزارش
     */
    public function toggleReportStatus($reportId)
    {
        try {
            $report = ResidentReport::find($reportId);
            
            if (!$report) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'title' => 'خطا',
                    'message' => 'گزارش مورد نظر یافت نشد.',
                    'duration' => 3000,
                ]);
                return;
            }
            
            // تغییر وضعیت
            $report->is_checked = !$report->is_checked;
            $report->save();
            
            $status = $report->is_checked ? 'فعال' : 'غیرفعال';
            $action = $report->is_checked ? 'فعال' : 'غیرفعال';
            
            // به‌روزرسانی داده‌های کارت‌ها برای محاسبه مجدد امتیازات
            $this->resetCachedData();
            
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'message' => "وضعیت گزارش {$action} شد.",
                'duration' => 3000,
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا',
                'message' => 'خطا در تغییر وضعیت گزارش: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    /**
     * ریست کردن داده‌های کش شده برای به‌روزرسانی کارت‌ها
     */
    public function resetCachedData()
    {
        // پاک کردن داده‌های کش شده کارت‌ها با استفاده از unset
        unset($this->pendingCards);
        unset($this->approvedCards);
        unset($this->pendingYellowCards);
        unset($this->pendingRedCards);
        unset($this->approvedYellowCards);
        unset($this->approvedRedCards);
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

                // به‌روزرسانی مستقیم در collection برای نمایش فوری
                if ($this->residentReports) {
                    $updatedReport = $this->residentReports->firstWhere('id', $reportId);
                    if ($updatedReport) {
                        $updatedReport->is_checked = $report->is_checked;
                    }
                }

                // به‌روزرسانی داده‌های کارت‌ها برای محاسبه مجدد امتیازات
                $this->resetCachedData();
                
                // بارگذاری مجدد گزارش‌های این اقامت‌گر برای اطمینان از sync
                if ($this->selectedResident) {
                    $resident = Resident::where('resident_full_name', $this->selectedResident)->first();
                    if ($resident) {
                        $this->residentReports = ResidentReport::whereHas('report', function($q) {
                            $q->where('category_id', 1);
                        })
                        ->where('resident_id', $resident->resident_id)
                        ->with(['report', 'report.category'])
                        ->orderBy('created_at', 'desc')
                        ->get();
                    }
                }
            }
        } catch (\Exception $e) {
            // \Log::error('Error toggling checked status', [
//     'report_id' => $reportId,
//     'error' => $e->getMessage()
// ]);
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
            ->where('resident_id', $resident->resident_id)
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
                ->where('resident_id', $resident->resident_id)
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
                ->where('resident_id', $resident->resident_id)
                ->where('is_checked', false)
                ->update(['is_checked' => true]);

                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفق!',
                    'text' => "تمام {$updatedCount} گزارش این اقامت‌گر چک شدند"
                ]);
            }

            // بارگذاری مجدد گزارش‌ها برای به‌روزرسانی UI
            // ابتدا resident را پیدا می‌کنیم
            $resident = Resident::where('resident_full_name', $this->selectedResident)->first();
            
            if ($resident) {
                // استفاده از resident_id از جدول residents
                $this->residentReports = ResidentReport::whereHas('report', function($q) {
                    $q->where('category_id', 1); // دسته‌بندی تخلف
                })
                ->where('resident_id', $resident->resident_id)
                ->with(['report', 'report.category'])
                ->orderBy('created_at', 'desc')
                ->get();
                
                // به‌روزرسانی داده‌های کارت‌ها برای محاسبه مجدد امتیازات
                $this->resetCachedData();
                
                // بارگذاری مجدد بخشودگی‌ها
                $this->loadResidentGrants($this->selectedResident);
            }

        } catch (\Exception $e) {
            // \Log::error('Error checking all reports', [
//     'error' => $e->getMessage(),
//     'trace' => $e->getTraceAsString(),
// ]);

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

            // بررسی وجود بخشودگی‌های فعال
            $activeGrants = \App\Models\ResidentGrant::where('resident_id', $resident->resident_id)
                ->where('is_active', true)
                ->get();

            if ($activeGrants->count() > 0) {
                $totalGrants = $activeGrants->sum('amount');
                $this->dispatch('showAlert', [
                    'type' => 'warning',
                    'title' => 'هشدار!',
                    'text' => "برای لغو چک کردن همه گزارش‌ها، ابتدا باید بخشودگی‌های فعال ({$activeGrants->count()} مورد، مجموع: {$totalGrants}) را حذف کنید."
                ]);
                return;
            }

            // FALSE کردن همه گزارش‌های اقامت‌گر (استفاده از update برای سرعت بیشتر)
            $updatedCount = ResidentReport::whereHas('report', function($q) {
                $q->where('category_id', 1);
            })
            ->where('resident_id', $resident->resident_id)
            ->where('is_checked', true)
            ->update(['is_checked' => false]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفق!',
                'text' => "تمام {$updatedCount} گزارش این اقامت‌گر لغو چک شدند"
            ]);

            // بارگذاری مجدد گزارش‌ها برای به‌روزرسانی UI
            // استفاده از resident_id از جدول residents
            $this->residentReports = ResidentReport::whereHas('report', function($q) {
                $q->where('category_id', 1); // دسته‌بندی تخلف
            })
            ->where('resident_id', $resident->resident_id)
            ->with(['report', 'report.category'])
            ->orderBy('created_at', 'desc')
            ->get();
            
            // به‌روزرسانی داده‌های کارت‌ها برای محاسبه مجدد امتیازات
            $this->resetCachedData();
            
            // بارگذاری مجدد بخشودگی‌ها
            $this->loadResidentGrants($this->selectedResident);
        } catch (\Exception $e) {
            // \Log::error('Error unchecking all reports', [
//     'error' => $e->getMessage(),
//     'trace' => $e->getTraceAsString(),
// ]);

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
     * تعداد گزارش‌های چک شده اقامت‌گر انتخابی
     */
    public function getCheckedReportsCountProperty()
    {
        if (empty($this->residentReports)) {
            return 0;
        }

        return $this->residentReports->where('is_checked', true)->count();
    }

    /**
     * تعداد کل گزارش‌های اقامت‌گر انتخابی
     */
    public function getResidentReportsCountProperty()
    {
        return $this->residentReports ? $this->residentReports->count() : 0;
    }

    /**
     * دریافت آستانه کارت زرد
     */
    public function getYellowCardThresholdProperty()
    {
        $threshold = \App\Models\Constant::where('key', 'yellow_card_threshold')->first();
        return $threshold ? (int)$threshold->value : 20;
    }

    /**
     * دریافت آستانه کارت قرمز
     */
    public function getRedCardThresholdProperty()
    {
        $threshold = \App\Models\Constant::where('key', 'red_card_threshold')->first();
        return $threshold ? (int)$threshold->value : 30;
    }

    /**
     * دریافت آستانه تعداد تخلف کارت زرد
     */
    public function getYellowViolationCountThresholdProperty()
    {
        $threshold = \App\Models\Constant::where('key', 'yellow_violation_count_threshold')->first();
        return $threshold ? (int)$threshold->value : 3;
    }

    /**
     * دریافت آستانه تعداد تخلف کارت قرمز
     */
    public function getRedViolationCountThresholdProperty()
    {
        $threshold = \App\Models\Constant::where('key', 'red_violation_count_threshold')->first();
        return $threshold ? (int)$threshold->value : 5;
    }

    /**
     * دریافت کارت‌های بررسی نشده
     */
    public function getPendingCardsProperty()
    {
        $yellowThreshold = $this->yellowCardThreshold;
        $redThreshold = $this->redCardThreshold;
        $yellowViolationCountThreshold = $this->yellowViolationCountThreshold;
        $redViolationCountThreshold = $this->redViolationCountThreshold;
        
        // دریافت گزارش‌های مستثنی شده از تنظیمات
        $excludedReportsConstant = \App\Models\Constant::where('key', 'excluded_reports')->first();
        $excludedReportIds = [];
        if ($excludedReportsConstant && $excludedReportsConstant->value) {
            $excludedReportIds = json_decode($excludedReportsConstant->value, true) ?? [];
        }
        // اضافه کردن گزارش اخطار سررسید (ID=2) به مستثنی‌ها
        $excludedReportIds[] = 2;
        
        // دریافت اقامت‌گرانی که کارت تأیید شده ندارند
        $approvedResidentIds = \App\Models\ResidentCard::where('card_status', 'approved')
            ->pluck('resident_id')
            ->toArray();
        
        // دریافت اقامت‌گران با مجموع امتیازات تخلفات (به جز گزارش‌های مستثنی)
        $residentsWithScores = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.resident_id')
            ->where('reports.category_id', 1) // فقط دسته‌بندی تخلف
            ->where('reports.negative_score', '>', 0) // فقط گزارش‌های با امتیاز منفی
            ->where('reports.id', '!=', 2) // حذف اخطار سررسید
            ->where('resident_reports.is_checked', false) // فقط گزارش‌های فعال (غیر چک شده)
            ->whereNotIn('residents.resident_id', $approvedResidentIds) // حذف اقامت‌گران با کارت تأیید شده
            ->selectRaw('
                residents.resident_id,
                residents.resident_full_name as resident_name,
                SUM(reports.negative_score) as total_score,
                COUNT(resident_reports.id) as violation_count,
                MIN(resident_reports.created_at) as first_violation
            ')
            ->groupBy('residents.resident_id', 'residents.resident_full_name')
            ->orderBy('total_score', 'desc')
            ->get();

        $pendingCards = [];
        
        foreach ($residentsWithScores as $resident) {
            // محاسبه بیشترین تعداد تخلف یکسان
            $maxRepeatedViolation = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $resident->resident_id)
                ->where('reports.category_id', 1)
                ->where('reports.negative_score', '>', 0)
                ->where('reports.id', '!=', 2)
                ->selectRaw('reports.id, COUNT(*) as count')
                ->groupBy('reports.id')
                ->orderByDesc('count')
                ->first();
            
            $maxRepeatedCount = $maxRepeatedViolation ? $maxRepeatedViolation->count : 0;
            
            // تعیین نوع کارت بر اساس امتیاز یا تعداد تخلف یکسان
            if ($resident->total_score >= $redThreshold || $maxRepeatedCount >= $redViolationCountThreshold) {
                // کارت قرمز
                $cardType = 'red';
            } elseif ($resident->total_score >= $yellowThreshold || $maxRepeatedCount >= $yellowViolationCountThreshold) {
                // کارت زرد
                $cardType = 'yellow';
            } else {
                // بدون کارت
                continue;
            }
            
            $pendingCards[] = (object)[
                'id' => 'pending_' . $resident->resident_id,
                'resident_id' => $resident->resident_id,
                'resident_name' => $resident->resident_name,
                'total_score' => $resident->total_score, // نمایش امتیاز مجموع واقعی
                'eligible_score' => $resident->eligible_score, // امتیاز واجد شرایط برای گزارش‌های تکراری
                'card_type' => $cardType,
                'first_violation' => $resident->first_violation,
                'approved_at' => null
            ];
        }
        
        \Log::info('Total cards created: ' . count($pendingCards));
        
        return collect($pendingCards);
    }


    /**
     * دریافت کارت‌های بررسی شده
     */
    public function getApprovedCardsProperty()
    {
        // دریافت کارت‌های تأیید شده از جدول resident_cards
        $approvedCards = \App\Models\ResidentCard::where('card_status', 'approved')
            ->orderBy('card_assigned_at', 'desc')
            ->get();
        
        // تبدیل به فرمت استاندارد کارت‌ها
        return $approvedCards->map(function($residentCard) {
            // پیدا کردن اقامت‌گر بر اساس resident_id
            $resident = \App\Models\Resident::where('resident_id', $residentCard->resident_id)->first();
            
            // محاسبه امتیاز فعلی (به جز اخطار سررسید)
            $currentScore = \App\Models\ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $residentCard->resident_id)
                ->where('reports.category_id', 1) // دسته‌بندی تخلف
                ->where('reports.id', '!=', 2) // حذف اخطار سررسید
                ->where('resident_reports.is_checked', false) // فقط گزارش‌های فعال
                ->sum('reports.negative_score');
            
            return (object)[
                'id' => $residentCard->id,
                'resident_id' => $residentCard->resident_id,
                'resident_name' => $resident ? $resident->resident_full_name : 'نامشخص',
                'card_type' => $residentCard->current_card_type,
                'current_score' => $currentScore,
                'total_score' => $currentScore,
                'card_status' => $residentCard->card_status,
                'card_assigned_at' => $residentCard->card_assigned_at,
                'approved_at' => $residentCard->card_assigned_at,
            ];
        });
    }

    /**
     * تعداد کارت‌های بررسی نشده
     */
    public function getPendingCardsCountProperty()
    {
        return $this->pendingCards->count();
    }

    /**
     * تعداد کارت‌های بررسی شده
     */
    public function getApprovedCardsCountProperty()
    {
        return $this->approvedCards->count();
    }

    /**
     * دریافت کارت‌های زرد بررسی نشده
     */
    public function getPendingYellowCardsProperty()
    {
        // مستقیماً متد getPendingCardsProperty را فراخوانی می‌کنیم تا از caching مشکل جلوگیری کنیم
        $pendingCards = $this->getPendingCardsProperty();
        
        $yellowCards = $pendingCards->filter(function($card) {
            return $card->card_type === 'yellow';
        });
        
        // محاسبه امتیاز فعلی برای هر کارت (به جز اخطار سررسید)
        $yellowCards = $yellowCards->map(function($card) {
            $currentScore = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $card->resident_id)
                ->where('reports.category_id', 1) // دسته‌بندی تخلف
                ->where('reports.id', '!=', 2) // حذف اخطار سررسید
                ->where('resident_reports.is_checked', false) // فقط گزارش‌های فعال
                ->sum('reports.negative_score');
            
            $card->current_score = $currentScore;
            return $card;
        });
        
        return $yellowCards;
    }

    /**
     * دریافت کارت‌های قرمز بررسی نشده
     */
    public function getPendingRedCardsProperty()
    {
        // مستقیماً متد getPendingCardsProperty را فراخوانی می‌کنیم تا از caching مشکل جلوگیری کنیم
        $pendingCards = $this->getPendingCardsProperty();
        
        $redCards = $pendingCards->filter(function($card) {
            return $card->card_type === 'red';
        });
        
        // محاسبه امتیاز فعلی برای هر کارت (به جز اخطار سررسید)
        $redCards = $redCards->map(function($card) {
            $currentScore = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $card->resident_id)
                ->where('reports.category_id', 1) // دسته‌بندی تخلف
                ->where('reports.id', '!=', 2) // حذف اخطار سررسید
                ->where('resident_reports.is_checked', false) // فقط گزارش‌های فعال
                ->sum('reports.negative_score');
            
            $card->current_score = $currentScore;
            return $card;
        });
        
        return $redCards;
    }

    /**
     * تعداد کارت‌های زرد بررسی نشده
     */
    public function getPendingYellowCardsCountProperty()
    {
        return $this->pendingYellowCards->count();
    }

    /**
     * تعداد کارت‌های قرمز بررسی نشده
     */
    public function getPendingRedCardsCountProperty()
    {
        return $this->pendingRedCards->count();
    }

    /**
     * دریافت کارت‌های اقامت‌گران
     */
    public function getResidentCardsProperty()
    {
        $yellowThreshold = $this->yellowCardThreshold;
        $redThreshold = $this->redCardThreshold;
        $yellowViolationCountThreshold = $this->yellowViolationCountThreshold;
        $redViolationCountThreshold = $this->redViolationCountThreshold;
        
        // دریافت تمام کارت‌های موجود از دیتابیس
        $residentCards = \App\Models\ResidentCard::with('resident')
            ->orderBy('card_assigned_at', 'desc')
            ->get();
        
        // فیلتر کردن اقامت‌گرانی بر اساس مجموع تخلفات هر اقامت‌گر
        $filteredCards = $residentCards->filter(function ($card) use ($yellowThreshold, $redThreshold, $yellowViolationCountThreshold, $redViolationCountThreshold) {
            // محاسبه مجموع تخلفات این اقامت‌گر خاص (به جز اخطار سررسید)
            $residentTotalScore = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $card->resident_id)
                ->where('reports.category_id', 1) // فقط تخلفات
                ->where('reports.id', '!=', 2) // حذف اخطار سررسید
                ->where('resident_reports.is_checked', false) // فقط گزارش‌های فعال
                ->sum('reports.negative_score');
            
            // محاسبه بیشترین تعداد تخلف یکسان
            $maxRepeatedViolation = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $card->resident_id)
                ->where('reports.category_id', 1)
                ->where('reports.negative_score', '>', 0)
                ->where('reports.id', '!=', 2)
                ->selectRaw('reports.id, COUNT(*) as count')
                ->groupBy('reports.id')
                ->orderByDesc('count')
                ->first();
            
            $maxRepeatedCount = $maxRepeatedViolation ? $maxRepeatedViolation->count : 0;
            
            // بررسی اینکه آیا این اقامت‌گر به آستانه کارت زرد رسیده است یا نه
            // اگر مجموع تخلفات کمتر از آستانه زرد و تکرار کمتر از آستانه زرد باشد، کارت را نمایش بده
            if ($residentTotalScore < $yellowThreshold && $maxRepeatedCount < $yellowViolationCountThreshold) {
                return true;
            }
            
            // اگر به آستانه رسیده، این کارت را در بخش اصلی نمایش نده
            return false;
        });
        
        return $filteredCards;
    }

    /**
     * تعداد کارت‌های اقامت‌گران
     */
    public function getResidentCardsCountProperty()
    {
        return $this->residentCards->count();
    }

    /**
     * دریافت کارت‌های بررسی نشده (اقامت‌گرانی که به آستانه رسیده‌اند)
     */
    public function getPendingThresholdCardsProperty()
    {
        $yellowThreshold = $this->yellowCardThreshold;
        $redThreshold = $this->redCardThreshold;
        $yellowViolationCountThreshold = $this->yellowViolationCountThreshold;
        $redViolationCountThreshold = $this->redViolationCountThreshold;
        
        // دریافت تمام کارت‌های موجود از دیتابیس
        $allCards = \App\Models\ResidentCard::with('resident')
            ->orderBy('card_assigned_at', 'desc')
            ->get();
        
        // فیلتر کردن اقامت‌گرانی بر اساس مجموع تخلفات هر اقامت‌گر
        $thresholdCards = $allCards->filter(function ($card) use ($yellowThreshold, $redThreshold, $yellowViolationCountThreshold, $redViolationCountThreshold) {
            // محاسبه مجموع تخلفات این اقامت‌گر خاص (به جز اخطار سررسید)
            $residentTotalScore = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $card->resident_id)
                ->where('reports.category_id', 1) // فقط تخلفات
                ->where('reports.id', '!=', 2) // حذف اخطار سررسید
                ->where('resident_reports.is_checked', false) // فقط گزارش‌های فعال
                ->sum('reports.negative_score');
            
            // محاسبه بیشترین تعداد تخلف یکسان
            $maxRepeatedViolation = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $card->resident_id)
                ->where('reports.category_id', 1)
                ->where('reports.negative_score', '>', 0)
                ->where('reports.id', '!=', 2)
                ->selectRaw('reports.id, COUNT(*) as count')
                ->groupBy('reports.id')
                ->orderByDesc('count')
                ->first();
            
            $maxRepeatedCount = $maxRepeatedViolation ? $maxRepeatedViolation->count : 0;
            
            // بررسی اینکه آیا این اقامت‌گر به آستانه کارت زرد رسیده است یا نه
            // اگر مجموع تخلفات مساوی یا بیشتر از آستانه زرد باشد یا تکرار به آستانه رسیده باشد، در این بخش نمایش بده
            if ($residentTotalScore >= $yellowThreshold || $maxRepeatedCount >= $yellowViolationCountThreshold) {
                // اضافه کردن مجموع تخلفات فعلی به کارت برای نمایش
                $card->current_score = $residentTotalScore;
                $card->max_repeated_count = $maxRepeatedCount;
                
                // تعیین نوع کارت بر اساس امتیاز فعلی یا تعداد تکرار
                if ($residentTotalScore >= $redThreshold || $maxRepeatedCount >= $redViolationCountThreshold) {
                    $card->suggested_card_type = 'red';
                } else {
                    $card->suggested_card_type = 'yellow';
                }
                
                return true;
            }
            
            return false;
        });
        
        return $thresholdCards;
    }

    /**
     * تعداد کارت‌های بررسی نشده
     */
    public function getPendingThresholdCardsCountProperty()
    {
        return $this->pendingThresholdCards->count();
    }

    /**
     * تأیید کارت اقامت‌گر
     */
    public function approveResidentCard($residentId)
    {
        // پیدا کردن کارت اقامت‌گر در دیتابیس
        $residentCard = \App\Models\ResidentCard::where('resident_id', $residentId)->first();
        
        if (!$residentCard) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا!',
                'message' => 'کارت اقامت‌گر یافت نشد.',
                'duration' => 3000,
            ]);
            return;
        }
        
        // به‌روزرسانی وضعیت کارت به تأیید شده
        $residentCard->card_status = 'approved';
        $residentCard->save();
        
        // دریافت نام اقامت‌گر از جدول residents
        $resident = \App\Models\Resident::where('resident_id', $residentId)->first();
        $residentName = $resident ? $resident->resident_full_name : 'نامشخص';
        
        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => "کارت {$residentCard->card_type_label} برای {$residentName} با موفقیت تأیید شد.",
            'duration' => 3000,
        ]);
    }

    /**
     * اختصاص کارت به اقامت‌گران بر اساس مجموع تخلفات
     */
    public function assignCardsToResidents()
    {
        $yellowThreshold = $this->yellowCardThreshold;
        $redThreshold = $this->redCardThreshold;
        
        // دریافت تمام اقامت‌گران با مجموع تخلفات
        $residentsWithScores = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.resident_id')
            ->where('reports.category_id', 1) // دسته‌بندی تخلف
            ->where('reports.negative_score', '>', 0)
            ->selectRaw('
                residents.resident_id,
                residents.resident_full_name as resident_name,
                SUM(reports.negative_score) as total_score,
                COUNT(resident_reports.id) as violation_count,
                MIN(resident_reports.created_at) as first_violation
            ')
            ->groupBy('residents.resident_id', 'residents.resident_full_name')
            ->orderBy('total_score', 'desc')
            ->get();

        foreach ($residentsWithScores as $resident) {
            // تعیین نوع کارت بر اساس مجموع تخلفات
            $currentCardType = null;
            $cardStatus = 'pending';
            $cardAssignedAt = now();
            
            if ($resident->total_score >= $redThreshold) {
                $currentCardType = 'red';
            } elseif ($resident->total_score >= $yellowThreshold) {
                $currentCardType = 'yellow';
            } else {
                continue; // مجموع تخلفات کمتر از آستانه زرد
            }
            
            // بررسی اینکه آیا کارتی از قبل برای این اقامت‌گر وجود دارد
            $existingCard = \App\Models\ResidentCard::where('resident_id', $resident->resident_id)->first();
            
            if ($existingCard) {
                // به‌روزرسانی کارت موجود (بدون ذخیره total_score و resident_name)
                $existingCard->update([
                    'current_card_type' => $currentCardType,
                    'card_status' => $cardStatus,
                    'card_assigned_at' => $cardAssignedAt,
                ]);
            } else {
                // ایجاد کارت جدید (بدون ذخیره total_score و resident_name)
                \App\Models\ResidentCard::create([
                    'resident_id' => $resident->resident_id,
                    'current_card_type' => $currentCardType,
                    'card_status' => $cardStatus,
                    'card_assigned_at' => $cardAssignedAt,
                ]);
            }
        }
    }

    /**
     * متد تست برای نمایش داده‌های کارت‌ها
     */
    public function testCardData()
    {
        $yellowCards = $this->pendingYellowCards;
        $redCards = $this->pendingRedCards;
        
        $data = [
            'yellow_cards_count' => $yellowCards->count(),
            'red_cards_count' => $redCards->count(),
            'yellow_cards' => $yellowCards->toArray(),
            'red_cards' => $redCards->toArray(),
            'all_pending_cards' => $this->pendingCards->toArray()
        ];
        
        // نمایش در لاگ برای دیباگ
        // \Log::info('Card Test Data:', $data);
        
        $this->dispatch('showAlert', [
            'type' => 'info',
            'title' => 'اطلاعات کارت‌ها',
            'text' => "کارت زرد: {$yellowCards->count()}، کارت قرمز: {$redCards->count()}"
        ]);
    }

    /**
     * متد دیباگ برای بررسی داده‌ها
     */
    public function debugCardData()
    {
        $yellowThreshold = $this->yellowCardThreshold;
        $redThreshold = $this->redCardThreshold;
        
        // دریافت تمام گزارش‌ها با امتیاز منفی
        $allReports = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.resident_id')
            ->where('reports.category_id', 1) // فقط دسته‌بندی تخلف
            ->where('reports.negative_score', '>', 0)
            ->selectRaw('
                residents.resident_full_name as resident_name,
                reports.negative_score,
                reports.title as report_title,
                resident_reports.created_at
            ')
            ->orderBy('residents.resident_full_name')
            ->orderBy('resident_reports.created_at', 'desc')
            ->limit(20)
            ->get();
            
        // محاسبه مجموع امتیاز هر اقامت‌گر
        $residentScores = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.resident_id')
            ->where('reports.category_id', 1) // فقط دسته‌بندی تخلف
            ->where('reports.negative_score', '>', 0)
            ->selectRaw('
                residents.resident_full_name as resident_name,
                residents.resident_id,
                SUM(reports.negative_score) as total_score,
                COUNT(resident_reports.id) as violation_count
            ')
            ->groupBy('residents.resident_id', 'residents.resident_full_name')
            ->orderBy('total_score', 'desc')
            ->get();
            
        // تفکیک کارت‌های زرد و قرمز
        $yellowCards = $residentScores->filter(function($r) use ($yellowThreshold, $redThreshold) {
            return $r->total_score >= $yellowThreshold && $r->total_score < $redThreshold;
        });
        
        $redCards = $residentScores->filter(function($r) use ($redThreshold) {
            return $r->total_score >= $redThreshold;
        });
            
        // بررسی اینکه آیا اصلاً داده‌ای وجود دارد یا نه
        $hasData = $allReports->count() > 0;
        $hasHighScores = $residentScores->filter(function($r) use ($yellowThreshold) { return $r->total_score >= $yellowThreshold; })->count() > 0;
        
        // نمایش نتایج برای عیب‌یابی
        $output = [
            'thresholds' => [
                'yellow' => $yellowThreshold,
                'red' => $redThreshold
            ],
            'has_any_data' => $hasData,
            'total_reports_count' => $allReports->count(),
            'total_residents_with_scores' => $residentScores->count(),
            'residents_above_yellow' => $residentScores->filter(function($r) use ($yellowThreshold) { return $r->total_score >= $yellowThreshold; })->count(),
            'residents_above_red' => $residentScores->filter(function($r) use ($redThreshold) { return $r->total_score >= $redThreshold; })->count(),
            'yellow_cards_count' => $yellowCards->count(),
            'red_cards_count' => $redCards->count(),
            'sample_reports' => $allReports->take(5),
            'yellow_cards' => $yellowCards->take(10),
            'red_cards' => $redCards->take(10),
            'all_resident_scores' => $residentScores->take(10),
        ];
        
        // اگر داده‌ای وجود ندارد، پیام مناسب نمایش بده
        if (!$hasData) {
            $output['message'] = 'هیچ گزارش تخلفی با امتیاز منفی در سیستم وجود ندارد!';
        } elseif (!$hasHighScores) {
            $output['message'] = "گزارش‌ها وجود دارند اما هیچ اقامت‌گری امتیاز کل {$yellowThreshold} یا بیشتر ندارد!";
        }
        
        dd($output);
    }

    /**
     * دریافت کارت‌های زرد بررسی شده
     */
    public function getApprovedYellowCardsProperty()
    {
        // مستقیماً متد getApprovedCardsProperty را فراخوانی می‌کنیم تا از caching مشکل جلوگیری کنیم
        $approvedCards = $this->getApprovedCardsProperty();
        
        $yellowCards = $approvedCards->filter(function($card) {
            return $card->card_type === 'yellow';
        });
        
        return $yellowCards;
    }

    /**
     * دریافت کارت‌های قرمز بررسی شده
     */
    public function getApprovedRedCardsProperty()
    {
        // مستقیماً متد getApprovedCardsProperty را فراخوانی می‌کنیم تا از caching مشکل جلوگیری کنیم
        $approvedCards = $this->getApprovedCardsProperty();
        
        $redCards = $approvedCards->filter(function($card) {
            return $card->card_type === 'red';
        });
        
        return $redCards;
    }

    /**
     * تعداد کارت‌های زرد بررسی شده
     */
    public function getApprovedYellowCardsCountProperty()
    {
        return $this->approvedYellowCards->count();
    }

    /**
     * تعداد کارت‌های قرمز بررسی شده
     */
    public function getApprovedRedCardsCountProperty()
    {
        return $this->approvedRedCards->count();
    }

    /**
     * حذف کارت تأیید شده
     */
    public function deleteCard($cardId)
    {
        try {
            // پیدا کردن کارت در دیتابیس
            $residentCard = \App\Models\ResidentCard::find($cardId);
            
            if (!$residentCard) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'title' => 'خطا',
                    'message' => 'کارت مورد نظر یافت نشد.',
                    'duration' => 3000,
                ]);
                return;
            }
            
            // پیدا کردن نام اقامت‌گر برای نمایش در پیام
            $resident = \App\Models\Resident::where('resident_id', $residentCard->resident_id)->first();
            $residentName = $resident ? $resident->resident_full_name : 'نامشخص';
            $cardType = $residentCard->current_card_type === 'yellow' ? 'زرد' : 'قرمز';
            
            // حذف کارت از دیتابیس
            $residentCard->delete();
            
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'message' => "کارت {$cardType} برای {$residentName} با موفقیت حذف شد.",
                'duration' => 3000,
            ]);
            
            // رفرش کردن کامپوننت برای نمایش تغییرات
            $this->dispatch('refreshComponent');
            
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا',
                'message' => 'خطا در حذف کارت: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    /**
     * رفرش کردن کامپوننت
     */
    public function refreshComponent()
    {
        // این متد برای رفرش کردن کامپوننت استفاده می‌شود
        // Livewire به صورت خودکار کامپوننت را رفرش می‌کند
    }

    /**
     * تأیید کارت
     */
    public function approveCard($residentId)
    {
        try {
            // پیدا کردن کارت از لیست کارت‌های بررسی نشده
            $card = null;
            
            // جستجو در کارت‌های زرد بررسی نشده
            $yellowCards = $this->getPendingYellowCardsProperty();
            $card = $yellowCards->firstWhere('resident_id', $residentId);
            
            // اگر در کارت‌های زرد نبود، در کارت‌های قرمز جستجو کن
            if (!$card) {
                $redCards = $this->getPendingRedCardsProperty();
                $card = $redCards->firstWhere('resident_id', $residentId);
            }
            
            if (!$card) {
                $this->dispatch('showToast', [
                    'type' => 'error',
                    'title' => 'خطا',
                    'message' => 'کارت مورد نظر یافت نشد.',
                    'duration' => 3000,
                ]);
                return;
            }
            
            // ایجاد یا به‌روزرسانی کارت در دیتابیس
            // مستقیماً از resident_id استفاده می‌کنیم
            $residentCard = \App\Models\ResidentCard::updateOrCreate(
                [
                    'resident_id' => $card->resident_id, // مستقیماً resident_id را ذخیره می‌کنیم
                    'current_card_type' => $card->card_type,
                ],
                [
                    'card_status' => 'approved',
                    'card_assigned_at' => now(),
                ]
            );
            
            // محاسبه امتیاز فعلی برای هر کارت
            $currentScore = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $card->resident_id)
                ->where('reports.category_id', 1) // دسته‌بندی تخلف
                ->where('resident_reports.is_checked', false) // فقط گزارش‌های فعال
                ->sum('reports.negative_score');
            
            // اگر جدول resident_card_scores وجود دارد، امتیاز را هم ذخیره کن
            if (Schema::hasTable('resident_card_scores')) {
                \DB::table('resident_card_scores')->updateOrCreate(
                    [
                        'resident_id' => $card->resident_id,
                        'resident_card_id' => $residentCard->id,
                    ],
                    [
                        'total_score' => $currentScore,
                        'card_type' => $card->card_type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            
            $cardTypeLabel = $card->card_type === 'yellow' ? 'زرد' : 'قرمز';
            
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'message' => "کارت {$cardTypeLabel} برای {$card->resident_name} با موفقیت تأیید شد.",
                'duration' => 3000,
            ]);
            
            // رفرش کردن کامپوننت برای نمایش تغییرات
            $this->dispatch('refreshComponent');
            
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا',
                'message' => 'خطا در تأیید کارت: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    /**
     * دانلود کارت‌های زرد
     */
    public function downloadYellowCards()
    {
        $yellowCards = $this->pendingYellowCards->merge($this->approvedYellowCards);
        
        // ایجاد CSV برای دانلود
        $csvContent = "نام اقامت‌گر,نوع کارت,امتیاز,وضعیت,تاریخ\n";
        
        foreach ($yellowCards as $card) {
            $status = $card->approved_at ? 'تأیید شده' : 'بررسی نشده';
            $date = $card->approved_at ? $card->approved_at : $card->first_violation;
            $csvContent .= "\"{$card->resident_name}\",\"کارت زرد\",\"{$card->total_score}\",\"{$status}\",\"{$date}\"\n";
        }
        
        return response()->streamDownload(function() use ($csvContent) {
            echo $csvContent;
        }, 'yellow-cards-' . date('Y-m-d') . '.csv');
    }

    /**
     * دانلود کارت‌های قرمز
     */
    public function downloadRedCards()
    {
        $redCards = $this->pendingRedCards->merge($this->approvedRedCards);
        
        // ایجاد CSV برای دانلود
        $csvContent = "نام اقامت‌گر,نوع کارت,امتیاز,وضعیت,تاریخ\n";
        
        foreach ($redCards as $card) {
            $status = $card->approved_at ? 'تأیید شده' : 'بررسی نشده';
            $date = $card->approved_at ? $card->approved_at : $card->first_violation;
            $csvContent .= "\"{$card->resident_name}\",\"کارت قرمز\",\"{$card->total_score}\",\"{$status}\",\"{$date}\"\n";
        }
        
        return response()->streamDownload(function() use ($csvContent) {
            echo $csvContent;
        }, 'red-cards-' . date('Y-m-d') . '.csv');
    }

    /**
     * بررسی و غیرفعال کردن بخشودگی‌ها و false کردن is_checked
     * این متد باید بعد از ثبت بخشودگی جدید یا ثبت تخلف جدید فراخوانی شود
     */
    private function checkAndDeactivateGrants($residentId)
    {
        ResidentReport::checkAndDeactivateGrantsForResident($residentId);
    }

    /**
     * دریافت پیام الگو با مقداردهی کدها برای یک گزارش
     */
    public function getPatternMessageWithVariables($residentReportId)
    {
        $residentReport = ResidentReport::with(['report', 'resident', 'report.patterns'])->find($residentReportId);
        
        if (!$residentReport || !$residentReport->report || !$residentReport->resident) {
            return [
                'success' => false,
                'message' => 'اطلاعات گزارش یافت نشد'
            ];
        }

        $report = $residentReport->report;
        $resident = $residentReport->resident;

        // دریافت اولین الگوی فعال مرتبط با گزارش
        $pattern = $report->activePatterns()
            ->where('patterns.is_active', true)
            ->whereNotNull('patterns.pattern_code')
            ->orderBy('report_pattern.sort_order')
            ->first();

        if (!$pattern || !$pattern->pattern_code) {
            return [
                'success' => false,
                'message' => 'الگویی برای این گزارش تعریف نشده است'
            ];
        }

        // دریافت متغیر الگو
        $patternVariable = \App\Models\PatternVariable::where('pattern_code', $pattern->pattern_code)
            ->where('is_active', true)
            ->first();

        if (!$patternVariable) {
            return [
                'success' => false,
                'message' => 'متغیری برای این الگو تعریف نشده است'
            ];
        }

        // استخراج کدها از متن الگو
        preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
        $variableCodes = $matches[0];
        
        if (empty($variableCodes)) {
            return [
                'success' => true,
                'pattern_title' => $pattern->title,
                'original_message' => $pattern->text,
                'final_message' => $pattern->text,
                'variables' => []
            ];
        }

        // جایگزینی کدها با مقادیر
        $finalMessage = $pattern->text;
        $variables = [];

        foreach ($variableCodes as $code) {
            // جستجو در جدول pivot
            $pivotData = DB::table('pattern_pattern_variables')
                ->where('pattern_id', $pattern->id)
                ->where('variable_code', $code)
                ->first();

            if ($pivotData && $pivotData->table_field) {
                $tableField = $pivotData->table_field;
                $tableName = $patternVariable->table_name;

                // استخراج مقدار
                $value = $this->getVariableValueFromTable($tableName, $tableField, $resident, $report);
                
                $variables[] = [
                    'code' => $code,
                    'field' => $tableField,
                    'table' => $tableName,
                    'value' => $value
                ];

                // جایگزینی در متن
                $finalMessage = str_replace($code, $value, $finalMessage);
            } else {
                $variables[] = [
                    'code' => $code,
                    'field' => 'نامشخص',
                    'table' => 'نامشخص',
                    'value' => ''
                ];
            }
        }

        return [
            'success' => true,
            'pattern_title' => $pattern->title,
            'pattern_code' => $pattern->pattern_code,
            'original_message' => $pattern->text,
            'final_message' => $finalMessage,
            'variables' => $variables
        ];
    }

    /**
     * استخراج مقدار متغیر از جدول مشخص شده
     */
    private function getVariableValueFromTable($tableName, $tableField, $resident, $report)
    {
        if (empty($tableName) || empty($tableField)) {
            return '';
        }

        switch ($tableName) {
            case 'residents':
                return $this->getResidentFieldValue($tableField, $resident);
            case 'reports':
                return $this->getReportFieldValue($tableField, $report);
            case 'units':
                return $this->getUnitFieldValue($tableField, $resident);
            case 'rooms':
                return $this->getRoomFieldValue($tableField, $resident);
            case 'beds':
                return $this->getBedFieldValue($tableField, $resident);
            default:
                return '';
        }
    }

    /**
     * دریافت مقدار از جدول residents
     */
    private function getResidentFieldValue($field, $resident)
    {
        switch ($field) {
            case 'resident_full_name':
            case 'full_name':
            case 'name':
                return $resident->resident_full_name ?? $resident->full_name ?? '';
            case 'resident_phone':
            case 'phone':
                return $resident->resident_phone ?? $resident->phone ?? '';
            case 'room_name':
                return $resident->room_name ?? '';
            case 'bed_name':
                return $resident->bed_name ?? '';
            case 'unit_name':
                return $resident->unit_name ?? '';
            case 'contract_payment_date_jalali':
            case 'payment_date_jalali':
                return $resident->contract_payment_date_jalali ?? '';
            case 'contract_start_date_jalali':
                return $resident->contract_start_date_jalali ?? '';
            case 'contract_end_date_jalali':
                return $resident->contract_end_date_jalali ?? '';
            default:
                return $resident->$field ?? '';
        }
    }

    /**
     * دریافت مقدار از جدول reports
     */
    private function getReportFieldValue($field, $report)
    {
        switch ($field) {
            case 'title':
                return $report->title ?? '';
            case 'description':
                return $report->description ?? '';
            case 'category_name':
                return $report->category->name ?? '';
            case 'negative_score':
                return (string)($report->negative_score ?? '');
            case 'type':
                return $report->type ?? '';
            default:
                return $report->$field ?? '';
        }
    }

    /**
     * دریافت مقدار از جدول units
     */
    private function getUnitFieldValue($field, $resident)
    {
        switch ($field) {
            case 'name':
                return $resident->unit_name ?? '';
            case 'id':
                return (string)($resident->unit_id ?? '');
            default:
                return '';
        }
    }

    /**
     * دریافت مقدار از جدول rooms
     */
    private function getRoomFieldValue($field, $resident)
    {
        switch ($field) {
            case 'name':
                return $resident->room_name ?? '';
            case 'id':
                return (string)($resident->room_id ?? '');
            default:
                return '';
        }
    }

    /**
     * دریافت مقدار از جدول beds
     */
    private function getBedFieldValue($field, $resident)
    {
        switch ($field) {
            case 'name':
                return $resident->bed_name ?? '';
            case 'id':
                return (string)($resident->bed_id ?? '');
            default:
                return '';
        }
    }

    public function render()
    {
        $reports = $this->reportsQuery->paginate($this->perPage);

        // همگام‌سازی اطلاعات با API برای رکوردهای نمایش داده شده
        foreach ($reports as $report) {
            if ($report->resident_id) {
                $apiService = new \App\Services\ResidentApiService();
                $apiService->syncResidentData($report);
            }
        }

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
            'topViolationResidents' => $this->topViolationResidents,
            'topViolationResidentsCount' => $this->topViolationResidentsCount,
            'selectedReportsCount' => $this->selectedReportsCount,
            'currentResident' => $this->currentResident,
            'reportsList' => $this->reportsList,
            'residentsList' => $residentsList,
        ]);
    }
}
