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
        @if(count($selectedCategories) > 0)
            <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <div>
                        <strong>{{ count($selectedCategories) }}</strong> دسته‌بندی انتخاب شده است
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
        @endif

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
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedCategories"
                                    value="{{ $category->id }}"
                                    style="cursor: pointer;"
                                >
                            </td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td>
                                @if($category->description)
                                    <p style="color: #666; font-size: 14px; margin: 0;">
                                        {{ Str::limit($category->description, 50) }}
                                    </p>
                                @else
                                    <span style="color: #999; font-style: italic;">بدون توضیح</span>
                                @endif
                            </td>
                            <td>
                                @if($category->reports_count > 0)
                                    <span style="background: #4cc9f0; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px;">
                                        {{ $category->reports_count }}
                                    </span>
                                @else
                                    <span style="background: #6c757d; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px;">
                                        ۰
                                    </span>
                                @endif
                            </td>
                            <td>{{ $category->created_at->format('Y/m/d') }}</td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <a href="/categories/edit/{{ $category->id }}" class="btn" style="background: #4cc9f0; color: white;" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($category->reports_count > 0)
                                        <button
                                            onclick="confirmDeleteCategoryWithReports({{ $category->id }}, '{{ $category->name }}', {{ $category->reports_count }})"
                                            class="btn btn-danger"
                                            title="حذف همراه با گزارش‌ها"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button
                                            onclick="confirmDelete({{ $category->id }}, 'Category', 'دسته‌بندی')"
                                            class="btn btn-danger"
                                            title="حذف"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-folder-open" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <p>هیچ دسته‌بندی یافت نشد</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $categories->links() }}
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
                    @this.deleteCategory(id, true);
                } else if (result.isDenied) {
                    // فقط دسته‌بندی را حذف کن (اگر امکان‌پذیر باشد)
                    @this.deleteCategory(id, false);
                }
            });
        }
    </script>
</div>
