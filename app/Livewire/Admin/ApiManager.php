<?php

namespace App\Livewire\Admin;

use App\Models\Resident;
use App\Models\Report;
use App\Models\ResidentReport;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ApiManager extends Component
{
    public $search = '';
    public $selectedResident = null;
    public $residentData = null;
    public $reports = [];
    public $editingEndpoint = null;
    public $editEndpointName = '';
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        $this->loadReports();
    }

    public function loadReports()
    {
        $this->reports = Report::with('category')->orderBy('title')->get();
        
        // محاسبه آمار برای هر گزارش
        foreach ($this->reports as $report) {
            // مجموع تخلفات این گزارش در کل سیستم
            $report->total_violations_system = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('reports.id', $report->id)
                ->sum('reports.negative_score') ?? 0;
            
            // تعداد کاربرانی که این گزارش برایشان ثبت شده
            $report->affected_residents_count = ResidentReport::where('report_id', $report->id)->count();
            
            // مجموع کل تخلفات در سیستم (برای همه گزارش‌ها)
            $report->total_violations_all_reports = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('reports.category_id', 1) // دسته‌بندی تخلف
                ->sum('reports.negative_score') ?? 0;
        }
    }

    public function selectResident($residentId)
    {
        $this->selectedResident = $residentId;
        $resident = Resident::where('resident_id', $residentId)->first();
        
        if ($resident) {
            $this->residentData = $resident;
            
            // دریافت گزارش‌های این کاربر
            $residentReports = ResidentReport::where('resident_id', $residentId)
                ->with('report', 'report.category')
                ->get();
            
            $this->residentData->reports = $residentReports;
            
            // محاسبه مجموع تخلفات
            $totalViolations = ResidentReport::join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('resident_reports.resident_id', $residentId)
                ->where('reports.category_id', 1) // دسته‌بندی تخلف
                ->sum('reports.negative_score') ?? 0;
            
            $this->residentData->total_violations = $totalViolations;
            
            // تفکیک تخلفات و اطلاع‌رسانی‌ها
            $this->residentData->violations = $residentReports->filter(function ($rr) {
                return $rr->report && $rr->report->category && $rr->report->category->name === 'تخلف';
            })->values();
            
            $this->residentData->notifications = $residentReports->filter(function ($rr) {
                return $rr->report && $rr->report->category && $rr->report->category->name === 'اطلاع‌رسانی';
            })->values();
        }
    }

    public function startEditEndpoint($reportId)
    {
        $report = Report::find($reportId);
        if ($report) {
            $this->editingEndpoint = $reportId;
            $this->editEndpointName = $report->api_endpoint_name ?? '';
        }
    }

    public function cancelEditEndpoint()
    {
        $this->editingEndpoint = null;
        $this->editEndpointName = '';
    }

    public function updateEndpoint()
    {
        $this->validate([
            'editEndpointName' => 'nullable|string|max:255',
        ]);

        $report = Report::find($this->editingEndpoint);
        if ($report) {
            $report->update([
                'api_endpoint_name' => $this->editEndpointName ?: null,
            ]);

            $this->message = 'نام endpoint با موفقیت به‌روزرسانی شد';
            $this->messageType = 'success';
            $this->loadReports();
            $this->cancelEditEndpoint();
        }
    }

    public function getResidentsProperty()
    {
        $query = Resident::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('resident_full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('resident_phone', 'like', '%' . $this->search . '%')
                    ->orWhere('resident_id', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('resident_full_name')->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.api-manager', [
            'residents' => $this->residents,
        ]);
    }
}
