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

    // Propertyهای computed
    public function getTotalScoreProperty()
    {
        $query = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->where('reports.category_id', 2) // دسته‌بندی اطلاع‌رسانی
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('resident_reports.resident_name', 'like', '%' . $this->search . '%')
                        ->orWhere('resident_reports.unit_name', 'like', '%' . $this->search . '%')
                        ->orWhere('resident_reports.room_name', 'like', '%' . $this->search . '%')
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
            $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
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
        $query = ResidentReport::selectRaw('unit_name, COUNT(*) as count, SUM(reports.negative_score) as total_score')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->where('reports.category_id', 2) // دسته‌بندی اطلاع‌رسانی
            ->whereNotNull('unit_name')
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
            ->groupBy('unit_name')
            ->orderByDesc('count');
            
        return $query->get();
    }

    public function getTopResidentsProperty()
    {
        $query = ResidentReport::selectRaw('
            resident_name,
            MAX(unit_name) as unit_name,
            MAX(room_name) as room_name,
            MAX(phone) as phone,
            COUNT(*) as report_count,
            SUM(reports.negative_score) as total_score
        ')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->where('reports.category_id', 2) // دسته‌بندی اطلاع‌رسانی
            ->whereNotNull('resident_name')
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
            ->groupBy('resident_name')
            ->orderByDesc('report_count')
            ->limit(10);
            
        return $query->get();
    }

    public function getReportsQueryProperty(): Builder
    {
        $query = ResidentReport::with(['report', 'report.category'])
            ->whereHas('report', function ($q) {
                $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('resident_name', 'like', '%' . $this->search . '%')
                        ->orWhere('unit_name', 'like', '%' . $this->search . '%')
                        ->orWhere('room_name', 'like', '%' . $this->search . '%')
                        ->orWhere('notes', 'like', '%' . $this->search . '%');
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
        // فقط دسته‌بندی اطلاع‌رسانی (ID = 2)
        $this->categories = Category::where('id', 2)->get();
        
        $this->reportsList = Report::where('category_id', 2)->get(); // دسته‌بندی اطلاع‌رسانی

        $this->units = ResidentReport::select('unit_id', 'unit_name')
            ->whereHas('report', function($q) {
                $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
            })
            ->whereNotNull('unit_id')
            ->distinct()
            ->orderBy('unit_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->unit_id,
                    'name' => $item->unit_name
                ];
            })
            ->toArray();

        $this->rooms = ResidentReport::select('room_id', 'room_name', 'unit_id')
            ->whereHas('report', function($q) {
                $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
            })
            ->whereNotNull('room_id')
            ->distinct()
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

    public function searchResidents()
    {
        if (empty($this->residentSearch)) {
            return [];
        }

        return ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
        })
        ->where(function($query) {
            $query->where('resident_name', 'like', '%' . $this->residentSearch . '%')
                  ->orWhere('phone', 'like', '%' . $this->residentSearch . '%');
        })
        ->distinct('resident_name')
        ->limit(10)
        ->get()
        ->map(function ($report) {
            return [
                'name' => $report->resident_name,
                'phone' => $report->phone,
                'unit_name' => $report->unit_name,
                'room_name' => $report->room_name
            ];
        })
        ->toArray();
    }

    public function selectResident($residentName)
    {
        $this->selectedResident = $residentName;
        $this->residentReports = ResidentReport::whereHas('report', function($q) {
            $q->where('category_id', 2); // دسته‌بندی اطلاع‌رسانی
        })
        ->where('resident_name', $residentName)
        ->with(['report', 'report.category'])
        ->orderBy('created_at', 'desc')
        ->get();
        $this->showResidentDetails = true;
        $this->residentSearch = '';
    }

    public function closeResidentDetails()
    {
        $this->showResidentDetails = false;
        $this->selectedResident = null;
        $this->residentReports = [];
        $this->residentSearch = '';
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

        return view('livewire.residents.notification-reports', [
            'reports' => $reports,
            'filteredRooms' => $filteredRooms,
            'totalScore' => $this->totalScore,
            'totalReportsCount' => $this->totalReportsCount,
            'distinctResidentsCount' => $this->distinctResidentsCount,
            'reportsByUnit' => $this->reportsByUnit,
            'topResidents' => $this->topResidents,
            'reportsList' => $this->reportsList,
            'residentsList' => $residentsList
        ]);
    }
}

