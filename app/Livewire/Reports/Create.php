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
    public $increase_coefficient = '1';
    public $auto_ability = true;
    public $patterns = [];
    public $selectedPattern = '';

    public function mount()
    {
        $this->categories = Category::all();
        
        // فقط الگوهایی که هنوز به هیچ گزارشی متصل نشده‌اند
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->whereDoesntHave('reports')
            ->orderBy('title')
            ->get();
    }

    protected $rules = [
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'negative_score' => 'required|integer|min:0',
        'increase_coefficient' => 'required|numeric|min:0',
        'selectedPattern' => 'required|exists:patterns,id',
    ];

    public function save()
    {
        $this->validate();

        // بررسی اینکه آیا الگوی انتخاب شده قبلاً به گزارش دیگری متصل شده یا نه
        $pattern = Pattern::find($this->selectedPattern);
        if ($pattern) {
            $existingReport = $pattern->reports()->first();
            if ($existingReport) {
                $this->addError('selectedPattern', 'این الگو قبلاً به گزارش "' . $existingReport->title . '" متصل شده است. هر الگو فقط می‌تواند به یک گزارش متصل شود.');
                return;
            }
        }

        $report = Report::create([
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'negative_score' => $this->negative_score,
            'increase_coefficient' => $this->increase_coefficient,
            'auto_ability' => $this->auto_ability
        ]);

        // اتصال الگو به گزارش (فقط یک الگو)
        if (!empty($this->selectedPattern)) {
            $report->patterns()->sync([
                $this->selectedPattern => [
                    'sort_order' => 1,
                    'is_active' => true,
                ]
            ]);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش جدید با موفقیت ایجاد شد.'
        ]);

        $this->reset();

        // بازگرداندن لیست دسته‌بندی‌ها و الگوها بعد از reset
        $this->categories = Category::all();
        // فقط الگوهایی که هنوز به هیچ گزارشی متصل نشده‌اند
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->whereDoesntHave('reports')
            ->orderBy('title')
            ->get();
        $this->selectedPattern = '';
    }

    public function render()
    {
        return view('livewire.reports.create');
    }
}
