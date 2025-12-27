<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;

class Create extends Component
{
    public $categories = [];
    public $category_id = '';
    public $title = '';
    public $description = '';
    public $negative_score = '';
    public $increase_coefficient = '';
    public $page_number = '';

    public function mount()
    {
        $this->categories = Category::all();
    }

    protected $rules = [
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'negative_score' => 'required|integer|min:0',
        'increase_coefficient' => 'required|numeric|min:0',
        'page_number' => 'required|integer|min:1'
    ];

    public function save()
    {
        $this->validate();

        Report::create([
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
            'text' => 'گزارش جدید با موفقیت ایجاد شد.'
        ]);

        $this->reset();

        // بازگرداندن لیست دسته‌بندی‌ها بعد از reset
        $this->categories = Category::all();
    }

    public function render()
    {
        return view('livewire.reports.create');
    }
}
