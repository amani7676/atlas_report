<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-table"></i> مدیریت نام گذاری جداول</h2>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                ایجاد نام جدول جدید
            </button>
        </div>

        <!-- Search -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی نام جدول..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
        </div>

        <!-- Table Names Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>نام (برای نمایش)</th>
                        <th>نام جدول در دیتابیس</th>
                        <th>قابلیت نمایش</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tableNames as $tableName)
                        <tr>
                            <td>
                                <strong style="color: var(--primary-color);">{{ $tableName->name }}</strong>
                            </td>
                            <td>
                                <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; color: #e83e8c;">{{ $tableName->table_name }}</code>
                            </td>
                            <td>
                                @if($tableName->is_visible)
                                    <span style="background: #10b98115; color: #10b981; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        <i class="fas fa-check-circle"></i> نمایش داده می‌شود
                                    </span>
                                @else
                                    <span style="background: #ef444415; color: #ef4444; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        <i class="fas fa-times-circle"></i> نمایش داده نمی‌شود
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="color: #666; font-size: 14px;">
                                    {{ \Carbon\Carbon::parse($tableName->created_at)->format('Y/m/d H:i') }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <button 
                                        wire:click="openModal({{ $tableName->id }})" 
                                        class="btn btn-sm btn-primary"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        wire:click="delete({{ $tableName->id }})" 
                                        wire:confirm="آیا مطمئن هستید که می‌خواهید این نام جدول را حذف کنید؟"
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
                            <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                <p>هیچ نام جدولی یافت نشد.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($tableNames->hasPages())
            <div style="margin-top: 20px;">
                {{ $tableNames->links() }}
            </div>
        @endif
    </div>

    <!-- Modal برای ایجاد/ویرایش -->
    @if($showModal)
        <div class="modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;" wire:click="closeModal">
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">
                        <i class="fas fa-table"></i>
                        {{ $editingId ? 'ویرایش نام جدول' : 'ایجاد نام جدول جدید' }}
                    </h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <!-- نام (برای نمایش) -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            نام (برای نمایش) <span style="color: red;">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="name"
                            class="form-control"
                            placeholder="مثال: اقامت‌گران"
                            style="width: 100%;"
                        >
                        @error('name') <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
                    </div>

                    <!-- نام جدول در دیتابیس -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                            نام جدول در دیتابیس <span style="color: red;">*</span>
                        </label>
                        @if($editingId)
                            <!-- در حالت ویرایش، input text نمایش بده -->
                            <input
                                type="text"
                                wire:model="table_name"
                                class="form-control"
                                placeholder="مثال: residents"
                                style="width: 100%;"
                                readonly
                            >
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                نام جدول در حالت ویرایش قابل تغییر نیست
                            </small>
                        @else
                            <!-- در حالت ایجاد جدید، dropdown نمایش بده -->
                            <select
                                wire:model.live="table_name"
                                class="form-control"
                                style="width: 100%;"
                            >
                                <option value="">انتخاب جدول از لیست...</option>
                                @foreach($this->availableTables as $table)
                                    <option value="{{ $table }}">{{ $table }}</option>
                                @endforeach
                            </select>
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                جدول مورد نظر را از لیست انتخاب کنید. فقط جداولی که قبلاً ثبت نشده‌اند نمایش داده می‌شوند.
                            </small>
                        @endif
                        @error('table_name') <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
                    </div>

                    <!-- قابلیت نمایش -->
                    <div style="margin-bottom: 25px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input
                                type="checkbox"
                                wire:model="is_visible"
                                style="width: 18px; height: 18px; cursor: pointer;"
                            >
                            <span style="font-weight: 500; color: #333;">
                                قابلیت نمایش
                            </span>
                        </label>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block; margin-right: 28px;">
                            در صورت فعال بودن، این جدول در لیست جداول قابل انتخاب نمایش داده می‌شود.
                        </small>
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
