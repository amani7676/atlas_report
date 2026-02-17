<div>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-plus-circle me-2"></i>
                ثبت گزارش جدید
            </h5>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <!-- اطلاعات اقامت‌گر -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resident_id && !$isGroupMode): ?>
                    <?php
                        $resident = \App\Models\Resident::where('resident_id', $resident_id)->first();
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resident): ?>
                        <div class="alert alert-info mb-3">
                            <strong>اقامت‌گر:</strong> <?php echo e($resident->resident_full_name ?? 'نامشخص'); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resident->unit_name): ?> - اتاق <?php echo e($resident->unit_name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resident->room_name): ?> - تخت <?php echo e($resident->room_name); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <!-- حالت گروهی -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isGroupMode && !empty($selectedResidents)): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-users me-2"></i>
                        <strong><?php echo e(count($selectedResidents)); ?> اقامت‌گر انتخاب شده</strong>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- انتخاب دسته‌بندی -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-folder"></i>
                        دسته‌بندی
                    </label>
                    <select class="form-select" wire:model.live="selectedCategory">
                        <option value="">همه دسته‌بندی‌ها</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <!-- انتخاب گزارش -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-file-alt"></i>
                        گزارش
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" wire:model="report_id" required>
                        <option value="">-- انتخاب گزارش --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($report->id); ?>">
                                <?php echo e($report->title); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->negative_score): ?> (<?php echo e($report->negative_score); ?> امتیاز) <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['report_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- توضیحات -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-comment"></i>
                        توضیحات
                    </label>
                    <textarea class="form-control" wire:model="description" rows="3" 
                              placeholder="توضیحات مربوط به این گزارش را وارد کنید..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- یادداشت‌ها -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-sticky-note"></i>
                        یادداشت‌ها
                    </label>
                    <textarea class="form-control" wire:model="notes" rows="2" 
                              placeholder="یادداشت‌های داخلی..."></textarea>
                </div>
                
                <!-- گزینه‌های پیامک -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model.live="sendSms" id="sendSms">
                        <label class="form-check-label" for="sendSms">
                            <i class="fas fa-sms me-2 text-primary"></i>
                            <strong>ارسال پیامک به اقامت‌گر(ها)</strong>
                        </label>
                    </div>
                </div>
                
                <!-- نمایش پیام الگو -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sendSms && isset($patternMessage)): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patternMessage['success']): ?>
                        <div class="mb-3">
                            <div class="card border-success">
                                <div class="card-header bg-light text-success">
                                    <h6 class="mb-0">
                                        <i class="fas fa-envelope-open-text me-2"></i>
                                        پیش‌نمایش پیام الگو
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted">عنوان الگو:</small>
                                        <strong class="text-success"><?php echo e($patternMessage['pattern_title']); ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">متن اصلی:</small>
                                        <div class="p-2 bg-light border rounded font-monospace small">
                                            <?php echo e($patternMessage['original_message']); ?>

                                        </div>
                                    </div>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isGroupMode || count($selectedResidents) > 1): ?>
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                wire:click="showSmsPreview">
                                            <i class="fas fa-eye me-1"></i>
                                            مشاهده پیش‌نمایش برای همه اقامت‌گران
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo e($patternMessage['message']); ?>

                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- دکمه‌ها -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        ثبت گزارش
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-times me-1"></i>
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SMS Preview Modal -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPreviewModal): ?>
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1050;" tabindex="-1">
    <div class="modal-dialog modal-xl" style="margin-top: 5vh;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.3); overflow: hidden;">
            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    پیش‌نمایش پیامک برای اقامت‌گران
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="closePreviewModal"></button>
            </div>
            
            <!-- Body -->
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="row">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $previewMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $preview): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-lg-6 col-xl-4 mb-3">
                            <div class="card h-100 <?php echo e($preview['has_phone'] ? 'border-success' : 'border-warning'); ?>">
                                <div class="card-header d-flex justify-content-between align-items-center <?php echo e($preview['has_phone'] ? 'bg-light text-success' : 'bg-warning text-dark'); ?>">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo e($preview['resident_name']); ?>

                                    </h6>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview['has_phone']): ?>
                                        <i class="fas fa-mobile-alt text-success" title="دارای شماره تماس"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle text-warning" title="بدون شماره تماس"></i>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted">کد اقامت:</small>
                                        <span class="badge bg-secondary"><?php echo e($preview['resident_id']); ?></span>
                                    </div>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview['unit_name']): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">واحد:</small>
                                            <span><?php echo e($preview['unit_name']); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview['room_name']): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">اتاق:</small>
                                            <span><?php echo e($preview['room_name']); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">شماره تماس:</small>
                                        <span class="<?php echo e($preview['has_phone'] ? 'text-success' : 'text-warning'); ?>">
                                            <?php echo e($preview['phone']); ?>

                                        </span>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div>
                                        <small class="text-muted d-block mb-1">پیام ارسالی:</small>
                                        <div class="p-2 bg-light border rounded" style="font-size: 0.9rem; line-height: 1.4;">
                                            <?php echo e($preview['message']); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($previewMessages)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">هیچ پیامی برای نمایش وجود ندارد</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <!-- Footer -->
            <div class="modal-footer">
                <div class="me-auto">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo e(count($previewMessages)); ?> پیام برای نمایش
                    </small>
                </div>
                <button type="button" class="btn btn-secondary" wire:click="closePreviewModal">
                    <i class="fas fa-times me-1"></i>
                    بستن
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // گوش دادن به رویداد reportCreated برای بستن مدال
    window.addEventListener('reportCreated', function() {
        // بستن مدال بعد از ثبت موفق
        const modal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
        if (modal) {
            modal.hide();
        }
        
        // رفرش صفحه یا به‌روزرسانی کامپوننت والد
        window.location.reload();
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\residents\create-resident-report.blade.php ENDPATH**/ ?>