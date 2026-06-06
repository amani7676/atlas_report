<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-cog"></i> مدیریت ثابت‌ها</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد ثابت جدید
            </button>
        </div>

        <!-- Search -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی ثابت (کلید، مقدار، توضیحات)..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- Constants Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>کلید</th>
                        <th>مقدار</th>
                        <th>نوع داده</th>
                        <th>توضیحات</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $constants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $constant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary-color);"><?php echo e($constant->key); ?></strong>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($constant->type === 'date'): ?>
                                    <span style="color: #666;"><?php echo e(jalaliDate($constant->value, 'Y/m/d')); ?></span>
                                <?php elseif($constant->type === 'number'): ?>
                                    <span style="color: #10b981; font-weight: 500;"><?php echo e(number_format($constant->value, 0)); ?></span>
                                <?php else: ?>
                                    <span><?php echo e(Str::limit($constant->value, 50)); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $typeLabels = [
                                        'string' => ['label' => 'رشته', 'color' => '#4361ee'],
                                        'number' => ['label' => 'عدد', 'color' => '#10b981'],
                                        'date' => ['label' => 'تاریخ', 'color' => '#f59e0b'],
                                        'enum' => ['label' => 'Enum', 'color' => '#8b5cf6'],
                                    ];
                                    $typeInfo = $typeLabels[$constant->type] ?? ['label' => $constant->type, 'color' => '#666'];
                                ?>
                                <span style="background: <?php echo e($typeInfo['color']); ?>15; color: <?php echo e($typeInfo['color']); ?>; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                    <?php echo e($typeInfo['label']); ?>

                                </span>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($constant->description): ?>
                                    <p style="color: #666; font-size: 14px; margin: 0;">
                                        <?php echo e(Str::limit($constant->description, 50)); ?>

                                    </p>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">بدون توضیح</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <span style="color: #666; font-size: 14px;">
                                    <?php echo e(jalaliDate($constant->created_at, 'Y/m/d H:i')); ?>

                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button 
                                        wire:click="openModal(<?php echo e($constant->id); ?>)" 
                                        class="btn btn-sm btn-primary"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        wire:click="delete(<?php echo e($constant->id); ?>)" 
                                        wire:confirm="آیا مطمئن هستید که می‌خواهید این ثابت را حذف کنید؟"
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
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                <p>هیچ ثابتی یافت نشد.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($constants->hasPages()): ?>
            <div style="margin-top: 20px;">
                <?php echo e($constants->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Modal برای ایجاد/ویرایش -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;" wire:click="closeModal">
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">
                        <i class="fas fa-cog"></i>
                        <?php echo e($editingId ? 'ویرایش ثابت' : 'ایجاد ثابت جدید'); ?>

                    </h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <!-- کلید -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            کلید <span style="color: red;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="key"
                            class="form-control"
                            placeholder="مثال: max_users"
                            style="width: 100%;"
                        >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- نوع داده -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            نوع داده <span style="color: red;">*</span>
                        </label>
                        <select wire:model.live="type" class="form-control" style="width: 100%;">
                            <option value="string">رشته (String)</option>
                            <option value="number">عدد (Number)</option>
                            <option value="date">تاریخ (Date)</option>
                            <option value="enum">Enum</option>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- مقادیر Enum (فقط برای نوع enum) -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'enum'): ?>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                مقادیر Enum <span style="color: red;">*</span>
                                <small style="color: #666; font-weight: normal;">(با کاما جدا کنید، مثال: فعال,غیرفعال,در انتظار)</small>
                            </label>
                            <input
                                type="text"
                                wire:model="enum_values"
                                class="form-control"
                                placeholder="فعال,غیرفعال,در انتظار"
                                style="width: 100%;"
                            >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['enum_values'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- مقدار -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            مقدار <span style="color: red;">*</span>
                        </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'date'): ?>
                            <input
                                type="date"
                                wire:model="value"
                                class="form-control"
                                style="width: 100%;"
                            >
                        <?php elseif($type === 'enum'): ?>
                            <select wire:model="value" class="form-control" style="width: 100%;">
                                <option value="">انتخاب کنید...</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($enum_values): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = explode(',', $enum_values); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enumValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e(trim($enumValue)); ?>"><?php echo e(trim($enumValue)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        <?php else: ?>
                            <input
                                type="<?php echo e($type === 'number' ? 'number' : 'text'); ?>"
                                wire:model="value"
                                class="form-control"
                                placeholder="<?php echo e($type === 'number' ? 'مثال: 100' : 'مثال: مقدار ثابت'); ?>"
                                style="width: 100%;"
                            >
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- توضیحات -->
                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            توضیحات
                        </label>
                        <textarea
                            wire:model="description"
                            class="form-control"
                            rows="3"
                            placeholder="توضیحات اختیاری..."
                            style="width: 100%; resize: vertical;"
                        ></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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





<?php /**PATH C:\laragon\www\atlas_report\resources\views/livewire/constants/index.blade.php ENDPATH**/ ?>