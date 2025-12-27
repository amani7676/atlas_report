<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Category;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str; // اضافه شده

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

    // Propertyهای computed
    public function getTotalScoreProperty()
    {
        $query = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
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
        return ResidentReport::whereNotNull('resident_id')
            ->distinct('resident_id')
            ->count('resident_id');
    }

    public function getReportsByUnitProperty()
    {
        return ResidentReport::selectRaw('unit_name, COUNT(*) as count, SUM(reports.negative_score) as total_score')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->whereNotNull('unit_name')
            ->groupBy('unit_name')
            ->orderByDesc('count')
            ->get();
    }

    public function getTopResidentsProperty()
    {
        return ResidentReport::selectRaw('
            resident_name,
            MAX(unit_name) as unit_name,
            MAX(room_name) as room_name,
            MAX(phone) as phone,
            COUNT(*) as report_count,
            SUM(reports.negative_score) as total_score
        ')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->whereNotNull('resident_name')
            ->groupBy('resident_name')
            ->orderByDesc('report_count')
            ->limit(10)
            ->get();
    }

    public function getReportsQueryProperty(): Builder
    {
        $query = ResidentReport::with(['report', 'report.category'])
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
        $this->categories = Category::all();
        $this->reportsList = Report::all();

        $this->units = ResidentReport::select('unit_id', 'unit_name')
            ->whereNotNull('unit_id')
            ->distinct()
            ->orderBy('unit_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->unit_id,
                    'name' => $item->unit_name
                ];
            })->toArray();

        $this->rooms = ResidentReport::select('room_id', 'room_name', 'unit_id')
            ->whereNotNull('room_id')
            ->distinct()
            ->orderBy('room_name')
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
        $this->gotoPage(1);
    }

    // متدهای جدید برای جستجوی اقامت‌گران
    public function searchResidents()
    {
        if (empty($this->residentSearch) || strlen($this->residentSearch) < 2) {
            return [];
        }

        return ResidentReport::where('resident_name', 'like', '%' . $this->residentSearch . '%')
            ->distinct('resident_name')
            ->orderBy('resident_name')
            ->pluck('resident_name')
            ->toArray();
    }

    public function selectResident($residentName)
    {
        $this->selectedResident = $residentName;
        $this->showResidentDetails = true;
        $this->residentSearch = $residentName; // برای نمایش نام در اینپوت

        $this->residentReports = ResidentReport::with(['report', 'report.category'])
            ->where('resident_name', $residentName)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function closeResidentDetails()
    {
        $this->selectedResident = null;
        $this->residentReports = [];
        $this->showResidentDetails = false;
        $this->residentSearch = '';
    }

    public function deleteReport($id)
    {
        $report = ResidentReport::findOrFail($id);
        $report->delete();

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش اقامت‌گر حذف شد.'
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

        ResidentReport::whereIn('id', $this->selectedReports)->delete();
        $this->selectedReports = [];

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش‌های انتخاب شده حذف شدند.'
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

    public function render()
    {
        $reports = $this->reportsQuery->paginate($this->perPage);
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
            'residentsList' => $residentsList
        ]);
    }
}
