<div>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="container-fluid py-3" dir="rtl">
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

    <!-- Report Modal -->
    @if($showReportModal)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-file-alt me-2"></i>
                            @if($reportType === 'individual')
                                ثبت گزارش برای {{ $currentResident['name'] }}
                            @else
                                ثبت گزارش گروهی برای {{ count($selectedResidents) }} اقامت‌گر
                            @endif
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Resident/Room Info -->
                        @if($reportType === 'individual')
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>اقامت‌گر:</strong> {{ $currentResident['name'] }}<br>
                                        <strong>تلفن:</strong> {{ $currentResident['phone'] }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>اتاق:</strong> {{ $currentResident['room_name'] }}<br>
                                        <strong>تخت:</strong> {{ $currentResident['bed_name'] }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <strong>تعداد اقامت‌گران انتخاب شده:</strong> {{ count($selectedResidents) }} نفر
                            </div>
                        @endif

                        <!-- Categories and Reports -->
                        <div class="mb-3">
                            <label class="form-label"><strong>دسته‌بندی گزارش‌ها:</strong></label>
                            <div class="accordion" id="reportAccordion">
                                @foreach($categories as $category)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category['id'] }}">
                                                <i class="fas fa-folder me-2"></i>
                                                {{ $category['name'] }}
                                                <span class="badge bg-secondary ms-2">{{ count($category['reports']) }}</span>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $category['id'] }}" class="accordion-collapse collapse" data-bs-parent="#reportAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    @foreach($category['reports'] as $report)
                                                        <div class="col-md-6 mb-2">
                                                            <div class="form-check">
                                                                <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    value="{{ $report['id'] }}"
                                                                    id="report_{{ $report['id'] }}"
                                                                    wire:model="selectedReports"
                                                                >
                                                                <label class="form-check-label" for="report_{{ $report['id'] }}">
                                                                    <div>
                                                                        <strong>{{ $report['title'] }}</strong>
                                                                        <span class="badge bg-danger ms-2">{{ $report['negative_score'] ?? 0 }}</span>
                                                                    </div>
                                                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($report['description'] ?? '', 50) }}</small>
                                                                </label>
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

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label"><strong>توضیحات اضافی:</strong></label>
                            <textarea
                                class="form-control"
                                id="notes"
                                rows="3"
                                wire:model="notes"
                                placeholder="توضیحات اختیاری..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="fas fa-times me-1"></i> انصراف
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="submitReport">
                            <i class="fas fa-save me-1"></i> ثبت گزارش
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
    </script>
</div>
