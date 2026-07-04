<?php

namespace App\Livewire\Residents;

use Livewire\Component;
use App\Models\ResidentReport;
use App\Models\Report;
use App\Models\Resident;
use App\Models\Category;
use App\Models\Pattern;
use App\Models\PatternVariable;
use App\Services\ResidentService;

class CreateResidentReport extends Component
{
    public $resident_id;
    public $report_id;
    public $notes;
    public $description;
    public $reports = [];
    public $categories = [];
    public $selectedCategory = null;
    public $selectedResidents = []; // For multiple selection
    public $isGroupMode = false;
    public $sendSms = false;
    public $patternMessage = null;
    public $showPreviewModal = false;
    public $previewMessages = [];

    public function mount($residentId = null)
    {
        if ($residentId) {
            $this->resident_id = $residentId;
        }
        $this->loadReports();
    }

    public function loadReports()
    {
        $this->categories = Category::select('id', 'name')->get();
        // Only load reports when category is selected to improve performance
        if ($this->selectedCategory) {
            $this->reports = Report::where('category_id', $this->selectedCategory)
                ->select('id', 'title', 'negative_score')
                ->get();
        } else {
            $this->reports = collect();
        }
    }

    public function updatedSelectedCategory()
    {
        $this->reports = $this->selectedCategory
            ? Report::where('category_id', $this->selectedCategory)
                ->select('id', 'title', 'negative_score')
                ->orderBy('title')
                ->get()
            : collect();

        // Reset report_id when category changes
        $this->report_id = null;
        $this->patternMessage = null;
    }
    
    public function updatedReportId()
    {
        $this->updatePatternMessage();
    }
    
    public function updatedSendSms()
    {
        if ($this->sendSms) {
            $this->updatePatternMessage();
        }
    }
    
    private function updatePatternMessage()
    {
        if (!$this->sendSms || !$this->report_id) {
            $this->patternMessage = null;
            return;
        }
        
        $report = Report::find($this->report_id);
        if (!$report) {
            $this->patternMessage = null;
            return;
        }
        
        // Get first active pattern for this report
        $pattern = $report->activePatterns()
            ->where('patterns.is_active', true)
            ->whereNotNull('patterns.pattern_code')
            ->first();
        
        if (!$pattern) {
            $this->patternMessage = [
                'success' => false,
                'message' => 'الگویی برای این گزارش تعریف نشده است'
            ];
            return;
        }
        
        $this->patternMessage = [
            'success' => true,
            'pattern_title' => $pattern->title,
            'pattern_code' => $pattern->pattern_code,
            'original_message' => $pattern->text,
            'pattern' => $pattern
        ];
    }

    protected $rules = [
        'resident_id' => 'required_without:selectedResidents|exists:residents,resident_id',
        'selectedResidents' => 'required_without:resident_id|array',
        'selectedResidents.*' => 'exists:residents,resident_id',
        'report_id' => 'required|exists:reports,id',
        'description' => 'nullable|string|max:1000',
    ];
    
