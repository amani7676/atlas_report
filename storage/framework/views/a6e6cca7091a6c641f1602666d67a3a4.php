<div>
    <?php $__env->startSection('title', 'مدیریت API'); ?>

    <style>
        .api-manager-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        .section-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .residents-table {
            width: 100%;
            border-collapse: collapse;
        }

        .residents-table th,
        .residents-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .residents-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .residents-table tr:hover {
            background: #f9fafb;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .resident-detail {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .detail-item label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .detail-item span {
            font-weight: 600;
            color: #1f2937;
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .reports-table th,
        .reports-table td {
            padding: 10px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .reports-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }

        .endpoint-name {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .input-group {
            margin-bottom: 12px;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
        }

        .input-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>

    <div class="api-manager-container">
        <h1 style="color: #1f2937; margin-bottom: 24px;">مدیریت API</h1>

        <!-- بخش APIهای موجود -->
        <div class="section-card">
            <h2 style="color: #1f2937; margin-bottom: 16px;">APIهای موجود</h2>
            <p style="color: #6b7280; margin-bottom: 16px;">لیست تمام APIهای موجود برای دریافت اطلاعات کاربران و تخلفات</p>
            
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>نام API</th>
                        <th>مسیر</th>
                        <th>توضیحات</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>دریافت اطلاعات یک کاربر</strong></td>
                        <td>
                            <span class="endpoint-name">/api/resident/{residentId}</span>
                        </td>
                        <td>دریافت اطلاعات کامل یک کاربر همراه با تخلفات و گزارش‌های اطلاع‌رسانی</td>
                        <td>
                            <a href="/api/resident/1" target="_blank" class="btn btn-primary btn-sm">تست</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>دریافت همه کاربران</strong></td>
                        <td>
                            <span class="endpoint-name">/api/residents/all</span>
                        </td>
                        <td>دریافت اطلاعات همه کاربران همراه با تخلفات و گزارش‌های اطلاع‌رسانی</td>
                        <td>
                            <a href="/api/residents/all" target="_blank" class="btn btn-primary btn-sm">تست</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>لیست گزارش‌ها با Endpoint</strong></td>
                        <td>
                            <span class="endpoint-name">/api/reports/endpoints</span>
                        </td>
                        <td>دریافت لیست همه گزارش‌ها همراه با نام endpoint</td>
                        <td>
                            <a href="/api/reports/endpoints" target="_blank" class="btn btn-primary btn-sm">تست</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>همگام‌سازی کاربران</strong></td>
                        <td>
                            <span class="endpoint-name">POST /api/residents/sync</span>
                        </td>
                        <td>همگام‌سازی کاربران از API خارجی</td>
                        <td>
                            <span style="color: #6b7280; font-size: 12px;">فقط POST</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message): ?>
            <div class="alert alert-<?php echo e($messageType === 'success' ? 'success' : 'error'); ?>">
                <?php echo e($message); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- بخش مدیریت Endpoint های گزارش‌ها -->
        <div class="section-card">
            <h2 style="color: #1f2937; margin-bottom: 16px;">مدیریت Endpoint های گزارش‌ها</h2>
            <p style="color: #6b7280; margin-bottom: 16px;">هر گزارش می‌تواند یک نام endpoint اختصاصی داشته باشد که در API استفاده می‌شود.</p>
            
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>عنوان گزارش</th>
                        <th>دسته‌بندی</th>
                        <th>نام Endpoint</th>
                        <th>تخلفات این گزارش</th>
                        <th>تعداد کاربران</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingEndpoint == $report->id): ?>
                            <tr>
                                <td colspan="6">
                                    <div style="background: #f9fafb; padding: 16px; border-radius: 6px;">
                                        <form wire:submit.prevent="updateEndpoint">
                                            <div class="input-group">
                                                <label>نام Endpoint:</label>
                                                <input type="text" wire:model="editEndpointName" placeholder="مثال: report-violation">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editEndpointName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #ef4444; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <div style="display: flex; gap: 8px;">
                                                <button type="submit" class="btn btn-success btn-sm">ذخیره</button>
                                                <button type="button" wire:click="cancelEditEndpoint" class="btn btn-secondary btn-sm">لغو</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><strong><?php echo e($report->title); ?></strong></td>
                                <td><?php echo e($report->category->name ?? '-'); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->api_endpoint_name): ?>
                                        <a href="/api/resident/{residentId}?endpoint=<?php echo e($report->api_endpoint_name); ?>" target="_blank" class="endpoint-name" style="text-decoration: none; color: #3b82f6;">
                                            <?php echo e($report->api_endpoint_name); ?>

                                        </a>
                                    <?php else: ?>
                                        <a href="/api/resident/{residentId}?report_id=<?php echo e($report->id); ?>" target="_blank" class="endpoint-name" style="text-decoration: none; color: #6b7280;">
                                            /api/resident/{id}?report_id=<?php echo e($report->id); ?>

                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->category->name === 'تخلف'): ?>
                                        <span class="badge badge-danger"><?php echo e($report->total_violations_system ?? 0); ?></span>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php echo e($report->affected_residents_count ?? 0); ?></td>
                                <td>
                                    <button wire:click="startEditEndpoint(<?php echo e($report->id); ?>)" class="btn btn-primary btn-sm">ویرایش</button>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
                <h4 style="margin-bottom: 10px; color: #1f2937;">آمار کلی تخلفات</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="background: white; padding: 15px; border-radius: 6px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #dc2626;">
                            <?php echo e($reports->first()->total_violations_all_reports ?? 0); ?>

                        </div>
                        <div style="color: #6b7280; font-size: 14px;">مجموع کل تخلفات</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش لیست کاربران -->
        <div class="section-card">
            <h2 style="color: #1f2937; margin-bottom: 16px;">لیست کاربران</h2>
            
            <div class="search-box">
                <input type="text" wire:model.live="search" placeholder="جستجو بر اساس نام، شماره تلفن یا ID...">
            </div>

            <table class="residents-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام کامل</th>
                        <th>شماره تلفن</th>
                        <th>شماره تخت</th>
                        <th>شماره اتاق</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $residents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($resident->resident_id); ?></td>
                            <td><?php echo e($resident->resident_full_name ?? '-'); ?></td>
                            <td><?php echo e($resident->resident_phone ?? '-'); ?></td>
                            <td><?php echo e($resident->bed_name ?? '-'); ?></td>
                            <td><?php echo e($resident->room_name ?? '-'); ?></td>
                            <td>
                                <button wire:click="selectResident(<?php echo e($resident->resident_id); ?>)" class="btn btn-primary btn-sm">مشاهده جزئیات</button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>

            <?php echo e($residents->links()); ?>

        </div>

        <!-- بخش جزئیات کاربر انتخاب شده -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResident && $residentData): ?>
            <div class="section-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2 style="color: #1f2937; margin: 0;">جزئیات کاربر</h2>
                    <div style="display: flex; gap: 8px;">
                        <a href="/api/resident/<?php echo e($residentData->resident_id); ?>" target="_blank" class="btn btn-success btn-sm">
                            <i class="fas fa-code"></i> تست API
                        </a>
                        <button wire:click="$set('selectedResident', null)" class="btn btn-secondary btn-sm">بستن</button>
                    </div>
                </div>

                <div class="resident-detail">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Resident ID</label>
                            <span><?php echo e($residentData->resident_id); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Contract ID</label>
                            <span><?php echo e($residentData->contract_id ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>نام کامل</label>
                            <span><?php echo e($residentData->resident_full_name ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>شماره تلفن</label>
                            <span><?php echo e($residentData->resident_phone ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>شماره تخت</label>
                            <span><?php echo e($residentData->bed_name ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>شماره اتاق</label>
                            <span><?php echo e($residentData->room_name ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>واحد</label>
                            <span><?php echo e($residentData->unit_name ?? '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>مجموع تخلفات</label>
                            <span style="color: <?php echo e($residentData->total_violations > 0 ? '#dc2626' : '#059669'); ?>;">
                                <?php echo e($residentData->total_violations); ?>

                            </span>
                        </div>
                    </div>

                    <h3 style="color: #1f2937; margin-bottom: 12px;">گزارش‌های ثبت شده</h3>
                    
                    <!-- تخلفات -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="color: #dc2626; margin-bottom: 8px;">تخلفات (<?php echo e($residentData->total_violations ?? 0); ?> امتیاز)</h4>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($residentData->violations && $residentData->violations->count() > 0): ?>
                            <table class="reports-table">
                                <thead>
                                    <tr>
                                        <th>عنوان گزارش</th>
                                        <th>نمره منفی</th>
                                        <th>Endpoint</th>
                                        <th>تاریخ ثبت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $residentData->violations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $residentReport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($residentReport->report->title ?? '-'); ?></td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($residentReport->report->negative_score > 0): ?>
                                                    <span class="badge badge-danger"><?php echo e($residentReport->report->negative_score); ?></span>
                                                <?php else: ?>
                                                    <span>-</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($residentReport->report->api_endpoint_name): ?>
                                                    <span class="endpoint-name"><?php echo e($residentReport->report->api_endpoint_name); ?></span>
                                                <?php else: ?>
                                                    <span class="endpoint-name">report_id=<?php echo e($residentReport->report->id); ?></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td><?php echo e(\Carbon\Carbon::parse($residentReport->created_at)->format('Y/m/d H:i')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="color: #6b7280;">هیچ تخلفی برای این کاربر ثبت نشده است.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- اطلاع‌رسانی‌ها -->
                    <div>
                        <h4 style="color: #3b82f6; margin-bottom: 8px;">گزارش‌های اطلاع‌رسانی</h4>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($residentData->notifications && $residentData->notifications->count() > 0): ?>
                            <table class="reports-table">
                                <thead>
                                    <tr>
                                        <th>عنوان گزارش</th>
                                        <th>Endpoint</th>
                                        <th>تاریخ ثبت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $residentData->notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $residentReport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($residentReport->report->title ?? '-'); ?></td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($residentReport->report->api_endpoint_name): ?>
                                                    <span class="endpoint-name"><?php echo e($residentReport->report->api_endpoint_name); ?></span>
                                                <?php else: ?>
                                                    <span class="endpoint-name">report_id=<?php echo e($residentReport->report->id); ?></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td><?php echo e(\Carbon\Carbon::parse($residentReport->created_at)->format('Y/m/d H:i')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="color: #6b7280;">هیچ گزارش اطلاع‌رسانی برای این کاربر ثبت نشده است.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\admin\api-manager.blade.php ENDPATH**/ ?>