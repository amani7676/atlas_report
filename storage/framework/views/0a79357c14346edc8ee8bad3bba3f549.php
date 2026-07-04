<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-robot"></i> مدیریت پیامک‌های خودکار</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد پیامک خودکار جدید
            </button>
        </div>

        <!-- Search -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی پیامک خودکار..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- Auto SMS Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>نوع ارسال</th>
                        <th>زمان ارسال</th>
                        <th>تعداد شرط‌ها</th>
                        <th>وضعیت</th>
                        <th>تعداد ارسال شده</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $autoSmsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $autoSms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary-color);"><?php echo e($autoSms->title); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSms->pattern): ?>
                                    <br><small class="text-info"><i class="fas fa-file-code"></i> الگو: <?php echo e($autoSms->pattern->title); ?></small>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSms->description): ?>
                                    <br><small class="text-muted"><?php echo e(Str::limit($autoSms->description, 50)); ?></small>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSms->send_type === 'immediate'): ?>
                                    <span class="badge bg-success">در لحظه</span>
                                <?php else: ?>
                                    <span class="badge bg-info">زمان‌دار</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSms->send_type === 'scheduled' && $autoSms->scheduled_at): ?>
                                    <span style="color: #666;"><?php echo e(jalaliDate($autoSms->scheduled_at, 'Y/m/d H:i')); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">وقتی شرط برقرار شد</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo e($autoSms->conditions->count()); ?></span>
                            </td>
                            <td>
                                <button 
                                    wire:click="toggleActive(<?php echo e($autoSms->id); ?>)"
                                    class="btn btn-sm <?php echo e($autoSms->is_active ? 'btn-success' : 'btn-secondary'); ?>"
                                    title="<?php echo e($autoSms->is_active ? 'غیرفعال کردن' : 'فعال کردن'); ?>"
                                >
                                    <i class="fas fa-<?php echo e($autoSms->is_active ? 'check-circle' : 'times-circle'); ?>"></i>
                                    <?php echo e($autoSms->is_active ? 'فعال' : 'غیرفعال'); ?>

                                </button>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo e($autoSms->total_sent); ?></span>
                            </td>
                            <td>
                                <span style="color: #666; font-size: 14px;">
                                    <?php echo e(jalaliDate($autoSms->created_at, 'Y/m/d H:i')); ?>

                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button 
                                        wire:click="openConditionModal(<?php echo e($autoSms->id); ?>)" 
                                        class="btn btn-sm btn-info"
                                        title="مدیریت شرط‌ها"
                                    >
                                        <i class="fas fa-filter"></i>
                                    </button>
                                    <button 
                                        wire:click="openModal(<?php echo e($autoSms->id); ?>)" 
                                        class="btn btn-sm btn-primary"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        wire:click="delete(<?php echo e($autoSms->id); ?>)" 
                                        wire:confirm="آیا مطمئن هستید که می‌خواهید این پیامک خودکار را حذف کنید؟"
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
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                <p>هیچ پیامک خودکاری یافت نشد.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSmsList->hasPages()): ?>
            <div style="margin-top: 20px;">
                <?php echo e($autoSmsList->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Modal برای ایجاد/ویرایش پیامک خودکار -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;" wire:click="closeModal">
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 1000px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">
                        <i class="fas fa-robot"></i>
                        <?php echo e($editingId ? 'ویرایش پیامک خودکار' : 'ایجاد پیامک خودکار جدید'); ?>

                    </h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <!-- عنوان -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            عنوان <span style="color: red;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="title"
                            class="form-control"
                            placeholder="مثال: ارسال پیامک به اقامت‌گران با تخلف بالا"
                            style="width: 100%;"
                        >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- انتخاب الگوی پیام -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            الگوی پیام <span style="color: red;">*</span>
                        </label>
                        <select wire:model.live="pattern_id" class="form-control" style="width: 100%;">
                            <option value="">انتخاب الگوی پیام...</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($pattern->id); ?>"><?php echo e($pattern->title); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pattern_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern_id): ?>
                            <?php
                                $selectedPattern = $patterns->firstWhere('id', $pattern_id);
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPattern): ?>
                                <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 6px; border-right: 3px solid var(--primary-color);">
                                    <strong>پیش‌نمایش الگو:</strong>
                                    <p style="margin: 5px 0 0 0; color: #666; white-space: pre-wrap;"><?php echo e($selectedPattern->text); ?></p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <small class="text-muted">الگوی پیام از بخش "پیام‌های الگویی" انتخاب می‌شود</small>
                    </div>

                    <!-- نوع ارسال -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            نوع ارسال <span style="color: red;">*</span>
                        </label>
                        <select wire:model.live="send_type" class="form-control" style="width: 100%;">
                            <option value="immediate">در لحظه (وقتی شرط برقرار شد)</option>
                            <option value="scheduled">زمان‌دار (در زمان مشخص)</option>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['send_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- زمان ارسال (فقط برای زمان‌دار) -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($send_type === 'scheduled'): ?>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                زمان ارسال <span style="color: red;">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                wire:model="scheduled_at"
                                class="form-control"
                                style="width: 100%;"
                            >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['scheduled_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- فعال/غیرفعال -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" wire:model="is_active" style="cursor: pointer;">
                            <span>فعال</span>
                        </label>
                    </div>

                    <!-- توضیحات -->
                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            توضیحات
                        </label>
                        <textarea
                            wire:model="description"
                            class="form-control"
                            rows="2"
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

                    <!-- انتخاب جداول مرتبط -->
                    <div style="margin-bottom: 25px; border-top: 2px solid #eee; padding-top: 20px;">
                        <h5 style="margin-bottom: 15px; color: var(--primary-color);">
                            <i class="fas fa-database"></i> انتخاب جداول مرتبط
                        </h5>
                        <small style="display: block; color: #666; margin-bottom: 10px;">
                            ابتدا جداولی که می‌خواهید در شرط‌ها استفاده کنید را انتخاب کنید. سپس فقط فیلدهای این جداول در بخش شرط‌ها نمایش داده می‌شوند.
                        </small>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tableKey => $tableLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; border: 2px solid <?php echo e(in_array($tableKey, $related_tables) ? '#4361ee' : '#ddd'); ?>; border-radius: 8px; cursor: pointer; background: <?php echo e(in_array($tableKey, $related_tables) ? '#e7f3ff' : '#fff'); ?>; transition: all 0.3s; user-select: none;">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="related_tables"
                                        value="<?php echo e($tableKey); ?>"
                                        style="cursor: pointer;"
                                    >
                                    <span style="font-weight: 500;"><?php echo e($tableLabel); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- بخش مدیریت شرط‌ها -->
                    <div style="margin-bottom: 25px; border-top: 2px solid #eee; padding-top: 20px;">
                        <h5 style="margin-bottom: 15px; color: var(--primary-color);">
                            <i class="fas fa-filter"></i> شرط‌های ارسال
                        </h5>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($related_tables)): ?>
                            <div style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; margin-bottom: 15px;">
                                <i class="fas fa-exclamation-triangle" style="color: #856404;"></i>
                                <strong style="color: #856404;">لطفاً ابتدا جداول مرتبط را انتخاب کنید.</strong>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php
                            $interConditions = collect($conditions)->where('condition_type', 'inter')->values()->all();
                            $checkConditions = collect($conditions)->where('condition_type', 'check')->values()->all();
                            $changeConditions = collect($conditions)->where('condition_type', 'change')->values()->all();
                        ?>

                        <!-- بخش شرط ورود (inter) -->
                        <div style="margin-bottom: 30px; border: 2px solid #4361ee; border-radius: 8px; padding: 20px; background: #f8f9ff;">
                            <h6 style="margin-bottom: 15px; color: #4361ee; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-sign-in-alt"></i> شرط ورود (Inter)
                            </h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($interConditions) > 0): ?>
                            <div style="margin-bottom: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>ترتیب</th>
                                                <th>نوع</th>
                                                <th>فیلد</th>
                                                <th>عملگر</th>
                                                <th>مقدار</th>
                                                <th>منطقی</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $interConditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conditionIndex => $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $originalIndex = array_search($condition, $conditions);
                                                ?>
                                                <tr>
                                                    <td><?php echo e($conditionIndex + 1); ?></td>
                                                    <td>
                                                        <?php
                                                            $typeLabels = [
                                                                'resident' => 'اقامت‌گر',
                                                                'resident_report' => 'گزارش اقامت‌گر',
                                                                'report' => 'گزارش',
                                                            ];
                                                        ?>
                                                        <span class="badge bg-info"><?php echo e($typeLabels[$condition['field_type']] ?? $condition['field_type']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo e($this->availableFields[$condition['field_type']][$condition['field_name']] ?? $condition['field_name']); ?>

                                                    </td>
                                                    <td>
                                                        <?php
                                                            $operatorLabels = [
                                                                '>' => '>',
                                                                '<' => '<',
                                                                '=' => '=',
                                                                '>=' => '>=',
                                                                '<=' => '<=',
                                                                '!=' => '!=',
                                                                'contains' => 'شامل',
                                                                'not_contains' => 'شامل نباشد',
                                                                'days_after' => 'بعد از (روز)',
                                                                'days_before' => 'قبل از (روز)',
                                                            ];
                                                        ?>
                                                        <span class="badge bg-secondary"><?php echo e($operatorLabels[$condition['operator']] ?? $condition['operator']); ?></span>
                                                    </td>
                                                    <td><strong><?php echo e($condition['value']); ?></strong></td>
                                                    <td>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($conditionIndex > 0): ?>
                                                            <span class="badge bg-warning"><?php echo e($condition['logical_operator'] ?? 'AND'); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div style="display: flex; gap: 5px;">
                                                            <button 
                                                                wire:click="editCondition(<?php echo e($originalIndex); ?>)" 
                                                                class="btn btn-sm btn-primary"
                                                                title="ویرایش"
                                                            >
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button 
                                                                wire:click="removeCondition(<?php echo e($originalIndex); ?>)" 
                                                                wire:confirm="آیا مطمئن هستید؟"
                                                                class="btn btn-sm btn-danger"
                                                                title="حذف"
                                                            >
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php else: ?>
                                <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; text-align: center; color: #666; margin-bottom: 20px;">
                                    <i class="fas fa-info-circle"></i> هنوز شرطی اضافه نشده است.
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button 
                                wire:click="$set('condition_type', 'inter')" 
                                class="btn btn-sm btn-primary"
                                style="width: 100%;"
                            >
                                <i class="fas fa-plus"></i> اضافه کردن شرط ورود
                            </button>
                        </div>

                        <!-- بخش شرط چک (check) -->
                        <div style="margin-bottom: 30px; border: 2px solid #10b981; border-radius: 8px; padding: 20px; background: #f0fdf4;">
                            <h6 style="margin-bottom: 15px; color: #10b981; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-check-circle"></i> شرط چک (Check)
                            </h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($checkConditions) > 0): ?>
                            <div style="margin-bottom: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>ترتیب</th>
                                                <th>نوع</th>
                                                <th>فیلد</th>
                                                <th>عملگر</th>
                                                <th>مقدار</th>
                                                <th>منطقی</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $checkConditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conditionIndex => $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $originalIndex = array_search($condition, $conditions);
                                                ?>
                                                <tr>
                                                    <td><?php echo e($conditionIndex + 1); ?></td>
                                                    <td>
                                                        <?php
                                                            $typeLabels = [
                                                                'resident' => 'اقامت‌گر',
                                                                'resident_report' => 'گزارش اقامت‌گر',
                                                                'report' => 'گزارش',
                                                            ];
                                                        ?>
                                                        <span class="badge bg-info"><?php echo e($typeLabels[$condition['field_type']] ?? $condition['field_type']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo e($this->availableFields[$condition['field_type']][$condition['field_name']] ?? $condition['field_name']); ?>

                                                    </td>
                                                    <td>
                                                        <?php
                                                            $operatorLabels = [
                                                                '>' => '>',
                                                                '<' => '<',
                                                                '=' => '=',
                                                                '>=' => '>=',
                                                                '<=' => '<=',
                                                                '!=' => '!=',
                                                                'contains' => 'شامل',
                                                                'not_contains' => 'شامل نباشد',
                                                                'days_after' => 'بعد از (روز)',
                                                                'days_before' => 'قبل از (روز)',
                                                            ];
                                                        ?>
                                                        <span class="badge bg-secondary"><?php echo e($operatorLabels[$condition['operator']] ?? $condition['operator']); ?></span>
                                                    </td>
                                                    <td><strong><?php echo e($condition['value']); ?></strong></td>
                                                    <td>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($conditionIndex > 0): ?>
                                                            <span class="badge bg-warning"><?php echo e($condition['logical_operator'] ?? 'AND'); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div style="display: flex; gap: 5px;">
                                                            <button 
                                                                wire:click="editCondition(<?php echo e($originalIndex); ?>)" 
                                                                class="btn btn-sm btn-primary"
                                                                title="ویرایش"
                                                            >
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button 
                                                                wire:click="removeCondition(<?php echo e($originalIndex); ?>)" 
                                                                wire:confirm="آیا مطمئن هستید؟"
                                                                class="btn btn-sm btn-danger"
                                                                title="حذف"
                                                            >
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php else: ?>
                                <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; text-align: center; color: #666; margin-bottom: 20px;">
                                    <i class="fas fa-info-circle"></i> هنوز شرطی اضافه نشده است.
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button 
                                wire:click="$set('condition_type', 'check')" 
                                class="btn btn-sm btn-success"
                                style="width: 100%;"
                            >
                                <i class="fas fa-plus"></i> اضافه کردن شرط چک
                            </button>
                        </div>

                        <!-- بخش شرط تغییرات (change) -->
                        <div style="margin-bottom: 30px; border: 2px solid #f59e0b; border-radius: 8px; padding: 20px; background: #fffbeb;">
                            <h6 style="margin-bottom: 15px; color: #f59e0b; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-exchange-alt"></i> شرط تغییرات (Change)
                            </h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($changeConditions) > 0): ?>
                            <div style="margin-bottom: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" style="font-size: 12px;">
                                        <thead>
                                            <tr>
                                                <th>ترتیب</th>
                                                <th>نوع</th>
                                                <th>فیلد</th>
                                                <th>عملگر</th>
                                                <th>مقدار</th>
                                                <th>منطقی</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $changeConditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conditionIndex => $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $originalIndex = array_search($condition, $conditions);
                                                ?>
                                                <tr>
                                                    <td><?php echo e($conditionIndex + 1); ?></td>
                                                    <td>
                                                        <?php
                                                            $typeLabels = [
                                                                'resident' => 'اقامت‌گر',
                                                                'resident_report' => 'گزارش اقامت‌گر',
                                                                'report' => 'گزارش',
                                                            ];
                                                        ?>
                                                        <span class="badge bg-info"><?php echo e($typeLabels[$condition['field_type']] ?? $condition['field_type']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php echo e($this->availableFields[$condition['field_type']][$condition['field_name']] ?? $condition['field_name']); ?>

                                                    </td>
                                                    <td>
                                                        <?php
                                                            $operatorLabels = [
                                                                '>' => '>',
                                                                '<' => '<',
                                                                '=' => '=',
                                                                '>=' => '>=',
                                                                '<=' => '<=',
                                                                '!=' => '!=',
                                                                'contains' => 'شامل',
                                                                'not_contains' => 'شامل نباشد',
                                                                'days_after' => 'بعد از (روز)',
                                                                'days_before' => 'قبل از (روز)',
                                                            ];
                                                        ?>
                                                        <span class="badge bg-secondary"><?php echo e($operatorLabels[$condition['operator']] ?? $condition['operator']); ?></span>
                                                    </td>
                                                    <td><strong><?php echo e($condition['value']); ?></strong></td>
                                                    <td>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($conditionIndex > 0): ?>
                                                            <span class="badge bg-warning"><?php echo e($condition['logical_operator'] ?? 'AND'); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div style="display: flex; gap: 5px;">
                                                            <button 
                                                                wire:click="editCondition(<?php echo e($originalIndex); ?>)" 
                                                                class="btn btn-sm btn-primary"
                                                                title="ویرایش"
                                                            >
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button 
                                                                wire:click="removeCondition(<?php echo e($originalIndex); ?>)" 
                                                                wire:confirm="آیا مطمئن هستید؟"
                                                                class="btn btn-sm btn-danger"
                                                                title="حذف"
                                                            >
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php else: ?>
                                <div style="padding: 15px; background: #f8f9fa; border-radius: 6px; text-align: center; color: #666; margin-bottom: 20px;">
                                    <i class="fas fa-info-circle"></i> هنوز شرطی اضافه نشده است.
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button 
                                wire:click="$set('condition_type', 'change')" 
                                class="btn btn-sm btn-warning"
                                style="width: 100%;"
                            >
                                <i class="fas fa-plus"></i> اضافه کردن شرط تغییرات
                            </button>
                        </div>

                        <!-- فرم اضافه/ویرایش شرط -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_type || $editingConditionId !== null): ?>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 15px; border: 2px solid <?php echo e($condition_type === 'inter' ? '#4361ee' : ($condition_type === 'check' ? '#10b981' : '#f59e0b')); ?>;">
                            <h6 style="margin-bottom: 15px;">
                                <?php echo e($editingConditionId !== null ? 'ویرایش شرط' : 'اضافه کردن شرط جدید'); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_type): ?>
                                    <span class="badge" style="background: <?php echo e($condition_type === 'inter' ? '#4361ee' : ($condition_type === 'check' ? '#10b981' : '#f59e0b')); ?>; color: white; margin-right: 10px;">
                                        <?php echo e($condition_type === 'inter' ? 'شرط ورود' : ($condition_type === 'check' ? 'شرط چک' : 'شرط تغییرات')); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </h6>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 10px;">
                                <!-- نوع شرط (فقط در حالت اضافه کردن) -->
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingConditionId === null): ?>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 500;">نوع شرط</label>
                                    <select wire:model="condition_type" class="form-control form-control-sm">
                                        <option value="inter">شرط ورود (Inter)</option>
                                        <option value="check">شرط چک (Check)</option>
                                        <option value="change">شرط تغییرات (Change)</option>
                                    </select>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <!-- نوع فیلد -->
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 500;">نوع فیلد</label>
                                    <select wire:model.live="condition_field_type" class="form-control form-control-sm" <?php echo e(empty($related_tables) ? 'disabled' : ''); ?>>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $related_tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tableKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($availableTables[$tableKey])): ?>
                                                <option value="<?php echo e($tableKey); ?>"><?php echo e($availableTables[$tableKey]); ?></option>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </div>

                                <!-- نام فیلد -->
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 500;">فیلد</label>
                                    <select wire:model.live="condition_field_name" class="form-control form-control-sm" <?php echo e(empty($related_tables) || empty($condition_field_type) ? 'disabled' : ''); ?>>
                                        <option value="">انتخاب کنید...</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($condition_field_type) && isset($this->filteredFields[$condition_field_type]) && is_array($this->filteredFields[$condition_field_type])): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->filteredFields[$condition_field_type]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $fieldLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($fieldKey); ?>"><?php echo e($fieldLabel); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <option value="" disabled>ابتدا نوع فیلد را انتخاب کنید</option>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </div>

                                <!-- عملگر -->
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 500;">عملگر</label>
                                    <select wire:model="condition_operator" class="form-control form-control-sm">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_data_type === 'date'): ?>
                                            <option value="days_after">بعد از (روز)</option>
                                            <option value="days_before">قبل از (روز)</option>
                                            <option value="=">مساوی با</option>
                                            <option value=">">بعد از</option>
                                            <option value="<">قبل از</option>
                                            <option value=">=">بعد از یا مساوی</option>
                                            <option value="<=">قبل از یا مساوی</option>
                                        <?php else: ?>
                                            <option value="=">=</option>
                                            <option value=">">></option>
                                            <option value="<"><</option>
                                            <option value=">=">>=</option>
                                            <option value="<="><=</option>
                                            <option value="!=">!=</option>
                                            <option value="contains">شامل</option>
                                            <option value="not_contains">شامل نباشد</option>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </div>

                                <!-- مقدار -->
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 500;">مقدار</label>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_data_type === 'date'): ?>
                                        <input type="number" wire:model="condition_value" class="form-control form-control-sm" placeholder="تعداد روز..." min="0">
                                        <small class="text-muted" style="font-size: 10px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_operator === 'days_after'): ?>
                                                روز بعد از تاریخ فیلد
                                            <?php elseif($condition_operator === 'days_before'): ?>
                                                روز قبل از تاریخ فیلد
                                            <?php else: ?>
                                                تعداد روز (مثلاً: 2 = 2 روز)
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </small>
                                    <?php elseif($condition_data_type === 'number'): ?>
                                        <input type="number" wire:model="condition_value" class="form-control form-control-sm" step="any">
                                    <?php elseif($condition_data_type === 'boolean'): ?>
                                        <select wire:model="condition_value" class="form-control form-control-sm">
                                            <option value="">انتخاب...</option>
                                            <option value="1">بله</option>
                                            <option value="0">خیر</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" wire:model="condition_value" class="form-control form-control-sm" placeholder="مقدار...">
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <!-- عملگر منطقی -->
                                <?php
                                    $currentTypeConditions = collect($conditions)->where('condition_type', $condition_type ?? 'inter')->values()->all();
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($currentTypeConditions) > 0): ?>
                                    <div>
                                        <label style="display: block; margin-bottom: 5px; font-size: 12px; font-weight: 500;">عملگر منطقی</label>
                                        <select wire:model="condition_logical_operator" class="form-control form-control-sm">
                                            <option value="AND">AND (و)</option>
                                            <option value="OR">OR (یا)</option>
                                        </select>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingConditionId !== null): ?>
                                    <button type="button" wire:click="updateCondition" class="btn btn-sm btn-success">
                                        <i class="fas fa-save"></i> به‌روزرسانی
                                    </button>
                                    <button type="button" wire:click="cancelEditCondition" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-times"></i> لغو
                                    </button>
                                <?php else: ?>
                                    <button type="button" wire:click="addCondition" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> اضافه کردن شرط
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    <!-- Modal برای مدیریت شرط‌ها -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showConditionModal && $currentAutoSmsId): ?>
        <?php
            $autoSms = \App\Models\AutoSms::with('conditions')->find($currentAutoSmsId);
            // نمایش هشدار در صورت عدم انتخاب جدول
            $hasRelatedTables = !empty($related_tables);
        ?>
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;" wire:click="closeConditionModal">
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 900px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">
                        <i class="fas fa-filter"></i>
                        مدیریت شرط‌های: <?php echo e($autoSms->title); ?>

                    </h3>
                    <button wire:click="closeConditionModal" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- لیست شرط‌های موجود -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoSms->conditions->count() > 0): ?>
                    <div style="margin-bottom: 25px;">
                        <h5 style="margin-bottom: 15px;">شرط‌های موجود:</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ترتیب</th>
                                        <th>نوع</th>
                                        <th>فیلد</th>
                                        <th>نوع داده</th>
                                        <th>عملگر</th>
                                        <th>مقدار</th>
                                        <th>عملگر منطقی</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $autoSms->conditions->sortBy('order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($condition->order); ?></td>
                                            <td>
                                                <?php
                                                    $typeLabels = [
                                                        'resident' => 'اقامت‌گر',
                                                        'resident_report' => 'گزارش اقامت‌گر',
                                                        'report' => 'گزارش',
                                                    ];
                                                ?>
                                                <span class="badge bg-info"><?php echo e($typeLabels[$condition->field_type] ?? $condition->field_type); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                    $fieldLabels = array_merge(
                                                        $this->availableFields['resident'] ?? [],
                                                        $this->availableFields['resident_report'] ?? [],
                                                        $this->availableFields['report'] ?? []
                                                    );
                                                ?>
                                                <?php echo e($fieldLabels[$condition->field_name] ?? $condition->field_name); ?>

                                            </td>
                                            <td>
                                                <?php
                                                    $dataTypeLabels = [
                                                        'string' => 'رشته',
                                                        'number' => 'عدد',
                                                        'date' => 'تاریخ',
                                                        'boolean' => 'بولین',
                                                    ];
                                                ?>
                                                <span class="badge bg-success"><?php echo e($dataTypeLabels[$condition->data_type ?? 'string'] ?? 'رشته'); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                    $operatorLabels = [
                                                        '>' => 'بزرگتر از',
                                                        '<' => 'کوچکتر از',
                                                        '=' => 'مساوی با',
                                                        '>=' => 'بزرگتر یا مساوی',
                                                        '<=' => 'کوچکتر یا مساوی',
                                                        '!=' => 'مخالف',
                                                        'contains' => 'شامل',
                                                        'not_contains' => 'شامل نباشد',
                                                    ];
                                                ?>
                                                <span class="badge bg-secondary"><?php echo e($operatorLabels[$condition->operator] ?? $condition->operator); ?></span>
                                            </td>
                                            <td><strong><?php echo e($condition->value); ?></strong></td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition->order > 0): ?>
                                                    <span class="badge bg-warning"><?php echo e($condition->logical_operator); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <button 
                                                        wire:click="openConditionModal(<?php echo e($autoSms->id); ?>, <?php echo e($condition->id); ?>)" 
                                                        class="btn btn-sm btn-primary"
                                                        title="ویرایش"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button 
                                                        wire:click="deleteCondition(<?php echo e($condition->id); ?>)" 
                                                        wire:confirm="آیا مطمئن هستید؟"
                                                        class="btn btn-sm btn-danger"
                                                        title="حذف"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- فرم اضافه/ویرایش شرط -->
                <div style="border-top: 2px solid #eee; padding-top: 20px;">
                    <h5 style="margin-bottom: 15px;"><?php echo e($editingConditionId ? 'ویرایش شرط' : 'اضافه کردن شرط جدید'); ?></h5>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($related_tables)): ?>
                        <div style="padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; margin-bottom: 15px;">
                            <i class="fas fa-exclamation-triangle" style="color: #856404;"></i>
                            <strong style="color: #856404;">لطفاً ابتدا در فرم اصلی، جداول مرتبط را انتخاب کنید.</strong>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <form wire:submit.prevent="saveCondition">
                        <!-- نوع فیلد -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                نوع فیلد <span style="color: red;">*</span>
                            </label>
                            <select wire:model.live="condition_field_type" class="form-control" style="width: 100%;" <?php echo e(empty($related_tables) ? 'disabled' : ''); ?>>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $related_tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tableKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($availableTables[$tableKey])): ?>
                                        <option value="<?php echo e($tableKey); ?>"><?php echo e($availableTables[$tableKey]); ?></option>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['condition_field_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- نام فیلد -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                فیلد <span style="color: red;">*</span>
                            </label>
                            <select wire:model.live="condition_field_name" class="form-control" style="width: 100%;" <?php echo e(empty($related_tables) || empty($condition_field_type) ? 'disabled' : ''); ?>>
                                <option value="">انتخاب کنید...</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($condition_field_type) && isset($this->filteredFields[$condition_field_type]) && is_array($this->filteredFields[$condition_field_type])): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->filteredFields[$condition_field_type]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $fieldLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($fieldKey); ?>"><?php echo e($fieldLabel); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <option value="" disabled>ابتدا نوع فیلد را انتخاب کنید</option>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['condition_field_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- عملگر -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                عملگر <span style="color: red;">*</span>
                            </label>
                            <select wire:model="condition_operator" class="form-control" style="width: 100%;">
                                <option value="=">مساوی با (=)</option>
                                <option value=">">بزرگتر از (>)</option>
                                <option value="<">کوچکتر از (<)</option>
                                <option value=">=">بزرگتر یا مساوی (>=)</option>
                                <option value="<=">کوچکتر یا مساوی (<=)</option>
                                <option value="!=">مخالف (!=)</option>
                                <option value="contains">شامل (contains)</option>
                                <option value="not_contains">شامل نباشد (not contains)</option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['condition_operator'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- مقدار -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                مقدار <span style="color: red;">*</span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_data_type): ?>
                                    <span class="badge bg-info" style="margin-right: 5px;">نوع: <?php echo e($condition_data_type === 'string' ? 'رشته' : ($condition_data_type === 'number' ? 'عدد' : ($condition_data_type === 'date' ? 'تاریخ' : 'بولین'))); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_data_type === 'date'): ?>
                                <input
                                    type="number"
                                    wire:model="condition_value"
                                    class="form-control"
                                    placeholder="تعداد روز..."
                                    min="0"
                                    style="width: 100%;"
                                >
                            <?php elseif($condition_data_type === 'number'): ?>
                                <input
                                    type="number"
                                    wire:model="condition_value"
                                    class="form-control"
                                    placeholder="عدد را وارد کنید..."
                                    step="any"
                                    style="width: 100%;"
                                >
                            <?php elseif($condition_data_type === 'boolean'): ?>
                                <select wire:model="condition_value" class="form-control" style="width: 100%;">
                                    <option value="">انتخاب کنید...</option>
                                    <option value="1">بله (true)</option>
                                    <option value="0">خیر (false)</option>
                                </select>
                            <?php else: ?>
                                <input
                                    type="text"
                                    wire:model="condition_value"
                                    class="form-control"
                                    placeholder="مقدار را وارد کنید..."
                                    style="width: 100%;"
                                >
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['condition_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($condition_data_type === 'date'): ?>
                                <small class="text-muted">تعداد روز را وارد کنید (مثلاً: 2 = 2 روز)</small>
                            <?php elseif($condition_data_type === 'number'): ?>
                                <small class="text-muted">عدد را وارد کنید</small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- عملگر منطقی -->
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                عملگر منطقی (برای ترکیب با شرط قبلی) <span style="color: red;">*</span>
                            </label>
                            <select wire:model="condition_logical_operator" class="form-control" style="width: 100%;">
                                <option value="AND">AND (و)</option>
                                <option value="OR">OR (یا)</option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['condition_logical_operator'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">برای اولین شرط این فیلد نادیده گرفته می‌شود</small>
                        </div>

                        <!-- ترتیب -->
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                ترتیب <span style="color: red;">*</span>
                            </label>
                            <input
                                type="number"
                                wire:model="condition_order"
                                class="form-control"
                                min="0"
                                style="width: 100%;"
                            >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['condition_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px; margin-top: 5px; display: block;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">ترتیب اجرای شرط (0 برای اولین شرط)</small>
                        </div>

                        <!-- دکمه‌ها -->
                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button" wire:click="closeConditionModal" class="btn" style="background: #6c757d; color: white;">
                                <i class="fas fa-times"></i> لغو
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ذخیره شرط
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\sms\auto.blade.php ENDPATH**/ ?>