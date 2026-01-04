<div>
    <div class="card">
        <div style="margin-bottom: 20px;">
            <h2><i class="fas fa-cog"></i> تنظیمات سیستم</h2>
            <p style="color: #666; margin-top: 10px;">مدیریت تنظیمات عمومی سیستم</p>
        </div>

        <form wire:submit.prevent="save">
            <!-- تایمر رفرش صفحه -->
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                    <i class="fas fa-clock" style="margin-left: 5px;"></i>
                    تایمر رفرش صفحه (دقیقه) <span style="color: red;">*</span>
                </label>
                <input
                    type="number"
                    wire:model="refresh_interval"
                    class="form-control"
                    placeholder="مثال: 5 (برای غیرفعال کردن: 0)"
                    min="0"
                    max="1440"
                    style="width: 100%; max-width: 400px;"
                >
                <small style="color: #666; margin-top: 5px; display: block;">
                    صفحه به صورت خودکار هر چند دقیقه یکبار رفرش می‌شود تا دیتابیس به‌روزرسانی شود و داده‌های API خوانده شوند. برای غیرفعال کردن رفرش خودکار، مقدار 0 را وارد کنید.
                </small>
                @error('refresh_interval') 
                    <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                        {{ $message }}
                    </span> 
                @enderror
            </div>

            <!-- لینک API -->
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                    <i class="fas fa-link" style="margin-left: 5px;"></i>
                    لینک API اقامت‌گران <span style="color: red;">*</span>
                </label>
                <input
                    type="url"
                    wire:model="api_url"
                    class="form-control"
                    placeholder="مثال: http://atlas2.test/api/residents"
                    style="width: 100%; max-width: 600px;"
                >
                <small style="color: #666; margin-top: 5px; display: block;">
                    آدرس API که داده‌های اقامت‌گران از آن دریافت می‌شود.
                </small>
                @error('api_url') 
                    <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                        {{ $message }}
                    </span> 
                @enderror
            </div>

            <!-- بخش تنظیمات ارسال پیامک -->
            <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <h3 style="margin-bottom: 20px; color: #333;">
                    <i class="fas fa-paper-plane" style="margin-left: 8px; color: #28a745;"></i>
                    تنظیمات ارسال پیامک
                </h3>

                <!-- تاخیر قبل از شروع ارسال -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-clock" style="margin-left: 5px;"></i>
                        تاخیر قبل از شروع ارسال (ثانیه) <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="sms_delay_before_start"
                        class="form-control"
                        placeholder="مثال: 2"
                        min="0"
                        max="60"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        مدت زمان تاخیر قبل از شروع ارسال پیامک‌ها (به ثانیه). پیش‌فرض: 2 ثانیه.
                    </small>
                    @error('sms_delay_before_start') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <!-- تاخیر بین هر پیامک -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-hourglass-half" style="margin-left: 5px;"></i>
                        تاخیر بین هر پیامک (میلی‌ثانیه) <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="sms_delay_between_messages"
                        class="form-control"
                        placeholder="مثال: 200"
                        min="0"
                        max="5000"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        مدت زمان تاخیر بین ارسال هر پیامک (به میلی‌ثانیه). پیش‌فرض: 200 میلی‌ثانیه (0.2 ثانیه).
                    </small>
                    @error('sms_delay_between_messages') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>
            </div>

            <!-- بخش تنظیمات گزارش تخلفات -->
            <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                <h3 style="margin-bottom: 20px; color: #333;">
                    <i class="fas fa-exclamation-triangle" style="margin-left: 8px; color: #dc3545;"></i>
                    تنظیمات گزارش تخلفات
                </h3>

                <!-- تعداد گزارش یکسان -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-repeat" style="margin-left: 5px;"></i>
                        تعداد گزارش یکسان برای نمایش <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="repeat_violation"
                        class="form-control"
                        placeholder="مثال: 3"
                        min="1"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        تعداد گزارش یکسان که باید برای نمایش در "اقامت‌گران با تخلف‌های تکرارای یکسان" استفاده شود.
                    </small>
                    @error('repeat_violation') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <!-- تعداد گزارش برای نمایش اقامت‌گران برتر -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-chart-line" style="margin-left: 5px;"></i>
                        تعداد گزارش برای نمایش اقامت‌گران برتر <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="count_violation"
                        class="form-control"
                        placeholder="مثال: 5"
                        min="1"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        تعداد گزارش که باید برای نمایش در "اقامت‌گران با تعداد گزارش بالا" استفاده شود.
                    </small>
                    @error('count_violation') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>

                <!-- مجموع نمرات منفی برای نمایش اقامت‌گران برتر -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                        <i class="fas fa-star" style="margin-left: 5px;"></i>
                        مجموع نمرات منفی برای نمایش اقامت‌گران برتر <span style="color: red;">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model="max_violation"
                        class="form-control"
                        placeholder="مثال: 10"
                        min="1"
                        style="width: 100%; max-width: 400px;"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        مجموع نمرات منفی که باید برای نمایش در "اقامت‌گران برتر (بیشترین گزارش)" استفاده شود.
                    </small>
                    @error('max_violation') 
                        <span style="color: red; font-size: 12px; margin-top: 5px; display: block;">
                            {{ $message }}
                        </span> 
                    @enderror
                </div>
            </div>

            <!-- دکمه ذخیره -->
            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>

    <!-- نمایش تنظیمات فعلی -->
    <div class="card" style="margin-top: 20px;">
        <h3 style="margin-bottom: 15px;">
            <i class="fas fa-info-circle"></i>
            تنظیمات فعلی
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">تایمر رفرش صفحه</div>
                <div style="font-size: 18px; font-weight: 600; color: var(--primary-color);">
                    {{ $refresh_interval }} دقیقه
                </div>
            </div>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">لینک API</div>
                <div style="font-size: 14px; font-weight: 500; color: #333; word-break: break-all;">
                    {{ $api_url }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // جلوگیری از reload تکراری با استفاده از flag
    if (!window.settingsUpdateReloaded) {
        window.settingsUpdateReloaded = false;
        
        document.addEventListener('livewire:init', () => {
            // راه‌اندازی مجدد تایمر بعد از ذخیره تنظیمات
            Livewire.on('settings-updated', () => {
                // جلوگیری از reload تکراری
                if (window.settingsUpdateReloaded) {
                    console.log('⚠️ جلوگیری از reload تکراری');
                    return;
                }
                
                window.settingsUpdateReloaded = true;
                
                // پاک کردن زمان شروع تایمر برای شروع مجدد
                localStorage.removeItem('timerStartTime');
                localStorage.removeItem('refreshInterval');
                
                console.log('⚙️ تنظیمات به‌روزرسانی شد، در حال reload...');
                
                setTimeout(() => {
                    window.settingsUpdateReloaded = false; // Reset flag بعد از reload
                    location.reload();
                }, 1000);
            });
        });
    }
</script>
