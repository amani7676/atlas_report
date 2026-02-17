<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #10b981;">
                <i class="fas fa-phone-alt"></i> مدیریت شماره‌های فرستنده
            </h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="fas fa-plus"></i> افزودن شماره جدید
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>شماره</th>
                        <th>عنوان</th>
                        <th>نوع</th>
                        <th>اولویت</th>
                        <th>API Key</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $senderNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $senderNumber): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 16px;"><?php echo e($senderNumber->number); ?></strong>
                            </td>
                            <td><?php echo e($senderNumber->title); ?></td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($senderNumber->is_pattern): ?>
                                    <span class="badge" style="background: #10b981; color: white;">الگویی</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #3b82f6; color: white;">ساده</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td><?php echo e($senderNumber->priority); ?></td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($senderNumber->api_key): ?>
                                    <span style="font-family: monospace; font-size: 11px; color: #666;">
                                        <?php echo e(Str::limit($senderNumber->api_key, 20)); ?>

                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <button 
                                    wire:click="toggleActive(<?php echo e($senderNumber->id); ?>)"
                                    class="btn btn-sm"
                                    style="background: <?php echo e($senderNumber->is_active ? '#10b981' : '#6c757d'); ?>; color: white;"
                                >
                                    <?php echo e($senderNumber->is_active ? 'فعال' : 'غیرفعال'); ?>

                                </button>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button 
                                        wire:click="openModal(<?php echo e($senderNumber->id); ?>)"
                                        class="btn btn-sm btn-info"
                                        style="color: white;"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        wire:click="delete(<?php echo e($senderNumber->id); ?>)"
                                        wire:confirm="آیا از حذف این شماره اطمینان دارید؟"
                                        class="btn btn-sm btn-danger"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($senderNumber->description): ?>
                            <tr>
                                <td colspan="7" style="padding-top: 0; padding-bottom: 15px;">
                                    <small style="color: #666; font-style: italic;"><?php echo e($senderNumber->description); ?></small>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i>
                                <p>هیچ شماره فرستنده‌ای ثبت نشده است.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-phone-alt"></i>
                            <?php echo e($editingId ? 'ویرایش شماره فرستنده' : 'افزودن شماره فرستنده جدید'); ?>

                        </h5>
                        <button type="button" wire:click="closeModal" class="btn-close" style="filter: brightness(0) invert(1);"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="form-group mb-3">
                                <label class="form-label">شماره فرستنده <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    wire:model="form.number" 
                                    class="form-control"
                                    placeholder="مثال: 90001429"
                                >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                    <span class="text-danger" style="font-size: 12px;"><?php echo e($message); ?></span> 
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">عنوان <span class="text-danger">*</span></label>
                                <input 
                                    type="text" 
                                    wire:model="form.title" 
                                    class="form-control"
                                    placeholder="مثال: شماره الگویی اصلی"
                                >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                    <span class="text-danger" style="font-size: 12px;"><?php echo e($message); ?></span> 
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">توضیحات</label>
                                <textarea 
                                    wire:model="form.description" 
                                    class="form-control" 
                                    rows="3"
                                    placeholder="توضیحات اختیاری..."
                                ></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">API Key (اختیاری)</label>
                                <input 
                                    type="text" 
                                    wire:model="form.api_key" 
                                    class="form-control"
                                    placeholder="API Key مرتبط با این شماره (اختیاری)"
                                >
                                <small class="form-text text-muted">
                                    اگر این شماره API Key مخصوص خودش دارد، اینجا وارد کنید. در غیر این صورت از API Key پیش‌فرض استفاده می‌شود.
                                </small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">اولویت</label>
                                        <input 
                                            type="number" 
                                            wire:model="form.priority" 
                                            class="form-control"
                                            min="0"
                                        >
                                        <small class="form-text text-muted">
                                            عدد بالاتر = اولویت بیشتر (برای انتخاب خودکار)
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check mt-4">
                                            <input 
                                                type="checkbox" 
                                                wire:model="form.is_pattern" 
                                                class="form-check-input"
                                                id="is_pattern"
                                            >
                                            <label class="form-check-label" for="is_pattern">
                                                برای پیامک‌های الگویی
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input 
                                                type="checkbox" 
                                                wire:model="form.is_active" 
                                                class="form-check-input"
                                                id="is_active"
                                            >
                                            <label class="form-check-label" for="is_active">
                                                فعال
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" wire:click="closeModal" class="btn btn-secondary">لغو</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> ذخیره
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\admin\sender-numbers.blade.php ENDPATH**/ ?>