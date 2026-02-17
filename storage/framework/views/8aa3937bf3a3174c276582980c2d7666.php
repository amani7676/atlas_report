<div style="display: flex; align-items: center; gap: 10px;">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orphanedRecordsCount > 0): ?>
        <span style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 20px; font-size: 14px; font-weight: bold;">
            <?php echo e($orphanedRecordsCount); ?>

        </span>
    <?php else: ?>
        <span style="background: #6c757d; color: white; padding: 6px 12px; border-radius: 20px; font-size: 14px;">
            0
        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <!-- آمار وضعیت -->
    <div class="d-flex gap-2">
        <small class="text-muted">گزارش‌ها:</small>
        <span class="badge bg-warning"><?php echo e(\App\Models\ResidentReport::count()); ?></span>
        
        <small class="text-muted">پیام‌ها:</small>
        <span class="badge bg-info"><?php echo e(\App\Models\SmsMessageResident::count()); ?></span>
        
        <small class="text-muted">Jobs:</small>
        <span class="badge bg-danger"><?php echo e(\Illuminate\Support\Facades\DB::table('jobs')->count()); ?></span>
    </div>
    
    <button wire:click="cleanupOrphanedRecords" wire:loading.attr="disabled" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-trash-alt" wire:loading.class="fa-spin"></i>
        <span wire:loading.remove="">پاکسازی اطلاعات به درد نخور</span>
        <span wire:loading="">در حال پردازش...</span>
    </button>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\layout\stats-bar.blade.php ENDPATH**/ ?>