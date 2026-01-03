<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Report;
use App\Models\Category;
use App\Models\Pattern;
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
    public $auto_ability = false;
    public $patterns = [];
    public $selectedPattern = '';

    public function mount($id)
    {
        $this->categories = Category::all();
        $this->report = Report::findOrFail($id);

        $this->category_id = $this->report->category_id;
        $this->title = $this->report->title;
        $this->description = $this->report->description;
        $this->negative_score = $this->report->negative_score;
        $this->increase_coefficient = $this->report->increase_coefficient;
        $this->auto_ability = $this->report->auto_ability ?? false;
        
        // بارگذاری الگوی مرتبط با گزارش (فقط اولین الگو)
        $firstPattern = $this->report->patterns()
            ->orderBy('report_pattern.sort_order')
            ->first();
        $this->selectedPattern = $firstPattern ? $firstPattern->id : '';
        
        // الگوهایی که به هیچ گزارشی متصل نشده‌اند یا فقط به این گزارش متصل شده‌اند
        $currentPatternId = $firstPattern ? $firstPattern->id : null;
        $query = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code');
            
        if ($currentPatternId) {
            // الگوهایی که به هیچ گزارشی متصل نشده‌اند یا الگوی فعلی این گزارش
            $query->where(function($q) use ($currentPatternId) {
                $q->whereDoesntHave('reports')
                  ->orWhere('id', $currentPatternId);
            });
        } else {
            // فقط الگوهایی که به هیچ گزارشی متصل نشده‌اند
            $query->whereDoesntHave('reports');
        }
        
        $this->patterns = $query->orderBy('title')->get();
    }

    protected $rules = [
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'negative_score' => 'required|integer|min:0',
        'increase_coefficient' => 'required|numeric|min:0',
        'selectedPattern' => 'required|exists:patterns,id',
    ];

    public function update()
    {
        $this->validate();

        // بررسی اینکه آیا الگوی انتخاب شده قبلاً به گزارش دیگری (غیر از گزارش فعلی) متصل شده یا نه
        $pattern = Pattern::find($this->selectedPattern);
        if ($pattern) {
            $existingReport = $pattern->reports()->where('reports.id', '!=', $this->report->id)->first();
            if ($existingReport) {
                $this->addError('selectedPattern', 'این الگو قبلاً به گزارش "' . $existingReport->title . '" متصل شده است. هر الگو فقط می‌تواند به یک گزارش متصل شود.');
                return;
            }
        }

        $this->report->update([
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'negative_score' => $this->negative_score,
            'increase_coefficient' => $this->increase_coefficient,
            'auto_ability' => $this->auto_ability,
        ]);

        // به‌روزرسانی الگوی مرتبط با گزارش (فقط یک الگو)
        if (!empty($this->selectedPattern)) {
            $this->report->patterns()->sync([
                $this->selectedPattern => [
                    'sort_order' => 1,
                    'is_active' => true,
                ]
            ]);
        }

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
