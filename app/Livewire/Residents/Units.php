<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\Report;
use App\Models\Category;
use App\Services\ResidentService;

class Units extends Component
{
    public $units = [];
    public $loading = true;
    public $error = null;
    public $search = '';
    public $filterEmptyBeds = false;
    public $selectedResidents = [];
    public $showReportModal = false;
    public $reportType = 'individual';
    public $currentResident = null;
    public $currentRoom = null;
    public $categories = [];
    public $reports = [];
    public $selectedReports = [];
    public $notes = '';
    public $expandedUnits = [];
    public $reportModalLoading = false;

    public function mount()
    {
        $this->loadUnits();
        $this->loadReportData();
    }

    public function loadUnits()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $residentService = new ResidentService();
            $this->units = $residentService->getAllResidents();
            $this->sortData();
        } catch (\Exception $e) {
            $this->error = 'خطا در دریافت اطلاعات از دیتابیس: ' . $e->getMessage();
            $this->units = $this->getSampleData();
        }

        $this->loading = false;
    }

    private function sortData()
    {
        usort($this->units, function ($a, $b) {
            return $a['unit']['code'] <=> $b['unit']['code'];
        });

        foreach ($this->units as &$unit) {
            usort($unit['rooms'], function ($a, $b) {
                $aNum = intval(preg_replace('/[^0-9]/', '', $a['name']));
                $bNum = intval(preg_replace('/[^0-9]/', '', $b['name']));
                return $aNum <=> $bNum;
            });
        }
    }

    public function loadReportData()
    {
        $this->categories = Category::with('reports')->get()->toArray();
        $this->reports = Report::all()->toArray();
    }

    public function openIndividualReport($resident, $bed, $unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        $this->reportType = 'individual';
        $this->currentResident = [
            'id' => $resident['id'],
            'name' => $resident['full_name'],
            'phone' => $resident['phone'],
            'job' => $resident['job'] ?? null,
            'bed_id' => $bed['id'],
            'bed_name' => $bed['name'],
            'unit_id' => $unit['unit']['id'],
            'unit_name' => $unit['unit']['name'],
            'room_id' => $room['id'],
            'room_name' => $room['name']
        ];

        $this->loadReportData();
        $this->selectedReports = [];
        $this->notes = '';
        $this->showReportModal = true;
        $this->dispatch('modal-opened');
    }

    public function openGroupReportFromRoom($unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        $roomResidents = [];
        foreach ($room['beds'] as $bed) {
            if ($bed['resident']) {
                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                $roomResidents[$key] = [
                    'resident_id' => $bed['resident']['id'],
                    'resident_name' => $bed['resident']['full_name'],
                    'phone' => $bed['resident']['phone'],
                    'job' => $bed['resident']['job'] ?? null,
                    'bed_id' => $bed['id'],
                    'bed_name' => $bed['name'],
                    'unit_id' => $unit['unit']['id'],
                    'unit_name' => $unit['unit']['name'],
                    'room_id' => $room['id'],
                    'room_name' => $room['name']
                ];
            }
        }

        if (empty($roomResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'این اتاق هیچ اقامت‌گری ندارد.'
            ]);
            return;
        }

        $this->reportType = 'group';
        $this->currentRoom = [
            'unit_id' => $unit['unit']['id'],
            'unit_name' => $unit['unit']['name'],
            'room_id' => $room['id'],
            'room_name' => $room['name']
        ];
        $this->selectedResidents = $roomResidents;

        $this->loadReportData();
        $this->selectedReports = [];
        $this->notes = '';
        $this->showReportModal = true;
        $this->dispatch('modal-opened');
    }

    public function toggleUnitExpansion($unitIndex)
    {
        if (in_array($unitIndex, $this->expandedUnits)) {
            $this->expandedUnits = array_diff($this->expandedUnits, [$unitIndex]);
        } else {
            $this->expandedUnits[] = $unitIndex;
        }
    }

    public function getJobTitle($job)
    {
        $jobs = [
            'daneshjo_dolati' => 'دانشجوی دولتی',
            'daneshjo_azad' => 'دانشجوی آزاد',
            'daneshjo_other' => 'سایر دانشجویان',
            'karmand_shakhse' => 'کارمند بخش خصوصی',
            'karmand_dolat' => 'کارمند دولت',
            'nurse' => 'پرستار',
            'azad' => 'آزاد',
            'other' => 'سایر'
        ];
        return $jobs[$job] ?? $job;
    }

    public function openSelectedGroupReport()
    {
        if (empty($this->selectedResidents)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک اقامت‌گر را انتخاب کنید.'
            ]);
            return;
        }

        $this->reportType = 'group';
        $this->loadReportData();
        $this->selectedReports = [];
        $this->notes = '';
        $this->showReportModal = true;
        $this->dispatch('modal-opened');
    }

    public function submitReport()
    {
        if (empty($this->selectedReports)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک گزارش را انتخاب کنید.'
            ]);
            return;
        }

        try {
            if ($this->reportType === 'individual') {
                $this->submitIndividualReport();
            } else {
                $this->submitGroupReport();
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ثبت گزارش: ' . $e->getMessage()
            ]);
        }
    }

    private function submitIndividualReport()
    {
        foreach ($this->selectedReports as $reportId) {
            \App\Models\ResidentReport::create([
                'report_id' => $reportId,
                'resident_id' => $this->currentResident['id'],
                'resident_name' => $this->currentResident['name'],
                'phone' => $this->currentResident['phone'],  // افزودن شماره تلفن
                'unit_id' => $this->currentResident['unit_id'],
                'unit_name' => $this->currentResident['unit_name'],
                'room_id' => $this->currentResident['room_id'],
                'room_name' => $this->currentResident['room_name'],
                'bed_id' => $this->currentResident['bed_id'],
                'bed_name' => $this->currentResident['bed_name'],
                'notes' => $this->notes,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش برای اقامت‌گر ثبت شد.'
        ]);

        $this->closeModal();
    }

    private function submitGroupReport()
    {
        foreach ($this->selectedResidents as $residentData) {
            foreach ($this->selectedReports as $reportId) {
                \App\Models\ResidentReport::create([
                    'report_id' => $reportId,
                    'resident_id' => $residentData['resident_id'],
                    'resident_name' => $residentData['resident_name'],
                    'phone' => $residentData['phone'],  // افزودن شماره تلفن
                    'unit_id' => $residentData['unit_id'],
                    'unit_name' => $residentData['unit_name'],
                    'room_id' => $residentData['room_id'],
                    'room_name' => $residentData['room_name'],
                    'bed_id' => $residentData['bed_id'],
                    'bed_name' => $residentData['bed_name'],
                    'notes' => $this->notes,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش‌ها برای اقامت‌گران انتخاب شده ثبت شد.'
        ]);

        $this->closeModal();
        $this->selectedResidents = [];
    }

    public function closeModal()
    {
        $this->showReportModal = false;
        $this->selectedReports = [];
        $this->notes = '';
        $this->currentResident = null;
        $this->currentRoom = null;
        $this->reportModalLoading = false;
    }

    public function toggleSelectResident($key, $resident, $bed, $unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];

        if (isset($this->selectedResidents[$key])) {
            unset($this->selectedResidents[$key]);
        } else {
            $this->selectedResidents[$key] = [
                'resident_id' => $resident['id'],
                'resident_name' => $resident['full_name'],
                'phone' => $resident['phone'],
                'job' => $resident['job'] ?? null,
                'bed_id' => $bed['id'],
                'bed_name' => $bed['name'],
                'unit_id' => $unit['unit']['id'],
                'unit_name' => $unit['unit']['name'],
                'room_id' => $room['id'],
                'room_name' => $room['name']
            ];
        }
    }

    public function selectAllInRoom($unitIndex, $roomIndex)
    {
        $unit = $this->units[$unitIndex];
        $room = $unit['rooms'][$roomIndex];
        $allSelected = true;

        foreach ($room['beds'] as $bed) {
            if ($bed['resident']) {
                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                if (!isset($this->selectedResidents[$key])) {
                    $allSelected = false;
                    break;
                }
            }
        }

        foreach ($room['beds'] as $bed) {
            if ($bed['resident']) {
                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                if ($allSelected) {
                    unset($this->selectedResidents[$key]);
                } else {
                    $this->selectedResidents[$key] = [
                        'resident_id' => $bed['resident']['id'],
                        'resident_name' => $bed['resident']['full_name'],
                        'phone' => $bed['resident']['phone'],
                        'job' => $bed['resident']['job'] ?? null,
                        'bed_id' => $bed['id'],
                        'bed_name' => $bed['name'],
                        'unit_id' => $unit['unit']['id'],
                        'unit_name' => $unit['unit']['name'],
                        'room_id' => $room['id'],
                        'room_name' => $room['name']
                    ];
                }
            }
        }
    }

    public function getFilteredUnits()
    {
        $filteredUnits = $this->units;

        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $filteredUnits = array_filter($filteredUnits, function ($unit) use ($searchTerm) {
                foreach ($unit['rooms'] as $room) {
                    if (strpos(strtolower($room['name']), $searchTerm) !== false) {
                        return true;
                    }
                    foreach ($room['beds'] as $bed) {
                        if ($bed['resident'] && (
                            strpos(strtolower($bed['resident']['full_name']), $searchTerm) !== false ||
                            strpos(strtolower($bed['resident']['phone']), $searchTerm) !== false
                        )) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        if ($this->filterEmptyBeds) {
            $filteredUnits = array_filter($filteredUnits, function ($unit) {
                foreach ($unit['rooms'] as $room) {
                    foreach ($room['beds'] as $bed) {
                        if ($bed['resident']) {
                            return true;
                        }
                    }
                }
                return false;
            });
        }

        return array_values($filteredUnits);
    }

    public function render()
    {
        $filteredUnits = $this->getFilteredUnits();

        return view('livewire.residents.units', [
            'filteredUnits' => $filteredUnits
        ]);
    }
}
