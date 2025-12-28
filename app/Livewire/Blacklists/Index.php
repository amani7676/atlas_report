<?php

namespace App\Livewire\Blacklists;

use Livewire\Component;
use App\Models\Blacklist;
use App\Services\MelipayamakService;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    
    // Modal states
    public $showModal = false;
    public $isEditing = false;
    public $editingId = null;
    
    // Form fields
    public $title = '';
    public $description = '';
    public $is_active = true;
    
    // API Response Modal
    public $showApiResponseModal = false;
    public $apiResponseData = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $blacklist = Blacklist::findOrFail($id);
        $this->editingId = $id;
        $this->title = $blacklist->title;
        $this->description = $blacklist->description ?? '';
        $this->is_active = $blacklist->is_active;
        $this->isEditing = true;
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
        $this->description = '';
        $this->is_active = true;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function createBlacklist()
    {
        $this->validate();

        try {
            // ایجاد لیست سیاه در API
            $melipayamakService = new MelipayamakService();
            $result = $melipayamakService->blackListAdd($this->title);

            if ($result['success']) {
                // ذخیره در دیتابیس
                $blacklist = Blacklist::create([
                    'title' => $this->title,
                    'blacklist_id' => $result['blacklist_id'],
                    'description' => $this->description,
                    'is_active' => $this->is_active,
                    'api_response' => is_string($result['api_response']) ? $result['api_response'] : json_encode($result['api_response']),
                    'http_status_code' => $result['http_status_code'] ?? null,
                ]);

                $this->dispatch('showAlert', [
                    'type' => 'success',
                    'title' => 'موفقیت!',
                    'text' => 'لیست سیاه با موفقیت ایجاد شد. کد 5 رقمی: ' . $result['blacklist_id']
                ]);

                $this->closeModal();
            } else {
                // ذخیره با خطا (برای بررسی بعدی)
                $blacklist = Blacklist::create([
                    'title' => $this->title,
                    'blacklist_id' => null,
                    'description' => $this->description,
                    'is_active' => false,
                    'api_response' => is_string($result['api_response']) ? $result['api_response'] : json_encode($result['api_response']),
                    'http_status_code' => $result['http_status_code'] ?? null,
                ]);

                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => $result['message'] ?? 'خطا در ایجاد لیست سیاه'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در ایجاد لیست سیاه: ' . $e->getMessage()
            ]);
        }
    }

    public function updateBlacklist()
    {
        $this->validate();

        try {
            $blacklist = Blacklist::findOrFail($this->editingId);
            $blacklist->update([
                'title' => $this->title,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'لیست سیاه با موفقیت به‌روزرسانی شد.'
            ]);

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در به‌روزرسانی لیست سیاه: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteBlacklist($id)
    {
        try {
            $blacklist = Blacklist::findOrFail($id);
            $blacklist->delete();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'لیست سیاه با موفقیت حذف شد.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'title' => 'خطا!',
                'text' => 'خطا در حذف لیست سیاه: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleActive($id)
    {
        try {
            $blacklist = Blacklist::findOrFail($id);
            $blacklist->update(['is_active' => !$blacklist->is_active]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'وضعیت لیست سیاه با موفقیت تغییر کرد.'
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

    public function showApiResponse($id)
    {
        $blacklist = Blacklist::findOrFail($id);
        $this->apiResponseData = [
            'title' => $blacklist->title,
            'blacklist_id' => $blacklist->blacklist_id,
            'api_response' => $blacklist->api_response,
            'http_status_code' => $blacklist->http_status_code,
            'created_at' => $blacklist->created_at,
        ];
        $this->showApiResponseModal = true;
    }

    public function closeApiResponseModal()
    {
        $this->showApiResponseModal = false;
        $this->apiResponseData = null;
    }

    public function getBlacklistsQueryProperty()
    {
        return Blacklist::when($this->search, function ($query) {
            $query->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('blacklist_id', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
        })
        ->orderBy($this->sortBy, $this->sortDirection);
    }

    public function render()
    {
        $blacklists = $this->blacklistsQuery->paginate($this->perPage);

        return view('livewire.blacklists.index', compact('blacklists'));
    }
}
