<div>
    <!-- استایل‌های خارجی -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- استایل‌های سفارشی برای صفحه‌بندی زیبا -->
    <style>
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

        /* استایل‌های ریسپانسیو برای موبایل */
        @media (max-width: 768px) {
            /* کاهش اندازه فونت‌ها و padding در موبایل */
            .card {
                margin-bottom: 0.75rem;
            }

            .card-body {
                padding: 0.75rem;
            }

            .card-header {
                padding: 0.5rem 0.75rem;
            }

            h5 {
                font-size: 1rem;
            }

            h6 {
                font-size: 0.875rem;
            }

            /* تنظیمات کارت‌های آماری */
            .stats-card h3 {
                font-size: 1.5rem;
            }

            /* تنظیمات جدول برای موبایل */
            .table-responsive {
                border-radius: 0.25rem;
            }

            .table th, .table td {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            /* بهبود نمایش فیلترها در موبایل */
            .col-md-2 {
                margin-bottom: 0.5rem;
            }

            /* تنظیمات صفحه‌بندی برای موبایل */
            .custom-pagination .page-link {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
                margin: 0 1px;
            }

            /* بهبود نمایش دکمه‌ها در موبایل */
            .btn-group-sm > .btn, .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            /* بهبود نمایش مودال در موبایل */
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            /* بهبود نمایش جستجو در موبایل */
            .input-group-sm {
                width: 100% !important;
            }

            /* تنظیمات عملیات گروهی در موبایل */
            .alert {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            /* بهبود نمایش بخش جستجوی اقامت‌گر */
            .position-absolute {
                z-index: 1000;
            }
        }

        /* استایل‌های خاص برای گوشی‌های کوچکتر */
        @media (max-width: 480px) {
            /* کاهش بیشتر اندازه فونت‌ها */
            .card-body {
                padding: 0.5rem;
            }

            h5 {
                font-size: 0.9rem;
            }

            h6 {
                font-size: 0.8rem;
            }

            /* تنظیمات جدول برای صفحه‌نمایش کوچک */
            .table th, .table td {
                padding: 0.3rem;
                font-size: 0.75rem;
            }

            /* بهبود نمایش کارت‌های آماری */
            .stats-card h3 {
                font-size: 1.25rem;
            }

            /* بهبود نمایش صفحه‌بندی */
            .custom-pagination .page-link {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }

            /* بهبود نمایش دکمه‌ها */
            .btn-group-sm > .btn, .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }

            /* بهبود نمایش مودال */
            .modal-body {
                padding: 0.75rem;
            }

            /* بهبود نمایش فیلترها */
            .form-select-sm, .form-control-sm {
                font-size: 0.75rem;
            }
        }
    </style>

    <div class="container-fluid py-3">
        <!-- بخش هدر و آمار کلی -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>
                    گزارش‌های ثبت‌شده اقامت‌گران
                </h5>
                <div class="d-flex flex-column flex-md-row">
                    {{-- نمایش مجموع نمرات منفی و تعداد کل گزارش‌ها --}}
                    <span class="badge bg-warning text-dark mb-1 mb-md-0 me-md-2">
                        <i class="fas fa-chart-line me-1"></i>
                        مجموع نمرات منفی: {{ $totalScore }}
                    </span>
                    <span class="badge bg-info">
                        <i class="fas fa-file-alt me-1"></i>
                        {{ $totalReportsCount }} گزارش
                    </span>
                </div>
            </div>
        </div>

        <!-- بخش کارت‌های آماری -->
        <div class="row mb-3">
            <div class="col-6 col-md-3">
                <div class="card bg-primary text-white stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">تعداد گزارش‌ها</h6>
                                <h3 class="mb-0">{{ $reports->total() }}</h3>
                            </div>
                            <i class="fas fa-file-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-danger text-white stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">مجموع نمرات منفی</h6>
                                <h3 class="mb-0">{{ $totalScore }}</h3>
                            </div>
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-success text-white stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">تعداد واحدها</h6>
                                <h3 class="mb-0">{{ count($units) }}</h3>
                            </div>
                            <i class="fas fa-building fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-warning text-dark stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">اقامت‌گران دارای گزارش</h6>
                                <h3 class="mb-0">{{ $distinctResidentsCount }}</h3>
                            </div>
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش جستجوی اقامت‌گر و اقامت‌گران برتر -->
        <div class="row mb-3">
            <!-- بخش جستجوی اقامت‌گران -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-search me-2"></i>جستجوی اقامت‌گر</h6>
                    </div>
                    <div class="card-body">
                        <!-- فیلد جستجو -->
                        <div class="mb-3 position-relative">
                            <input type="text" wire:model.live.debounce.300ms="residentSearch" class="form-control"
                                placeholder="نام اقامت‌گر را وارد کنید...">
                            @if (!$showResidentDetails && $residentSearch && count($residentsList) > 0)
                                <div class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-lg"
                                    style="z-index: 10; max-height: 200px; overflow-y: auto;">
                                    @foreach ($residentsList as $resident)
                                        <a href="#" wire:click="selectResident('{{ $resident }}')"
                                            class="d-block p-2 text-decoration-none hover-bg-light">
                                            {{ $resident }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if ($showResidentDetails && $selectedResident)
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">{{ $selectedResident }}</h5>
                                    <button wire:click="closeResidentDetails" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times"></i> بستن
                                    </button>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="card bg-light text-center">
                                            <div class="card-body p-2">
                                                <h6 class="card-title mb-1">تعداد گزارش‌ها</h6>
                                                <h4 class="mb-0 text-primary">{{ count($residentReports) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-light text-center">
                                            <div class="card-body p-2">
                                                <h6 class="card-title mb-1">مجموع نمرات منفی</h6>
                                                <h4 class="mb-0 text-danger">
                                                    {{ $residentReports->sum(function ($report) {return $report->report->negative_score ?? 0;}) }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-2">لیست گزارش‌ها:</h6>
                                <div style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>گزارش</th>
                                                <th>نمره</th>
                                                <th>توضیحات</th>
                                                <th>تاریخ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($residentReports as $report)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $report->report->title ?? 'حذف شده' }}</strong>
                                                            <br>
                                                            <small
                                                                class="text-muted">{{ $report->report->category->name ?? 'بدون دسته' }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-danger">
                                                            {{ $report->report->negative_score ?? 0 }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $report->notes }}</td>
                                                    <td>{{ jalaliDate($report->created_at, 'Y/m/d') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">گزارشی یافت نشد</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- بخش اقامت‌گران برتر -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-danger text-dark">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>اقامت‌گران برتر (بیشترین گزارش)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>اقامت‌گر</th>
                                        <th>تلفن</th>
                                        <th>موقعیت</th>
                                        <th>تعداد گزارش</th>
                                        <th>مجموع نمرات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    @forelse ($topResidents as $resident)
                                        <tr>
                                            <td>
                                                <span
                                                    class="badge rounded-pill bg-danger">{{ $counter++ }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $resident->resident_name }}</strong>
                                            </td>
                                            <td>
                                                {{ $resident->phone ?? 'ثبت نشده' }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-primary mb-1">{{ $resident->unit_name ?? 'واحد نامشخص' }}</span>
                                                <br>
                                                <small>
                                                    اتاق: {{ $resident->room_name ?? 'نامشخص' }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $resident->report_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $resident->total_score }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <p class="text-muted mb-0">اقامت‌گری یافت نشد</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش جستجو و فیلترهای اصلی -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <button class="btn btn-outline-primary btn-sm mb-2 mb-md-0" wire:click="$toggle('showFilters')">
                        <i class="fas fa-filter me-1"></i>
                        فیلترها {{ $showFilters ? '▼' : '▶' }}
                    </button>

                    <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                placeholder="جستجو در گزارش‌ها...">
                        </div>

                        <select wire:model.live="perPage" class="form-select form-select-sm">
                            <option value="10">10 در صفحه</option>
                            <option value="25">25 در صفحه</option>
                            <option value="50">50 در صفحه</option>
                            <option value="100">100 در صفحه</option>
                        </select>
                    </div>
                </div>

                @if ($showFilters)
                    <div class="row mt-3 g-2">
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">واحد</label>
                            <select wire:model.live="filters.unit_id" class="form-select form-select-sm">
                                <option value="">همه واحدها</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">اتاق</label>
                            <select wire:model.live="filters.room_id" class="form-select form-select-sm">
                                <option value="">همه اتاق‌ها</option>
                                @foreach ($filteredRooms as $room)
                                    <option value="{{ $room['id'] }}">{{ $room['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">دسته‌بندی</label>
                            <select wire:model.live="filters.category_id" class="form-select form-select-sm">
                                <option value="">همه دسته‌بندی‌ها</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">گزارش</label>
                            <select wire:model.live="filters.report_id" class="form-select form-select-sm">
                                <option value="">همه گزارش‌ها</option>
                                @foreach ($reportsList as $report)
                                    <option value="{{ $report->id }}">{{ $report->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">از تاریخ</label>
                            <input type="date" wire:model.live="filters.date_from"
                                class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">تا تاریخ</label>
                            <input type="date" wire:model.live="filters.date_to"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-12 mt-2">
                            <button wire:click="resetFilters" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i>حذف فیلترها
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- بخش عملیات گروهی -->
        @if (count($selectedReports) > 0)
            <div class="alert alert-warning mb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="mb-2 mb-md-0">
                        <strong>{{ count($selectedReports) }}</strong> گزارش انتخاب شده است
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-2">
                        <select wire:model="bulkAction" class="form-select form-select-sm">
                            <option value="">عملیات گروهی</option>
                            <option value="delete">حذف انتخاب‌شده‌ها</option>
                        </select>
                        <button wire:click="executeBulkAction" class="btn btn-danger btn-sm">
                            <i class="fas fa-play me-1"></i> اجرا
                        </button>
                        <button wire:click="$set('selectedReports', [])" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i> لغو
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- جدول اصلی گزارش‌ها -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>شماره</th>
                                <th wire:click="sortBy('resident_name')" style="cursor: pointer;">
                                    اقامت‌گر
                                    @if ($sortField === 'resident_name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th class="d-none d-md-table-cell">موقعیت</th>
                                <th>گزارش</th>
                                <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                    تاریخ ثبت
                                    @if ($sortField === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </th>
                                <th>نمره منفی</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter_number = 0; ?>
                            @forelse($reports as $report)
                                <?php $counter_number++; ?>
                                <tr>
                                    <td style="width: 1%; text-align: center;">
                                        <span class="badge rounded-pill bg-secondary text-white">
                                            {{ $counter_number }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $report->resident_name ?? 'نامشخص' }}</strong>
                                            @if ($report->resident_id)
                                                <br>
                                                <small class="text-muted">ID: {{ $report->resident_id }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <div>
                                            <span
                                                class="badge bg-primary mb-1">{{ $report->unit_name ?? 'واحد نامشخص' }}</span>
                                            <br>
                                            <small>
                                                اتاق: {{ $report->room_name ?? 'نامشخص' }}
                                                @if ($report->bed_name)
                                                    - تخت: {{ $report->bed_name }}
                                                @endif
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $report->report->title ?? 'گزارش حذف شده' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                دسته:
                                                <span class="badge bg-info">
                                                    {{ $report->report->category->name ?? 'بدون دسته' }}
                                                </span>
                                            </small>
                                            @if ($report->notes)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    {{ Str::limit($report->notes, 50) }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ jalaliDate($report->created_at, 'Y/m/d H:i') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $report->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger fs-6">
                                            {{ $report->report->negative_score ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-danger"
                                                onclick="confirmDeleteReport({{ $report->id }})" title="حذف گزارش">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-outline-info" data-bs-toggle="modal"
                                                data-bs-target="#reportDetails{{ $report->id }}"
                                                title="مشاهده جزئیات">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>

                                        <!-- مودال (پنجره پاپ‌آپ) جزئیات گزارش -->
                                        <div class="modal fade" id="reportDetails{{ $report->id }}"
                                            tabindex="-1">
                                            <div class="modal-dialog modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-info text-white">
                                                        <h5 class="modal-title">جزئیات گزارش</h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-12 col-md-6">
                                                                <p><strong>اقامت‌گر:</strong>
                                                                    {{ $report->resident_name }}</p>
                                                                <p><strong>واحد:</strong> {{ $report->unit_name }}</p>
                                                                <p><strong>اتاق:</strong> {{ $report->room_name }}</p>
                                                                <p><strong>تخت:</strong> {{ $report->bed_name }}</p>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <p><strong>گزارش:</strong>
                                                                    {{ $report->report->title ?? 'حذف شده' }}</p>
                                                                <p><strong>دسته‌بندی:</strong>
                                                                    {{ $report->report->category->name ?? 'بدون دسته' }}
                                                                </p>
                                                                <p><strong>نمره منفی:</strong> <span
                                                                        class="badge bg-danger">{{ $report->report->negative_score ?? 0 }}</span>
                                                                </p>
                                                                <p><strong>ضریب افزایش:</strong>
                                                                    {{ $report->report->increase_coefficient ?? 0 }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        @if ($report->notes)
                                                            <div class="mt-3">
                                                                <strong>توضیحات:</strong>
                                                                <div class="alert alert-light mt-2">
                                                                    {{ $report->notes }}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="mt-3">
                                                            <strong>اطلاعات ثبت:</strong>
                                                            <p class="mb-1">تاریخ ثبت:
                                                                {{ jalaliDate($report->created_at, 'Y/m/d H:i') }}</p>
                                                            <p class="mb-0">آخرین ویرایش:
                                                                {{ jalaliDate($report->updated_at, 'Y/m/d H:i') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">بستن</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">هیچ گزارشی یافت نشد</p>
                                        @if ($search || array_filter($filters))
                                            <button wire:click="resetFilters" class="btn btn-sm btn-outline-primary">
                                                حذف فیلترها
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- فوتر جدول با صفحه‌بندی زیبا و سفارشی -->
            @if ($reports->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted small mb-2 mb-sm-0">
                        نمایش
                        {{ $reports->firstItem() ?? 0 }}
                        تا
                        {{ $reports->lastItem() ?? 0 }}
                        از
                        {{ $reports->total() }}
                        نتیجه
                    </div>
                    {{-- صفحه‌بندی سفارشی --}}
                    <nav aria-label="Page navigation">
                        <ul class="pagination custom-pagination mb-0">
                            {{-- دکمه "قبلی" --}}
                            <li class="page-item {{ $reports->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="#" wire:click="previousPage()" tabindex="-1"
                                    aria-disabled="{{ $reports->onFirstPage() ? 'true' : 'false' }}">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>

                            {{-- شماره صفحات --}}
                            @foreach ($reports->getUrlRange(1, $reports->lastPage()) as $page => $url)
                                @if ($page == $reports->currentPage())
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
                            <li class="page-item {{ !$reports->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="#" wire:click="nextPage()"
                                    aria-disabled="{{ !$reports->hasMorePages() ? 'true' : 'false' }}">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>

    <!-- اسکریپت‌های خارجی -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDeleteReport(id) {
            Swal.fire({
                title: 'حذف گزارش',
                text: 'آیا مطمئن هستید که می‌خواهید این گزارش را حذف کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deleteReport(id);
                }
            });
        }

        window.addEventListener('confirmBulkDelete', event => {
            const {
                type,
                count
            } = event.detail;

            Swal.fire({
                title: `حذف ${count} گزارش`,
                text: `آیا مطمئن هستید که می‌خواهید ${count} گزارش انتخاب شده را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deleteMultipleReports();
                }
            });
        });

        document.addEventListener('livewire:navigated', () => {
            var modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                if (!bootstrap.Modal.getInstance(modal)) {
                    new bootstrap.Modal(modal);
                }
            });
        });
    </script>
</div>
