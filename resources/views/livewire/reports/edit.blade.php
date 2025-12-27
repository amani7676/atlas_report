<div>
    <div class="card">
        <h2 style="margin-bottom: 20px;">ویرایش گزارش</h2>

        <form wire:submit.prevent="update">
            <div class="form-group">
                <label class="form-label">دسته‌بندی *</label>
                <select wire:model="category_id" class="form-control" required>
                    <option value="">انتخاب دسته‌بندی</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">عنوان گزارش *</label>
                <input
                    type="text"
                    wire:model="title"
                    class="form-control"
                    required
                >
                @error('title') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">توضیحات *</label>
                <textarea
                    wire:model="description"
                    class="form-control"
                    rows="4"
                    required
                ></textarea>
                @error('description') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">نمره منفی *</label>
                    <input
                        type="number"
                        wire:model="negative_score"
                        class="form-control"
                        min="0"
                        required
                    >
                    @error('negative_score') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">ضریب افزایش *</label>
                    <input
                        type="number"
                        wire:model="increase_coefficient"
                        class="form-control"
                        step="0.01"
                        min="0"
                        required
                    >
                    @error('increase_coefficient') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">شماره صفحه *</label>
                    <input
                        type="number"
                        wire:model="page_number"
                        class="form-control"
                        min="1"
                        required
                    >
                    @error('page_number') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    به‌روزرسانی گزارش
                </button>
                <a href="/reports" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به لیست
                </a>
                <button
                    type="button"
                    onclick="confirmDelete({{ $report->id }}, 'Report')"
                    class="btn btn-danger"
                >
                    <i class="fas fa-trash"></i>
                    حذف گزارش
                </button>
            </div>
        </form>
    </div>
</div>
