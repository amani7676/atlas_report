<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;
use Livewire\Attributes\On;

class Edit extends Component
{
    public $report;
    public $categories = [];
    public $category_id = '';
    public $title = '';
    public $description = '';
    public $negative_score = '';
    public $increase_coefficient = '';
    public $page_number = '';

    public function mount($id)
    {
        $this->categories = Category::all();
        $this->report = Report::findOrFail($id);

        $this->category_id = $this->report->category_id;
        $this->title = $this->report->title;
        $this->description = $this->report->description;
        $this->negative_score = $this->report->negative_score;
        $this->increase_coefficient = $this->report->increase_coefficient;
        $this->page_number = $this->report->page_number;
    }

    protected $rules = [
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'negative_score' => 'required|integer|min:0',
        'increase_coefficient' => 'required|numeric|min:0',
        'page_number' => 'required|integer|min:1'
    ];

    public function update()
    {
        $this->validate();

        $this->report->update([
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'negative_score' => $this->negative_score,
            'increase_coefficient' => $this->increase_coefficient,
            'page_number' => $this->page_number
        ]);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش با موفقیت به‌روزرسانی شد.'
        ]);
    }

    #[On('deleteReport')]
    public function deleteReport($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش با موفقیت حذف شد.'
        ]);

        $this->redirect('/reports', navigate: true);
    }

    public function render()
    {
        return view('livewire.reports.edit');
    }
}
