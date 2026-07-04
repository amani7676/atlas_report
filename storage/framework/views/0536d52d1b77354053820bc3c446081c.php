<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">اقامت‌گران</h3>
                    <div class="d-flex gap-2">
                        <button wire:click="syncResidents" wire:loading.attr="disabled" class="btn btn-primary">
                            <i class="fas fa-sync-alt" wire:loading.class="fa-spin"></i>
                            <span wire:loading.remove>همگام‌سازی</span>
                            <span wire:loading>در حال همگام‌سازی...</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6 col-sm-12">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="جستجو بر اساس نام، تلفن، واحد، اتاق یا تخت..."
                                    wire:model.live="search"
                                >
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <select class="form-select" wire:model.live="perPage">
                                <option value="10">10 رکورد</option>
                                <option value="20">20 رکورد</option>
                                <option value="50">50 رکورد</option>
                                <option value="100">100 رکورد</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="text-muted">
                                تعداد کل: <?php echo e($residents->total()); ?> اقامت‌گر
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($residents->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>کد اقامت</th>
                                        <th>نام کامل</th>
                                        <th>تلفن</th>
                                        <th>سن</th>
                                        <th>شغل</th>
                                        <th>واحد</th>
                                        <th>اتاق</th>
                                        <th>تخت</th>
                                        <th>تاریخ شروع</th>
                                        <th>تاریخ پایان</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $residents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($resident->resident_id); ?></td>
                                            <td><?php echo e($resident->resident_full_name ?? '-'); ?></td>
                                            <td><?php echo e($resident->resident_phone ?? '-'); ?></td>
                                            <td><?php echo e($resident->resident_age ?? '-'); ?></td>
                                            <td><?php echo e($resident->resident_job ?? '-'); ?></td>
                                            <td><?php echo e($resident->unit_name ?? '-'); ?></td>
                                            <td><?php echo e($resident->room_name ?? '-'); ?></td>
                                            <td><?php echo e($resident->bed_name ?? '-'); ?></td>
                                            <td><?php echo e($resident->contract_start_date ? $resident->contract_start_date->format('Y/m/d') : '-'); ?></td>
                                            <td><?php echo e($resident->contract_end_date ? $resident->contract_end_date->format('Y/m/d') : '-'); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" title="مشاهده جزئیات">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" title="ارسال پیامک">
                                                        <i class="fas fa-sms"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            <?php echo e($residents->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هیچ اقامت‌گری یافت نشد</h5>
                            <p class="text-muted">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
                                    نتیجه‌ای برای جستجوی "<?php echo e($search); ?>" یافت نشد.
                                <?php else: ?>
                                    هنوز هیچ اقامت‌گری در سیستم ثبت نشده است.
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$search): ?>
                                <button wire:click="syncResidents" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i>
                                    همگام‌سازی داده‌ها
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\residents\index.blade.php ENDPATH**/ ?>