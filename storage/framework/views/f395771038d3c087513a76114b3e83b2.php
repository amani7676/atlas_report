<div wire:poll.10s="pollSyncStatus">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAlert): ?>
        <div id="sync-alert-container" 
             style="position: fixed; top: 20px; left: 20px; z-index: 9999; min-width: 300px; max-width: 500px; animation: slideInLeft 0.3s ease-out;"
             wire:ignore.self>
            <div class="alert 
                <?php if($alertType === 'success'): ?> alert-success 
                <?php elseif($alertType === 'error'): ?> alert-danger 
                <?php elseif($alertType === 'warning'): ?> alert-warning 
                <?php else: ?> alert-info 
                <?php endif; ?>" 
                style="display: flex; align-items: flex-start; gap: 12px; padding: 15px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-bottom: 0;">
                <div style="flex-shrink: 0; font-size: 20px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($alertType === 'success'): ?>
                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                    <?php elseif($alertType === 'error'): ?>
                        <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>
                    <?php elseif($alertType === 'warning'): ?>
                        <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
                    <?php else: ?>
                        <i class="fas fa-info-circle" style="color: #17a2b8;"></i>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 5px; font-size: 14px;">
                        <?php echo e($alertTitle); ?>

                    </div>
                    <div style="font-size: 13px; color: #666;">
                        <?php echo e($alertMessage); ?>

                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastSyncData && $alertType === 'success'): ?>
                        <div style="margin-top: 8px; font-size: 12px; color: #888; display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-users"></i> همگام‌سازی: <?php echo e($lastSyncData['synced_count'] ?? 0); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($lastSyncData['created_count']) && $lastSyncData['created_count'] > 0): ?>
                                <span><i class="fas fa-plus-circle"></i> ایجاد: <?php echo e($lastSyncData['created_count']); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($lastSyncData['updated_count']) && $lastSyncData['updated_count'] > 0): ?>
                                <span><i class="fas fa-sync"></i> به‌روزرسانی: <?php echo e($lastSyncData['updated_count']); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($lastSyncData['time'])): ?>
                            <div style="margin-top: 5px; font-size: 11px; color: #aaa;">
                                <i class="fas fa-clock"></i> <?php echo e($lastSyncData['time']); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button type="button" 
                        wire:click="closeAlert"
                        style="flex-shrink: 0; background: none; border: none; font-size: 18px; color: #666; cursor: pointer; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='rgba(0,0,0,0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <style>
            @keyframes slideInLeft {
                from {
                    opacity: 0;
                    transform: translateX(-100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            @keyframes slideOutLeft {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(-100%);
                }
            }
            .alert-hiding {
                animation: slideOutLeft 0.2s ease-in forwards;
            }
        </style>
        <script>
            document.addEventListener('livewire:init', () => {
                // مخفی کردن آلارم بعد از 5 ثانیه (برای موفقیت) یا 10 ثانیه (برای خطا)
                Livewire.on('hide-alert-after-delay', () => {
                    setTimeout(() => {
                        const container = document.getElementById('sync-alert-container');
                        if (container) {
                            container.classList.add('alert-hiding');
                            // بعد از انیمیشن، کامپوننت را ببند
                            setTimeout(() => {
                                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closeAlert');
                            }, 200);
                        }
                    }, <?php echo e($alertType === 'error' ? 10000 : 5000); ?>);
                });

                // اگر آلارم نمایش داده شد، بعد از مدت مشخص آن را مخفی کن
                setTimeout(() => {
                    const container = document.getElementById('sync-alert-container');
                    if (container) {
                        Livewire.dispatch('hide-alert-after-delay');
                    }
                }, <?php echo e($alertType === 'error' ? 10000 : 5000); ?>);
            });
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\layout\sync-alert.blade.php ENDPATH**/ ?>