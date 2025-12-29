<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use App\Models\AutoSms;
use App\Models\AutoSmsCondition;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Auto extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $showModal = false;
    public $showConditionModal = false;
    public $editingId = null;
    public $editingConditionId = null;
    public $currentAutoSmsId = null;
    public $conditions = []; // شرط‌های موقت برای فرم

    // Form properties
    public $title = '';
    public $text = '';
    public $pattern_id = null;
    public $send_type = 'immediate';
    public $scheduled_at = '';
    public $is_active = true;
    public $description = '';

    // Condition form properties
    public $condition_field_type = 'resident';
    public $condition_field_name = '';
    public $condition_data_type = 'string';
    public $condition_operator = '=';
    public $condition_value = '';
    public $condition_logical_operator = 'AND';
    public $condition_order = 0;

    // Available fields for conditions (will be populated from database)
    public $availableFields = [];
    public $fieldDataTypes = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'pattern_id' => 'required|exists:patterns,id',
        'send_type' => 'required|in:immediate,scheduled',
        'scheduled_at' => 'required_if:send_type,scheduled|nullable|date',
        'is_active' => 'boolean',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'title.required' => 'عنوان الزامی است.',
        'pattern_id.required' => 'انتخاب الگوی پیام الزامی است.',
        'pattern_id.exists' => 'الگوی انتخاب شده معتبر نیست.',
        'scheduled_at.required_if' => 'برای ارسال زمان‌دار، زمان ارسال الزامی است.',
    ];

    public function mount()
    {
        $this->loadFieldsFromDatabase();
        
        \Log::info('Auto SMS Component Mounted', [
            'resident_fields_count' => count($this->availableFields['resident'] ?? []),
            'report_fields_count' => count($this->availableFields['report'] ?? []),
            'resident_report_fields_count' => count($this->availableFields['resident_report'] ?? []),
        ]);
    }

    /**
     * بارگذاری فیلدها از دیتابیس
     */
    public function loadFieldsFromDatabase()
    {
        // فیلدهای جدول residents
        $residentColumns = Schema::getColumnListing('residents');
        $residentFields = [];
        $residentDataTypes = [];
        
        foreach ($residentColumns as $column) {
            // حذف فیلدهای غیرقابل استفاده
            if (in_array($column, ['id', 'created_at', 'updated_at', 'resident_data', 'unit_data', 'room_data', 'bed_data', 'extra_data', 'last_synced_at'])) {
                continue;
            }
            
            $columnType = $this->getColumnType('residents', $column);
            $dataType = $this->mapDatabaseTypeToDataType($columnType);
            
            // نام فارسی برای فیلد
            $persianName = $this->getPersianFieldName('resident', $column);
            
            $residentFields[$column] = $persianName;
            $residentDataTypes[$column] = $dataType;
        }
        
        $this->availableFields['resident'] = $residentFields;
        $this->fieldDataTypes['resident'] = $residentDataTypes;
        
        // فیلدهای جدول reports
        $reportColumns = Schema::getColumnListing('reports');
        $reportFields = [];
        $reportDataTypes = [];
        
        foreach ($reportColumns as $column) {
            // حذف فیلدهای غیرقابل استفاده
            if (in_array($column, ['id', 'created_at', 'updated_at', 'category_id'])) {
                continue;
            }
            
            $columnType = $this->getColumnType('reports', $column);
            $dataType = $this->mapDatabaseTypeToDataType($columnType);
            
            // نام فارسی برای فیلد
            $persianName = $this->getPersianFieldName('report', $column);
            
            $reportFields[$column] = $persianName;
            $reportDataTypes[$column] = $dataType;
        }
        
        $this->availableFields['report'] = $reportFields;
        $this->fieldDataTypes['report'] = $reportDataTypes;
        
        \Log::info('Report fields loaded from database', [
            'fields_count' => count($reportFields),
            'fields' => array_keys($reportFields),
        ]);
        
        // فیلدهای aggregate از resident_reports
        $this->availableFields['resident_report'] = [
            'report_count' => 'تعداد گزارش‌ها',
            'total_score' => 'مجموع نمرات منفی',
            'last_report_date' => 'تاریخ آخرین گزارش',
        ];
        $this->fieldDataTypes['resident_report'] = [
            'report_count' => 'number',
            'total_score' => 'number',
            'last_report_date' => 'date',
        ];
    }

    /**
     * دریافت نوع ستون از دیتابیس
     */
    protected function getColumnType($table, $column)
    {
        try {
            $columnInfo = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]);
            if (!empty($columnInfo)) {
                return $columnInfo[0]->Type;
            }
        } catch (\Exception $e) {
            // در صورت خطا، از schema استفاده می‌کنیم
        }
        
        return 'varchar(255)'; // پیش‌فرض
    }

    /**
     * تبدیل نوع دیتابیس به نوع داده ما
     */
    protected function mapDatabaseTypeToDataType($dbType)
    {
        $dbType = strtolower($dbType);
        
        if (strpos($dbType, 'int') !== false || strpos($dbType, 'decimal') !== false || strpos($dbType, 'float') !== false || strpos($dbType, 'double') !== false) {
            return 'number';
        } elseif (strpos($dbType, 'date') !== false || strpos($dbType, 'time') !== false) {
            return 'date';
        } elseif (strpos($dbType, 'bool') !== false || strpos($dbType, 'tinyint(1)') !== false) {
            return 'boolean';
        } else {
            return 'string';
        }
    }

    /**
     * دریافت نام فارسی برای فیلد
     */
    protected function getPersianFieldName($type, $fieldName)
    {
        $persianNames = [
            'resident' => [
                'resident_id' => 'شناسه اقامت‌گر',
                'full_name' => 'نام کامل',
                'phone' => 'شماره تلفن',
                'national_id' => 'کد ملی',
                'national_code' => 'کد ملی',
                'unit_id' => 'شناسه واحد',
                'unit_name' => 'نام واحد',
                'unit_code' => 'کد واحد',
                'room_id' => 'شناسه اتاق',
                'room_name' => 'نام اتاق',
                'bed_id' => 'شناسه تخت',
                'bed_name' => 'نام تخت',
                'contract_start_date' => 'تاریخ شروع قرارداد',
                'contract_end_date' => 'تاریخ پایان قرارداد',
                'contract_expiry_date' => 'تاریخ انقضا قرارداد',
            ],
            'report' => [
                'title' => 'عنوان',
                'description' => 'توضیحات',
                'negative_score' => 'نمره منفی',
                'increase_coefficient' => 'ضریب افزایش',
                'page_number' => 'شماره صفحه',
            ],
        ];
        
        return $persianNames[$type][$fieldName] ?? $fieldName;
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->title = '';
        $this->text = '';
        $this->pattern_id = null;
        $this->send_type = 'immediate';
        $this->scheduled_at = '';
        $this->is_active = true;
        $this->description = '';
        $this->conditions = [];
        $this->resetConditionForm();
        $this->showModal = false;
        $this->resetValidation();
    }

    public function resetConditionForm()
    {
        $this->editingConditionId = null;
        $this->condition_field_type = 'resident';
        $this->condition_field_name = '';
        $this->condition_data_type = 'string';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->condition_logical_operator = 'AND';
        $this->condition_order = 0;
        $this->showConditionModal = false;
        $this->resetValidation();
    }

    public function updatedConditionFieldName()
    {
        // وقتی فیلد تغییر کرد، نوع داده را به‌روزرسانی کن
        if ($this->condition_field_name && isset($this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name])) {
            $this->condition_data_type = $this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name];
        } else {
            $this->condition_data_type = 'string';
        }
    }

    public function updatedConditionFieldType()
    {
        // وقتی نوع فیلد تغییر کرد، فیلد و نوع داده را ریست کن
        $this->condition_field_name = '';
        $this->condition_data_type = 'string';
    }

    public function openModal($id = null)
    {
        if ($id) {
            $autoSms = AutoSms::with('conditions')->findOrFail($id);
            $this->editingId = $id;
            $this->title = $autoSms->title;
            $this->text = $autoSms->text;
            $this->pattern_id = $autoSms->pattern_id;
            $this->send_type = $autoSms->send_type;
            $this->scheduled_at = $autoSms->scheduled_at ? $autoSms->scheduled_at->format('Y-m-d\TH:i') : '';
            $this->is_active = $autoSms->is_active;
            $this->description = $autoSms->description ?? '';
            // بارگذاری شرط‌های موجود
            $this->conditions = $autoSms->conditions->map(function($condition) {
                return [
                    'id' => $condition->id,
                    'field_type' => $condition->field_type,
                    'field_name' => $condition->field_name,
                    'data_type' => $condition->data_type ?? 'string',
                    'operator' => $condition->operator,
                    'value' => $condition->value,
                    'logical_operator' => $condition->logical_operator,
                    'order' => $condition->order,
                ];
            })->toArray();
        } else {
            $this->resetForm();
            $this->conditions = [];
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'text' => $this->text, // برای نمایش یا backup
            'pattern_id' => $this->pattern_id,
            'send_type' => $this->send_type,
            'scheduled_at' => $this->send_type === 'scheduled' ? $this->scheduled_at : null,
            'is_active' => $this->is_active,
            'description' => $this->description,
        ];

        if ($this->editingId) {
            $autoSms = AutoSms::findOrFail($this->editingId);
            $autoSms->update($data);
            $autoSmsId = $autoSms->id;
            $message = 'پیامک خودکار با موفقیت به‌روزرسانی شد.';
        } else {
            $autoSms = AutoSms::create($data);
            $autoSmsId = $autoSms->id;
            $message = 'پیامک خودکار با موفقیت ایجاد شد.';
        }

        // ذخیره شرط‌ها
        $this->saveConditions($autoSmsId);

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => $message,
            'duration' => 3000,
        ]);

        $this->resetForm();
        $this->resetPage();
    }

    /**
     * ذخیره شرط‌ها برای Auto SMS
     */
    public function saveConditions($autoSmsId)
    {
        // حذف شرط‌های قدیمی که دیگر در لیست نیستند
        $existingConditionIds = collect($this->conditions)->pluck('id')->filter()->toArray();
        AutoSmsCondition::where('auto_sms_id', $autoSmsId)
            ->whereNotIn('id', $existingConditionIds)
            ->delete();

        // ذخیره یا به‌روزرسانی شرط‌ها
        foreach ($this->conditions as $index => $condition) {
            $conditionData = [
                'auto_sms_id' => $autoSmsId,
                'field_type' => $condition['field_type'],
                'field_name' => $condition['field_name'],
                'data_type' => $condition['data_type'] ?? 'string',
                'operator' => $condition['operator'],
                'value' => $condition['value'],
                'logical_operator' => $condition['logical_operator'] ?? 'AND',
                'order' => $index,
            ];

            if (isset($condition['id']) && $condition['id']) {
                // به‌روزرسانی شرط موجود
                AutoSmsCondition::where('id', $condition['id'])
                    ->where('auto_sms_id', $autoSmsId)
                    ->update($conditionData);
            } else {
                // ایجاد شرط جدید
                AutoSmsCondition::create($conditionData);
            }
        }
    }

    /**
     * اضافه کردن شرط جدید به لیست موقت
     */
    public function addCondition()
    {
        // تعیین نوع داده بر اساس فیلد انتخاب شده
        if ($this->condition_field_name && isset($this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name])) {
            $this->condition_data_type = $this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name];
        } else {
            $this->condition_data_type = 'string';
        }

        $this->validate([
            'condition_field_type' => 'required|in:resident,resident_report,report',
            'condition_field_name' => 'required|string',
            'condition_data_type' => 'required|in:string,number,date,boolean',
            'condition_operator' => 'required|in:>,<,=,>=,<=,contains,not_contains,!=,days_after,days_before',
            'condition_value' => 'required|string',
            'condition_logical_operator' => 'required|in:AND,OR',
        ], [
            'condition_field_type.required' => 'نوع فیلد الزامی است.',
            'condition_field_name.required' => 'نام فیلد الزامی است.',
            'condition_data_type.required' => 'نوع داده الزامی است.',
            'condition_operator.required' => 'عملگر الزامی است.',
            'condition_value.required' => 'مقدار الزامی است.',
        ]);

        $this->conditions[] = [
            'id' => null, // جدید
            'field_type' => $this->condition_field_type,
            'field_name' => $this->condition_field_name,
            'data_type' => $this->condition_data_type,
            'operator' => $this->condition_operator,
            'value' => $this->condition_value,
            'logical_operator' => $this->condition_logical_operator,
            'order' => count($this->conditions),
        ];

        // ریست فرم شرط
        $this->condition_field_type = 'resident';
        $this->condition_field_name = '';
        $this->condition_data_type = 'string';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->condition_logical_operator = 'AND';
    }

    /**
     * ویرایش شرط در لیست موقت
     */
    public function editCondition($index)
    {
        if (isset($this->conditions[$index])) {
            $condition = $this->conditions[$index];
            $this->editingConditionId = $index;
            $this->condition_field_type = $condition['field_type'];
            $this->condition_field_name = $condition['field_name'];
            $this->condition_data_type = $condition['data_type'] ?? 'string';
            $this->condition_operator = $condition['operator'];
            $this->condition_value = $condition['value'];
            $this->condition_logical_operator = $condition['logical_operator'] ?? 'AND';
        }
    }

    /**
     * به‌روزرسانی شرط در لیست موقت
     */
    public function updateCondition()
    {
        if ($this->editingConditionId === null || !isset($this->conditions[$this->editingConditionId])) {
            return;
        }

        // تعیین نوع داده بر اساس فیلد انتخاب شده
        if ($this->condition_field_name && isset($this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name])) {
            $this->condition_data_type = $this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name];
        } else {
            $this->condition_data_type = 'string';
        }

        $this->validate([
            'condition_field_type' => 'required|in:resident,resident_report,report',
            'condition_field_name' => 'required|string',
            'condition_data_type' => 'required|in:string,number,date,boolean',
            'condition_operator' => 'required|in:>,<,=,>=,<=,contains,not_contains,!=,days_after,days_before',
            'condition_value' => 'required|string',
            'condition_logical_operator' => 'required|in:AND,OR',
        ], [
            'condition_field_type.required' => 'نوع فیلد الزامی است.',
            'condition_field_name.required' => 'نام فیلد الزامی است.',
            'condition_data_type.required' => 'نوع داده الزامی است.',
            'condition_operator.required' => 'عملگر الزامی است.',
            'condition_value.required' => 'مقدار الزامی است.',
        ]);

        $this->conditions[$this->editingConditionId] = [
            'id' => $this->conditions[$this->editingConditionId]['id'], // حفظ ID اگر وجود دارد
            'field_type' => $this->condition_field_type,
            'field_name' => $this->condition_field_name,
            'data_type' => $this->condition_data_type,
            'operator' => $this->condition_operator,
            'value' => $this->condition_value,
            'logical_operator' => $this->condition_logical_operator,
            'order' => $this->editingConditionId,
        ];

        // ریست فرم شرط
        $this->editingConditionId = null;
        $this->condition_field_type = 'resident';
        $this->condition_field_name = '';
        $this->condition_data_type = 'string';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->condition_logical_operator = 'AND';
    }

    /**
     * حذف شرط از لیست موقت
     */
    public function removeCondition($index)
    {
        if (isset($this->conditions[$index])) {
            unset($this->conditions[$index]);
            $this->conditions = array_values($this->conditions); // بازسازی ایندکس‌ها
        }
    }

    /**
     * لغو ویرایش شرط
     */
    public function cancelEditCondition()
    {
        $this->editingConditionId = null;
        $this->condition_field_type = 'resident';
        $this->condition_field_name = '';
        $this->condition_data_type = 'string';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->condition_logical_operator = 'AND';
    }

    public function delete($id)
    {
        $autoSms = AutoSms::findOrFail($id);
        $autoSms->delete();

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => 'پیامک خودکار با موفقیت حذف شد.',
            'duration' => 3000,
        ]);

        $this->resetPage();
    }

    public function openConditionModal($autoSmsId, $conditionId = null)
    {
        $this->currentAutoSmsId = $autoSmsId;
        
        if ($conditionId) {
            $condition = AutoSmsCondition::findOrFail($conditionId);
            $this->editingConditionId = $conditionId;
            $this->condition_field_type = $condition->field_type;
            $this->condition_field_name = $condition->field_name;
            $this->condition_data_type = $condition->data_type ?? 'string';
            $this->condition_operator = $condition->operator;
            $this->condition_value = $condition->value;
            $this->condition_logical_operator = $condition->logical_operator;
            $this->condition_order = $condition->order;
        } else {
            $this->resetConditionForm();
            // تعیین order بعدی
            $maxOrder = AutoSmsCondition::where('auto_sms_id', $autoSmsId)->max('order') ?? -1;
            $this->condition_order = $maxOrder + 1;
        }
        
        $this->showConditionModal = true;
    }

    public function closeConditionModal()
    {
        $this->resetConditionForm();
        $this->currentAutoSmsId = null;
    }

    public function saveCondition()
    {
        // تعیین نوع داده بر اساس فیلد انتخاب شده
        if ($this->condition_field_name && isset($this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name])) {
            $this->condition_data_type = $this->fieldDataTypes[$this->condition_field_type][$this->condition_field_name];
        } else {
            $this->condition_data_type = 'string'; // پیش‌فرض
        }

        $operatorRules = 'required|in:>,<,=,>=,<=,contains,not_contains,!=,days_after,days_before';
        
        $this->validate([
            'condition_field_type' => 'required|in:resident,resident_report,report',
            'condition_field_name' => 'required|string',
            'condition_data_type' => 'required|in:string,number,date,boolean',
            'condition_operator' => $operatorRules,
            'condition_value' => 'required|string',
            'condition_logical_operator' => 'required|in:AND,OR',
            'condition_order' => 'required|integer|min:0',
        ], [
            'condition_field_type.required' => 'نوع فیلد الزامی است.',
            'condition_field_name.required' => 'نام فیلد الزامی است.',
            'condition_data_type.required' => 'نوع داده الزامی است.',
            'condition_operator.required' => 'عملگر الزامی است.',
            'condition_value.required' => 'مقدار الزامی است.',
        ]);

        $data = [
            'auto_sms_id' => $this->currentAutoSmsId,
            'field_type' => $this->condition_field_type,
            'field_name' => $this->condition_field_name,
            'data_type' => $this->condition_data_type,
            'operator' => $this->condition_operator,
            'value' => $this->condition_value,
            'logical_operator' => $this->condition_logical_operator,
            'order' => $this->condition_order,
        ];

        try {
            if ($this->editingConditionId) {
                $condition = AutoSmsCondition::findOrFail($this->editingConditionId);
                $condition->update($data);
                $message = 'شرط با موفقیت به‌روزرسانی شد و در دیتابیس ذخیره شد.';
                
                \Log::info('Auto SMS Condition Updated', [
                    'condition_id' => $condition->id,
                    'auto_sms_id' => $this->currentAutoSmsId,
                    'field_type' => $this->condition_field_type,
                    'field_name' => $this->condition_field_name,
                    'data_type' => $this->condition_data_type,
                    'operator' => $this->condition_operator,
                    'value' => $this->condition_value,
                ]);
            } else {
                $condition = AutoSmsCondition::create($data);
                $message = 'شرط با موفقیت اضافه شد و در دیتابیس ذخیره شد.';
                
                \Log::info('Auto SMS Condition Created', [
                    'condition_id' => $condition->id,
                    'auto_sms_id' => $this->currentAutoSmsId,
                    'field_type' => $this->condition_field_type,
                    'field_name' => $this->condition_field_name,
                    'data_type' => $this->condition_data_type,
                    'operator' => $this->condition_operator,
                    'value' => $this->condition_value,
                ]);
            }

            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'message' => $message,
                'duration' => 3000,
            ]);

            $this->closeConditionModal();
        } catch (\Exception $e) {
            \Log::error('Error saving Auto SMS Condition', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا!',
                'message' => 'خطا در ذخیره شرط در دیتابیس: ' . $e->getMessage(),
                'duration' => 5000,
            ]);
        }
    }

    public function deleteCondition($id)
    {
        $condition = AutoSmsCondition::findOrFail($id);
        $condition->delete();

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => 'شرط با موفقیت حذف شد.',
            'duration' => 3000,
        ]);
    }

    public function toggleActive($id)
    {
        $autoSms = AutoSms::findOrFail($id);
        $autoSms->update(['is_active' => !$autoSms->is_active]);

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => 'وضعیت پیامک خودکار تغییر کرد.',
            'duration' => 3000,
        ]);
    }

    public function getAvailableFieldNamesProperty()
    {
        return $this->availableFields[$this->condition_field_type] ?? [];
    }

    public function render()
    {
        $query = AutoSms::with(['conditions', 'pattern']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('text', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('pattern', function($q2) {
                      $q2->where('title', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $autoSmsList = $query->orderBy('created_at', 'desc')->paginate($this->perPage);
        
        // دریافت لیست الگوهای فعال
        $patterns = \App\Models\Pattern::where('is_active', true)->get();

        return view('livewire.sms.auto', [
            'autoSmsList' => $autoSmsList,
            'patterns' => $patterns,
        ]);
    }
}

