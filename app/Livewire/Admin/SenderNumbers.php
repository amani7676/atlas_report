<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\SenderNumber;
use Illuminate\Support\Facades\Log;

class SenderNumbers extends Component
{
    public $senderNumbers = [];
    public $showModal = false;
    public $editingId = null;
    public $form = [
        'number' => '',
        'title' => '',
        'description' => '',
        'api_key' => '',
        'is_active' => true,
        'is_pattern' => false,
        'priority' => 0,
    ];

    protected $rules = [
        'form.number' => 'required|string|max:20|unique:sender_numbers,number',
        'form.title' => 'required|string|max:255',
        'form.description' => 'nullable|string',
        'form.api_key' => 'nullable|string|max:255',
        'form.is_active' => 'boolean',
        'form.is_pattern' => 'boolean',
        'form.priority' => 'integer|min:0',
    ];

    protected $messages = [
        'form.number.required' => 'شماره فرستنده الزامی است.',
        'form.number.unique' => 'این شماره قبلاً ثبت شده است.',
        'form.title.required' => 'عنوان الزامی است.',
    ];

    public function mount()
    {
        $this->loadSenderNumbers();
    }

    public function loadSenderNumbers()
    {
        $this->senderNumbers = SenderNumber::orderBy('is_pattern', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('title')
            ->get();
    }

    public function openModal($id = null)
    {
        $this->editingId = $id;
        $this->resetForm();
        
        if ($id) {
            $senderNumber = SenderNumber::find($id);
            if ($senderNumber) {
                $this->form = [
                    'number' => $senderNumber->number,
                    'title' => $senderNumber->title,
                    'description' => $senderNumber->description ?? '',
                    'api_key' => $senderNumber->api_key ?? '',
                    'is_active' => $senderNumber->is_active,
                    'is_pattern' => $senderNumber->is_pattern,
                    'priority' => $senderNumber->priority,
                ];
            }
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'number' => '',
            'title' => '',
            'description' => '',
            'api_key' => '',
            'is_active' => true,
            'is_pattern' => false,
            'priority' => 0,
        ];
        $this->resetErrorBag();
    }

    public function save()
    {
        // اگر در حال ویرایش هستیم، unique rule را تغییر می‌دهیم
        if ($this->editingId) {
            $this->rules['form.number'] = 'required|string|max:20|unique:sender_numbers,number,' . $this->editingId;
        }

        $this->validate();

        try {
            if ($this->editingId) {
                $senderNumber = SenderNumber::find($this->editingId);
                $senderNumber->update($this->form);
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'شماره فرستنده با موفقیت به‌روزرسانی شد.',
                ]);
            } else {
                SenderNumber::create($this->form);
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'شماره فرستنده با موفقیت ثبت شد.',
                ]);
            }

            $this->closeModal();
            $this->loadSenderNumbers();
        } catch (\Exception $e) {
            Log::error('Error saving sender number', [
                'error' => $e->getMessage(),
                'form' => $this->form,
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ذخیره شماره فرستنده: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $senderNumber = SenderNumber::find($id);
            if ($senderNumber) {
                $senderNumber->delete();
                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'شماره فرستنده با موفقیت حذف شد.',
                ]);
                $this->loadSenderNumbers();
            }
        } catch (\Exception $e) {
            Log::error('Error deleting sender number', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در حذف شماره فرستنده: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleActive($id)
    {
        try {
            $senderNumber = SenderNumber::find($id);
            if ($senderNumber) {
                $senderNumber->update([
                    'is_active' => !$senderNumber->is_active,
                ]);
                $this->loadSenderNumbers();
            }
        } catch (\Exception $e) {
            Log::error('Error toggling sender number active status', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.sender-numbers');
    }
}
