<?php

namespace App\Livewire\TableNames;

use Livewire\Component;
use App\Models\TableName;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $showModal = false;
    public $editingId = null;
    
    // فرم
    public $name = '';
    public $table_name = '';
    public $is_visible = true;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'table_name' => 'required|string|max:255',
            'is_visible' => 'boolean',
        ];

        if ($this->editingId) {
            $rules['table_name'] .= '|unique:table_names,table_name,' . $this->editingId;
        } else {
            $rules['table_name'] .= '|unique:table_names,table_name';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'نام الزامی است.',
        'table_name.required' => 'نام جدول الزامی است.',
        'table_name.unique' => 'این نام جدول قبلاً استفاده شده است.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->table_name = '';
        $this->is_visible = true;
        $this->showModal = false;
        $this->resetValidation();
    }

    public function openModal($id = null)
    {
        if ($id) {
            $tableName = TableName::findOrFail($id);
            $this->editingId = $id;
            $this->name = $tableName->name;
            $this->table_name = $tableName->table_name;
            $this->is_visible = $tableName->is_visible;
        } else {
            $this->resetForm();
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
            'name' => $this->name,
            'table_name' => $this->table_name,
            'is_visible' => $this->is_visible,
        ];

        if ($this->editingId) {
            $tableName = TableName::findOrFail($this->editingId);
            $tableName->update($data);
            $message = 'نام جدول با موفقیت به‌روزرسانی شد.';
        } else {
            TableName::create($data);
            $message = 'نام جدول با موفقیت ایجاد شد.';
        }

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => $message,
            'duration' => 3000,
        ]);

        $this->resetForm();
        $this->resetPage();
    }

    public function delete($id)
    {
        $tableName = TableName::findOrFail($id);
        $tableName->delete();

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => 'نام جدول با موفقیت حذف شد.',
            'duration' => 3000,
        ]);

        $this->resetPage();
    }

    /**
     * دریافت تمام جداول موجود در دیتابیس
     */
    public function getAvailableTablesProperty()
    {
        try {
            $tables = [];
            $databaseName = DB::getDatabaseName();
            
            // دریافت لیست جداول از دیتابیس
            $tableList = DB::select("SHOW TABLES");
            $key = "Tables_in_{$databaseName}";
            
            foreach ($tableList as $table) {
                $tableName = $table->$key;
                // حذف جداول migration
                if (strpos($tableName, 'migrations') !== 0) {
                    $tables[] = $tableName;
                }
            }
            
            sort($tables);
            
            // حذف جداولی که قبلاً ثبت شده‌اند (فقط در حالت ایجاد جدید)
            if (!$this->editingId) {
                $registeredTables = TableName::pluck('table_name')->toArray();
                $tables = array_diff($tables, $registeredTables);
            }
            
            return $tables;
        } catch (\Exception $e) {
            \Log::error('Error getting database tables', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * وقتی جدول انتخاب شد، نام نمایشی را به صورت خودکار پر کن
     */
    public function updatedTableName()
    {
        if ($this->table_name && !$this->name) {
            // تبدیل نام جدول به نام نمایشی (مثلاً: residents -> اقامت‌گران)
            $this->name = $this->convertTableNameToDisplayName($this->table_name);
        }
    }
    
    /**
     * تبدیل نام جدول به نام نمایشی
     */
    protected function convertTableNameToDisplayName($tableName)
    {
        $names = [
            'residents' => 'اقامت‌گران',
            'reports' => 'گزارش‌ها',
            'resident_reports' => 'گزارش‌های اقامت‌گران',
            'categories' => 'دسته‌بندی‌ها',
            'patterns' => 'الگوها',
            'pattern_variables' => 'متغیرهای الگو',
            'sms_messages' => 'پیام‌های SMS',
            'sms_message_residents' => 'پیام‌های SMS اقامت‌گران',
            'auto_sms' => 'پیامک‌های خودکار',
            'auto_sms_conditions' => 'شرط‌های پیامک خودکار',
            'constants' => 'ثابت‌ها',
            'table_names' => 'نام گذاری جداول',
            'blacklists' => 'لیست‌های سیاه',
            'sender_numbers' => 'شماره‌های فرستنده',
        ];
        
        return $names[$tableName] ?? ucfirst(str_replace('_', ' ', $tableName));
    }

    public function render()
    {
        $query = TableName::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('table_name', 'like', '%' . $this->search . '%');
            });
        }

        $tableNames = $query->orderBy('name')->paginate($this->perPage);

        return view('livewire.table-names.index', [
            'tableNames' => $tableNames,
        ]);
    }
}
