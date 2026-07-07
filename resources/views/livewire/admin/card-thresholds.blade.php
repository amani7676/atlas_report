<div>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-sliders-h me-2"></i>
                            تنظیمات آستانه کارت‌های انضباطی
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($successMessage)
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-check-circle me-2"></i>موفقیت!</strong>
                                {{ $successMessage }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form wire:submit="saveThresholds">
                            <div class="mb-4">
                                <label for="yellowThreshold" class="form-label fw-bold">
                                    <i class="fas fa-id-card text-warning me-2"></i>
                                    آستانه کارت زرد
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control @error('yellowThreshold') ? 'is-invalid' : ''" 
                                    id="yellowThreshold"
                                    wire:model.live="yellowThreshold"
                                    min="1" 
                                    max="100"
                                    step="1"
                                    placeholder="مثال: 20">
                                <div class="form-text text-muted">
                                    اقامت‌گرانی که مجموع امتیاز تخلفاتشان به این عدد برسد، کارت زرد دریافت می‌کنند.
                                </div>
                                @error('yellowThreshold')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="redThreshold" class="form-label fw-bold">
                                    <i class="fas fa-id-card text-danger me-2"></i>
                                    آستانه کارت قرمز
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control @error('redThreshold') ? 'is-invalid' : ''" 
                                    id="redThreshold"
                                    wire:model.live="redThreshold"
                                    min="1" 
                                    max="100"
                                    step="1"
                                    placeholder="مثال: 30">
                                <div class="form-text text-muted">
                                    اقامت‌گرانی که مجموع امتیاز تخلفاتشان به این عدد برسد، کارت قرمز دریافت می‌کنند.
                                </div>
                                @error('redThreshold')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <h6 class="text-primary mb-3">
                                <i class="fas fa-hashtag me-2"></i>
                                آستانه‌های تعداد تخلف
                            </h6>

                            <div class="mb-4">
                                <label for="yellowViolationCountThreshold" class="form-label fw-bold">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    آستانه تعداد تخلف کارت زرد
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control @error('yellowViolationCountThreshold') ? 'is-invalid' : ''" 
                                    id="yellowViolationCountThreshold"
                                    wire:model.live="yellowViolationCountThreshold"
                                    min="1" 
                                    max="50"
                                    step="1"
                                    placeholder="مثال: 3">
                                <div class="form-text text-muted">
                                    اقامت‌گرانی که تعداد تخلفات یکسان (گزارش مشابه) به این عدد برسد، کارت زرد دریافت می‌کنند.
                                </div>
                                @error('yellowViolationCountThreshold')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="redViolationCountThreshold" class="form-label fw-bold">
                                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                    آستانه تعداد تخلف کارت قرمز
                                </label>
                                <input 
                                    type="number" 
                                    class="form-control @error('redViolationCountThreshold') ? 'is-invalid' : ''" 
                                    id="redViolationCountThreshold"
                                    wire:model.live="redViolationCountThreshold"
                                    min="1" 
                                    max="50"
                                    step="1"
                                    placeholder="مثال: 5">
                                <div class="form-text text-muted">
                                    اقامت‌گرانی که تعداد تخلفات یکسان (گزارش مشابه) به این عدد برسد، کارت قرمز دریافت می‌کنند.
                                </div>
                                @error('redViolationCountThreshold')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- نمایش وضعیت فعلی -->
                            <div class="alert alert-info mb-4">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    وضعیت فعلی:
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>کارت زرد:</strong> امتیاز ≥ {{ $yellowThreshold }} یا تعداد تخلف یکسان ≥ {{ $yellowViolationCountThreshold }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>کارت قرمز:</strong> امتیاز ≥ {{ $redThreshold }} یا تعداد تخلف یکسان ≥ {{ $redViolationCountThreshold }}
                                    </div>
                                </div>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    نکته: آستانه‌های کارت قرمز باید بزرگتر یا مساوی آستانه‌های کارت زرد باشند.
                                </small>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary" wire:click="loadThresholds">
                                    <i class="fas fa-undo me-2"></i>
                                    بازنشانی
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    ذخیره تنظیمات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- کارت راهنما -->
                <div class="card mt-4 shadow">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i>
                            راهنمای استفاده
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-warning">
                                    <i class="fas fa-id-card me-2"></i>
                                    کارت زرد
                                </h6>
                                <p class="small text-muted">
                                    اخطاری برای اقامت‌گرانی که مجموع امتیاز تخلفاتشان به آستانه زرد رسیده باشد.
                                    این کارت به عنوان هشدار اولیه عمل می‌کند.
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-danger">
                                    <i class="fas fa-id-card me-2"></i>
                                    کارت قرمز
                                </h6>
                                <p class="small text-muted">
                                    اخطاری جدی برای اقامت‌گرانی که مجموع امتیاز تخلفاتشان به آستانه قرمز رسیده باشد.
                                    این کارت نشان‌دهنده نیاز به اقدامات انضباطی جدی است.
                                </p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6 class="text-primary">
                                <i class="fas fa-cogs me-2"></i>
                                نحوه عملکرد
                            </h6>
                            <ol class="small text-muted me-3">
                                <li>سیستم به صورت خودکار مجموع امتیاز تخلفات هر اقامت‌گر را محاسبه می‌کند</li>
                                <li>اگر مجموع امتیاز ≥ آستانه زرد باشد، کارت زرد نمایش داده می‌شود</li>
                                <li>اگر مجموع امتیاز ≥ آستانه قرمز باشد، کارت قرمز نمایش داده می‌شود</li>
                                <li>امتیازات به صورت زنده و لحظه‌ای به‌روزرسانی می‌شوند</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 10px;
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        border-bottom: none;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .btn {
        border-radius: 6px;
        padding: 8px 16px;
    }
    
    .alert {
        border: none;
        border-radius: 8px;
    }
    
    .invalid-feedback {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // اضافه کردن انیمیشن برای کارت‌ها
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endpush
