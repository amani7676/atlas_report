<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\ViolationSetting;

class Violations extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $reportFilter = '';
    public $dateFilter = '';
    public $showStatistics = false;
    
    // تنظیمات نمایش آمار
    public $minViolationCount = 1;
    public $minReportCount = 1;
    public $maxViolationScore = 10;
    public $summaryTopCount = 15;
    public $showMostRepeatedViolation = true;
    public $showMostViolationsPerson = true;
    public $showAllPersonViolations = true;

    public function mount()
    {
        $this->loadSettings();
    }
    
    /**
     * بارگذاری تنظیمات از دیتابیس
     */
    public function loadSettings()
    {
        // ایجاد تنظیمات پیش‌فرض اگر وجود نداشته باشند
        ViolationSetting::createDefaultSettings();
        
        // بارگذاری مقادیر از دیتابیس
        $this->minViolationCount = (int) ViolationSetting::getValue('min_violation_count', 1);
        $this->minReportCount = (int) ViolationSetting::getValue('min_report_count', 1);
        $this->maxViolationScore = (int) ViolationSetting::getValue('max_violation_score', 10);
        $this->summaryTopCount = (int) ViolationSetting::getValue('summary_top_count', 15);
        $this->showMostRepeatedViolation = ViolationSetting::getValue('show_most_repeated_violation', '1') === '1';
        $this->showMostViolationsPerson = ViolationSetting::getValue('show_most_violations_person', '1') === '1';
        $this->showAllPersonViolations = ViolationSetting::getValue('show_all_person_violations', '1') === '1';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingReportFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }
    
    public function toggleStatistics()
    {
        $this->showStatistics = !$this->showStatistics;
    }
    
    public function updateSettings()
    {
        $this->validate([
            'minViolationCount' => 'required|integer|min:1',
            'minReportCount' => 'required|integer|min:1',
            'maxViolationScore' => 'required|integer|min:0',
            'summaryTopCount' => 'required|integer|min:1|max:50',
        ]);
        
        // ذخیره تنظیمات در دیتابیس
        ViolationSetting::setValue('min_violation_count', $this->minViolationCount);
        ViolationSetting::setValue('min_report_count', $this->minReportCount);
        ViolationSetting::setValue('max_violation_score', $this->maxViolationScore);
        ViolationSetting::setValue('summary_top_count', $this->summaryTopCount);
        ViolationSetting::setValue('show_most_repeated_violation', $this->showMostRepeatedViolation ? '1' : '0', null, 'boolean');
        ViolationSetting::setValue('show_most_violations_person', $this->showMostViolationsPerson ? '1' : '0', null, 'boolean');
        ViolationSetting::setValue('show_all_person_violations', $this->showAllPersonViolations ? '1' : '0', null, 'boolean');
        
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'تنظیمات با موفقیت در دیتابیس ذخیره شد.'
        ]);
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->reportFilter = '';
        $this->dateFilter = '';
        $this->resetPage();
    }

    public function getReportsListProperty()
    {
        return Report::orderBy('title')->get();
    }
    
    public function getViolationStatisticsProperty()
    {
        // آمار تخلفات تکراری
        $repeatedViolations = ResidentReport::selectRaw('
                resident_id,
                resident_name,
                report_id,
                reports.title as report_title,
                COUNT(*) as violation_count,
                SUM(reports.negative_score) as total_score
            ')
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->where('reports.negative_score', '>', 0)
            ->groupBy('resident_id', 'resident_name', 'report_id', 'reports.title')
            ->having('violation_count', '>=', $this->minViolationCount)
            ->orderBy('violation_count', 'desc')
            ->orderBy('total_score', 'desc')
            ->limit(20)
            ->get();
        
        // آمار گزارش‌های اقامت‌گران
        $residentReports = ResidentReport::selectRaw('
                resident_id,
                resident_name,
                COUNT(*) as report_count,
                SUM(CASE WHEN reports.negative_score > 0 THEN reports.negative_score ELSE 0 END) as total_violation_score
            ')
            ->leftJoin('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->groupBy('resident_id', 'resident_name')
            ->having('report_count', '>=', $this->minReportCount)
            ->orderBy('report_count', 'desc')
            ->orderBy('total_violation_score', 'desc')
            ->limit(20)
            ->get();
        
        // اقامت‌گران برتر (بدون تخلف یا با امتیاز کم)
        $topResidents = ResidentReport::selectRaw('
                residents.resident_id,
                residents.resident_full_name,
                residents.resident_phone,
                COUNT(resident_reports.id) as total_reports,
                SUM(CASE WHEN reports.negative_score > 0 THEN reports.negative_score ELSE 0 END) as total_violation_score
            ')
            ->join('residents', 'resident_reports.resident_id', '=', 'residents.resident_id')
            ->leftJoin('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->groupBy('residents.resident_id', 'residents.resident_full_name', 'residents.resident_phone')
            ->having('total_violation_score', '<=', $this->maxViolationScore)
            ->orderBy('total_violation_score', 'asc')
            ->orderBy('total_reports', 'desc')
            ->limit(20)
            ->get();
        
        // آمار خلاصه تخلفات
        $summaryStats = [
            // 1. بیشترین تکرار یک تخلف یکسان
            'mostRepeatedViolation' => ResidentReport::selectRaw('
                    reports.title as report_title,
                    resident_reports.resident_name,
                    COUNT(*) as count
                ')
                ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->where('reports.negative_score', '>', 0)
                ->groupBy('reports.title', 'resident_reports.resident_name')
                ->orderBy('count', 'desc')
                ->first(),
            
            // 2. بیشترین تعداد تخلف برای یک شخص
            'mostViolationsPerson' => ResidentReport::selectRaw('
                    resident_reports.resident_name,
                    COUNT(CASE WHEN reports.negative_score > 0 THEN 1 END) as violation_count,
                    SUM(CASE WHEN reports.negative_score > 0 THEN reports.negative_score ELSE 0 END) as total_score
                ')
                ->leftJoin('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->groupBy('resident_reports.resident_name')
                ->orderBy('violation_count', 'desc')
                ->orderBy('total_score', 'desc')
                ->first(),
            
            // 3. مجموع تخلفات هر شخص (برای نمایش جدول کامل)
            'allPersonViolations' => ResidentReport::selectRaw('
                    resident_reports.resident_name,
                    COUNT(CASE WHEN reports.negative_score > 0 THEN 1 END) as violation_count,
                    SUM(CASE WHEN reports.negative_score > 0 THEN reports.negative_score ELSE 0 END) as total_score,
                    COUNT(*) as total_reports
                ')
                ->leftJoin('reports', 'resident_reports.report_id', '=', 'reports.id')
                ->groupBy('resident_reports.resident_name')
                ->orderBy('total_score', 'desc')
                ->orderBy('violation_count', 'desc')
                ->limit($this->summaryTopCount)
                ->get(),
        ];
        
        return [
            'repeatedViolations' => $repeatedViolations,
            'residentReports' => $residentReports,
            'topResidents' => $topResidents,
            'summaryStats' => $summaryStats,
        ];
    }

    public function render()
    {
        $query = ResidentReport::with(['resident', 'report'])
            ->join('reports', 'resident_reports.report_id', '=', 'reports.id')
            ->where('reports.negative_score', '>', 0)
            ->orderBy('resident_reports.created_at', 'desc');

        // فیلتر جستجو
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('resident_reports.resident_name', 'like', '%' . $this->search . '%')
                  ->orWhere('reports.title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('resident', function ($q) {
                      $q->where('resident_full_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // فیلتر گزارش
        if ($this->reportFilter) {
            $query->where('report_id', $this->reportFilter);
        }

        // فیلتر تاریخ
        if ($this->dateFilter) {
            $query->whereDate('resident_reports.created_at', $this->dateFilter);
        }

        $reports = $query->paginate(20);

        return view('livewire.reports.violations', [
            'reports' => $reports,
            'statistics' => $this->showStatistics ? $this->violationStatistics : null,
        ]);
    }
}
