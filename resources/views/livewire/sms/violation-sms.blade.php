<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-exclamation-triangle"></i> پیامک‌های ارسال شده خودکار تخلفات</h2>
        </div>

        <!-- Filters -->
        <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <!-- Search -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">جستجو</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="نام، شماره تلفن، عنوان..."
                        class="form-control"
                        style="width: 100%;"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">وضعیت</label>
                    <select wire:model.live="statusFilter" class="form-control" style="width: 100%;">
                        <option value="">همه</option>
                        <option value="pending">در انتظار</option>
                        <option value="sent">ارسال شده</option>
                        <option value="failed">ناموفق</option>
                    </select>
                </div>

                <!-- Report Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">گزارش</label>
                    <select wire:model.live="reportFilter" class="form-control" style="width: 100%;">
                        <option value="">همه</option>
                        @foreach($this->reportsList as $report)
                            <option value="{{ $report->id }}">{{ $report->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pattern Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">الگو</label>
                    <select wire:model.live="patternFilter" class="form-control" style="width: 100%;">
                        <option value="">همه</option>
                        @foreach($this->patternsList as $pattern)
                            <option value="{{ $pattern->id }}">{{ $pattern->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">تاریخ</label>
                    <input
                        type="date"
                        wire:model.live="dateFilter"
                        class="form-control"
                        style="width: 100%;"
                    >
                </div>

                <!-- Clear Filters -->
                <div style="display: flex; align-items: flex-end;">
                    <button wire:click="clearFilters" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-times"></i> پاک کردن فیلترها
                    </button>
                </div>
            </div>
        </div>

        <!-- SMS Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>اقامت‌گر</th>
                        <th>شماره تلفن</th>
                        <th>گزارش</th>
                        <th>الگو</th>
                        <th>وضعیت</th>
                        <th>تاریخ ارسال</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($smsList as $sms)
                        <tr>
                            <td>
                                <strong style="color: var(--primary-color);">
                                    {{ $sms->resident_name ?? ($sms->resident->full_name ?? 'نامشخص') }}
                                </strong>
                            </td>
                            <td>
                                <span style="direction: ltr; display: inline-block;">{{ $sms->phone }}</span>
                            </td>
                            <td>
                                @if($sms->report)
                                    <span class="badge bg-info">{{ $sms->report->title }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sms->pattern)
                                    <span class="badge bg-primary">{{ $sms->pattern->title }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sms->status === 'sent')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> ارسال شده
                                    </span>
                                @elseif($sms->status === 'failed')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> ناموفق
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> در انتظار
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($sms->sent_at)
                                    <span style="color: #666; font-size: 14px;">
                                        {{ jalaliDate($sms->sent_at, 'Y/m/d H:i') }}
                                    </span>
                                @else
                                    <span style="color: #666; font-size: 14px;">
                                        {{ jalaliDate($sms->created_at, 'Y/m/d H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <button 
                                    wire:click="openModal({{ $sms->id }})" 
                                    class="btn btn-sm btn-info"
                                    title="مشاهده جزئیات"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                <p>هیچ پیامکی یافت نشد.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($smsList->hasPages())
            <div style="margin-top: 20px;">
                {{ $smsList->links() }}
            </div>
        @endif
    </div>

    <!-- Modal برای مشاهده جزئیات -->
    @if($showModal && $selectedSms)
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;" wire:click="closeModal">
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">
                        <i class="fas fa-info-circle"></i>
                        جزئیات پیامک
                    </h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="display: grid; gap: 20px;">
                    <!-- اطلاعات اقامت‌گر -->
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 6px;">
                        <h5 style="margin-bottom: 10px; color: var(--primary-color);">
                            <i class="fas fa-user"></i> اطلاعات اقامت‌گر
                        </h5>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <div>
                                <strong>نام:</strong> {{ $selectedSms->resident_name ?? ($selectedSms->resident->full_name ?? 'نامشخص') }}
                            </div>
                            <div>
                                <strong>شماره تلفن:</strong> 
                                <span style="direction: ltr; display: inline-block;">{{ $selectedSms->phone }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- اطلاعات گزارش -->
                    @if($selectedSms->report)
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 6px;">
                        <h5 style="margin-bottom: 10px; color: var(--primary-color);">
                            <i class="fas fa-file-alt"></i> اطلاعات گزارش
                        </h5>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <div>
                                <strong>عنوان:</strong> {{ $selectedSms->report->title }}
                            </div>
                            <div>
                                <strong>نوع:</strong> 
                                <span class="badge bg-info">{{ $selectedSms->report->type ?? 'violation' }}</span>
                            </div>
                            @if($selectedSms->report->description)
                            <div style="grid-column: 1 / -1;">
                                <strong>توضیحات:</strong> {{ $selectedSms->report->description }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- اطلاعات الگو -->
                    @if($selectedSms->pattern)
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 6px;">
                        <h5 style="margin-bottom: 10px; color: var(--primary-color);">
                            <i class="fas fa-file-code"></i> اطلاعات الگو
                        </h5>
                        <div style="display: grid; gap: 10px;">
                            <div>
                                <strong>عنوان:</strong> {{ $selectedSms->pattern->title }}
                            </div>
                            <div>
                                <strong>متن الگو:</strong>
                                <div style="padding: 10px; background: white; border-radius: 4px; margin-top: 5px;">
                                    {{ $selectedSms->pattern->text }}
                                </div>
                            </div>
                            @if($selectedSms->pattern_variables)
                            <div>
                                <strong>متغیرها:</strong>
                                <div style="padding: 10px; background: white; border-radius: 4px; margin-top: 5px;">
                                    {{ $selectedSms->pattern_variables }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- وضعیت ارسال -->
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 6px;">
                        <h5 style="margin-bottom: 10px; color: var(--primary-color);">
                            <i class="fas fa-paper-plane"></i> وضعیت ارسال
                        </h5>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <div>
                                <strong>وضعیت:</strong>
                                @if($selectedSms->status === 'sent')
                                    <span class="badge bg-success">ارسال شده</span>
                                @elseif($selectedSms->status === 'failed')
                                    <span class="badge bg-danger">ناموفق</span>
                                @else
                                    <span class="badge bg-warning">در انتظار</span>
                                @endif
                            </div>
                            <div>
                                <strong>تاریخ ایجاد:</strong> {{ jalaliDate($selectedSms->created_at, 'Y/m/d H:i:s') }}
                            </div>
                            @if($selectedSms->sent_at)
                            <div>
                                <strong>تاریخ ارسال:</strong> {{ jalaliDate($selectedSms->sent_at, 'Y/m/d H:i:s') }}
                            </div>
                            @endif
                            @if($selectedSms->response_code)
                            <div>
                                <strong>کد پاسخ:</strong> {{ $selectedSms->response_code }}
                            </div>
                            @endif
                            @if($selectedSms->error_message)
                            <div style="grid-column: 1 / -1;">
                                <strong>پیام خطا:</strong>
                                <div style="padding: 10px; background: #fff3cd; border-radius: 4px; margin-top: 5px; color: #856404;">
                                    {{ $selectedSms->error_message }}
                                </div>
                            </div>
                            @endif
                            @if($selectedSms->api_response)
                            <div style="grid-column: 1 / -1;">
                                <strong>پاسخ API:</strong>
                                <pre style="padding: 10px; background: white; border-radius: 4px; margin-top: 5px; font-size: 12px; max-height: 200px; overflow-y: auto;">{{ json_encode($selectedSms->api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 25px;">
                    <button wire:click="closeModal" class="btn btn-secondary">
                        <i class="fas fa-times"></i> بستن
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>



