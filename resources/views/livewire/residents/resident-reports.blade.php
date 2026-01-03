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

        /* استایل‌های زیبا برای جدول گزارش‌ها */
        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .table tbody tr:hover:not(.selected-row) {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        /* استایل برای ردیف‌های انتخاب شده - باید بعد از hover باشد */
        .table tbody tr.selected-row {
            background-color: #CBF3BB !important;
        }
        
        .table tbody tr.selected-row td {
            background-color: #CBF3BB !important;
        }
        
        .table tbody tr.selected-row:hover {
            background-color: #B8E8A5 !important;
        }
        
        .table tbody tr.selected-row:hover td {
            background-color: #B8E8A5 !important;
        }
        
        /* اطمینان از اعمال رنگ برای ردیف‌های انتخاب شده */
        .table-sm tbody tr.selected-row,
        .table-hover tbody tr.selected-row {
            background-color: #CBF3BB !important;
        }
        
        .table-sm tbody tr.selected-row td,
        .table-hover tbody tr.selected-row td {
            background-color: #CBF3BB !important;
        }

        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 15px;
        }

        .table thead th:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .table tbody td {
            border-bottom: 1px solid #e5e7eb;
            padding: 15px;
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
        <!-- بخش جستجوی اقامت‌گر در بالای صفحه -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-search me-2"></i>جستجوی اقامت‌گر</h6>
            </div>
            <div class="card-body">
                <!-- فیلد جستجو -->
                <div class="position-relative">
                    <input type="text" wire:model.live.debounce.300ms="residentSearch" class="form-control"
                        placeholder="نام اقامت‌گر را وارد کنید...">
                    @if (!$showResidentModal && $residentSearch && count($residentsList) > 0)
                        <div class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-lg"
                            style="z-index: 10; max-height: 200px; overflow-y: auto;">
                            @foreach ($residentsList as $resident)
                                <a href="#" wire:click="selectResident('{{ $resident }}')"
                                    class="d-block p-2 text-decoration-none hover-bg-light"
                                    style="cursor: pointer; transition: background-color 0.2s;">
                                    <i class="fas fa-user me-2 text-primary"></i>{{ $resident }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- بخش هدر و آمار کلی -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    گزارش‌های تخلفی اقامت‌گران
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


        <!-- بخش جدول‌های اقامت‌گران -->
        <div class="row mb-3">
            <!-- جدول اقامت‌گران با تخلف‌های تکرارای یکسان -->
            <div class="col-12 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-redo me-2"></i>
                            اقامت‌گران با تخلف‌های تکرارای یکسان
                            <span class="badge bg-light text-dark ms-2">{{ $repeatViolationResidentsCount }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>اقامت‌گر</th>
                                        <th>نوع تخلف</th>
                                        <th>تعداد تکرار</th>
                                        <th>موقعیت</th>
                                        <th>بخشودگی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($repeatViolationResidents as $index => $resident)
                                        <tr>
                                            <td>
                                                <span class="badge rounded-pill bg-primary">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                @if(!empty($resident->resident_name))
                                                    <a href="#"
                                                       wire:click.prevent="filterByResident('{{ $resident->resident_name }}', {{ $resident->report_id }})"
                                                       class="text-decoration-none text-primary fw-bold"
                                                       style="cursor: pointer;"
                                                       title="مشاهده تخلف‌های تکرارای این اقامت‌گر">
                                                        <i class="fas fa-link me-1"></i>{{ $resident->resident_name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">نامشخص</span>
                                                @endif
                                                @if($resident->phone)
                                                    <br><small class="text-muted">{{ $resident->phone }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $resident->report_name ?? 'نامشخص' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $resident->repeat_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary mb-1">{{ $resident->unit_name ?? 'واحد نامشخص' }}</span>
                                                <br>
                                                <small class="text-muted">اتاق: {{ $resident->room_name ?? 'نامشخص' }}</small>
                                            </td>
                                            <td>
                                                @if(isset($resident->grants_total_count) && $resident->grants_total_count > 0)
                                                    <span class="badge bg-success">{{ $resident->grants_count ?? 0 }} فعال</span>
                                                    @if(($resident->grants_total_count ?? 0) > ($resident->grants_count ?? 0))
                                                        <span class="badge bg-secondary ms-1">{{ ($resident->grants_total_count ?? 0) - ($resident->grants_count ?? 0) }} غیرفعال</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">کل: {{ $resident->grants_total_count ?? 0 }} مورد | مجموع: {{ number_format($resident->grants_total ?? 0, 0) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                                <p class="mb-0">هیچ اقامت‌گری با تخلف تکرارای یکسان یافت نشد.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول اقامت‌گران با تعداد گزارش بالا -->
            <div class="col-12 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            اقامت‌گران با تعداد گزارش بالا
                            <span class="badge bg-light text-dark ms-2">{{ $countViolationResidentsCount }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>اقامت‌گر</th>
                                        <th>تعداد گزارش</th>
                                        <th>مجموع نمرات</th>
                                        <th>موقعیت</th>
                                        <th>بخشودگی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($countViolationResidents as $index => $resident)
                                        <tr>
                                            <td>
                                                <span class="badge rounded-pill bg-info">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                @if(!empty($resident->resident_name))
                                                    <a href="#"
                                                       wire:click.prevent="filterByResident('{{ $resident->resident_name }}')"
                                                       class="text-decoration-none text-info fw-bold"
                                                       style="cursor: pointer;"
                                                       title="مشاهده همه تخلف‌های این اقامت‌گر">
                                                        <i class="fas fa-link me-1"></i>{{ $resident->resident_name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">نامشخص</span>
                                                @endif
                                                @if($resident->phone)
                                                    <br><small class="text-muted">{{ $resident->phone }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $resident->report_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $resident->total_score }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary mb-1">{{ $resident->unit_name ?? 'واحد نامشخص' }}</span>
                                                <br>
                                                <small class="text-muted">اتاق: {{ $resident->room_name ?? 'نامشخص' }}</small>
                                            </td>
                                            <td>
                                                @if(isset($resident->grants_total_count) && $resident->grants_total_count > 0)
                                                    <span class="badge bg-success">{{ $resident->grants_count ?? 0 }} فعال</span>
                                                    @if(($resident->grants_total_count ?? 0) > ($resident->grants_count ?? 0))
                                                        <span class="badge bg-secondary ms-1">{{ ($resident->grants_total_count ?? 0) - ($resident->grants_count ?? 0) }} غیرفعال</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">کل: {{ $resident->grants_total_count ?? 0 }} مورد | مجموع: {{ number_format($resident->grants_total ?? 0, 0) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                                <p class="mb-0">هیچ اقامت‌گری با تعداد گزارش بالا یافت نشد.</p>
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

        <!-- بخش اقامت‌گران برتر -->
        <div class="row mb-3">
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
                                        <th>بخشودگی</th>
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
                                                @if(!empty($resident->resident_name))
                                                    <a href="#"
                                                       wire:click.prevent="filterByResident('{{ $resident->resident_name }}')"
                                                       class="text-decoration-none text-danger fw-bold"
                                                       style="cursor: pointer;"
                                                       title="مشاهده همه تخلف‌های این اقامت‌گر">
                                                        <i class="fas fa-link me-1"></i>{{ $resident->resident_name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">نامشخص</span>
                                                @endif
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
                                                <small class="d-block text-muted mt-1">(مجموع تخلف)</small>
                                            </td>
                                            <td>
                                                @if(isset($resident->grants_total_count) && $resident->grants_total_count > 0)
                                                    <span class="badge bg-success">{{ $resident->grants_count ?? 0 }} فعال</span>
                                                    @if(($resident->grants_total_count ?? 0) > ($resident->grants_count ?? 0))
                                                        <span class="badge bg-secondary ms-1">{{ ($resident->grants_total_count ?? 0) - ($resident->grants_count ?? 0) }} غیرفعال</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">کل: {{ $resident->grants_total_count ?? 0 }} مورد | مجموع: {{ number_format($resident->grants_total ?? 0, 0) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
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
        <div class="card mb-3" id="reports-list-section">
            <div class="card-header bg-light">
                @if($filterByResidentName)
                    <div class="alert alert-info mb-2 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-filter me-1"></i>
                                <strong>فیلتر فعال:</strong> نمایش گزارش‌های اقامت‌گر <strong>{{ $filterByResidentName }}</strong>
                            </span>
                            <button wire:click="clearResidentFilter" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i> حذف فیلتر
                            </button>
                        </div>
                    </div>
                @endif
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

        <!-- جدول اصلی گزارش‌های تخلفی -->
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
                                <tr style="transition: all 0.2s ease;">
                                    <td style="width: 1%; text-align: center; vertical-align: middle;">
                                        <span class="badge rounded-pill" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 13px; padding: 6px 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            {{ $counter_number }}
                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                {{ mb_substr($report->resident_name ?? 'ن', 0, 1) }}
                                            </div>
                                            <div>
                                                <strong style="font-size: 14px; color: #1f2937; display: block; margin-bottom: 3px;">{{ $report->resident_name ?? 'نامشخص' }}</strong>
                                                @if ($report->resident_id)
                                                    <small style="color: #6b7280; font-size: 11px;">
                                                        <i class="fas fa-id-card"></i> ID: {{ $report->resident_id }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell" style="vertical-align: middle;">
                                        <div>
                                            <span class="badge" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 5px 10px; margin-bottom: 5px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                <i class="fas fa-building"></i> {{ $report->unit_name ?? 'واحد نامشخص' }}
                                            </span>
                                            <br>
                                            <div style="margin-top: 5px; font-size: 12px; color: #6b7280;">
                                                <i class="fas fa-door-open"></i> اتاق: {{ $report->room_name ?? 'نامشخص' }}
                                                @if ($report->bed_name)
                                                    <br>
                                                    <i class="fas fa-bed"></i> تخت: {{ $report->bed_name }}
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="padding: 8px; background: #f8f9fa; border-radius: 6px; border-right: 3px solid #3b82f6;">
                                            <strong style="font-size: 14px; color: #1f2937; display: block; margin-bottom: 5px;">
                                                {{ $report->report->title ?? 'گزارش حذف شده' }}
                                            </strong>
                                            <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                                                <span class="badge" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; padding: 3px 8px; font-size: 11px;">
                                                    <i class="fas fa-tag"></i> {{ $report->report->category->name ?? 'بدون دسته' }}
                                                </span>
                                            </div>
                                            @if ($report->notes)
                                                <div style="margin-top: 5px; padding: 5px; background: white; border-radius: 4px; border: 1px solid #e5e7eb;">
                                                    <small style="color: #6b7280; font-size: 11px;">
                                                        <i class="fas fa-sticky-note"></i> {{ Str::limit($report->notes, 50) }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="text-align: center;">
                                            <div style="font-size: 13px; font-weight: 600; color: #1f2937; margin-bottom: 3px;">
                                                {{ jalaliDate($report->created_at, 'Y/m/d') }}
                                            </div>
                                            <div style="font-size: 11px; color: #6b7280;">
                                                <i class="fas fa-clock"></i> {{ jalaliDate($report->created_at, 'H:i') }}
                                            </div>
                                            <small style="color: #9ca3af; font-size: 10px; display: block; margin-top: 3px;">
                                                {{ $report->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle; text-align: center;">
                                        <span class="badge" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; font-size: 14px; padding: 6px 12px; border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <i class="fas fa-minus-circle"></i> {{ $report->report->negative_score ?? 0 }}
                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-danger" style="border-radius: 6px 0 0 6px;"
                                                onclick="confirmDeleteReport({{ $report->id }})" title="حذف گزارش">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info" style="border-radius: 0 6px 6px 0;"
                                                onclick="return openReportModal({{ $report->id }});"
                                                title="مشاهده جزئیات">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>

                                        <!-- مودال (پنجره پاپ‌آپ) جزئیات گزارش -->
                                        <div class="modal fade" id="reportDetails{{ $report->id }}"
                                            tabindex="-1" wire:ignore>
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

        // تابع برای باز کردن modal گزارش
        // تابع برای باز کردن modal گزارش - در scope global
        window.openReportModal = function(reportId) {
            const modalElement = document.getElementById('reportDetails' + reportId);
            if (modalElement) {
                // اگر modal قبلاً initialize شده، از همان استفاده کن
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    // اگر initialize نشده، جدید بساز
                    modal = new bootstrap.Modal(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                }
                // فقط modal را نشان بده
                modal.show();
            }
            return false; // جلوگیری از default behavior
        }

        // اسکرول به بخش لیست گزارش‌ها هنگام فیلتر کردن
        document.addEventListener('livewire:init', () => {
            Livewire.on('scrollToReports', () => {
                setTimeout(() => {
                    const reportsSection = document.getElementById('reports-list-section');
                    if (reportsSection) {
                        reportsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
            });
        });
    </script>

    <!-- مدال نمایش جزئیات اقامت‌گر -->
    @if($showResidentModal && $selectedResidentData)
    <div class="modal fade show" id="residentModal" tabindex="-1" aria-labelledby="residentModalLabel" aria-hidden="false" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="residentModalLabel">
                        <i class="fas fa-user me-2"></i>
                        جزئیات اقامت‌گر: {{ $selectedResidentData->resident_full_name ?? 'نامشخص' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeResidentModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- اطلاعات اصلی اقامت‌گر -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>اطلاعات اصلی</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <strong>نام کامل:</strong>
                                            <div>{{ $selectedResidentData->resident_full_name ?? 'نامشخص' }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>شماره تلفن:</strong>
                                            <div style="direction: ltr; text-align: right;">{{ $selectedResidentData->resident_phone ?? '-' }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>سن:</strong>
                                            <div>{{ $selectedResidentData->resident_age ?? '-' }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>تاریخ تولد:</strong>
                                            <div>{{ $selectedResidentData->resident_birth_date ? jalaliDate($selectedResidentData->resident_birth_date, 'Y/m/d') : '-' }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>شغل:</strong>
                                            <div>{{ $selectedResidentData->resident_job ?? '-' }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>منبع معرفی:</strong>
                                            <div>{{ $selectedResidentData->resident_referral_source ?? '-' }}</div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>فرم:</strong>
                                            <div>
                                                @if($selectedResidentData->resident_form)
                                                    <span class="badge bg-success">دارد</span>
                                                @else
                                                    <span class="badge bg-danger">ندارد</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>مدرک:</strong>
                                            <div>
                                                @if($selectedResidentData->resident_document)
                                                    <span class="badge bg-success">دارد</span>
                                                @else
                                                    <span class="badge bg-danger">ندارد</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اطلاعات موقعیت -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>موقعیت</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <strong>واحد:</strong>
                                            <div>
                                                <span class="badge bg-primary">{{ $selectedResidentData->unit_name ?? 'نامشخص' }}</span>
                                                @if($selectedResidentData->unit_code)
                                                    <small class="text-muted">({{ $selectedResidentData->unit_code }})</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>اتاق:</strong>
                                            <div>
                                                <span class="badge bg-info">{{ $selectedResidentData->room_name ?? 'نامشخص' }}</span>
                                                @if($selectedResidentData->room_code)
                                                    <small class="text-muted">({{ $selectedResidentData->room_code }})</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>تخت:</strong>
                                            <div>
                                                <span class="badge bg-warning text-dark">{{ $selectedResidentData->bed_name ?? 'نامشخص' }}</span>
                                                @if($selectedResidentData->bed_code)
                                                    <small class="text-muted">({{ $selectedResidentData->bed_code }})</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اطلاعات قرارداد -->
                    @if($selectedResidentData->contract_start_date || $selectedResidentData->contract_end_date)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>اطلاعات قرارداد</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <strong>تاریخ شروع:</strong>
                                            <div>{{ $selectedResidentData->contract_start_date ? jalaliDate($selectedResidentData->contract_start_date, 'Y/m/d') : '-' }}</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>تاریخ پایان:</strong>
                                            <div>{{ $selectedResidentData->contract_end_date ? jalaliDate($selectedResidentData->contract_end_date, 'Y/m/d') : '-' }}</div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>وضعیت قرارداد:</strong>
                                            <div>
                                                @if($selectedResidentData->contract_is_active ?? true)
                                                    <span class="badge bg-success">فعال</span>
                                                @else
                                                    <span class="badge bg-danger">غیرفعال</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- آمار و گزارش‌ها -->
                    <div class="row mb-4">
                        <div class="col-12 col-md-4 mb-3">
                            <div class="card bg-light text-center">
                                <div class="card-body">
                                    <h6 class="card-title mb-1">تعداد گزارش‌ها</h6>
                                    <h4 class="mb-0 text-primary">{{ count($residentReports) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <div class="card bg-light text-center">
                                <div class="card-body">
                                    <h6 class="card-title mb-1">مجموع نمرات منفی</h6>
                                    <h4 class="mb-0 text-danger">{{ $this->getResidentTotalNegativeScore() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <div class="card bg-light text-center">
                                <div class="card-body">
                                    <h6 class="card-title mb-1">تعداد بخشودگی‌ها</h6>
                                    <h4 class="mb-0 text-success">{{ count($selectedResidentGrants) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- دکمه‌های عملیات -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div>
                                @if($grantCheckError)
                                    <div style="margin-bottom: 12px; padding: 12px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; color: #92400e; text-align: center; font-weight: 500;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ $grantCheckError }}
                                    </div>
                                @endif
                                <div class="d-flex gap-2 flex-wrap">
                                    @if(!$showGrantForm)
                                        <button type="button" wire:click="openGrantForm" class="btn btn-success">
                                            <i class="fas fa-gift me-1"></i> ثبت بخشودگی
                                        </button>
                                    @endif
                                <button type="button" 
                                        class="btn btn-sm btn-primary"
                                        wire:click="checkAllReports"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="checkAllReports">
                                        <i class="fas fa-check-square me-1"></i>چک همه گزارش‌ها
                                    </span>
                                    <span wire:loading wire:target="checkAllReports">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        ...
                                    </span>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-danger"
                                        wire:click="uncheckAllReports"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uncheckAllReports">
                                        <i class="fas fa-square me-1"></i>لغو چک همه
                                    </span>
                                    <span wire:loading wire:target="uncheckAllReports">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        ...
                                    </span>
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- فرم ثبت بخشودگی -->
                    @if($showGrantForm)
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-gift me-2"></i>
                                    ثبت بخشودگی جدید
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeGrantForm">
                                    <i class="fas fa-times"></i> بستن
                                </button>
                            </div>
                            
                            <form wire:submit.prevent="saveGrant">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="grantAmount" class="form-label">مقدار بخشودگی <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control @error('grantAmount') is-invalid @enderror" 
                                               id="grantAmount"
                                               wire:model="grantAmount"
                                               step="0.01"
                                               min="0"
                                               placeholder="مقدار بخشودگی را وارد کنید">
                                        @error('grantAmount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="grantDate" class="form-label">تاریخ بخشودگی</label>
                                        <input type="date" 
                                               class="form-control @error('grantDate') is-invalid @enderror" 
                                               id="grantDate"
                                               wire:model="grantDate">
                                        @error('grantDate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="grantDescription" class="form-label">توضیحات</label>
                                        <textarea 
                                              class="form-control" 
                                              id="grantDescription"
                                              wire:model="grantDescription"
                                              rows="3"
                                              placeholder="توضیحات (اختیاری)"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" wire:click="closeGrantForm">انصراف</button>
                                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveGrant">
                                            <i class="fas fa-save me-1"></i>ذخیره
                                        </span>
                                        <span wire:loading wire:target="saveGrant">
                                            <span class="spinner-border spinner-border-sm me-1"></span>
                                            در حال ذخیره...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- لیست بخشودگی‌ها -->
                    @if(count($selectedResidentGrants) > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-gift me-2"></i>لیست بخشودگی‌ها</h6>
                        </div>
                        <div class="card-body p-0">
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>مقدار</th>
                                            <th>تاریخ</th>
                                            <th>توضیحات</th>
                                            <th>وضعیت</th>
                                            <th width="100">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedResidentGrants as $grant)
                                            <tr style="background-color: {{ $grant->is_active ? '#e8f5e9' : '#f5f5f5' }};">
                                                <td>{{ number_format($grant->amount, 0) }}</td>
                                                <td>{{ $grant->grant_date ? jalaliDate($grant->grant_date, 'Y/m/d') : '-' }}</td>
                                                <td>{{ $grant->description ?? '-' }}</td>
                                                <td>
                                                    @if($grant->is_active)
                                                        <span class="badge bg-success">فعال</span>
                                                    @else
                                                        <span class="badge bg-secondary">غیرفعال</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm"
                                                            wire:click="deleteGrant({{ $grant->id }})"
                                                            wire:confirm="آیا مطمئن هستید که می‌خواهید این بخشودگی را حذف کنید؟"
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- لیست گزارش‌های تخلفی -->
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                لیست گزارش‌ها:
                                <span class="badge bg-light text-dark ms-2">
                                    {{ $this->checkedReportsCount }} از {{ $this->residentReportsCount }}
                                </span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
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
                                            @php
                                                $isChecked = (bool)($report->is_checked ?? false);
                                                
                                                // بررسی اینکه آیا تاریخ گزارش بعد از تاریخ‌های بخشودگی است
                                                $reportDate = \Carbon\Carbon::parse($report->created_at);
                                                $isAfterGrant = false;
                                                
                                                if (count($selectedResidentGrants) > 0) {
                                                    foreach ($selectedResidentGrants as $grant) {
                                                        if ($grant->grant_date) {
                                                            $grantDate = \Carbon\Carbon::parse($grant->grant_date);
                                                            if ($reportDate->isAfter($grantDate)) {
                                                                $isAfterGrant = true;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <tr wire:key="resident-report-{{ $report->id }}" 
                                                @if($isChecked)
                                                    style="background-color: #CBF3BB !important; transition: background-color 0.2s ease;"
                                                    class="selected-row"
                                                @else
                                                    style="background-color: transparent; transition: background-color 0.2s ease;"
                                                @endif>
                                                <td>
                                                    <div>
                                                        <strong>{{ $report->report->title ?? 'حذف شده' }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $report->report->category->name ?? 'بدون دسته' }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        {{ $report->report->negative_score ?? 0 }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ Str::limit($report->notes ?? '-', 50) }}</small>
                                                </td>
                                                <td>
                                                    @if($isAfterGrant)
                                                        <small style="color: #76153C; font-weight: bold;">
                                                            {{ jalaliDate($report->created_at, 'Y/m/d H:i') }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted">
                                                            {{ jalaliDate($report->created_at, 'Y/m/d H:i') }}
                                                        </small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                                    <p class="mb-0">هیچ گزارشی یافت نشد.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeResidentModal">
                        <i class="fas fa-times me-1"></i>بستن
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

