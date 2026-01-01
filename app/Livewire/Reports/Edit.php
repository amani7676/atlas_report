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
    public $selectedPatterns = [];

    public function mount($id)
    {
        $this->categories = Category::all();
        $this->patterns = Pattern::where('is_active', true)
            ->where('status', 'approved')
            ->whereNotNull('pattern_code')
            ->orderBy('title')
            ->get();
        $this->report = Report::findOrFail($id);

        $this->category_id = $this->report->category_id;
        $this->title = $this->report->title;
        $this->description = $this->report->description;
        $this->negative_score = $this->report->negative_score;
        $this->increase_coefficient = $this->report->increase_coefficient;
        $this->auto_ability = $this->report->auto_ability ?? false;
        
        // بارگذاری الگوهای مرتبط با گزارش
        $this->selectedPatterns = $this->report->patterns()
            ->orderBy('report_pattern.sort_order')
            ->pluck('patterns.id')
            ->toArray();
    }

    protected $rules = [
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'negative_score' => 'required|integer|min:0',
        'increase_coefficient' => 'required|numeric|min:0',
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
            'auto_ability' => $this->auto_ability,
        ]);

        // به‌روزرسانی الگوهای مرتبط با گزارش
        $syncData = [];
        foreach ($this->selectedPatterns as $index => $patternId) {
            $syncData[$patternId] = [
                'sort_order' => $index + 1,
                'is_active' => true,
            ];
        }
        $this->report->patterns()->sync($syncData);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'گزارش با موفقیت به‌روزرسانی شد.'
        ]);
    }
    
    public function togglePattern($patternId)
    {
        $patternId = (int)$patternId;
        $index = array_search($patternId, $this->selectedPatterns);
        
        if ($index !== false) {
            // حذف از لیست
            unset($this->selectedPatterns[$index]);
            $this->selectedPatterns = array_values($this->selectedPatterns);
        } else {
            // اضافه به لیست
            $this->selectedPatterns[] = $patternId;
        }
        
        // اطمینان از اینکه آرایه به درستی re-index شده است
        $this->selectedPatterns = array_values(array_unique($this->selectedPatterns));
    }
    
    public function removePattern($patternId)
    {
        $this->selectedPatterns = array_values(array_diff($this->selectedPatterns, [$patternId]));
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
