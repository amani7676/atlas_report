<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>مدیریت لیست‌های سیاه</h2>
            <button wire:click="openCreateModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد لیست سیاه جدید
            </button>
        </div>

        <!-- Search -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی لیست سیاه (عنوان، کد، توضیحات)..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- Blacklists Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان
                            @if($sortBy === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('blacklist_id')" style="cursor: pointer;">
                            کد 5 رقمی
                            @if($sortBy === 'blacklist_id')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>توضیحات</th>
                        <th wire:click="sortBy('is_active')" style="cursor: pointer;">
                            وضعیت
                            @if($sortBy === 'is_active')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            تاریخ ایجاد
                            @if($sortBy === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blacklists as $blacklist)
                        <tr>
                            <td>
                                <strong>{{ $blacklist->title }}</strong>
                            </td>
                            <td>
                                @if($blacklist->blacklist_id)
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                                        {{ $blacklist->blacklist_id }}
                                    </span>
                                @else
                                    <span style="color: #dc3545; font-style: italic;">در انتظار</span>
                                @endif
                            </td>
                            <td>
                                @if($blacklist->description)
                                    <p style="color: #666; font-size: 14px; margin: 0;">
                                        {{ Str::limit($blacklist->description, 50) }}
                                    </p>
                                @else
                                    <span style="color: #999; font-style: italic;">بدون توضیح</span>
                                @endif
                            </td>
                            <td>
                                @if($blacklist->is_active)
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        فعال
                                    </span>
                                @else
                                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        غیرفعال
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $blacklist->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    @if($blacklist->api_response)
                                        <button 
                                            wire:click="showApiResponse({{ $blacklist->id }})" 
                                            class="btn" 
                                            style="background: #17a2b8; color: white; padding: 5px 10px; font-size: 12px;"
                                            title="مشاهده پاسخ API"
                                        >
                                            <i class="fas fa-code"></i>
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="toggleActive({{ $blacklist->id }})" 
                                        class="btn" 
                                        style="background: {{ $blacklist->is_active ? '#ffc107' : '#28a745' }}; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="{{ $blacklist->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}"
                                    >
                                        <i class="fas fa-{{ $blacklist->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button 
                                        wire:click="openEditModal({{ $blacklist->id }})" 
                                        class="btn" 
                                        style="background: #4361ee; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        onclick="confirmDeleteBlacklist({{ $blacklist->id }}, '{{ $blacklist->title }}')" 
                                        class="btn btn-danger" 
                                        style="padding: 5px 10px; font-size: 12px;"
                                        title="حذف"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                لیست سیاهی یافت نشد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($blacklists->hasPages())
            <div style="margin-top: 20px; display: flex; justify-content: center;">
                {{ $blacklists->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>{{ $isEditing ? 'ویرایش لیست سیاه' : 'ایجاد لیست سیاه جدید' }}</h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $isEditing ? 'updateBlacklist' : 'createBlacklist' }}">
                    <div class="form-group">
                        <label class="form-label">عنوان لیست سیاه <span style="color: red;">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            class="form-control" 
                            placeholder="مثال: لیست سیاه تست"
                            required
                        >
                        @error('title') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">توضیحات</label>
                        <textarea 
                            wire:model="description" 
                            class="form-control" 
                            rows="3"
                            placeholder="توضیحات اختیاری..."
                        ></textarea>
                        @error('description') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
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

                    @if($isEditing)
                        <div style="background: #fff3cd; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #ffc107;">
                            <i class="fas fa-info-circle" style="color: #856404;"></i>
                            <span style="color: #856404; margin-right: 10px;">
                                توجه: ویرایش فقط در دیتابیس محلی اعمال می‌شود و تغییری در پنل ملی پیامک ایجاد نمی‌کند.
                            </span>
                        </div>
                    @endif

                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">
                            لغو
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEditing ? 'ذخیره تغییرات' : 'ایجاد لیست سیاه' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- API Response Modal -->
    @if($showApiResponseModal && $apiResponseData)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>پاسخ API - {{ $apiResponseData['title'] }}</h3>
                    <button wire:click="closeApiResponseModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px;">
                        <strong>کد 5 رقمی:</strong> 
                        @if($apiResponseData['blacklist_id'])
                            <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                                {{ $apiResponseData['blacklist_id'] }}
                            </span>
                        @else
                            <span style="color: #dc3545;">دریافت نشد</span>
                        @endif
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>کد وضعیت HTTP:</strong> {{ $apiResponseData['http_status_code'] ?? '-' }}
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>تاریخ ایجاد:</strong> {{ $apiResponseData['created_at'] }}
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <strong>پاسخ خام API:</strong>
                    <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 400px; overflow-y: auto;">
                        <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px; direction: ltr; text-align: left;">{{ $apiResponseData['api_response'] }}</pre>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button wire:click="closeApiResponseModal" class="btn btn-primary">
                        بستن
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        function confirmDeleteBlacklist(id, title) {
            Swal.fire({
                title: 'حذف لیست سیاه',
                html: `آیا مطمئن هستید که می‌خواهید لیست سیاه <strong>"${title}"</strong> را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deleteBlacklist(id);
                }
            });
        }
    </script>
</div>
