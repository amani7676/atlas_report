<div>
    <div class="card">
        <div style="margin-bottom: 20px;">
            <h2><i class="fas fa-hand-sparkles"></i> مدیریت پیام‌های خوش‌آمدگویی</h2>
            <p style="color: #666; margin-top: 10px;">تنظیم و مدیریت پیام‌های خوش‌آمدگویی برای اقامت‌گران جدید</p>
        </div>

        <!-- تنظیمات -->
        <div class="card" style="margin-bottom: 30px; background: #f8f9fa;">
            <h3 style="margin-bottom: 20px; color: #333;">
                <i class="fas fa-cog" style="margin-left: 8px;"></i>
                تنظیمات پیام خوش‌آمدگویی
            </h3>

            <form wire:submit.prevent="saveSettings">
                <!-- انتخاب الگو -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-file-alt" style="margin-left: 5px;"></i>
                        الگوی پیام خوش‌آمدگویی <span style="color: red;">*</span>
                    </label>
                    <select
                        wire:model="welcome_pattern_id"
                        class="form-control"
                        style="width: 100%; max-width: 500px;"
                    >
                        <option value="">-- انتخاب الگو --</option>
                        @foreach($patterns as $p)
                            <option value="{{ $p->id }}">{{ $p->title }} (کد: {{ $p->pattern_code }})</option>
                        @endforeach
                    </select>
                    @if($pattern)
                        <small style="color: #666; margin-top: 5px; display: block;">
                            متن الگو: {{ Str::limit($pattern->text, 100) }}
                        </small>
                    @endif
                    @error('welcome_pattern_id') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <!-- تاریخ و زمان شروع -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-calendar-alt" style="margin-left: 5px;"></i>
                        تاریخ و زمان شروع ارسال <span style="color: red;">*</span>
                    </label>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <input
                            type="date"
                            wire:model="welcome_start_date"
                            class="form-control"
                            style="width: 200px;"
                        >
                        <input
                            type="time"
                            wire:model="welcome_start_time"
                            class="form-control"
                            step="1"
                            style="width: 150px;"
                        >
                    </div>
                    <small style="color: #666; margin-top: 5px; display: block;">
                        از این تاریخ به بعد، برای اقامت‌گرانی که ایجاد می‌شوند، پیام خوش‌آمدگویی ارسال می‌شود.
                    </small>
                    @error('welcome_start_date') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                    @error('welcome_start_time') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                    <i class="fas fa-save"></i> ذخیره تنظیمات
                </button>

                @if($settings->welcome_pattern_id && $settings->welcome_start_datetime)
                    <button type="button" wire:click="sendWelcomeMessages" class="btn btn-success" style="margin-top: 10px; margin-right: 10px;">
                        <i class="fas fa-paper-plane"></i> ارسال دستی پیام‌های خوش‌آمدگویی
                    </button>
                @endif
            </form>
        </div>

        <!-- لیست پیام‌های ارسال شده -->
        <div class="card">
            <h3 style="margin-bottom: 20px; color: #333;">
                <i class="fas fa-list" style="margin-left: 8px;"></i>
                لیست پیام‌های ارسال شده
            </h3>

            <!-- فیلترها -->
            <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">نام</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search_name"
                            class="form-control"
                            placeholder="جستجو بر اساس نام..."
                        >
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">شماره تلفن</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search_phone"
                            class="form-control"
                            placeholder="جستجو بر اساس شماره..."
                        >
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">واحد</label>
                        <select wire:model.live="search_unit" class="form-control">
                            <option value="">همه واحدها</option>
                            @foreach($unitsList as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">اتاق</label>
                        <select wire:model.live="search_room" class="form-control">
                            <option value="">همه اتاق‌ها</option>
                            @foreach($roomsList as $room)
                                <option value="{{ $room }}">{{ $room }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">تخت</label>
                        <select wire:model.live="search_bed" class="form-control">
                            <option value="">همه تخت‌ها</option>
                            @foreach($bedsList as $bed)
                                <option value="{{ $bed }}">{{ $bed }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- جدول -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>شماره تلفن</th>
                            <th>واحد</th>
                            <th>اتاق</th>
                            <th>تخت</th>
                            <th>وضعیت ارسال</th>
                            <th>زمان ارسال / ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr>
                                <td>{{ $message->resident_name }}</td>
                                <td>{{ $message->phone }}</td>
                                <td>{{ $message->resident->unit_name ?? '-' }}</td>
                                <td>{{ $message->resident->room_name ?? '-' }}</td>
                                <td>{{ $message->resident->bed_name ?? '-' }}</td>
                                <td>
                                    @if($message->status === 'sent')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> ارسال شده
                                        </span>
                                    @elseif($message->status === 'pending')
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> در انتظار
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle"></i> خطا
                                            @if($message->error_message)
                                                <br><small style="font-size: 10px;">{{ Str::limit($message->error_message, 30) }}</small>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($message->sent_at)
                                        <span style="color: #28a745; font-weight: 500;">
                                            <i class="fas fa-check-circle"></i> {{ $message->sent_at->format('Y/m/d H:i:s') }}
                                        </span>
                                    @elseif($message->status === 'pending')
                                        <span style="color: #ffc107;">
                                            <i class="fas fa-clock"></i> {{ $message->created_at->format('Y/m/d H:i:s') }}
                                            <br><small style="font-size: 11px; color: #666;">در انتظار ارسال</small>
                                        </span>
                                    @else
                                        <span style="color: #dc3545;">
                                            <i class="fas fa-times-circle"></i> {{ $message->created_at->format('Y/m/d H:i:s') }}
                                            <br><small style="font-size: 11px; color: #dc3545;">ارسال نشده</small>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: #666;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                    هیچ پیامی یافت نشد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="margin-top: 20px;">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
</div>
