<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>ارسال SMS دستی</h2>
        </div>

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
                        <select wire:model="selectedReport" class="form-control" required>
                            <option value="">انتخاب گزارش</option>
                            @foreach($reports as $report)
                                <option value="{{ $report->id }}">{{ $report->title }} ({{ $report->category->name }})</option>
                            @endforeach
                        </select>
                        @error('selectedReport') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input 
                                type="checkbox" 
                                wire:model.live="usePattern"
                            >
                            <span>استفاده از الگوی پیامک (Pattern)</span>
                        </label>
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                            اگر فعال باشد، از الگوهای تأیید شده استفاده می‌شود. در غیر این صورت از پیامک عادی استفاده می‌شود.
                        </small>
                    </div>

                    @if($usePattern)
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label class="form-label">الگوی پیامک *</label>
                            <select wire:model="selectedPattern" class="form-control" required>
                                <option value="">انتخاب الگو</option>
                                @foreach($patterns as $pattern)
                                    <option value="{{ $pattern->id }}">
                                        {{ $pattern->title }} 
                                        @if($pattern->pattern_code)
                                            (کد: {{ $pattern->pattern_code }})
                                        @endif
                                    </option>
                                @endforeach
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
                                @endif
                            @endif
                            @error('selectedPattern') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                    @else
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
                    @endif

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
</div>