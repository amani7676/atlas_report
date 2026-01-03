<div>
    @section('title', 'ارسال گروهی پیامک')

    <!-- Bootstrap CSS برای Pagination -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Material Design Variables */
        :root {
            --mdc-primary: #4361ee;
            --mdc-primary-dark: #3a0ca3;
            --mdc-primary-light: #6b7fef;
            --mdc-secondary: #4cc9f0;
            --mdc-surface: #ffffff;
            --mdc-background: #f5f7fb;
            --mdc-on-surface: #212529;
            --mdc-on-primary: #ffffff;
            --mdc-error: #f72585;
            --mdc-success: #28a745;
            --mdc-warning: #ff9e00;
            --mdc-shadow-1: 0 2px 4px rgba(0,0,0,0.1);
            --mdc-shadow-2: 0 4px 8px rgba(0,0,0,0.12);
            --mdc-shadow-3: 0 8px 16px rgba(0,0,0,0.15);
            --mdc-shadow-4: 0 12px 24px rgba(0,0,0,0.18);
        }

        /* Material Card */
        .mdc-card {
            background: var(--mdc-surface);
            border-radius: 16px;
            box-shadow: var(--mdc-shadow-2);
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mdc-card:hover {
            box-shadow: var(--mdc-shadow-3);
        }

        /* Material Typography */
        .mdc-headline-4 {
            font-size: 28px;
            font-weight: 500;
            letter-spacing: -0.5px;
            color: var(--mdc-on-surface);
            margin: 0 0 24px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mdc-headline-5 {
            font-size: 20px;
            font-weight: 500;
            color: var(--mdc-on-surface);
            margin: 0 0 16px 0;
        }

        .mdc-body-1 {
            font-size: 14px;
            line-height: 1.5;
            color: var(--mdc-on-surface);
        }

        /* Material Button */
        .mdc-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 24px;
            border: none;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: var(--mdc-shadow-1);
        }

        .mdc-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .mdc-button:active::before {
            width: 300px;
            height: 300px;
        }

        .mdc-button--raised {
            background: var(--mdc-primary);
            color: var(--mdc-on-primary);
        }

        .mdc-button--raised:hover {
            background: var(--mdc-primary-dark);
            box-shadow: var(--mdc-shadow-2);
            transform: translateY(-2px);
        }

        .mdc-button--outlined {
            background: transparent;
            color: var(--mdc-primary);
            border: 1px solid var(--mdc-primary);
            box-shadow: none;
        }

        .mdc-button--outlined:hover {
            background: rgba(67, 97, 238, 0.08);
            box-shadow: var(--mdc-shadow-1);
        }

        .mdc-button--text {
            background: transparent;
            color: var(--mdc-primary);
            box-shadow: none;
            padding: 8px 16px;
        }

        .mdc-button--text:hover {
            background: rgba(67, 97, 238, 0.08);
        }

        /* Material Chip/Badge */
        .mdc-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            background: rgba(67, 97, 238, 0.12);
            color: var(--mdc-primary);
        }

        .mdc-chip--primary {
            background: var(--mdc-primary);
            color: var(--mdc-on-primary);
        }

        .mdc-chip--success {
            background: rgba(40, 167, 69, 0.12);
            color: #28a745;
        }

        .mdc-chip--danger {
            background: rgba(247, 37, 133, 0.12);
            color: var(--mdc-error);
        }

        .mdc-chip--info {
            background: rgba(23, 162, 184, 0.12);
            color: #17a2b8;
        }

        /* Material Text Field */
        .mdc-text-field {
            position: relative;
            margin-bottom: 16px;
        }

        .mdc-text-field__input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 4px;
            font-size: 14px;
            background: var(--mdc-surface);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mdc-text-field__input:focus {
            outline: none;
            border-color: var(--mdc-primary);
            border-width: 2px;
            padding: 11px 15px;
        }

        .mdc-text-field__label {
            position: absolute;
            right: 16px;
            top: 12px;
            font-size: 14px;
            color: rgba(0, 0, 0, 0.6);
            pointer-events: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--mdc-surface);
            padding: 0 4px;
        }

        .mdc-text-field__input:focus + .mdc-text-field__label,
        .mdc-text-field__input:not(:placeholder-shown) + .mdc-text-field__label {
            top: -8px;
            font-size: 12px;
            color: var(--mdc-primary);
        }

        /* Material Select */
        .mdc-select {
            position: relative;
            margin-bottom: 16px;
        }

        .mdc-select__native-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 4px;
            font-size: 14px;
            background: var(--mdc-surface);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: left 16px center;
            padding-left: 40px;
        }

        .mdc-select__native-control:focus {
            outline: none;
            border-color: var(--mdc-primary);
            border-width: 2px;
            padding-left: 39px;
        }

        /* Material Table */
        .mdc-data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--mdc-surface);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--mdc-shadow-1);
        }

        .mdc-data-table__header-row {
            background: #f8f9fa;
        }

        .mdc-data-table__header-cell {
            padding: 16px;
            text-align: right;
            font-size: 12px;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.87);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.12);
        }

        .mdc-data-table__row {
            transition: background-color 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mdc-data-table__row:hover {
            background-color: rgba(67, 97, 238, 0.04);
        }

        .mdc-data-table__row--selected {
            background-color: rgba(67, 97, 238, 0.08);
        }

        .mdc-data-table__cell {
            padding: 16px;
            text-align: right;
            font-size: 14px;
            color: rgba(0, 0, 0, 0.87);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        /* Material Checkbox */
        .mdc-checkbox {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .mdc-checkbox__native-control {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .mdc-checkbox__background {
            position: absolute;
            top: 0;
            left: 0;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0, 0, 0, 0.54);
            border-radius: 2px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mdc-checkbox__native-control:checked + .mdc-checkbox__background {
            background-color: var(--mdc-primary);
            border-color: var(--mdc-primary);
        }

        .mdc-checkbox__checkmark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            color: white;
            font-size: 14px;
            transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mdc-checkbox__native-control:checked ~ .mdc-checkbox__checkmark {
            transform: translate(-50%, -50%) scale(1);
        }

        /* Floating Action Button */
        .mdc-fab {
            position: fixed;
            bottom: 24px;
            left: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--mdc-primary);
            color: var(--mdc-on-primary);
            border: none;
            box-shadow: var(--mdc-shadow-4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .mdc-fab:hover {
            box-shadow: var(--mdc-shadow-4);
            transform: scale(1.1);
        }

        .mdc-fab:active {
            transform: scale(0.95);
        }

        /* Filter Card */
        .filter-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: var(--mdc-shadow-1);
        }

        /* Stats Bar */
        .stats-bar {
            background: linear-gradient(135deg, var(--mdc-primary), var(--mdc-primary-dark));
            color: var(--mdc-on-primary);
            padding: 16px 24px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            box-shadow: var(--mdc-shadow-2);
        }

        .stats-bar__text {
            font-size: 16px;
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 64px 24px;
            color: rgba(0, 0, 0, 0.54);
        }

        .empty-state__icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state__text {
            font-size: 16px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Navigation Tabs */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }

        .nav-item {
            margin-bottom: -2px;
        }

        .nav-link {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            border: none;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
            cursor: pointer;
            font-weight: 400;
        }

        .nav-link:hover {
            color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05);
        }

        .nav-link.active {
            color: #0d6efd !important;
            border-bottom-color: #0d6efd;
            font-weight: 600;
            background-color: transparent;
        }

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
            .mdc-card {
                padding: 16px;
            }

            .mdc-headline-4 {
                font-size: 24px;
            }

            .breadcrumb {
                font-size: 14px;
            }

            .custom-pagination .page-link {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
        }
    </style>

    <div class="container-fluid py-3">
        <!-- Navigation Tabs (مشابه گزارش تخلفی) -->
        <ul class="nav nav-tabs mb-3" role="tablist" style="border-bottom: 2px solid #dee2e6;">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->is('residents/group-sms') ? 'active' : '' }}" 
                   href="/residents/group-sms"
                   style="color: {{ request()->is('residents/group-sms') ? '#0d6efd' : '#6c757d' }}; border-bottom: {{ request()->is('residents/group-sms') ? '2px solid #0d6efd' : 'none' }}; padding: 12px 20px; font-weight: {{ request()->is('residents/group-sms') ? '600' : '400' }};">
                    <i class="fas fa-paper-plane me-2"></i>
                    ارسال گروهی پیامک
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->is('sms/sent') ? 'active' : '' }}" 
                   href="/sms/sent"
                   style="color: {{ request()->is('sms/sent') ? '#0d6efd' : '#6c757d' }}; border-bottom: {{ request()->is('sms/sent') ? '2px solid #0d6efd' : 'none' }}; padding: 12px 20px; font-weight: {{ request()->is('sms/sent') ? '600' : '400' }};">
                    <i class="fas fa-history me-2"></i>
                    لیست پیام‌های ارسال شده
                </a>
            </li>
        </ul>

        <!-- Header Card (مشابه گزارش تخلفی) -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0">
                    <i class="fas fa-paper-plane me-2"></i>
                    ارسال گروهی پیامک
                </h5>
                <div class="d-flex flex-column flex-md-row gap-2">
                    @if($selectedCount > 0)
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ $selectedCount }} مورد انتخاب شده
                        </span>
                    @endif
                    @if(isset($filteredCount))
                        <span class="badge bg-info">
                            <i class="fas fa-users me-1"></i>
                            {{ $filteredCount }} اقامت‌گر فیلتر شده
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mdc-card fade-in">

        <!-- Filter Toggle Buttons -->
        <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
            <button 
                class="mdc-button mdc-button--outlined" 
                wire:click="$set('showFilters', {{ !$showFilters }})"
            >
                <i class="fas fa-{{ $showFilters ? 'eye-slash' : 'filter' }}"></i>
                {{ $showFilters ? 'مخفی کردن فیلترها' : 'نمایش فیلترها' }}
            </button>
            @if(count(array_filter($filters)))
                <button class="mdc-button mdc-button--text" wire:click="resetFilters" style="color: var(--mdc-error);">
                    <i class="fas fa-times"></i>
                    پاک کردن فیلترها
                </button>
            @endif
        </div>

        <!-- Filters -->
        @if($showFilters)
            <div class="filter-card fade-in">
                <h5 class="mdc-headline-5" style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <i class="fas fa-filter" style="color: var(--mdc-primary);"></i>
                    فیلترها
                </h5>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px;">
                    <!-- واحد -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.unit_name">
                            <option value="">همه واحدها</option>
                            @foreach($unitsList as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                        <label class="mdc-text-field__label">واحد</label>
                    </div>

                    <!-- اتاق -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.room_name">
                            <option value="">همه اتاق‌ها</option>
                            @foreach($roomsList as $room)
                                <option value="{{ $room }}">{{ $room }}</option>
                            @endforeach
                        </select>
                        <label class="mdc-text-field__label">اتاق</label>
                    </div>

                    <!-- تخت -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.bed_name">
                            <option value="">همه تخت‌ها</option>
                            @foreach($bedsList as $bed)
                                <option value="{{ $bed }}">{{ $bed }}</option>
                            @endforeach
                        </select>
                        <label class="mdc-text-field__label">تخت</label>
                    </div>

                    <!-- نام -->
                    <div class="mdc-text-field">
                        <input 
                            type="text" 
                            class="mdc-text-field__input" 
                            placeholder=" "
                            wire:model.live.debounce.300ms="filters.resident_full_name"
                        >
                        <label class="mdc-text-field__label">نام</label>
                    </div>

                    <!-- تلفن -->
                    <div class="mdc-text-field">
                        <input 
                            type="text" 
                            class="mdc-text-field__input" 
                            placeholder=" "
                            wire:model.live.debounce.300ms="filters.resident_phone"
                        >
                        <label class="mdc-text-field__label">تلفن</label>
                    </div>

                    <!-- مدرک -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.resident_document">
                            <option value="">همه</option>
                            <option value="1">دارد</option>
                            <option value="0">ندارد</option>
                        </select>
                        <label class="mdc-text-field__label">مدرک</label>
                    </div>

                    <!-- فرم -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.resident_form">
                            <option value="">همه</option>
                            <option value="1">دارد</option>
                            <option value="0">ندارد</option>
                        </select>
                        <label class="mdc-text-field__label">فرم</label>
                    </div>

                    <!-- وضعیت قرارداد -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.contract_state">
                            <option value="">همه</option>
                            <option value="active">فعال</option>
                            <option value="inactive">غیرفعال</option>
                        </select>
                        <label class="mdc-text-field__label">وضعیت قرارداد</label>
                    </div>

                    <!-- گذشته از سررسید -->
                    <div class="mdc-text-field">
                        <input 
                            type="number" 
                            class="mdc-text-field__input" 
                            placeholder=" "
                            wire:model.live.debounce.300ms="filters.payment_overdue_days"
                            min="0"
                        >
                        <label class="mdc-text-field__label">گذشته از سررسید (روز)</label>
                    </div>

                    <!-- وضعیت کاربر -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.resident_status">
                            <option value="">همه</option>
                            <option value="active">فعال</option>
                            <option value="exit">خروج</option>
                        </select>
                        <label class="mdc-text-field__label">وضعیت کاربر</label>
                    </div>

                    <!-- نوع نوت -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.notes_type">
                            <option value="">همه</option>
                            <option value="payment">پرداخت</option>
                            <option value="end_date">تاریخ انقضا</option>
                            <option value="exit">خروج</option>
                            <option value="demand">درخواست</option>
                            <option value="other">سایر</option>
                        </select>
                        <label class="mdc-text-field__label">نوع نوت</label>
                    </div>

                    <!-- بدهی -->
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="filters.has_debt">
                            <option value="">همه</option>
                            <option value="1">دارد</option>
                            <option value="0">ندارد</option>
                        </select>
                        <label class="mdc-text-field__label">بدهی</label>
                    </div>
                </div>
            </div>
        @endif

        <!-- Table -->
        <div style="overflow-x: auto; border-radius: 8px; box-shadow: var(--mdc-shadow-1);">
            <table class="mdc-data-table">
                <thead>
                    <tr class="mdc-data-table__header-row">
                        <th class="mdc-data-table__header-cell" style="width: 50px;">
                            <input 
                                type="checkbox" 
                                wire:click="toggleSelectAll"
                                {{ $selectAll ? 'checked' : '' }}
                                style="cursor: pointer; width: 18px; height: 18px;"
                            >
                        </th>
                        <th class="mdc-data-table__header-cell">ردیف</th>
                        <th class="mdc-data-table__header-cell">نام</th>
                        <th class="mdc-data-table__header-cell">تلفن</th>
                        <th class="mdc-data-table__header-cell">واحد</th>
                        <th class="mdc-data-table__header-cell">اتاق</th>
                        <th class="mdc-data-table__header-cell">تخت</th>
                        <th class="mdc-data-table__header-cell">مدرک</th>
                        <th class="mdc-data-table__header-cell">فرم</th>
                        <th class="mdc-data-table__header-cell">وضعیت قرارداد</th>
                        <th class="mdc-data-table__header-cell">تاریخ سررسید</th>
                        <th class="mdc-data-table__header-cell">وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($residents as $index => $resident)
                        <tr class="mdc-data-table__row {{ in_array($resident->id, $selectedResidents) ? 'mdc-data-table__row--selected' : '' }}">
                            <td class="mdc-data-table__cell">
                                <input 
                                    type="checkbox" 
                                    value="{{ $resident->id }}"
                                    wire:click="toggleSelectResident({{ $resident->id }})"
                                    {{ in_array($resident->id, $selectedResidents) ? 'checked' : '' }}
                                    style="cursor: pointer; width: 18px; height: 18px;"
                                >
                            </td>
                            <td class="mdc-data-table__cell">{{ $residents->firstItem() + $index }}</td>
                            <td class="mdc-data-table__cell">
                                <strong style="color: var(--mdc-on-surface);">{{ $resident->resident_full_name ?? 'نامشخص' }}</strong>
                            </td>
                            <td class="mdc-data-table__cell">{{ $resident->resident_phone ?? '-' }}</td>
                            <td class="mdc-data-table__cell">{{ $resident->unit_name ?? '-' }}</td>
                            <td class="mdc-data-table__cell">{{ $resident->room_name ?? '-' }}</td>
                            <td class="mdc-data-table__cell">{{ $resident->bed_name ?? '-' }}</td>
                            <td class="mdc-data-table__cell">
                                @if($resident->resident_document)
                                    <span class="mdc-chip mdc-chip--success">دارد</span>
                                @else
                                    <span class="mdc-chip">ندارد</span>
                                @endif
                            </td>
                            <td class="mdc-data-table__cell">
                                @if($resident->resident_form)
                                    <span class="mdc-chip mdc-chip--success">دارد</span>
                                @else
                                    <span class="mdc-chip">ندارد</span>
                                @endif
                            </td>
                            <td class="mdc-data-table__cell">
                                @if($resident->contract_state === 'active')
                                    <span class="mdc-chip mdc-chip--success">فعال</span>
                                @else
                                    <span class="mdc-chip">غیرفعال</span>
                                @endif
                            </td>
                            <td class="mdc-data-table__cell">
                                @if($resident->contract_payment_date_jalali)
                                    <span class="mdc-chip mdc-chip--info">
                                        {{ $resident->contract_payment_date_jalali }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="mdc-data-table__cell">
                                @if($resident->resident_deleted_at)
                                    <span class="mdc-chip mdc-chip--danger">خروج</span>
                                @else
                                    <span class="mdc-chip mdc-chip--success">فعال</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="empty-state">
                                <div class="empty-state__icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <div class="empty-state__text">هیچ اقامت‌گری یافت نشد</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination (مشابه گزارش تخلفی) -->
        @if($residents->hasPages())
            <div class="card mt-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted" style="font-size: 14px;">
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
            </div>
        @endif

        <!-- Pattern Selection Card -->
        <div class="mdc-card fade-in" style="margin-top: 24px;">
            <h5 class="mdc-headline-5" style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <i class="fas fa-envelope-open-text" style="color: var(--mdc-primary);"></i>
                ارسال پیام الگویی
            </h5>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <!-- انتخاب الگو -->
                <div class="mdc-select">
                    <select class="mdc-select__native-control" wire:model.live="selectedPattern">
                        <option value="">انتخاب الگو (اختیاری)</option>
                        @foreach($patterns as $pattern)
                            <option value="{{ $pattern->id }}">{{ $pattern->title }}</option>
                        @endforeach
                    </select>
                    <label class="mdc-text-field__label">الگوی پیام</label>
                </div>

                <!-- انتخاب شماره فرستنده -->
                @if($availableSenderNumbers->count() > 0)
                    <div class="mdc-select">
                        <select class="mdc-select__native-control" wire:model.live="selectedSenderNumberId">
                            <option value="">شماره فرستنده پیش‌فرض</option>
                            @foreach($availableSenderNumbers as $senderNumber)
                                <option value="{{ $senderNumber->id }}">{{ $senderNumber->number }} - {{ $senderNumber->name ?? 'بدون نام' }}</option>
                            @endforeach
                        </select>
                        <label class="mdc-text-field__label">شماره فرستنده</label>
                    </div>
                @endif
            </div>

            @if($selectedPattern)
                @php
                    $selectedPatternObj = $patterns->firstWhere('id', $selectedPattern);
                @endphp
                @if($selectedPatternObj)
                    <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 16px; border-right: 4px solid var(--mdc-primary);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <strong style="color: var(--mdc-primary);">{{ $selectedPatternObj->title }}</strong>
                        </div>
                        <div style="color: rgba(0,0,0,0.7); font-size: 13px; line-height: 1.6;">
                            {{ $selectedPatternObj->text }}
                        </div>
                    </div>
                @endif
            @endif

            <!-- Progress Bar -->
            @if($isSending)
                <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-weight: 500;">در حال ارسال...</span>
                        <span style="font-size: 12px; color: rgba(0,0,0,0.6);">
                            {{ $sendingProgress['sent'] + $sendingProgress['failed'] }} / {{ $sendingProgress['total'] }}
                        </span>
                    </div>
                    <div style="background: #e0e0e0; height: 8px; border-radius: 4px; overflow: hidden;">
                        <div style="background: var(--mdc-primary); height: 100%; width: {{ $sendingProgress['total'] > 0 ? (($sendingProgress['sent'] + $sendingProgress['failed']) / $sendingProgress['total'] * 100) : 0 }}%; transition: width 0.3s;"></div>
                    </div>
                    @if($sendingProgress['current'])
                        <div style="margin-top: 8px; font-size: 12px; color: rgba(0,0,0,0.6);">
                            در حال ارسال به: <strong>{{ $sendingProgress['current'] }}</strong>
                        </div>
                    @endif
                    <div style="margin-top: 8px; display: flex; gap: 16px; font-size: 12px;">
                        <span style="color: var(--mdc-success);">
                            <i class="fas fa-check-circle"></i> موفق: {{ $sendingProgress['sent'] }}
                        </span>
                        <span style="color: var(--mdc-error);">
                            <i class="fas fa-times-circle"></i> ناموفق: {{ $sendingProgress['failed'] }}
                        </span>
                    </div>
                </div>
            @endif

            <!-- Send Button Bar -->
            <div class="stats-bar fade-in" style="margin-top: 20px;">
                <div class="stats-bar__text">
                    <i class="fas fa-users" style="margin-left: 8px;"></i>
                    <strong>{{ $selectedCount }}</strong> اقامت‌گر انتخاب شده
                    @if($selectedPattern && $selectedCount > 0)
                        <span style="margin-right: 16px; color: rgba(255,255,255,0.8); font-size: 14px;">
                            (پیام الگویی ارسال می‌شود)
                        </span>
                    @endif
                </div>
                <div style="display: flex; gap: 12px;">
                    @if($selectedPattern)
                        @if($selectedCount > 0)
                            <button 
                                type="button"
                                onclick="startSendingProcess()"
                                wire:loading.attr="disabled"
                                class="mdc-button mdc-button--raised" 
                                style="background: var(--mdc-on-primary); color: var(--mdc-primary);"
                                @if($isSending) disabled @endif
                            >
                                <i class="fas fa-paper-plane"></i>
                                @if($isSending)
                                    در حال ارسال...
                                @else
                                    ارسال پیام الگویی به انتخاب شده‌ها
                                @endif
                            </button>
                        @else
                            <div style="padding: 10px 24px; background: rgba(255,255,255,0.2); border-radius: 24px; font-size: 14px;">
                                <i class="fas fa-info-circle"></i>
                                لطفاً حداقل یک اقامت‌گر را انتخاب کنید
                            </div>
                        @endif
                    @else
                        @if($selectedCount > 0)
                            <button 
                                class="mdc-button mdc-button--raised" 
                                wire:click="sendSms" 
                                style="background: var(--mdc-on-primary); color: var(--mdc-primary);"
                            >
                                <i class="fas fa-paper-plane"></i>
                                ارسال پیامک (روش قدیمی)
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
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
                            <div>
                                <strong>نتیجه ارسال:</strong>
                                <div class="mt-1">{{ $sendingProgress['result_message'] ?? 'ارسال انجام شد' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- جزئیات پاسخ‌های API ملی پیامک -->
                    @if(!empty($sendResults))
                    <div class="mt-3">
                        <h6 class="mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            جزئیات پاسخ‌های API ملی پیامک:
                        </h6>
                        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px;">
                            @foreach($sendResults as $index => $result)
                            <div class="mb-3 p-3" style="background: {{ $result['success'] ? '#f0f9ff' : '#fff3cd' }}; border-radius: 5px; border-right: 3px solid {{ $result['success'] ? '#28a745' : '#f72585' }};">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong style="color: {{ $result['success'] ? '#28a745' : '#f72585' }};">
                                        {{ $index + 1 }}. {{ $result['resident_name'] }} ({{ $result['phone'] }})
                                    </strong>
                                    <span class="badge {{ $result['success'] ? 'bg-success' : 'bg-danger' }}">
                                        {{ $result['success'] ? '✓ موفق' : '✗ خطا' }}
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <strong>پیام:</strong> 
                                    <span style="color: {{ $result['success'] ? '#28a745' : '#f72585' }};">
                                        {{ $result['message'] ?? 'بدون پیام' }}
                                    </span>
                                </div>
                                
                                @if(isset($result['response_code']))
                                <div class="mb-2">
                                    <strong>کد پاسخ:</strong> 
                                    <code>{{ $result['response_code'] }}</code>
                                </div>
                                @endif
                                
                                @if(isset($result['rec_id']))
                                <div class="mb-2">
                                    <strong>RecId:</strong> 
                                    <code>{{ $result['rec_id'] }}</code>
                                </div>
                                @endif
                                
                                @if(isset($result['api_response']))
                                <div class="mb-2">
                                    <strong>پاسخ API:</strong>
                                    <pre style="background: #f8f9fa; padding: 8px; border-radius: 3px; font-size: 11px; margin-top: 5px; overflow-x: auto;">{{ is_array($result['api_response']) ? json_encode($result['api_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $result['api_response'] }}</pre>
                                </div>
                                @endif
                                
                                @if(isset($result['raw_response']))
                                <div class="mb-2">
                                    <strong>پاسخ خام (Raw Response):</strong>
                                    <pre style="background: #f8f9fa; padding: 8px; border-radius: 3px; font-size: 11px; margin-top: 5px; overflow-x: auto; max-height: 150px; overflow-y: auto;">{{ is_array($result['raw_response']) ? json_encode($result['raw_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $result['raw_response'] }}</pre>
                                </div>
                                @endif
                                
                                @if(isset($result['full_result']))
                                <details class="mt-2">
                                    <summary style="cursor: pointer; color: #0066cc; font-weight: bold;">
                                        <i class="fas fa-chevron-down me-1"></i>
                                        نمایش تمام اطلاعات نتیجه
                                    </summary>
                                    <pre style="background: #f8f9fa; padding: 8px; border-radius: 3px; font-size: 11px; margin-top: 5px; overflow-x: auto; max-height: 200px; overflow-y: auto;">{{ json_encode($result['full_result'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
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
        
        // مدیریت قفل صفحه هنگام نمایش مدال
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-progress-modal', () => {
                // بلافاصله قفل صفحه
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                
                // اطمینان از نمایش مدال
                setTimeout(() => {
                    const modal = document.querySelector('[wire\\:key*="progress-modal"]');
                    if (modal) {
                        modal.style.display = 'block';
                        modal.classList.add('show', 'd-block');
                    }
                }, 50);
            });
            
            Livewire.on('hide-progress-modal', () => {
                // باز کردن قفل صفحه
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
            });
        });
    </script>
    @endscript
</div>
