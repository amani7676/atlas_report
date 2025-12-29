<div>
    <div class="card">
        <h2 style="margin-bottom: 20px;">ایجاد گزارش جدید</h2>

        <form wire:submit.prevent="save">
            <div class="form-group">
                <label class="form-label">دسته‌بندی *</label>
                <select wire:model="category_id" class="form-control" required>
                    <option value="">انتخاب دسته‌بندی</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">عنوان گزارش *</label>
                <input
                    type="text"
                    wire:model="title"
                    class="form-control"
                    placeholder="مثال: نامرتب بودن اتاق"
                    required
                >
                @error('title') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">توضیحات *</label>
                <textarea
                    wire:model="description"
                    class="form-control"
                    rows="4"
                    placeholder="توضیحات کامل گزارش..."
                    required
                ></textarea>
                @error('description') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">نمره منفی *</label>
                    <input
                        type="number"
                        wire:model="negative_score"
                        class="form-control"
                        min="0"
                        required
                    >
                    @error('negative_score') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">ضریب افزایش *</label>
                    <input
                        type="number"
                        wire:model="increase_coefficient"
                        class="form-control"
                        step="0.01"
                        min="0"
                        required
                    >
                    @error('increase_coefficient') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">شماره صفحه *</label>
                    <input
                        type="number"
                        wire:model="page_number"
                        class="form-control"
                        min="1"
                        required
                    >
                    @error('page_number') <span style="color: #f72585; font-size: 14px;">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- انتخاب الگوهای پیامک -->
            <div class="form-group" style="margin-top: 30px;">
                <label class="form-label">
                    <i class="fas fa-sms"></i>
                    الگوهای پیامک مرتبط (اختیاری)
                </label>
                <small style="display: block; color: #666; margin-bottom: 10px; font-size: 12px;">
                    می‌توانید یک یا چند الگوی پیامک را به این گزارش متصل کنید. این الگوها در صفحه ارسال پیامک دستی نمایش داده می‌شوند.
                </small>
                
                @if(count($patterns) > 0)
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                        @foreach($patterns as $pattern)
                            <label 
                                style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; border: 2px solid {{ in_array($pattern->id, $selectedPatterns) ? '#4361ee' : '#ddd' }}; border-radius: 8px; cursor: pointer; background: {{ in_array($pattern->id, $selectedPatterns) ? '#e7f3ff' : '#fff' }}; transition: all 0.3s;"
                                wire:click="togglePattern({{ $pattern->id }})"
                            >
                                <input 
                                    type="checkbox" 
                                    checked="{{ in_array($pattern->id, $selectedPatterns) ? 'checked' : '' }}"
                                    style="cursor: pointer;"
                                    readonly
                                >
                                <div>
                                    <strong style="display: block; font-size: 14px;">{{ $pattern->title }}</strong>
                                    @if($pattern->pattern_code)
                                        <small style="color: #666; font-size: 11px;">کد: {{ $pattern->pattern_code }}</small>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p style="color: #666; font-size: 14px; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                        <i class="fas fa-info-circle"></i>
                        هیچ الگوی فعالی یافت نشد. لطفاً ابتدا الگوهایی را در بخش "الگوها" ایجاد کنید.
                    </p>
                @endif
                
                @if(count($selectedPatterns) > 0)
                    <div style="background: #e7f3ff; padding: 15px; border-radius: 6px; margin-top: 15px;">
                        <strong style="display: block; margin-bottom: 10px; color: #4361ee;">
                            <i class="fas fa-check-circle"></i>
                            الگوهای انتخاب شده ({{ count($selectedPatterns) }}):
                        </strong>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            @foreach($selectedPatterns as $patternId)
                                @php
                                    $pattern = $patterns->firstWhere('id', $patternId);
                                @endphp
                                @if($pattern)
                                    <span 
                                        style="background: #4361ee; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;"
                                    >
                                        {{ $pattern->title }}
                                        <button 
                                            type="button"
                                            wire:click="removePattern({{ $pattern->id }})"
                                            style="background: rgba(255,255,255,0.3); border: none; color: white; cursor: pointer; padding: 2px 6px; border-radius: 50%; font-size: 10px; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center;"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    ذخیره گزارش
                </button>
                <a href="/reports" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به لیست
                </a>
            </div>
        </form>
    </div>
</div>
