<div>
    @section('title', 'ارسال گروهی پیامک')

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

        /* Responsive */
        @media (max-width: 768px) {
            .mdc-card {
                padding: 16px;
            }

            .mdc-headline-4 {
                font-size: 24px;
            }
        }
    </style>

    <div class="mdc-card fade-in">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <h1 class="mdc-headline-4">
                <i class="fas fa-paper-plane" style="color: var(--mdc-primary);"></i>
                ارسال گروهی پیامک
            </h1>
            @if($selectedCount > 0)
                <div class="mdc-chip mdc-chip--primary" style="font-size: 14px; padding: 8px 16px;">
                    <i class="fas fa-check-circle" style="margin-left: 8px;"></i>
                    {{ $selectedCount }} مورد انتخاب شده
                </div>
            @endif
        </div>

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
                                wire:model.live="selectAll"
                                wire:click="toggleSelectAll"
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

        <!-- Pagination -->
        @if($residents->hasPages())
            <div style="margin-top: 24px; display: flex; justify-content: center;">
                {{ $residents->links() }}
            </div>
        @endif

        <!-- Send Button Bar -->
        @if($selectedCount > 0)
            <div class="stats-bar fade-in">
                <div class="stats-bar__text">
                    <i class="fas fa-users" style="margin-left: 8px;"></i>
                    <strong>{{ $selectedCount }}</strong> اقامت‌گر انتخاب شده است
                </div>
                <button class="mdc-button mdc-button--raised" wire:click="sendSms" style="background: var(--mdc-on-primary); color: var(--mdc-primary);">
                    <i class="fas fa-paper-plane"></i>
                    ارسال پیامک
                </button>
            </div>
        @endif
    </div>
</div>
