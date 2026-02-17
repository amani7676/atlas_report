<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAlarm): ?>
    <div style="position: fixed; top: 20px; right: 20px; z-index: 10000; background: linear-gradient(135deg, #ff6b6b, #ee5a52); color: white; padding: 16px 20px; border-radius: 12px; box-shadow: 0 8px 25px rgba(238, 90, 82, 0.3); min-width: 320px; max-width: 400px;">
        <div style="display: flex; align-items: flex-start; gap: 15px;">
            <div style="flex-shrink: 0; font-size: 24px;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 700; margin-bottom: 6px; font-size: 16px;">
                    نیاز به همگام‌سازی
                </div>
                <div style="font-size: 13px; opacity: 0.95; margin-bottom: 12px;">
                    داده‌ها ممکن است به‌روز نباشند. برای دریافت آخرین اطلاعات روی دکمه همگام‌سازی کلیک کنید.
                </div>
                <button wire:click="syncAndRefresh" 
                        wire:loading.attr="disabled"
                        style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid rgba(255, 255, 255, 0.3); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px;"
                        <?php if($syncing): ?> disabled <?php endif; ?>>
                    <i class="fas fa-sync-alt" wire:loading.class="fa-spin"></i>
                    <span wire:loading.remove>همگام‌سازی داده‌ها</span>
                    <span wire:loading>در حال همگام‌سازی...</span>
                </button>
            </div>
            <button wire:click="closeAlarm" 
                    style="flex-shrink: 0; background: rgba(255, 255, 255, 0.2); border: none; color: white; cursor: pointer; padding: 0; width: 28px; height: 28px; border-radius: 50%; font-size: 14px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\layout\persistent-alarm.blade.php ENDPATH**/ ?>