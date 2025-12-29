<div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>پیام‌های ارسال شده</h2>
        </div>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div style="background: linear-gradient(135deg, #4361ee, #3a0ca3); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;">{{ $statusCounts['all'] }}</div>
                <div style="font-size: 14px; opacity: 0.9;">همه پیام‌ها</div>
            </div>
            <div style="background: linear-gradient(135deg, #4cc9f0, #2db8d9); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;">{{ $statusCounts['sent'] }}</div>
                <div style="font-size: 14px; opacity: 0.9;">ارسال شده</div>
            </div>
            <div style="background: linear-gradient(135deg, #f72585, #d1145a); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;">{{ $statusCounts['failed'] }}</div>
                <div style="font-size: 14px; opacity: 0.9;">ناموفق</div>
            </div>
            <div style="background: linear-gradient(135deg, #ff9e00, #ff8500); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;">{{ $statusCounts['pending'] }}</div>
                <div style="font-size: 14px; opacity: 0.9;">در انتظار</div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-search" style="color: #666;"></i>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="جستجو بر اساس نام، شماره تلفن، عنوان..."
                        class="form-control"
                        style="width: 300px;"
                    >
                </div>

                <select wire:model.live="statusFilter" class="form-control" style="width: 150px;">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="sent">ارسال شده</option>
                    <option value="failed">ناموفق</option>
                    <option value="pending">در انتظار</option>
                </select>

                @if($search || $statusFilter)
                    <button wire:click="resetFilters" class="btn" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> پاک کردن فیلترها
                    </button>
                @endif
            </div>

            @if(count($selectedIds) > 0)
                <button wire:click="resendMultipleSms" class="btn btn-success">
                    <i class="fas fa-redo"></i>
                    ارسال مجدد {{ count($selectedIds) }} پیام ناموفق
                </button>
            @endif
        </div>

        <!-- Messages Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                style="cursor: pointer;"
                            >
                        </th>
                        <th wire:click="sortBy('resident_name')" style="cursor: pointer;">
                            نام اقامت‌گر
                            @if($sortField === 'resident_name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('phone')" style="cursor: pointer;">
                            شماره تلفن
                            @if($sortField === 'phone')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('title')" style="cursor: pointer;">
                            عنوان پیام
                            @if($sortField === 'title')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>گزارش</th>
                        <th>نوع</th>
                        <th wire:click="sortBy('status')" style="cursor: pointer;">
                            وضعیت
                            @if($sortField === 'status')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('sent_at')" style="cursor: pointer;">
                            تاریخ ارسال
                            @if($sortField === 'sent_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                            تاریخ ایجاد
                            @if($sortField === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sentMessages as $sentMessage)
                        <tr>
                            <td>
                                @if($sentMessage->status === 'failed')
                                    <input
                                        type="checkbox"
                                        wire:model.live="selectedIds"
                                        value="{{ $sentMessage->id }}"
                                        style="cursor: pointer;"
                                    >
                                @endif
                            </td>
                            <td>
                                <strong>{{ $sentMessage->resident_name ?? 'بدون نام' }}</strong>
                            </td>
                            <td>{{ $sentMessage->phone ?? 'بدون شماره' }}</td>
                            <td>
                                <div>
                                    <strong>{{ $sentMessage->title ?? 'بدون عنوان' }}</strong>
                                    @if($sentMessage->description)
                                        <br>
                                        <small style="color: #666;">{{ \Illuminate\Support\Str::limit($sentMessage->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($sentMessage->report)
                                    <span style="background: #4361ee; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block;">
                                        <i class="fas fa-file-alt"></i> {{ $sentMessage->report->title }}
                                    </span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sentMessage->is_pattern)
                                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block;">
                                        <i class="fas fa-code"></i> الگویی
                                    </span>
                                    @if($sentMessage->pattern)
                                        <br><small style="color: #666; margin-top: 3px; display: block;">کد: {{ $sentMessage->pattern->pattern_code }}</small>
                                    @endif
                                @else
                                    <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; display: inline-block;">
                                        <i class="fas fa-envelope"></i> عادی
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($sentMessage->status === 'sent')
                                    <div>
                                        <span style="background: #4cc9f0; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block;">
                                            <i class="fas fa-check-circle"></i> ارسال شده
                                        </span>
                                        @if($sentMessage->response_code)
                                            <br><small style="color: #666; margin-top: 5px; display: block;">کد: {{ $sentMessage->response_code }}</small>
                                        @endif
                                    </div>
                                @elseif($sentMessage->status === 'failed')
                                    <div>
                                        <span style="background: #f72585; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block;">
                                            <i class="fas fa-times-circle"></i> ناموفق
                                        </span>
                                        @if($sentMessage->response_code)
                                            <br><small style="color: #666; margin-top: 5px; display: block;">کد: {{ $sentMessage->response_code }}</small>
                                        @endif
                                    </div>
                                @else
                                    <span style="background: #ff9e00; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                        <i class="fas fa-clock"></i> در انتظار
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($sentMessage->sent_at)
                                    {{ jalaliDate($sentMessage->sent_at, 'Y/m/d H:i') }}
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td>{{ jalaliDate($sentMessage->created_at, 'Y/m/d H:i') }}</td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    @if($sentMessage->status === 'failed')
                                        <button
                                            wire:click="resendSms({{ $sentMessage->id }})"
                                            class="btn btn-success btn-sm"
                                            title="ارسال مجدد"
                                        >
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    @endif
                                    @if($sentMessage->error_message)
                                        <button
                                            onclick="showError({{ json_encode([
                                                'success' => false,
                                                'response_code' => $sentMessage->response_code,
                                                'message' => $sentMessage->error_message,
                                                'raw_response' => $sentMessage->raw_response,
                                                'api_response' => $sentMessage->api_response
                                            ]) }})"
                                            class="btn btn-danger btn-sm"
                                            title="مشاهده خطا"
                                        >
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                                <p>هیچ پیامی یافت نشد</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($sentMessages->hasPages())
            <div style="margin-top: 20px; padding: 15px; background: white; border-top: 1px solid #dee2e6; border-radius: 0 0 10px 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div style="color: #6c757d; font-size: 14px;">
                    نمایش
                    <strong>{{ $sentMessages->firstItem() ?? 0 }}</strong>
                    تا
                    <strong>{{ $sentMessages->lastItem() ?? 0 }}</strong>
                    از
                    <strong>{{ $sentMessages->total() }}</strong>
                    نتیجه
                </div>
                {{-- صفحه‌بندی سفارشی --}}
                <nav aria-label="Page navigation">
                    <ul class="pagination custom-pagination mb-0">
                        {{-- دکمه "قبلی" --}}
                        <li class="page-item {{ $sentMessages->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="#" wire:click="previousPage" tabindex="-1"
                                aria-disabled="{{ $sentMessages->onFirstPage() ? 'true' : 'false' }}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>

                        {{-- شماره صفحات --}}
                        @foreach ($sentMessages->getUrlRange(1, $sentMessages->lastPage()) as $page => $url)
                            @if ($page == $sentMessages->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="#" wire:click="gotoPage({{ $page }})">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- دکمه "بعدی" --}}
                        <li class="page-item {{ !$sentMessages->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="#" wire:click="nextPage"
                                aria-disabled="{{ !$sentMessages->hasMorePages() ? 'true' : 'false' }}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>
</div>