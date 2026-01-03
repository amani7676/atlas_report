<div>

    <!-- Bootstrap CSS برای Pagination -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Custom Pagination (مشابه گزارش تخلفی) */
        .custom-pagination .page-link {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 3px;
            border: 1px solid #dee2e6;
            color: #0d6efd;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
        }

        .custom-pagination .page-link:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .custom-pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.4);
        }

        .custom-pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }

        .custom-pagination .page-link i {
            font-size: 0.75rem;
        }

        /* Responsive Styles - Global */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 992px) {
            .card {
                padding: 15px;
            }

            .row > [class*="col-"] {
                margin-bottom: 15px;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 15px;
            }

            .d-flex.justify-content-between > * {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .card {
                padding: 12px;
                margin-bottom: 12px;
            }

            .card h4 {
                font-size: 18px;
                line-height: 1.4;
            }

            .card h6 {
                font-size: 14px;
            }

            .custom-pagination .page-link {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }

            .table {
                font-size: 12px;
                min-width: 800px;
            }

            .table th,
            .table td {
                padding: 8px 6px;
                white-space: nowrap;
            }

            .btn {
                padding: 8px 14px;
                font-size: 13px;
            }

            .form-control,
            .form-select {
                font-size: 14px;
            }

            .form-label {
                font-size: 13px;
            }

            /* Force columns to full width on mobile */
            .row > [class*="col-md-"],
            .row > [class*="col-lg-"],
            .row > [class*="col-xl-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            /* Search and filter section */
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 12px;
            }

            .d-flex.justify-content-between > * {
                width: 100%;
            }

            /* Card body responsive */
            .card-body {
                padding: 12px;
            }

            /* Badge responsive */
            .badge {
                font-size: 12px;
                padding: 5px 10px;
            }

            /* Input group responsive */
            .input-group {
                width: 100%;
            }

            .input-group-text {
                padding: 8px 12px;
                font-size: 14px;
            }

            /* Table container responsive */
            .table-container {
                margin: 0 -12px;
                padding: 0 12px;
            }

            /* Button in table header */
            .table th button {
                font-size: 11px;
                padding: 4px 10px;
            }
        }

        @media (max-width: 576px) {
            .card {
                padding: 10px;
            }

            .card h4 {
                font-size: 16px;
            }

            .card h6 {
                font-size: 13px;
            }

            .table {
                font-size: 11px;
                min-width: 700px;
            }

            .table th,
            .table td {
                padding: 6px 4px;
            }

            .btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 11px;
            }

            .form-control,
            .form-select {
                font-size: 13px;
                padding: 6px 10px;
            }

            .form-label {
                font-size: 12px;
                margin-bottom: 5px;
            }

            .badge {
                font-size: 11px;
                padding: 4px 8px;
            }

            .custom-pagination .page-link {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }

            /* Stack all columns */
            .row > [class*="col-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
                margin-bottom: 10px;
            }

            /* Button full width on mobile */
            .btn.w-100 {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 8px;
            }

            .card h4 {
                font-size: 14px;
            }

            .table {
                font-size: 10px;
                min-width: 600px;
            }

            .table th,
            .table td {
                padding: 5px 3px;
            }

            .btn {
                padding: 5px 10px;
                font-size: 11px;
            }

            .form-control,
            .form-select {
                font-size: 12px;
                padding: 5px 8px;
            }

            .badge {
                font-size: 10px;
                padding: 3px 6px;
            }

            .btn-sm {
                padding: 4px 8px;
                font-size: 10px;
            }

            .input-group-text {
                padding: 5px 10px;
                font-size: 12px;
            }

            .table-container {
                margin: 0 -8px;
                padding: 0 8px;
            }

            .table th button {
                font-size: 10px;
                padding: 3px 8px;
            }

            .card h6 {
                font-size: 12px;
            }
        }
    </style>

    @script
    <script>
        // showAlert غیرفعال شده - دیگر نمایش داده نمی‌شود
    </script>
    @endscript

    <!-- آلارم برای عدم وجود گزارش -->
    @if($patternReportWarning)
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="position: fixed; top: 70px; right: 20px; z-index: 1050; min-width: 300px; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ $patternReportWarning }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" wire:click="$set('patternReportWarning', null)"></button>
        </div>
    @endif

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h4 class="mb-0" style="flex: 1; min-width: 200px;">
                <i class="fas fa-calendar-times text-danger"></i>
                <span class="d-none d-md-inline">اقامت‌گران با سررسید گذشته (امروز و قبل از امروز)</span>
                <span class="d-md-none">سررسید گذشته</span>
            </h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="badge bg-danger">
                    {{ $residents->total() }} مورد
                </div>
                @if(!empty($selectedResidents) && count($selectedResidents) > 0)
                    <div class="badge bg-primary">
                        {{ count($selectedResidents) }} انتخاب شده
                    </div>
                @endif
            </div>
        </div>

        <!-- بخش انتخاب الگو و ارسال -->
        @if(!empty($selectedResidents) && count($selectedResidents) > 0)
            <div class="card mb-3" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-paper-plane text-primary"></i>
                        ارسال پیامک به {{ count($selectedResidents) }} نفر انتخاب شده
                    </h6>
                    
                    <div class="row g-3">
                        <!-- انتخاب الگو -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                <i class="fas fa-file-alt"></i>
                                انتخاب الگو
                            </label>
                            <select 
                                wire:model="selectedPattern" 
                                class="form-select"
                                style="font-size: 14px;"
                            >
                                <option value="">-- انتخاب الگو --</option>
                                @foreach($patterns as $pattern)
                                    <option value="{{ $pattern->id }}">
                                        {{ $pattern->title }}
                                    </option>
                                @endforeach
                            </select>
                            @if($selectedPattern)
                                @php
                                    $selectedPatternObj = \App\Models\Pattern::find($selectedPattern);
                                @endphp
                                @if($selectedPatternObj)
                                    <div class="mt-2 p-2 bg-light rounded" style="font-size: 12px;">
                                        <strong>پیش‌نمایش:</strong><br>
                                        {{ \Illuminate\Support\Str::limit($selectedPatternObj->text, 100) }}
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- دکمه ارسال -->
                        <div class="col-12 col-md-6 d-flex align-items-end">
                            <button 
                                type="button"
                                onclick="startSendingProcess()"
                                wire:loading.attr="disabled"
                                wire:target="startSending,sendPatternSms"
                                wire:disabled="{{ !$this->canSend ? 'true' : 'false' }}"
                                class="btn btn-success w-100"
                                id="send-sms-btn"
                                style="
                                    @if(!$this->canSend)
                                        opacity: 0.6; 
                                        cursor: not-allowed;
                                        background: #6c757d;
                                    @else
                                        cursor: pointer; 
                                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                        border: none; 
                                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); 
                                        transition: all 0.3s ease;
                                    @endif
                                "
                                @if($this->canSend)
                                    onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.6)';"
                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)';"
                                @endif
                            >
                                <span wire:loading.remove wire:target="sendPatternSms">
                                    <i class="fas fa-paper-plane"></i>
                                    ارسال
                                </span>
                                <span wire:loading wire:target="sendPatternSms">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    در حال ارسال...
                                </span>
                            </button>
                        </div>
                        
                        <script>
                            function updateSendButton() {
                                const btn = document.getElementById('send-sms-btn');
                                if (!btn) return;
                                
                                // بررسی از طریق Livewire
                                @this.get('selectedResidents').then(selectedResidents => {
                                    @this.get('selectedPattern').then(selectedPattern => {
                                        const hasSelection = Array.isArray(selectedResidents) && selectedResidents.length > 0;
                                        const hasPattern = selectedPattern !== null && selectedPattern !== '';
                                        const canSend = hasSelection && hasPattern;
                                        
                                        if (canSend) {
                                            btn.disabled = false;
                                            btn.removeAttribute('disabled');
                                            btn.style.opacity = '1';
                                            btn.style.cursor = 'pointer';
                                            btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                                            btn.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
                                        } else {
                                            btn.disabled = true;
                                            btn.setAttribute('disabled', 'disabled');
                                            btn.style.opacity = '0.6';
                                            btn.style.cursor = 'not-allowed';
                                            btn.style.background = '#6c757d';
                                            btn.style.boxShadow = 'none';
                                        }
                                    });
                                });
                            }
                            
                            document.addEventListener('livewire:init', () => {
                                // به‌روزرسانی دکمه بعد از هر تغییر
                                Livewire.hook('morph.updated', () => {
                                    setTimeout(updateSendButton, 100);
                                });
                                
                                Livewire.on('updateSendButton', () => {
                                    setTimeout(updateSendButton, 100);
                                });
                                
                                // به‌روزرسانی اولیه
                                setTimeout(updateSendButton, 500);
                            });
                            
                            // به‌روزرسانی بعد از هر تغییر در checkbox یا select
                            document.addEventListener('change', (e) => {
                                if (e.target.matches('input[type="checkbox"]') || e.target.matches('select')) {
                                    setTimeout(updateSendButton, 200);
                                }
                            });
                        </script>
                    </div>

                    <!-- Progress Bar -->
                    @if($isSending)
                        <div class="mt-3">
                            <div class="progress" style="height: 25px;">
                                @php
                                    $progressPercent = $sendingProgress['total'] > 0 
                                        ? ($sendingProgress['sent'] + $sendingProgress['failed']) / $sendingProgress['total'] * 100 
                                        : 0;
                                @endphp
                                <div 
                                    class="progress-bar progress-bar-striped progress-bar-animated" 
                                    role="progressbar" 
                                    style="width: {{ $progressPercent }}%"
                                >
                                    {{ $sendingProgress['sent'] + $sendingProgress['failed'] }} / {{ $sendingProgress['total'] }}
                                </div>
                            </div>
                            <div class="mt-2 text-center" style="font-size: 12px;">
                                <span class="badge bg-success">{{ $sendingProgress['sent'] }} موفق</span>
                                <span class="badge bg-danger">{{ $sendingProgress['failed'] }} ناموفق</span>
                                @if($sendingProgress['current'])
                                    <span class="badge bg-info">در حال ارسال: {{ $sendingProgress['current'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- جستجو -->
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    type="text" 
                    class="form-control" 
                    placeholder="جستجو بر اساس نام یا تلفن..."
                    wire:model.live.debounce.300ms="search"
                >
            </div>
        </div>

        <!-- جدول -->
        <div class="table-responsive table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 120px;">
                            <button 
                                type="button"
                                wire:click="toggleSelectAll"
                                wire:key="select-all-button"
                                class="btn btn-sm"
                                style="
                                    background: {{ $selectAll ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' }};
                                    color: white;
                                    border: none;
                                    border-radius: 20px;
                                    padding: 6px 16px;
                                    font-size: 12px;
                                    font-weight: 600;
                                    cursor: pointer;
                                    transition: all 0.3s ease;
                                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 6px;
                                "
                                onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.25)';"
                                onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)';"
                            >
                                <i class="fas {{ $selectAll ? 'fa-check-square' : 'fa-square' }}"></i>
                                <span>{{ $selectAll ? 'لغو انتخاب همه' : 'انتخاب همه' }}</span>
                            </button>
                        </th>
                        <th>ردیف</th>
                        <th>نام</th>
                        <th>تلفن</th>
                        <th>واحد</th>
                        <th>اتاق</th>
                        <th>تخت</th>
                        <th>تاریخ سررسید پرداخت</th>
                        <th>روزهای گذشته از سررسید</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($residents as $index => $resident)
                        @php
                            $disabledInfo = $this->isResidentDisabled($resident);
                            $isDisabled = $disabledInfo['disabled'];
                            $disabledReason = $disabledInfo['reason'];
                        @endphp
                        <tr style="{{ $isDisabled ? 'opacity: 0.5; background-color: #f8f9fa;' : '' }}" 
                            title="{{ $isDisabled ? $disabledReason : '' }}">
                            <td>
                                @if($isDisabled)
                                    <input 
                                        type="checkbox" 
                                        disabled
                                        style="cursor: not-allowed; opacity: 0.5;"
                                    >
                                @else
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selectedResidents"
                                        value="{{ $resident->id }}"
                                        wire:key="resident-checkbox-{{ $resident->id }}-{{ $loop->index }}"
                                        style="cursor: pointer;"
                                    >
                                @endif
                            </td>
                            <td>{{ $residents->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $resident->resident_full_name ?? 'نامشخص' }}</strong>
                                @if($isDisabled)
                                    <br>
                                    <small class="text-danger" style="font-size: 11px;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $disabledReason }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $resident->resident_phone ?? '-' }}</td>
                            <td>{{ $resident->unit_name ?? '-' }}</td>
                            <td>{{ $resident->room_name ?? '-' }}</td>
                            <td>{{ $resident->bed_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-danger">
                                    {{ $resident->contract_payment_date_jalali ?? ($resident->contract_payment_date ? \Morilog\Jalali\Jalalian::fromCarbon($resident->contract_payment_date)->format('Y/m/d') : '-') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $daysPast = $this->getDaysPastDue($resident->contract_payment_date_jalali);
                                @endphp
                                @if($daysPast == 0)
                                    <span class="badge bg-warning" style="font-size: 13px;">
                                        امروز (0 روز)
                                    </span>
                                @elseif($daysPast == 1)
                                    <span class="badge bg-danger" style="font-size: 13px;">
                                        1 روز گذشته
                                    </span>
                                @else
                                    <span class="badge bg-danger" style="font-size: 13px;">
                                        {{ $daysPast }} روز گذشته
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>هیچ اقامت‌گری با سررسید گذشته یافت نشد</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- صفحه‌بندی (مشابه گزارش تخلفی) -->
        @if($residents->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small mb-2 mb-sm-0">
                    نمایش
                    {{ $residents->firstItem() ?? 0 }}
                    تا
                    {{ $residents->lastItem() ?? 0 }}
                    از
                    {{ $residents->total() }}
                    نتیجه
                </div>
                {{-- صفحه‌بندی سفارشی --}}
                <nav aria-label="Page navigation">
                    <ul class="pagination custom-pagination mb-0">
                        {{-- دکمه "قبلی" --}}
                        <li class="page-item {{ $residents->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="#" wire:click="previousPage()" tabindex="-1"
                                aria-disabled="{{ $residents->onFirstPage() ? 'true' : 'false' }}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>

                        {{-- شماره صفحات --}}
                        @foreach ($residents->getUrlRange(1, $residents->lastPage()) as $page => $url)
                            @if ($page == $residents->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="#"
                                        wire:click="gotoPage({{ $page }})">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- دکمه "بعدی" --}}
                        <li class="page-item {{ !$residents->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="#" wire:click="nextPage()"
                                aria-disabled="{{ !$residents->hasMorePages() ? 'true' : 'false' }}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    <!-- مدال پیشرفت ارسال پیام -->
    @if($showProgressModal || $isSending)
    <div class="modal fade show d-block" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="false" style="display: block !important; background: rgba(0,0,0,0.5); z-index: 9999;" wire:key="progress-modal-{{ $sendingProgress['total'] ?? 0 }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="progressModalLabel">
                        <i class="fas fa-paper-plane me-2"></i>
                        در حال ارسال پیام‌ها...
                    </h5>
                </div>
                <div class="modal-body">
                    <!-- آمار کلی -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="mb-2">آمار کلی</h6>
                                    <div class="d-flex justify-content-around">
                                        <div>
                                            <div class="text-muted small">کل پیام‌ها</div>
                                            <div class="h5 mb-0 text-primary">{{ $sendingProgress['total'] ?? 0 }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">ارسال شده</div>
                                            <div class="h5 mb-0 text-success">{{ $sendingProgress['sent'] ?? 0 }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">خطا</div>
                                            <div class="h5 mb-0 text-danger">{{ $sendingProgress['failed'] ?? 0 }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">مانده</div>
                                            <div class="h5 mb-0 text-warning">{{ ($sendingProgress['total'] ?? 0) - ($sendingProgress['sent'] ?? 0) - ($sendingProgress['failed'] ?? 0) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- نوار پیشرفت -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted">پیشرفت</span>
                            <span class="small text-muted">
                                {{ $sendingProgress['current_index'] ?? 0 }} از {{ $sendingProgress['total'] ?? 0 }}
                            </span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            @php
                                $progressPercent = ($sendingProgress['total'] ?? 0) > 0 
                                    ? ((($sendingProgress['sent'] ?? 0) + ($sendingProgress['failed'] ?? 0)) / ($sendingProgress['total'] ?? 1)) * 100 
                                    : 0;
                            @endphp
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 style="width: {{ $progressPercent }}%"
                                 aria-valuenow="{{ $progressPercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($progressPercent, 1) }}%
                            </div>
                        </div>
                    </div>

                    <!-- اقامت‌گر فعلی (فقط در حین ارسال) -->
                    @if(($sendingProgress['current'] ?? null) && !($sendingProgress['completed'] ?? false))
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div>
                                <strong>در حال ارسال به:</strong>
                                <div class="mt-1">{{ $sendingProgress['current'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- نتیجه ارسال (بعد از اتمام) -->
                    @if($sendingProgress['completed'] ?? false)
                    <div class="alert {{ ($sendingProgress['failed'] ?? 0) > 0 ? 'alert-warning' : 'alert-success' }} mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas {{ ($sendingProgress['failed'] ?? 0) > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }} me-2"></i>
                            <div style="width: 100%;">
                                <strong>نتیجه ارسال:</strong>
                                <div class="mt-1">{{ $sendingProgress['result_message'] ?? 'ارسال انجام شد' }}</div>
                                
                                <!-- نمایش جزئیات خطاها -->
                                @if(($sendingProgress['failed'] ?? 0) > 0 && !empty($sendingProgress['errors'] ?? []))
                                <div class="mt-3">
                                    <strong class="text-danger">جزئیات خطاها:</strong>
                                    <div class="mt-2" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($sendingProgress['errors'] as $error)
                                        <div class="card mb-2" style="background: #fff3cd; border: 1px solid #ffc107;">
                                            <div class="card-body p-2">
                                                <div class="small">
                                                    <strong class="text-danger">
                                                        <i class="fas fa-user"></i> {{ $error['resident_name'] ?? 'نامشخص' }}
                                                    </strong>
                                                    <br>
                                                    <span class="text-muted">
                                                        <i class="fas fa-phone"></i> {{ $error['phone'] ?? 'نامشخص' }}
                                                    </span>
                                                    <br>
                                                    <span class="text-danger mt-1 d-block">
                                                        <i class="fas fa-exclamation-circle"></i> 
                                                        <strong>خطا:</strong> {{ $error['error_message'] ?? 'خطای نامشخص' }}
                                                    </span>
                                                    @if(!empty($error['response_code']) && $error['response_code'] !== 'نامشخص')
                                                    <br>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-code"></i> 
                                                        <strong>کد پاسخ:</strong> {{ $error['response_code'] }}
                                                    </span>
                                                    @endif
                                                    @if(!empty($error['http_status_code']))
                                                    <br>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-server"></i> 
                                                        <strong>HTTP Status:</strong> {{ $error['http_status_code'] }}
                                                    </span>
                                                    @endif
                                                    @if(!empty($error['status']))
                                                    <br>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-info-circle"></i> 
                                                        <strong>وضعیت:</strong> {{ $error['status'] }}
                                                    </span>
                                                    @endif
                                                    @if(!empty($error['raw_response']) && strlen($error['raw_response']) < 200)
                                                    <br>
                                                    <span class="text-muted small" style="word-break: break-word;">
                                                        <i class="fas fa-file-alt"></i> 
                                                        <strong>پاسخ خام:</strong> {{ $error['raw_response'] }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if(!($sendingProgress['completed'] ?? false))
                    <button type="button" 
                            class="btn btn-danger" 
                            wire:click="cancelSending"
                            wire:loading.attr="disabled">
                        <i class="fas fa-times me-1"></i>
                        لغو ارسال
                    </button>
                    @else
                    <button type="button" 
                            class="btn btn-primary" 
                            wire:click="closeProgressModal"
                            wire:loading.attr="disabled">
                        <i class="fas fa-check me-1"></i>
                        بستن
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- قفل صفحه هنگام ارسال -->
    <div class="modal-backdrop fade show" style="z-index: 9998; pointer-events: all;"></div>
    @endif

    @script
    <script>
        // شروع فرآیند ارسال
        window.startSendingProcess = function() {
            // ابتدا مدال را نمایش بده
            @this.call('startSending').then(() => {
                // بعد از نمایش مدال، ارسال را شروع کن
                setTimeout(() => {
                    @this.call('sendPatternSms');
                }, 200);
            });
        };
        
        // گوش دادن به event برای باز/بسته کردن مدال و قفل صفحه
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-progress-modal', () => {
                // قفل صفحه
                document.body.style.overflow = 'hidden';
                
                // اطمینان از نمایش مدال Livewire
                setTimeout(() => {
                    const livewireModal = document.querySelector('[wire\\:key*="progress-modal"]');
                    if (livewireModal) {
                        livewireModal.style.display = 'block';
                        livewireModal.classList.add('show', 'd-block');
                    }
                }, 50);
            });
            
            Livewire.on('hide-progress-modal', () => {
                // باز کردن صفحه
                document.body.style.overflow = '';
            });
        });
    </script>
    @endscript
</div>
