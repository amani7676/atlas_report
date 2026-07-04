<div>
    @section('title', 'مدیریت API')

    <style>
        .api-manager-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        .section-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .residents-table {
            width: 100%;
            border-collapse: collapse;
        }

        .residents-table th,
        .residents-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .residents-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .residents-table tr:hover {
            background: #f9fafb;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .resident-detail {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .detail-item label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .detail-item span {
            font-weight: 600;
            color: #1f2937;
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .reports-table th,
        .reports-table td {
            padding: 10px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .reports-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }

        .endpoint-name {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .input-group {
            margin-bottom: 12px;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
        }

        .input-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>

    <div class="api-manager-container">
        <h1 style="color: #1f2937; margin-bottom: 24px;">مدیریت API</h1>

        <!-- بخش APIهای موجود -->
        <div class="section-card">
            <h2 style="color: #1f2937; margin-bottom: 16px;">APIهای موجود</h2>
            <p style="color: #6b7280; margin-bottom: 16px;">لیست تمام APIهای موجود برای دریافت اطلاعات کاربران و تخلفات</p>
            
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>نام API</th>
                        <th>مسیر</th>
                        <th>توضیحات</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>دریافت اطلاعات یک کاربر</strong></td>
                        <td>
                            <span class="endpoint-name">/api/resident/{residentId}</span>
                        </td>
                        <td>دریافت اطلاعات کامل یک کاربر همراه با تخلفات و گزارش‌های اطلاع‌رسانی</td>
                        <td>
                            <a href="/api/resident/1" target="_blank" class="btn btn-primary btn-sm">تست</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>دریافت همه کاربران</strong></td>
                        <td>
                            <span class="endpoint-name">/api/residents/all</span>
                        </td>
                        <td>دریافت اطلاعات همه کاربران همراه با تخلفات و گزارش‌های اطلاع‌رسانی</td>
                        <td>
                            <a href="/api/residents/all" target="_blank" class="btn btn-primary btn-sm">تست</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>لیست گزارش‌ها با Endpoint</strong></td>
                        <td>
                            <span class="endpoint-name">/api/reports/endpoints</span>
                        </td>
                        <td>دریافت لیست همه گزارش‌ها همراه با نام endpoint</td>
                        <td>
                            <a href="/api/reports/endpoints" target="_blank" class="btn btn-primary btn-sm">تست</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>همگام‌سازی کاربران</strong></td>
                        <td>
                            <span class="endpoint-name">POST /api/residents/sync</span>
                        </td>
                        <td>همگام‌سازی کاربران از API خارجی</td>
                        <td>
                            <span style="color: #6b7280; font-size: 12px;">فقط POST</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($message)
            <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'error' }}">
                {{ $message }}
            </div>
        @endif

        <!-- بخش مدیریت Endpoint های گزارش‌ها -->
        <div class="section-card">
            <h2 style="color: #1f2937; margin-bottom: 16px;">مدیریت Endpoint های گزارش‌ها</h2>
            <p style="color: #6b7280; margin-bottom: 16px;">هر گزارش می‌تواند یک نام endpoint اختصاصی داشته باشد که در API استفاده می‌شود.</p>
            
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>عنوان گزارش</th>
                        <th>دسته‌بندی</th>
                        <th>نام Endpoint</th>
                        <th>تخلفات این گزارش</th>
                        <th>تعداد کاربران</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                        @if ($editingEndpoint == $report->id)
                            <tr>
                                <td colspan="6">
                                    <div style="background: #f9fafb; padding: 16px; border-radius: 6px;">
                                        <form wire:submit.prevent="updateEndpoint">
                                            <div class="input-group">
                                                <label>نام Endpoint:</label>
                                                <input type="text" wire:model="editEndpointName" placeholder="مثال: report-violation">
                                                @error('editEndpointName') <span style="color: #ef4444; font-size: 12px;">{{ $message }}</span> @enderror
                                            </div>
                                            <div style="display: flex; gap: 8px;">
                                                <button type="submit" class="btn btn-success btn-sm">ذخیره</button>
                                                <button type="button" wire:click="cancelEditEndpoint" class="btn btn-secondary btn-sm">لغو</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td><strong>{{ $report->title }}</strong></td>
                                <td>{{ $report->category->name ?? '-' }}</td>
                                <td>
                                    @if ($report->api_endpoint_name)
                                        <a href="/api/resident/{residentId}?endpoint={{ $report->api_endpoint_name }}" target="_blank" class="endpoint-name" style="text-decoration: none; color: #3b82f6;">
                                            {{ $report->api_endpoint_name }}
                                        </a>
                                    @else
                                        <a href="/api/resident/{residentId}?report_id={{ $report->id }}" target="_blank" class="endpoint-name" style="text-decoration: none; color: #6b7280;">
                                            /api/resident/{id}?report_id={{ $report->id }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($report->category->name === 'تخلف')
                                        <span class="badge badge-danger">{{ $report->total_violations_system ?? 0 }}</span>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td>{{ $report->affected_residents_count ?? 0 }}</td>
                                <td>
                                    <button wire:click="startEditEndpoint({{ $report->id }})" class="btn btn-primary btn-sm">ویرایش</button>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
                <h4 style="margin-bottom: 10px; color: #1f2937;">آمار کلی تخلفات</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="background: white; padding: 15px; border-radius: 6px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #dc2626;">
                            {{ $reports->first()->total_violations_all_reports ?? 0 }}
                        </div>
                        <div style="color: #6b7280; font-size: 14px;">مجموع کل تخلفات</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش لیست کاربران -->
        <div class="section-card">
            <h2 style="color: #1f2937; margin-bottom: 16px;">لیست کاربران</h2>
            
            <div class="search-box">
                <input type="text" wire:model.live="search" placeholder="جستجو بر اساس نام، شماره تلفن یا ID...">
            </div>

            <table class="residents-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام کامل</th>
                        <th>شماره تلفن</th>
                        <th>شماره تخت</th>
                        <th>شماره اتاق</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($residents as $resident)
                        <tr>
                            <td>{{ $resident->resident_id }}</td>
                            <td>{{ $resident->resident_full_name ?? '-' }}</td>
                            <td>{{ $resident->resident_phone ?? '-' }}</td>
                            <td>{{ $resident->bed_name ?? '-' }}</td>
                            <td>{{ $resident->room_name ?? '-' }}</td>
                            <td>
                                <button wire:click="selectResident({{ $resident->resident_id }})" class="btn btn-primary btn-sm">مشاهده جزئیات</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $residents->links() }}
        </div>

        <!-- بخش جزئیات کاربر انتخاب شده -->
        @if ($selectedResident && $residentData)
            <div class="section-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2 style="color: #1f2937; margin: 0;">جزئیات کاربر</h2>
                    <div style="display: flex; gap: 8px;">
                        <a href="/api/resident/{{ $residentData->resident_id }}" target="_blank" class="btn btn-success btn-sm">
                            <i class="fas fa-code"></i> تست API
                        </a>
                        <button wire:click="$set('selectedResident', null)" class="btn btn-secondary btn-sm">بستن</button>
                    </div>
                </div>

                <div class="resident-detail">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Resident ID</label>
                            <span>{{ $residentData->resident_id }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Contract ID</label>
                            <span>{{ $residentData->contract_id ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>نام کامل</label>
                            <span>{{ $residentData->resident_full_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>شماره تلفن</label>
                            <span>{{ $residentData->resident_phone ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>شماره تخت</label>
                            <span>{{ $residentData->bed_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>شماره اتاق</label>
                            <span>{{ $residentData->room_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>واحد</label>
                            <span>{{ $residentData->unit_name ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <label>مجموع تخلفات</label>
                            <span style="color: {{ $residentData->total_violations > 0 ? '#dc2626' : '#059669' }};">
                                {{ $residentData->total_violations }}
                            </span>
                        </div>
                    </div>

                    <h3 style="color: #1f2937; margin-bottom: 12px;">گزارش‌های ثبت شده</h3>
                    
                    <!-- تخلفات -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="color: #dc2626; margin-bottom: 8px;">تخلفات ({{ $residentData->total_violations ?? 0 }} امتیاز)</h4>
                        @if ($residentData->violations && $residentData->violations->count() > 0)
                            <table class="reports-table">
                                <thead>
                                    <tr>
                                        <th>عنوان گزارش</th>
                                        <th>نمره منفی</th>
                                        <th>Endpoint</th>
                                        <th>تاریخ ثبت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($residentData->violations as $residentReport)
                                        <tr>
                                            <td>{{ $residentReport->report->title ?? '-' }}</td>
                                            <td>
                                                @if ($residentReport->report->negative_score > 0)
                                                    <span class="badge badge-danger">{{ $residentReport->report->negative_score }}</span>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($residentReport->report->api_endpoint_name)
                                                    <span class="endpoint-name">{{ $residentReport->report->api_endpoint_name }}</span>
                                                @else
                                                    <span class="endpoint-name">report_id={{ $residentReport->report->id }}</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($residentReport->created_at)->format('Y/m/d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p style="color: #6b7280;">هیچ تخلفی برای این کاربر ثبت نشده است.</p>
                        @endif
                    </div>

                    <!-- اطلاع‌رسانی‌ها -->
                    <div>
                        <h4 style="color: #3b82f6; margin-bottom: 8px;">گزارش‌های اطلاع‌رسانی</h4>
                        @if ($residentData->notifications && $residentData->notifications->count() > 0)
                            <table class="reports-table">
                                <thead>
                                    <tr>
                                        <th>عنوان گزارش</th>
                                        <th>Endpoint</th>
                                        <th>تاریخ ثبت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($residentData->notifications as $residentReport)
                                        <tr>
                                            <td>{{ $residentReport->report->title ?? '-' }}</td>
                                            <td>
                                                @if ($residentReport->report->api_endpoint_name)
                                                    <span class="endpoint-name">{{ $residentReport->report->api_endpoint_name }}</span>
                                                @else
                                                    <span class="endpoint-name">report_id={{ $residentReport->report->id }}</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($residentReport->created_at)->format('Y/m/d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p style="color: #6b7280;">هیچ گزارش اطلاع‌رسانی برای این کاربر ثبت نشده است.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
