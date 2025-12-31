<div>
    @section('title', 'اقامت‌گران با سررسید پرداخت امروز')

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="fas fa-calendar-times text-danger"></i>
                اقامت‌گران با سررسید پرداخت امروز
            </h4>
            <div class="badge bg-danger">
                {{ $residents->total() }} مورد
            </div>
        </div>

        <!-- جستجو -->
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    type="text" 
                    class="form-control" 
                    placeholder="جستجو بر اساس نام، تلفن یا کد ملی..."
                    wire:model.live.debounce.300ms="search"
                >
            </div>
        </div>

        <!-- جدول -->
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>نام</th>
                        <th>تلفن</th>
                        <th>کد ملی</th>
                        <th>واحد</th>
                        <th>اتاق</th>
                        <th>تخت</th>
                        <th>تاریخ سررسید پرداخت</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($residents as $index => $resident)
                        <tr>
                            <td>{{ $residents->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $resident->resident_full_name ?? 'نامشخص' }}</strong>
                            </td>
                            <td>{{ $resident->resident_phone ?? '-' }}</td>
                            <td>-</td>
                            <td>{{ $resident->unit_name ?? '-' }}</td>
                            <td>{{ $resident->room_name ?? '-' }}</td>
                            <td>{{ $resident->bed_name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-danger">
                                    {{ $resident->contract_payment_date_jalali ?? ($resident->contract_payment_date ? jalaliDate($resident->contract_payment_date, 'Y/m/d') : '-') }}
                                </span>
                            </td>
                            <td>
                                @if($resident->contract_state === 'active')
                                    <span class="badge bg-success">فعال</span>
                                @else
                                    <span class="badge bg-secondary">غیرفعال</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>هیچ اقامت‌گری با سررسید پرداخت امروز یافت نشد</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- صفحه‌بندی -->
        @if($residents->hasPages())
            <div class="mt-3">
                {{ $residents->links() }}
            </div>
        @endif
    </div>
</div>


