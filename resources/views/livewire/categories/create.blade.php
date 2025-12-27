<div>
    <div class="card">
        <h2 style="margin-bottom: 20px;">ایجاد دسته‌بندی جدید</h2>

        <form wire:submit.prevent="save">
            <div class="form-group">
                <label class="form-label">نام دسته‌بندی *</label>
                <input
                    type="text"
                    wire:model="name"
                    class="form-control"
                    placeholder="مثال: نظافت اتاق"
                    required
                >
                @error('name') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">توضیحات</label>
                <textarea
                    wire:model="description"
                    class="form-control"
                    rows="3"
                    placeholder="توضیحات اختیاری درباره این دسته‌بندی..."
                ></textarea>
                @error('description') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    ذخیره دسته‌بندی
                </button>
                <a href="/categories" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به لیست
                </a>
            </div>
        </form>
    </div>
</div>
