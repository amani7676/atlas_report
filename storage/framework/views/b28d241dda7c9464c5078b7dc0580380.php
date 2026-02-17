<div>
    <div class="card">
        <h2 style="margin-bottom: 20px;">ایجاد دسته‌بندی جدید</h2>

        <form wire:submit.prevent="save">
            <div class="form-group">
                <label class="form-label">نام دسته‌بندی *</label>
                <input
                    type="text"
                    wire:model="name"
                    class="form-control"
                    placeholder="مثال: نظافت اتاق"
                    required
                >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f72585; font-size: 14px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">توضیحات</label>
                <textarea
                    wire:model="description"
                    class="form-control"
                    rows="3"
                    placeholder="توضیحات اختیاری درباره این دسته‌بندی..."
                ></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f72585; font-size: 14px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    ذخیره دسته‌بندی
                </button>
                <a href="/categories" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به لیست
                </a>
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\categories\create.blade.php ENDPATH**/ ?>