<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">لاگ‌های ارسال پیام خوش‌آمدگویی</h4>
            <p class="text-muted mb-0">مشاهده و مدیریت لاگ‌های ارسال پیام‌های خوش‌آمدگویی</p>
        </div>
        <a href="/welcome-messages" class="btn btn-outline-primary">
            <i class="fas fa-arrow-right me-2"></i>بازگشت به پیام‌ها
        </a>
    </div>

    <!-- فیلترها -->
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="resetFilters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">جستجو</label>
                        <input type="text" class="form-control" wire:model.live="search" 
                               placeholder="نام، تلفن یا شناسه اقامت‌گر...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">وضعیت</label>
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="">همه</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">از تاریخ</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تا تاریخ</label>
                        <input type="date" class="form-control" wire:model.live="dateTo">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-refresh me-2"></i>بازنشانی فیلترها
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول لاگ‌ها -->
    <div class="card">
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>اقامت‌گر</th>
                                <th>تلفن</th>
                                <th>پیام</th>
                                <th>وضعیت</th>
                                <th>RecId</th>
                                <th>زمان ارسال</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $log->resident_name }}</strong>
                                            <br><small class="text-muted">شناسه: {{ $log->resident_id }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="bg-light">{{ $log->resident_phone }}</code>
                                    </td>
                                    <td>
                                        @if($log->welcomeMessage)
                                            <div>
                                                <strong>{{ $log->welcomeMessage->title }}</strong>
                                                @if($log->welcomeMessage->pattern_code)
                                                    <br><small class="text-muted">کد: {{ $log->welcomeMessage->pattern_code }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status === 'sent')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>ارسال شده
                                            </span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>ناموفق
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>در انتظار
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->rec_id)
                                            <code class="bg-success text-white">{{ $log->rec_id }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ jalaliDate($log->created_at, 'Y/m/d H:i:s') }}</small>
                                        @if($log->sent_at)
                                            <br><small class="text-success">ارسال: {{ jalaliDate($log->sent_at, 'H:i:s') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($log->status === 'failed')
                                                <button type="button" class="btn btn-outline-warning" 
                                                        wire:click="resendMessage({{ $log->id }})"
                                                        title="ارسال مجدد">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-info" 
                                                    wire:click="$wire.set('selectedLog', {{ $log->id }}); $wire.dispatch('showDetailsModal')"
                                                    title="جزئیات">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    wire:click="deleteLog({{ $log->id }})"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">هیچ لاگی یافت نشد</h5>
                    <p class="text-muted">هیچ لاگ ارسال پیامی در سیستم وجود ندارد.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- مودال جزئیات -->
    <div wire:ignore.self>
        <div class="modal fade" id="detailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">جزئیات ارسال پیام</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if(isset($selectedLog))
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>اطلاعات اقامت‌گر</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>نام:</strong></td>
                                            <td>{{ $selectedLog->resident_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>شناسه:</strong></td>
                                            <td>{{ $selectedLog->resident_id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>تلفن:</strong></td>
                                            <td>{{ $selectedLog->resident_phone }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>اطلاعات ارسال</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>وضعیت:</strong></td>
                                            <td>
                                                @if($selectedLog->status === 'sent')
                                                    <span class="badge bg-success">ارسال شده</span>
                                                @elseif($selectedLog->status === 'failed')
                                                    <span class="badge bg-danger">ناموفق</span>
                                                @else
                                                    <span class="badge bg-warning">در انتظار</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($selectedLog->rec_id)
                                        <tr>
                                            <td><strong>RecId:</strong></td>
                                            <td><code>{{ $selectedLog->rec_id }}</code></td>
                                        </tr>
                                        @endif
                                        @if($selectedLog->response_code)
                                        <tr>
                                            <td><strong>کد پاسخ:</strong></td>
                                            <td><code>{{ $selectedLog->response_code }}</code></td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>ایجاد:</strong></td>
                                            <td>{{ jalaliDate($selectedLog->created_at, 'Y/m/d H:i:s') }}</td>
                                        </tr>
                                        @if($selectedLog->sent_at)
                                        <tr>
                                            <td><strong>ارسال:</strong></td>
                                            <td>{{ jalaliDate($selectedLog->sent_at, 'Y/m/d H:i:s') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            @if($selectedLog->error_message)
                            <div class="alert alert-danger mt-3">
                                <strong>خطا:</strong> {{ $selectedLog->error_message }}
                            </div>
                            @endif

                            @if($selectedLog->api_response)
                            <div class="mt-3">
                                <h6>پاسخ API</h6>
                                <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">{{ is_array($selectedLog->api_response) ? json_encode($selectedLog->api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $selectedLog->api_response }}</pre>
                            </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- اسکریپت‌ها -->
    <script>
        document.addEventListener('livewire:init', () => {
            // باز کردن مودال جزئیات
            Livewire.on('showDetailsModal', () => {
                new bootstrap.Modal(document.getElementById('detailsModal')).show();
            });
        });
    </script>
</div>
