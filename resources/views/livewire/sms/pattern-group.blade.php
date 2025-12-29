<div>
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

            @if(count($selectedResidents) > 0)
                <div style="background: #d1fae5; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-right: 3px solid #10b981;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>{{ count($selectedResidents) }}</strong> اقامت‌گر انتخاب شده
                        </div>
                        <button wire:click="openSendModal" class="btn" style="background: #10b981; color: white; border: none;">
                            <i class="fas fa-paper-plane"></i>
                            ارسال SMS الگویی به انتخاب‌شده‌ها
                        </button>
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

    <!-- Modal for sending Pattern SMS -->
    @if($showSendModal)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; padding: 30px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="color: #10b981;"><i class="fas fa-file-code"></i> ارسال SMS الگویی به {{ count($selectedResidents) }} نفر</h3>
                    <button wire:click="closeSendModal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>

                <!-- Info about selected residents -->
                <div style="background: #d1fae5; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-right: 3px solid #10b981;">
                    <p><strong>تعداد اقامت‌گران انتخاب شده:</strong> {{ count($selectedResidents) }} نفر</p>
                </div>

                <form wire:submit.prevent="sendSms">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">گزارش (اختیاری)</label>
                        <select wire:model.live="selectedReport" class="form-control">
                            <option value="">بدون گزارش</option>
                            @foreach($reports as $report)
                                <option value="{{ $report->id }}">{{ $report->title }} ({{ $report->category->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">الگوی پیامک *</label>
                        <select wire:model.live="selectedPattern" class="form-control" required>
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
                        @error('selectedPattern') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    @if($selectedPattern)
                        @php
                            $selectedPatternObj = $patterns->firstWhere('id', $selectedPattern);
                        @endphp
                        @if($selectedPatternObj)
                            <div style="background: #d1fae5; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-right: 3px solid #10b981;">
                                <p><strong>عنوان الگو:</strong> {{ $selectedPatternObj->title }}</p>
                                @if($selectedPatternObj->pattern_code)
                                    <p><strong>کد الگو:</strong> <code style="background: white; padding: 2px 6px; border-radius: 3px;">{{ $selectedPatternObj->pattern_code }}</code></p>
                                @endif
                                <p><strong>متن الگو:</strong></p>
                                <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin-top: 5px;">
                                    {{ $selectedPatternObj->text }}
                                </div>
                            </div>
                        @endif
                    @endif

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" wire:click="closeSendModal" class="btn" style="background: #6c757d; color: white;">لغو</button>
                        <button type="submit" class="btn" style="background: #10b981; color: white; border: none;" {{ !$selectedPattern ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane"></i>
                            ارسال به {{ count($selectedResidents) }} نفر
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
