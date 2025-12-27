<?php

namespace App\Livewire\Categories;

use Livewire\Component;
use App\Models\Category;

class Edit extends Component
{
    public $category;
    public $name;
    public $description;

    public function mount($id)
    {
        $this->category = Category::findOrFail($id);
        $this->name = $this->category->name;
        $this->description = $this->category->description;
    }

    protected $rules = [
        'name' => 'required|string|max:255|unique:categories,name,' . null,
        'description' => 'nullable|string'
    ];

    public function update()
    {
        $this->validate();

        $this->category->update([
            'name' => $this->name,
            'description' => $this->description
        ]);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'دسته‌بندی با موفقیت به‌روزرسانی شد.'
        ]);
    }

    public function render()
    {
        return view('livewire.categories.edit');
    }
}
