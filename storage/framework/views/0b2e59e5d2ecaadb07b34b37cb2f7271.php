<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">لاگ‌های ارسال پیام خوش‌آمدگویی</h4>
            <p class="text-muted mb-0">مشاهده و مدیریت لاگ‌های ارسال پیام‌های خوش‌آمدگویی</p>
        </div>
        <a href="/welcome-messages" class="btn btn-outline-primary">
            <i class="fas fa-arrow-right me-2"></i>بازگشت به پیام‌ها
        </a>
    </div>

    <!-- فیلترها -->
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="resetFilters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">جستجو</label>
                        <input type="text" class="form-control" wire:model.live="search" 
                               placeholder="نام، تلفن یا شناسه اقامت‌گر...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">وضعیت</label>
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="">همه</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">از تاریخ</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تا تاریخ</label>
                        <input type="date" class="form-control" wire:model.live="dateTo">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-refresh me-2"></i>بازنشانی فیلترها
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول لاگ‌ها -->
    <div class="card">
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>اقامت‌گر</th>
                                <th>تلفن</th>
                                <th>پیام</th>
                                <th>وضعیت</th>
                                <th>RecId</th>
                                <th>زمان ارسال</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo e($log->resident_name); ?></strong>
                                            <br><small class="text-muted">شناسه: <?php echo e($log->resident_id); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="bg-light"><?php echo e($log->resident_phone); ?></code>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->welcomeMessage): ?>
                                            <div>
                                                <strong><?php echo e($log->welcomeMessage->title); ?></strong>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->welcomeMessage->pattern_code): ?>
                                                    <br><small class="text-muted">کد: <?php echo e($log->welcomeMessage->pattern_code); ?></small>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->status === 'sent'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>ارسال شده
                                            </span>
                                        <?php elseif($log->status === 'failed'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>ناموفق
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>در انتظار
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->rec_id): ?>
                                            <code class="bg-success text-white"><?php echo e($log->rec_id); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo e(jalaliDate($log->created_at, 'Y/m/d H:i:s')); ?></small>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->sent_at): ?>
                                            <br><small class="text-success">ارسال: <?php echo e(jalaliDate($log->sent_at, 'H:i:s')); ?></small>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->status === 'failed'): ?>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        wire:click="resendMessage(<?php echo e($log->id); ?>)"
                                                        title="ارسال مجدد">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <button type="button" class="btn btn-outline-info" 
                                                    wire:click="$wire.set('selectedLog', <?php echo e($log->id); ?>); $wire.dispatch('showDetailsModal')"
                                                    title="جزئیات">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    wire:click="deleteLog(<?php echo e($log->id); ?>)"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    <?php echo e($logs->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">هیچ لاگی یافت نشد</h5>
                    <p class="text-muted">هیچ لاگ ارسال پیامی در سیستم وجود ندارد.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- مودال جزئیات -->
    <div wire:ignore.self>
        <div class="modal fade" id="detailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">جزئیات ارسال پیام</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($selectedLog)): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>اطلاعات اقامت‌گر</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>نام:</strong></td>
                                            <td><?php echo e($selectedLog->resident_name); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>شناسه:</strong></td>
                                            <td><?php echo e($selectedLog->resident_id); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>تلفن:</strong></td>
                                            <td><?php echo e($selectedLog->resident_phone); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>اطلاعات ارسال</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>وضعیت:</strong></td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedLog->status === 'sent'): ?>
                                                    <span class="badge bg-success">ارسال شده</span>
                                                <?php elseif($selectedLog->status === 'failed'): ?>
                                                    <span class="badge bg-danger">ناموفق</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">در انتظار</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedLog->rec_id): ?>
                                        <tr>
                                            <td><strong>RecId:</strong></td>
                                            <td><code><?php echo e($selectedLog->rec_id); ?></code></td>
                                        </tr>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedLog->response_code): ?>
                                        <tr>
                                            <td><strong>کد پاسخ:</strong></td>
                                            <td><code><?php echo e($selectedLog->response_code); ?></code></td>
                                        </tr>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <tr>
                                            <td><strong>ایجاد:</strong></td>
                                            <td><?php echo e(jalaliDate($selectedLog->created_at, 'Y/m/d H:i:s')); ?></td>
                                        </tr>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedLog->sent_at): ?>
                                        <tr>
                                            <td><strong>ارسال:</strong></td>
                                            <td><?php echo e(jalaliDate($selectedLog->sent_at, 'Y/m/d H:i:s')); ?></td>
                                        </tr>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </table>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedLog->error_message): ?>
                            <div class="alert alert-danger mt-3">
                                <strong>خطا:</strong> <?php echo e($selectedLog->error_message); ?>

                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedLog->api_response): ?>
                            <div class="mt-3">
                                <h6>پاسخ API</h6>
                                <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;"><?php echo e(is_array($selectedLog->api_response) ? json_encode($selectedLog->api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $selectedLog->api_response); ?></pre>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- اسکریپت‌ها -->
    <script>
        document.addEventListener('livewire:init', () => {
            // باز کردن مودال جزئیات
            Livewire.on('showDetailsModal', () => {
                new bootstrap.Modal(document.getElementById('detailsModal')).show();
            });
        });
    </script>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\welcome-messages\logs.blade.php ENDPATH**/ ?>