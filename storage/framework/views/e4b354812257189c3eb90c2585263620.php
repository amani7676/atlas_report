<div>
    <div class="card">
        <h2 style="margin-bottom: 20px;">ویرایش گزارش</h2>

        <form wire:submit.prevent="update">
            <div class="form-group">
                <label class="form-label">دسته‌بندی *</label>
                <select wire:model="category_id" class="form-control" required>
                    <option value="">انتخاب دسته‌بندی</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php echo e($category_id == $category->id ? 'selected' : ''); ?>>
                            <?php echo e($category->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f72585; font-size: 14px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">عنوان گزارش *</label>
                <input
                    type="text"
                    wire:model="title"
                    class="form-control"
                    required
                >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f72585; font-size: 14px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">توضیحات *</label>
                <textarea
                    wire:model="description"
                    class="form-control"
                    rows="4"
                    required
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

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">نمره منفی *</label>
                    <input
                        type="number"
                        wire:model="negative_score"
                        class="form-control"
                        min="0"
                        required
                    >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['negative_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f72585; font-size: 14px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">ضریب افزایش *</label>
                    <input
                        type="number"
                        wire:model="increase_coefficient"
                        class="form-control"
                        step="0.01"
                        min="0"
                        required
                    >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['increase_coefficient'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f72585; font-size: 14px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input
                        type="checkbox"
                        wire:model="auto_ability"
                        style="width: 18px; height: 18px; cursor: pointer;"
                    >
                    <span class="form-label" style="margin: 0;">
                        <i class="fas fa-robot"></i>
                        قابلیت ارسال خودکار پیام
                    </span>
                </label>
                <small style="display: block; color: #666; margin-top: 5px; font-size: 12px; margin-right: 28px;">
                    در صورت فعال بودن، این گزارش می‌تواند در سیستم ارسال خودکار پیام استفاده شود.
                </small>
            </div>

            <!-- انتخاب الگوی پیامک -->
            <div class="form-group" style="margin-top: 30px;">
                <label class="form-label">
                    <i class="fas fa-sms"></i>
                    الگوی پیامک مرتبط *
                </label>
                <small style="display: block; color: #666; margin-bottom: 10px; font-size: 12px;">
                    باید یک الگوی پیامک را برای این گزارش انتخاب کنید. این الگو در صفحه ارسال پیامک دستی و ارسال خودکار استفاده می‌شود.
                </small>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($patterns) > 0): ?>
                    <select wire:model="selectedPattern" class="form-control" required>
                        <option value="">انتخاب الگوی پیامک</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pattern->id); ?>" <?php echo e($selectedPattern == $pattern->id ? 'selected' : ''); ?>>
                                <?php echo e($pattern->title); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->pattern_code): ?>
                                    (کد: <?php echo e($pattern->pattern_code); ?>)
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedPattern'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #f72585; font-size: 14px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <p style="color: #f72585; font-size: 14px; padding: 15px; background: #fff3cd; border-radius: 6px; border-right: 4px solid #f72585;">
                        <i class="fas fa-exclamation-triangle"></i>
                        هیچ الگوی فعالی یافت نشد. لطفاً ابتدا الگوهایی را در بخش "الگوها" ایجاد کنید.
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    به‌روزرسانی گزارش
                </button>
                <a href="/reports" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به لیست
                </a>
                <button
                    type="button"
                    onclick="confirmDelete(<?php echo e($report->id); ?>, 'Report')"
                    class="btn btn-danger"
                >
                    <i class="fas fa-trash"></i>
                    حذف گزارش
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // تابع تایید حذف
    function confirmDelete(id, type, persianType = null) {
        const typeName = persianType || (type === 'Report' ? 'گزارش' : 'دسته‌بندی');
        
        Swal.fire({
            title: `حذف ${typeName}`,
            text: `آیا مطمئن هستید که می‌خواهید این ${typeName} را حذف کنید؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'لغو',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                if (type === 'Report') {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteReport(id);
                } else {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteCategory(id);
                }
            }
        });
    }
</script>
<?php /**PATH C:\laragon\www\atlas_report\resources\views/livewire/reports/edit.blade.php ENDPATH**/ ?>