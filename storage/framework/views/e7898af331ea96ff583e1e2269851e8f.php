<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>لیست دسته‌بندی‌ها</h2>
            <a href="/categories/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد دسته‌بندی جدید
            </a>
        </div>

        <!-- Bulk Actions -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedCategories) > 0): ?>
            <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <div>
                        <strong><?php echo e(count($selectedCategories)); ?></strong> دسته‌بندی انتخاب شده است
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <select wire:model="bulkAction" class="form-control" style="width: 150px;">
                            <option value="">عملیات گروهی</option>
                            <option value="delete">حذف انتخاب‌شده‌ها</option>
                        </select>
                        <button wire:click="executeBulkAction" class="btn btn-danger">
                            <i class="fas fa-play"></i> اجرا
                        </button>
                        <button wire:click="$set('selectedCategories', [])" class="btn" style="background: #6c757d; color: white;">
                            <i class="fas fa-times"></i> لغو
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; font-size: 14px;">
                    <input
                        type="checkbox"
                        id="deleteWithReports"
                        wire:model.live="deleteWithReports"
                    >
                    <label for="deleteWithReports" style="cursor: pointer;">
                        حذف دسته‌بندی‌ها همراه با گزارش‌های مرتبط
                    </label>
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
                    placeholder="جستجوی دسته‌بندی..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- Categories Table -->
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
                        <th>نام دسته‌بندی</th>
                        <th>توضیحات</th>
                        <th>تعداد گزارش‌ها</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedCategories"
                                    value="<?php echo e($category->id); ?>"
                                    style="cursor: pointer;"
                                >
                            </td>
                            <td>
                                <strong><?php echo e($category->name); ?></strong>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->description): ?>
                                    <p style="color: #666; font-size: 14px; margin: 0;">
                                        <?php echo e(Str::limit($category->description, 50)); ?>

                                    </p>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">بدون توضیح</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->reports_count > 0): ?>
                                    <span style="background: #4cc9f0; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px;">
                                        <?php echo e($category->reports_count); ?>

                                    </span>
                                <?php else: ?>
                                    <span style="background: #6c757d; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px;">
                                        ۰
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td><?php echo e(jalaliDate($category->created_at, 'Y/m/d')); ?></td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <a href="/categories/edit/<?php echo e($category->id); ?>" class="btn" style="background: #4cc9f0; color: white;" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->reports_count > 0): ?>
                                        <button
                                            onclick="confirmDeleteCategoryWithReports(<?php echo e($category->id); ?>, '<?php echo e($category->name); ?>', <?php echo e($category->reports_count); ?>)"
                                            class="btn btn-danger"
                                            title="حذف همراه با گزارش‌ها"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button
                                            onclick="confirmDelete(<?php echo e($category->id); ?>, 'Category', 'دسته‌بندی')"
                                            class="btn btn-danger"
                                            title="حذف"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-folder-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <p>هیچ دسته‌بندی یافت نشد</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <?php echo e($categories->links()); ?>

        </div>
    </div>

    <script>
        // Confirm delete category with reports
        window.confirmDeleteCategoryWithReports = function(id, name, reportsCount) {
            Swal.fire({
                title: 'حذف دسته‌بندی همراه با گزارش‌ها',
                html: `آیا مطمئن هستید که می‌خواهید دسته‌بندی <strong>"${name}"</strong> را حذف کنید؟<br>
                      <span style="color: #f72585;">این عمل ${reportsCount} گزارش مرتبط را نیز حذف خواهد کرد!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، همه را حذف کن',
                cancelButtonText: 'لغو',
                reverseButtons: true,
                showDenyButton: true,
                denyButtonText: 'فقط دسته‌بندی را حذف کن',
                denyButtonColor: '#ff9e00'
            }).then((result) => {
                if (result.isConfirmed) {
                    // حذف همراه با گزارش‌ها
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteCategory(id, true);
                } else if (result.isDenied) {
                    // فقط دسته‌بندی را حذف کن (اگر امکان‌پذیر باشد)
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteCategory(id, false);
                }
            });
        }
    </script>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\categories\index.blade.php ENDPATH**/ ?>