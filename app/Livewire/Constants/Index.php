<?php

namespace App\Livewire\Constants;

use Livewire\Component;
use App\Models\Constant;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $showModal = false;
    public $editingId = null;
    
    // فرم
    public $key = '';
    public $value = '';
    public $type = 'string';
    public $enum_values = '';
    public $description = '';

    protected function rules()
    {
        $rules = [
            'value' => 'required',
            'type' => 'required|in:string,number,date,enum',
            'enum_values' => 'required_if:type,enum',
            'description' => 'nullable|string',
        ];

        if ($this->editingId) {
            $rules['key'] = 'required|string|max:255|unique:constants,key,' . $this->editingId;
        } else {
            $rules['key'] = 'required|string|max:255|unique:constants,key';
        }

        return $rules;
    }

    protected $messages = [
        'key.required' => 'کلید الزامی است.',
        'key.unique' => 'این کلید قبلاً استفاده شده است.',
        'value.required' => 'مقدار الزامی است.',
        'type.required' => 'نوع داده الزامی است.',
        'enum_values.required_if' => 'برای نوع enum، مقادیر الزامی است.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->key = '';
        $this->value = '';
        $this->type = 'string';
        $this->enum_values = '';
        $this->description = '';
        $this->showModal = false;
        $this->resetValidation();
    }

    public function openModal($id = null)
    {
        if ($id) {
            $constant = Constant::findOrFail($id);
            $this->editingId = $id;
            $this->key = $constant->key;
            $this->value = $constant->value;
            $this->type = $constant->type;
            $this->enum_values = is_array($constant->enum_values) 
                ? implode(',', $constant->enum_values) 
                : ($constant->enum_values ?? '');
            $this->description = $constant->description ?? '';
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

        // اعتبارسنجی مقدار بر اساس نوع
        if ($this->type === 'number' && !is_numeric($this->value)) {
            $this->addError('value', 'مقدار باید عددی باشد.');
            return;
        }

        if ($this->type === 'date') {
            try {
                \Carbon\Carbon::parse($this->value);
            } catch (\Exception $e) {
                $this->addError('value', 'فرمت تاریخ نامعتبر است.');
                return;
            }
        }

        if ($this->type === 'enum') {
            $enumArray = array_map('trim', explode(',', $this->enum_values));
            if (empty($enumArray)) {
                $this->addError('enum_values', 'حداقل یک مقدار برای enum الزامی است.');
                return;
            }
            if (!in_array($this->value, $enumArray)) {
                $this->addError('value', 'مقدار باید یکی از مقادیر enum باشد.');
                return;
            }
        }

        $data = [
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description,
        ];

        if ($this->type === 'enum') {
            $data['enum_values'] = array_map('trim', explode(',', $this->enum_values));
        }

        if ($this->editingId) {
            $constant = Constant::findOrFail($this->editingId);
            $constant->update($data);
            $message = 'ثابت با موفقیت به‌روزرسانی شد.';
        } else {
            Constant::create($data);
            $message = 'ثابت با موفقیت ایجاد شد.';
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
        $constant = Constant::findOrFail($id);
        $constant->delete();

        $this->dispatch('showToast', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'message' => 'ثابت با موفقیت حذف شد.',
            'duration' => 3000,
        ]);

        $this->resetPage();
    }

    public function updatedType()
    {
        // وقتی نوع تغییر می‌کند، مقدار را پاک می‌کنیم
        $this->value = '';
        $this->enum_values = '';
    }

    public function render()
    {
        $query = Constant::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('key', 'like', '%' . $this->search . '%')
                  ->orWhere('value', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $constants = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.constants.index', [
            'constants' => $constants,
        ]);
    }
}

