<?php

namespace App\Livewire\Patterns;

use Livewire\Component;
use App\Models\Pattern;
use App\Models\Blacklist;
use App\Models\Report;
use App\Models\Category;
use App\Models\PatternVariable;
use App\Models\PatternPatternVariable;
use App\Services\MelipayamakService;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 20;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = 'approved'; // پیش‌فرض: فقط الگوهای تایید شده
    
    // Modal states
    public $showModal = false;
    public $isEditing = false;
    public $editingId = null;
    
    // Form fields
    public $title = '';
    public $text = '';
    public $pattern_code = '';
    public $blacklist_id = '1'; // مقدار پیش‌فرض
    public $status = 'pending';
    public $rejection_reason = '';
    public $is_active = true;
    
    // API Response Modal
    public $showApiResponseModal = false;
    public $apiResponseData = null;
    
    // Sync from API
    public $syncing = false;
    
    // View Raw API Response
    public $showRawApiResponseModal = false;
    public $rawApiResponseData = null;
    
    // نمایش پاسخ API بعد از همگام‌سازی
    public $showSyncResponseModal = false;
    public $syncResponseData = null;

    public function mount()
    {
        // اگر از route /patterns/create آمده‌ایم، مودال ایجاد را باز می‌کنیم
        if (request()->is('patterns/create*')) {
            $this->openCreateModal();
        }
    }

    public function syncFromApi()
    {
        // فقط مدال رو نشون بده
        $this->syncResponseData = [
            'success' => true,
            'title' => 'همگام‌سازی الگوها',
            'message' => 'این یک تست است - مدال کار می‌کند!'
        ];
        $this->showSyncResponseModal = true;
    }

    public function closeSyncResponseModal()
    {
        $this->showSyncResponseModal = false;
        $this->syncResponseData = null;
    }

    public function render()
    {
        $query = Pattern::query();

        // جستجو
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('text', 'like', '%' . $this->search . '%')
                  ->orWhere('pattern_code', 'like', '%' . $this->search . '%');
            });
        }

        // فیلتر وضعیت
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // مرتب‌سازی
        $query->orderBy($this->sortBy, $this->sortDirection);

        $patterns = $query->paginate($this->perPage);

        return view('livewire.patterns.index', [
            'patterns' => $patterns
        ]);
    }
}
