<?php

namespace App\Livewire\Variables;

use Livewire\Component;
use App\Models\PatternVariable;
use App\Models\TableName;
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
    public $table_field = '';
    public $table_name = '';
    public $variable_type = 'user';
    public $description = '';
    public $is_active = true;
    public $sort_order = 0;
    
    // Table fields
    public $availableTableFields = [];
    public $selectedTableField = '';

    protected $rules = [
        'code' => 'required|string|max:50|regex:/^\{\d+\}$/',
        'title' => 'required|string|max:255',
        'table_field' => 'required|string|max:255',
        'table_name' => 'nullable|string|max:255',
        'variable_type' => 'required|in:user,report,general',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    protected $messages = [
        'code.regex' => 'کد باید به فرمت {0}, {1}, {2} و ... باشد',
    ];

    public function mount()
    {
        // اگر از route /variables/create آمده‌ایم، مودال ایجاد را باز می‌کنیم
        if (request()->is('variables/create*')) {
            $this->openCreateModal();
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->loadTableFields(); // بارگذاری فیلدهای جدول
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $variable = PatternVariable::findOrFail($id);
        $this->editingId = $id;
        $this->code = $variable->code;
        $this->title = $variable->title;
        $this->table_field = $variable->table_field;
        $this->table_name = $variable->table_name ?? '';
        $this->variable_type = $variable->variable_type;
        $this->description = $variable->description ?? '';
        $this->is_active = $variable->is_active;
        $this->sort_order = $variable->sort_order;
        $this->isEditing = true;
        $this->loadTableFields(); // بارگذاری فیلدهای جدول
        $this->selectedTableField = $variable->table_field;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->code = '';
        $this->title = '';
        $this->table_field = '';
        $this->table_name = '';
        $this->variable_type = 'user';
        $this->description = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->editingId = null;
        $this->availableTableFields = [];
        $this->selectedTableField = '';
        $this->resetValidation();
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
        
        // برای هر جدول ثبت شده، فیلدهای آن را می‌خوانیم
        foreach ($registeredTables as $tableName) {
            $tableNameStr = $tableName->table_name;
            $tableDisplayName = $tableName->name;
            
            if (Schema::hasTable($tableNameStr)) {
                try {
                    $columns = Schema::getColumnListing($tableNameStr);
                    foreach ($columns as $column) {
                        // حذف فیلدهای سیستمی
                        if (!in_array($column, ['id', 'created_at', 'updated_at'])) {
                            // برای فیلدهای مستقیم جدول
                            $this->availableTableFields[] = [
                                'name' => $column,
                                'label' => $tableDisplayName . ' - ' . $this->getFieldLabel($column),
                                'table_name' => $tableNameStr,
                                'table_display_name' => $tableDisplayName,
                            ];
                            
                            // اگر جدول دارای رابطه با جدول دیگر است (مثلاً category_id)، فیلدهای آن را هم اضافه می‌کنیم
                            // این منطق برای جدول‌های خاص مثل reports که با categories رابطه دارد
                            if ($tableNameStr === 'reports' && $column === 'category_id' && Schema::hasTable('categories')) {
                                try {
                                    $categoryColumns = Schema::getColumnListing('categories');
                                    foreach ($categoryColumns as $catColumn) {
                                        if (!in_array($catColumn, ['id', 'created_at', 'updated_at'])) {
                                            $this->availableTableFields[] = [
                                                'name' => 'category.' . $catColumn,
                                                'label' => $tableDisplayName . ' - دسته‌بندی - ' . $this->getFieldLabel($catColumn),
                                                'table_name' => 'categories',
                                                'table_display_name' => 'دسته‌بندی‌ها',
                                            ];
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // در صورت خطا، ادامه می‌دهیم
                                }
                            }
                        }
                    }
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

    public function generateNextCode()
    {
        // پیدا کردن آخرین کد استفاده شده
        $lastVariable = PatternVariable::orderBy('sort_order', 'desc')->first();
        if ($lastVariable) {
            // استخراج عدد از کد
            preg_match('/\{(\d+)\}/', $lastVariable->code, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : -1;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 0;
        }
        
        $this->code = '{' . $nextNumber . '}';
    }

    public function createVariable()
    {
        $this->validate();

        try {
            // بررسی تکراری نبودن کد
            if (PatternVariable::where('code', $this->code)->exists()) {
                $this->addError('code', 'این کد قبلاً استفاده شده است');
                return;
            }

            PatternVariable::create([
                'code' => $this->code,
                'title' => $this->title,
                'table_field' => $this->table_field,
                'table_name' => $this->table_name ?: null,
                'variable_type' => $this->variable_type,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'متغیر با موفقیت ایجاد شد.'
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
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
            $variable = PatternVariable::findOrFail($this->editingId);
            
            // بررسی تکراری نبودن کد (به جز خودش)
            if (PatternVariable::where('code', $this->code)->where('id', '!=', $this->editingId)->exists()) {
                $this->addError('code', 'این کد قبلاً استفاده شده است');
                return;
            }

            $variable->update([
                'code' => $this->code,
                'title' => $this->title,
                'table_field' => $this->table_field,
                'table_name' => $this->table_name ?: null,
                'variable_type' => $this->variable_type,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
                'sort_order' => $this->sort_order,
            ]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'متغیر با موفقیت به‌روزرسانی شد.'
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
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
            $variable = PatternVariable::findOrFail($id);
            $variable->delete();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'متغیر با موفقیت حذف شد.'
            ]);
        } catch (\Exception $e) {
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
