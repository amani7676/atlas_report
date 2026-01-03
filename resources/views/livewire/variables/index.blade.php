<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>مدیریت متغیرهای الگو</h2>
            <button wire:click="openCreateModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد متغیر جدید
            </button>
        </div>

        <!-- Search and Filters -->
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی متغیر (عنوان، کد، فیلد)..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="margin: 0;">فیلتر نوع:</label>
                <select wire:model.live="typeFilter" class="form-control" style="width: 150px;">
                    <option value="">همه</option>
                    <option value="user">کاربر</option>
                    <option value="report">گزارش</option>
                    <option value="general">عمومی</option>
                </select>
            </div>
        </div>

        <!-- Variables Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th wire:click="sortBy('code')" style="cursor: pointer;">
                            کد
                            @if($sortBy === 'code')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان
                            @if($sortBy === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>فیلد جدول</th>
                        <th>نام جدول</th>
                        <th wire:click="sortBy('variable_type')" style="cursor: pointer;">
                            نوع
                            @if($sortBy === 'variable_type')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('sort_order')" style="cursor: pointer;">
                            ترتیب
                            @if($sortBy === 'sort_order')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('is_active')" style="cursor: pointer;">
                            وضعیت
                            @if($sortBy === 'is_active')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variables as $variable)
                        <tr>
                            <td>
                                <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-family: monospace;">
                                    {{ $variable->code }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $variable->title }}</strong>
                                @if($variable->description)
                                    <br><small style="color: #666; font-size: 12px;">{{ Str::limit($variable->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                    {{ $variable->table_field }}
                                </code>
                            </td>
                            <td>
                                @if($variable->table_name)
                                    <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                        {{ $variable->table_name }}
                                    </code>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($variable->variable_type === 'user')
                                    <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        کاربر
                                    </span>
                                @elseif($variable->variable_type === 'report')
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        گزارش
                                    </span>
                                @else
                                    <span style="background: #17a2b8; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        عمومی
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    {{ $variable->sort_order }}
                                </span>
                            </td>
                            <td>
                                @if($variable->is_active)
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
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <button 
                                        wire:click="toggleActive({{ $variable->id }})" 
                                        class="btn" 
                                        style="background: {{ $variable->is_active ? '#ffc107' : '#28a745' }}; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="{{ $variable->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}"
                                    >
                                        <i class="fas fa-{{ $variable->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button 
                                        wire:click="openEditModal({{ $variable->id }})" 
                                        class="btn" 
                                        style="background: #4361ee; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        onclick="confirmDeleteVariable({{ $variable->id }}, '{{ $variable->title }}')" 
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
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                متغیری یافت نشد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($variables->hasPages())
            <div style="margin-top: 20px; display: flex; justify-content: center;">
                {{ $variables->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>{{ $isEditing ? 'ویرایش متغیر' : 'ایجاد متغیر جدید' }}</h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $isEditing ? 'updateVariable' : 'createVariable' }}">
                    <div class="form-group">
                        <label class="form-label">کد متغیر <span style="color: red;">*</span></label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input 
                                type="text" 
                                wire:model="code" 
                                class="form-control" 
                                placeholder="مثال: {0}, {1}, {2}"
                                required
                                style="flex: 1;"
                                pattern="\{[0-9]+\}"
                            >
                            <button 
                                type="button"
                                wire:click="generateNextCode"
                                class="btn" 
                                style="background: #17a2b8; color: white;"
                                title="تولید کد بعدی"
                            >
                                <i class="fas fa-magic"></i>
                                تولید کد
                            </button>
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            فرمت: {0}, {1}, {2} و ...
                        </small>
                        @error('code') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">عنوان <span style="color: red;">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            class="form-control" 
                            placeholder="مثال: نام کاربر"
                            required
                        >
                        @error('title') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">فیلد جدول <span style="color: red;">*</span></label>
                        
                        @if(!empty($availableTableFields))
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px; max-height: 300px; overflow-y: auto;">
                                <strong style="font-size: 13px; display: block; margin-bottom: 10px;">فیلدهای موجود در جداول ثبت شده:</strong>
                                @php
                                    // گروه‌بندی فیلدها بر اساس جدول
                                    $groupedFields = [];
                                    foreach ($availableTableFields as $field) {
                                        $tableKey = $field['table_display_name'] ?? ($field['table_name'] ?? 'سایر');
                                        if (!isset($groupedFields[$tableKey])) {
                                            $groupedFields[$tableKey] = [];
                                        }
                                        $groupedFields[$tableKey][] = $field;
                                    }
                                @endphp
                                @foreach($groupedFields as $tableDisplayName => $fields)
                                    <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #dee2e6;">
                                        <strong style="font-size: 12px; color: #666; display: block; margin-bottom: 8px;">
                                            <i class="fas fa-table"></i> {{ $tableDisplayName }}
                                        </strong>
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            @foreach($fields as $field)
                                                <button 
                                                    type="button"
                                                    wire:click="selectTableField('{{ $field['name'] }}')"
                                                    class="btn" 
                                                    style="background: {{ $selectedTableField === $field['name'] ? '#28a745' : '#4361ee' }}; color: white; padding: 5px 10px; font-size: 12px;"
                                                    title="{{ $field['name'] }} ({{ $field['table_name'] ?? '' }})"
                                                >
                                                    {{ $field['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <input 
                            type="text" 
                            wire:model="table_field" 
                            class="form-control" 
                            placeholder="مثال: fullname, phone, name یا از لیست بالا انتخاب کنید"
                            required
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            نام فیلد در جدول دیتابیس (می‌توانید از لیست بالا انتخاب کنید یا مستقیماً وارد کنید)
                        </small>
                        @error('table_field') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">نام جدول (اختیاری)</label>
                        <input 
                            type="text" 
                            wire:model="table_name" 
                            class="form-control" 
                            placeholder="مثال: residents, reports"
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            نام جدول دیتابیس (در صورت نیاز)
                        </small>
                        @error('table_name') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">نوع متغیر <span style="color: red;">*</span></label>
                        <select wire:model.live="variable_type" class="form-control" required>
                            <option value="user">کاربر</option>
                            <option value="report">گزارش</option>
                            <option value="general">عمومی</option>
                        </select>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            با تغییر نوع، فیلدهای جدول مربوطه نمایش داده می‌شوند
                        </small>
                        @error('variable_type') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
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
                        <label class="form-label">ترتیب نمایش</label>
                        <input 
                            type="number" 
                            wire:model="sort_order" 
                            class="form-control" 
                            min="0"
                            placeholder="0"
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            عدد کمتر = نمایش بالاتر
                        </small>
                        @error('sort_order') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
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

                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">
                            لغو
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEditing ? 'ذخیره تغییرات' : 'ایجاد متغیر' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        function confirmDeleteVariable(id, title) {
            Swal.fire({
                title: 'حذف متغیر',
                html: `آیا مطمئن هستید که می‌خواهید متغیر <strong>"${title}"</strong> را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deleteVariable(id);
                }
            });
        }
    </script>
</div>
