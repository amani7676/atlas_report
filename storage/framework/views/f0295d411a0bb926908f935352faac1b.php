<div class="container mx-auto px-4 py-8" dir="rtl">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">تست ارسال پیامک الگویی</h2>

            <form wire:submit.prevent="sendTest">
                <!-- انتخاب الگو -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        انتخاب الگو <span class="text-red-500">*</span>
                    </label>
                    <select 
                        wire:model.live="selectedPattern" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- انتخاب الگو --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pattern->id); ?>">
                                <?php echo e($pattern->title); ?> (کد: <?php echo e($pattern->pattern_code); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedPattern'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- نمایش متن الگو -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patternText): ?>
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            متن الگو:
                        </label>
                        <div class="text-gray-800 whitespace-pre-wrap font-medium">
                            <?php echo e($patternText); ?>

                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- ورودی متغیرها -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($variables) > 0): ?>
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">
                                مقادیر متغیرها:
                            </label>
                            <div class="text-xs text-gray-500 bg-blue-50 px-3 py-1 rounded-full">
                                <i class="fas fa-info-circle"></i> مقادیر نمونه به صورت خودکار پر شده‌اند
                            </div>
                        </div>
                        <div class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex-shrink-0 w-32">
                                        <label class="text-sm text-gray-600 font-medium">
                                            <?php echo e($variable['code']); ?>

                                        </label>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?php echo e($variable['title']); ?>

                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variable['table_field']): ?>
                                            <div class="text-xs text-blue-600 mt-1">
                                                فیلد: <?php echo e($variable['table_field']); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$variable['exists_in_db'] ?? true): ?>
                                            <div class="text-xs text-red-600 mt-1 bg-red-50 px-2 py-1 rounded">
                                                <i class="fas fa-exclamation-triangle"></i> تعریف نشده
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <input 
                                            type="text" 
                                            wire:model.live="variableValues.<?php echo e($variable['index']); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            placeholder="مقدار <?php echo e($variable['title']); ?> را وارد کنید"
                                        >
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variable['variable_type'] === 'user'): ?>
                                            <div class="text-xs text-green-600 mt-1">
                                                <i class="fas fa-user"></i> متغیر کاربری
                                            </div>
                                        <?php elseif($variable['variable_type'] === 'report'): ?>
                                            <div class="text-xs text-purple-600 mt-1">
                                                <i class="fas fa-file-alt"></i> متغیر گزارش
                                            </div>
                                        <?php elseif($variable['variable_type'] === 'general'): ?>
                                            <div class="text-xs text-orange-600 mt-1">
                                                <i class="fas fa-cog"></i> متغیر عمومی
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <!-- دکمه پر کردن مجدد مقادیر نمونه -->
                        <div class="mt-4 flex gap-2">
                            <button 
                                type="button"
                                wire:click="extractVariables"
                                class="text-sm text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1 rounded-full border border-blue-200"
                            >
                                <i class="fas fa-refresh"></i> پر کردن مجدد مقادیر نمونه
                            </button>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($variables) > 0): ?>
                                <button 
                                    type="button"
                                    wire:click="debugVariables"
                                    class="text-sm text-orange-600 hover:text-orange-800 bg-orange-50 px-3 py-1 rounded-full border border-orange-200"
                                >
                                    <i class="fas fa-bug"></i> دیباگ متغیرها
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($previewMessage): ?>
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200 border-r-4 border-r-blue-500">
                            <strong class="text-blue-700 block mb-3 flex items-center gap-2">
                                <i class="fas fa-eye"></i> پیش‌نمایش پیام ارسالی:
                            </strong>
                            <div class="bg-white p-4 rounded-lg border border-blue-200 text-gray-800 text-sm leading-relaxed">
                                <?php echo $previewMessage; ?>

                            </div>
                            
                            <?php
                                $variablesArray = [];
                                // پیدا کردن تمام ایندکس‌ها و مرتب کردن اونها (مثل کنترلر)
                                $indices = [];
                                foreach ($variables as $variable) {
                                    $indices[] = $variable['index'];
                                }
                                sort($indices);
                                
                                // پر کردن آرایه بر اساس ترتیب مرتب شده
                                foreach ($indices as $index) {
                                    $value = $variableValues[$index] ?? '';
                                    $variablesArray[] = $value;
                                }
                            ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($variablesArray) > 0): ?>
                                <div class="mt-4 pt-4 border-t border-blue-200">
                                    <strong class="text-gray-600 text-xs block mb-2">متغیرهای ارسالی به API:</strong>
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $index = $variable['index'];
                                                $value = $variableValues[$index] ?? '';
                                            ?>
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-mono">
                                                { <?php echo e($index); ?> }: <?php echo e($value ?: '[خالی]'); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="bg-gray-100 p-3 rounded">
                                        <strong class="text-gray-600 text-xs block mb-1">رشته ارسالی به API (با جداکننده ;):</strong>
                                        <code class="block mt-1 p-2 bg-white rounded text-xs text-left direction-ltr break-all">
                                            <?php echo e(implode(';', $variablesArray)); ?>

                                        </code>
                                    </div>
                                    
                                    <!-- نمایش تطابق با الگو -->
                                    <div class="mt-3 p-3 bg-yellow-50 rounded border border-yellow-200">
                                        <strong class="text-yellow-700 text-xs block mb-1">
                                            <i class="fas fa-info-circle"></i> بررسی تطابق:
                                        </strong>
                                        <?php
                                            preg_match_all('/\{(\d+)\}/', $patternText, $patternMatches);
                                            $expectedCount = count(array_unique($patternMatches[1]));
                                            $actualCount = count($variablesArray);
                                        ?>
                                        <div class="text-xs text-yellow-800 mt-1">
                                            متغیرهای مورد انتظار: <?php echo e($expectedCount); ?> | متغیرهای وارد شده: <?php echo e($actualCount); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expectedCount == $actualCount): ?>
                                                <span class="text-green-600 font-medium"> ✓ تطابق دارد</span>
                                            <?php else: ?>
                                                <span class="text-red-600 font-medium"> ✗ تطابق ندارد</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- شماره فرستنده -->
                <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone-alt text-yellow-600"></i> شماره فرستنده <span class="text-red-500">*</span>
                    </label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($availableSenderNumbers) > 0): ?>
                        <select wire:model.live="selectedSenderNumberId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSenderNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sender): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sender->id); ?>">
                                    <?php echo e($sender->title); ?> (<?php echo e($sender->number); ?>)
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sender->api_key): ?>
                                        - دارای API Key
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-gray-800 font-mono"><?php echo e($senderNumber); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-gray-800 font-mono"><?php echo e($senderNumber); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($senderNumber === 'تنظیم نشده'): ?>
                                <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded">
                                    (لطفاً در فایل .env تنظیم کنید)
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="mt-2 text-xs text-gray-600">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($availableSenderNumbers) > 0): ?>
                            شماره فرستنده را از لیست انتخاب کنید. برای مدیریت شماره‌ها به 
                            <a href="/sender-numbers" target="_blank" class="text-blue-600 underline">صفحه مدیریت شماره‌های فرستنده</a> بروید.
                        <?php else: ?>
                            این شماره برای ارسال پیامک الگویی استفاده می‌شود. برای تغییر، متغیر <code class="bg-gray-100 px-1 rounded">MELIPAYAMAK_PATTERN_FROM</code> را در فایل <code class="bg-gray-100 px-1 rounded">.env</code> تنظیم کنید یا از 
                            <a href="/sender-numbers" target="_blank" class="text-blue-600 underline">صفحه مدیریت شماره‌های فرستنده</a> استفاده کنید.
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>

                <!-- شماره تلفن -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        شماره تلفن گیرنده <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        wire:model="phone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="09123456789"
                        maxlength="11"
                    >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- دکمه ارسال -->
                <div class="flex justify-end gap-4">
                    <button 
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="sendTest">
                            ارسال تست
                        </span>
                        <span wire:loading wire:target="sendTest">
                            در حال ارسال...
                        </span>
                    </button>
                </div>
            </form>

            <!-- نمایش نتیجه -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showResult && $result): ?>
                <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">پاسخ API ملی پیامک</h3>
                    
                    <div class="space-y-4">
                        <!-- وضعیت -->
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-700">وضعیت:</span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result['success'] ?? false): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    ✅ موفق
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                    ❌ ناموفق
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- پیام -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['message'])): ?>
                            <div>
                                <span class="font-medium text-gray-700">پیام:</span>
                                <p class="mt-1 text-gray-800"><?php echo e($result['message']); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- RecId -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['rec_id'])): ?>
                            <div>
                                <span class="font-medium text-gray-700">RecId:</span>
                                <p class="mt-1 text-gray-800 font-mono"><?php echo e($result['rec_id']); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- کد پاسخ -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['response_code'])): ?>
                            <div>
                                <span class="font-medium text-gray-700">کد پاسخ:</span>
                                <p class="mt-1 text-gray-800 font-mono"><?php echo e($result['response_code']); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- پاسخ خام -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['raw_response'])): ?>
                            <div>
                                <span class="font-medium text-gray-700">پاسخ خام API:</span>
                                <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                    <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words"><?php echo e($result['raw_response']); ?></pre>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- پاسخ API (JSON) -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['api_response'])): ?>
                            <div>
                                <span class="font-medium text-gray-700">پاسخ API (JSON):</span>
                                <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                    <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words"><?php echo e(is_array($result['api_response']) ? json_encode($result['api_response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $result['api_response']); ?></pre>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- خطا -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['error'])): ?>
                            <div>
                                <span class="font-medium text-red-700">خطا:</span>
                                <p class="mt-1 text-red-800"><?php echo e($result['error']); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- اطلاعات کامل (برای دیباگ) -->
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-gray-600 hover:text-gray-800">
                                نمایش اطلاعات کامل (برای دیباگ)
                            </summary>
                            <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                <pre class="text-xs text-gray-800 whitespace-pre-wrap break-words"><?php echo e(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        </details>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>


<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\sms\pattern-test.blade.php ENDPATH**/ ?>