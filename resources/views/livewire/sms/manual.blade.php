<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>ارسال SMS دستی</h2>
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
        
        {{-- پیام همگام‌سازی حذف شد - اکنون به صورت آلارم در بالا سمت چپ نمایش داده می‌شود --}}

        @if($loading)
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #4361ee;"></i>
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
                                <div style="background: #4361ee; color: white; padding: 10px; border-radius: 6px 6px 0 0;">
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
                                                        class="btn btn-primary btn-sm"
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

    <!-- Modal for sending SMS -->
    @if($showModal && $selectedResident)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; padding: 30px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>ارسال SMS به {{ $selectedResident['name'] }}</h3>
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
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">پیام SMS *</label>
                        <select wire:model="selectedSmsMessage" class="form-control" required>
                            <option value="">انتخاب پیام</option>
                            @foreach($smsMessages as $sms)
                                <option value="{{ $sms->id }}">{{ $sms->title }}</option>
                            @endforeach
                        </select>
                        @error('selectedSmsMessage') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">یادداشت (اختیاری)</label>
                        <textarea wire:model="notes" class="form-control" rows="3" placeholder="یادداشت..."></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">لغو</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            ثبت گزارش و ارسال SMS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- مودال نمایش پاسخ API --}}
    @if($showApiResponseModal && $apiResponseData)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 900px; max-height: 90vh; overflow-y: auto; padding: 30px; direction: rtl;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px;">
                    <h3 style="margin: 0; color: #4361ee;">
                        <i class="fas fa-info-circle"></i> پاسخ API ملی پیامک
                    </h3>
                    <button wire:click="closeApiResponseModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- اطلاعات کلی --}}
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <strong style="color: #666; display: block; margin-bottom: 5px;">وضعیت:</strong>
                            @if($apiResponseData['success'])
                                <span style="color: #28a745; font-weight: bold; font-size: 16px;">
                                    <i class="fas fa-check-circle"></i> موفق
                                </span>
                            @else
                                <span style="color: #dc3545; font-weight: bold; font-size: 16px;">
                                    <i class="fas fa-times-circle"></i> ناموفق
                                </span>
                            @endif
                        </div>
                        <div>
                            <strong style="color: #666; display: block; margin-bottom: 5px;">پیام:</strong>
                            <span style="color: {{ $apiResponseData['success'] ? '#28a745' : '#dc3545' }};">
                                {{ $apiResponseData['message'] ?? 'N/A' }}
                            </span>
                        </div>
                        @if($apiResponseData['rec_id'])
                            <div>
                                <strong style="color: #666; display: block; margin-bottom: 5px;">RecId:</strong>
                                <span style="color: #4361ee; font-weight: bold; font-family: monospace;">
                                    {{ $apiResponseData['rec_id'] }}
                                </span>
                            </div>
                        @endif
                        @if($apiResponseData['response_code'])
                            <div>
                                <strong style="color: #666; display: block; margin-bottom: 5px;">کد پاسخ:</strong>
                                <span style="color: #666; font-family: monospace;">
                                    {{ $apiResponseData['response_code'] }}
                                </span>
                            </div>
                        @endif
                        @if($apiResponseData['http_status_code'])
                            <div>
                                <strong style="color: #666; display: block; margin-bottom: 5px;">کد وضعیت HTTP:</strong>
                                <span style="color: #666;">
                                    {{ $apiResponseData['http_status_code'] }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- اطلاعات الگو (اگر پیامک الگویی باشد) --}}
                @if($apiResponseData['is_pattern'] && $apiResponseData['pattern_code'])
                    <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-right: 4px solid #4361ee;">
                        <h4 style="margin: 0 0 15px 0; color: #4361ee;">
                            <i class="fas fa-file-alt"></i> اطلاعات الگو
                        </h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <strong style="color: #666; display: block; margin-bottom: 5px;">عنوان الگو:</strong>
                                <span>{{ $apiResponseData['pattern_title'] ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <strong style="color: #666; display: block; margin-bottom: 5px;">کد الگو:</strong>
                                <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-family: monospace;">
                                    {{ $apiResponseData['pattern_code'] }}
                                </span>
                            </div>
                            @if($apiResponseData['pattern_text'])
                                <div style="grid-column: 1 / -1;">
                                    <strong style="color: #666; display: block; margin-bottom: 5px;">متن الگو:</strong>
                                    <div style="background: white; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6;">
                                        {{ $apiResponseData['pattern_text'] }}
                                    </div>
                                </div>
                            @endif
                            @if($apiResponseData['variables'] && count($apiResponseData['variables']) > 0)
                                <div style="grid-column: 1 / -1;">
                                    <strong style="color: #666; display: block; margin-bottom: 5px;">متغیرهای ارسالی:</strong>
                                    <div style="background: white; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6;">
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                            @foreach($apiResponseData['variables'] as $index => $variable)
                                                <span style="background: #e0e7ff; color: #4361ee; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                                    {{{ $index }}}: {{ $variable }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @if($apiResponseData['variables_string'])
                                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6;">
                                                <strong style="color: #666; font-size: 12px;">رشته ارسالی به API:</strong>
                                                <code style="display: block; margin-top: 5px; padding: 5px; background: #f8f9fa; border-radius: 3px; font-size: 11px; direction: ltr; text-align: left;">
                                                    {{ $apiResponseData['variables_string'] }}
                                                </code>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- اطلاعات گیرنده --}}
                @if($apiResponseData['phone'] || $apiResponseData['resident_name'])
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: #856404;">
                            <i class="fas fa-user"></i> اطلاعات گیرنده
                        </h4>
                        <div style="display: flex; gap: 20px;">
                            @if($apiResponseData['resident_name'])
                                <div>
                                    <strong style="color: #666;">نام:</strong>
                                    <span>{{ $apiResponseData['resident_name'] }}</span>
                                </div>
                            @endif
                            @if($apiResponseData['phone'])
                                <div>
                                    <strong style="color: #666;">شماره تلفن:</strong>
                                    <span style="font-family: monospace;">{{ $apiResponseData['phone'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- پاسخ خام API --}}
                @if($apiResponseData['raw_response'])
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: #666;">
                            <i class="fas fa-code"></i> پاسخ خام API
                        </h4>
                        <div style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; max-height: 300px; overflow-y: auto;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: 'Courier New', monospace; font-size: 12px; direction: ltr; text-align: left; color: #333;">{{ $apiResponseData['raw_response'] }}</pre>
                        </div>
                    </div>
                @endif

                {{-- پاسخ API (JSON) --}}
                @if($apiResponseData['api_response'])
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: #666;">
                            <i class="fas fa-file-code"></i> پاسخ API (JSON)
                        </h4>
                        <div style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; max-height: 300px; overflow-y: auto;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: 'Courier New', monospace; font-size: 12px; direction: ltr; text-align: left; color: #333;">{{ is_array($apiResponseData['api_response']) ? json_encode($apiResponseData['api_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $apiResponseData['api_response'] }}</pre>
                        </div>
                    </div>
                @endif

                {{-- دکمه بستن --}}
                <div style="display: flex; justify-content: flex-end; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                    <button wire:click="closeApiResponseModal" class="btn btn-primary" style="min-width: 120px;">
                        <i class="fas fa-times"></i> بستن
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>