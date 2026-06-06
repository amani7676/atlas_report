<div>
    <?php $__env->startSection('title', 'مدیریت API Key'); ?>

    <style>
        .api-key-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .auth-card {
            max-width: 400px;
            margin: 100px auto;
            padding: 32px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .api-key-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .api-key-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .api-key-table th,
        .api-key-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .api-key-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .api-key-table tr:hover {
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

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .input-group {
            margin-bottom: 16px;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .input-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
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

        .key-value {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 4px;
            word-break: break-all;
            max-width: 400px;
        }

        .actions {
            display: flex;
            gap: 8px;
        }
    </style>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isAuthenticated): ?>
        <div class="auth-card">
            <h2 style="text-align: center; margin-bottom: 24px; color: #1f2937;">ورود به مدیریت API Key</h2>
            <form wire:submit.prevent="authenticate">
                <div class="input-group">
                    <label for="password">رمز عبور:</label>
                    <input type="password" id="password" wire:model="password" placeholder="رمز عبور را وارد کنید" required autofocus>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message && $messageType === 'error'): ?>
                    <div class="alert alert-error"><?php echo e($message); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="submit" class="btn btn-primary" style="width: 100%;">ورود</button>
            </form>
        </div>
    <?php else: ?>
        <div class="api-key-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1 style="color: #1f2937; margin: 0;">مدیریت API Key</h1>
                <button wire:click="logout" class="btn btn-secondary">خروج</button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message): ?>
                <div class="alert alert-<?php echo e($messageType === 'success' ? 'success' : 'error'); ?>">
                    <?php echo e($message); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="api-key-card">
                <!-- Debug info -->
                <div style="background: #f0f0f0; padding: 10px; margin-bottom: 16px; border-radius: 4px;">
                    <small>Debug: showingAddForm = <?php echo e($showingAddForm ? 'TRUE' : 'FALSE'); ?></small>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2 style="color: #1f2937; margin: 0;">لیست API Key ها</h2>
                    <button wire:click="showAddForm" class="btn btn-success" onclick="console.log('Button clicked!')">افزودن API Key جدید</button>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showingAddForm): ?>
                    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="margin: 0; color: #1f2937; font-size: 18px;">افزودن API Key جدید</h3>
                                <button type="button" wire:click="cancelAdd" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
                            </div>
                            <form wire:submit.prevent="addKey">
                                <div class="input-group">
                                    <label>نام کاربری:</label>
                                    <input type="text" wire:model="newUsername" placeholder="نام کاربری را وارد کنید" required>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newUsername'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #ef4444; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="input-group">
                                    <label>API Key:</label>
                                    <input type="text" wire:model="newApiKey" placeholder="API Key را وارد کنید" required>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newApiKey'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span style="color: #ef4444; font-size: 12px;"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="input-group">
                                    <label>توضیحات:</label>
                                    <textarea wire:model="newDescription" placeholder="توضیحات اختیاری"></textarea>
                                </div>
                                <div class="input-group">
                                    <label style="display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" wire:model="newIsActive">
                                        فعال
                                    </label>
                                </div>
                                <div style="display: flex; gap: 8px; margin-top: 20px;">
                                    <button type="submit" class="btn btn-success">ذخیره</button>
                                    <button type="button" wire:click="cancelAdd" class="btn btn-secondary">لغو</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($apiKeys) > 0): ?>
                    <table class="api-key-table">
                        <thead>
                            <tr>
                                <th>نام کلید</th>
                                <th>مقدار</th>
                                <th>توضیحات</th>
                                <th>وضعیت</th>
                                <th>تاریخ ایجاد</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $apiKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingKey == $key['id']): ?>
                                    <tr>
                                        <td colspan="6">
                                            <div style="background: #f9fafb; padding: 20px; border-radius: 8px;">
                                                <h4 style="margin-top: 0;">ویرایش API Key</h4>
                                                <form wire:submit.prevent="updateKey">
                                                    <div class="input-group">
                                                        <label>نام کلید:</label>
                                                        <input type="text" wire:model="editKeyName" required>
                                                    </div>
                                                    <div class="input-group">
                                                        <label>مقدار API Key:</label>
                                                        <textarea wire:model="editKeyValue" required></textarea>
                                                    </div>
                                                    <div class="input-group">
                                                        <label>توضیحات:</label>
                                                        <textarea wire:model="editDescription"></textarea>
                                                    </div>
                                                    <div class="input-group">
                                                        <label style="display: flex; align-items: center; gap: 8px;">
                                                            <input type="checkbox" wire:model="editIsActive">
                                                            فعال
                                                        </label>
                                                    </div>
                                                    <div style="display: flex; gap: 8px;">
                                                        <button type="submit" class="btn btn-primary">ذخیره</button>
                                                        <button type="button" wire:click="cancelEdit" class="btn btn-secondary">لغو</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><strong><?php echo e($key['key_name']); ?></strong></td>
                                        <td>
                                            <div class="key-value">
                                                <?php
                                                    $keyData = json_decode($key['key_value'], true);
                                                    if ($keyData && isset($keyData['username'])) {
                                                        echo 'Username: ' . e($keyData['username']) . '<br>API Key: ' . e(substr($keyData['api_key'], 0, 20)) . '...';
                                                    } else {
                                                        echo e(Str::limit($key['key_value'], 50));
                                                    }
                                                ?>
                                            </div>
                                        </td>
                                        <td><?php echo e($key['description'] ?? '-'); ?></td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key['is_active']): ?>
                                                <span class="badge badge-success">فعال</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">غیرفعال</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td><?php echo e(\Carbon\Carbon::parse($key['created_at'])->format('Y/m/d H:i')); ?></td>
                                        <td>
                                            <div class="actions">
                                                <button wire:click="startEdit(<?php echo e($key['id']); ?>)" class="btn btn-primary" style="font-size: 12px; padding: 6px 12px;">ویرایش</button>
                                                <button wire:click="toggleActive(<?php echo e($key['id']); ?>)" class="btn btn-<?php echo e($key['is_active'] ? 'secondary' : 'success'); ?>" style="font-size: 12px; padding: 6px 12px;">
                                                    <?php echo e($key['is_active'] ? 'غیرفعال' : 'فعال'); ?>

                                                </button>
                                                <button wire:click="deleteKey(<?php echo e($key['id']); ?>)" class="btn btn-danger" style="font-size: 12px; padding: 6px 12px;" onclick="return confirm('آیا مطمئن هستید؟')">حذف</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #6b7280;">
                        <p>هیچ API Key ای ثبت نشده است.</p>
                        <button wire:click="showAddForm" class="btn btn-success">افزودن اولین API Key</button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views/livewire/admin/api-key-manager.blade.php ENDPATH**/ ?>