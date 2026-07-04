<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>ارسال SMS گروهی</h2>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #4361ee;"></i>
                <p>در حال بارگذاری...</p>
            </div>
        <?php elseif($error): ?>
            <div style="background: #fff3cd; padding: 15px; border-radius: 6px; margin-bottom: 20px; color: #856404;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo e($error); ?>

            </div>
        <?php else: ?>
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedResidents) > 0): ?>
                <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo e(count($selectedResidents)); ?></strong> اقامت‌گر انتخاب شده
                        </div>
                        <button wire:click="openSendModal" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i>
                            ارسال SMS به انتخاب‌شده‌ها
                        </button>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Units and Residents -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filteredUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitIndex => $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $unit['rooms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomIndex => $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($room['beds']) && count(array_filter($room['beds'], fn($bed) => $bed['resident'])) > 0): ?>
                            <div class="card" style="border: 1px solid #ddd;">
                                <div style="background: #4361ee; color: white; padding: 10px; border-radius: 6px 6px 0 0; display: flex; justify-content: space-between; align-items: center;">
                                    <strong><?php echo e($room['name']); ?></strong> - <?php echo e($unit['unit']['name']); ?>

                                </div>
                                <div style="padding: 15px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $room['beds']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bedIndex => $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bed['resident']): ?>
                                            <?php
                                                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                                                $isSelected = isset($selectedResidents[$key]);
                                            ?>
                                            <div style="padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div style="flex: 1;">
                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                            <input
                                                                type="checkbox"
                                                                wire:click="toggleSelectResident(
                                                                    '<?php echo e($key); ?>',
                                                                    <?php echo \Illuminate\Support\Js::from($bed['resident'])->toHtml() ?>,
                                                                    <?php echo \Illuminate\Support\Js::from($bed)->toHtml() ?>,
                                                                    <?php echo e($unitIndex); ?>,
                                                                    <?php echo e($roomIndex); ?>

                                                                )"
                                                                <?php echo e($isSelected ? 'checked' : ''); ?>

                                                                style="cursor: pointer;"
                                                            >
                                                            <div>
                                                                <strong><?php echo e($bed['resident']['full_name'] ?? 'بدون نام'); ?></strong>
                                                                <br>
                                                                <small style="color: #666;"><?php echo e($bed['resident']['phone'] ?? 'بدون شماره'); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
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

    <!-- Modal for sending SMS -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSendModal): ?>
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; padding: 30px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>ارسال SMS به <?php echo e(count($selectedResidents)); ?> نفر</h3>
                    <button wire:click="closeSendModal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>

                <!-- Info about selected residents -->
                <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <p><strong>تعداد اقامت‌گران انتخاب شده:</strong> <?php echo e(count($selectedResidents)); ?> نفر</p>
                </div>

                <form wire:submit.prevent="sendSms">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">پیام SMS *</label>
                        <select wire:model.live="selectedSmsMessage" class="form-control" required>
                            <option value="">انتخاب پیام</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $smsMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sms->id); ?>"><?php echo e($sms->title); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedSmsMessage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedSmsMessage): ?>
                        <?php
                            $selectedMsg = $smsMessages->firstWhere('id', $selectedSmsMessage);
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedMsg): ?>
                            <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                                <p><strong>عنوان پیام:</strong> <?php echo e($selectedMsg->title); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedMsg->description): ?>
                                    <p><strong>توضیحات:</strong> <?php echo e($selectedMsg->description); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <p><strong>متن پیام:</strong></p>
                                <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin-top: 5px;">
                                    <?php echo e($selectedMsg->text); ?>

                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedMsg->link): ?>
                                    <p style="margin-top: 10px;"><strong>لینک:</strong> <a href="<?php echo e($selectedMsg->link); ?>" target="_blank"><?php echo e($selectedMsg->link); ?></a></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" wire:click="closeSendModal" class="btn" style="background: #6c757d; color: white;">لغو</button>
                        <button type="submit" class="btn btn-success" <?php echo e(!$selectedSmsMessage ? 'disabled' : ''); ?>>
                            <i class="fas fa-paper-plane"></i>
                            ارسال به <?php echo e(count($selectedResidents)); ?> نفر
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\sms\group.blade.php ENDPATH**/ ?>