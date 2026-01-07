<?php

namespace App\Livewire\WelcomeMessages;

use Livewire\Component;
use App\Models\WelcomeMessageLog;
use Livewire\WithPagination;

class Logs extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function render()
    {
        $query = WelcomeMessageLog::with('welcomeMessage')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('resident_name', 'like', '%' . $this->search . '%')
                      ->orWhere('resident_phone', 'like', '%' . $this->search . '%')
                      ->orWhere('resident_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.welcome-messages.logs', [
            'logs' => $query,
            'statusOptions' => [
                'pending' => 'در انتظار',
                'sent' => 'ارسال شده',
                'failed' => 'ناموفق',
            ],
        ]);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function resendMessage($logId)
    {
        try {
            $log = WelcomeMessageLog::findOrFail($logId);
            
            if ($log->status === 'sent') {
                $this->dispatch('showToast', [
                    'type' => 'warning',
                    'title' => 'هشدار',
                    'message' => 'این پیام قبلاً با موفقیت ارسال شده است.',
                ]);
                return;
            }

            // ایجاد job جدید برای ارسال مجدد
            \App\Jobs\ResendWelcomeMessage::dispatch($log);
            
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت',
                'message' => 'درخواست ارسال مجدد پیام با موفقیت ثبت شد.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا',
                'message' => 'خطا در ارسال مجدد پیام: ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteLog($logId)
    {
        try {
            $log = WelcomeMessageLog::findOrFail($logId);
            $log->delete();
            
            $this->dispatch('showToast', [
                'type' => 'success',
                'title' => 'موفقیت',
                'message' => 'لاگ با موفقیت حذف شد.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'error',
                'title' => 'خطا',
                'message' => 'خطا در حذف لاگ: ' . $e->getMessage(),
            ]);
        }
    }
}
