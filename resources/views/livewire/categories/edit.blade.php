<div>
    <div class="card">
        <h2 style="margin-bottom: 20px;">ویرایش دسته‌بندی</h2>

        <form wire:submit.prevent="update">
            <div class="form-group">
                <label class="form-label">نام دسته‌بندی *</label>
                <input
                    type="text"
                    wire:model="name"
                    class="form-control"
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
                ></textarea>
                @error('description') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    به‌روزرسانی دسته‌بندی
                </button>
                <a href="/categories" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به لیست
                </a>
            </div>
        </form>
    </div>
</div>
