<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;
use App\Models\Pattern;

class Create extends Component
{
    public $categories = [];
    public $category_id = '';
    public $title = '';
    public $description = '';
    public $negative_score = '';
    public $increase_coefficient = '';
    public $page_number = '';
    public $patterns = [];
    public $selectedPatterns = [];

    public function mount()
    {
        $this->categories = Category::all();
        $this->patterns = Pattern::where('is_active', true)
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
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

        $report = Report::create([
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'negative_score' => $this->negative_score,
            'increase_coefficient' => $this->increase_coefficient,
            'page_number' => $this->page_number
        ]);

        // اتصال الگوها به گزارش
        if (!empty($this->selectedPatterns)) {
            $syncData = [];
            foreach ($this->selectedPatterns as $index => $patternId) {
                $syncData[$patternId] = [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ];
            }
            $report->patterns()->sync($syncData);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش جدید با موفقیت ایجاد شد.'
        ]);

        $this->reset();

        // بازگرداندن لیست دسته‌بندی‌ها و الگوها بعد از reset
        $this->categories = Category::all();
        $this->patterns = Pattern::where('is_active', true)
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
        $this->selectedPatterns = [];
    }
    
    public function togglePattern($patternId)
    {
        if (in_array($patternId, $this->selectedPatterns)) {
            $this->selectedPatterns = array_values(array_diff($this->selectedPatterns, [$patternId]));
        } else {
            $this->selectedPatterns[] = $patternId;
        }
    }
    
    public function removePattern($patternId)
    {
        $this->selectedPatterns = array_values(array_diff($this->selectedPatterns, [$patternId]));
    }

    public function render()
    {
        return view('livewire.reports.create');
    }
}
