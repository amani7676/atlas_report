<?php

namespace App\Livewire\Categories;

use Livewire\Component;
use App\Models\Category;
use App\Models\Report;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $selectedCategories = [];
    public $selectAll = false;
    public $bulkAction = '';
    public $deleteWithReports = false;

    protected $listeners = ['categoryDeleted' => '$refresh', 'categoriesBulkDeleted' => '$refresh'];

    #[On('deleteCategory')] // این decorator را اضافه کنید
    public function deleteCategory($id, $withReports = false)
    {
        $category = Category::withCount('reports')->findOrFail($id);

        if ($withReports) {
            // حذف دسته‌بندی همراه با گزارش‌های مرتبط
            Report::where('category_id', $id)->delete();
            $category->delete();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'دسته‌بندی و گزارش‌های مرتبط با موفقیت حذف شدند.'
            ]);
        } else {
            // بررسی وجود گزارش‌های مرتبط
            if ($category->reports_count > 0) {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'این دسته‌بندی دارای ' . $category->reports_count . ' گزارش است. برای حذف، ابتدا گزارش‌ها را حذف کنید یا گزینه "حذف همراه با گزارش‌ها" را انتخاب کنید.'
                ]);
                return;
            }

            $category->delete();

            $this->dispatch('showAlert', [
                'type' => 'success',
                'title' => 'موفقیت!',
                'text' => 'دسته‌بندی با موفقیت حذف شد.'
            ]);
        }

        $this->dispatch('categoryDeleted');
    }

    #[On('deleteMultipleCategories')] // این decorator را اضافه کنید
    public function deleteMultipleCategories()
    {
        if (empty($this->selectedCategories)) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'title' => 'هشدار!',
                'text' => 'لطفاً حداقل یک دسته‌بندی را انتخاب کنید.'
            ]);
            return;
        }

        if ($this->deleteWithReports) {
            // حذف دسته‌بندی‌ها همراه با گزارش‌های مرتبط
            foreach ($this->selectedCategories as $categoryId) {
                Report::where('category_id', $categoryId)->delete();
            }
        } else {
            // بررسی وجود گزارش‌های مرتبط
            $categoriesWithReports = Category::whereIn('id', $this->selectedCategories)
                ->whereHas('reports')
                ->count();

            if ($categoriesWithReports > 0) {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'title' => 'خطا!',
                    'text' => 'برخی از دسته‌بندی‌های انتخاب شده دارای گزارش هستند. برای حذف، گزینه "حذف همراه با گزارش‌ها" را انتخاب کنید.'
                ]);
                return;
            }
        }

        Category::whereIn('id', $this->selectedCategories)->delete();

        $this->selectedCategories = [];
        $this->selectAll = false;
        $this->deleteWithReports = false;

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'دسته‌بندی‌های انتخاب شده با موفقیت حذف شدند.'
        ]);

        $this->dispatch('categoriesBulkDeleted');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCategories = $this->categoriesQuery->pluck('id')->toArray();
        } else {
            $this->selectedCategories = [];
        }
    }

    public function updatedSelectedCategories()
    {
        $this->selectAll = false;
    }

    public function executeBulkAction()
    {
        if ($this->bulkAction === 'delete' && !empty($this->selectedCategories)) {
            $this->dispatch('confirmBulkDelete', [
                'type' => 'categories',
                'count' => count($this->selectedCategories)
            ]);
        }
    }

    public function getCategoriesQueryProperty()
    {
        return Category::withCount('reports')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $categories = $this->categoriesQuery->paginate($this->perPage);

        return view('livewire.categories.index', compact('categories'));
    }
}
