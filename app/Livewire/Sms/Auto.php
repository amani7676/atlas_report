<?php

namespace App\Livewire\Sms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AutoSms;
use App\Models\AutoSmsCondition;
use App\Models\Pattern;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Auto extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    
    // Modal properties
    public $showModal = false;
    public $editingId = null;
    
    // Form properties
    public $title = '';
    public $pattern_id = '';
    public $send_type = 'immediate';
    public $scheduled_at = '';
    public $is_active = true;
    public $description = '';
    public $related_tables = [];
    
    // Condition properties
    public $conditions = [];
    public $condition_type = '';
    public $condition_field_type = '';
    public $condition_field_name = '';
    public $condition_operator = '=';
    public $condition_value = '';
    public $condition_logical_operator = 'AND';
    public $condition_order = 0;
    public $condition_data_type = 'string';
    public $editingConditionId = null;
    
    // Condition modal
    public $showConditionModal = false;
    public $currentAutoSmsId = null;
    
    // Available tables and fields
    public $availableTables = [];
    public $availableFields = [];
    
    protected $rules = [
        'title' => 'required|string|max:255',
        'pattern_id' => 'required|exists:patterns,id',
        'send_type' => 'required|in:immediate,scheduled',
        'scheduled_at' => 'nullable|required_if:send_type,scheduled|date',
        'is_active' => 'boolean',
        'description' => 'nullable|string',
        'related_tables' => 'array',
    ];

    public function mount()
    {
        $this->initializeAvailableTables();
        $this->initializeAvailableFields();
    }

    public function initializeAvailableTables()
    {
        $this->availableTables = [
            'resident' => 'اقامت‌گر',
            'resident_report' => 'گزارش اقامت‌گر',
            'report' => 'گزارش',
        ];
    }

    public function initializeAvailableFields()
    {
        $this->availableFields = [
            'resident' => [
                'full_name' => 'نام کامل',
                'phone' => 'شماره تلفن',
                'national_id' => 'کد ملی',
                'start_date' => 'تاریخ شروع قرارداد',
                'end_date' => 'تاریخ پایان قرارداد',
                'expiry_date' => 'تاریخ انقضای قرارداد',
                // برای backward compatibility
                'contract_start_date' => 'تاریخ شروع قرارداد',
                'contract_end_date' => 'تاریخ پایان قرارداد',
                'contract_expiry_date' => 'تاریخ انقضای قرارداد',
            ],
            'resident_report' => [
                'report_count' => 'تعداد گزارش',
                'total_score' => 'امتیاز کل',
                'last_report_date' => 'تاریخ آخرین گزارش',
            ],
            'report' => [
                'title' => 'عنوان',
                'description' => 'توضیحات',
                'negative_score' => 'امتیاز منفی',
                'type' => 'نوع',
            ],
        ];
    }

    public function getFilteredFieldsProperty()
    {
        if (empty($this->condition_field_type) || !isset($this->availableFields[$this->condition_field_type])) {
            return [];
        }
        return $this->availableFields[$this->condition_field_type];
    }

    public function updatedConditionFieldType()
    {
        $this->condition_field_name = '';
        $this->condition_data_type = 'string';
        $this->updateConditionDataType();
    }

    public function updatedConditionFieldName()
    {
        $this->updateConditionDataType();
    }

    protected function updateConditionDataType()
    {
        if (empty($this->condition_field_name) || empty($this->condition_field_type)) {
            return;
        }

        // تعیین نوع داده بر اساس نام فیلد
        $dateFields = ['start_date', 'end_date', 'expiry_date', 'contract_start_date', 'contract_end_date', 'contract_expiry_date', 'last_report_date'];
        $numberFields = ['report_count', 'total_score', 'negative_score'];
        $booleanFields = ['is_active'];

        if (in_array($this->condition_field_name, $dateFields)) {
            $this->condition_data_type = 'date';
        } elseif (in_array($this->condition_field_name, $numberFields)) {
            $this->condition_data_type = 'number';
        } elseif (in_array($this->condition_field_name, $booleanFields)) {
            $this->condition_data_type = 'boolean';
        } else {
            $this->condition_data_type = 'string';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        if ($id) {
            $autoSms = AutoSms::with('conditions')->findOrFail($id);
            $this->editingId = $id;
            $this->title = $autoSms->title;
            $this->pattern_id = $autoSms->pattern_id;
            $this->send_type = $autoSms->send_type;
            $this->scheduled_at = $autoSms->scheduled_at ? $autoSms->scheduled_at->format('Y-m-d\TH:i') : '';
            $this->is_active = $autoSms->is_active;
            $this->description = $autoSms->description;
            $this->related_tables = $autoSms->related_tables ?? [];
            $this->conditions = $autoSms->conditions->map(function ($condition) {
                return [
                    'id' => $condition->id,
                    'condition_type' => $condition->condition_type,
                    'field_type' => $condition->field_type,
                    'field_name' => $condition->field_name,
                    'data_type' => $condition->data_type,
                    'operator' => $condition->operator,
                    'value' => $condition->value,
                    'logical_operator' => $condition->logical_operator,
                    'order' => $condition->order,
                ];
            })->toArray();
        } else {
            $this->resetForm();
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
        $this->editingId = null;
        $this->title = '';
        $this->pattern_id = '';
        $this->send_type = 'immediate';
        $this->scheduled_at = '';
        $this->is_active = true;
        $this->description = '';
        $this->related_tables = [];
        $this->conditions = [];
        $this->resetConditionForm();
    }

    public function resetConditionForm()
    {
        $this->condition_type = '';
        $this->condition_field_type = '';
        $this->condition_field_name = '';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->condition_logical_operator = 'AND';
        $this->condition_order = 0;
        $this->condition_data_type = 'string';
        $this->editingConditionId = null;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'pattern_id' => $this->pattern_id,
            'send_type' => $this->send_type,
            'scheduled_at' => $this->send_type === 'scheduled' && $this->scheduled_at ? $this->scheduled_at : null,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'related_tables' => $this->related_tables,
        ];

        if ($this->editingId) {
            $autoSms = AutoSms::findOrFail($this->editingId);
            $autoSms->update($data);
            
            // حذف شرط‌های قدیمی
            $autoSms->conditions()->delete();
        } else {
            $autoSms = AutoSms::create($data);
        }

        // ذخیره شرط‌ها
        foreach ($this->conditions as $condition) {
            AutoSmsCondition::create([
                'auto_sms_id' => $autoSms->id,
                'condition_type' => $condition['condition_type'],
                'field_type' => $condition['field_type'],
                'field_name' => $condition['field_name'],
                'data_type' => $condition['data_type'] ?? 'string',
                'operator' => $condition['operator'],
                'value' => $condition['value'],
                'logical_operator' => $condition['logical_operator'] ?? 'AND',
                'order' => $condition['order'] ?? 0,
            ]);
        }

        $this->closeModal();
        session()->flash('message', 'پیامک خودکار با موفقیت ذخیره شد.');
    }

    public function addCondition()
    {
        if (empty($this->condition_type) || empty($this->condition_field_type) || empty($this->condition_field_name) || empty($this->condition_value)) {
            session()->flash('error', 'لطفاً تمام فیلدهای شرط را پر کنید.');
            return;
        }

        $this->conditions[] = [
            'condition_type' => $this->condition_type,
            'field_type' => $this->condition_field_type,
            'field_name' => $this->condition_field_name,
            'data_type' => $this->condition_data_type,
            'operator' => $this->condition_operator,
            'value' => $this->condition_value,
            'logical_operator' => $this->condition_logical_operator,
            'order' => $this->condition_order,
        ];

        $this->resetConditionForm();
    }

    public function editCondition($index)
    {
        if (isset($this->conditions[$index])) {
            $condition = $this->conditions[$index];
            $this->condition_type = $condition['condition_type'];
            $this->condition_field_type = $condition['field_type'];
            $this->condition_field_name = $condition['field_name'];
            $this->condition_data_type = $condition['data_type'] ?? 'string';
            $this->condition_operator = $condition['operator'];
            $this->condition_value = $condition['value'];
            $this->condition_logical_operator = $condition['logical_operator'] ?? 'AND';
            $this->condition_order = $condition['order'] ?? 0;
            $this->editingConditionId = $index;
        }
    }

    public function updateCondition()
    {
        if ($this->editingConditionId !== null && isset($this->conditions[$this->editingConditionId])) {
            $this->conditions[$this->editingConditionId] = [
                'condition_type' => $this->condition_type,
                'field_type' => $this->condition_field_type,
                'field_name' => $this->condition_field_name,
                'data_type' => $this->condition_data_type,
                'operator' => $this->condition_operator,
                'value' => $this->condition_value,
                'logical_operator' => $this->condition_logical_operator,
                'order' => $this->condition_order,
            ];
            $this->resetConditionForm();
        }
    }

    public function cancelEditCondition()
    {
        $this->resetConditionForm();
    }

    public function removeCondition($index)
    {
        if (isset($this->conditions[$index])) {
            unset($this->conditions[$index]);
            $this->conditions = array_values($this->conditions);
        }
    }

    public function toggleActive($id)
    {
        $autoSms = AutoSms::findOrFail($id);
        $autoSms->update(['is_active' => !$autoSms->is_active]);
    }

    public function delete($id)
    {
        $autoSms = AutoSms::findOrFail($id);
        $autoSms->conditions()->delete();
        $autoSms->delete();
        session()->flash('message', 'پیامک خودکار با موفقیت حذف شد.');
    }

    public function openConditionModal($autoSmsId, $conditionId = null)
    {
        $this->currentAutoSmsId = $autoSmsId;
        $autoSms = AutoSms::findOrFail($autoSmsId);
        $this->related_tables = $autoSms->related_tables ?? [];
        
        if ($conditionId) {
            $condition = AutoSmsCondition::findOrFail($conditionId);
            $this->editingConditionId = $conditionId;
            $this->condition_type = $condition->condition_type;
            $this->condition_field_type = $condition->field_type;
            $this->condition_field_name = $condition->field_name;
            $this->condition_data_type = $condition->data_type ?? 'string';
            $this->condition_operator = $condition->operator;
            $this->condition_value = $condition->value;
            $this->condition_logical_operator = $condition->logical_operator ?? 'AND';
            $this->condition_order = $condition->order ?? 0;
        } else {
            $this->resetConditionForm();
        }
        
        $this->showConditionModal = true;
    }

    public function closeConditionModal()
    {
        $this->showConditionModal = false;
        $this->currentAutoSmsId = null;
        $this->resetConditionForm();
    }

    public function saveCondition()
    {
        if (empty($this->condition_field_type) || empty($this->condition_field_name) || empty($this->condition_value)) {
            session()->flash('error', 'لطفاً تمام فیلدهای شرط را پر کنید.');
            return;
        }

        $data = [
            'auto_sms_id' => $this->currentAutoSmsId,
            'condition_type' => $this->condition_type ?: 'inter',
            'field_type' => $this->condition_field_type,
            'field_name' => $this->condition_field_name,
            'data_type' => $this->condition_data_type,
            'operator' => $this->condition_operator,
            'value' => $this->condition_value,
            'logical_operator' => $this->condition_logical_operator,
            'order' => $this->condition_order,
        ];

        if ($this->editingConditionId) {
            $condition = AutoSmsCondition::findOrFail($this->editingConditionId);
            $condition->update($data);
        } else {
            AutoSmsCondition::create($data);
        }

        $this->closeConditionModal();
        session()->flash('message', 'شرط با موفقیت ذخیره شد.');
    }

    public function deleteCondition($conditionId)
    {
        AutoSmsCondition::findOrFail($conditionId)->delete();
        session()->flash('message', 'شرط با موفقیت حذف شد.');
    }

    public function getPatternsProperty()
    {
        return Pattern::where('is_active', true)
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
    }

    public function getAutoSmsListProperty()
    {
        $query = AutoSms::with(['pattern', 'conditions'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('pattern', function ($q) {
                      $q->where('title', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return $query->paginate(20);
    }

    public function render()
    {
        return view('livewire.sms.auto', [
            'autoSmsList' => $this->autoSmsList,
            'patterns' => $this->patterns,
        ]);
    }
}



