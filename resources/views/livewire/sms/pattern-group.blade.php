<div>
    {{-- کارت نمایش اقامت‌گران انتخاب شده (گوشه بالا راست) --}}
    @if(count($selectedResidents) > 0)
        <div style="position: fixed; top: 80px; left: 20px; z-index: 1000; max-width: 350px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 2px solid #10b981;">
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-size: 16px;">
                    <i class="fas fa-users"></i> انتخاب شده‌ها ({{ count($selectedResidents) }})
                </h4>
                <button wire:click="clearSelection" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 18px; cursor: pointer; padding: 2px 8px; border-radius: 4px;" title="پاک کردن همه">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="max-height: 400px; overflow-y: auto; padding: 10px;">
                @foreach($selectedResidents as $key => $resident)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; margin-bottom: 8px; background: #f8f9fa; border-radius: 6px; border-right: 3px solid #10b981;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 13px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $resident['name'] ?? $resident['resident_name'] ?? 'بدون نام' }}
                            </div>
                            <div style="font-size: 11px; color: #666; margin-top: 2px;">
                                {{ $resident['phone'] ?? 'بدون شماره' }}
                            </div>
                        </div>
                        <button 
                            wire:click="removeResident('{{ $key }}')"
                            style="background: #dc2626; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; margin-right: 8px;"
                            title="حذف از لیست"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <div style="padding: 10px; background: #f0fdf4; border-top: 1px solid #dee2e6; border-radius: 0 0 8px 8px;">
                <button 
                    wire:click="$dispatch('scrollToForm')"
                    onclick="document.getElementById('sms-form-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                    style="width: 100%; background: #10b981; color: white; border: none; padding: 8px; border-radius: 6px; cursor: pointer; font-weight: 600;"
                >
                    <i class="fas fa-arrow-down"></i> رفتن به فرم ارسال
                </button>
            </div>
        </div>
    @endif

    <!-- آلارم برای عدم وجود گزارش -->
    @if($patternReportWarning)
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 1050; width: auto; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ $patternReportWarning }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" wire:click="$set('patternReportWarning', null)"></button>
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #10b981;"><i class="fas fa-file-code"></i> ارسال SMS الگویی گروهی</h2>
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
            @if(count($selectedResidents) > 0)
                <div id="sms-form-section" class="card" style="margin-bottom: 30px; border: 2px solid #10b981; background: #f0fdf4;">
                    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 15px; border-radius: 6px 6px 0 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; color: white;">
                                <i class="fas fa-file-code"></i> ارسال SMS الگویی به {{ count($selectedResidents) }} نفر
                            </h3>
                            <button wire:click="clearSelection" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 20px; cursor: pointer; padding: 5px 10px; border-radius: 5px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div style="padding: 25px;">
                        <form wire:submit.prevent="sendSms">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label class="form-label">گزارش (اختیاری)</label>
                                <select wire:model.live="selectedReport" class="form-control">
                                    <option value="">بدون گزارش</option>
                                    @foreach($reports as $report)
                                        <option value="{{ $report->id }}">{{ $report->title }} ({{ $report->category->name }})</option>
                                    @endforeach
                                </select>
                                
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
                                                    <i class="fas fa-eye"></i> پیش‌نمایش پیام ارسالی (نمونه با اولین اقامت‌گر):
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

                            {{-- نمایش پیشرفت ارسال --}}
                            @if($isSending)
                                <div style="background: #e0f2fe; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-right: 4px solid #0ea5e9;">
                                    <h4 style="margin: 0 0 15px 0; color: #0369a1;">
                                        <i class="fas fa-spinner fa-spin"></i> در حال ارسال...
                                    </h4>
                                    <div style="background: white; padding: 15px; border-radius: 6px;">
                                        <div style="margin-bottom: 10px;">
                                            <strong>در حال ارسال به:</strong> {{ $sendingProgress['current'] ?? '...' }}
                                        </div>
                                        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                                            <div>
                                                <strong style="color: #059669;">✓ ارسال شده:</strong> 
                                                <span style="font-size: 20px; font-weight: bold; color: #059669;">{{ $sendingProgress['sent'] }}</span>
                                            </div>
                                            <div>
                                                <strong style="color: #dc2626;">✗ ناموفق:</strong> 
                                                <span style="font-size: 20px; font-weight: bold; color: #dc2626;">{{ $sendingProgress['failed'] }}</span>
                                            </div>
                                            <div>
                                                <strong style="color: #666;">کل:</strong> 
                                                <span style="font-size: 20px; font-weight: bold; color: #666;">{{ $sendingProgress['total'] }}</span>
                                            </div>
                                        </div>
                                        <div style="background: #e5e7eb; height: 20px; border-radius: 10px; overflow: hidden;">
                                            @php
                                                $progressPercent = $sendingProgress['total'] > 0 
                                                    ? (($sendingProgress['sent'] + $sendingProgress['failed']) / $sendingProgress['total']) * 100 
                                                    : 0;
                                            @endphp
                                            <div style="background: linear-gradient(90deg, #10b981 0%, #059669 100%); height: 100%; width: {{ $progressPercent }}%; transition: width 0.3s;"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- نمایش نتایج ارسال --}}
                            @if(!$isSending && count($sendResults) > 0)
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #dee2e6;">
                                    <h4 style="margin: 0 0 15px 0; color: #10b981;">
                                        <i class="fas fa-check-circle"></i> نتایج ارسال
                                    </h4>
                                    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                                        <div>
                                            <strong style="color: #059669;">✓ ارسال شده:</strong> 
                                            <span style="font-size: 18px; font-weight: bold; color: #059669;">{{ $sendingProgress['sent'] }}</span>
                                        </div>
                                        <div>
                                            <strong style="color: #dc2626;">✗ ناموفق:</strong> 
                                            <span style="font-size: 18px; font-weight: bold; color: #dc2626;">{{ $sendingProgress['failed'] }}</span>
                                        </div>
                                        <div>
                                            <strong style="color: #666;">کل:</strong> 
                                            <span style="font-size: 18px; font-weight: bold; color: #666;">{{ $sendingProgress['total'] }}</span>
                                        </div>
                                    </div>
                                    
                                    <div style="max-height: 400px; overflow-y: auto;">
                                        @foreach($sendResults as $index => $sendResult)
                                            @php
                                                $result = $sendResult['result'];
                                                $reportCreated = $sendResult['report_created'] ?? false;
                                                $reportError = $sendResult['report_error'] ?? null;
                                            @endphp
                                            <div style="background: {{ $result['success'] ? '#d1fae5' : '#fee2e2' }}; padding: 15px; border-radius: 8px; margin-bottom: 12px; border-right: 4px solid {{ $result['success'] ? '#10b981' : '#dc2626' }}; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                                    <div style="flex: 1;">
                                                        <strong style="font-size: 15px; color: #1f2937;">{{ $index + 1 }}. {{ $sendResult['resident_name'] }}</strong>
                                                        <br>
                                                        <small style="color: #666; font-size: 12px;">
                                                            <i class="fas fa-phone"></i> {{ $sendResult['phone'] }}
                                                        </small>
                                                    </div>
                                                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                                        <span style="padding: 5px 14px; background: {{ $result['success'] ? '#10b981' : '#dc2626' }}; color: white; border-radius: 15px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                            {{ $result['success'] ? '✓ پیامک موفق' : '✗ پیامک ناموفق' }}
                                                        </span>
                                                        @if(!($result['success'] ?? false))
                                                            <button 
                                                                wire:click="resendSms({{ $index }})"
                                                                wire:loading.attr="disabled"
                                                                style="background: #3b82f6; color: white; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                                                                title="ارسال مجدد"
                                                            >
                                                                <span wire:loading.remove wire:target="resendSms({{ $index }})">
                                                                    <i class="fas fa-redo"></i> ارسال مجدد
                                                                </span>
                                                                <span wire:loading wire:target="resendSms({{ $index }})">
                                                                    <i class="fas fa-spinner fa-spin"></i>
                                                                </span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                {{-- وضعیت ثبت گزارش --}}
                                                @if($selectedReport)
                                                    <div style="margin-top: 10px; padding: 10px; background: {{ $reportCreated ? '#d1fae5' : '#fee2e2' }}; border-radius: 6px; border-right: 3px solid {{ $reportCreated ? '#10b981' : '#dc2626' }};">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            @if($reportCreated)
                                                                <i class="fas fa-check-circle" style="color: #10b981; font-size: 16px;"></i>
                                                                <strong style="color: #059669;">گزارش ثبت شد</strong>
                                                                @if(isset($sendResult['resident_report_id']))
                                                                    <span style="font-size: 11px; color: #666; margin-right: 5px;">
                                                                        (ID: {{ $sendResult['resident_report_id'] }})
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <i class="fas fa-times-circle" style="color: #dc2626; font-size: 16px;"></i>
                                                                <strong style="color: #dc2626;">گزارش ثبت نشد</strong>
                                                            @endif
                                                        </div>
                                                        @if($reportError)
                                                            <div style="margin-top: 8px; padding: 8px; background: white; border-radius: 4px; border: 1px solid #fca5a5;">
                                                                <strong style="color: #dc2626; font-size: 12px; display: block; margin-bottom: 4px;">دلیل خطا:</strong>
                                                                <span style="color: #991b1b; font-size: 11px; font-family: monospace;">{{ $reportError }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                {{-- وضعیت ارسال پیامک --}}
                                                <div style="margin-top: 10px; padding: 10px; background: {{ $result['success'] ? '#d1fae5' : '#fee2e2' }}; border-radius: 6px; border-right: 3px solid {{ $result['success'] ? '#10b981' : '#dc2626' }};">
                                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                                        @if($result['success'])
                                                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 16px;"></i>
                                                            <strong style="color: #059669;">پیامک ارسال شد</strong>
                                                        @else
                                                            <i class="fas fa-times-circle" style="color: #dc2626; font-size: 16px;"></i>
                                                            <strong style="color: #dc2626;">پیامک ارسال نشد</strong>
                                                        @endif
                                                    </div>
                                                    
                                                    <div style="margin-top: 6px; font-size: 13px;">
                                                        <strong>پیام:</strong> <span style="color: #374151;">{{ $result['message'] ?? 'بدون پیام' }}</span>
                                                    </div>
                                                    
                                                    @if(isset($result['rec_id']))
                                                        <div style="margin-top: 6px; font-size: 12px; color: #666;">
                                                            <strong>RecId:</strong> <code style="background: white; padding: 2px 6px; border-radius: 3px; font-family: monospace;">{{ $result['rec_id'] }}</code>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(isset($result['response_code']))
                                                        <div style="margin-top: 6px; font-size: 12px; color: #666;">
                                                            <strong>کد پاسخ:</strong> <code style="background: white; padding: 2px 6px; border-radius: 3px; font-family: monospace;">{{ $result['response_code'] }}</code>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(isset($result['raw_response']) && !$result['success'])
                                                        <details style="margin-top: 8px;">
                                                            <summary style="cursor: pointer; color: #666; font-size: 12px; font-weight: 600;">نمایش پاسخ خام API</summary>
                                                            <div style="margin-top: 8px; padding: 10px; background: white; border-radius: 4px; border: 1px solid #d1d5db; font-size: 11px; font-family: monospace; direction: ltr; text-align: left; max-height: 200px; overflow-y: auto;">
                                                                {{ $result['raw_response'] }}
                                                            </div>
                                                        </details>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                                <button type="button" wire:click="clearSelection" class="btn" style="background: #6c757d; color: white;">لغو</button>
                                <button 
                                    type="submit" 
                                    class="btn" 
                                    style="background: {{ ($patternReportWarning || !$selectedPattern) ? '#9ca3af' : '#10b981' }}; color: white; border: none; cursor: {{ ($patternReportWarning || !$selectedPattern) ? 'not-allowed' : 'pointer' }};" 
                                    wire:loading.attr="disabled" 
                                    @if($patternReportWarning || !$selectedPattern) disabled @endif
                                >
                                    <span wire:loading.remove wire:target="sendSms">
                                        <i class="fas fa-paper-plane"></i>
                                        ارسال به {{ count($selectedResidents) }} نفر
                                    </span>
                                    <span wire:loading wire:target="sendSms">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        در حال ارسال...
                                    </span>
                                </button>
                            </div>
                        </form>
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

            @if(count($selectedResidents) > 0 && !$isSending)
                <div style="background: #d1fae5; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-right: 3px solid #10b981;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>{{ count($selectedResidents) }}</strong> اقامت‌گر انتخاب شده
                        </div>
                    </div>
                </div>
            @endif

            <!-- Units and Residents -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                @foreach($filteredUnits as $unitIndex => $unit)
                    @foreach($unit['rooms'] as $roomIndex => $room)
                        @if(isset($room['beds']) && count(array_filter($room['beds'], fn($bed) => $bed['resident'])) > 0)
                            <div class="card" style="border: 1px solid #ddd;">
                                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px; border-radius: 6px 6px 0 0; display: flex; justify-content: space-between; align-items: center;">
                                    <strong>{{ $room['name'] }}</strong> - {{ $unit['unit']['name'] }}
                                    <button
                                        wire:click="selectAllInRoom({{ $unitIndex }}, {{ $roomIndex }})"
                                        class="btn btn-sm"
                                        style="background: rgba(255,255,255,0.2); color: white; font-size: 12px;"
                                    >
                                        انتخاب همه
                                    </button>
                                </div>
                                <div style="padding: 15px;">
                                    @foreach($room['beds'] as $bedIndex => $bed)
                                        @if($bed['resident'])
                                            @php
                                                $key = $unitIndex . '_' . $roomIndex . '_' . $bed['id'];
                                                $isSelected = isset($selectedResidents[$key]);
                                            @endphp
                                            <div style="padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div style="flex: 1;">
                                                        <div style="display: flex; align-items: center; gap: 10px;">
                                                            <input
                                                                type="checkbox"
                                                                wire:click="toggleSelectResident(
                                                                    '{{ $key }}',
                                                                    @js($bed['resident']),
                                                                    @js($bed),
                                                                    {{ $unitIndex }},
                                                                    {{ $roomIndex }}
                                                                )"
                                                                {{ $isSelected ? 'checked' : '' }}
                                                                style="cursor: pointer;"
                                                            >
                                                            <div>
                                                                <strong>{{ $bed['resident']['full_name'] ?? 'بدون نام' }}</strong>
                                                                <br>
                                                                <small style="color: #666;">{{ $bed['resident']['phone'] ?? 'بدون شماره' }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
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

    {{-- اسکریپت برای اسکرول خودکار --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scrollToForm', () => {
                setTimeout(() => {
                    const formSection = document.getElementById('sms-form-section');
                    if (formSection) {
                        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        window.scrollBy(0, -20);
                    }
                }, 200);
            });
        });
        
        // Auto-scroll to form when residents are selected
        document.addEventListener('livewire:update', () => {
            if (@js(count($selectedResidents) > 0)) {
                setTimeout(() => {
                    const formSection = document.getElementById('sms-form-section');
                    if (formSection) {
                        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 300);
            }
        });
    </script>

    <!-- مدال پیشرفت ارسال پیام -->
    @if($showProgressModal || $isSending)
    <div class="modal fade show d-block" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="false" style="display: block !important; background: rgba(0,0,0,0.5); z-index: 9999;" wire:key="progress-modal-{{ $sendingProgress['total'] ?? 0 }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="progressModalLabel">
                        <i class="fas fa-paper-plane me-2"></i>
                        در حال ارسال پیام‌ها...
                    </h5>
                </div>
                <div class="modal-body">
                    <!-- آمار کلی -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="mb-2">آمار کلی</h6>
                                    <div class="d-flex justify-content-around">
                                        <div>
                                            <div class="text-muted small">کل پیام‌ها</div>
                                            <div class="h5 mb-0 text-primary">{{ $sendingProgress['total'] ?? 0 }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">ارسال شده</div>
                                            <div class="h5 mb-0 text-success">{{ $sendingProgress['sent'] ?? 0 }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">خطا</div>
                                            <div class="h5 mb-0 text-danger">{{ $sendingProgress['failed'] ?? 0 }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small">مانده</div>
                                            <div class="h5 mb-0 text-warning">{{ ($sendingProgress['total'] ?? 0) - ($sendingProgress['sent'] ?? 0) - ($sendingProgress['failed'] ?? 0) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- نوار پیشرفت -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted">پیشرفت</span>
                            <span class="small text-muted">
                                {{ $sendingProgress['current_index'] ?? 0 }} از {{ $sendingProgress['total'] ?? 0 }}
                            </span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            @php
                                $progressPercent = ($sendingProgress['total'] ?? 0) > 0 
                                    ? ((($sendingProgress['sent'] ?? 0) + ($sendingProgress['failed'] ?? 0)) / ($sendingProgress['total'] ?? 1)) * 100 
                                    : 0;
                            @endphp
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 style="width: {{ $progressPercent }}%"
                                 aria-valuenow="{{ $progressPercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($progressPercent, 1) }}%
                            </div>
                        </div>
                    </div>

                    <!-- اقامت‌گر فعلی (فقط در حین ارسال) -->
                    @if(($sendingProgress['current'] ?? null) && !($sendingProgress['completed'] ?? false))
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div>
                                <strong>در حال ارسال به:</strong>
                                <div class="mt-1">{{ $sendingProgress['current'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- نتیجه ارسال (بعد از اتمام) -->
                    @if($sendingProgress['completed'] ?? false)
                    <div class="alert {{ ($sendingProgress['failed'] ?? 0) > 0 ? 'alert-warning' : 'alert-success' }} mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas {{ ($sendingProgress['failed'] ?? 0) > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }} me-2"></i>
                            <div>
                                <strong>نتیجه ارسال:</strong>
                                <div class="mt-1">{{ $sendingProgress['result_message'] ?? 'ارسال انجام شد' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if(!($sendingProgress['completed'] ?? false))
                    <button type="button" 
                            class="btn btn-danger" 
                            wire:click="cancelSending"
                            wire:loading.attr="disabled">
                        <i class="fas fa-times me-1"></i>
                        لغو ارسال
                    </button>
                    @else
                    <button type="button" 
                            class="btn btn-primary" 
                            wire:click="closeProgressModal"
                            wire:loading.attr="disabled">
                        <i class="fas fa-check me-1"></i>
                        بستن
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        // مدیریت قفل صفحه هنگام نمایش مدال
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-progress-modal', () => {
                document.body.style.overflow = 'hidden';
            });
            
            Livewire.on('hide-progress-modal', () => {
                document.body.style.overflow = '';
            });
        });
    </script>
    @endscript
</div>
