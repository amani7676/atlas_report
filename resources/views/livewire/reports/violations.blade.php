<div>
    <!-- استایل‌های سفارشی برای صفحه‌بندی زیبا -->
    <style>
        .custom-pagination .page-link {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 3px;
            border: 1px solid #dee2e6;
            color: #0d6efd;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
        }

        .custom-pagination .page-link:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .custom-pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.4);
        }

        .custom-pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }

        .custom-pagination .page-link i {
            font-size: 0.75rem;
        }

        /* استایل‌های ریسپانسیو برای موبایل */
        @media (max-width: 768px) {
            .custom-pagination .page-link {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }
        }
    </style>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2><i class="fas fa-exclamation-triangle"></i> گزارش‌های تخلفات</h2>
            <div style="display: flex; gap: 10px;">
                <button wire:click="toggleStatistics" class="btn {{ $showStatistics ? 'btn-success' : 'btn-outline-success' }}">
                    <i class="fas fa-chart-bar"></i> {{ $showStatistics ? 'مخفی کردن آمار' : 'نمایش آمار' }}
                </button>
            </div>
        </div>

        <!-- بخش تنظیمات در صفحه اصلی -->
        <div class="card mb-4" style="border: 1px solid #e3e6f0;">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="fas fa-cog"></i> تنظیمات نمایش آمار تخلفات</h5>
            </div>
            <div class="card-body">
                <form wire:submit="updateSettings">
                    <div class="row">
                        <!-- حداقل تعداد تکرار تخلف -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-exclamation-circle"></i>
                                حداقل تعداد تکرار تخلف
                                <small class="text-muted">(برای نمایش در جدول تخلفات تکراری)</small>
                            </label>
                            <input 
                                type="number" 
                                wire:model="minViolationCount" 
                                class="form-control" 
                                min="1" 
                                required
                            >
                            @error('minViolationCount') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- حداقل تعداد گزارش اقامت‌گر -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-clipboard-list"></i>
                                حداقل تعداد گزارش اقامت‌گر
                                <small class="text-muted">(برای نمایش در جدول گزارش‌های اقامت‌گران)</small>
                            </label>
                            <input 
                                type="number" 
                                wire:model="minReportCount" 
                                class="form-control" 
                                min="1" 
                                required
                            >
                            @error('minReportCount') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- حداکثر امتیاز تخلف برای اقامت‌گران برتر -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-star"></i>
                                حداکثر امتیاز تخلف برای اقامت‌گران برتر
                                <small class="text-muted">(اقامت‌گرانی با امتیاز کمتر یا مساوی این مقدار نمایش داده می‌شوند)</small>
                            </label>
                            <input 
                                type="number" 
                                wire:model="maxViolationScore" 
                                class="form-control" 
                                min="0" 
                                required
                            >
                            @error('maxViolationScore') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <!-- تنظیمات بخش خلاصه آمار -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                <i class="fas fa-chart-pie"></i>
                                تعداد برتر برای نمایش
                                <small class="text-muted">(تعداد نفرات برتر در جدول خلاصه آمار)</small>
                            </label>
                            <input 
                                type="number" 
                                wire:model="summaryTopCount" 
                                class="form-control" 
                                min="1" 
                                max="50"
                                required
                            >
                            @error('summaryTopCount') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>

                        <!-- نمایش بخش‌های مختلف -->
                        <div class="col-md-8 mb-3">
                            <label class="form-label">
                                <i class="fas fa-eye"></i>
                                بخش‌های قابل نمایش
                            </label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="showMostRepeatedViolation" 
                                            class="form-check-input" 
                                            id="showMostRepeatedViolation"
                                        >
                                        <label class="form-check-label small" for="showMostRepeatedViolation">
                                            بیشترین تکرار تخلف
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="showMostViolationsPerson" 
                                            class="form-check-input" 
                                            id="showMostViolationsPerson"
                                        >
                                        <label class="form-check-label small" for="showMostViolationsPerson">
                                            بیشترین تخلفات شخص
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            wire:model.live="showAllPersonViolations" 
                                            class="form-check-input" 
                                            id="showAllPersonViolations"
                                        >
                                        <label class="form-check-label small" for="showAllPersonViolations">
                                            جدول کامل تخلفات
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ذخیره تنظیمات در دیتابیس
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <!-- Search -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">جستجو</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="نام اقامت‌گر، عنوان گزارش..."
                        class="form-control"
                        style="width: 100%;"
                    >
                </div>

                <!-- Report Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">گزارش</label>
                    <select wire:model.live="reportFilter" class="form-control" style="width: 100%;">
                        <option value="">همه</option>
                        @foreach($this->reportsList as $report)
                            <option value="{{ $report->id }}">{{ $report->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">تاریخ</label>
                    <input
                        type="date"
                        wire:model.live="dateFilter"
                        class="form-control"
                        style="width: 100%;"
                    >
                </div>

                <!-- Clear Filters -->
                <div style="display: flex; align-items: flex-end;">
                    <button wire:click="clearFilters" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-times"></i> پاک کردن فیلترها
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        @if($showStatistics && $statistics)
            <div style="margin-bottom: 20px;">
                <!-- جدول خلاصه آمار تخلفات -->
                <div class="card mb-4" style="border: 1px solid #e3e6f0;">
                    <div class="card-header" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white;">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> خلاصه آمار تخلفات</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- 1. بیشترین تکرار یک تخلف یکسان -->
                            @if($showMostRepeatedViolation)
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-danger" style="border-left: 4px solid #dc3545;">
                                    <div class="card-body">
                                        <h6 class="card-title text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> بیشترین تکرار تخلف
                                        </h6>
                                        @if($statistics['summaryStats']['mostRepeatedViolation'])
                                            <p class="card-text">
                                                <strong>گزارش:</strong> {{ $statistics['summaryStats']['mostRepeatedViolation']->report_title }}<br>
                                                <strong>شخص:</strong> {{ $statistics['summaryStats']['mostRepeatedViolation']->resident_name }}<br>
                                                <strong class="text-danger">تعداد تکرار:</strong> 
                                                <span class="badge bg-danger">{{ $statistics['summaryStats']['mostRepeatedViolation']->count }} بار</span>
                                            </p>
                                        @else
                                            <p class="card-text text-muted">موردی یافت نشد</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- 2. بیشترین تعداد تخلف برای یک شخص -->
                            @if($showMostViolationsPerson)
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-warning" style="border-left: 4px solid #ffc107;">
                                    <div class="card-body">
                                        <h6 class="card-title text-warning">
                                            <i class="fas fa-user-times"></i> بیشترین تخلفات شخص
                                        </h6>
                                        @if($statistics['summaryStats']['mostViolationsPerson'])
                                            <p class="card-text">
                                                <strong>شخص:</strong> {{ $statistics['summaryStats']['mostViolationsPerson']->resident_name }}<br>
                                                <strong class="text-warning">تعداد تخلفات:</strong> 
                                                <span class="badge bg-warning">{{ $statistics['summaryStats']['mostViolationsPerson']->violation_count }} مورد</span><br>
                                                <strong class="text-danger">مجموع امتیاز:</strong> 
                                                <span class="badge bg-danger">{{ $statistics['summaryStats']['mostViolationsPerson']->total_score }}</span>
                                            </p>
                                        @else
                                            <p class="card-text text-muted">موردی یافت نشد</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- 3. آمار کلی -->
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-info" style="border-left: 4px solid #17a2b8;">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">
                                            <i class="fas fa-info-circle"></i> آمار کلی
                                        </h6>
                                        <p class="card-text">
                                            <strong>تعداد کل افراد:</strong> 
                                            <span class="badge bg-info">{{ $statistics['summaryStats']['allPersonViolations']->count() }} نفر</span><br>
                                            <strong class="text-success">متوسط تخلف:</strong> 
                                            <span class="badge bg-success">
                                                {{ $statistics['summaryStats']['allPersonViolations']->avg('violation_count') ? number_format($statistics['summaryStats']['allPersonViolations']->avg('violation_count'), 1) : 0 }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- جدول کامل مجموع تخلفات هر شخص -->
                        @if($showAllPersonViolations)
                        <div class="mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-list-ol"></i> مجموع تخلفات هر شخص ({{ $summaryTopCount }} نفر برتر - مرتب شده بر اساس امتیاز)
                            </h6>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ردیف</th>
                                            <th>نام شخص</th>
                                            <th class="text-center">تعداد تخلفات</th>
                                            <th class="text-center">مجموع امتیاز</th>
                                            <th class="text-center">کل گزارش‌ها</th>
                                            <th class="text-center">وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($statistics['summaryStats']['allPersonViolations'] as $index => $person)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $person->resident_name }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $person->violation_count > 5 ? 'bg-danger' : ($person->violation_count > 2 ? 'bg-warning' : 'bg-success') }}">
                                                        {{ $person->violation_count }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $person->total_score > 10 ? 'bg-danger' : ($person->total_score > 5 ? 'bg-warning' : 'bg-success') }}">
                                                        {{ $person->total_score }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $person->total_reports }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($person->total_score >= $redThreshold || ($person->max_repeated_violation_count ?? 0) >= $redViolationCountThreshold)
                                                        <span class="badge bg-danger">کارت قرمز</span>
                                                    @elseif($person->total_score >= $yellowThreshold || ($person->max_repeated_violation_count ?? 0) >= $yellowViolationCountThreshold)
                                                        <span class="badge bg-warning">کارت زرد</span>
                                                    @else
                                                        <span class="badge bg-info">بدون کارت</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">موردی یافت نشد</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
                    
                    <!-- تخلفات تکراری -->
                    <div class="card" style="border: 1px solid #e3e6f0;">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h6 class="mb-0"><i class="fas fa-exclamation-circle"></i> تخلفات تکراری (حداقل {{ $minViolationCount }} بار)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>اقامت‌گر</th>
                                            <th>گزارش</th>
                                            <th class="text-center">تعداد</th>
                                            <th class="text-center">امتیاز</th>
                                            <th>توضیحات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($statistics['repeatedViolations'] as $violation)
                                            <tr>
                                                <td>{{ $violation->resident_name }}</td>
                                                <td>{{ $violation->report_title }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger">{{ $violation->violation_count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning">{{ $violation->total_score }}</span>
                                                </td>
                                                <td>
                                                    <div style="max-width: 150px; word-wrap: break-word;">
                                                        @if(!empty($violation->last_description))
                                                            <small class="text-muted">{{ Str::limit($violation->last_description, 50) }}</small>
                                                        @else
                                                            <small class="text-muted">-</small>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">موردی یافت نشد</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- گزارش‌های اقامت‌گران -->
                    <div class="card" style="border: 1px solid #e3e6f0;">
                        <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                            <h6 class="mb-0"><i class="fas fa-clipboard-list"></i> گزارش‌های اقامت‌گران (حداقل {{ $minReportCount }} گزارش)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>اقامت‌گر</th>
                                            <th class="text-center">تعداد کل</th>
                                            <th class="text-center">امتیاز تخلف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($statistics['residentReports'] as $report)
                                            <tr>
                                                <td>{{ $report->resident_name }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $report->report_count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $report->total_violation_score > 0 ? 'bg-danger' : 'bg-success' }}">
                                                        {{ $report->total_violation_score }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">موردی یافت نشد</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- اقامت‌گران برتر -->
                    <div class="card" style="border: 1px solid #e3e6f0;">
                        <div class="card-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                            <h6 class="mb-0"><i class="fas fa-star"></i> اقامت‌گران برتر (حداکثر {{ $maxViolationScore }} امتیاز تخلف)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>نام کامل</th>
                                            <th>شماره تلفن</th>
                                            <th class="text-center">کل گزارش‌ها</th>
                                            <th class="text-center">امتیاز تخلف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($statistics['topResidents'] as $resident)
                                            <tr>
                                                <td>{{ $resident->resident_full_name }}</td>
                                                <td>{{ $resident->resident_phone }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">{{ $resident->total_reports }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success">{{ $resident->total_violation_score }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">موردی یافت نشد</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        <!-- Reports Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>اقامت‌گر</th>
                        <th>گزارش</th>
                        <th>امتیاز تخلف</th>
                        <th>توضیحات گزارش</th>
                        <th>تاریخ ثبت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->resident_name }}</strong>
                                @if($report->resident)
                                    <br><small class="text-muted">{{ $report->resident->resident_full_name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-danger">{{ $report->report->title }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">{{ $report->report->negative_score }}</span>
                            </td>
                            <td>
                                <div style="max-width: 200px; word-wrap: break-word;">
                                    @if(!empty($report->description))
                                        <span class="text-muted">{{ Str::limit($report->description, 100) }}</span>
                                        @if(strlen($report->description) > 100)
                                            <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="tooltip" title="{{ $report->description }}">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ jalaliDate($report->created_at, 'Y/m/d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">گزارشی یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($reports->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    <nav class="custom-pagination">
                        {{ $reports->links() }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>
