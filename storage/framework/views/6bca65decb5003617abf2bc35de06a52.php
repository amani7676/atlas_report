<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-table"></i> مدیریت نام گذاری جداول</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد نام جدول جدید
            </button>
        </div>

        <!-- Search -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی نام جدول..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- Table Names Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>نام (برای نمایش)</th>
                        <th>نام جدول در دیتابیس</th>
                        <th>قابلیت نمایش</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tableNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tableName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary-color);"><?php echo e($tableName->name); ?></strong>
                            </td>
                            <td>
                                <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; color: #e83e8c;"><?php echo e($tableName->table_name); ?></code>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tableName->is_visible): ?>
                                    <span style="background: #10b98115; color: #10b981; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        <i class="fas fa-check-circle"></i> نمایش داده می‌شود
                                    </span>
                                <?php else: ?>
                                    <span style="background: #ef444415; color: #ef4444; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        <i class="fas fa-times-circle"></i> نمایش داده نمی‌شود
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <span style="color: #666; font-size: 14px;">
                                    <?php echo e(jalaliDate($tableName->created_at, 'Y/m/d H:i')); ?>

                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button 
                                        wire:click="openModal(<?php echo e($tableName->id); ?>)" 
                                        class="btn btn-sm btn-primary"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        wire:click="delete(<?php echo e($tableName->id); ?>)" 
                                        wire:confirm="آیا مطمئن هستید که می‌خواهید این نام جدول را حذف کنید؟"
                                        class="btn btn-sm btn-danger"
                                        title="حذف"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                <p>هیچ نام جدولی یافت نشد.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tableNames->hasPages()): ?>
            <div style="margin-top: 20px;">
                <?php echo e($tableNames->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Modal برای ایجاد/ویرایش -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;" wire:click="closeModal">
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">
                        <i class="fas fa-table"></i>
                        <?php echo e($editingId ? 'ویرایش نام جدول' : 'ایجاد نام جدول جدید'); ?>

                    </h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <!-- نام (برای نمایش) -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            نام (برای نمایش) <span style="color: red;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="name"
                            class="form-control"
                            placeholder="مثال: اقامت‌گران"
                            style="width: 100%;"
                        >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- نام جدول در دیتابیس -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            نام جدول در دیتابیس <span style="color: red;">*</span>
                        </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingId): ?>
                            <!-- در حالت ویرایش، input text نمایش بده -->
                            <input
                                type="text"
                                wire:model="table_name"
                                class="form-control"
                                placeholder="مثال: residents"
                                style="width: 100%;"
                                readonly
                            >
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                نام جدول در حالت ویرایش قابل تغییر نیست
                            </small>
                        <?php else: ?>
                            <!-- در حالت ایجاد جدید، dropdown نمایش بده -->
                            <select
                                wire:model.live="table_name"
                                class="form-control"
                                style="width: 100%;"
                            >
                                <option value="">انتخاب جدول از لیست...</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($table); ?>"><?php echo e($table); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                جدول مورد نظر را از لیست انتخاب کنید. فقط جداولی که قبلاً ثبت نشده‌اند نمایش داده می‌شوند.
                            </small>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['table_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- قابلیت نمایش -->
                    <div style="margin-bottom: 25px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input
                                type="checkbox"
                                wire:model="is_visible"
                                style="width: 18px; height: 18px; cursor: pointer;"
                            >
                            <span style="font-weight: 500; color: #333;">
                                قابلیت نمایش
                            </span>
                        </label>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block; margin-right: 28px;">
                            در صورت فعال بودن، این جدول در لیست جداول قابل انتخاب نمایش داده می‌شود.
                        </small>
                    </div>

                    <!-- دکمه‌ها -->
                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">
                            <i class="fas fa-times"></i> لغو
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ذخیره
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views/livewire/table-names/index.blade.php ENDPATH**/ ?>