    public function showSmsPreview()
    {
        if (!$this->sendSms || !$this->report_id) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'لطفاً ابتدا گزارش را انتخاب و گزینه ارسال پیامک را فعال کنید'
            ]);
            return;
        }
        
        $residents = $this->getTargetResidents();
        
        if (empty($residents)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'هیچ اقامت‌گری برای پیش نمایش انتخاب نشده است'
            ]);
            return;
        }
        
        $this->previewMessages = [];
        $pattern = $this->patternMessage['pattern'] ?? null;
        
        if (!$pattern) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'الگویی برای این گزارش یافت نشد'
            ]);
            return;
        }
        
        foreach ($residents as $resident) {
            $finalMessage = $this->generatePersonalizedMessage($pattern, $resident);
            
            $this->previewMessages[] = [
                'resident_id' => $resident->resident_id,
                'resident_name' => $resident->resident_full_name ?? 'نامشخص',
                'phone' => $resident->resident_phone ?? 'ندارد',
                'unit_name' => $resident->unit_name ?? '',
                'room_name' => $resident->room_name ?? '',
                'message' => $finalMessage,
                'has_phone' => !empty($resident->resident_phone)
            ];
        }
        
        $this->showPreviewModal = true;
    }
    
    private function getTargetResidents()
    {
        $residents = collect();
        
        if ($this->isGroupMode && !empty($this->selectedResidents)) {
            $residents = Resident::whereIn('resident_id', $this->selectedResidents)->get();
        } elseif ($this->resident_id) {
            $resident = Resident::where('resident_id', $this->resident_id)->first();
            if ($resident) {
                $residents = collect([$resident]);
            }
        }
        
        return $residents;
    }
    
    private function generatePersonalizedMessage($pattern, $resident)
    {
        $message = $pattern->text;
        $report = Report::find($this->report_id);
        
        // Extract variable codes from pattern text
        preg_match_all('/\{(\d+)\}/', $message, $matches);
        $variableCodes = $matches[0];
        
        if (empty($variableCodes)) {
            return $message;
        }
        
        // Get pattern variable
        $patternVariable = PatternVariable::where('pattern_code', $pattern->pattern_code)
            ->where('is_active', true)
            ->first();
        
        if (!$patternVariable) {
            return $message;
        }
        
        // Replace variables
        foreach ($variableCodes as $code) {
            $pivotData = \Illuminate\Support\Facades\DB::table('pattern_pattern_variables')
                ->where('pattern_id', $pattern->id)
                ->where('variable_code', $code)
                ->first();
            
            if ($pivotData && $pivotData->table_field) {
                $value = $this->getVariableValue($patternVariable->table_name, $pivotData->table_field, $resident, $report);
                $message = str_replace($code, $value, $message);
            }
        }
        
        return $message;
    }
    
    private function getVariableValue($tableName, $tableField, $resident, $report)
    {
        switch ($tableName) {
            case 'residents':
                return $resident->$tableField ?? '';
            case 'reports':
                return $report->$tableField ?? '';
            default:
                return '';
        }
    }
    
    public function closePreviewModal()
    {
        $this->showPreviewModal = false;
        $this->previewMessages = [];
    }

    public function save()
    {
        \Log::info('CreateResidentReport save() called', [
            'resident_id' => $this->resident_id,
            'selectedResidents' => $this->selectedResidents,
            'report_id' => $this->report_id,
            'description' => $this->description,
            'notes' => $this->notes,
            'isGroupMode' => $this->isGroupMode,
            'sendSms' => $this->sendSms
        ]);
        
        $this->validate();
        
        $residents = $this->getTargetResidents();
        
        if ($residents->isEmpty()) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'هیچ اقامت‌گری برای ثبت گزارش انتخاب نشده است'
            ]);
            return;
        }
        
        $report = Report::find($this->report_id);
        
        // Check for existing reports in a single query for performance
        $existingReportIds = ResidentReport::whereIn('resident_id', $residents->pluck('resident_id'))
            ->where('report_id', $this->report_id)
            ->pluck('resident_id')
            ->toArray();
        
        // Filter out residents with existing reports
        $residentsToCreate = $residents->reject(function ($resident) use ($existingReportIds) {
            return in_array($resident->resident_id, $existingReportIds);
        });
        
        if ($residentsToCreate->isEmpty()) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'همه گزارش‌ها قبلاً برای این اقامت‌گران ثبت شده است'
            ]);
            return;
        }
        
        // Prepare batch insert data
        $reportsToInsert = [];
        foreach ($residentsToCreate as $resident) {
            $reportsToInsert[] = [
                'resident_id' => $resident->resident_id,
                'report_id' => $this->report_id,
                'unit_id' => $resident->unit_id ?? null,
                'room_id' => $resident->room_id ?? null,
                'bed_id' => $resident->bed_id ?? null,
                'notes' => $this->notes,
                'description' => $this->description,
                'has_been_sent' => $this->sendSms,
                'is_checked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Batch insert for better performance
        ResidentReport::insert($reportsToInsert);
        
        // Retrieve the created reports to trigger events for grant deactivation
        $createdReports = ResidentReport::whereIn('resident_id', $residentsToCreate->pluck('resident_id'))
            ->where('report_id', $this->report_id)
            ->where('has_been_sent', $this->sendSms)
            ->get();
        
        // Manually trigger events for grant deactivation
        foreach ($createdReports as $createdReport) {
            event(new \App\Events\ResidentReportCreated($createdReport));
        }
        
        \Log::info('ResidentReports batch created', [
            'count' => count($reportsToInsert),
            'sendSms' => $this->sendSms
        ]);
        
        // Handle SMS sending if enabled
        if ($this->sendSms && $this->patternMessage && $this->patternMessage['success']) {
            $this->sendSmsToResidents($residentsToCreate, $report);
        }
        
        // Reset form
        $this->reset(['report_id', 'notes', 'description']);
        
        // Send event to update list
        $this->dispatch('reportCreated');
        
        // Show success message
        $message = $residentsToCreate->count() > 1 
            ? "{$residentsToCreate->count()} گزارش با موفقیت ثبت شد"
            : 'گزارش با موفقیت ثبت شد';
            
        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => $message
        ]);
    }
    
    private function sendSmsToResidents($residents, $report)
    {
        $pattern = $this->patternMessage['pattern'];

        // Cache pattern variables and pivot data outside the loop for performance
        preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
        $variableCodes = $matches[0];

        // Get all pivot data at once
        $pivotDataMap = \Illuminate\Support\Facades\DB::table('pattern_pattern_variables')
            ->where('pattern_id', $pattern->id)
            ->whereIn('variable_code', $variableCodes)
            ->get()
            ->keyBy('variable_code');

        // Get pattern variable once
        $patternVariable = PatternVariable::where('pattern_code', $pattern->pattern_code)
            ->where('is_active', true)
            ->first();

        // Get sender number once
        $senderNumber = \App\Models\SenderNumber::getActivePatternNumbers()->first();
        $senderNumberValue = $senderNumber ? $senderNumber->number : null;
        $apiKey = $senderNumber ? $senderNumber->api_key : null;

        $bodyId = (int)$pattern->pattern_code;
        $melipayamakService = new \App\Services\MelipayamakService();

        foreach ($residents as $resident) {
            if (empty($resident->resident_phone)) {
                continue;
            }

            try {
                // Generate personalized message
                $finalMessage = $this->generatePersonalizedMessage($pattern, $resident);

                // Extract variables for SMS using cached data
                $variables = [];
                foreach ($variableCodes as $code) {
                    $pivot = $pivotDataMap->get($code);

                    if ($pivot && $pivot->table_field && $patternVariable) {
                        $value = $this->getVariableValue($patternVariable->table_name, $pivot->table_field, $resident, $report);
                        $variables[] = $value;
                    }
                }

                // Create SMS record with pending status
                $smsMessageResident = \App\Models\SmsMessageResident::create([
                    'sms_message_id' => null,
                    'report_id' => $this->report_id,
                    'pattern_id' => $pattern->id,
                    'is_pattern' => true,
                    'pattern_variables' => implode(';', $variables),
                    'resident_id' => $resident->id,
                    'resident_name' => $resident->resident_full_name ?? '',
                    'phone' => $resident->resident_phone,
                    'title' => $pattern->title,
                    'description' => $finalMessage,
                    'status' => 'pending',
                ]);

                // Send SMS synchronously but with timeout to prevent blocking
                $result = $melipayamakService->sendByBaseNumber(
                    $resident->resident_phone,
                    $bodyId,
                    $variables,
                    $senderNumberValue,
                    $apiKey
                );

                // Update SMS record status
                if ($result['success']) {
                    $smsMessageResident->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_code' => $result['response_code'] ?? null,
                        'rec_id' => $result['rec_id'] ?? null,
                        'api_response' => $result['api_response'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]);
                } else {
                    $smsMessageResident->update([
                        'status' => 'failed',
                        'error_message' => $result['message'] ?? 'خطا در ارسال',
                        'response_code' => $result['response_code'] ?? null,
                        'rec_id' => $result['rec_id'] ?? null,
                        'api_response' => $result['api_response'] ?? null,
                        'raw_response' => $result['raw_response'] ?? null,
                    ]);
                }

                \Log::info('SMS record created and sent for resident', [
                    'resident_id' => $resident->resident_id,
                    'phone' => $resident->resident_phone,
                    'pattern_id' => $pattern->id,
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'No message'
                ]);

            } catch (\Exception $e) {
                \Log::error('Error creating/sending SMS for resident', [
                    'resident_id' => $resident->resident_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.residents.create-resident-report');
    }
}
