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
                @if($resident_id && !$isGroupMode)
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
                
                <!-- حالت گروهی -->
                @if($isGroupMode && !empty($selectedResidents))
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-users me-2"></i>
                        <strong>{{ count($selectedResidents) }} اقامت‌گر انتخاب شده</strong>
                    </div>
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
                
                <!-- گزینه‌های پیامک -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model.live="sendSms" id="sendSms">
                        <label class="form-check-label" for="sendSms">
                            <i class="fas fa-sms me-2 text-primary"></i>
                            <strong>ارسال پیامک به اقامت‌گر(ها)</strong>
                        </label>
                    </div>
                </div>
                
                <!-- نمایش پیام الگو -->
                @if($sendSms && isset($patternMessage))
                    @if($patternMessage['success'])
                        <div class="mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-light text-success">
                                    <h6 class="mb-0">
                                        <i class="fas fa-envelope-open-text me-2"></i>
                                        پیش‌نمایش پیام الگو
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted">عنوان الگو:</small>
                                        <strong class="text-success">{{ $patternMessage['pattern_title'] }}</strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">متن اصلی:</small>
                                        <div class="p-2 bg-light border rounded font-monospace small">
                                            {{ $patternMessage['original_message'] }}
                                        </div>
                                    </div>
                                    
                                    @if($isGroupMode || count($selectedResidents) > 1)
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                wire:click="showSmsPreview">
                                            <i class="fas fa-eye me-1"></i>
                                            مشاهده پیش‌نمایش برای همه اقامت‌گران
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $patternMessage['message'] }}
                            </div>
                        </div>
                    @endif
                @endif

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

<!-- SMS Preview Modal -->
@if($showPreviewModal)
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1050;" tabindex="-1">
    <div class="modal-dialog modal-xl" style="margin-top: 5vh;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.3); overflow: hidden;">
            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    پیش‌نمایش پیامک برای اقامت‌گران
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="closePreviewModal"></button>
            </div>
            
            <!-- Body -->
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="row">
                    @foreach($previewMessages as $index => $preview)
                        <div class="col-lg-6 col-xl-4 mb-3">
                            <div class="card h-100 {{ $preview['has_phone'] ? 'border-success' : 'border-warning' }}">
                                <div class="card-header d-flex justify-content-between align-items-center {{ $preview['has_phone'] ? 'bg-light text-success' : 'bg-warning text-dark' }}">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $preview['resident_name'] }}
                                    </h6>
                                    @if($preview['has_phone'])
                                        <i class="fas fa-mobile-alt text-success" title="دارای شماره تماس"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-warning" title="بدون شماره تماس"></i>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted">کد اقامت:</small>
                                        <span class="badge bg-secondary">{{ $preview['resident_id'] }}</span>
                                    </div>
                                    
                                    @if($preview['unit_name'])
                                        <div class="mb-2">
                                            <small class="text-muted">واحد:</small>
                                            <span>{{ $preview['unit_name'] }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($preview['room_name'])
                                        <div class="mb-2">
                                            <small class="text-muted">اتاق:</small>
                                            <span>{{ $preview['room_name'] }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">شماره تماس:</small>
                                        <span class="{{ $preview['has_phone'] ? 'text-success' : 'text-warning' }}">
                                            {{ $preview['phone'] }}
                                        </span>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div>
                                        <small class="text-muted d-block mb-1">پیام ارسالی:</small>
                                        <div class="p-2 bg-light border rounded" style="font-size: 0.9rem; line-height: 1.4;">
                                            {{ $preview['message'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if(empty($previewMessages))
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">هیچ پیامی برای نمایش وجود ندارد</p>
                    </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="modal-footer">
                <div class="me-auto">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ count($previewMessages) }} پیام برای نمایش
                    </small>
                </div>
                <button type="button" class="btn btn-secondary" wire:click="closePreviewModal">
                    <i class="fas fa-times me-1"></i>
                    بستن
                </button>
            </div>
        </div>
    </div>
</div>
@endif

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
