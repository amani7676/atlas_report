<div>
    <!-- استایل‌های خارجی -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

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

        /* استایل‌های زیبا برای جدول گزارش‌ها */
        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* استایل‌های ویژه برای سه جدول اول */
        .stats-tables .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .stats-tables .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .stats-tables .card-header {
            border-bottom: 2px solid #e9ecef;
            font-weight: 500;
        }

        .stats-tables .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        .stats-tables .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        .table tbody tr:hover:not(.selected-row) {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        /* استایل برای ردیف‌های انتخاب شده - باید بعد از hover باشد */
        .table tbody tr.selected-row {
            background-color: #CBF3BB !important;
        }
        
        .table tbody tr.selected-row td {
            background-color: #CBF3BB !important;
        }
        
        .table tbody tr.selected-row:hover {
            background-color: #B8E8A5 !important;
        }
        
        .table tbody tr.selected-row:hover td {
            background-color: #B8E8A5 !important;
        }
        
        /* اطمینان از اعمال رنگ برای ردیف‌های انتخاب شده */
        .table-sm tbody tr.selected-row,
        .table-hover tbody tr.selected-row {
            background-color: #CBF3BB !important;
        }
        
        .table-sm tbody tr.selected-row td,
        .table-hover tbody tr.selected-row td {
            background-color: #CBF3BB !important;
        }

        .table thead th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            border: none;
            padding: 12px 15px;
        }

        .table thead th:hover {
            background: #e9ecef;
        }

        .table tbody td {
            border-bottom: 1px solid #e5e7eb;
            padding: 15px;
        }

        /* استایل‌های ریسپانسیو برای موبایل */
        @media (max-width: 768px) {
            /* کاهش اندازه فونت‌ها و padding در موبایل */
            .card {
                margin-bottom: 0.75rem;
            }

            .card-body {
                padding: 0.75rem;
            }

            .card-header {
                padding: 0.5rem 0.75rem;
            }

            h5 {
                font-size: 1rem;
            }

            h6 {
                font-size: 0.875rem;
            }

            /* تنظیمات کارت‌های آماری */
            .stats-card h3 {
                font-size: 1.5rem;
            }

            /* تنظیمات جدول برای موبایل */
            .table-responsive {
                border-radius: 0.25rem;
            }

            .table th, .table td {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            /* بهبود نمایش فیلترها در موبایل */
            .col-md-2 {
                margin-bottom: 0.5rem;
            }

            /* تنظیمات صفحه‌بندی برای موبایل */
            .custom-pagination .page-link {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
                margin: 0 1px;
            }

            /* بهبود نمایش دکمه‌ها در موبایل */
            .btn-group-sm > .btn, .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            /* بهبود نمایش مودال در موبایل */
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            /* بهبود نمایش جستجو در موبایل */
            .input-group-sm {
                width: 100% !important;
            }

            /* تنظیمات عملیات گروهی در موبایل */
            .alert {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            /* بهبود نمایش بخش جستجوی اقامت‌گر */
            .position-absolute {
                z-index: 1000;
            }
        }

        /* استایل‌های خاص برای گوشی‌های کوچکتر */
        @media (max-width: 480px) {
            /* کاهش بیشتر اندازه فونت‌ها */
            .card-body {
                padding: 0.5rem;
            }

            h5 {
                font-size: 0.9rem;
            }

            h6 {
                font-size: 0.8rem;
            }

            /* تنظیمات جدول برای صفحه‌نمایش کوچک */
            .table th, .table td {
                padding: 0.3rem;
                font-size: 0.75rem;
            }

            /* بهبود نمایش کارت‌های آماری */
            .stats-card h3 {
                font-size: 1.25rem;
            }

            /* بهبود نمایش صفحه‌بندی */
            .custom-pagination .page-link {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }

            /* بهبود نمایش دکمه‌ها */
            .btn-group-sm > .btn, .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }

            /* بهبود نمایش مودال */
            .modal-body {
                padding: 0.75rem;
            }

            /* بهبود نمایش فیلترها */
            .form-select-sm, .form-control-sm {
                font-size: 0.75rem;

                /* بهبود نمایش مودال در موبایل */
                .modal-dialog {
                    margin: 0.5rem;
                    max-width: calc(100% - 1rem);
                }

                /* بهبود نمایش جستجو در موبایل */
                .input-group-sm {
                    width: 100% !important;
                }

                /* تنظیمات عملیات گروهی در موبایل */
                .alert {
                    padding: 0.5rem;
                    font-size: 0.8rem;
                }

                /* بهبود نمایش بخش جستجوی اقامت‌گر */
                .position-absolute {
                    z-index: 1000;
                }
            }

            /* استایل‌های خاص برای گوشی‌های کوچکتر */
            @media (max-width: 480px) {
                /* کاهش بیشتر اندازه فونت‌ها */
                .card-body {
                    padding: 0.5rem;
                }

                h5 {
                    font-size: 0.9rem;
                }
            }
        }
    </style>

    <div class="container-fluid py-3">
        <!-- بخش مدیریت کارت‌ها -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white;">
                        <h6 class="mb-0 d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-id-card me-2"></i>
                                مدیریت کارت‌های انضباطی
                            </div>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- کادر کارت‌های زرد -->
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div style="display: inline-block; background: black; border-radius: 50%; padding: 4px; margin-left: 8px;">
                                                    <img src="<?php echo e(asset('icons/yellow-card-icon.webp')); ?>" alt="کارت زرد" style="width: 35px; height: 27px;">
                                                </div>
                                                کارت‌های زرد
                                                <span class="badge bg-dark ms-2"><?php echo e($this->pendingYellowCardsCount + $this->approvedYellowCardsCount); ?></span>
                                            </div>
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <!-- تب‌های داخل کارت زرد -->
                                        <ul class="nav nav-tabs" id="yellowCardTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="yellow-pending-tab" data-bs-toggle="tab" data-bs-target="#yellow-pending" type="button" role="tab">
                                                    <i class="fas fa-clock me-2"></i>
                                                    بررسی نشده (<?php echo e($this->pendingYellowCardsCount); ?>)
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="yellow-approved-tab" data-bs-toggle="tab" data-bs-target="#yellow-approved" type="button" role="tab">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    بررسی شده (<?php echo e($this->approvedYellowCardsCount); ?>)
                                                </button>
                                            </li>
                                        </ul>

                                        <!-- محتوای تب‌های زرد -->
                                        <div class="tab-content" id="yellowCardTabsContent">
                                            <!-- کارت‌های زرد بررسی نشده -->
                                            <div class="tab-pane fade show active" id="yellow-pending" role="tabpanel">
                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                    <table class="table table-sm table-hover">
                                                        <thead class="table-light sticky-top">
                                                            <tr>
                                                                <th>اقامت‌گر</th>
                                                                <th>امتیاز</th>
                                                                <th>عملیات</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->pendingYellowCards ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="#" 
                                                                           wire:click.prevent="selectResident('<?php echo e($card->resident_name); ?>')"
                                                                           class="text-decoration-none fw-bold"
                                                                           style="cursor: pointer;">
                                                                            <?php echo e($card->resident_name); ?>

                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-warning text-dark"><?php echo e($card->current_score ?? 0); ?></span>
                                                                    </td>
                                                                    <td>
                                                                        <button wire:click="approveCard(<?php echo e($card->resident_id); ?>)" 
                                                                                class="btn btn-sm btn-success">
                                                                            <i class="fas fa-check"></i> تایید
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <tr>
                                                                    <td colspan="3" class="text-center text-muted py-3">
                                                                        کارت زرد بررسی نشده‌ای وجود ندارد
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- کارت‌های زرد بررسی شده -->
                                            <div class="tab-pane fade" id="yellow-approved" role="tabpanel">
                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                    <table class="table table-sm table-hover">
                                                        <thead class="table-light sticky-top">
                                                            <tr>
                                                                <th>اقامت‌گر</th>
                                                                <th>امتیاز</th>
                                                                <th>تاریخ تایید</th>
                                                                <th>عملیات</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->approvedYellowCards ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="#" 
                                                                           wire:click.prevent="selectResident('<?php echo e($card->resident_name); ?>')"
                                                                           class="text-decoration-none fw-bold"
                                                                           style="cursor: pointer;">
                                                                            <?php echo e($card->resident_name); ?>

                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-warning text-dark"><?php echo e($card->current_score ?? 0); ?></span>
                                                                    </td>
                                                                    <td><?php echo e(jalaliDate($card->approved_at, 'Y/m/d')); ?></td>
                                                                    <td>
                                                                        <button wire:click="deleteCard(<?php echo e($card->id); ?>)" 
                                                                                class="btn btn-sm btn-danger"
                                                                                onclick="return confirm('آیا از حذف این کارت اطمینان دارید؟')">
                                                                            <i class="fas fa-trash"></i> حذف
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted py-3">
                                                                        کارت زرد تأیید شده‌ای وجود ندارد
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- کادر کارت‌های قرمز -->
                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div style="display: inline-block; background: black; border-radius: 50%; padding: 4px; margin-left: 8px;">
                                                    <img src="<?php echo e(asset('icons/red-card-icon.webp')); ?>" alt="کارت قرمز" style="width: 35px; height: 27px;">
                                                </div>
                                                کارت‌های قرمز
                                                <span class="badge bg-light text-dark ms-2"><?php echo e($this->pendingRedCardsCount + $this->approvedRedCardsCount); ?></span>
                                            </div>
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <!-- تب‌های داخل کارت قرمز -->
                                        <ul class="nav nav-tabs" id="redCardTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="red-pending-tab" data-bs-toggle="tab" data-bs-target="#red-pending" type="button" role="tab">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    بررسی نشده (<?php echo e($this->pendingRedCardsCount); ?>)
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="red-approved-tab" data-bs-toggle="tab" data-bs-target="#red-approved" type="button" role="tab">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    بررسی شده (<?php echo e($this->approvedRedCardsCount); ?>)
                                                </button>
                                            </li>
                                        </ul>

                                        <!-- محتوای تب‌های قرمز -->
                                        <div class="tab-content" id="redCardTabsContent">
                                            <!-- کارت‌های قرمز بررسی نشده -->
                                            <div class="tab-pane fade show active" id="red-pending" role="tabpanel">
                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                    <table class="table table-sm table-hover">
                                                        <thead class="table-light sticky-top">
                                                            <tr>
                                                                <th>اقامت‌گر</th>
                                                                <th>امتیاز</th>
                                                                <th>عملیات</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->pendingRedCards ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="#" 
                                                                           wire:click.prevent="selectResident('<?php echo e($card->resident_name); ?>')"
                                                                           class="text-decoration-none fw-bold"
                                                                           style="cursor: pointer;">
                                                                            <?php echo e($card->resident_name); ?>

                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-danger"><?php echo e($card->current_score ?? 0); ?></span>
                                                                    </td>
                                                                    <td>
                                                                        <button wire:click="approveCard(<?php echo e($card->resident_id); ?>)" 
                                                                                class="btn btn-sm btn-success">
                                                                            <i class="fas fa-check"></i> تایید
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <tr>
                                                                    <td colspan="3" class="text-center text-muted py-3">
                                                                        کارت قرمز بررسی نشده‌ای وجود ندارد
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- کارت‌های قرمز بررسی شده -->
                                            <div class="tab-pane fade" id="red-approved" role="tabpanel">
                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                    <table class="table table-sm table-hover">
                                                        <thead class="table-light sticky-top">
                                                            <tr>
                                                                <th>اقامت‌گر</th>
                                                                <th>امتیاز</th>
                                                                <th>تاریخ تایید</th>
                                                                <th>عملیات</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->approvedRedCards ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <tr>
                                                                    <td>
                                                                        <a href="#" 
                                                                           wire:click.prevent="selectResident('<?php echo e($card->resident_name); ?>')"
                                                                           class="text-decoration-none fw-bold"
                                                                           style="cursor: pointer;">
                                                                            <?php echo e($card->resident_name); ?>

                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-danger"><?php echo e($card->total_score); ?></span>
                                                                    </td>
                                                                    <td><?php echo e(jalaliDate($card->approved_at, 'Y/m/d')); ?></td>
                                                                    <td>
                                                                        <button wire:click="deleteCard(<?php echo e($card->id); ?>)" 
                                                                                class="btn btn-sm btn-danger"
                                                                                onclick="return confirm('آیا از حذف این کارت اطمینان دارید؟')">
                                                                            <i class="fas fa-trash"></i> حذف
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted py-3">
                                                                        کارت قرمز تأیید شده‌ای وجود ندارد
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش جستجو و فیلترها و جدول گزارش‌ها -->
        <div class="card mb-3" id="reports-list-section">
            <div class="card-header bg-light" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#mainContentCollapse" aria-expanded="true" aria-controls="mainContentCollapse">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        جستجو، فیلترها و گزارش‌های تخلفی
                        <i class="fas fa-chevron-up ms-2" id="mainContentIcon"></i>
                    </h6>
                    <div>
                        <span class="badge bg-primary me-1"><?php echo e(count($reports)); ?> گزارش</span>
                        <span class="badge bg-info"><?php echo e($reports->total()); ?> مجموع</span>
                    </div>
                </div>
            </div>
            <div class="collapse show" id="mainContentCollapse">
                <!-- بخش جستجو و فیلترهای اصلی -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filterByResidentName): ?>
                    <div class="alert alert-info mb-2 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-filter me-1"></i>
                                <strong>فیلتر فعال:</strong> نمایش گزارش‌های اقامت‌گر <strong><?php echo e($filterByResidentName); ?></strong>
                            </span>
                            <button wire:click="clearResidentFilter" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i> حذف فیلتر
                            </button>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <button class="btn btn-outline-primary btn-sm mb-2 mb-md-0" wire:click="$toggle('showFilters')">
                        <i class="fas fa-filter me-1"></i>
                        فیلترها <?php echo e($showFilters ? '▼' : '▶'); ?>

                    </button>

                    <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                placeholder="جستجو در گزارش‌ها، نام اقامت‌گر...">
                        </div>

                        <select wire:model.live="perPage" class="form-select form-select-sm">
                            <option value="10">10 در صفحه</option>
                            <option value="25">25 در صفحه</option>
                            <option value="50">50 در صفحه</option>
                            <option value="100">100 در صفحه</option>
                        </select>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFilters): ?>
                    <div class="row mt-3 g-2">
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">واحد</label>
                            <select wire:model.live="filters.unit_id" class="form-select form-select-sm">
                                <option value="">همه واحدها</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($unit['id']); ?>"><?php echo e($unit['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">اتاق</label>
                            <select wire:model.live="filters.room_id" class="form-select form-select-sm">
                                <option value="">همه اتاق‌ها</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filteredRooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($room['id']); ?>"><?php echo e($room['name']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">دسته‌بندی</label>
                            <select wire:model.live="filters.category_id" class="form-select form-select-sm">
                                <option value="">همه دسته‌بندی‌ها</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">گزارش</label>
                            <select wire:model.live="filters.report_id" class="form-select form-select-sm">
                                <option value="">همه گزارش‌ها</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($report->id); ?>"><?php echo e($report->title); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">از تاریخ</label>
                            <input type="date" wire:model.live="filters.date_from"
                                class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">تا تاریخ</label>
                            <input type="date" wire:model.live="filters.date_to"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-12 mt-2">
                            <button wire:click="resetFilters" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i>حذف فیلترها
                            </button>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- بخش عملیات گروهی -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedReports) > 0): ?>
            <div class="alert alert-warning mb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="mb-2 mb-md-0">
                        <strong><?php echo e(count($selectedReports)); ?></strong> گزارش انتخاب شده است
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-2">
                        <select wire:model="bulkAction" class="form-select form-select-sm">
                            <option value="">عملیات گروهی</option>
                            <option value="delete">حذف انتخاب‌شده‌ها</option>
                        </select>
                        <button wire:click="executeBulkAction" class="btn btn-danger btn-sm">
                            <i class="fas fa-play me-1"></i> اجرا
                        </button>
                        <button wire:click="$set('selectedReports', [])" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i> لغو
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- جدول اصلی گزارش‌های تخلفی -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>شماره</th>
                                <th wire:click="sortBy('resident_name')" style="cursor: pointer;">
                                    اقامت‌گر
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'resident_name'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </th>
                                <th class="d-none d-md-table-cell">موقعیت</th>
                                <th>گزارش</th>
                                <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                    تاریخ ثبت
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'created_at'): ?>
                                        <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </th>
                                <th>نمره منفی</th>
                                <th>توضیحات</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter_number = 0; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $counter_number++; ?>
                                <tr style="transition: all 0.2s ease;">
                                    <td style="width: 1%; text-align: center; vertical-align: middle;">
                                        <span class="badge rounded-pill bg-primary" style="font-size: 13px; padding: 6px 12px;">
                                            <?php echo e($counter_number); ?>

                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #6c757d; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                <?php echo e(mb_substr($report->resident_name ?? 'ن', 0, 1)); ?>

                                            </div>
                                            <div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($report->resident_name)): ?>
                                                    <a href="#" 
                                                       wire:click.prevent="selectResident('<?php echo e($report->resident_name); ?>')"
                                                       class="text-decoration-none fw-bold"
                                                       style="cursor: pointer; color: #1f2937; font-size: 14px; display: block; margin-bottom: 3px;"
                                                       title="مشاهده جزئیات اقامت‌گر">
                                                        <?php echo e($report->resident_name); ?>

                                                    </a>
                                                <?php else: ?>
                                                    <strong style="font-size: 14px; color: #1f2937; display: block; margin-bottom: 3px;">نامشخص</strong>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->resident_id): ?>
                                                    <small style="color: #6b7280; font-size: 11px;">
                                                        <i class="fas fa-id-card"></i> ID: <?php echo e($report->resident_id); ?>

                                                    </small>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell" style="vertical-align: middle;">
                                        <div>
                                            <span class="badge" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 5px 10px; margin-bottom: 5px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                <i class="fas fa-building"></i> <?php echo e($report->unit_name ?? 'واحد نامشخص'); ?>

                                            </span>
                                            <br>
                                            <div style="margin-top: 5px; font-size: 12px; color: #6b7280;">
                                                <i class="fas fa-door-open"></i> اتاق: <?php echo e($report->room_name ?? 'نامشخص'); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->bed_name): ?>
                                                    <br>
                                                    <i class="fas fa-bed"></i> تخت: <?php echo e($report->bed_name); ?>

                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="display: flex; align-items: start; gap: 12px;">
                                            <!-- کارت گزارش -->
                                            <div style="flex: 1; padding: 8px; background: #f8f9fa; border-radius: 6px; border-right: 3px solid #3b82f6;">
                                                <strong style="font-size: 14px; color: #1f2937; display: block; margin-bottom: 5px;">
                                                    <?php echo e($report->report->title ?? 'گزارش حذف شده'); ?>

                                                </strong>
                                                <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                                                    <span class="badge" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; padding: 3px 8px; font-size: 11px;">
                                                        <i class="fas fa-tag"></i> <?php echo e($report->report->category->name ?? 'بدون دسته'); ?>

                                                    </span>
                                                </div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->notes): ?>
                                                    <div style="margin-top: 5px; padding: 5px; background: white; border-radius: 4px; border: 1px solid #e5e7eb;">
                                                        <small style="color: #6b7280; font-size: 11px;">
                                                            <i class="fas fa-sticky-note"></i> <?php echo e(Str::limit($report->notes, 50)); ?>

                                                        </small>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            
                                            <!-- توضیحات در کنار -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->description): ?>
                                                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 8px 12px; border-radius: 8px; border: 1px solid #fbbf24; max-width: 200px; min-width: 120px;">
                                                    <div style="font-size: 12px; color: #92400e; line-height: 1.4;">
                                                        <i class="fas fa-comment-alt" style="margin-left: 4px; color: #f59e0b;"></i>
                                                        <?php echo e(Str::limit($report->description, 80)); ?>

                                                    </div>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="text-align: center;">
                                            <div style="font-size: 13px; font-weight: 600; color: #1f2937; margin-bottom: 3px;">
                                                <?php echo e(jalaliDate($report->created_at, 'Y/m/d')); ?>

                                            </div>
                                            <div style="font-size: 11px; color: #6b7280;">
                                                <i class="fas fa-clock"></i> <?php echo e(jalaliDate($report->created_at, 'H:i')); ?>

                                            </div>
                                            <small style="color: #9ca3af; font-size: 10px; display: block; margin-top: 3px;">
                                                <?php echo e($report->created_at->diffForHumans()); ?>

                                            </small>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle; text-align: center;">
                                        <span class="badge" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; font-size: 14px; padding: 6px 12px; border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <i class="fas fa-minus-circle"></i> <?php echo e($report->report->negative_score ?? 0); ?>

                                        </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="max-width: 200px; word-wrap: break-word;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($report->description)): ?>
                                                <div style="padding: 6px; background: #fef3c7; border-radius: 6px; border: 1px solid #fbbf24; font-size: 12px; color: #92400e; line-height: 1.4;">
                                                    <i class="fas fa-comment-alt" style="margin-right: 4px; color: #f59e0b;"></i>
                                                    <?php echo e(Str::limit($report->description, 80)); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strlen($report->description) > 80): ?>
                                                        <button type="button" class="btn btn-sm btn-link p-0 ms-1" data-bs-toggle="tooltip" title="<?php echo e($report->description); ?>">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </button>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-danger btn-sm"
                                                onclick="confirmDeleteReport(<?php echo e($report->id); ?>)" title="حذف گزارش">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button wire:click="toggleReportStatus(<?php echo e($report->id); ?>)" 
                                                    class="btn <?php echo e($report->is_checked ? 'btn-secondary' : 'btn-success'); ?> btn-sm"
                                                    title="<?php echo e($report->is_checked ? 'غیرفعال کردن' : 'فعال کردن'); ?> وضعیت">
                                                <i class="fas <?php echo e($report->is_checked ? 'fa-times' : 'fa-check'); ?>"></i>
                                            </button>
                                        </div>

                                        <!-- مودال (پنجره پاپ‌آپ) جزئیات گزارش -->
                                        <div class="modal fade" id="reportDetails<?php echo e($report->id); ?>"
                                            tabindex="-1" wire:ignore>
                                            <div class="modal-dialog modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-info text-white">
                                                        <h5 class="modal-title">جزئیات گزارش</h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-12 col-md-6">
                                                                <p><strong>اقامت‌گر:</strong>
                                                                    <?php echo e($report->resident_name); ?></p>
                                                                <p><strong>واحد:</strong> <?php echo e($report->unit_name); ?></p>
                                                                <p><strong>اتاق:</strong> <?php echo e($report->room_name); ?></p>
                                                                <p><strong>تخت:</strong> <?php echo e($report->bed_name); ?></p>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <p><strong>گزارش:</strong>
                                                                    <?php echo e($report->report->title ?? 'حذف شده'); ?></p>
                                                                <p><strong>دسته‌بندی:</strong>
                                                                    <?php echo e($report->report->category->name ?? 'بدون دسته'); ?>

                                                                </p>
                                                                <p><strong>نمره منفی:</strong> <span
                                                                        class="badge bg-danger"><?php echo e($report->report->negative_score ?? 0); ?></span>
                                                                </p>
                                                                <p><strong>ضریب افزایش:</strong>
                                                                    <?php echo e($report->report->increase_coefficient ?? 0); ?>

                                                                </p>
                                                            </div>
                                                        </div>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->notes): ?>
                                                            <div class="mt-3">
                                                                <strong>یادداشت‌ها:</strong>
                                                                <div class="alert alert-light mt-2">
                                                                    <?php echo e($report->notes); ?>

                                                                </div>
                                                            </div>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->description): ?>
                                                            <div class="mt-3">
                                                                <strong>توضیحات گزارش:</strong>
                                                                <div class="alert alert-warning mt-2" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #fbbf24;">
                                                                    <?php echo e($report->description); ?>

                                                                </div>
                                                            </div>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                        <div class="mt-3">
                                                            <strong>اطلاعات ثبت:</strong>
                                                            <p class="mb-1">تاریخ ثبت:
                                                                <?php echo e(jalaliDate($report->created_at, 'Y/m/d H:i')); ?></p>
                                                            <p class="mb-0">آخرین ویرایش:
                                                                <?php echo e(jalaliDate($report->updated_at, 'Y/m/d H:i')); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">بستن</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">هیچ گزارشی یافت نشد</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || array_filter($filters)): ?>
                                            <button wire:click="resetFilters" class="btn btn-sm btn-outline-primary">
                                                حذف فیلترها
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- فوتر جدول با صفحه‌بندی زیبا و سفارشی -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reports->hasPages()): ?>
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                    <div class="text-muted small mb-2 mb-sm-0">
                        نمایش
                        <?php echo e($reports->firstItem() ?? 0); ?>

                        تا
                        <?php echo e($reports->lastItem() ?? 0); ?>

                        از
                        <?php echo e($reports->total()); ?>

                        نتیجه
                    </div>
                    
                    <nav aria-label="Page navigation">
                        <ul class="pagination custom-pagination mb-0">
                            
                            <li class="page-item <?php echo e($reports->onFirstPage() ? 'disabled' : ''); ?>">
                                <a class="page-link" href="#" wire:click="previousPage()" tabindex="-1"
                                    aria-disabled="<?php echo e($reports->onFirstPage() ? 'true' : 'false'); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reports->getUrlRange(1, $reports->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page == $reports->currentPage()): ?>
                                    <li class="page-item active">
                                        <span class="page-link"><?php echo e($page); ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="#"
                                            wire:click="gotoPage(<?php echo e($page); ?>)"><?php echo e($page); ?></a>
                                    </li>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <li class="page-item <?php echo e(!$reports->hasMorePages() ? 'disabled' : ''); ?>">
                                <a class="page-link" href="#" wire:click="nextPage()"
                                    aria-disabled="<?php echo e(!$reports->hasMorePages() ? 'true' : 'false'); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- اسکریپت‌های خارجی -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDeleteReport(id) {
            Swal.fire({
                title: 'حذف گزارش',
                text: 'آیا مطمئن هستید که می‌خواهید این گزارش را حذف کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteReport(id);
                }
            });
        }

        window.addEventListener('confirmBulkDelete', event => {
            const {
                type,
                count
            } = event.detail;

            Swal.fire({
                title: `حذف ${count} گزارش`,
                text: `آیا مطمئن هستید که می‌خواهید ${count} گزارش انتخاب شده را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteMultipleReports();
                }
            });
        });

        // تابع برای باز کردن modal گزارش
        // تابع برای باز کردن modal گزارش - در scope global
        window.openReportModal = function(reportId) {
            const modalElement = document.getElementById('reportDetails' + reportId);
            if (modalElement) {
                // اگر modal قبلاً initialize شده، از همان استفاده کن
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    // اگر initialize نشده، جدید بساز
                    modal = new bootstrap.Modal(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                }
                // فقط modal را نشان بده
                modal.show();
            }
            return false; // جلوگیری از default behavior
        }

        // اسکرول به بخش لیست گزارش‌ها هنگام فیلتر کردن
        document.addEventListener('livewire:init', () => {
            Livewire.on('scrollToReports', () => {
                setTimeout(() => {
                    const reportsSection = document.getElementById('reports-list-section');
                    if (reportsSection) {
                        reportsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
            });
        });

        // تابع برای چرخش آیکون chevron هنگام باز/بسته شدن dropdown
        document.addEventListener('DOMContentLoaded', function() {
            // گوش دادن به رویدادهای Bootstrap collapse
            const mainContentCollapse = document.getElementById('mainContentCollapse');
            
            if (mainContentCollapse) {
                mainContentCollapse.addEventListener('show.bs.collapse', function () {
                    document.getElementById('mainContentIcon').style.transform = 'rotate(180deg)';
                });
                
                mainContentCollapse.addEventListener('hide.bs.collapse', function () {
                    document.getElementById('mainContentIcon').style.transform = 'rotate(0deg)';
                });
            }
        });
    </script>

    <!-- مدال نمایش جزئیات اقامت‌گر -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showResidentModal && $selectedResidentData): ?>
    <div class="modal fade show" id="residentModal" tabindex="-1" aria-labelledby="residentModalLabel" aria-hidden="false" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="residentModalLabel">
                        <i class="fas fa-user me-2"></i>
                        جزئیات اقامت‌گر: <?php echo e($selectedResidentData->resident_full_name ?? 'نامشخص'); ?>

                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeResidentModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- اطلاعات اصلی اقامت‌گر -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>اطلاعات اصلی</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <strong>نام کامل:</strong>
                                            <div><?php echo e($selectedResidentData->resident_full_name ?? 'نامشخص'); ?></div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>شماره تلفن:</strong>
                                            <div style="direction: ltr; text-align: right;"><?php echo e($selectedResidentData->resident_phone ?? '-'); ?></div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>سن:</strong>
                                            <div><?php echo e($selectedResidentData->resident_age ?? '-'); ?></div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>تاریخ تولد:</strong>
                                            <div><?php echo e($selectedResidentData->resident_birth_date ? jalaliDate($selectedResidentData->resident_birth_date, 'Y/m/d') : '-'); ?></div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>شغل:</strong>
                                            <div><?php echo e($selectedResidentData->resident_job ?? '-'); ?></div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>منبع معرفی:</strong>
                                            <div><?php echo e($selectedResidentData->resident_referral_source ?? '-'); ?></div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>فرم:</strong>
                                            <div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData->resident_form): ?>
                                                    <span class="badge bg-success">دارد</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">ندارد</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <strong>مدرک:</strong>
                                            <div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData->resident_document): ?>
                                                    <span class="badge bg-success">دارد</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">ندارد</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اطلاعات موقعیت -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>موقعیت</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <strong>واحد:</strong>
                                            <div>
                                                <span class="badge bg-primary"><?php echo e($selectedResidentData->unit_name ?? 'نامشخص'); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData->unit_code): ?>
                                                    <small class="text-muted">(<?php echo e($selectedResidentData->unit_code); ?>)</small>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>اتاق:</strong>
                                            <div>
                                                <span class="badge bg-info"><?php echo e($selectedResidentData->room_name ?? 'نامشخص'); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData->room_code): ?>
                                                    <small class="text-muted">(<?php echo e($selectedResidentData->room_code); ?>)</small>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>تخت:</strong>
                                            <div>
                                                <span class="badge bg-warning text-dark"><?php echo e($selectedResidentData->bed_name ?? 'نامشخص'); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData->bed_code): ?>
                                                    <small class="text-muted">(<?php echo e($selectedResidentData->bed_code); ?>)</small>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اطلاعات قرارداد -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData->contract_start_date || $selectedResidentData->contract_end_date): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>اطلاعات قرارداد</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <strong>تاریخ شروع:</strong>
                                            <div><?php echo e($selectedResidentData->contract_start_date ? jalaliDate($selectedResidentData->contract_start_date, 'Y/m/d') : '-'); ?></div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>تاریخ پایان:</strong>
                                            <div><?php echo e($selectedResidentData->contract_end_date ? jalaliDate($selectedResidentData->contract_end_date, 'Y/m/d') : '-'); ?></div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <strong>وضعیت قرارداد:</strong>
                                            <div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData->contract_is_active ?? true): ?>
                                                    <span class="badge bg-success">فعال</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">غیرفعال</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- آمار و گزارش‌ها -->
                    <div class="row mb-4">
                        <div class="col-12 col-md-4 mb-3">
                            <div class="card bg-light text-center">
                                <div class="card-body">
                                    <h6 class="card-title mb-1">تعداد گزارش‌ها</h6>
                                    <h4 class="mb-0 text-primary"><?php echo e(count($residentReports)); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <div class="card bg-light text-center">
                                <div class="card-body">
                                    <h6 class="card-title mb-1">مجموع نمرات منفی</h6>
                                    <h4 class="mb-0 text-danger"><?php echo e($this->getResidentTotalNegativeScore()); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <div class="card bg-light text-center">
                                <div class="card-body">
                                    <h6 class="card-title mb-1">تعداد بخشودگی‌ها</h6>
                                    <h4 class="mb-0 text-success"><?php echo e(count($selectedResidentGrants)); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- دکمه‌های عملیات -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grantCheckError): ?>
                                    <div style="margin-bottom: 12px; padding: 12px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; color: #92400e; text-align: center; font-weight: 500;">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?php echo e($grantCheckError); ?>

                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData): ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createReportModal">
                                            <i class="fas fa-plus me-1"></i> ثبت گزارش جدید
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$showGrantForm): ?>
                                        <button type="button" wire:click="openGrantForm" class="btn btn-success">
                                            <i class="fas fa-gift me-1"></i> ثبت بخشودگی
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button type="button" 
                                        class="btn btn-sm btn-primary"
                                        wire:click="checkAllReports"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="checkAllReports">
                                        <i class="fas fa-check-square me-1"></i>چک همه گزارش‌ها
                                    </span>
                                    <span wire:loading wire:target="checkAllReports">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        ...
                                    </span>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-danger"
                                        wire:click="uncheckAllReports"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uncheckAllReports">
                                        <i class="fas fa-square me-1"></i>لغو چک همه
                                    </span>
                                    <span wire:loading wire:target="uncheckAllReports">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        ...
                                    </span>
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- فرم ثبت بخشودگی -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGrantForm): ?>
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-gift me-2"></i>
                                    ثبت بخشودگی جدید
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closeGrantForm">
                                    <i class="fas fa-times"></i> بستن
                                </button>
                            </div>
                            
                            <form wire:submit.prevent="saveGrant">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="grantAmount" class="form-label">مقدار بخشودگی <span class="text-danger">*</span></label>
                                        <input type="number" 
                                               class="form-control <?php $__errorArgs = ['grantAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               id="grantAmount"
                                               wire:model="grantAmount"
                                               step="0.01"
                                               min="0"
                                               placeholder="مقدار بخشودگی را وارد کنید">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['grantAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="grantDate" class="form-label">تاریخ بخشودگی</label>
                                        <input type="date" 
                                               class="form-control <?php $__errorArgs = ['grantDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               id="grantDate"
                                               wire:model="grantDate">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['grantDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="grantDescription" class="form-label">توضیحات</label>
                                        <textarea 
                                              class="form-control" 
                                              id="grantDescription"
                                              wire:model="grantDescription"
                                              rows="3"
                                              placeholder="توضیحات (اختیاری)"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" wire:click="closeGrantForm">انصراف</button>
                                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveGrant">
                                            <i class="fas fa-save me-1"></i>ذخیره
                                        </span>
                                        <span wire:loading wire:target="saveGrant">
                                            <span class="spinner-border spinner-border-sm me-1"></span>
                                            در حال ذخیره...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- لیست بخشودگی‌ها -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedResidentGrants) > 0): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-gift me-2"></i>لیست بخشودگی‌ها</h6>
                        </div>
                        <div class="card-body p-0">
                            <div style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>مقدار</th>
                                            <th>تاریخ</th>
                                            <th>توضیحات</th>
                                            <th>وضعیت</th>
                                            <th width="100">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $selectedResidentGrants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr style="background-color: <?php echo e($grant->is_active ? '#e8f5e9' : '#f5f5f5'); ?>;">
                                                <td><?php echo e(number_format($grant->amount, 0)); ?></td>
                                                <td><?php echo e($grant->grant_date ? jalaliDate($grant->grant_date, 'Y/m/d') : '-'); ?></td>
                                                <td><?php echo e($grant->description ?? '-'); ?></td>
                                                <td>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grant->is_active): ?>
                                                        <span class="badge bg-success">فعال</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">غیرفعال</span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                                <td>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm"
                                                            wire:click="deleteGrant(<?php echo e($grant->id); ?>)"
                                                            wire:confirm="آیا مطمئن هستید که می‌خواهید این بخشودگی را حذف کنید؟"
                                                            title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- لیست گزارش‌های تخلفی -->
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                لیست گزارش‌ها:
                                <span class="badge bg-light text-dark ms-2">
                                    <?php echo e($this->checkedReportsCount); ?> از <?php echo e($this->residentReportsCount); ?>

                                </span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>گزارش</th>
                                            <th>نمره</th>
                                            <th>توضیحات</th>
                                            <th>تاریخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $residentReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $isChecked = (bool)($report->is_checked ?? false);
                                                
                                                // بررسی اینکه آیا تاریخ گزارش بعد از تاریخ‌های بخشودگی است
                                                $reportDate = \Carbon\Carbon::parse($report->created_at);
                                                $isAfterGrant = false;
                                                
                                                if (count($selectedResidentGrants) > 0) {
                                                    foreach ($selectedResidentGrants as $grant) {
                                                        if ($grant->grant_date) {
                                                            $grantDate = \Carbon\Carbon::parse($grant->grant_date);
                                                            if ($reportDate->isAfter($grantDate)) {
                                                                $isAfterGrant = true;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            ?>
                                            <tr wire:key="resident-report-<?php echo e($report->id); ?>" 
                                                <?php if($isChecked): ?>
                                                    style="background-color: #CBF3BB !important; transition: background-color 0.2s ease;"
                                                    class="selected-row"
                                                <?php else: ?>
                                                    style="background-color: transparent; transition: background-color 0.2s ease;"
                                                <?php endif; ?>>
                                                <td>
                                                    <div>
                                                        <strong><?php echo e($report->report->title ?? 'حذف شده'); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo e($report->report->category->name ?? 'بدون دسته'); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        <?php echo e($report->report->negative_score ?? 0); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo e(Str::limit($report->notes ?? '-', 50)); ?></small>
                                                </td>
                                                <td>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAfterGrant): ?>
                                                        <small style="color: #76153C; font-weight: bold;">
                                                            <?php echo e(jalaliDate($report->created_at, 'Y/m/d H:i')); ?>

                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">
                                                            <?php echo e(jalaliDate($report->created_at, 'Y/m/d H:i')); ?>

                                                        </small>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                                    <p class="mb-0">هیچ گزارشی یافت نشد.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeResidentModal">
                        <i class="fas fa-times me-1"></i>بستن
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- مدال ثبت گزارش جدید -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedResidentData): ?>
    <div class="modal fade" id="createReportModal" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createReportModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>
                        ثبت گزارش جدید برای <?php echo e($selectedResidentData->resident_full_name ?? 'نامشخص'); ?>

                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('residents.create-resident-report', ['residentId' => $selectedResidentData->resident_id]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2407318976-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH C:\laragon\www\atlas_report\resources\views/livewire/residents/resident-reports.blade.php ENDPATH**/ ?>