<div>
    <div class="card">
        <div style="margin-bottom: 20px;">
            <h2><i class="fas fa-cog"></i> تنظیمات سیستم</h2>
            <p style="color: #666; margin-top: 10px;">مدیریت تنظیمات عمومی سیستم</p>
        </div>

        <form wire:submit.prevent="save">
            <!-- لینک API -->
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                    <i class="fas fa-link" style="margin-left: 5px;"></i>
                    لینک API اقامت‌گران <span style="color: red;">*</span>
                </label>
                <input
                    type="url"
                    wire:model="api_url"
                    class="form-control"
                    placeholder="مثال: http://atlas2.test/api/residents"
                    style="width: 100%; max-width: 600px;"
                >
                <small style="color: #666; margin-top: 5px; display: block;">
                    آدرس API که داده‌های اقامت‌گران از آن دریافت می‌شود.
                </small>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['api_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                    <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                        <?php echo e($message); ?>

                    </span> 
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- بخش تنظیمات کارت‌ها -->
            <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <h3 style="margin-bottom: 20px; color: #333;">
                    <i class="fas fa-id-card" style="margin-left: 8px; color: #ffc107;"></i>
                    تنظیمات سیستم کارت‌ها
                </h3>

                <!-- تعداد تخلف یکسان برای کارت زرد -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-square" style="margin-left: 5px; color: #ffc107;"></i>
                        تعداد تخلف یکسان برای کارت زرد <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="yellow_violation_count_threshold"
                        class="form-control"
                        placeholder="مثال: 3"
                        min="1"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        تعداد تکرار یک تخلف برای دریافت کارت زرد. پیش‌فرض: 3 بار.
                    </small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['yellow_violation_count_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            <?php echo e($message); ?>

                        </span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- تعداد تخلف یکسان برای کارت قرمز -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-square" style="margin-left: 5px; color: #dc3545;"></i>
                        تعداد تخلف یکسان برای کارت قرمز <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="red_violation_count_threshold"
                        class="form-control"
                        placeholder="مثال: 5"
                        min="1"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        تعداد تکرار یک تخلف برای دریافت کارت قرمز. پیش‌فرض: 5 بار.
                    </small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['red_violation_count_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            <?php echo e($message); ?>

                        </span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- گزارش‌های مستثنی شده -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-filter" style="margin-left: 5px;"></i>
                        گزارش‌های مستثنی شده از محاسبه تخلفات
                    </label>
                    <div style="margin-bottom: 10px;">
                        <select 
                            wire:model="excluded_reports" 
                            multiple
                            class="form-control"
                            style="width: 100%; max-width: 600px; min-height: 150px;"
                        >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($report['id']); ?>" <?php echo e(in_array($report['id'], $excluded_reports) ? 'selected' : ''); ?>>
                                    <?php echo e($report['title']); ?> (<?php echo e($report['category_name']); ?>) - <?php echo e($report['negative_score']); ?> امتیاز
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <small style="color: #666;">
                            گزارش‌هایی را انتخاب کنید که در محاسبه تخلفات و نمایش کارت‌ها در صفحه "گزارش‌های تخلفی" لحاظ نشوند.
                            برای انتخاب چندگانه، کلید Ctrl را نگه دارید و روی موارد مورد نظر کلیک کنید.
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($excluded_reports) > 0): ?>
                            <button 
                                type="button" 
                                wire:click="$set('excluded_reports', [])"
                                class="btn btn-sm btn-outline-danger"
                                style="margin-right: 10px;"
                            >
                                <i class="fas fa-times"></i> پاک کردن انتخاب
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['excluded_reports'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            <?php echo e($message); ?>

                        </span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($excluded_reports) > 0): ?>
                        <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px; border-right: 3px solid #007bff;">
                            <small style="color: #333; font-weight: 500;">
                                <i class="fas fa-info-circle"></i>
                                <?php echo e(count($excluded_reports)); ?> گزارش مستثنی شده:
                            </small>
                            <div style="margin-top: 5px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $excluded_reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reportId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $report = collect($reports)->firstWhere('id', $reportId);
                                        if($report):
                                    ?>
                                        <span style="display: inline-block; margin: 2px; padding: 3px 8px; background: #e3f2fd; border-radius: 12px; font-size: 12px; color: #1976d2;">
                                            <?php echo e($report['title']); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- امتیاز کارت زرد -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-square" style="margin-left: 5px; color: #ffc107;"></i>
                        امتیاز برای کارت زرد <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="yellow_card_threshold"
                        class="form-control"
                        placeholder="مثال: 15"
                        min="1"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        مجموع امتیاز تخلفات که برای دریافت کارت زرد لازم است. پیش‌فرض: 15 امتیاز.
                    </small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['yellow_card_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            <?php echo e($message); ?>

                        </span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- امتیاز کارت قرمز -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-square" style="margin-left: 5px; color: #dc3545;"></i>
                        امتیاز برای کارت قرمز <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="red_card_threshold"
                        class="form-control"
                        placeholder="مثال: 25"
                        min="1"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        مجموع امتیاز تخلفات که برای دریافت کارت قرمز لازم است. پیش‌فرض: 25 امتیاز.
                    </small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['red_card_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            <?php echo e($message); ?>

                        </span> 
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- دکمه ذخیره -->
            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>

    <!-- نمایش تنظیمات فعلی -->
    <div class="card" style="margin-top: 20px;">
        <h3 style="margin-bottom: 15px;">
            <i class="fas fa-info-circle"></i>
            تنظیمات فعلی
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">لینک API</div>
                <div style="font-size: 14px; font-weight: 500; color: #333; word-break: break-all;">
                    <?php echo e($api_url); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // راه‌اندازی مجدد تایمر بعد از ذخیره تنظیمات
        Livewire.on('settings-updated', () => {
            // پاک کردن زمان شروع تایمر برای شروع مجدد
            localStorage.removeItem('timerStartTime');
            localStorage.removeItem('refreshInterval');
            setTimeout(() => {
                location.reload();
            }, 1000);
        });
    });
</script>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\settings\index.blade.php ENDPATH**/ ?>