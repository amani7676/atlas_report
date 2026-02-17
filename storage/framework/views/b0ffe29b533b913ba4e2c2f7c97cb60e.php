<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>مدیریت الگوهای پیام</h2>
            <div style="display: flex; gap: 10px;">
                <button wire:click="viewRawApiResponse" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-eye"></i>
                    مشاهده پاسخ API
                </button>
                <button 
                    type="button"
                    wire:click="syncFromApi" 
                    class="btn" 
                    style="background: #17a2b8; color: white;" 
                    wire:loading.attr="disabled"
                    wire:target="syncFromApi"
                >
                    <i class="fas fa-sync" wire:loading.class="fa-spin" wire:target="syncFromApi"></i>
                    <span wire:loading.remove wire:target="syncFromApi">همگام‌سازی الگوها</span>
                    <span wire:loading wire:target="syncFromApi">در حال همگام‌سازی...</span>
                </button>
                <button wire:click="openCreateModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    ایجاد الگوی جدید
                </button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی الگو (عنوان، متن، کد)..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="margin: 0;">فیلتر وضعیت:</label>
                <select wire:model.live="statusFilter" class="form-control" style="width: 150px;">
                    <option value="">همه</option>
                    <option value="pending">در انتظار</option>
                    <option value="approved">تایید شده</option>
                    <option value="rejected">رد شده</option>
                </select>
            </div>
        </div>

        <!-- Patterns Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'title'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th>متن الگو</th>
                        <th wire:click="sortBy('pattern_code')" style="cursor: pointer;">
                            کد الگو
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'pattern_code'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th>لیست سیاه</th>
                        <th>تعداد گزارش‌ها</th>
                        <th wire:click="sortBy('status')" style="cursor: pointer;">
                            وضعیت
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'status'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('is_active')" style="cursor: pointer;">
                            فعال
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'is_active'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            تاریخ ایجاد
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'created_at'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $patterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($pattern->title); ?></strong>
                            </td>
                            <td>
                                <p style="color: #666; font-size: 14px; margin: 0; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo e(Str::limit($pattern->text, 50)); ?>

                                </p>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->pattern_code): ?>
                                    <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                        <?php echo e($pattern->pattern_code); ?>

                                    </span>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->blacklist_id): ?>
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        <?php echo e($pattern->blacklist_id); ?>

                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <span style="background: <?php echo e($pattern->reports_count > 0 ? '#17a2b8' : '#6c757d'); ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                    <?php echo e($pattern->reports_count ?? 0); ?>

                                </span>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->status === 'approved'): ?>
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        تایید شده
                                    </span>
                                <?php elseif($pattern->status === 'rejected'): ?>
                                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        رد شده
                                    </span>
                                <?php else: ?>
                                    <span style="background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        در انتظار
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->is_active): ?>
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
                                <?php echo e(jalaliDate($pattern->created_at, 'Y/m/d H:i')); ?>

                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pattern->api_response): ?>
                                        <button 
                                            wire:click="showApiResponse(<?php echo e($pattern->id); ?>)" 
                                            class="btn" 
                                            style="background: #17a2b8; color: white; padding: 5px 10px; font-size: 12px;"
                                            title="مشاهده پاسخ API"
                                        >
                                            <i class="fas fa-code"></i>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button 
                                        wire:click="toggleActive(<?php echo e($pattern->id); ?>)" 
                                        class="btn" 
                                        style="background: <?php echo e($pattern->is_active ? '#ffc107' : '#28a745'); ?>; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="<?php echo e($pattern->is_active ? 'غیرفعال کردن' : 'فعال کردن'); ?>"
                                    >
                                        <i class="fas fa-<?php echo e($pattern->is_active ? 'pause' : 'play'); ?>"></i>
                                    </button>
                                    <button 
                                        wire:click="openEditModal(<?php echo e($pattern->id); ?>)" 
                                        class="btn" 
                                        style="background: #4361ee; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        onclick="confirmDeletePattern(<?php echo e($pattern->id); ?>, '<?php echo e($pattern->title); ?>')" 
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
                                الگویی یافت نشد
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination (مشابه ارسال گروهی) -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($patterns->hasPages()): ?>
            <div class="card mt-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted" style="font-size: 14px;">
                        نمایش
                        <?php echo e($patterns->firstItem() ?? 0); ?>

                        تا
                        <?php echo e($patterns->lastItem() ?? 0); ?>

                        از
                        <?php echo e($patterns->total()); ?>

                        نتیجه
                    </div>
                    
                    <nav aria-label="Page navigation">
                        <ul class="pagination custom-pagination mb-0">
                            
                            <li class="page-item <?php echo e($patterns->onFirstPage() ? 'disabled' : ''); ?>">
                                <a class="page-link" href="#" wire:click="previousPage()" tabindex="-1"
                                    aria-disabled="<?php echo e($patterns->onFirstPage() ? 'true' : 'false'); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $patterns->getUrlRange(1, $patterns->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page == $patterns->currentPage()): ?>
                                    <li class="page-item active">
                                        <span class="page-link"><?php echo e($page); ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#"
                                            wire:click="gotoPage(<?php echo e($page); ?>)"><?php echo e($page); ?></a>
                                    </li>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <li class="page-item <?php echo e(!$patterns->hasMorePages() ? 'disabled' : ''); ?>">
                                <a class="page-link" href="#" wire:click="nextPage()"
                                    aria-disabled="<?php echo e(!$patterns->hasMorePages() ? 'true' : 'false'); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Create/Edit Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><?php echo e($isEditing ? 'ویرایش الگو' : 'ایجاد الگوی جدید'); ?></h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="<?php echo e($isEditing ? 'updatePattern' : 'createPattern'); ?>">
                    <div class="form-group">
                        <label class="form-label">عنوان الگو <span style="color: red;">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            class="form-control" 
                            placeholder="مثال: الگوی خوش‌آمدگویی"
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
                        <label class="form-label">متن الگو <span style="color: red;">*</span></label>
                        
                        <!-- انتخاب متغیرها -->
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <strong style="font-size: 14px;">متغیرها:</strong>
                                <a href="/variables" target="_blank" class="btn" style="background: #6c757d; color: white; padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-cog"></i>
                                    مدیریت متغیرها
                                </a>
                            </div>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($availableVariables['user'])): ?>
                                <div style="margin-bottom: 10px;">
                                    <strong style="font-size: 14px;">متغیرهای کاربر:</strong>
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableVariables['user'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button 
                                                type="button"
                                                wire:click="insertVariable('<?php echo e($var['key']); ?>', 'user')"
                                                class="btn" 
                                                style="background: #4361ee; color: white; padding: 5px 10px; font-size: 12px;"
                                                title="<?php echo e($var['label']); ?> (<?php echo e($var['code'] ?? ''); ?>) - فیلد: <?php echo e($var['key']); ?>"
                                            >
                                                <?php echo e($var['label']); ?> <small>(<?php echo e($var['code'] ?? ''); ?>)</small>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($availableVariables['report'])): ?>
                                <div style="margin-bottom: 10px;">
                                    <strong style="font-size: 14px;">متغیرهای گزارش:</strong>
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableVariables['report'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button 
                                                type="button"
                                                wire:click="insertVariable('<?php echo e($var['key']); ?>', 'report')"
                                                class="btn" 
                                                style="background: #28a745; color: white; padding: 5px 10px; font-size: 12px;"
                                                title="<?php echo e($var['label']); ?> (<?php echo e($var['code'] ?? ''); ?>) - فیلد: <?php echo e($var['key']); ?>"
                                            >
                                                <?php echo e($var['label']); ?> <small>(<?php echo e($var['code'] ?? ''); ?>)</small>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($availableVariables['general'])): ?>
                                <div>
                                    <strong style="font-size: 14px;">متغیرهای عمومی:</strong>
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableVariables['general'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button 
                                                type="button"
                                                wire:click="insertVariable('<?php echo e($var['key']); ?>', 'general')"
                                                class="btn" 
                                                style="background: #17a2b8; color: white; padding: 5px 10px; font-size: 12px;"
                                                title="<?php echo e($var['label']); ?> (<?php echo e($var['code'] ?? ''); ?>) - فیلد: <?php echo e($var['key']); ?>"
                                            >
                                                <?php echo e($var['label']); ?> <small>(<?php echo e($var['code'] ?? ''); ?>)</small>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($availableVariables['user']) && empty($availableVariables['report']) && empty($availableVariables['general'])): ?>
                                <div style="text-align: center; padding: 20px; color: #999;">
                                    <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                    <p>هیچ متغیر فعالی یافت نشد.</p>
                                    <a href="/variables/create" target="_blank" class="btn btn-primary" style="margin-top: 10px;">
                                        <i class="fas fa-plus"></i>
                                        ایجاد متغیر جدید
                                    </a>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <!-- نمایش متغیرهای انتخاب شده -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedVariables)): ?>
                            <div style="background: #e8f4fd; padding: 10px; border-radius: 6px; margin-bottom: 10px;">
                                <strong style="font-size: 13px;">متغیرهای استفاده شده:</strong>
                                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedVariables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span style="background: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                                            <span style="background: #4361ee; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-family: monospace;">
                                                <?php echo e($var['code'] ?? '{' . $var['index'] . '}'); ?>

                                            </span>
                                            <span><?php echo e($var['label']); ?></span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($var['code'])): ?>
                                                <button 
                                                    type="button"
                                                    wire:click="removeVariable(<?php echo e($var['index']); ?>)"
                                                    style="background: #dc3545; color: white; border: none; border-radius: 3px; padding: 2px 6px; cursor: pointer; font-size: 10px;"
                                                    title="حذف"
                                                >
                                                    ×
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <textarea 
                            wire:model="text" 
                            class="form-control" 
                            rows="5"
                            placeholder="متن الگوی پیامک... (از دکمه‌های بالا برای اضافه کردن متغیرها استفاده کنید)"
                            required
                        ></textarea>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            متغیرها به صورت {0}, {1}, {2} و ... در متن قرار می‌گیرند
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">کد الگو (از ملی پیامک)</label>
                        <input 
                            type="text" 
                            wire:model="pattern_code" 
                            class="form-control" 
                            placeholder="کد الگو (اختیاری)"
                        >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pattern_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">کد لیست سیاه (5 رقمی) <span style="color: red;">*</span></label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input 
                                type="text" 
                                wire:model="blacklist_id" 
                                class="form-control" 
                                placeholder="مثال: 12345"
                                required
                                style="flex: 1;"
                                pattern="[0-9]*"
                                inputmode="numeric"
                            >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($activeBlacklists) > 0): ?>
                                <select 
                                    wire:change="$set('blacklist_id', $event.target.value)" 
                                    class="form-control" 
                                    style="width: 200px;"
                                    title="انتخاب از لیست موجود"
                                >
                                    <option value="">یا از لیست انتخاب کنید</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeBlacklists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blacklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($blacklist->blacklist_id); ?>"><?php echo e($blacklist->title); ?> (<?php echo e($blacklist->blacklist_id); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            می‌توانید عدد را مستقیماً وارد کنید یا از لیست انتخاب کنید
                        </small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['blacklist_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">وضعیت</label>
                        <select wire:model="status" class="form-control">
                            <option value="pending">در انتظار</option>
                            <option value="approved">تایید شده</option>
                            <option value="rejected">رد شده</option>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: red; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'rejected'): ?>
                        <div class="form-group">
                            <label class="form-label">دلیل رد</label>
                            <textarea 
                                wire:model="rejection_reason" 
                                class="form-control" 
                                rows="3"
                                placeholder="دلیل رد الگو..."
                            ></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rejection_reason'];
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
                            <?php echo e($isEditing ? 'ذخیره تغییرات' : 'ایجاد الگو'); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- API Response Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showApiResponseModal && $apiResponseData): ?>
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>پاسخ API - <?php echo e($apiResponseData['title'] ?? 'ویرایش الگو'); ?></h3>
                    <button wire:click="closeApiResponseModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="background: <?php echo e(isset($apiResponseData['success']) && $apiResponseData['success'] ? '#d4edda' : '#f8d7da'); ?>; padding: 20px; border-radius: 6px; margin-bottom: 15px; border: 1px solid <?php echo e(isset($apiResponseData['success']) && $apiResponseData['success'] ? '#c3e6cb' : '#f5c6cb'); ?>;">
                    <div style="margin-bottom: 15px;">
                        <strong style="display: block; margin-bottom: 8px; color: <?php echo e(isset($apiResponseData['success']) && $apiResponseData['success'] ? '#155724' : '#721c24'); ?>;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($apiResponseData['success']) && $apiResponseData['success']): ?>
                                <i class="fas fa-check-circle"></i> موفق
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i> ناموفق
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </strong>
                        <p style="margin: 0; color: <?php echo e(isset($apiResponseData['success']) && $apiResponseData['success'] ? '#155724' : '#721c24'); ?>;">
                            <?php echo e($apiResponseData['message'] ?? 'پاسخ دریافت شد'); ?>

                        </p>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($apiResponseData['status'])): ?>
                        <div style="margin-bottom: 10px;">
                            <strong>وضعیت الگو:</strong> 
                            <span style="background: <?php echo e($apiResponseData['status'] === 'pending' ? '#ffc107' : ($apiResponseData['status'] === 'approved' ? '#28a745' : '#dc3545')); ?>; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($apiResponseData['status'] === 'pending'): ?>
                                    در انتظار تأیید
                                <?php elseif($apiResponseData['status'] === 'approved'): ?>
                                    تأیید شده
                                <?php else: ?>
                                    رد شده
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($apiResponseData['status_message'])): ?>
                            <div style="margin-bottom: 10px; color: #666; font-size: 13px;">
                                <i class="fas fa-info-circle"></i> <?php echo e($apiResponseData['status_message']); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($apiResponseData['pattern_code'])): ?>
                        <div style="margin-bottom: 10px;">
                            <strong>کد الگو:</strong> 
                            <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                                <?php echo e($apiResponseData['pattern_code']); ?>

                            </span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div style="margin-bottom: 10px;">
                        <strong>کد وضعیت HTTP:</strong> <?php echo e($apiResponseData['http_status_code'] ?? '-'); ?>

                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($apiResponseData['created_at'])): ?>
                        <div style="margin-bottom: 10px;">
                            <strong>تاریخ ایجاد:</strong> <?php echo e($apiResponseData['created_at']); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($apiResponseData['parsed_response']) && $apiResponseData['parsed_response']): ?>
                    <div style="margin-bottom: 15px;">
                        <strong>اطلاعات الگو از API:</strong>
                        <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px;">
                            <div style="margin-bottom: 8px;"><strong>BodyID:</strong> <?php echo e($apiResponseData['parsed_response']['BodyID'] ?? '-'); ?></div>
                            <div style="margin-bottom: 8px;"><strong>Title:</strong> <?php echo e($apiResponseData['parsed_response']['Title'] ?? '-'); ?></div>
                            <div style="margin-bottom: 8px;"><strong>Body:</strong> <?php echo e($apiResponseData['parsed_response']['Body'] ?? '-'); ?></div>
                            <div style="margin-bottom: 8px;"><strong>BodyStatus:</strong> <?php echo e($apiResponseData['parsed_response']['BodyStatus'] ?? '-'); ?></div>
                            <div style="margin-bottom: 8px;"><strong>InsertDate:</strong> <?php echo e($apiResponseData['parsed_response']['InsertDate'] ?? '-'); ?></div>
                            <div><strong>Description:</strong> <?php echo e($apiResponseData['parsed_response']['Description'] ?? '-'); ?></div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($apiResponseData['api_response']) && $apiResponseData['api_response']): ?>
                    <div style="margin-bottom: 15px;">
                        <strong>پاسخ خام API:</strong>
                        <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 400px; overflow-y: auto;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px; direction: ltr; text-align: left;"><?php echo e($apiResponseData['api_response']); ?></pre>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button wire:click="closeApiResponseModal" class="btn btn-primary">
                        بستن
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Raw API Response Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showRawApiResponseModal && $rawApiResponseData): ?>
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 900px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>پاسخ خام API - GetSharedServiceBody</h3>
                    <button wire:click="closeRawApiResponseModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px;">
                        <strong>وضعیت:</strong> 
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rawApiResponseData['success']): ?>
                            <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                ✅ موفق
                            </span>
                        <?php else: ?>
                            <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                ❌ خطا
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>پیام:</strong> <?php echo e($rawApiResponseData['message'] ?? '-'); ?>

                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>تعداد الگوها:</strong> <?php echo e($rawApiResponseData['patterns_count'] ?? 0); ?>

                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>کد وضعیت HTTP:</strong> <?php echo e($rawApiResponseData['http_status_code'] ?? '-'); ?>

                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($rawApiResponseData['patterns'])): ?>
                    <div style="margin-bottom: 15px;">
                        <strong>الگوهای دریافت شده:</strong>
                        <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 300px; overflow-y: auto;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px; direction: ltr; text-align: left;"><?php echo e(json_encode($rawApiResponseData['patterns'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div style="margin-bottom: 15px;">
                    <strong>پاسخ خام کامل API:</strong>
                    <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 400px; overflow-y: auto;">
                        <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 11px; direction: ltr; text-align: left;"><?php echo e($rawApiResponseData['raw_response']); ?></pre>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button wire:click="closeRawApiResponseModal" class="btn" style="background: #6c757d; color: white;">
                        بستن
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rawApiResponseData['success'] && !empty($rawApiResponseData['patterns'])): ?>
                        <button 
                            type="button"
                            wire:click="syncFromApi" 
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="syncFromApi"
                        >
                            <i class="fas fa-sync" wire:loading.class="fa-spin" wire:target="syncFromApi"></i>
                            <span wire:loading.remove wire:target="syncFromApi">همگام‌سازی الگوها</span>
                            <span wire:loading wire:target="syncFromApi">در حال همگام‌سازی...</span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Sync Response Modal - نمایش پاسخ API بعد از همگام‌سازی -->
    <!-- Debug: نمایش وضعیت مدال -->
    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px;">
        <small>Debug Sync Modal: showSyncResponseModal = <?php echo e($showSyncResponseModal ? 'TRUE' : 'FALSE'); ?>, syncResponseData = <?php echo e($syncResponseData ? 'SET' : 'NULL'); ?></small>
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSyncResponseModal && $syncResponseData): ?>
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 1000px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>پاسخ API ملی پیامک - همگام‌سازی الگوها</h3>
                    <button wire:click="closeSyncResponseModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px;">
                        <strong>وضعیت:</strong> 
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($syncResponseData['success']): ?>
                            <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                ✅ موفق
                            </span>
                        <?php else: ?>
                            <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                ❌ خطا
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Username:</strong> 
                        <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-family: monospace;"><?php echo e($syncResponseData['username'] ?? '-'); ?></code>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Password (API Key):</strong> 
                        <code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-family: monospace;"><?php echo e($syncResponseData['password'] ?? '-'); ?></code>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>پیام:</strong> <?php echo e($syncResponseData['message'] ?? '-'); ?>

                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>تعداد الگوهای دریافت شده:</strong> <?php echo e($syncResponseData['patterns_count'] ?? 0); ?>

                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>کد وضعیت HTTP:</strong> <?php echo e($syncResponseData['http_status_code'] ?? '-'); ?>

                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($syncResponseData['patterns'])): ?>
                    <div style="margin-bottom: 15px;">
                        <strong>الگوهای دریافت شده از API:</strong>
                        <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 400px; overflow-y: auto;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px; direction: ltr; text-align: left;"><?php echo e(json_encode($syncResponseData['patterns'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($syncResponseData['raw_response'])): ?>
                <div style="margin-bottom: 15px;">
                    <strong>پاسخ خام کامل API:</strong>
                    <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 500px; overflow-y: auto;">
                        <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 11px; direction: ltr; text-align: left;"><?php echo e($syncResponseData['raw_response']); ?></pre>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button wire:click="closeSyncResponseModal" class="btn btn-primary">
                        بستن
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script>
        function confirmDeletePattern(id, title) {
            Swal.fire({
                title: 'حذف الگو',
                html: `آیا مطمئن هستید که می‌خواهید الگو <strong>"${title}"</strong> را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deletePattern(id);
                }
            });
        }
    </script>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\patterns\index.blade.php ENDPATH**/ ?>