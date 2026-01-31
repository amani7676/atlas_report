<div style="display: flex; align-items: center; gap: 10px;">
    @if($orphanedRecordsCount > 0)
        <span style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">
            {{ $orphanedRecordsCount }}
        </span>
    @else
        <span style="background: #6c757d; color: white; padding: 6px 12px; border-radius: 20px; font-size: 14px;">
            0
        </span>
    @endif
    
    <!-- آمار وضعیت -->
    <div class="d-flex gap-2">
        <small class="text-muted">گزارش‌ها:</small>
        <span class="badge bg-warning">{{ \App\Models\ResidentReport::count() }}</span>
        
        <small class="text-muted">پیام‌ها:</small>
        <span class="badge bg-info">{{ \App\Models\SmsMessageResident::count() }}</span>
        
        <small class="text-muted">Jobs:</small>
        <span class="badge bg-danger">{{ \Illuminate\Support\Facades\DB::table('jobs')->count() }}</span>
    </div>
    
    <button wire:click="cleanupOrphanedRecords" wire:loading.attr="disabled" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-trash-alt" wire:loading.class="fa-spin"></i>
        <span wire:loading.remove="">پاکسازی اطلاعات به درد نخور</span>
        <span wire:loading="">در حال پردازش...</span>
    </button>
</div>
