<div>
    <div class="card">
        <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h2>لیست گزارش‌ها</h2>
            <a href="/reports/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد گزارش جدید
            </a>
        </div>

        <!-- Bulk Actions -->
        @if(count($selectedReports) > 0)
            <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 15px;">
                    <div>
                        <strong>{{ count($selectedReports) }}</strong> گزارش انتخاب شده است
                    </div>
                    <div class="d-flex gap-2" style="flex-wrap: wrap;">
                        <select wire:model="bulkAction" class="form-control">
                            <option value="">عملیات گروهی</option>
                            <option value="delete">حذف انتخاب‌شده‌ها</option>
                        </select>
                        <button wire:click="executeBulkAction" class="btn btn-danger">
                            <i class="fas fa-play"></i> اجرا
                        </button>
                        <button wire:click="$set('selectedReports', [])" class="btn" style="background: #6c757d; color: white;">
                            <i class="fas fa-times"></i> لغو
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Search and Filters -->
        <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <div class="d-flex align-items-center gap-2" style="flex: 1; min-width: 200px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی گزارش..."
                    class="form-control"
                >
            </div>

            <div class="d-flex gap-2">
                <select wire:model.live="perPage" class="form-control">
                    <option value="5">5 در صفحه</option>
                    <option value="10">10 در صفحه</option>
                    <option value="25">25 در صفحه</option>
                    <option value="50">50 در صفحه</option>
                </select>
            </div>
        </div>

        <!-- Reports Table -->
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
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان
                            @if($sortField === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @else
                                <i class="fas fa-sort"></i>
                            @endif
                        </th>
                        <th>دسته‌بندی</th>
                        <th wire:click="sortBy('negative_score')" style="cursor: pointer;">
                            نمره منفی
                            @if($sortField === 'negative_score')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @else
                                <i class="fas fa-sort"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('increase_coefficient')" style="cursor: pointer;">
                            ضریب افزایش
                            @if($sortField === 'increase_coefficient')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @else
                                <i class="fas fa-sort"></i>
                            @endif
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedReports"
                                    value="{{ $report->id }}"
                                    style="cursor: pointer;"
                                >
                            </td>
                            <td>{{ $report->title }}</td>
                            <td>
                                <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    {{ $report->category->name }}
                                </span>
                            </td>
                            <td>
                                <span style="color: #f72585; font-weight: bold;">
                                    {{ $report->negative_score }}
                                </span>
                            </td>
                            <td>{{ $report->increase_coefficient }}</td>
                            <td>
                                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                                    <a href="/reports/edit/{{ $report->id }}" class="btn" style="background: #4cc9f0; color: white;" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        onclick="confirmDelete({{ $report->id }}, 'Report', 'گزارش')"
                                        class="btn btn-danger"
                                        title="حذف"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <p>هیچ گزارشی یافت نشد</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $reports->links() }}
        </div>
    </div>

    <script>
        // تابع تایید حذف
        function confirmDelete(id, type, persianType = null) {
            const typeName = persianType || (type === 'Report' ? 'گزارش' : 'دسته‌بندی');
            
            Swal.fire({
                title: `حذف ${typeName}`,
                text: `آیا مطمئن هستید که می‌خواهید این ${typeName} را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (type === 'Report') {
                        @this.deleteReport(id);
                    } else {
                        @this.deleteCategory(id);
                    }
                }
            });
        }

        // Listen for bulk delete confirmation
        window.addEventListener('confirmBulkDelete', event => {
            const { type, count } = event.detail;

            Swal.fire({
                title: `حذف ${count} مورد`,
                text: `آیا مطمئن هستید که می‌خواهید ${count} ${type === 'reports' ? 'گزارش' : 'دسته‌بندی'} انتخاب شده را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (type === 'reports') {
                        @this.deleteMultipleReports();
                    } else if (type === 'categories') {
                        @this.deleteMultipleCategories();
                    }
                }
            });
        });
    </script>
</div>
