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
                        <th>کد الگو</th>
                        <th>نام جدول</th>
                        <th>الگوی مرتبط</th>
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
                        <th>وضعیت</th>
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
                                @if($variable->pattern_code)
                                    <code style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 3px; font-size: 12px; border: 1px solid #bbdefb;">
                                        {{ $variable->pattern_code }}
                                    </code>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
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
                                @php
                                    $patternConnections = DB::table('pattern_pattern_variables')
                                        ->where('pattern_variable_id', $variable->id)
                                        ->get();
                                @endphp
                                @if($patternConnections->isNotEmpty())
                                    <div style="max-height: 80px; overflow-y: auto;">
                                        @foreach($patternConnections as $connection)
                                            @php
                                                $pattern = \App\Models\Pattern::find($connection->pattern_id);
                                            @endphp
                                            @if($pattern)
                                                <div style="margin-bottom: 3px;">
                                                    <span style="background: #6f42c1; color: white; padding: 1px 4px; border-radius: 2px; font-size: 10px;">
                                                        {{ $pattern->title }}
                                                    </span>
                                                    <small style="color: #666; font-size: 9px;">
                                                        ({{ $connection->variable_code }})
                                                    </small>
                                                </div>
                                            @else
                                                <div style="color: #dc3545; font-size: 10px;">الگو حذف شده</div>
                                            @endif
                                        @endforeach
                                    </div>
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
                            <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
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
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 900px; max-height: 95vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>{{ $isEditing ? 'ویرایش متغیر' : 'ایجاد متغیر جدید' }}</h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $isEditing ? 'updateVariable' : 'createVariable' }}">
                    
                    <!-- 1. لیست فیلدهای جداول -->
                    <div class="form-group">
                        <label class="form-label">لیست فیلدهای جداول</label>
                        
                        @if(!empty($availableTableFields))
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px; max-height: 300px; overflow-y: auto;">
                                <strong style="font-size: 13px; display: block; margin-bottom: 15px;">فیلدهای موجود در جداول ثبت شده:</strong>
                                @php
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
                                    <div style="margin-bottom: 20px; padding: 15px; background: white; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <h6 style="margin-bottom: 10px; color: #495057; font-size: 14px;">
                                            <i class="fas fa-table"></i> {{ $tableDisplayName }}
                                            <small style="color: #6c757d; margin-right: 10px;">({{ $fields[0]['table_name'] ?? '' }})</small>
                                        </h6>
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            @foreach($fields as $field)
                                                <button 
                                                    type="button"
                                                    wire:click="selectTableField('{{ $field['name'] }}')"
                                                    class="btn" 
                                                    style="background: {{ $selectedTableField === $field['name'] ? '#28a745' : '#4361ee' }}; color: white; padding: 5px 10px; font-size: 12px; border-radius: 4px;"
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
                            wire:model="pattern_code" 
                            class="form-control" 
                            placeholder="کد الگو (اگر الگو انتخاب شود، به صورت خودکار تنظیم می‌شود)"
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            این فیلد اختیاری است. فیلدهای اصلی را در بخش تخصیص متغیرها به الگوها انتخاب کنید.
                        </small>
                        @error('pattern_code') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <!-- 2. عنوان متغیر -->
                    <div class="form-group">
                        <label class="form-label">عنوان متغیر <span style="color: red;">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            class="form-control" 
                            placeholder="مثال: نام کامل کاربر (اگر الگو انتخاب شود، نام الگو به صورت خودکار وارد می‌شود)"
                            required
                        >
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            @if(!empty($selectedPatterns))
                                @if(count($selectedPatterns) === 1)
                                    عنوان با نام الگوی انتخاب شده یکی است. می‌توانید آن را تغییر دهید.
                                @else
                                    چند الگو انتخاب شده است، لطفاً عنوان مناسب وارد کنید.
                                @endif
                            @else
                                عنوان را وارد کنید یا یک الگو انتخاب کنید تا عنوان به صورت خودکار تنظیم شود.
                            @endif
                        </small>
                        @error('title') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <!-- 3. لیست کشویی الگوهای پیامی -->
                    <div class="form-group">
                        <label class="form-label">الگوهای پیامی <span style="color: red;">*</span></label>
                        <select wire:model.live="selectedPatterns" class="form-control" multiple size="4">
                            @foreach($availablePatterns as $id => $title)
                                <option value="{{ $id }}" {{ in_array($id, $selectedPatterns) ? 'selected' : '' }}>
                                    {{ $title }}
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            انتخاب حداقل یک الگو الزامی است. می‌توانید چند الگو را با نگه داشتن Ctrl انتخاب کنید.
                        </small>
                        @error('selectedPatterns') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <!-- 4. نمایش متن الگو و 5. کدهای متغیر و 6. تخصیص فیلدها -->
                    @if(!empty($selectedPatterns))
                        <div class="form-group">
                            <label class="form-label">تخصیص متغیرها به الگوها</label>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                                <!-- نمایش متن الگوها -->
                                @foreach($selectedPatterns as $patternId)
                                    @php
                                        $pattern = \App\Models\Pattern::find($patternId);
                                        if(!$pattern) continue;
                                    @endphp
                                    <div style="margin-bottom: 15px; padding: 10px; background: white; border-radius: 4px; border: 1px solid #dee2e6;">
                                        <h6 style="margin-bottom: 5px; color: #495057; font-size: 13px;">
                                            <i class="fas fa-file-alt"></i> {{ $pattern->title }}
                                            @if($pattern->pattern_code) <small>({{ $pattern->pattern_code }})</small> @endif
                                        </h6>
                                        <div style="padding: 8px; background: #e9ecef; border-radius: 4px; font-family: monospace; font-size: 12px;">
                                            {{ $patternTexts[$patternId] ?? '' }}
                                        </div>
                                    </div>
                                @endforeach
                                
                                <!-- لیست یکپارچه کدهای متغیر و تخصیص فیلدها -->
                                @php
                                    $allVariableCodes = [];
                                    foreach($selectedPatterns as $patternId) {
                                        if(isset($patternVariables[$patternId]) && !empty($patternVariables[$patternId])) {
                                            foreach($patternVariables[$patternId] as $variableCode) {
                                                $allVariableCodes[$variableCode] = $variableCode;
                                            }
                                        }
                                    }
                                    $allVariableCodes = array_values($allVariableCodes);
                                @endphp
                                
                                @if(!empty($allVariableCodes))
                                    <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 6px; border: 1px solid #dee2e6;">
                                        <h6 style="margin-bottom: 10px; color: #495057;">
                                            <i class="fas fa-code"></i> تخصیص فیلدها به کدهای متغیر
                                        </h6>
                                        <div style="margin-bottom: 10px;">
                                            <strong style="font-size: 12px; color: #666;">کدهای متغیرهای موجود:</strong>
                                            <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                                @foreach($allVariableCodes as $variableCode)
                                                    <span style="background: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">
                                                        {{ $variableCode }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        @foreach($allVariableCodes as $variableCode)
                                            <div style="margin-bottom: 10px;">
                                                <label style="font-size: 12px; font-weight: bold; color: #495057;">
                                                    {{ $variableCode }} → فیلد مربوطه:
                                                </label>
                                                <select 
                                                    wire:model="variableAssignments.{{ $variableCode }}" 
                                                    class="form-control form-control-sm"
                                                    style="font-size: 12px;"
                                                >
                                                    <option value="">انتخاب فیلد...</option>
                                                    @foreach($availableTableFields as $field)
                                                        <option value="{{ $field['name'] }}">
                                                            {{ $field['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('variableAssignments.' . $variableCode) 
                                                    <span style="color: red; font-size: 11px;">{{ $message }}</span> 
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p style="color: #6c757d; font-size: 12px; font-style: italic; margin-top: 10px;">
                                        هیچ کد متغیری ({0}, {1}, ...) در الگوهای انتخاب شده وجود ندارد.
                                    </p>
                                @endif
                            </div>
                            @error('variableAssignments') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- فیلدهای دیگر -->
                    <div class="form-group">
                        <label class="form-label">نوع متغیر <span style="color: red;">*</span></label>
                        <select wire:model.live="variable_type" class="form-control" required>
                            <option value="user">کاربر</option>
                            <option value="report">گزارش</option>
                            <option value="general">عمومی</option>
                        </select>
                        @error('variable_type') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">نام جدول (اختیاری)</label>
                        <input 
                            type="text" 
                            wire:model="table_name" 
                            class="form-control" 
                            placeholder="مثال: residents, reports"
                        >
                        @error('table_name') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
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
                        @error('sort_order') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                wire:model="is_active" 
                                class="form-check-input" 
                                id="is_active"
                            >
                            <label class="form-check-label" for="is_active">
                                فعال
                            </label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            {{ $isEditing ? 'به‌روزرسانی' : 'ایجاد' }}
                        </button>
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
function confirmDeleteVariable(id, title) {
    if (confirm('آیا از حذف متغیر "' + title + '" اطمینان دارید؟')) {
        @this.call('deleteVariable', id);
    }
}
</script>
