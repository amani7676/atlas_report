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
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input
                        type="checkbox"
                        wire:model="auto_ability"
                        style="width: 18px; height: 18px; cursor: pointer;"
                    >
                    <span class="form-label" style="margin: 0;">
                        <i class="fas fa-robot"></i>
                        قابلیت ارسال خودکار پیام
                    </span>
                </label>
                <small style="display: block; color: #666; margin-top: 5px; font-size: 12px; margin-right: 28px;">
                    در صورت فعال بودن، این گزارش می‌تواند در سیستم ارسال خودکار پیام استفاده شود.
                </small>
            </div>

            <!-- انتخاب الگوی پیامک -->
            <div class="form-group" style="margin-top: 30px;">
                <label class="form-label">
                    <i class="fas fa-sms"></i>
                    الگوی پیامک مرتبط *
                </label>
                <small style="display: block; color: #666; margin-bottom: 10px; font-size: 12px;">
                    باید یک الگوی پیامک را برای این گزارش انتخاب کنید. این الگو در صفحه ارسال پیامک دستی و ارسال خودکار استفاده می‌شود.
                </small>
                
                @if(count($patterns) > 0)
                    <select wire:model="selectedPattern" class="form-control" required>
                        <option value="">انتخاب الگوی پیامک</option>
                        @foreach($patterns as $pattern)
                            <option value="{{ $pattern->id }}" {{ $selectedPattern == $pattern->id ? 'selected' : '' }}>
                                {{ $pattern->title }}
                                @if($pattern->pattern_code)
                                    (کد: {{ $pattern->pattern_code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('selectedPattern') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
                @else
                    <p style="color: #f72585; font-size: 14px; padding: 15px; background: #fff3cd; border-radius: 6px; border-right: 4px solid #f72585;">
                        <i class="fas fa-exclamation-triangle"></i>
                        هیچ الگوی فعالی یافت نشد. لطفاً ابتدا الگوهایی را در بخش "الگوها" ایجاد کنید.
                    </p>
                @endif
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
