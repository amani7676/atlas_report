<div>
    @section('title', 'اقامت‌گران با سررسید گذشته')

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

        /* Responsive */
        @media (max-width: 768px) {
            .custom-pagination .page-link {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
        }
    </style>

    @script
    <script>
        $wire.on('showAlert', (event) => {
            const { type, title, text } = event;
            let icon = 'info-circle';
            let bgColor = '#0d6efd';
            
            if (type === 'success') {
                icon = 'check-circle';
                bgColor = '#28a745';
            } else if (type === 'error') {
                icon = 'exclamation-circle';
                bgColor = '#dc3545';
            } else if (type === 'warning') {
                icon = 'exclamation-triangle';
                bgColor = '#ffc107';
            }
            
            // نمایش alert با SweetAlert2 یا alert ساده
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: title,
                    text: text,
                    confirmButtonText: 'باشه',
                    confirmButtonColor: bgColor
                });
            } else {
                alert(title + '\n\n' + text);
            }
        });
    </script>
    @endscript

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="fas fa-calendar-times text-danger"></i>
                اقامت‌گران با سررسید گذشته (امروز و قبل از امروز)
            </h4>
            <div class="d-flex align-items-center gap-3">
                <div class="badge bg-danger">
                    {{ $residents->total() }} مورد
                </div>
                @if($selectedCount > 0)
                    <div class="badge bg-primary">
                        {{ $selectedCount }} انتخاب شده
                    </div>
                @endif
            </div>
        </div>

        <!-- بخش انتخاب الگو و ارسال -->
        @if($selectedCount > 0)
            <div class="card mb-3" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-paper-plane text-primary"></i>
                        ارسال پیامک به {{ $selectedCount }} نفر انتخاب شده
                    </h6>
                    
                    <div class="row g-3">
                        <!-- انتخاب الگو -->
                        <div class="col-md-10">
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
                        <div class="col-md-2 d-flex align-items-end">
                            <button 
                                wire:click="sendPatternSms" 
                                wire:loading.attr="disabled"
                                class="btn btn-success w-100"
                                @if(!$selectedPattern) disabled @endif
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
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input 
                                type="checkbox" 
                                wire:click="toggleSelectAll"
                                {{ $selectAll ? 'checked' : '' }}
                                style="cursor: pointer;"
                            >
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
                                <input 
                                    type="checkbox" 
                                    wire:click="toggleSelectResident({{ $resident->id }})"
                                    {{ in_array($resident->id, $selectedResidents) ? 'checked' : '' }}
                                    {{ $isDisabled ? 'disabled' : '' }}
                                    style="cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }};"
                                >
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
</div>
