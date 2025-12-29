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
                                                        wire:click="openModal(
                                                            @js($bed['resident']),
                                                            @js($bed),
                                                            {{ $unitIndex }},
                                                            {{ $roomIndex }}
                                                        )"
                                                        class="btn btn-sm"
                                                        style="background: #10b981; color: white; border: none;"
                                                        style="font-size: 12px;"
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

    <!-- Modal for sending Pattern SMS -->
    @if($showModal && $selectedResident)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; padding: 30px; max-width: 800px; width: 100%; max-height: 95vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="color: #10b981;"><i class="fas fa-file-code"></i> ارسال SMS الگویی به {{ $selectedResident['name'] }}</h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>

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
                            <i class="fas fa-phone-alt"></i> شماره فرستنده:
                        </label>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                            <span style="font-size: 18px; font-weight: bold; color: #1f2937; font-family: monospace;">{{ $senderNumber }}</span>
                            @if($senderNumber === 'تنظیم نشده')
                                <span style="font-size: 11px; color: #dc2626; background: #fee2e2; padding: 4px 8px; border-radius: 4px;">
                                    (لطفاً در فایل .env تنظیم کنید)
                                </span>
                            @endif
                        </div>
                        <p style="margin-top: 8px; font-size: 11px; color: #78716c;">
                            این شماره برای ارسال پیامک الگویی استفاده می‌شود. برای تغییر، متغیر <code style="background: #f3f4f6; padding: 2px 4px; border-radius: 3px;">MELIPAYAMAK_PATTERN_FROM</code> را در فایل <code style="background: #f3f4f6; padding: 2px 4px; border-radius: 3px;">.env</code> تنظیم کنید.
                        </p>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">لغو</button>
                        <button type="submit" class="btn" style="background: #10b981; color: white; border: none;">
                            <i class="fas fa-paper-plane"></i>
                            ثبت گزارش و ارسال SMS الگویی
                        </button>
                    </div>
                </form>

                {{-- بخش Debug - همیشه نمایش داده می‌شود --}}
                <div style="margin-top: 20px; padding: 10px; background: #f0f0f0; border-radius: 5px; font-size: 11px; color: #666; border: 1px solid #ddd;">
                    <strong>Debug Info:</strong><br>
                    showResult = {{ $showResult ? 'true' : 'false' }}<br>
                    result = {{ $result ? 'SET (' . (is_array($result) ? 'array' : gettype($result)) . ')' : 'NULL' }}<br>
                    @if($result && is_array($result))
                        result['success'] = {{ isset($result['success']) ? ($result['success'] ? 'true' : 'false') : 'not set' }}<br>
                        result['message'] = {{ $result['message'] ?? 'N/A' }}<br>
                    @endif
                </div>

                {{-- نمایش نتیجه (مشابه PatternTest) --}}
                @if($showResult && $result)
                    <div data-result-section style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e9ecef; background: #f8f9fa; border-radius: 8px; padding: 20px;">
                        <h3 style="margin: 0 0 20px 0; color: #10b981; font-size: 20px;">
                            <i class="fas fa-info-circle"></i> پاسخ API ملی پیامک
                        </h3>
                        
                        <div style="space-y: 15px;">
                            <!-- وضعیت -->
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <span style="font-weight: 600; color: #666;">وضعیت:</span>
                                @if($result['success'] ?? false)
                                    <span style="padding: 5px 15px; background: #d1fae5; color: #059669; border-radius: 20px; font-size: 14px; font-weight: 600;">
                                        ✅ موفق
                                    </span>
                                @else
                                    <span style="padding: 5px 15px; background: #fee2e2; color: #dc2626; border-radius: 20px; font-size: 14px; font-weight: 600;">
                                        ❌ ناموفق
                                    </span>
                                @endif
                            </div>

                            <!-- پیام -->
                            @if(isset($result['message']))
                                <div style="margin-bottom: 15px;">
                                    <span style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">پیام:</span>
                                    <p style="color: #333; margin: 0;">{{ $result['message'] }}</p>
                                </div>
                            @endif

                            <!-- RecId -->
                            @if(isset($result['rec_id']))
                                <div style="margin-bottom: 15px;">
                                    <span style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">RecId:</span>
                                    <p style="color: #333; font-family: monospace; margin: 0;">{{ $result['rec_id'] }}</p>
                                </div>
                            @endif

                            <!-- کد پاسخ -->
                            @if(isset($result['response_code']))
                                <div style="margin-bottom: 15px;">
                                    <span style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">کد پاسخ:</span>
                                    <p style="color: #333; font-family: monospace; margin: 0;">{{ $result['response_code'] }}</p>
                                </div>
                            @endif

                            <!-- پاسخ خام -->
                            @if(isset($result['raw_response']))
                                <div style="margin-bottom: 15px;">
                                    <span style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">پاسخ خام API:</span>
                                    <div style="padding: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                                        <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 12px; color: #333; direction: ltr; text-align: left;">{{ $result['raw_response'] }}</pre>
                                    </div>
                                </div>
                            @endif

                            <!-- پاسخ API (JSON) -->
                            @if(isset($result['api_response']))
                                <div style="margin-bottom: 15px;">
                                    <span style="font-weight: 600; color: #666; display: block; margin-bottom: 5px;">پاسخ API (JSON):</span>
                                    <div style="padding: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                                        <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 12px; color: #333; direction: ltr; text-align: left;">{{ is_array($result['api_response']) ? json_encode($result['api_response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $result['api_response'] }}</pre>
                                    </div>
                                </div>
                            @endif

                            <!-- خطا -->
                            @if(isset($result['error']))
                                <div style="margin-bottom: 15px;">
                                    <span style="font-weight: 600; color: #dc2626; display: block; margin-bottom: 5px;">خطا:</span>
                                    <p style="color: #dc2626; margin: 0;">{{ $result['error'] }}</p>
                                </div>
                            @endif

                            <!-- اطلاعات کامل (برای دیباگ) -->
                            <details style="margin-top: 20px; cursor: pointer;">
                                <summary style="padding: 10px; background: #e9ecef; border-radius: 5px; font-weight: 600; color: #495057; user-select: none;">
                                    نمایش اطلاعات کامل (برای دیباگ)
                                </summary>
                                <div style="margin-top: 10px; padding: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                                    <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 11px; color: #333; direction: ltr; text-align: left;">{{ json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </details>
                        </div>
                    </div>
                @else
                    {{-- اگر showResult true است اما result null است --}}
                    @if($showResult)
                        <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e9ecef;">
                            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-right: 4px solid #ffc107;">
                                <h4 style="margin: 0 0 10px 0; color: #856404;">
                                    <i class="fas fa-exclamation-triangle"></i> هشدار: نتیجه ارسال موجود نیست
                                </h4>
                                <p style="color: #856404; margin: 0;">
                                    showResult = true اما result = null. لطفاً لاگ‌های سیستم را بررسی کنید.
                                </p>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif

    {{-- اسکریپت برای اسکرول خودکار به نتیجه --}}
    <script>
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
