<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Report;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedReports = [];
    public $selectAll = false;
    public $bulkAction = '';

    protected $listeners = ['reportDeleted' => '$refresh', 'reportsBulkDeleted' => '$refresh'];

    #[On('deleteReport')] // این decorator را اضافه کنید
    public function deleteReport($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش با موفقیت حذف شد.'
        ]);

        $this->dispatch('reportDeleted');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[On('deleteMultipleReports')] // این decorator را اضافه کنید
    public function deleteMultipleReports()
    {
        if (empty($this->selectedReports)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک گزارش را انتخاب کنید.'
            ]);
            return;
        }

        Report::whereIn('id', $this->selectedReports)->delete();

        $this->selectedReports = [];
        $this->selectAll = false;

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش‌های انتخاب شده با موفقیت حذف شدند.'
        ]);

        $this->dispatch('reportsBulkDeleted');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedReports = $this->reportsQuery->pluck('id')->toArray();
        } else {
            $this->selectedReports = [];
        }
    }

    public function updatedSelectedReports()
    {
        $this->selectAll = false;
    }

    public function executeBulkAction()
    {
        if ($this->bulkAction === 'delete' && !empty($this->selectedReports)) {
            $this->dispatch('confirmBulkDelete', [
                'type' => 'reports',
                'count' => count($this->selectedReports)
            ]);
        }
    }

    public function getReportsQueryProperty()
    {
        return Report::with('category')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $reports = $this->reportsQuery->paginate($this->perPage);

        return view('livewire.reports.index', compact('reports'));
    }
}
