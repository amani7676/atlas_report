<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>پیام‌های ارسال شده</h2>
        </div>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div style="background: linear-gradient(135deg, #4361ee, #3a0ca3); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?php echo e($statusCounts['all']); ?></div>
                <div style="font-size: 14px; opacity: 0.9;">همه پیام‌ها</div>
            </div>
            <div style="background: linear-gradient(135deg, #4cc9f0, #2db8d9); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?php echo e($statusCounts['sent']); ?></div>
                <div style="font-size: 14px; opacity: 0.9;">ارسال شده</div>
            </div>
            <div style="background: linear-gradient(135deg, #f72585, #d1145a); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?php echo e($statusCounts['failed']); ?></div>
                <div style="font-size: 14px; opacity: 0.9;">ناموفق</div>
            </div>
            <div style="background: linear-gradient(135deg, #ff9e00, #ff8500); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?php echo e($statusCounts['pending']); ?></div>
                <div style="font-size: 14px; opacity: 0.9;">در انتظار</div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-search" style="color: #666;"></i>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="جستجو بر اساس نام، شماره تلفن، عنوان..."
                        class="form-control"
                        style="width: 300px;"
                    >
                </div>

                <select wire:model.live="statusFilter" class="form-control" style="width: 150px;">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="sent">ارسال شده</option>
                    <option value="failed">ناموفق</option>
                    <option value="pending">در انتظار</option>
                </select>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $statusFilter): ?>
                    <button wire:click="resetFilters" class="btn" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> پاک کردن فیلترها
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                <button wire:click="resendMultipleSms" class="btn btn-success">
                    <i class="fas fa-redo"></i>
                    ارسال مجدد <?php echo e(count($selectedIds)); ?> پیام ناموفق
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Messages Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                style="cursor: pointer;"
                            >
                        </th>
                        <th wire:click="sortBy('resident_name')" style="cursor: pointer;">
                            نام اقامت‌گر
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'resident_name'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('phone')" style="cursor: pointer;">
                            شماره تلفن
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'phone'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان پیام
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'title'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th>گزارش</th>
                        <th>نوع</th>
                        <th wire:click="sortBy('status')" style="cursor: pointer;">
                            وضعیت
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'status'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('sent_at')" style="cursor: pointer;">
                            تاریخ ارسال
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'sent_at'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            تاریخ ایجاد
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'created_at'): ?>
                                <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sentMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sentMessage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->status === 'failed'): ?>
                                    <input
                                        type="checkbox"
                                        wire:model.live="selectedIds"
                                        value="<?php echo e($sentMessage->id); ?>"
                                        style="cursor: pointer;"
                                    >
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo e($sentMessage->resident_name ?? 'بدون نام'); ?></strong>
                            </td>
                            <td><?php echo e($sentMessage->phone ?? 'بدون شماره'); ?></td>
                            <td>
                                <div>
                                    <strong><?php echo e($sentMessage->title ?? 'بدون عنوان'); ?></strong>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->description): ?>
                                        <br>
                                        <small style="color: #666;"><?php echo e(\Illuminate\Support\Str::limit($sentMessage->description, 50)); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->report): ?>
                                    <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block;">
                                        <i class="fas fa-file-alt"></i> <?php echo e($sentMessage->report->title); ?>

                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->is_pattern): ?>
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block;">
                                        <i class="fas fa-code"></i> الگویی
                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->pattern): ?>
                                        <br><small style="color: #666; margin-top: 3px; display: block;">کد: <?php echo e($sentMessage->pattern->pattern_code); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block;">
                                        <i class="fas fa-envelope"></i> عادی
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->status === 'sent'): ?>
                                    <div>
                                        <span style="background: #4cc9f0; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block;">
                                            <i class="fas fa-check-circle"></i> ارسال شده
                                        </span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->response_code): ?>
                                            <br><small style="color: #666; margin-top: 5px; display: block;">کد: <?php echo e($sentMessage->response_code); ?></small>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php elseif($sentMessage->status === 'failed'): ?>
                                    <div>
                                        <span style="background: #f72585; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block;">
                                            <i class="fas fa-times-circle"></i> ناموفق
                                        </span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->response_code): ?>
                                            <br><small style="color: #666; margin-top: 5px; display: block;">کد: <?php echo e($sentMessage->response_code); ?></small>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="background: #ff9e00; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                        <i class="fas fa-clock"></i> در انتظار
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->sent_at): ?>
                                    <?php echo e(jalaliDate($sentMessage->sent_at, 'Y/m/d H:i')); ?>

                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td><?php echo e(jalaliDate($sentMessage->created_at, 'Y/m/d H:i')); ?></td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->status === 'failed'): ?>
                                        <button
                                            wire:click="resendSms(<?php echo e($sentMessage->id); ?>)"
                                            class="btn btn-success btn-sm"
                                            title="ارسال مجدد"
                                        >
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessage->error_message): ?>
                                        <button
                                            onclick="showError(<?php echo e(json_encode([
                                                'success' => false,
                                                'response_code' => $sentMessage->response_code,
                                                'message' => $sentMessage->error_message,
                                                'raw_response' => $sentMessage->raw_response,
                                                'api_response' => $sentMessage->api_response
                                            ])); ?>)"
                                            class="btn btn-danger btn-sm"
                                            title="مشاهده خطا"
                                        >
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <p>هیچ پیامی یافت نشد</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sentMessages->hasPages()): ?>
            <div style="margin-top: 20px; padding: 15px; background: white; border-top: 1px solid #dee2e6; border-radius: 0 0 10px 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="color: #6c757d; font-size: 14px;">
                    نمایش
                    <strong><?php echo e($sentMessages->firstItem() ?? 0); ?></strong>
                    تا
                    <strong><?php echo e($sentMessages->lastItem() ?? 0); ?></strong>
                    از
                    <strong><?php echo e($sentMessages->total()); ?></strong>
                    نتیجه
                </div>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination custom-pagination mb-0">
                        
                        <li class="page-item <?php echo e($sentMessages->onFirstPage() ? 'disabled' : ''); ?>">
                            <a class="page-link" href="#" wire:click="previousPage" tabindex="-1"
                                aria-disabled="<?php echo e($sentMessages->onFirstPage() ? 'true' : 'false'); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sentMessages->getUrlRange(1, $sentMessages->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page == $sentMessages->currentPage()): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?php echo e($page); ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="#" wire:click="gotoPage(<?php echo e($page); ?>)"><?php echo e($page); ?></a>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <li class="page-item <?php echo e(!$sentMessages->hasMorePages() ? 'disabled' : ''); ?>">
                            <a class="page-link" href="#" wire:click="nextPage"
                                aria-disabled="<?php echo e(!$sentMessages->hasMorePages() ? 'true' : 'false'); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\sms\sent-messages.blade.php ENDPATH**/ ?>