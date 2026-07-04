<div>
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg shadow-lg mb-6">
        <h1 class="text-3xl font-bold mb-2">پیام‌های خوش‌آمدگویی اقامت‌گران</h1>
        <p class="text-blue-100">تنظیمات ارسال خودکار پیام خوش‌آمدگویی به اقامت‌گران جدید</p>
    </div>

    <!-- Settings Card -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-6 text-gray-800">تنظیمات سیستم خوش‌آمدگویی</h2>
        
        <form wire:submit="saveSettings">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- گزارش انتخابی -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        گزارش خوش‌آمدگویی <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="selected_report_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">انتخاب گزارش...</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($report->id); ?>"><?php echo e($report->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selected_report_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- تاریخ شروع -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        تاریخ شروع ارسال <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model="welcome_start_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['welcome_start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- فاصله زمانی بررسی -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        فاصله زمانی بررسی (دقیقه) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="welcome_check_interval_minutes" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['welcome_check_interval_minutes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="text-xs text-gray-500 mt-1">هر چند دقیقه یکبار اقامت‌گران جدید بررسی شوند</p>
                </div>

                <!-- وضعیت فعال بودن -->
                <div class="flex items-end">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="welcome_system_active" class="ml-2 w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">سیستم خوش‌آمدگویی فعال</span>
                    </label>
                </div>
            </div>

            <!-- Information Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle ml-2"></i>
                    اطلاعات فیلترهای خودکار
                </h3>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>• فقط اقامت‌گرانی که وضعیت قرارداد آنها (contract_state) برابر "active" باشد</li>
                    <li>• تاریخ ثبت‌نام یا شروع قرارداد از تاریخ شروع انتخاب شده به بعد باشد</li>
                    <li>• قبلاً پیام خوش‌آمدگویی دریافت نکرده باشند</li>
                    <li>• شماره تلفن معتبر داشته باشند</li>
                </ul>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md transition duration-200 flex items-center">
                    <i class="fas fa-save ml-2"></i>
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>

    <!-- Status Card -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($welcome_system_active): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
                <div class="mr-3">
                    <h3 class="text-sm font-medium text-green-800">سیستم خوش‌آمدگویی فعال است</h3>
                    <p class="text-xs text-green-600 mt-1">
                        سیستم هر <?php echo e($welcome_check_interval_minutes); ?> دقیقه یکبار اقامت‌گران جدید را بررسی می‌کند
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-pause-circle text-gray-500 text-xl"></i>
                </div>
                <div class="mr-3">
                    <h3 class="text-sm font-medium text-gray-800">سیستم خوش‌آمدگویی غیرفعال است</h3>
                    <p class="text-xs text-gray-600 mt-1">
                        برای شروع ارسال پیام‌های خوش‌آمدگویی، سیستم را فعال کنید
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Toast Notifications -->
    <script>
        <?php if(session()->has('success')): ?>
            setTimeout(() => {
                alert('<?php echo e(session('success')); ?>');
            }, 100);
        <?php endif; ?>

        <?php if(session()->has('error')): ?>
            setTimeout(() => {
                alert('<?php echo e(session('error')); ?>');
            }, 100);
        <?php endif; ?>
    </script>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\welcome-messages\simple.blade.php ENDPATH**/ ?>