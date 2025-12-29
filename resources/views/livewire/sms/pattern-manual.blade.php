<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #10b981;"><i class="fas fa-file-code"></i> ارسال SMS الگویی دستی</h2>
            <button 
                wire:click="syncResidents" 
                wire:loading.attr="disabled"
                class="btn btn-primary"
                style="display: flex; align-items: center; gap: 8px;"
                @if($syncing) disabled @endif
            >
                <i class="fas fa-sync-alt" wire:loading.class="fa-spin"></i>
                <span wire:loading.remove>همگام‌سازی از API</span>
                <span wire:loading>در حال همگام‌سازی...</span>
            </button>
        </div>
        
        @if($loading)
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #10b981;"></i>
                <p>در حال بارگذاری...</p>
            </div>
        @elseif($error)
            <div style="background: #fff3cd; padding: 15px; border-radius: 6px; margin-bottom: 20px; color: #856404;">
                <i class="fas fa-exclamation-triangle"></i> {{ $error }}
            </div>
        @else
            {{-- فرم ارسال SMS (نمایش در صفحه اصلی) --}}
            @if($selectedResident)
                <div id="sms-form-section" class="card" style="margin-bottom: 30px; border: 2px solid #10b981; background: #f0fdf4;">
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 15px; border-radius: 6px 6px 0 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; color: white;">
                                <i class="fas fa-file-code"></i> ارسال SMS الگویی به {{ $selectedResident['name'] }}
                            </h3>
                            <button wire:click="clearSelection" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 20px; cursor: pointer; padding: 5px 10px; border-radius: 5px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div style="padding: 25px;">
                        <form wire:submit.prevent="submit">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">گزارش *</label>
                                <select wire:model.live="selectedReport" class="form-control" required>
                                    <option value="">انتخاب گزارش</option>
                                    @foreach($reports as $report)
                                        <option value="{{ $report->id }}">{{ $report->title }} ({{ $report->category->name }})</option>
                                    @endforeach
                                </select>
                                @error('selectedReport') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                                
                                @if($selectedReport && $reportPatterns && $reportPatterns->count() > 0)
                                    <div style="background: #d1fae5; padding: 10px; border-radius: 6px; margin-top: 10px; border-right: 3px solid #10b981;">
                                        <strong style="display: block; margin-bottom: 8px;">الگوهای مرتبط با این گزارش:</strong>
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                            @foreach($reportPatterns as $pattern)
                                                <span 
                                                    style="background: #10b981; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;"
                                                >
                                                    {{ $pattern->title }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">الگوی پیامک *</label>
                                <select wire:model.live="selectedPattern" class="form-control" required>
                                    <option value="">انتخاب الگو</option>
                                    @if($selectedReport && $reportPatterns && $reportPatterns->count() > 0)
                                        <optgroup label="الگوهای مرتبط با گزارش">
                                            @foreach($reportPatterns as $pattern)
                                                <option value="{{ $pattern->id }}">
                                                    {{ $pattern->title }} 
                                                    @if($pattern->pattern_code)
                                                        (کد: {{ $pattern->pattern_code }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    <optgroup label="همه الگوها">
                                        @foreach($patterns as $pattern)
                                            <option value="{{ $pattern->id }}">
                                                {{ $pattern->title }} 
                                                @if($pattern->pattern_code)
                                                    (کد: {{ $pattern->pattern_code }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                @if($selectedPattern)
                                    @php
                                        $selectedPatternObj = $patterns->firstWhere('id', $selectedPattern);
                                    @endphp
                                    @if($selectedPatternObj)
                                        <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; margin-top: 10px;">
                                            <strong>متن الگو:</strong>
                                            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">{{ $selectedPatternObj->text }}</p>
                                        </div>
                                        
                                        {{-- پیش‌نمایش پیام با متغیرهای جایگزین شده --}}
                                        @if($previewMessage)
                                            <div style="background: #d1fae5; padding: 15px; border-radius: 6px; margin-top: 10px; border-right: 3px solid #10b981;">
                                                <strong style="color: #059669; display: block; margin-bottom: 10px;">
                                                    <i class="fas fa-eye"></i> پیش‌نمایش پیام ارسالی:
                                                </strong>
                                                <div style="background: white; padding: 12px; border-radius: 5px; border: 1px solid #dee2e6; font-size: 14px; line-height: 1.8;">
                                                    {!! $previewMessage !!}
                                                </div>
                                                
                                                @if(count($previewVariables) > 0)
                                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                                                        <strong style="color: #666; font-size: 12px; display: block; margin-bottom: 5px;">متغیرهای ارسالی به API:</strong>
                                                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                                            @foreach($previewVariables as $index => $variable)
                                                                <span style="background: #a7f3d0; color: #059669; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-family: monospace;">
                                                                    { {{ $index }} }: {{ $variable }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                        <div style="margin-top: 8px; padding: 8px; background: #f8f9fa; border-radius: 3px;">
                                                            <strong style="color: #666; font-size: 11px;">رشته ارسالی به API (با جداکننده ;):</strong>
                                                            <code style="display: block; margin-top: 5px; padding: 5px; background: white; border-radius: 3px; font-size: 11px; direction: ltr; text-align: left; word-break: break-all;">
                                                                {{ implode(';', $previewVariables) }}
                                                            </code>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                @endif
                                @error('selectedPattern') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">یادداشت (اختیاری)</label>
                                <textarea wire:model="notes" class="form-control" rows="3" placeholder="یادداشت..."></textarea>
                            </div>

                            <!-- شماره فرستنده -->
                            <div class="form-group" style="margin-bottom: 20px; padding: 12px; background: #fef3c7; border-radius: 6px; border-right: 3px solid #f59e0b;">
                                <label class="form-label" style="color: #92400e; font-weight: 600;">
                                    <i class="fas fa-phone-alt"></i> شماره فرستنده <span class="text-danger">*</span>
                                </label>
                                @if(count($availableSenderNumbers) > 0)
                                    <select wire:model.live="selectedSenderNumberId" class="form-control" style="margin-top: 8px;">
                                        @foreach($availableSenderNumbers as $sender)
                                            <option value="{{ $sender->id }}">
                                                {{ $sender->title }} ({{ $sender->number }})
                                                @if($sender->api_key)
                                                    - دارای API Key
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                                        <span style="font-size: 16px; font-weight: bold; color: #1f2937; font-family: monospace;">{{ $senderNumber }}</span>
                                    </div>
                                @else
                                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                                        <span style="font-size: 18px; font-weight: bold; color: #1f2937; font-family: monospace;">{{ $senderNumber }}</span>
                                        @if($senderNumber === 'تنظیم نشده')
                                            <span style="font-size: 11px; color: #dc2626; background: #fee2e2; padding: 4px 8px; border-radius: 4px;">
                                                (لطفاً در فایل .env تنظیم کنید)
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                <p style="margin-top: 8px; font-size: 11px; color: #78716c;">
                                    @if(count($availableSenderNumbers) > 0)
                                        شماره فرستنده را از لیست انتخاب کنید. برای مدیریت شماره‌ها به 
                                        <a href="/sender-numbers" target="_blank" style="color: #10b981; text-decoration: underline;">صفحه مدیریت شماره‌های فرستنده</a> بروید.
                                    @else
                                        این شماره برای ارسال پیامک الگویی استفاده می‌شود. برای تغییر، متغیر <code style="background: #f3f4f6; padding: 2px 4px; border-radius: 3px;">MELIPAYAMAK_PATTERN_FROM</code> را در فایل <code style="background: #f3f4f6; padding: 2px 4px; border-radius: 3px;">.env</code> تنظیم کنید یا از 
                                        <a href="/sender-numbers" target="_blank" style="color: #10b981; text-decoration: underline;">صفحه مدیریت شماره‌های فرستنده</a> استفاده کنید.
                                    @endif
                                </p>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                                <button type="button" wire:click="clearSelection" class="btn" style="background: #6c757d; color: white;">لغو</button>
                                <button type="submit" class="btn" style="background: #10b981; color: white; border: none;">
                                    <i class="fas fa-paper-plane"></i>
                                    ثبت گزارش و ارسال SMS الگویی
                                </button>
                            </div>
                        </form>

                        {{-- نمایش نتیجه (مشابه PatternTest) --}}
                        @if($showResult && $result)
                            <div data-result-section class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                                <h3 class="text-xl font-bold mb-4 text-gray-800">پاسخ API ملی پیامک</h3>
                                
                                <div class="space-y-4">
                                    <!-- وضعیت -->
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-700">وضعیت:</span>
                                        @if($result['success'] ?? false)
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                                ✅ موفق
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                                ❌ ناموفق
                                            </span>
                                        @endif
                                    </div>

                                    <!-- پیام -->
                                    @if(isset($result['message']))
                                        <div>
                                            <span class="font-medium text-gray-700">پیام:</span>
                                            <p class="mt-1 text-gray-800">{{ $result['message'] }}</p>
                                        </div>
                                    @endif

                                    <!-- RecId -->
                                    @if(isset($result['rec_id']))
                                        <div>
                                            <span class="font-medium text-gray-700">RecId:</span>
                                            <p class="mt-1 text-gray-800 font-mono">{{ $result['rec_id'] }}</p>
                                        </div>
                                    @endif

                                    <!-- کد پاسخ -->
                                    @if(isset($result['response_code']))
                                        <div>
                                            <span class="font-medium text-gray-700">کد پاسخ:</span>
                                            <p class="mt-1 text-gray-800 font-mono">{{ $result['response_code'] }}</p>
                                        </div>
                                    @endif

                                    <!-- پاسخ خام -->
                                    @if(isset($result['raw_response']))
                                        <div>
                                            <span class="font-medium text-gray-700">پاسخ خام API:</span>
                                            <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                                <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words">{{ $result['raw_response'] }}</pre>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- پاسخ API (JSON) -->
                                    @if(isset($result['api_response']))
                                        <div>
                                            <span class="font-medium text-gray-700">پاسخ API (JSON):</span>
                                            <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                                <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words">{{ is_array($result['api_response']) ? json_encode($result['api_response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $result['api_response'] }}</pre>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- خطا -->
                                    @if(isset($result['error']))
                                        <div>
                                            <span class="font-medium text-red-700">خطا:</span>
                                            <p class="mt-1 text-red-800">{{ $result['error'] }}</p>
                                        </div>
                                    @endif

                                    <!-- وضعیت ثبت گزارش -->
                                    @if(isset($result['report_created']))
                                        <div style="margin-top: 15px; padding: 12px; background: {{ $result['report_created'] ? '#d1fae5' : '#fee2e2' }}; border-radius: 6px; border-right: 3px solid {{ $result['report_created'] ? '#10b981' : '#dc2626' }};">
                                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                                @if($result['report_created'])
                                                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 18px;"></i>
                                                    <strong style="color: #059669; font-size: 14px;">گزارش ثبت شد</strong>
                                                    @if(isset($result['resident_report_id']))
                                                        <span style="font-size: 11px; color: #666; margin-right: 5px;">
                                                            (ID: {{ $result['resident_report_id'] }})
                                                        </span>
                                                    @endif
                                                @else
                                                    <i class="fas fa-times-circle" style="color: #dc2626; font-size: 18px;"></i>
                                                    <strong style="color: #dc2626; font-size: 14px;">گزارش ثبت نشد</strong>
                                                @endif
                                            </div>
                                            @if(isset($result['report_error']) && $result['report_error'])
                                                <div style="margin-top: 8px; padding: 8px; background: white; border-radius: 4px; border: 1px solid #fca5a5;">
                                                    <strong style="color: #dc2626; font-size: 12px; display: block; margin-bottom: 4px;">دلیل خطا:</strong>
                                                    <span style="color: #991b1b; font-size: 11px; font-family: monospace;">{{ $result['report_error'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
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
                        placeholder="جستجوی اقامت‌گر..."
                        class="form-control"
                        style="width: 300px;"
                    >
                </div>
            </div>

            <!-- Units and Residents -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                @foreach($filteredUnits as $unitIndex => $unit)
                    @foreach($unit['rooms'] as $roomIndex => $room)
                        @if(isset($room['beds']) && count(array_filter($room['beds'], fn($bed) => $bed['resident'])) > 0)
                            <div class="card" style="border: 1px solid #ddd;">
                                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px; border-radius: 6px 6px 0 0;">
                                    <strong>{{ $room['name'] }}</strong> - {{ $unit['unit']['name'] }}
                                </div>
                                <div style="padding: 15px;">
                                    @foreach($room['beds'] as $bedIndex => $bed)
                                        @if($bed['resident'])
                                            <div style="padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div>
                                                        <strong>{{ $bed['resident']['full_name'] ?? 'بدون نام' }}</strong>
                                                        <br>
                                                        <small style="color: #666;">{{ $bed['resident']['phone'] ?? 'بدون شماره' }}</small>
                                                    </div>
                                                    <button
                                                        wire:click="selectResident(
                                                            @js($bed['resident']),
                                                            @js($bed),
                                                            {{ $unitIndex }},
                                                            {{ $roomIndex }}
                                                        )"
                                                        class="btn btn-sm"
                                                        style="background: #10b981; color: white; border: none; font-size: 12px;"
                                                    >
                                                        <i class="fas fa-paper-plane"></i> ارسال
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>

            @if(count($filteredUnits) === 0)
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <p>هیچ اقامت‌گری یافت نشد</p>
                </div>
            @endif
        @endif
    </div>

    {{-- مودال حذف شده - فرم در صفحه اصلی نمایش داده می‌شود --}}

    {{-- اسکریپت برای اسکرول خودکار --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scrollToForm', () => {
                setTimeout(() => {
                    const formSection = document.getElementById('sms-form-section');
                    if (formSection) {
                        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // کمی بالاتر از فرم برای نمایش بهتر
                        window.scrollBy(0, -20);
                    }
                }, 200);
            });
            
            Livewire.on('resultReady', (data) => {
                console.log('Result ready:', data);
                // بعد از re-render، به نتیجه اسکرول کن
                setTimeout(() => {
                    const resultSection = document.querySelector('[data-result-section]');
                    if (resultSection) {
                        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 500);
            });
        });
        
        document.addEventListener('livewire:update', () => {
            // اگر نتیجه نمایش داده شد، به آن اسکرول کن
            setTimeout(() => {
                const resultSection = document.querySelector('[data-result-section]');
                if (resultSection) {
                    resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 300);
        });

    </script>
</div>
