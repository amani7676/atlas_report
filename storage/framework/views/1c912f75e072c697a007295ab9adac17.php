<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>مدیریت متغیرهای الگو</h2>
            <button wire:click="openCreateModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد متغیر جدید
            </button>
        </div>

        <!-- Search and Filters -->
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی متغیر (عنوان، کد، فیلد)..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="margin: 0;">فیلتر نوع:</label>
                <select wire:model.live="typeFilter" class="form-control" style="width: 150px;">
                    <option value="">همه</option>
                    <option value="user">کاربر</option>
                    <option value="report">گزارش</option>
                    <option value="general">عمومی</option>
                </select>
            </div>
        </div>

        <!-- Variables Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th wire:click="sortBy('code')" style="cursor: pointer;">
                            کد
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'code'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'title'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th>فیلد جدول</th>
                        <th>نام جدول</th>
                        <th>الگوی مرتبط</th>
                        <th wire:click="sortBy('variable_type')" style="cursor: pointer;">
                            نوع
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'variable_type'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('sort_order')" style="cursor: pointer;">
                            ترتیب
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'sort_order'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('is_active')" style="cursor: pointer;">
                            وضعیت
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'is_active'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-family: monospace;">
                                    <?php echo e($variable->code); ?>

                                </span>
                            </td>
                            <td>
                                <strong><?php echo e($variable->title); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variable->description): ?>
                                    <br><small style="color: #666; font-size: 12px;"><?php echo e(Str::limit($variable->description, 50)); ?></small>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                    <?php echo e($variable->table_field); ?>

                                </code>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variable->table_name): ?>
                                    <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                        <?php echo e($variable->table_name); ?>

                                    </code>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $patternConnections = DB::table('pattern_pattern_variables')
                                        ->where('pattern_variable_id', $variable->id)
                                        ->get();
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patternConnections->isNotEmpty()): ?>
                                    <div style="max-height: 80px; overflow-y: auto;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patternConnections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $connection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $pattern = \App\Models\Pattern::find($connection->pattern_id);
                                            ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern): ?>
                                                <div style="margin-bottom: 3px;">
                                                    <span style="background: #6f42c1; color: white; padding: 1px 4px; border-radius: 2px; font-size: 10px;">
                                                        <?php echo e($pattern->title); ?>

                                                    </span>
                                                    <small style="color: #666; font-size: 9px;">
                                                        (<?php echo e($connection->variable_code); ?>)
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <div style="color: #dc3545; font-size: 10px;">الگو حذف شده</div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variable->variable_type === 'user'): ?>
                                    <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        کاربر
                                    </span>
                                <?php elseif($variable->variable_type === 'report'): ?>
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        گزارش
                                    </span>
                                <?php else: ?>
                                    <span style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        عمومی
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <?php echo e($variable->sort_order); ?>

                                </span>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variable->is_active): ?>
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        فعال
                                    </span>
                                <?php else: ?>
                                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        غیرفعال
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <button 
                                        wire:click="toggleActive(<?php echo e($variable->id); ?>)" 
                                        class="btn" 
                                        style="background: <?php echo e($variable->is_active ? '#ffc107' : '#28a745'); ?>; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="<?php echo e($variable->is_active ? 'غیرفعال کردن' : 'فعال کردن'); ?>"
                                    >
                                        <i class="fas fa-<?php echo e($variable->is_active ? 'pause' : 'play'); ?>"></i>
                                    </button>
                                    <button 
                                        wire:click="openEditModal(<?php echo e($variable->id); ?>)" 
                                        class="btn" 
                                        style="background: #4361ee; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        onclick="confirmDeleteVariable(<?php echo e($variable->id); ?>, '<?php echo e($variable->title); ?>')" 
                                        class="btn btn-danger" 
                                        style="padding: 5px 10px; font-size: 12px;"
                                        title="حذف"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                متغیری یافت نشد
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variables->hasPages()): ?>
            <div style="margin-top: 20px; display: flex; justify-content: center;">
                <?php echo e($variables->links('pagination::bootstrap-4')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Create/Edit Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><?php echo e($isEditing ? 'ویرایش متغیر' : 'ایجاد متغیر جدید'); ?></h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="<?php echo e($isEditing ? 'updateVariable' : 'createVariable'); ?>">
                    <div class="form-group">
                        <label class="form-label">کد متغیر <span style="color: red;">*</span></label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input 
                                type="text" 
                                wire:model="code" 
                                class="form-control" 
                                placeholder="مثال: {0}, {1}, {2}"
                                required
                                style="flex: 1;"
                                pattern="\{[0-9]+\}"
                            >
                            <button 
                                type="button"
                                wire:click="generateNextCode"
                                class="btn" 
                                style="background: #17a2b8; color: white;"
                                title="تولید کد بعدی"
                            >
                                <i class="fas fa-magic"></i>
                                تولید کد
                            </button>
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            فرمت: {0}, {1}, {2} و ...
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">عنوان <span style="color: red;">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            class="form-control" 
                            placeholder="مثال: نام کاربر"
                            required
                        >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">فیلد جدول <span style="color: red;">*</span></label>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($availableTableFields)): ?>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px; max-height: 300px; overflow-y: auto;">
                                <strong style="font-size: 13px; display: block; margin-bottom: 10px;">فیلدهای موجود در جداول ثبت شده:</strong>
                                <?php
                                    // گروه‌بندی فیلدها بر اساس جدول
                                    $groupedFields = [];
                                    foreach ($availableTableFields as $field) {
                                        $tableKey = $field['table_display_name'] ?? ($field['table_name'] ?? 'سایر');
                                        if (!isset($groupedFields[$tableKey])) {
                                            $groupedFields[$tableKey] = [];
                                        }
                                        $groupedFields[$tableKey][] = $field;
                                    }
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groupedFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tableDisplayName => $fields): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #dee2e6;">
                                        <strong style="font-size: 12px; color: #666; display: block; margin-bottom: 8px;">
                                            <i class="fas fa-table"></i> <?php echo e($tableDisplayName); ?>

                                        </strong>
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <button 
                                                    type="button"
                                                    wire:click="selectTableField('<?php echo e($field['name']); ?>')"
                                                    class="btn" 
                                                    style="background: <?php echo e($selectedTableField === $field['name'] ? '#28a745' : '#4361ee'); ?>; color: white; padding: 5px 10px; font-size: 12px;"
                                                    title="<?php echo e($field['name']); ?> (<?php echo e($field['table_name'] ?? ''); ?>)"
                                                >
                                                    <?php echo e($field['label']); ?>

                                                </button>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <input 
                            type="text" 
                            wire:model="table_field" 
                            class="form-control" 
                            placeholder="مثال: fullname, phone, name یا از لیست بالا انتخاب کنید"
                            required
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            نام فیلد در جدول دیتابیس (می‌توانید از لیست بالا انتخاب کنید یا مستقیماً وارد کنید)
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['table_field'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">نام جدول (اختیاری)</label>
                        <input 
                            type="text" 
                            wire:model="table_name" 
                            class="form-control" 
                            placeholder="مثال: residents, reports"
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            نام جدول دیتابیس (در صورت نیاز)
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['table_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">الگوهای مرتبط <span style="color: red;">*</span></label>
                        <select wire:model.live="pattern_ids" class="form-control" multiple size="4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availablePatterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>" <?php echo e(in_array($id, $pattern_ids) ? 'selected' : ''); ?>>
                                    <?php echo e($title); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            انتخاب حداقل یک الگو الزامی است. می‌توانید چند الگو را با نگه داشتن Ctrl انتخاب کنید.
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pattern_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($pattern_ids)): ?>
                        <div class="form-group">
                            <label class="form-label">کد متغیر برای الگوها <span style="color: red;">*</span></label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input 
                                    type="text" 
                                    wire:model="variable_code" 
                                    class="form-control" 
                                    placeholder="مثال: {0}, {1}, {2}"
                                    required
                                    style="flex: 1;"
                                    pattern="\{[0-9]+\}"
                                >
                                <button 
                                    type="button"
                                    wire:click="generateNextVariableCode"
                                    class="btn" 
                                    style="background: #17a2b8; color: white;"
                                    title="تولید کد بعدی برای الگوها"
                                >
                                    <i class="fas fa-magic"></i>
                                    تولید کد
                                </button>
                            </div>
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                این کد برای همه الگوهای انتخاب شده اعمال می‌شود
                            </small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['variable_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">نوع متغیر <span style="color: red;">*</span></label>
                        <select wire:model.live="variable_type" class="form-control" required>
                            <option value="user">کاربر</option>
                            <option value="report">گزارش</option>
                            <option value="general">عمومی</option>
                        </select>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            با تغییر نوع، فیلدهای جدول مربوطه نمایش داده می‌شوند
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['variable_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
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
                            placeholder="توضیحات اختیاری..."
                        ></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ترتیب نمایش</label>
                        <input 
                            type="number" 
                            wire:model="sort_order" 
                            class="form-control" 
                            min="0"
                            placeholder="0"
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            عدد کمتر = نمایش بالاتر
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sort_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input 
                                type="checkbox" 
                                wire:model="is_active"
                            >
                            <span>فعال</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">
                            لغو
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <?php echo e($isEditing ? 'ذخیره تغییرات' : 'ایجاد متغیر'); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script>
        function confirmDeleteVariable(id, title) {
            Swal.fire({
                title: 'حذف متغیر',
                html: `آیا مطمئن هستید که می‌خواهید متغیر <strong>"${title}"</strong> را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteVariable(id);
                }
            });
        }
    </script>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\variables\index_old.blade.php ENDPATH**/ ?>