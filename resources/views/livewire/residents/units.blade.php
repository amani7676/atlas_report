<div>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="container-fluid py-3" dir="rtl">
        <!-- نمایش پاسخ دیتابیس بعد از ثبت گزارش -->
        @if($showSubmissionResult && count($lastSubmittedReports) > 0)
            <div class="card mb-3" style="border: 2px solid #10b981; box-shadow: 0 4px 12px rgba(16,185,129,0.2);">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
                    <h6 class="mb-0">
                        <i class="fas fa-database me-2"></i>
                        <strong>پاسخ دیتابیس - گزارش‌های ثبت شده</strong>
                        <span class="badge bg-light text-dark ms-2">{{ count($lastSubmittedReports) }} مورد</span>
                    </h6>
                    <button wire:click="closeSubmissionResult" class="btn btn-sm" style="background: rgba(255,255,255,0.2); border: none; color: white;" title="بستن">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>گزارش‌ها با موفقیت در دیتابیس ثبت شدند!</strong>
                        <p class="mb-0 mt-2" style="font-size: 13px;">در زیر می‌توانید پاسخ کامل دیتابیس و تمام داده‌های ثبت شده را مشاهده کنید:</p>
                    </div>
                    @foreach($lastSubmittedReports as $index => $report)
                        <div class="mb-3 p-3" style="background: #f0fdf4; border-radius: 8px; border-right: 4px solid #10b981;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0" style="color: #059669;">
                                    <i class="fas fa-file-alt me-2"></i>
                                    گزارش #{{ $index + 1 }}
                                </h6>
                                <span class="badge bg-success">ID: {{ $report['id'] }}</span>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <strong style="color: #666;">گزارش:</strong>
                                    <div style="color: #1f2937;">{{ $report['report_title'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <strong style="color: #666;">دسته‌بندی:</strong>
                                    <div style="color: #1f2937;">{{ $report['category_name'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <strong style="color: #666;">اقامت‌گر:</strong>
                                    <div style="color: #1f2937;">{{ $report['resident_name'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <strong style="color: #666;">تلفن:</strong>
                                    <div style="color: #1f2937; direction: ltr; text-align: right;">{{ $report['phone'] }}</div>
                                </div>
                                <div class="col-md-4">
                                    <strong style="color: #666;">واحد:</strong>
                                    <div style="color: #1f2937;">{{ $report['unit_name'] ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <strong style="color: #666;">اتاق:</strong>
                                    <div style="color: #1f2937;">{{ $report['room_name'] ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <strong style="color: #666;">تخت:</strong>
                                    <div style="color: #1f2937;">{{ $report['bed_name'] ?? '-' }}</div>
                                </div>
                                @if($report['notes'])
                                    <div class="col-12">
                                        <strong style="color: #666;">توضیحات:</strong>
                                        <div style="color: #1f2937;">{{ $report['notes'] }}</div>
                                    </div>
                                @endif
                                <div class="col-12">
                                    <strong style="color: #666;">تاریخ ثبت:</strong>
                                    <div style="color: #1f2937;">{{ jalaliDate($report['created_at'], 'Y/m/d H:i:s') }}</div>
                                </div>
                            </div>

                            <!-- نمایش تمام داده‌های رکورد از دیتابیس -->
                            <details class="mt-3">
                                <summary style="cursor: pointer; color: #059669; font-weight: 600; font-size: 13px; padding: 8px; background: #d1fae5; border-radius: 4px;">
                                    <i class="fas fa-database me-1"></i>
                                    <strong>پاسخ کامل دیتابیس - نمایش تمام داده‌های ثبت شده</strong>
                                </summary>
                                <div class="mt-2 p-3" style="background: white; border-radius: 6px; border: 1px solid #d1fae5;">
                                    <div class="mb-2" style="color: #666; font-size: 12px;">
                                        <i class="fas fa-info-circle me-1"></i>
                                        این پاسخ کامل دیتابیس است که پس از ثبت گزارش برگردانده شده:
                                    </div>
                                    <pre style="margin: 0; font-size: 11px; color: #374151; direction: ltr; text-align: left; max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 12px; border-radius: 4px; border: 1px solid #e5e7eb;">{{ json_encode($report['all_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </details>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-users me-2"></i>
                        سیستم مدیریت اقامت‌گران
                    </span>
                    <span>{{ count(array_filter($filteredUnits, function($unit) {
                        return array_filter($unit['rooms'], function($room) {
                            return $room['bed_count'] > 0;
                        });
                    })) }} گروه</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                class="form-control"
                                placeholder="جستجوی اقامت‌گر..."
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="filterEmptyBeds"
                                wire:model.live="filterEmptyBeds"
                            >
                            <label class="form-check-label" for="filterEmptyBeds">
                                فقط اتاق‌های دارای اقامت‌گر
                            </label>
                        </div>
                    </div>
                </div>

                @if($error)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ $error }}
                    </div>
                @endif

                @if(count($selectedResidents) > 0)
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <span>{{ count($selectedResidents) }} اقامت‌گر انتخاب شده</span>
                        <button
                            class="btn btn-warning btn-sm"
                            wire:click="openSelectedGroupReport"
                        >
                            <i class="fas fa-file-alt me-1"></i>
                            ثبت گزارش گروهی
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if($loading)
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
                <p class="mt-2">در حال دریافت اطلاعات از API...</p>
            </div>
        @else
            <!-- Rooms Tables -->
            <div class="row">
                @foreach($filteredUnits as $unitIndex => $unit)
                    @foreach($unit['rooms'] as $roomIndex => $room)
                        @if($room['bed_count'] > 0)
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="fas fa-door-closed me-2"></i>
                                            {{ $room['name'] }}
                                        </h6>
                                        <div>
                                            <button
                                                class="btn btn-sm btn-light me-2"
                                                wire:click="selectAllInRoom({{ $unitIndex }}, {{ $roomIndex }})"
                                            >
                                                <i class="fas fa-check-square"></i>
                                            </button>
                                            <button
                                                class="btn btn-sm btn-warning"
                                                wire:click="openGroupReportFromRoom({{ $unitIndex }}, {{ $roomIndex }})"
                                            >
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="30px">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                wire:click="selectAllInRoom({{ $unitIndex }}, {{ $roomIndex }})"
                                                            >
                                                        </th>
                                                        <th>نام</th>
                                                        <th>تلفن</th>
                                                        <th>شغل</th>
                                                        <th>تخت</th>
                                                        <th width="50px">عملیات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($room['beds'] as $bed)
                                                        @if($bed['resident'])
                                                            <tr>
                                                                <td>
                                                                    <input
                                                                        class="form-check-input"
                                                                        type="checkbox"
                                                                        wire:model="selectedResidents.{{ $unitIndex }}_{{ $roomIndex }}_{{ $bed['id'] }}"
                                                                        wire:click="toggleSelectResident('{{ $unitIndex }}_{{ $roomIndex }}_{{ $bed['id'] }}', {{ json_encode($bed['resident']) }}, {{ json_encode($bed) }}, {{ $unitIndex }}, {{ $roomIndex }})"
                                                                    >
                                                                </td>
                                                                <td>{{ $bed['resident']['full_name'] }}</td>
                                                                <td>{{ $bed['resident']['phone'] }}</td>
                                                                <td>{{ $this->getJobTitle($bed['resident']['job'] ?? '') }}</td>
                                                                <td>{{ $bed['name'] }}</td>
                                                                <td>
                                                                    <button
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        wire:click="openIndividualReport({{ json_encode($bed['resident']) }}, {{ json_encode($bed) }}, {{ $unitIndex }}, {{ $roomIndex }})"
                                                                    >
                                                                        <i class="fas fa-file-medical"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @else
                                                            <tr class="table-secondary">
                                                                <td></td>
                                                                <td colspan="4" class="text-center">
                                                                    <i class="fas fa-bed me-2"></i>
                                                                    تخت {{ $bed['name'] }} - خالی
                                                                </td>
                                                                <td></td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach

                @if(count(array_filter($filteredUnits, function($unit) {
                    return array_filter($unit['rooms'], function($room) {
                        return $room['bed_count'] > 0;
                    });
                })) === 0)
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            هیچ اطلاعاتی یافت نشد
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Report Modal - Material Design -->
    @if($showReportModal)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1050;" tabindex="-1">
            <div class="modal-dialog modal-lg" style="margin-top: 5vh;">
                <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 8px 32px rgba(0,0,0,0.3); overflow: hidden; position: relative;">
                    
                    <!-- Loading Overlay -->
                    @if($reportModalLoading)
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.95); z-index: 1000; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 16px;">
                            <div style="text-align: center;">
                                <div style="width: 80px; height: 80px; margin: 0 auto 20px; position: relative;">
                                    <div style="width: 80px; height: 80px; border: 6px solid #f3f4f6; border-top-color: #667eea; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60px; height: 60px; border: 4px solid #f3f4f6; border-top-color: #764ba2; border-radius: 50%; animation: spin 0.8s linear infinite reverse;"></div>
                                </div>
                                <h4 style="color: #667eea; font-weight: 600; margin-bottom: 10px; font-size: 20px;">
                                    <i class="fas fa-paper-plane" style="margin-left: 8px;"></i>
                                    در حال ثبت گزارش و ارسال پیامک...
                                </h4>
                                <p style="color: #64748b; font-size: 14px; margin: 0;">
                                    لطفاً صبر کنید
                                </p>
                            </div>
                        </div>
                        <style>
                            @keyframes spin {
                                0% { transform: rotate(0deg); }
                                100% { transform: rotate(360deg); }
                            }
                        </style>
                    @endif
                    <!-- Header with Material Design -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; position: relative;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-alt" style="font-size: 24px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <h5 style="margin: 0; font-size: 20px; font-weight: 600;">
                                    @if($reportType === 'individual')
                                        ثبت گزارش برای {{ $currentResident['name'] }}
                                    @else
                                        ثبت گزارش گروهی برای {{ count($selectedResidents) }} اقامت‌گر
                                    @endif
                                </h5>
                            </div>
                            <button type="button" wire:click="closeModal" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-body" style="padding: 24px; background: #f8f9fa;">
                        <!-- Resident/Room Info - Material Card -->
                        @if($reportType === 'individual')
                            <div style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); padding: 20px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            {{ mb_substr($currentResident['name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-size: 12px; color: #64748b; margin-bottom: 2px;">اقامت‌گر</div>
                                            <div style="font-weight: 600; color: #1e293b; font-size: 14px;">{{ $currentResident['name'] }}</div>
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 2px;">تلفن</div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 14px; direction: ltr; text-align: right;">
                                            <i class="fas fa-phone" style="margin-left: 6px; color: #3b82f6;"></i>{{ $currentResident['phone'] }}
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 2px;">اتاق</div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 14px;">
                                            <i class="fas fa-door-open" style="margin-left: 6px; color: #10b981;"></i>{{ $currentResident['room_name'] }}
                                        </div>
                                    </div>
                                    <div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 2px;">تخت</div>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 14px;">
                                            <i class="fas fa-bed" style="margin-left: 6px; color: #f59e0b;"></i>{{ $currentResident['bed_name'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 20px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                                        {{ count($selectedResidents) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #92400e; font-size: 16px;">تعداد اقامت‌گران انتخاب شده</div>
                                        <div style="color: #78350f; font-size: 14px; margin-top: 4px;">{{ count($selectedResidents) }} نفر</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Categories and Reports - Material Design -->
                        <div style="margin-bottom: 24px;">
                            <label style="font-weight: 600; color: #1e293b; margin-bottom: 16px; display: block; font-size: 16px;">
                                <i class="fas fa-folder-open" style="margin-left: 8px; color: #667eea;"></i>
                                دسته‌بندی گزارش‌ها
                            </label>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                @foreach($categories as $category)
                                    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; transition: all 0.3s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.12)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'">
                                        <div style="padding: 16px; cursor: pointer; display: flex; align-items: center; justify-content: space-between;" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category['id'] }}" aria-expanded="false">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                                                    <i class="fas fa-folder"></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; color: #1e293b; font-size: 15px;">{{ $category['name'] }}</div>
                                                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">{{ count($category['reports']) }} گزارش</div>
                                                </div>
                                            </div>
                                            <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                                {{ count($category['reports']) }}
                                            </span>
                                        </div>
                                        <div id="collapse{{ $category['id'] }}" class="collapse" data-bs-parent="#reportAccordion">
                                            <div style="padding: 16px; background: #f8f9fa; border-top: 1px solid #e5e7eb;">
                                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                                                    @foreach($category['reports'] as $report)
                                                        <div style="background: white; padding: 16px; border-radius: 10px; border: 2px solid #e5e7eb; transition: all 0.2s; cursor: pointer;" 
                                                             onmouseover="this.style.borderColor='#667eea'; this.style.boxShadow='0 2px 8px rgba(102,126,234,0.2)'" 
                                                             onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'"
                                                             onclick="document.getElementById('report_{{ $report['id'] }}').click()">
                                                            <div style="display: flex; align-items: start; gap: 12px;">
                                                                <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    value="{{ $report['id'] }}"
                                                                    id="report_{{ $report['id'] }}"
                                                                    wire:model="selectedReports"
                                                                    style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer;"
                                                                >
                                                                <div style="flex: 1;">
                                                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                                                        <strong style="font-size: 14px; color: #1e293b; font-weight: 600;">{{ $report['title'] }}</strong>
                                                                        @if(isset($report['negative_score']) && $report['negative_score'] > 0)
                                                                            <span style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                                                                -{{ $report['negative_score'] }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @if(isset($report['description']) && $report['description'])
                                                                        <div style="font-size: 12px; color: #64748b; line-height: 1.5; margin-top: 4px;">
                                                                            {{ \Illuminate\Support\Str::limit($report['description'], 60) }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notes - Material Design -->
                        <div style="margin-bottom: 24px;">
                            <label for="notes" style="font-weight: 600; color: #1e293b; margin-bottom: 12px; display: block; font-size: 16px;">
                                <i class="fas fa-sticky-note" style="margin-left: 8px; color: #f59e0b;"></i>
                                توضیحات اضافی
                            </label>
                            <div style="position: relative;">
                                <textarea
                                    class="form-control"
                                    id="notes"
                                    rows="4"
                                    wire:model="notes"
                                    placeholder="توضیحات اختیاری..."
                                    style="border-radius: 12px; border: 2px solid #e5e7eb; padding: 16px; font-size: 14px; transition: all 0.2s; resize: none;"
                                    onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
                                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'"
                                ></textarea>
                            </div>
                        </div>

                        <!-- نمایش پاسخ دیتابیس -->
                        @if($databaseResponse)
                            <div style="margin-bottom: 24px; margin-top: 24px; border-top: 2px solid #e5e7eb; padding-top: 24px;">
                                @if($databaseResponse['success'])
                                    <div style="background: #d1fae5; padding: 16px; border-radius: 12px; border-right: 4px solid #10b981; margin-bottom: 16px;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 20px;"></i>
                                            <strong style="color: #059669; font-size: 16px;">{{ $databaseResponse['message'] }}</strong>
                                        </div>
                                        <div style="color: #047857; font-size: 13px;">تعداد: {{ count($databaseResponse['reports'] ?? []) }} گزارش</div>
                                    </div>

                                    @if(!empty($databaseResponse['reports']))
                                        <div style="max-height: 400px; overflow-y: auto;">
                                            @foreach($databaseResponse['reports'] as $index => $report)
                                                <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; margin-bottom: 12px; border-right: 3px solid #10b981;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                        <strong style="color: #059669; font-size: 14px;">گزارش #{{ $index + 1 }}</strong>
                                                        <span style="background: #10b981; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">ID: {{ $report['id'] }}</span>
                                                    </div>

                                                    <div style="background: white; padding: 12px; border-radius: 6px; margin-bottom: 10px;">
                                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
                                                            <div><strong style="color: #666;">گزارش:</strong> <span style="color: #1f2937;">{{ $report['report_title'] ?? 'نامشخص' }}</span></div>
                                                            <div><strong style="color: #666;">دسته‌بندی:</strong> <span style="color: #1f2937;">{{ $report['category_name'] ?? 'بدون دسته' }}</span></div>
                                                            <div><strong style="color: #666;">اقامت‌گر:</strong> <span style="color: #1f2937;">{{ $report['resident_name'] ?? '-' }}</span></div>
                                                            <div><strong style="color: #666;">تلفن:</strong> <span style="color: #1f2937; direction: ltr; text-align: right;">{{ $report['phone'] ?? '-' }}</span></div>
                                                            <div><strong style="color: #666;">واحد:</strong> <span style="color: #1f2937;">{{ $report['unit_name'] ?? '-' }}</span></div>
                                                            <div><strong style="color: #666;">اتاق:</strong> <span style="color: #1f2937;">{{ $report['room_name'] ?? '-' }}</span></div>
                                                            <div><strong style="color: #666;">تخت:</strong> <span style="color: #1f2937;">{{ $report['bed_name'] ?? '-' }}</span></div>
                                                            @if(!empty($report['notes']))
                                                                <div style="grid-column: 1 / -1;"><strong style="color: #666;">توضیحات:</strong> <span style="color: #1f2937;">{{ $report['notes'] }}</span></div>
                                                            @endif
                                                            <div style="grid-column: 1 / -1;"><strong style="color: #666;">تاریخ ثبت:</strong> <span style="color: #1f2937;">{{ jalaliDate($report['created_at'] ?? now(), 'Y/m/d H:i:s') }}</span></div>
                                                        </div>
                                                    </div>

                                                    <!-- نمایش تمام داده‌های رکورد از دیتابیس -->
                                                    <details style="margin-top: 10px;">
                                                        <summary style="cursor: pointer; color: #3b82f6; font-weight: 600; font-size: 12px; padding: 8px; background: #dbeafe; border-radius: 4px;">
                                                            <i class="fas fa-database" style="margin-left: 5px;"></i>
                                                            <strong>پاسخ کامل دیتابیس - نمایش تمام داده‌های ثبت شده</strong>
                                                        </summary>
                                                        <div style="margin-top: 8px; padding: 12px; background: white; border-radius: 6px; border: 1px solid #d1fae5;">
                                                            <div style="color: #666; font-size: 11px; margin-bottom: 8px;">
                                                                <i class="fas fa-info-circle" style="margin-left: 5px;"></i>
                                                                این پاسخ کامل دیتابیس است که پس از ثبت گزارش برگردانده شده:
                                                            </div>
                                                            <pre style="margin: 0; font-size: 10px; color: #374151; direction: ltr; text-align: left; max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #e5e7eb; white-space: pre-wrap; word-wrap: break-word;">{{ json_encode($report['all_data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                        </div>
                                                    </details>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div style="background: #fee2e2; padding: 16px; border-radius: 12px; border-right: 4px solid #ef4444;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                            <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px;"></i>
                                            <strong style="color: #dc2626; font-size: 16px;">خطا در ثبت گزارش</strong>
                                        </div>
                                        <div style="color: #991b1b; font-size: 13px; white-space: pre-wrap;">{{ $databaseResponse['message'] }}</div>
                                        @if(!empty($databaseResponse['error_details']))
                                            <details style="margin-top: 10px;">
                                                <summary style="cursor: pointer; color: #dc2626; font-size: 12px;">جزئیات خطا</summary>
                                                <pre style="margin-top: 8px; padding: 10px; background: white; border-radius: 4px; font-size: 11px; direction: ltr; text-align: left; max-height: 200px; overflow-y: auto;">{{ json_encode($databaseResponse['error_details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </details>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Footer - Material Design -->
                    <div style="padding: 20px 24px; background: white; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" wire:click="closeModal" style="background: #f1f5f9; color: #64748b; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                            <i class="fas fa-times"></i> انصراف
                        </button>
                        <button type="button" wire:click="submitReport" wire:loading.attr="disabled" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(102,126,234,0.3);" onmouseover="this.style.boxShadow='0 6px 16px rgba(102,126,234,0.4)'" onmouseout="this.style.boxShadow='0 4px 12px rgba(102,126,234,0.3)'">
                            <span wire:loading.remove wire:target="submitReport">
                                <i class="fas fa-save"></i> ثبت گزارش
                            </span>
                            <span wire:loading wire:target="submitReport">
                                <i class="fas fa-spinner fa-spin"></i> در حال ثبت...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // لاگ پاسخ دیتابیس در کنسول
        window.addEventListener('logDatabaseResponse', event => {
            const response = event.detail;
            console.log('=== پاسخ دیتابیس ===');
            console.log('وضعیت:', response.success ? '✅ موفق' : '❌ خطا');
            
            if (response.success) {
                console.log('تعداد گزارش‌های ثبت شده:', response.count);
                console.log('گزارش‌های ثبت شده:', response.reports);
                
                // نمایش JSON کامل در کنسول
                console.log('پاسخ کامل دیتابیس (JSON):');
                console.log(JSON.stringify(response.reports, null, 2));
                
                // نمایش جزئیات هر گزارش
                if (response.reports && response.reports.length > 0) {
                    response.reports.forEach((report, index) => {
                        console.log(`\n--- گزارش #${index + 1} ---`);
                        console.log('ID:', report.id);
                        console.log('گزارش:', report.report_title);
                        console.log('دسته‌بندی:', report.category_name);
                        console.log('اقامت‌گر:', report.resident_name);
                        console.log('تلفن:', report.phone);
                        console.log('واحد:', report.unit_name);
                        console.log('اتاق:', report.room_name);
                        console.log('تخت:', report.bed_name);
                        console.log('تمام داده‌ها:', report.all_data);
                    });
                }
            } else {
                console.error('خطا:', response.error);
                if (response.error_details) {
                    console.error('جزئیات خطا:', response.error_details);
                }
            }
            
            console.log('===================');
        });
    </script>
</div>
