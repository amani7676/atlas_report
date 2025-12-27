<div>
    <div class="card">
        <h2>داشبورد مدیریت</h2>
        <p>سیستم گزارش‌گیری اقامت‌گران</p>
    </div>

    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="stats-card">
            <i class="fas fa-file-alt" style="font-size: 24px;"></i>
            <div class="stats-number">{{ $totalReports }}</div>
            <div class="stats-label">گزارش‌های ثبت‌شده</div>
        </div>

        <div class="stats-card" style="background: linear-gradient(135deg, #4cc9f0, #2db8d9);">
            <i class="fas fa-list" style="font-size: 24px;"></i>
            <div class="stats-number">{{ $totalCategories }}</div>
            <div class="stats-label">دسته‌بندی‌ها</div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px;">آخرین گزارش‌ها</h3>

        @if($recentReports->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>دسته‌بندی</th>
                            <th>نمره منفی</th>
                            <th>تاریخ ایجاد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReports as $report)
                            <tr>
                                <td>{{ $report->title }}</td>
                                <td>{{ $report->category->name }}</td>
                                <td>
                                    <span style="color: #f72585; font-weight: bold;">
                                        {{ $report->negative_score }}
                                    </span>
                                </td>
                                <td>{{ $report->created_at->format('Y/m/d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <p>هنوز هیچ گزارشی ثبت نشده است</p>
            </div>
        @endif
    </div>
</div>
