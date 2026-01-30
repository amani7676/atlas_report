<div>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-plus-circle me-2"></i>
                ثبت گزارش جدید
            </h5>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <!-- اطلاعات اقامت‌گر -->
                @if($resident_id)
                    @php
                        $resident = \App\Models\Resident::where('resident_id', $resident_id)->first();
                    @endphp
                    @if($resident)
                        <div class="alert alert-info mb-3">
                            <strong>اقامت‌گر:</strong> {{ $resident->resident_full_name ?? 'نامشخص' }}
                            @if($resident->unit_name) - اتاق {{ $resident->unit_name }} @endif
                            @if($resident->room_name) - تخت {{ $resident->room_name }} @endif
                        </div>
                    @endif
                @endif

                <!-- انتخاب دسته‌بندی -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-folder"></i>
                        دسته‌بندی
                    </label>
                    <select class="form-select" wire:model.live="selectedCategory">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- انتخاب گزارش -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-file-alt"></i>
                        گزارش
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" wire:model="report_id" required>
                        <option value="">-- انتخاب گزارش --</option>
                        @foreach($reports as $report)
                            <option value="{{ $report->id }}">
                                {{ $report->title }}
                                @if($report->negative_score) ({{ $report->negative_score }} امتیاز) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('report_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- توضیحات -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-comment"></i>
                        توضیحات
                    </label>
                    <textarea class="form-control" wire:model="description" rows="3" 
                              placeholder="توضیحات مربوط به این گزارش را وارد کنید..."></textarea>
                    @error('description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- یادداشت‌ها -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-sticky-note"></i>
                        یادداشت‌ها
                    </label>
                    <textarea class="form-control" wire:model="notes" rows="2" 
                              placeholder="یادداشت‌های داخلی..."></textarea>
                </div>

                <!-- دکمه‌ها -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        ثبت گزارش
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-times me-1"></i>
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // گوش دادن به رویداد reportCreated برای بستن مدال
    window.addEventListener('reportCreated', function() {
        // بستن مدال بعد از ثبت موفق
        const modal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
        if (modal) {
            modal.hide();
        }
        
        // رفرش صفحه یا به‌روزرسانی کامپوننت والد
        window.location.reload();
    });
</script>
@endpush
