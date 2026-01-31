<?php

namespace App\Livewire\Admin;

use App\Models\ApiKey;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ApiKeyManager extends Component
{
    public $password = '';
    public $isAuthenticated = false;
    public $apiKeys = [];
    public $editingKey = null;
    public $editKeyName = '';
    public $editKeyValue = '';
    public $editDescription = '';
    public $editIsActive = true;
    public $showingAddForm = false;
    public $newUsername = '';
    public $newApiKey = '';
    public $newDescription = '';
    public $newIsActive = true;
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'editKeyName' => 'required|string|max:255',
        'editKeyValue' => 'required|string',
        'editDescription' => 'nullable|string',
        'newUsername' => 'required|string',
        'newApiKey' => 'required|string',
        'newDescription' => 'nullable|string',
    ];

    public function mount()
    {
        // بررسی اینکه آیا کاربر قبلاً وارد شده است
        if (session('api_key_manager_authenticated')) {
            $this->isAuthenticated = true;
            $this->loadApiKeys();
        }
    }

    public function authenticate()
    {
        if ($this->password === '1') {
            $this->isAuthenticated = true;
            session(['api_key_manager_authenticated' => true]);
            $this->loadApiKeys();
            $this->password = '';
            $this->message = '';
        } else {
            $this->message = 'رمز عبور اشتباه است';
            $this->messageType = 'error';
        }
    }

    public function logout()
    {
        $this->isAuthenticated = false;
        session()->forget('api_key_manager_authenticated');
        $this->apiKeys = [];
        $this->resetForms();
    }

    public function loadApiKeys()
    {
        $this->apiKeys = ApiKey::orderBy('key_name')->get()->toArray();
    }

    public function startEdit($keyId)
    {
        $key = ApiKey::find($keyId);
        if ($key) {
            $this->editingKey = $keyId;
            $this->editKeyName = $key->key_name;
            $this->editKeyValue = $key->key_value;
            $this->editDescription = $key->description ?? '';
            $this->editIsActive = $key->is_active;
        }
    }

    public function cancelEdit()
    {
        $this->editingKey = null;
        $this->resetEditForm();
    }

    public function updateKey()
    {
        $this->validate([
            'editKeyName' => 'required|string|max:255',
            'editKeyValue' => 'required|string',
            'editDescription' => 'nullable|string',
        ]);

        $key = ApiKey::find($this->editingKey);
        if ($key) {
            // بررسی اینکه آیا نام کلید تغییر کرده و تکراری است
            if ($key->key_name !== $this->editKeyName) {
                $exists = ApiKey::where('key_name', $this->editKeyName)
                    ->where('id', '!=', $this->editingKey)
                    ->exists();
                if ($exists) {
                    $this->message = 'این نام کلید قبلاً استفاده شده است';
                    $this->messageType = 'error';
                    return;
                }
            }

            $key->update([
                'key_name' => $this->editKeyName,
                'key_value' => $this->editKeyValue,
                'description' => $this->editDescription,
                'is_active' => $this->editIsActive,
            ]);

            $this->message = 'API Key با موفقیت به‌روزرسانی شد';
            $this->messageType = 'success';
            $this->loadApiKeys();
            $this->cancelEdit();
        }
    }

    public function deleteKey($keyId)
    {
        $key = ApiKey::find($keyId);
        if ($key) {
            $key->delete();
            $this->message = 'API Key با موفقیت حذف شد';
            $this->messageType = 'success';
            $this->loadApiKeys();
        }
    }

    public function toggleActive($keyId)
    {
        $key = ApiKey::find($keyId);
        if ($key) {
            $key->update(['is_active' => !$key->is_active]);
            $this->message = 'وضعیت API Key تغییر کرد';
            $this->messageType = 'success';
            $this->loadApiKeys();
        }
    }

    public function showAddForm()
    {
        $this->showingAddForm = true;
        $this->resetAddForm();
        // Debug: log to check if method is called
        Log::info('showAddForm called, showingAddForm property: ' . $this->showingAddForm);
        // Force Livewire to update
        $this->dispatch('refresh-component');
    }

    public function cancelAdd()
    {
        $this->showingAddForm = false;
        $this->resetAddForm();
    }

    public function addKey()
    {
        $this->validate([
            'newUsername' => 'required|string',
            'newApiKey' => 'required|string',
            'newDescription' => 'nullable|string',
        ]);

        ApiKey::create([
            'key_name' => 'main_api',
            'key_value' => json_encode([
                'username' => $this->newUsername,
                'api_key' => $this->newApiKey
            ]),
            'description' => $this->newDescription,
            'is_active' => $this->newIsActive,
        ]);

        $this->message = 'API Key جدید با موفقیت اضافه شد';
        $this->messageType = 'success';
        $this->loadApiKeys();
        $this->cancelAdd();
    }

    public function resetEditForm()
    {
        $this->editKeyName = '';
        $this->editKeyValue = '';
        $this->editDescription = '';
        $this->editIsActive = true;
    }

    public function resetAddForm()
    {
        $this->newUsername = '';
        $this->newApiKey = '';
        $this->newDescription = '';
        $this->newIsActive = true;
    }

    public function resetForms()
    {
        $this->resetEditForm();
        $this->resetAddForm();
        $this->editingKey = null;
        $this->showingAddForm = false;
    }

    public function render()
    {
        return view('livewire.admin.api-key-manager');
    }
}
