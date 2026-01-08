<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0;">داشبورد مدیریت</h2>
                <p style="margin: 5px 0 0 0;">سیستم گزارش‌گیری اقامت‌گران</p>
            </div>
            <button wire:click="cleanupOrphanedRecords" wire:loading.attr="disabled" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-sync-alt" wire:loading.class="fa-spin"></i>
                <span wire:loading.remove>رفرش و پاک‌سازی</span>
                <span wire:loading>در حال پردازش...</span>
            </button>
        </div>
    </div>

    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <style>
            @media (max-width: 768px) {
                .grid {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
        <div class="stats-card">
            <i class="fas fa-file-alt" style="font-size: 24px;"></i>
            <div class="stats-number">{{ $totalReports }}</div>
            <div class="stats-label">گزارش‌های ثبت‌شده</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #4cc9f0, #2db8d9);">
            <i class="fas fa-list" style="font-size: 24px;"></i>
            <div class="stats-number">{{ $totalCategories }}</div>
            <div class="stats-label">دسته‌بندی‌ها</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #06ffa5, #00c896);">
            <i class="fas fa-paper-plane" style="font-size: 24px;"></i>
            <div class="stats-number">{{ $totalSentMessages }}</div>
            <div class="stats-label">پیام‌های ارسال شده</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #ff6b6b, #ee5a52);">
            <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
            <div class="stats-number">{{ $failedMessages }}</div>
            <div class="stats-label">پیام‌های ناموفق</div>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">آخرین پیام‌های ارسال شده</h3>
            <a href="{{ route('sms.sent') }}" style="color: #007bff; text-decoration: none; font-size: 14px;">
                مشاهده همه <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        @if($recentSentMessages->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>نام اقامت‌گر</th>
                            <th>متن پیام</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSentMessages as $message)
                            <tr>
                                <td>{{ $message->resident_name }}</td>
                                <td>
                                    <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $message->smsMessage->text ?? '' }}">
                                        {{ $message->smsMessage->text ?? 'نامشخص' }}
                                    </div>
                                </td>
                                <td>
                                    @if($message->status == 'sent')
                                        <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-check"></i> ارسال شده
                                        </span>
                                    @elseif($message->status == 'failed')
                                        <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-times"></i> ناموفق
                                        </span>
                                    @else
                                        <span style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-clock"></i> در انتظار
                                        </span>
                                    @endif
                                </td>
                                <td>{{ jalaliDate($message->created_at, 'Y/m/d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-sms" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <p>هنوز هیچ پیامی ارسال نشده است</p>
            </div>
        @endif
    </div>
</div>
