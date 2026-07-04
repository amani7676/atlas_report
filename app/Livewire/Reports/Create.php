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
    public $api_endpoint_name = '';

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
        'api_endpoint_name' => 'nullable|regex:/^[a-zA-Z0-9_]+$/|max:255',
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

        // بررسی تکراری بودن نام endpoint
        if (!empty($this->api_endpoint_name)) {
            $existingEndpoint = Report::where('api_endpoint_name', $this->api_endpoint_name)->first();
            if ($existingEndpoint) {
                $this->addError('api_endpoint_name', 'این نام endpoint قبلاً برای گزارش "' . $existingEndpoint->title . '" استفاده شده است.');
                return;
            }
        }

        $report = Report::create([
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'negative_score' => $this->negative_score,
            'increase_coefficient' => $this->increase_coefficient,
            'auto_ability' => $this->auto_ability,
            'api_endpoint_name' => $this->api_endpoint_name ?: null
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
