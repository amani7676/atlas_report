<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-cog"></i> مدیریت ثابت‌ها</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد ثابت جدید
            </button>
        </div>

        <!-- Search -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی ثابت (کلید، مقدار، توضیحات)..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- Constants Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>کلید</th>
                        <th>مقدار</th>
                        <th>نوع داده</th>
                        <th>توضیحات</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($constants as $constant)
                        <tr>
                            <td>
                                <strong style="color: var(--primary-color);">{{ $constant->key }}</strong>
                            </td>
                            <td>
                                @if($constant->type === 'date')
                                    <span style="color: #666;">{{ \Carbon\Carbon::parse($constant->value)->format('Y/m/d') }}</span>
                                @elseif($constant->type === 'number')
                                    <span style="color: #10b981; font-weight: 500;">{{ number_format($constant->value, 0) }}</span>
                                @else
                                    <span>{{ Str::limit($constant->value, 50) }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeLabels = [
                                        'string' => ['label' => 'رشته', 'color' => '#4361ee'],
                                        'number' => ['label' => 'عدد', 'color' => '#10b981'],
                                        'date' => ['label' => 'تاریخ', 'color' => '#f59e0b'],
                                        'enum' => ['label' => 'Enum', 'color' => '#8b5cf6'],
                                    ];
                                    $typeInfo = $typeLabels[$constant->type] ?? ['label' => $constant->type, 'color' => '#666'];
                                @endphp
                                <span style="background: {{ $typeInfo['color'] }}15; color: {{ $typeInfo['color'] }}; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                    {{ $typeInfo['label'] }}
                                </span>
                            </td>
                            <td>
                                @if($constant->description)
                                    <p style="color: #666; font-size: 14px; margin: 0;">
                                        {{ Str::limit($constant->description, 50) }}
                                    </p>
                                @else
                                    <span style="color: #999; font-style: italic;">بدون توضیح</span>
                                @endif
                            </td>
                            <td>
                                <span style="color: #666; font-size: 14px;">
                                    {{ \Carbon\Carbon::parse($constant->created_at)->format('Y/m/d H:i') }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button 
                                        wire:click="openModal({{ $constant->id }})" 
                                        class="btn btn-sm btn-primary"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        wire:click="delete({{ $constant->id }})" 
                                        wire:confirm="آیا مطمئن هستید که می‌خواهید این ثابت را حذف کنید؟"
                                        class="btn btn-sm btn-danger"
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
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                <p>هیچ ثابتی یافت نشد.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($constants->hasPages())
            <div style="margin-top: 20px;">
                {{ $constants->links() }}
            </div>
        @endif
    </div>

    <!-- Modal برای ایجاد/ویرایش -->
    @if($showModal)
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;" wire:click="closeModal">
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">
                        <i class="fas fa-cog"></i>
                        {{ $editingId ? 'ویرایش ثابت' : 'ایجاد ثابت جدید' }}
                    </h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <!-- کلید -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            کلید <span style="color: red;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="key"
                            class="form-control"
                            placeholder="مثال: max_users"
                            style="width: 100%;"
                        >
                        @error('key') <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
                    </div>

                    <!-- نوع داده -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            نوع داده <span style="color: red;">*</span>
                        </label>
                        <select wire:model.live="type" class="form-control" style="width: 100%;">
                            <option value="string">رشته (String)</option>
                            <option value="number">عدد (Number)</option>
                            <option value="date">تاریخ (Date)</option>
                            <option value="enum">Enum</option>
                        </select>
                        @error('type') <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
                    </div>

                    <!-- مقادیر Enum (فقط برای نوع enum) -->
                    @if($type === 'enum')
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                                مقادیر Enum <span style="color: red;">*</span>
                                <small style="color: #666; font-weight: normal;">(با کاما جدا کنید، مثال: فعال,غیرفعال,در انتظار)</small>
                            </label>
                            <input
                                type="text"
                                wire:model="enum_values"
                                class="form-control"
                                placeholder="فعال,غیرفعال,در انتظار"
                                style="width: 100%;"
                            >
                            @error('enum_values') <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- مقدار -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            مقدار <span style="color: red;">*</span>
                        </label>
                        @if($type === 'date')
                            <input
                                type="date"
                                wire:model="value"
                                class="form-control"
                                style="width: 100%;"
                            >
                        @elseif($type === 'enum')
                            <select wire:model="value" class="form-control" style="width: 100%;">
                                <option value="">انتخاب کنید...</option>
                                @if($enum_values)
                                    @foreach(explode(',', $enum_values) as $enumValue)
                                        <option value="{{ trim($enumValue) }}">{{ trim($enumValue) }}</option>
                                    @endforeach
                                @endif
                            </select>
                        @else
                            <input
                                type="{{ $type === 'number' ? 'number' : 'text' }}"
                                wire:model="value"
                                class="form-control"
                                placeholder="{{ $type === 'number' ? 'مثال: 100' : 'مثال: مقدار ثابت' }}"
                                style="width: 100%;"
                            >
                        @endif
                        @error('value') <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
                    </div>

                    <!-- توضیحات -->
                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            توضیحات
                        </label>
                        <textarea
                            wire:model="description"
                            class="form-control"
                            rows="3"
                            placeholder="توضیحات اختیاری..."
                            style="width: 100%; resize: vertical;"
                        ></textarea>
                        @error('description') <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
                    </div>

                    <!-- دکمه‌ها -->
                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">
                            <i class="fas fa-times"></i> لغو
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ذخیره
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>



