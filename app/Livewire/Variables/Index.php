<?php

namespace App\Livewire\Variables;

use Livewire\Component;
use App\Models\PatternVariable;
use App\Models\TableName;
use App\Models\Pattern;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortBy = 'sort_order';
    public $sortDirection = 'asc';
    public $typeFilter = '';
    
    // Modal states
    public $showModal = false;
    public $isEditing = false;
    public $editingId = null;
    
    // Form fields
    public $code = '';
    public $title = '';
    public $pattern_code = ''; // تغییر از table_field به pattern_code
    public $table_name = '';
    public $variable_type = 'user';
    public $description = '';
    public $is_active = true;
    public $sort_order = 0;
    public $pattern_ids = [];
    public $variable_code = '';
    
    // Pattern fields for new modal
    public $selectedPatterns = [];
    public $patternTexts = [];
    public $patternVariables = [];
    public $variableAssignments = []; // [variable_code] = table_field (consolidated for all patterns)
    
    // Table fields
    public $availableTableFields = [];
    public $selectedTableField = '';
    
    // Pattern fields
    public $availablePatterns = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'pattern_code' => 'nullable|string|max:255',
        'table_name' => 'nullable|string|max:255',
        'variable_type' => 'required|in:user,report,general',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0',
        'selectedPatterns' => 'required|array|min:1',
        'selectedPatterns.*' => 'exists:patterns,id',
        'variableAssignments' => 'required|array',
        'variableAssignments.*' => 'required|string',
    ];

    protected $messages = [
        'title.required' => 'عنوان متغیر الزامی است',
        'selectedPatterns.required' => 'انتخاب حداقل یک الگو الزامی است',
        'selectedPatterns.min' => 'انتخاب حداقل یک الگو الزامی است',
        'selectedPatterns.*.exists' => 'الگوی انتخاب شده معتبر نیست',
        'variableAssignments.required' => 'تخصیص متغیرها به الگوها الزامی است',
        'variableAssignments.*.required' => 'برای هر کد متغیر باید یک فیلد انتخاب کنید',
    ];

    public function mount()
    {
        // اگر از route /variables/create آمده‌ایم، مودال ایجاد را باز می‌کنیم
        if (request()->is('variables/create*')) {
            $this->openCreateModal();
        }
        
        // بارگذاری الگوهای موجود
        $this->loadAvailablePatterns();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->loadTableFields();
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $variable = PatternVariable::findOrFail($id);
        $this->editingId = $id;
        $this->isEditing = true; // تنظیم زودتر isEditing
        $this->title = $variable->title;
        $this->pattern_code = $variable->pattern_code; // تغییر از table_field به pattern_code
        $this->table_name = $variable->table_name ?? '';
        $this->variable_type = $variable->variable_type;
        $this->description = $variable->description ?? '';
        $this->is_active = $variable->is_active;
        $this->sort_order = $variable->sort_order;
        $this->loadTableFields();
        $this->selectedTableField = $variable->pattern_code ?? '';
        
        // بارگذاری اتصالات به الگوها
        $patternConnections = DB::table('pattern_pattern_variables')
            ->where('pattern_variable_id', $id)
            ->get();
            
        $this->selectedPatterns = $patternConnections->pluck('pattern_id')->toArray();
        
        // بارگذاری داده‌های الگوها
        $this->loadPatternData();
        
        // بارگذاری تخصیص‌های موجود (consolidated)
        foreach ($patternConnections as $connection) {
            // استفاده از فیلد table_field از جدول اتصال که فیلد مربوط به کد متغیر را ذخیره می‌کند
            $this->variableAssignments[$connection->variable_code] = $connection->table_field;
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->title = '';
        $this->pattern_code = ''; // تغییر از table_field به pattern_code
        $this->table_name = '';
        $this->variable_type = 'user';
        $this->description = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->selectedPatterns = [];
        $this->patternTexts = [];
        $this->patternVariables = [];
        $this->variableAssignments = [];
        $this->editingId = null;
        $this->availableTableFields = [];
        $this->selectedTableField = '';
        $this->resetValidation();
    }
    
    public function loadAvailablePatterns()
    {
        $this->availablePatterns = Pattern::where('is_active', true)
            ->where('status', 'approved') // فقط الگوهای تایید شده
            ->orderBy('title')
            ->get()
            ->mapWithKeys(function ($pattern) {
                $label = $pattern->title;
                if ($pattern->pattern_code) {
                    $label .= ' (' . $pattern->pattern_code . ')';
                }
                return [$pattern->id => $label];
            })
            ->toArray();
    }
    
    public function updatedSelectedPatterns()
    {
        $this->loadPatternData();
        
        // فقط در حالت ایجاد (نه ویرایش)، اگر فقط یک الگو انتخاب شده، عنوان متغیر و کد الگو را با نام و کد الگو یکی کن
        if (!$this->isEditing && count($this->selectedPatterns) === 1) {
            $patternId = $this->selectedPatterns[0];
            $pattern = Pattern::find($patternId);
            if ($pattern) {
                if (empty($this->title)) {
                    $this->title = $pattern->title;
                }
                if (empty($this->pattern_code)) {
                    $this->pattern_code = $pattern->pattern_code;
                }
            }
        }
    }
    
    public function loadPatternData()
    {
        $this->patternTexts = [];
        $this->patternVariables = [];
        $this->variableAssignments = [];
        
        foreach ($this->selectedPatterns as $patternId) {
            $pattern = Pattern::find($patternId);
            if ($pattern) {
                $this->patternTexts[$patternId] = $pattern->text;
                
                // استخراج متغیرها از متن الگو
                preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
                $variables = [];
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $code) {
                        $variables[] = $code;
                    }
                }
                $this->patternVariables[$patternId] = $variables;
                
                // مقداردهی اولیه variableAssignments (consolidated)
                foreach ($variables as $code) {
                    if (!isset($this->variableAssignments[$code])) {
                        $this->variableAssignments[$code] = '';
                    }
                }
            }
        }
    }
    
    public function updatedVariableType()
    {
        // وقتی نوع متغیر تغییر کرد، فیلدهای جدول را بارگذاری می‌کنیم
        $this->loadTableFields();
    }
    
    public function loadTableFields()
    {
        $this->availableTableFields = [];
        $this->selectedTableField = '';
        
        // دریافت تمام جدول‌های ثبت شده در "نام گذاری جدول‌ها"
        $registeredTables = TableName::where('is_visible', true)
            ->orderBy('name')
            ->get();
        
        // اگر جدولی ثبت نشده باشد، از جدول‌های پیش‌فرض استفاده می‌کنیم
        if ($registeredTables->isEmpty()) {
            // استفاده از منطق قبلی برای backward compatibility
            $this->loadDefaultTableFields();
            return;
        }
        
        // برای هر جدول ثبت شده، فیلدهای آن را می‌خوانیم و جداگانه نمایش می‌دهیم
        foreach ($registeredTables as $tableName) {
            $tableNameStr = $tableName->table_name;
            $tableDisplayName = $tableName->name;
            
            if (Schema::hasTable($tableNameStr)) {
                try {
                    $columns = Schema::getColumnListing($tableNameStr);
                    $tableFields = [];
                    
                    foreach ($columns as $column) {
                        // حذف فیلدهای سیستمی
                        if (!in_array($column, ['id', 'created_at', 'updated_at'])) {
                            $tableFields[] = [
                                'name' => $column,
                                'label' => $this->getFieldLabel($column),
                                'table_name' => $tableNameStr,
                                'table_display_name' => $tableDisplayName,
                            ];
                        }
                    }
                    
                    // اضافه کردن فیلدهای این جدول به لیست اصلی
                    $this->availableTableFields = array_merge($this->availableTableFields, $tableFields);
                    
                } catch (\Exception $e) {
                    \Log::error('Error loading table fields', [
                        'table_name' => $tableNameStr,
                        'error' => $e->getMessage(),
                    ]);
                    // در صورت خطا، ادامه می‌دهیم
                }
            }
        }
        
        // اگر فیلدی پیدا نشد، از فیلدهای پیش‌فرض استفاده می‌کنیم
        if (empty($this->availableTableFields)) {
            $this->loadDefaultTableFields();
        }
        
        // تعیین نام جدول پیش‌فرض بر اساس نوع متغیر
        if ($this->variable_type === 'report') {
            $this->table_name = 'reports';
        } elseif ($this->variable_type === 'user') {
            $this->table_name = 'residents';
        } else {
            $this->table_name = '';
        }
    }
    
    /**
     * بارگذاری فیلدهای پیش‌فرض (برای backward compatibility)
     */
    protected function loadDefaultTableFields()
    {
        if ($this->variable_type === 'report') {
            $defaultFields = [
                'title' => 'عنوان گزارش',
                'description' => 'توضیحات گزارش',
                'negative_score' => 'امتیاز منفی',
                'increase_coefficient' => 'ضریب افزایش',
                'category.name' => 'نام دسته‌بندی',
            ];
            
            foreach ($defaultFields as $name => $label) {
                $this->availableTableFields[] = [
                    'name' => $name,
                    'label' => $label,
                    'table_name' => 'reports',
                    'table_display_name' => 'گزارش‌ها',
                ];
            }
        } elseif ($this->variable_type === 'user') {
            // برای کاربر، فیلدهای API residents را می‌خوانیم
            try {
                $response = Http::timeout(10)->get('http://atlas2.test/api/residents');
                if ($response->successful()) {
                    $units = $response->json();
                    $fields = [];
                    
                    // استخراج فیلدهای ممکن از ساختار API
                    if (!empty($units)) {
                        $firstUnit = $units[0];
                        
                        // فیلدهای unit
                        if (isset($firstUnit['unit'])) {
                            foreach (array_keys($firstUnit['unit']) as $key) {
                                $fields['unit_' . $key] = 'واحد - ' . $this->getFieldLabel($key);
                            }
                        }
                        
                        // فیلدهای room
                        if (isset($firstUnit['rooms'][0])) {
                            foreach (array_keys($firstUnit['rooms'][0]) as $key) {
                                if ($key !== 'beds') {
                                    $fields['room_' . $key] = 'اتاق - ' . $this->getFieldLabel($key);
                                }
                            }
                        }
                        
                        // فیلدهای bed
                        if (isset($firstUnit['rooms'][0]['beds'][0])) {
                            foreach (array_keys($firstUnit['rooms'][0]['beds'][0]) as $key) {
                                if ($key !== 'resident') {
                                    $fields['bed_' . $key] = 'تخت - ' . $this->getFieldLabel($key);
                                }
                            }
                        }
                        
                        // فیلدهای resident
                        if (isset($firstUnit['rooms'][0]['beds'][0]['resident'])) {
                            foreach (array_keys($firstUnit['rooms'][0]['beds'][0]['resident']) as $key) {
                                $fields[$key] = $this->getFieldLabel($key);
                            }
                        }
                    }
                    
                    foreach ($fields as $name => $label) {
                        $this->availableTableFields[] = [
                            'name' => $name,
                            'label' => $label,
                            'table_name' => 'residents',
                            'table_display_name' => 'اقامت‌گران',
                        ];
                    }
                }
            } catch (\Exception $e) {
                // در صورت خطا، فیلدهای پیش‌فرض را اضافه می‌کنیم
                $defaultFields = [
                    'full_name' => 'نام کامل',
                    'name' => 'نام',
                    'phone' => 'شماره تلفن',
                    'national_id' => 'کد ملی',
                    'national_code' => 'کد ملی',
                    'unit_name' => 'نام واحد',
                    'unit_code' => 'کد واحد',
                    'room_name' => 'نام اتاق',
                    'bed_name' => 'نام تخت',
                    'start_date' => 'تاریخ شروع قرارداد',
                    'end_date' => 'تاریخ پایان قرارداد',
                    'expiry_date' => 'تاریخ سررسید',
                    'contract_start_date' => 'تاریخ شروع قرارداد',
                    'contract_end_date' => 'تاریخ پایان قرارداد',
                    'contract_expiry_date' => 'تاریخ سررسید',
                ];
                
                foreach ($defaultFields as $name => $label) {
                    $this->availableTableFields[] = [
                        'name' => $name,
                        'label' => $label,
                        'table_name' => 'residents',
                        'table_display_name' => 'اقامت‌گران',
                    ];
                }
            }
        } else {
            // برای عمومی، فیلدهای خاصی نداریم
            $this->availableTableFields = [
                [
                    'name' => 'today',
                    'label' => 'تاریخ امروز',
                    'table_name' => '',
                    'table_display_name' => '',
                ],
            ];
        }
    }
    
    public function getFieldLabel($fieldName)
    {
        $labels = [
            'title' => 'عنوان',
            'description' => 'توضیحات',
            'name' => 'نام',
            'full_name' => 'نام کامل',
            'phone' => 'شماره تلفن',
            'national_id' => 'کد ملی',
            'national_code' => 'کد ملی',
            'negative_score' => 'امتیاز منفی',
            'increase_coefficient' => 'ضریب افزایش',
            'out_sms' => 'ارسال پیام خودکار',
            'category_id' => 'شناسه دسته‌بندی',
            'type' => 'نوع',
            'unit_name' => 'نام واحد',
            'unit_code' => 'کد واحد',
            'room_name' => 'نام اتاق',
            'bed_name' => 'نام تخت',
            'contract_start_date' => 'تاریخ شروع قرارداد',
            'contract_end_date' => 'تاریخ پایان قرارداد',
            'contract_expiry_date' => 'تاریخ سررسید',
        ];
        
        // اگر label در دیکشنری وجود داشت، آن را برمی‌گردانیم
        if (isset($labels[$fieldName])) {
            return $labels[$fieldName];
        }
        
        // در غیر این صورت، تبدیل snake_case به label فارسی
        $label = str_replace('_', ' ', $fieldName);
        $label = ucwords($label);
        
        return $label;
    }
    
    public function selectTableField($fieldName)
    {
        $this->table_field = $fieldName;
        $this->selectedTableField = $fieldName;
        
        // پیدا کردن فیلد انتخاب شده و تنظیم table_name و title
        foreach ($this->availableTableFields as $field) {
            if ($field['name'] === $fieldName) {
                // تنظیم table_name اگر وجود داشته باشد
                if (isset($field['table_name']) && !empty($field['table_name'])) {
                    $this->table_name = $field['table_name'];
                }
                
                // اگر title خالی است، از label استفاده می‌کنیم
                if (empty($this->title)) {
                    $this->title = $field['label'];
                }
                break;
            }
        }
    }

    public function getPatternCode()
    {
        // اگر فقط یک الگو انتخاب شده، کد آن را برمی‌گردان
        if (count($this->selectedPatterns) === 1) {
            $patternId = $this->selectedPatterns[0];
            $pattern = Pattern::find($patternId);
            if ($pattern) {
                return $pattern->pattern_code;
            }
        }
        
        // در غیر این صورت، خالی برمی‌گردان
        return '';
    }

    public function createVariable()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            
            // ایجاد متغیر اصلی
            $variable = PatternVariable::create([
                'code' => 'auto_generated', // کد خودکار، چون ما از کدهای اختصاصی استفاده می‌کنیم
                'title' => $this->title,
                'pattern_code' => $this->getPatternCode(), // ذخیره کد الگوی اصلی
                'table_name' => $this->table_name ?: null,
                'variable_type' => $this->variable_type,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);

            // ایجاد اتصالات به الگوها با متغیرهای اختصاصی
            foreach ($this->selectedPatterns as $patternId) {
                $pattern = Pattern::find($patternId);
                if ($pattern) {
                    // استخراج متغیرها از متن الگو
                    preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
                    $variables = [];
                    if (!empty($matches[0])) {
                        foreach ($matches[0] as $code) {
                            $variables[] = $code;
                        }
                    }
                    
                    // ایجاد اتصال برای هر متغیر در این الگو
                    foreach ($variables as $variableCode) {
                        $tableField = $this->variableAssignments[$variableCode] ?? '';
                        if (!empty($tableField)) {
                            // بررسی تکراری نبودن کد متغیر برای این الگو
                            if (DB::table('pattern_pattern_variables')
                                ->where('pattern_id', $patternId)
                                ->where('variable_code', $variableCode)
                                ->exists()) {
                                $this->addError('variableAssignments.' . $variableCode, "کد متغیر {$variableCode} برای این الگو قبلاً استفاده شده است");
                                DB::rollBack();
                                return;
                            }
                            
                            // پیدا کردن آخرین sort_order برای این الگو
                            $lastSortOrder = DB::table('pattern_pattern_variables')
                                ->where('pattern_id', $patternId)
                                ->max('sort_order') ?? 0;
                            
                            DB::table('pattern_pattern_variables')->insert([
                                'pattern_id' => $patternId,
                                'pattern_variable_id' => $variable->id,
                                'variable_code' => $variableCode,
                                'table_field' => $tableField, // ذخیره فیلد جدول در کد متغیر
                                'sort_order' => $lastSortOrder + 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'متغیر با موفقیت ایجاد شد.'
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ایجاد متغیر: ' . $e->getMessage()
            ]);
        }
    }

    public function updateVariable()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            
            $variable = PatternVariable::findOrFail($this->editingId);
            
            // به‌روزرسانی متغیر اصلی
            $variable->update([
                'title' => $this->title,
                'pattern_code' => $this->getPatternCode(), // ذخیره کد الگوی اصلی
                'table_name' => $this->table_name ?: null,
                'variable_type' => $this->variable_type,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);

            // حذف اتصالات قبلی به الگوها
            DB::table('pattern_pattern_variables')
                ->where('pattern_variable_id', $this->editingId)
                ->delete();

            // ایجاد اتصالات جدید به الگوها با متغیرهای اختصاصی
            foreach ($this->selectedPatterns as $patternId) {
                $pattern = Pattern::find($patternId);
                if ($pattern) {
                    // استخراج متغیرها از متن الگو
                    preg_match_all('/\{(\d+)\}/', $pattern->text, $matches);
                    $variables = [];
                    if (!empty($matches[0])) {
                        foreach ($matches[0] as $code) {
                            $variables[] = $code;
                        }
                    }
                    
                    // ایجاد اتصال برای هر متغیر در این الگو
                    foreach ($variables as $variableCode) {
                        $tableField = $this->variableAssignments[$variableCode] ?? '';
                        if (!empty($tableField)) {
                            // بررسی تکراری نبودن کد متغیر برای این الگو
                            if (DB::table('pattern_pattern_variables')
                                ->where('pattern_id', $patternId)
                                ->where('variable_code', $variableCode)
                                ->where('pattern_variable_id', '!=', $this->editingId)
                                ->exists()) {
                                $this->addError('variableAssignments.' . $variableCode, "کد متغیر {$variableCode} برای این الگو قبلاً استفاده شده است");
                                DB::rollBack();
                                return;
                            }
                            
                            // پیدا کردن آخرین sort_order برای این الگو
                            $lastSortOrder = DB::table('pattern_pattern_variables')
                                ->where('pattern_id', $patternId)
                                ->max('sort_order') ?? 0;
                            
                            DB::table('pattern_pattern_variables')->insert([
                                'pattern_id' => $patternId,
                                'pattern_variable_id' => $this->editingId,
                                'variable_code' => $variableCode,
                                'table_field' => $tableField, // ذخیره فیلد جدول در کد متغیر
                                'sort_order' => $lastSortOrder + 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'متغیر با موفقیت به‌روزرسانی شد.'
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در به‌روزرسانی متغیر: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteVariable($id)
    {
        try {
            DB::beginTransaction();
            
            // حذف اتصالات به الگوها
            DB::table('pattern_pattern_variables')
                ->where('pattern_variable_id', $id)
                ->delete();
            
            // حذف متغیر اصلی
            $variable = PatternVariable::findOrFail($id);
            $variable->delete();

            DB::commit();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'متغیر با موفقیت حذف شد.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در حذف متغیر: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleActive($id)
    {
        try {
            $variable = PatternVariable::findOrFail($id);
            $variable->update(['is_active' => !$variable->is_active]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'وضعیت متغیر با موفقیت تغییر کرد.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در تغییر وضعیت: ' . $e->getMessage()
            ]);
        }
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getVariablesQueryProperty()
    {
        return PatternVariable::when($this->search, function ($query) {
            $query->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%')
                ->orWhere('table_field', 'like', '%' . $this->search . '%')
                ->orWhere('table_name', 'like', '%' . $this->search . '%');
        })
        ->when($this->typeFilter, function ($query) {
            $query->where('variable_type', $this->typeFilter);
        })
        ->orderBy($this->sortBy, $this->sortDirection);
    }

    public function render()
    {
        $variables = $this->variablesQuery->paginate($this->perPage);

        return view('livewire.variables.index', compact('variables'));
    }
}
