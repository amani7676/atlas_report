<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>مدیریت پیام‌های SMS</h2>
            <button wire:click="openCreateModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد پیام جدید
            </button>
        </div>

        <!-- Search -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی پیام..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- SMS Messages Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان
                            @if($sortField === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>نوع پیام</th>
                        <th>متن پیام</th>
                        <th>وضعیت</th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            تاریخ ایجاد
                            @if($sortField === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($smsMessages as $sms)
                        <tr>
                            <td>
                                <strong>{{ $sms->title }}</strong>
                            </td>
                            <td>
                                @if($sms->message_type === 'manual')
                                    <span style="background: #4361ee; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                        دستی
                                    </span>
                                @elseif($sms->message_type === 'group')
                                    <span style="background: #4cc9f0; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                        گروهی
                                    </span>
                                @else
                                    <span style="background: #ff9e00; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                        خودکار
                                    </span>
                                @endif
                            </td>
                            <td>
                                <p style="color: #666; font-size: 14px; margin: 0; max-width: 300px;">
                                    {{ Str::limit($sms->text, 50) }}
                                </p>
                            </td>
                            <td>
                                @if($sms->is_active)
                                    <span style="background: #4cc9f0; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                        فعال
                                    </span>
                                @else
                                    <span style="background: #6c757d; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                        غیرفعال
                                    </span>
                                @endif
                            </td>
                            <td>{{ $sms->created_at->format('Y/m/d H:i') }}</td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <button wire:click="openSendModal({{ $sms->id }})" class="btn btn-success" title="ارسال پیامک">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                    <button wire:click="openEditModal({{ $sms->id }})" class="btn" style="background: #4cc9f0; color: white;" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        onclick="confirmDeleteSms({{ $sms->id }})"
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
                            <td colspan="6" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <p>هیچ پیامی یافت نشد</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 20px;">
            {{ $smsMessages->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; padding: 30px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>{{ $modalMode === 'create' ? 'ایجاد پیام جدید' : 'ویرایش پیام' }}</h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="form-group">
                        <label class="form-label">عنوان *</label>
                        <input type="text" wire:model="title" class="form-control" required>
                        @error('title') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">توضیحات</label>
                        <textarea wire:model="description" class="form-control" rows="3"></textarea>
                        @error('description') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">لینک (اختیاری)</label>
                        <input type="url" wire:model="link" class="form-control" placeholder="https://...">
                        @error('link') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">متن پیام *</label>
                        <textarea wire:model="text" class="form-control" rows="5" required placeholder="متن پیامک که برای اقامتگران ارسال می‌شود"></textarea>
                        @error('text') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                        <small style="color: #666; font-size: 12px;">
                            می‌توانید از متغیرهای {resident_name} و {violation} استفاده کنید (برای پیامک خودکار)
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">نوع پیام *</label>
                        <select wire:model="message_type" class="form-control" required>
                            <option value="manual">دستی</option>
                            <option value="group">گروهی</option>
                            <option value="automatic">خودکار (برای تخلفات)</option>
                        </select>
                        @error('message_type') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" wire:model="is_active">
                            <span>فعال</span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">لغو</button>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Send SMS Modal -->
    @if($showSendModal && $selectedMessage)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; padding: 30px; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>ارسال پیامک: {{ $selectedMessage->title }}</h3>
                    <button wire:click="closeSendModal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>

                <div style="background: #e8f4fd; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <p><strong>متن پیام:</strong> {{ $selectedMessage->text }}</p>
                    @if($selectedMessage->link)
                        <p><strong>لینک:</strong> {{ $selectedMessage->link }}</p>
                    @endif
                </div>

                <!-- Resident Search -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">جستجوی اقامت‌گر</label>
                    <input type="text" wire:model.live.debounce.300ms="residentSearch" class="form-control" placeholder="جستجو بر اساس نام، شماره تلفن یا واحد...">
                </div>

                @if($loadingResidents)
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #4361ee;"></i>
                        <p>در حال بارگذاری...</p>
                    </div>
                @else
                    <!-- Residents List -->
                    <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 10px;">
                        <div style="margin-bottom: 10px;">
                            <button wire:click="selectAllResidents" class="btn" style="background: #4361ee; color: white; font-size: 12px;">
                                انتخاب/لغو انتخاب همه
                            </button>
                            <span style="margin-right: 15px; color: #666;">
                                {{ count($selectedResidents) }} مورد انتخاب شده
                            </span>
                        </div>

                        <table class="table" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">
                                        <input
                                            type="checkbox"
                                            {{ count($selectedResidents) === count($filteredResidents) && count($filteredResidents) > 0 ? 'checked' : '' }}
                                            wire:click="selectAllResidents"
                                            style="cursor: pointer;"
                                        >
                                    </th>
                                    <th>نام</th>
                                    <th>شماره تلفن</th>
                                    <th>واحد</th>
                                    <th>اتاق</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($filteredResidents as $resident)
                                    <tr>
                                        <td>
                                            <input
                                                type="checkbox"
                                                wire:click="toggleResidentSelection({{ $resident['id'] }})"
                                                {{ in_array($resident['id'], $selectedResidents) ? 'checked' : '' }}
                                                style="cursor: pointer;"
                                            >
                                        </td>
                                        <td>{{ $resident['name'] }}</td>
                                        <td>{{ $resident['phone'] }}</td>
                                        <td>{{ $resident['unit_name'] }}</td>
                                        <td>{{ $resident['room_name'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                                            اقامت‌گری یافت نشد
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" wire:click="closeSendModal" class="btn" style="background: #6c757d; color: white;">لغو</button>
                    <button wire:click="sendSms" class="btn btn-success" {{ empty($selectedResidents) ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane"></i>
                        ارسال به {{ count($selectedResidents) }} نفر
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function confirmDeleteSms(id) {
        Swal.fire({
            title: 'حذف پیام',
            text: 'آیا مطمئن هستید که می‌خواهید این پیام را حذف کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'لغو',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', id);
            }
        });
    }
</script>