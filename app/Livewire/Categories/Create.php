<?php

namespace App\Livewire\Categories;

use Livewire\Component;
use App\Models\Category;

class Create extends Component
{
    public $name = '';
    public $description = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:categories,name',
        'description' => 'nullable|string'
    ];

    public function save()
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
            'description' => $this->description
        ]);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'موفقیت!',
            'text' => 'دسته‌بندی جدید با موفقیت ایجاد شد.'
        ]);

        $this->reset();
    }

    public function render()
    {
        return view('livewire.categories.create');
    }
}
