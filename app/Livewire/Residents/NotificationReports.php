<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Category;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NotificationReports extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filters = [
        'report_id' => null,
        'date_from' => null,
        'date_to' => null,
        'resident_name' => null,
        'resident_phone' => null
    ];
    public $units = [];
    public $rooms = [];
    public $categories = [];
    public $reportsList = [];
    public $selectedReports = [];
    public $bulkAction = '';
    public $selectAll = false;

    // Propertyهای computed
    public function getTotalScoreProperty()
    {
        $query = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->leftJoin('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->where('reports.category_id', 2) // دسته‌بندی اطلاع‌رسانی
            ->when($this->filters['report_id'], function ($query) {
                $query->where('resident_reports.report_id', $this->filters['report_id']);
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
            $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
        })
        ->whereNotNull('resident_id')
        ->when($this->filters['report_id'], function ($query) {
            $query->where('report_id', $this->filters['report_id']);
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
        $query = ResidentReport::selectRaw('MAX(residents.unit_name) as unit_name, COUNT(*) as count, SUM(reports.negative_score) as total_score')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->leftJoin('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->where('reports.category_id', 2) // دسته‌بندی اطلاع‌رسانی
            ->whereNotNull('residents.unit_name')
            ->when($this->filters['report_id'], function ($query) {
                $query->where('resident_reports.report_id', $this->filters['report_id']);
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('resident_reports.created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('resident_reports.created_at', '<=', $this->filters['date_to']);
            })
            ->groupBy('residents.unit_id')
            ->orderByDesc('count');
            
        return $query->get();
    }

    public function getReportsQueryProperty(): Builder
    {
        $query = ResidentReport::with(['report', 'report.category', 'resident'])
            ->whereHas('report', function ($q) {
                $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
            })
            ->when($this->filters['report_id'], function ($query) {
                $query->where('report_id', $this->filters['report_id']);
            })
            ->when($this->filters['date_from'], function ($query) {
                $query->whereDate('created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function ($query) {
                $query->whereDate('created_at', '<=', $this->filters['date_to']);
            })
            ->when($this->filters['resident_name'], function ($query) {
                $query->whereHas('resident', function ($q) {
                    $q->where('resident_full_name', 'like', '%' . $this->filters['resident_name'] . '%');
                });
            })
            ->when($this->filters['resident_phone'], function ($query) {
                $query->whereHas('resident', function ($q) {
                    $q->where('resident_phone', 'like', '%' . $this->filters['resident_phone'] . '%');
                });
            });

        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    public function mount()
    {
        $this->loadFilterData();
    }

    public function loadFilterData()
    {
        // فقط دسته‌بندی اطلاع‌رسانی (ID = 2)
        $this->categories = Category::where('id', 2)->get();
        
        $this->reportsList = Report::where('category_id', 2)->get(); // دسته‌بندی اطلاع‌رسانی

        $this->units = ResidentReport::select('residents.unit_id', 'residents.unit_name')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->whereHas('report', function($q) {
                $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
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
            })
            ->toArray();

        $this->rooms = ResidentReport::select('residents.room_id', 'residents.room_name', 'residents.unit_id')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.id')
            ->whereHas('report', function($q) {
                $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
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
            })
            ->toArray();
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
            'report_id' => null,
            'date_from' => null,
            'date_to' => null,
            'resident_name' => null,
            'resident_phone' => null
        ];
        $this->resetPage();
    }

    public function deleteReport($id)
    {
        $report = ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
        })->findOrFail($id);
        
        $report->delete();
        
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش اطلاع‌رسانی حذف شد.'
        ]);
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
        
        return view('livewire.residents.notification-reports', [
            'reports' => $reports,
            'totalScore' => $this->totalScore,
            'totalReportsCount' => $this->totalReportsCount,
            'distinctResidentsCount' => $this->distinctResidentsCount,
            'reportsByUnit' => $this->reportsByUnit,
            'reportsList' => $this->reportsList
        ]);
    }
}
