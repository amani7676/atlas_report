<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #10b981;"><i class="fas fa-file-code"></i> ارسال SMS الگویی دستی</h2>
            <button 
                wire:click="syncResidents" 
                wire:loading.attr="disabled"
                class="btn btn-primary"
                style="display: flex; align-items: center; gap: 8px;"
                <?php if($syncing): ?> disabled <?php endif; ?>
            >
                <i class="fas fa-sync-alt" wire:loading.class="fa-spin"></i>
                <span wire:loading.remove>همگام‌سازی از API</span>
                <span wire:loading>در حال همگام‌سازی...</span>
            </button>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #10b981;"></i>
                <p>در حال بارگذاری...</p>
            </div>
        <?php elseif($error): ?>
            <div style="background: #fff3cd; padding: 15px; border-radius: 6px; margin-bottom: 20px; color: #856404;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo e($error); ?>

            </div>
        <?php else: ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResident): ?>
                <div id="sms-form-section" class="card" style="margin-bottom: 30px; border: 2px solid #10b981; background: #f0fdf4;">
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 15px; border-radius: 6px 6px 0 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; color: white;">
                                <i class="fas fa-file-code"></i> ارسال SMS الگویی به <?php echo e($selectedResident['name']); ?>

                            </h3>
                            <button wire:click="clearSelection" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 20px; cursor: pointer; padding: 5px 10px; border-radius: 5px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div style="padding: 25px;">
                        <form wire:submit.prevent="submit">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">گزارش *</label>
                                <select wire:model.live="selectedReport" class="form-control" required>
                                    <option value="">انتخاب گزارش</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($report->id); ?>"><?php echo e($report->title); ?> (<?php echo e($report->category->name); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedReport'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedReport && $reportPatterns && $reportPatterns->count() > 0): ?>
                                    <div style="background: #d1fae5; padding: 10px; border-radius: 6px; margin-top: 10px; border-right: 3px solid #10b981;">
                                        <strong style="display: block; margin-bottom: 8px;">الگوهای مرتبط با این گزارش:</strong>
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportPatterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span 
                                                    style="background: #10b981; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;"
                                                >
                                                    <?php echo e($pattern->title); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">الگوی پیامک *</label>
                                <select wire:model.live="selectedPattern" class="form-control" required>
                                    <option value="">انتخاب الگو</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedReport && $reportPatterns && $reportPatterns->count() > 0): ?>
                                        <optgroup label="الگوهای مرتبط با گزارش">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportPatterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($pattern->id); ?>">
                                                    <?php echo e($pattern->title); ?> 
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->pattern_code): ?>
                                                        (کد: <?php echo e($pattern->pattern_code); ?>)
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </optgroup>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <optgroup label="همه الگوها">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($pattern->id); ?>">
                                                <?php echo e($pattern->title); ?> 
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->pattern_code): ?>
                                                    (کد: <?php echo e($pattern->pattern_code); ?>)
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </optgroup>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPattern): ?>
                                    <?php
                                        $selectedPatternObj = $patterns->firstWhere('id', $selectedPattern);
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPatternObj): ?>
                                        <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; margin-top: 10px;">
                                            <strong>متن الگو:</strong>
                                            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;"><?php echo e($selectedPatternObj->text); ?></p>
                                        </div>
                                        
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($previewMessage): ?>
                                            <div style="background: #d1fae5; padding: 15px; border-radius: 6px; margin-top: 10px; border-right: 3px solid #10b981;">
                                                <strong style="color: #059669; display: block; margin-bottom: 10px;">
                                                    <i class="fas fa-eye"></i> پیش‌نمایش پیام ارسالی:
                                                </strong>
                                                <div style="background: white; padding: 12px; border-radius: 5px; border: 1px solid #dee2e6; font-size: 14px; line-height: 1.8;">
                                                    <?php echo $previewMessage; ?>

                                                </div>
                                                
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($previewVariables) > 0): ?>
                                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                                                        <strong style="color: #666; font-size: 12px; display: block; margin-bottom: 5px;">متغیرهای ارسالی به API:</strong>
                                                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $previewVariables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <span style="background: #a7f3d0; color: #059669; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-family: monospace;">
                                                                    { <?php echo e($index); ?> }: <?php echo e($variable); ?>

                                                                </span>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                        <div style="margin-top: 8px; padding: 8px; background: #f8f9fa; border-radius: 3px;">
                                                            <strong style="color: #666; font-size: 11px;">رشته ارسالی به API (با جداکننده ;):</strong>
                                                            <code style="display: block; margin-top: 5px; padding: 5px; background: white; border-radius: 3px; font-size: 11px; direction: ltr; text-align: left; word-break: break-all;">
                                                                <?php echo e(implode(';', $previewVariables)); ?>

                                                            </code>
                                                        </div>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedPattern'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">یادداشت (اختیاری)</label>
                                <textarea wire:model="notes" class="form-control" rows="3" placeholder="یادداشت..."></textarea>
                            </div>

                            <!-- شماره فرستنده -->
                            <div class="form-group" style="margin-bottom: 20px; padding: 12px; background: #fef3c7; border-radius: 6px; border-right: 3px solid #f59e0b;">
                                <label class="form-label" style="color: #92400e; font-weight: 600;">
                                    <i class="fas fa-phone-alt"></i> شماره فرستنده <span class="text-danger">*</span>
                                </label>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($availableSenderNumbers) > 0): ?>
                                    <select wire:model.live="selectedSenderNumberId" class="form-control" style="margin-top: 8px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSenderNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sender): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sender->id); ?>">
                                                <?php echo e($sender->title); ?> (<?php echo e($sender->number); ?>)
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sender->api_key): ?>
                                                    - دارای API Key
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                                        <span style="font-size: 16px; font-weight: bold; color: #1f2937; font-family: monospace;"><?php echo e($senderNumber); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                                        <span style="font-size: 18px; font-weight: bold; color: #1f2937; font-family: monospace;"><?php echo e($senderNumber); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($senderNumber === 'تنظیم نشده'): ?>
                                            <span style="font-size: 11px; color: #dc2626; background: #fee2e2; padding: 4px 8px; border-radius: 4px;">
                                                (لطفاً در فایل .env تنظیم کنید)
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                                <button type="button" wire:click="clearSelection" class="btn" style="background: #6c757d; color: white;">لغو</button>
                                <button type="submit" class="btn" style="background: #10b981; color: white; border: none;">
                                    <i class="fas fa-paper-plane"></i>
                                    ثبت گزارش و ارسال SMS الگویی
                                </button>
                            </div>
                        </form>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showResult && $result): ?>
                            <div data-result-section class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
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

                                    <!-- HTTP Status Code -->
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['http_status_code'])): ?>
                                        <div>
                                            <span class="font-medium text-gray-700">کد وضعیت HTTP:</span>
                                            <p class="mt-1 text-gray-800 font-mono"><?php echo e($result['http_status_code']); ?></p>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <!-- پاسخ خام -->
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['raw_response'])): ?>
                                        <div>
                                            <span class="font-medium text-gray-700">پاسخ خام API:</span>
                                            <div class="mt-2 p-3 bg-white rounded border border-gray-300 max-h-64 overflow-y-auto">
                                                <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words font-mono"><?php echo e($result['raw_response']); ?></pre>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <!-- پاسخ API (JSON) -->
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['api_response'])): ?>
                                        <div>
                                            <span class="font-medium text-gray-700">پاسخ API (JSON):</span>
                                            <div class="mt-2 p-3 bg-white rounded border border-gray-300 max-h-64 overflow-y-auto">
                                                <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words font-mono"><?php echo e(is_array($result['api_response']) ? json_encode($result['api_response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $result['api_response']); ?></pre>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <!-- تمام اطلاعات نتیجه (برای دیباگ) -->
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result) && is_array($result)): ?>
                                        <div>
                                            <span class="font-medium text-gray-700">تمام اطلاعات پاسخ:</span>
                                            <div class="mt-2 p-3 bg-white rounded border border-gray-300 max-h-96 overflow-y-auto">
                                                <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words font-mono"><?php echo e(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); ?></pre>
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

                                    <!-- وضعیت ثبت گزارش -->
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['report_created'])): ?>
                                        <div style="margin-top: 15px; padding: 12px; background: <?php echo e($result['report_created'] ? '#d1fae5' : '#fee2e2'); ?>; border-radius: 6px; border-right: 3px solid <?php echo e($result['report_created'] ? '#10b981' : '#dc2626'); ?>;">
                                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($result['report_created']): ?>
                                                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 18px;"></i>
                                                    <strong style="color: #059669; font-size: 14px;">گزارش ثبت شد</strong>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['resident_report_id'])): ?>
                                                        <span style="font-size: 11px; color: #666; margin-right: 5px;">
                                                            (ID: <?php echo e($result['resident_report_id']); ?>)
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle" style="color: #dc2626; font-size: 18px;"></i>
                                                    <strong style="color: #dc2626; font-size: 14px;">گزارش ثبت نشد</strong>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($result['report_error']) && $result['report_error']): ?>
                                                <div style="margin-top: 8px; padding: 8px; background: white; border-radius: 4px; border: 1px solid #fca5a5;">
                                                    <strong style="color: #dc2626; font-size: 12px; display: block; margin-bottom: 4px;">دلیل خطا:</strong>
                                                    <span style="color: #991b1b; font-size: 11px; font-family: monospace;"><?php echo e($result['report_error']); ?></span>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Search -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-search" style="color: #666;"></i>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="جستجوی اقامت‌گر..."
                        class="form-control"
                        style="width: 300px;"
                    >
                </div>
            </div>

            <!-- Units and Residents -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filteredUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitIndex => $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $unit['rooms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomIndex => $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($room['beds']) && count(array_filter($room['beds'], fn($bed) => $bed['resident'])) > 0): ?>
                            <div class="card" style="border: 1px solid #ddd;">
                                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px; border-radius: 6px 6px 0 0;">
                                    <strong><?php echo e($room['name']); ?></strong> - <?php echo e($unit['unit']['name']); ?>

                                </div>
                                <div style="padding: 15px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $room['beds']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bedIndex => $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bed['resident']): ?>
                                            <div style="padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong><?php echo e($bed['resident']['full_name'] ?? 'بدون نام'); ?></strong>
                                                        <br>
                                                        <small style="color: #666;"><?php echo e($bed['resident']['phone'] ?? 'بدون شماره'); ?></small>
                                                    </div>
                                                    <button
                                                        wire:click="selectResident(
                                                            <?php echo \Illuminate\Support\Js::from($bed['resident'])->toHtml() ?>,
                                                            <?php echo \Illuminate\Support\Js::from($bed)->toHtml() ?>,
                                                            <?php echo e($unitIndex); ?>,
                                                            <?php echo e($roomIndex); ?>

                                                        )"
                                                        class="btn btn-sm"
                                                        style="background: #10b981; color: white; border: none; font-size: 12px;"
                                                    >
                                                        <i class="fas fa-paper-plane"></i> ارسال
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($filteredUnits) === 0): ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <p>هیچ اقامت‌گری یافت نشد</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    

    
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scrollToForm', () => {
                setTimeout(() => {
                    const formSection = document.getElementById('sms-form-section');
                    if (formSection) {
                        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // کمی بالاتر از فرم برای نمایش بهتر
                        window.scrollBy(0, -20);
                    }
                }, 200);
            });
            
            Livewire.on('resultReady', (data) => {
                console.log('Result ready:', data);
                // بعد از re-render، به نتیجه اسکرول کن
                setTimeout(() => {
                    const resultSection = document.querySelector('[data-result-section]');
                    if (resultSection) {
                        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 500);
            });
        });
        
        document.addEventListener('livewire:update', () => {
            // اگر نتیجه نمایش داده شد، به آن اسکرول کن
            setTimeout(() => {
                const resultSection = document.querySelector('[data-result-section]');
                if (resultSection) {
                    resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 300);
        });

    </script>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\sms\pattern-manual.blade.php ENDPATH**/ ?>