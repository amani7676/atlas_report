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
                        <th>کد الگو</th>
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
                        <th>وضعیت</th>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variable->pattern_code): ?>
                                    <code style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 3px; font-size: 12px; border: 1px solid #bbdefb;">
                                        <?php echo e($variable->pattern_code); ?>

                                    </code>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 900px; max-height: 95vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><?php echo e($isEditing ? 'ویرایش متغیر' : 'ایجاد متغیر جدید'); ?></h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="<?php echo e($isEditing ? 'updateVariable' : 'createVariable'); ?>">
                    
                    <!-- 1. لیست فیلدهای جداول -->
                    <div class="form-group">
                        <label class="form-label">لیست فیلدهای جداول</label>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($availableTableFields)): ?>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px; max-height: 300px; overflow-y: auto;">
                                <strong style="font-size: 13px; display: block; margin-bottom: 15px;">فیلدهای موجود در جداول ثبت شده:</strong>
                                <?php
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
                                    <div style="margin-bottom: 20px; padding: 15px; background: white; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <h6 style="margin-bottom: 10px; color: #495057; font-size: 14px;">
                                            <i class="fas fa-table"></i> <?php echo e($tableDisplayName); ?>

                                            <small style="color: #6c757d; margin-right: 10px;">(<?php echo e($fields[0]['table_name'] ?? ''); ?>)</small>
                                        </h6>
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <button 
                                                    type="button"
                                                    wire:click="selectTableField('<?php echo e($field['name']); ?>')"
                                                    class="btn" 
                                                    style="background: <?php echo e($selectedTableField === $field['name'] ? '#28a745' : '#4361ee'); ?>; color: white; padding: 5px 10px; font-size: 12px; border-radius: 4px;"
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
                            wire:model="pattern_code" 
                            class="form-control" 
                            placeholder="کد الگو (اگر الگو انتخاب شود، به صورت خودکار تنظیم می‌شود)"
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            این فیلد اختیاری است. فیلدهای اصلی را در بخش تخصیص متغیرها به الگوها انتخاب کنید.
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pattern_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- 2. عنوان متغیر -->
                    <div class="form-group">
                        <label class="form-label">عنوان متغیر <span style="color: red;">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            class="form-control" 
                            placeholder="مثال: نام کامل کاربر (اگر الگو انتخاب شود، نام الگو به صورت خودکار وارد می‌شود)"
                            required
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedPatterns)): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedPatterns) === 1): ?>
                                    عنوان با نام الگوی انتخاب شده یکی است. می‌توانید آن را تغییر دهید.
                                <?php else: ?>
                                    چند الگو انتخاب شده است، لطفاً عنوان مناسب وارد کنید.
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                عنوان را وارد کنید یا یک الگو انتخاب کنید تا عنوان به صورت خودکار تنظیم شود.
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- 3. لیست کشویی الگوهای پیامی -->
                    <div class="form-group">
                        <label class="form-label">الگوهای پیامی <span style="color: red;">*</span></label>
                        <select wire:model.live="selectedPatterns" class="form-control" multiple size="4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availablePatterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>" <?php echo e(in_array($id, $selectedPatterns) ? 'selected' : ''); ?>>
                                    <?php echo e($title); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            انتخاب حداقل یک الگو الزامی است. می‌توانید چند الگو را با نگه داشتن Ctrl انتخاب کنید.
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedPatterns'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- 4. نمایش متن الگو و 5. کدهای متغیر و 6. تخصیص فیلدها -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedPatterns)): ?>
                        <div class="form-group">
                            <label class="form-label">تخصیص متغیرها به الگوها</label>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedPatterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patternId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $pattern = \App\Models\Pattern::find($patternId);
                                        if(!$pattern) continue;
                                    ?>
                                    <div style="margin-bottom: 20px; padding: 15px; background: white; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <h6 style="margin-bottom: 10px; color: #495057;">
                                            <i class="fas fa-file-alt"></i> <?php echo e($pattern->title); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->pattern_code): ?> <small>(<?php echo e($pattern->pattern_code); ?>)</small> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </h6>
                                        
                                        <!-- متن الگو -->
                                        <div style="margin-bottom: 15px; padding: 10px; background: #e9ecef; border-radius: 4px; font-family: monospace; font-size: 13px;">
                                            <?php echo e($patternTexts[$patternId] ?? ''); ?>

                                        </div>
                                        
                                        <!-- کدهای متغیر و تخصیص فیلدها -->
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($patternVariables[$patternId]) && !empty($patternVariables[$patternId])): ?>
                                            <div style="margin-bottom: 10px;">
                                                <strong style="font-size: 12px; color: #666;">کدهای متغیرهای موجود:</strong>
                                                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patternVariables[$patternId]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variableCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span style="background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">
                                                            <?php echo e($variableCode); ?>

                                                        </span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patternVariables[$patternId]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variableCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div style="margin-bottom: 10px;">
                                                    <label style="font-size: 12px; font-weight: bold; color: #495057;">
                                                        <?php echo e($variableCode); ?> → فیلد مربوطه:
                                                    </label>
                                                    <select 
                                                        wire:model="variableAssignments.<?php echo e($patternId); ?>.<?php echo e($variableCode); ?>" 
                                                        class="form-control form-control-sm"
                                                        style="font-size: 12px;"
                                                    >
                                                        <option value="">انتخاب فیلد...</option>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableTableFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($field['name']); ?>">
                                                                <?php echo e($field['label']); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </select>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['variableAssignments.' . $patternId . '.' . $variableCode];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                                        <span style="color: red; font-size: 11px;"><?php echo e($message); ?></span> 
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <p style="color: #6c757d; font-size: 12px; font-style: italic;">
                                                این الگو هیچ کد متغیری ({0}, {1}, ...) ندارد.
                                            </p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['variableAssignments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- فیلدهای دیگر -->
                    <div class="form-group">
                        <label class="form-label">نوع متغیر <span style="color: red;">*</span></label>
                        <select wire:model.live="variable_type" class="form-control" required>
                            <option value="user">کاربر</option>
                            <option value="report">گزارش</option>
                            <option value="general">عمومی</option>
                        </select>
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
                        <label class="form-label">نام جدول (اختیاری)</label>
                        <input 
                            type="text" 
                            wire:model="table_name" 
                            class="form-control" 
                            placeholder="مثال: residents, reports"
                        >
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
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                wire:model="is_active" 
                                class="form-check-input" 
                                id="is_active"
                            >
                            <label class="form-check-label" for="is_active">
                                فعال
                            </label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo e($isEditing ? 'به‌روزرسانی' : 'ایجاد'); ?>

                        </button>
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<script>
function confirmDeleteVariable(id, title) {
    if (confirm('آیا از حذف متغیر "' + title + '" اطمینان دارید؟')) {
        window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('deleteVariable', id);
    }
}
</script>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\variables\index.blade.php ENDPATH**/ ?>