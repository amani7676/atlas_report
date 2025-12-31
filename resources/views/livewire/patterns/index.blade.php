<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>مدیریت الگوهای پیام</h2>
            <div style="display: flex; gap: 10px;">
                <button wire:click="viewRawApiResponse" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-eye"></i>
                    مشاهده پاسخ API
                </button>
                <button wire:click="syncFromApi" class="btn" style="background: #17a2b8; color: white;" wire:loading.attr="disabled">
                    <i class="fas fa-sync" wire:loading.class="fa-spin"></i>
                    {{ $syncing ? 'در حال همگام‌سازی...' : 'همگام‌سازی از API' }}
                </button>
                <button wire:click="openCreateModal" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    ایجاد الگوی جدید
                </button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #666;"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="جستجوی الگو (عنوان، متن، کد)..."
                    class="form-control"
                    style="width: 300px;"
                >
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="margin: 0;">فیلتر وضعیت:</label>
                <select wire:model.live="statusFilter" class="form-control" style="width: 150px;">
                    <option value="">همه</option>
                    <option value="pending">در انتظار</option>
                    <option value="approved">تایید شده</option>
                    <option value="rejected">رد شده</option>
                </select>
            </div>
        </div>

        <!-- Patterns Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان
                            @if($sortBy === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>متن الگو</th>
                        <th wire:click="sortBy('pattern_code')" style="cursor: pointer;">
                            کد الگو
                            @if($sortBy === 'pattern_code')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>لیست سیاه</th>
                        <th wire:click="sortBy('status')" style="cursor: pointer;">
                            وضعیت
                            @if($sortBy === 'status')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('is_active')" style="cursor: pointer;">
                            فعال
                            @if($sortBy === 'is_active')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            تاریخ ایجاد
                            @if($sortBy === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patterns as $pattern)
                        <tr>
                            <td>
                                <strong>{{ $pattern->title }}</strong>
                            </td>
                            <td>
                                <p style="color: #666; font-size: 14px; margin: 0; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ Str::limit($pattern->text, 50) }}
                                </p>
                            </td>
                            <td>
                                @if($pattern->pattern_code)
                                    <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                        {{ $pattern->pattern_code }}
                                    </span>
                                @else
                                    <span style="color: #999; font-style: italic;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($pattern->blacklist_id)
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        {{ $pattern->blacklist_id }}
                                    </span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($pattern->status === 'approved')
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        تایید شده
                                    </span>
                                @elseif($pattern->status === 'rejected')
                                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        رد شده
                                    </span>
                                @else
                                    <span style="background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        در انتظار
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($pattern->is_active)
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        فعال
                                    </span>
                                @else
                                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        غیرفعال
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ jalaliDate($pattern->created_at, 'Y/m/d H:i') }}
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    @if($pattern->api_response)
                                        <button 
                                            wire:click="showApiResponse({{ $pattern->id }})" 
                                            class="btn" 
                                            style="background: #17a2b8; color: white; padding: 5px 10px; font-size: 12px;"
                                            title="مشاهده پاسخ API"
                                        >
                                            <i class="fas fa-code"></i>
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="toggleActive({{ $pattern->id }})" 
                                        class="btn" 
                                        style="background: {{ $pattern->is_active ? '#ffc107' : '#28a745' }}; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="{{ $pattern->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}"
                                    >
                                        <i class="fas fa-{{ $pattern->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button 
                                        wire:click="openEditModal({{ $pattern->id }})" 
                                        class="btn" 
                                        style="background: #4361ee; color: white; padding: 5px 10px; font-size: 12px;"
                                        title="ویرایش"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        onclick="confirmDeletePattern({{ $pattern->id }}, '{{ $pattern->title }}')" 
                                        class="btn btn-danger" 
                                        style="padding: 5px 10px; font-size: 12px;"
                                        title="حذف"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                الگویی یافت نشد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($patterns->hasPages())
            <div style="margin-top: 20px; display: flex; justify-content: center;">
                {{ $patterns->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>{{ $isEditing ? 'ویرایش الگو' : 'ایجاد الگوی جدید' }}</h3>
                    <button wire:click="closeModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $isEditing ? 'updatePattern' : 'createPattern' }}">
                    <div class="form-group">
                        <label class="form-label">عنوان الگو <span style="color: red;">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            class="form-control" 
                            placeholder="مثال: الگوی خوش‌آمدگویی"
                            required
                        >
                        @error('title') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">متن الگو <span style="color: red;">*</span></label>
                        
                        <!-- انتخاب متغیرها -->
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <strong style="font-size: 14px;">متغیرها:</strong>
                                <a href="/variables" target="_blank" class="btn" style="background: #6c757d; color: white; padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-cog"></i>
                                    مدیریت متغیرها
                                </a>
                            </div>
                            
                            @if(!empty($availableVariables['user']))
                                <div style="margin-bottom: 10px;">
                                    <strong style="font-size: 14px;">متغیرهای کاربر:</strong>
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                        @foreach($availableVariables['user'] ?? [] as $var)
                                            <button 
                                                type="button"
                                                wire:click="insertVariable('{{ $var['key'] }}', 'user')"
                                                class="btn" 
                                                style="background: #4361ee; color: white; padding: 5px 10px; font-size: 12px;"
                                                title="{{ $var['label'] }} ({{ $var['code'] ?? '' }}) - فیلد: {{ $var['key'] }}"
                                            >
                                                {{ $var['label'] }} <small>({{ $var['code'] ?? '' }})</small>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            @if(!empty($availableVariables['report']))
                                <div style="margin-bottom: 10px;">
                                    <strong style="font-size: 14px;">متغیرهای گزارش:</strong>
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                        @foreach($availableVariables['report'] ?? [] as $var)
                                            <button 
                                                type="button"
                                                wire:click="insertVariable('{{ $var['key'] }}', 'report')"
                                                class="btn" 
                                                style="background: #28a745; color: white; padding: 5px 10px; font-size: 12px;"
                                                title="{{ $var['label'] }} ({{ $var['code'] ?? '' }}) - فیلد: {{ $var['key'] }}"
                                            >
                                                {{ $var['label'] }} <small>({{ $var['code'] ?? '' }})</small>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            @if(!empty($availableVariables['general']))
                                <div>
                                    <strong style="font-size: 14px;">متغیرهای عمومی:</strong>
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                        @foreach($availableVariables['general'] ?? [] as $var)
                                            <button 
                                                type="button"
                                                wire:click="insertVariable('{{ $var['key'] }}', 'general')"
                                                class="btn" 
                                                style="background: #17a2b8; color: white; padding: 5px 10px; font-size: 12px;"
                                                title="{{ $var['label'] }} ({{ $var['code'] ?? '' }}) - فیلد: {{ $var['key'] }}"
                                            >
                                                {{ $var['label'] }} <small>({{ $var['code'] ?? '' }})</small>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            @if(empty($availableVariables['user']) && empty($availableVariables['report']) && empty($availableVariables['general']))
                                <div style="text-align: center; padding: 20px; color: #999;">
                                    <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                    <p>هیچ متغیر فعالی یافت نشد.</p>
                                    <a href="/variables/create" target="_blank" class="btn btn-primary" style="margin-top: 10px;">
                                        <i class="fas fa-plus"></i>
                                        ایجاد متغیر جدید
                                    </a>
                                </div>
                            @endif
                        </div>
                        
                        <!-- نمایش متغیرهای انتخاب شده -->
                        @if(!empty($selectedVariables))
                            <div style="background: #e8f4fd; padding: 10px; border-radius: 6px; margin-bottom: 10px;">
                                <strong style="font-size: 13px;">متغیرهای استفاده شده:</strong>
                                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                    @foreach($selectedVariables as $var)
                                        <span style="background: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                                            <span style="background: #4361ee; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-family: monospace;">
                                                {{ $var['code'] ?? '{' . $var['index'] . '}' }}
                                            </span>
                                            <span>{{ $var['label'] }}</span>
                                            @if(isset($var['code']))
                                                <button 
                                                    type="button"
                                                    wire:click="removeVariable({{ $var['index'] }})"
                                                    style="background: #dc3545; color: white; border: none; border-radius: 3px; padding: 2px 6px; cursor: pointer; font-size: 10px;"
                                                    title="حذف"
                                                >
                                                    ×
                                                </button>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <textarea 
                            wire:model="text" 
                            class="form-control" 
                            rows="5"
                            placeholder="متن الگوی پیامک... (از دکمه‌های بالا برای اضافه کردن متغیرها استفاده کنید)"
                            required
                        ></textarea>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            متغیرها به صورت {0}, {1}, {2} و ... در متن قرار می‌گیرند
                        </small>
                        @error('text') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">کد الگو (از ملی پیامک)</label>
                        <input 
                            type="text" 
                            wire:model="pattern_code" 
                            class="form-control" 
                            placeholder="کد الگو (اختیاری)"
                        >
                        @error('pattern_code') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">کد لیست سیاه (5 رقمی) <span style="color: red;">*</span></label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input 
                                type="text" 
                                wire:model="blacklist_id" 
                                class="form-control" 
                                placeholder="مثال: 12345"
                                required
                                style="flex: 1;"
                                pattern="[0-9]*"
                                inputmode="numeric"
                            >
                            @if(count($activeBlacklists) > 0)
                                <select 
                                    wire:change="$set('blacklist_id', $event.target.value)" 
                                    class="form-control" 
                                    style="width: 200px;"
                                    title="انتخاب از لیست موجود"
                                >
                                    <option value="">یا از لیست انتخاب کنید</option>
                                    @foreach($activeBlacklists as $blacklist)
                                        <option value="{{ $blacklist->blacklist_id }}">{{ $blacklist->title }} ({{ $blacklist->blacklist_id }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                            می‌توانید عدد را مستقیماً وارد کنید یا از لیست انتخاب کنید
                        </small>
                        @error('blacklist_id') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">وضعیت</label>
                        <select wire:model="status" class="form-control">
                            <option value="pending">در انتظار</option>
                            <option value="approved">تایید شده</option>
                            <option value="rejected">رد شده</option>
                        </select>
                        @error('status') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                    </div>

                    @if($status === 'rejected')
                        <div class="form-group">
                            <label class="form-label">دلیل رد</label>
                            <textarea 
                                wire:model="rejection_reason" 
                                class="form-control" 
                                rows="3"
                                placeholder="دلیل رد الگو..."
                            ></textarea>
                            @error('rejection_reason') <span style="color: red; font-size: 12px;">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input 
                                type="checkbox" 
                                wire:model="is_active"
                            >
                            <span>فعال</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background: #6c757d; color: white;">
                            لغو
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEditing ? 'ذخیره تغییرات' : 'ایجاد الگو' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- API Response Modal -->
    @if($showApiResponseModal && $apiResponseData)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>پاسخ API - {{ $apiResponseData['title'] ?? 'ویرایش الگو' }}</h3>
                    <button wire:click="closeApiResponseModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="background: {{ isset($apiResponseData['success']) && $apiResponseData['success'] ? '#d4edda' : '#f8d7da' }}; padding: 20px; border-radius: 6px; margin-bottom: 15px; border: 1px solid {{ isset($apiResponseData['success']) && $apiResponseData['success'] ? '#c3e6cb' : '#f5c6cb' }};">
                    <div style="margin-bottom: 15px;">
                        <strong style="display: block; margin-bottom: 8px; color: {{ isset($apiResponseData['success']) && $apiResponseData['success'] ? '#155724' : '#721c24' }};">
                            @if(isset($apiResponseData['success']) && $apiResponseData['success'])
                                <i class="fas fa-check-circle"></i> موفق
                            @else
                                <i class="fas fa-times-circle"></i> ناموفق
                            @endif
                        </strong>
                        <p style="margin: 0; color: {{ isset($apiResponseData['success']) && $apiResponseData['success'] ? '#155724' : '#721c24' }};">
                            {{ $apiResponseData['message'] ?? 'پاسخ دریافت شد' }}
                        </p>
                    </div>
                    
                    @if(isset($apiResponseData['status']))
                        <div style="margin-bottom: 10px;">
                            <strong>وضعیت الگو:</strong> 
                            <span style="background: {{ $apiResponseData['status'] === 'pending' ? '#ffc107' : ($apiResponseData['status'] === 'approved' ? '#28a745' : '#dc3545') }}; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                @if($apiResponseData['status'] === 'pending')
                                    در انتظار تأیید
                                @elseif($apiResponseData['status'] === 'approved')
                                    تأیید شده
                                @else
                                    رد شده
                                @endif
                            </span>
                        </div>
                        @if(isset($apiResponseData['status_message']))
                            <div style="margin-bottom: 10px; color: #666; font-size: 13px;">
                                <i class="fas fa-info-circle"></i> {{ $apiResponseData['status_message'] }}
                            </div>
                        @endif
                    @endif
                    
                    @if(isset($apiResponseData['pattern_code']))
                        <div style="margin-bottom: 10px;">
                            <strong>کد الگو:</strong> 
                            <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                                {{ $apiResponseData['pattern_code'] }}
                            </span>
                        </div>
                    @endif
                    
                    <div style="margin-bottom: 10px;">
                        <strong>کد وضعیت HTTP:</strong> {{ $apiResponseData['http_status_code'] ?? '-' }}
                    </div>
                    
                    @if(isset($apiResponseData['created_at']))
                        <div style="margin-bottom: 10px;">
                            <strong>تاریخ ایجاد:</strong> {{ $apiResponseData['created_at'] }}
                        </div>
                    @endif
                </div>

                @if(isset($apiResponseData['parsed_response']) && $apiResponseData['parsed_response'])
                    <div style="margin-bottom: 15px;">
                        <strong>اطلاعات الگو از API:</strong>
                        <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px;">
                            <div style="margin-bottom: 8px;"><strong>BodyID:</strong> {{ $apiResponseData['parsed_response']['BodyID'] ?? '-' }}</div>
                            <div style="margin-bottom: 8px;"><strong>Title:</strong> {{ $apiResponseData['parsed_response']['Title'] ?? '-' }}</div>
                            <div style="margin-bottom: 8px;"><strong>Body:</strong> {{ $apiResponseData['parsed_response']['Body'] ?? '-' }}</div>
                            <div style="margin-bottom: 8px;"><strong>BodyStatus:</strong> {{ $apiResponseData['parsed_response']['BodyStatus'] ?? '-' }}</div>
                            <div style="margin-bottom: 8px;"><strong>InsertDate:</strong> {{ $apiResponseData['parsed_response']['InsertDate'] ?? '-' }}</div>
                            <div><strong>Description:</strong> {{ $apiResponseData['parsed_response']['Description'] ?? '-' }}</div>
                        </div>
                    </div>
                @endif

                @if(isset($apiResponseData['api_response']) && $apiResponseData['api_response'])
                    <div style="margin-bottom: 15px;">
                        <strong>پاسخ خام API:</strong>
                        <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 400px; overflow-y: auto;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px; direction: ltr; text-align: left;">{{ $apiResponseData['api_response'] }}</pre>
                        </div>
                    </div>
                @endif

                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button wire:click="closeApiResponseModal" class="btn btn-primary">
                        بستن
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Raw API Response Modal -->
    @if($showRawApiResponseModal && $rawApiResponseData)
        <div style="position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: white; border-radius: 10px; width: 100%; max-width: 900px; max-height: 90vh; overflow-y: auto; padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>پاسخ خام API - GetSharedServiceBody</h3>
                    <button wire:click="closeRawApiResponseModal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px;">
                        <strong>وضعیت:</strong> 
                        @if($rawApiResponseData['success'])
                            <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                ✅ موفق
                            </span>
                        @else
                            <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                ❌ خطا
                            </span>
                        @endif
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>پیام:</strong> {{ $rawApiResponseData['message'] ?? '-' }}
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>تعداد الگوها:</strong> {{ $rawApiResponseData['patterns_count'] ?? 0 }}
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>کد وضعیت HTTP:</strong> {{ $rawApiResponseData['http_status_code'] ?? '-' }}
                    </div>
                </div>

                @if(!empty($rawApiResponseData['patterns']))
                    <div style="margin-bottom: 15px;">
                        <strong>الگوهای دریافت شده:</strong>
                        <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 300px; overflow-y: auto;">
                            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 12px; direction: ltr; text-align: left;">{{ json_encode($rawApiResponseData['patterns'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @endif

                <div style="margin-bottom: 15px;">
                    <strong>پاسخ خام کامل API:</strong>
                    <div style="background: #fff; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 10px; max-height: 400px; overflow-y: auto;">
                        <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; font-family: monospace; font-size: 11px; direction: ltr; text-align: left;">{{ $rawApiResponseData['raw_response'] }}</pre>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button wire:click="closeRawApiResponseModal" class="btn" style="background: #6c757d; color: white;">
                        بستن
                    </button>
                    @if($rawApiResponseData['success'] && !empty($rawApiResponseData['patterns']))
                        <button wire:click="syncFromApi" class="btn btn-primary">
                            <i class="fas fa-sync"></i>
                            همگام‌سازی با دیتابیس
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <script>
        function confirmDeletePattern(id, title) {
            Swal.fire({
                title: 'حذف الگو',
                html: `آیا مطمئن هستید که می‌خواهید الگو <strong>"${title}"</strong> را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deletePattern(id);
                }
            });
        }
    </script>
</div>
