<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0;">داشبورد مدیریت</h2>
                <p style="margin: 5px 0 0 0;">سیستم گزارش‌گیری اقامت‌گران</p>
            </div>
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
                <button wire:click="cleanupOrphanedRecords" wire:loading.attr="disabled" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-trash-alt" wire:loading.class="fa-spin"></i>
                    <span wire:loading.remove>نیاز به حذف اطلاعات به درد نخور</span>
                    <span wire:loading>در حال پردازش...</span>
                </button>
            </div>
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
            <div class="stats-number"><?php echo e($totalReports); ?></div>
            <div class="stats-label">گزارش‌های ثبت‌شده</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #4cc9f0, #2db8d9);">
            <i class="fas fa-list" style="font-size: 24px;"></i>
            <div class="stats-number"><?php echo e($totalCategories); ?></div>
            <div class="stats-label">دسته‌بندی‌ها</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #06ffa5, #00c896);">
            <i class="fas fa-paper-plane" style="font-size: 24px;"></i>
            <div class="stats-number"><?php echo e($totalSentMessages); ?></div>
            <div class="stats-label">پیام‌های ارسال شده</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #ff6b6b, #ee5a52);">
            <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
            <div class="stats-number"><?php echo e($failedMessages); ?></div>
            <div class="stats-label">پیام‌های ناموفق</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #4ecdc4, #44a3aa);">
            <i class="fas fa-check-circle" style="font-size: 24px;"></i>
            <div class="stats-number"><?php echo e($deliveryStats['delivered'] ?? 0); ?></div>
            <div class="stats-label">پیام‌های تحویل شده</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #f7b731, #f5a623);">
            <i class="fas fa-clock" style="font-size: 24px;"></i>
            <div class="stats-number"><?php echo e($deliveryStats['delivery_pending'] ?? 0); ?></div>
            <div class="stats-label">در انتظار دلیوری</div>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">آخرین پیام‌های ارسال شده</h3>
            <div style="display: flex; align-items: center; gap: 10px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastDeliveryCheck): ?>
                    <span style="color: #666; font-size: 12px;">
                        آخرین بررسی دلیوری: <?php echo e(jalaliDate($lastDeliveryCheck, 'Y/m/d H:i')); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button wire:click="updateDeliveryStatus" wire:loading.attr="disabled" class="btn btn-info btn-sm" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-sync" wire:loading.class="fa-spin"></i>
                    <span wire:loading.remove>بررسی وضعیت دلیوری</span>
                    <span wire:loading>در حال بررسی...</span>
                </button>
                <a href="<?php echo e(route('sms.sent')); ?>" style="color: #007bff; text-decoration: none; font-size: 14px;">
                    مشاهده همه <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentSentMessages->count() > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>نام اقامت‌گر</th>
                            <th>متن پیام</th>
                            <th>وضعیت ارسال</th>
                            <th>وضعیت دلیوری</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentSentMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($message->resident_name); ?></td>
                                <td>
                                    <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo e($message->smsMessage->text ?? ''); ?>">
                                        <?php echo e($message->smsMessage->text ?? 'نامشخص'); ?>

                                    </div>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message->status == 'sent'): ?>
                                        <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-check"></i> ارسال شده
                                        </span>
                                    <?php elseif($message->status == 'delivered'): ?>
                                        <span style="background: #d1ecf1; color: #0c5460; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-check-double"></i> تحویل شده
                                        </span>
                                    <?php elseif($message->status == 'failed'): ?>
                                        <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-times"></i> ناموفق
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-clock"></i> در انتظار
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message->delivery_checked): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message->delivery_status == 1): ?>
                                            <span style="background: #d1ecf1; color: #0c5460; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                                <i class="fas fa-check-double"></i> رسیده به گوشی
                                            </span>
                                        <?php elseif($message->delivery_status == 0): ?>
                                            <span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                                <i class="fas fa-check"></i> رسیده به مخابرات
                                            </span>
                                        <?php elseif(in_array($message->delivery_status, [2, 3, 5, 16, 35])): ?>
                                            <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                                <i class="fas fa-times"></i> تحویل نشد
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                                کد: <?php echo e($message->delivery_status ?? 'نامشخص'); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-clock"></i> بررسی نشده
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php echo e(jalaliDate($message->created_at, 'Y/m/d H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-sms" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <p>هنوز هیچ پیامی ارسال نشده است</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>