<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>سیستم گزارش‌گیری اقامت‌گران</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #ff9e00;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark-color);
            overflow-x: hidden;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            position: fixed;
            height: 100vh;
            padding-top: 20px;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            right: 0;
            transform: translateX(0);
        }

        .content {
            flex: 1;
            margin-right: var(--sidebar-width);
            transition: all 0.3s ease;
            width: calc(100% - var(--sidebar-width));
        }

        .navbar {
            background: white;
            height: var(--header-height);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            border-right: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            border-right-color: var(--success-color);
        }

        .main-content {
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #d1145a;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #2db8d9;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stats-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }

        .stats-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 24px;
            cursor: pointer;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .content {
                margin-right: 0;
                width: 100%;
            }

            .mobile-menu-btn {
                display: block;
            }

            .main-content {
                padding: 15px;
            }

            .card {
                padding: 15px;
                margin-bottom: 15px;
            }

            .table {
                font-size: 14px;
            }

            .table th,
            .table td {
                padding: 8px 5px;
            }

            .btn {
                padding: 6px 12px;
                font-size: 14px;
            }

            .stats-card {
                padding: 15px;
            }

            .stats-number {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .navbar h3 {
                font-size: 16px;
            }

            .sidebar-header h2 {
                font-size: 20px;
            }

            .main-content {
                padding: 10px;
            }

            .card {
                padding: 10px;
            }

            .btn {
                padding: 5px 10px;
                font-size: 13px;
            }
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }
        .rtl-popup {
            direction: rtl;
        }
    </style>

    @livewireStyles
</head>

<body>
    <div class="main-container">
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>🏨 سیستم گزارش</h2>
                <p>اقامت‌گران</p>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>داشبورد</span>
                    </a>
                </li>
                <li>
                    <a href="/reports" class="{{ request()->is('reports*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span>گزارش‌ها</span>
                    </a>
                </li>
                <li>
                    <a href="/reports/create" class="{{ request()->is('reports/create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>ایجاد گزارش جدید</span>
                    </a>
                </li>
                <li>
                    <a href="/categories" class="{{ request()->is('categories*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>دسته‌بندی‌ها</span>
                    </a>
                </li>

                <li>
                    <a href="/categories/create" class="{{ request()->is('categories/create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>ایجاد دسته‌بندی جدید</span>
                    </a>
                </li>

                <li>
                    <a href="/residents" class="{{ request()->is('residents') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>مدیریت اقامت‌گران</span>
                    </a>
                </li>
                <li>
                    <a href="/resident-reports" class="{{ request()->is('resident-reports') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>گزارش‌های اقامت‌گران</span>
                    </a>
                </li>
                <li>
                    <a href="/sms" class="{{ request()->is('sms') && !request()->is('sms/manual') && !request()->is('sms/group') ? 'active' : '' }}">
                        <i class="fas fa-sms"></i>
                        <span>مدیریت پیام‌های SMS</span>
                    </a>
                </li>
                <li>
                    <a href="/sms/manual" class="{{ request()->is('sms/manual') ? 'active' : '' }}">
                        <i class="fas fa-user"></i>
                        <span>ارسال SMS دستی</span>
                    </a>
                </li>
                <li>
                    <a href="/sms/group" class="{{ request()->is('sms/group') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>ارسال SMS گروهی</span>
                    </a>
                </li>
                <li>
                    <a href="/sms/sent" class="{{ request()->is('sms/sent') ? 'active' : '' }}">
                        <i class="fas fa-history"></i>
                        <span>پیام‌های ارسال شده</span>
                    </a>
                </li>

            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content" id="content">
            <!-- Navbar -->
            <nav class="navbar">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h3 style="margin-right: 20px; color: var(--primary-color);">
                    @yield('title', 'سیستم گزارش‌گیری')
                </h3>
                <div style="margin-right: auto;"></div>
                <div style="color: var(--primary-color);">
                    <i class="fas fa-user"></i>
                    <span>مدیر سیستم</span>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="main-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        // Check if we're on mobile and close sidebar by default
        function checkMobileAndCloseSidebar() {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.remove('open');
            }
        }

        // Initial check when page loads
        document.addEventListener('DOMContentLoaded', checkMobileAndCloseSidebar);

        // Check when window is resized
        window.addEventListener('resize', checkMobileAndCloseSidebar);

        // Sidebar functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        // Close sidebar when clicking on overlay
        document.getElementById('overlay').addEventListener('click', toggleSidebar);

        // SweetAlert2 configuration
        window.addEventListener('showAlert', event => {
            Swal.fire({
                icon: event.detail.type,
                title: event.detail.title,
                text: event.detail.text,
                confirmButtonText: 'باشه',
                confirmButtonColor: '#4361ee'
            });
        });

        // Log Melipayamak API Response to Console
        window.addEventListener('logMelipayamakResponse', event => {
            const response = event.detail;
            console.log('=== پاسخ ملی پیامک ===');
            console.log('وضعیت:', response.success ? '✅ موفق' : '❌ خطا');
            console.log('کد پاسخ:', response.response_code);
            console.log('پیام:', response.message);
            console.log('پاسخ خام (Raw Response):', response.raw_response);
            console.log('پاسخ API (Parsed):', response.api_response);
            if (response.rec_id) {
                console.log('RecId:', response.rec_id);
            }
            console.log('===================');
        });

        // تابع نمایش خطای کامل API ملی پیامک
        window.showError = function(errorData) {
            // اگر errorData یک رشته باشد (برای سازگاری با کد قدیمی)
            if (typeof errorData === 'string') {
                Swal.fire({
                    icon: 'error',
                    title: 'پیام خطا',
                    html: '<div style="text-align: right; direction: rtl;">' + errorData + '</div>',
                    confirmButtonText: 'باشه',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }

            // ساخت HTML برای نمایش اطلاعات کامل
            let html = '<div style="text-align: right; direction: rtl; font-family: monospace; font-size: 13px;">';
            html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px;">';
            html += '<strong style="color: #dc3545;">وضعیت:</strong> ';
            html += '<span style="color: ' + (errorData.success ? '#28a745' : '#dc3545') + ';">';
            html += errorData.success ? '✅ موفق' : '❌ خطا';
            html += '</span><br><br>';
            
            html += '<strong>کد پاسخ:</strong> ' + (errorData.response_code || '-') + '<br><br>';
            html += '<strong>پیام:</strong> ' + (errorData.message || '-') + '<br><br>';
            
            if (errorData.raw_response) {
                html += '<strong>پاسخ خام (Raw Response):</strong><br>';
                html += '<div style="background: #fff; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 5px; word-break: break-all;">';
                html += errorData.raw_response;
                html += '</div><br>';
            }
            
            if (errorData.api_response) {
                html += '<strong>پاسخ API (Parsed):</strong><br>';
                html += '<div style="background: #fff; padding: 10px; border: 1px solid #dee2e6; border-radius: 4px; margin-top: 5px; max-height: 300px; overflow-y: auto;">';
                html += '<pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;">';
                html += JSON.stringify(errorData.api_response, null, 2);
                html += '</pre>';
                html += '</div>';
            }
            
            html += '</div></div>';

            Swal.fire({
                icon: 'error',
                title: 'جزئیات خطا',
                html: html,
                width: '700px',
                confirmButtonText: 'باشه',
                confirmButtonColor: '#4361ee',
                customClass: {
                    popup: 'rtl-popup'
                }
            });
        };

        // Confirm delete for single items
        window.confirmDelete = function(id, type, title) {
            let deleteTitle = '';
            let deleteText = '';

            if (type === 'Report') {
                deleteTitle = 'حذف گزارش';
                deleteText = 'آیا مطمئن هستید که می‌خواهید این گزارش را حذف کنید؟';
            } else if (type === 'Category') {
                deleteTitle = 'حذف دسته‌بندی';
                deleteText = 'آیا مطمئن هستید که می‌خواهید این دسته‌بندی را حذف کنید؟';
            }

            Swal.fire({
                title: deleteTitle,
                text: deleteText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (type === 'Report') {
                        // Dispatch event to Livewire component
                        Livewire.dispatch('deleteReport', {
                            id: id
                        });
                    } else if (type === 'Category') {
                        // Dispatch event to Livewire component
                        Livewire.dispatch('deleteCategory', {
                            id: id,
                            withReports: false
                        });
                    }
                }
            });
        }

        // Confirm delete category with reports
        window.confirmDeleteCategoryWithReports = function(id, name, reportsCount) {
            Swal.fire({
                title: 'حذف دسته‌بندی همراه با گزارش‌ها',
                html: `آیا مطمئن هستید که می‌خواهید دسته‌بندی <strong>"${name}"</strong> را حذف کنید؟<br>
                  <span style="color: #f72585;">این عمل ${reportsCount} گزارش مرتبط را نیز حذف خواهد کرد!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، همه را حذف کن',
                cancelButtonText: 'لغو',
                reverseButtons: true,
                showDenyButton: true,
                denyButtonText: 'فقط دسته‌بندی را حذف کن',
                denyButtonColor: '#ff9e00'
            }).then((result) => {
                if (result.isConfirmed) {
                    // حذف همراه با گزارش‌ها
                    Livewire.dispatch('deleteCategory', {
                        id: id,
                        withReports: true
                    });
                } else if (result.isDenied) {
                    // فقط دسته‌بندی را حذف کن (اگر امکان‌پذیر باشد)
                    Livewire.dispatch('deleteCategory', {
                        id: id,
                        withReports: false
                    });
                }
            });
        }

        // Listen for bulk delete confirmation from Livewire
        window.addEventListener('confirmBulkDelete', event => {
            const {
                type,
                count
            } = event.detail;

            Swal.fire({
                title: `حذف ${count} مورد`,
                text: `آیا مطمئن هستید که می‌خواهید ${count} ${type === 'reports' ? 'گزارش' : 'دسته‌بندی'} انتخاب شده را حذف کنید؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'لغو',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (type === 'reports') {
                        Livewire.dispatch('deleteMultipleReports');
                    } else if (type === 'categories') {
                        Livewire.dispatch('deleteMultipleCategories');
                    }
                }
            });
        });

        // Handle Livewire navigation
        document.addEventListener('livewire:navigated', () => {
            // Close sidebar on mobile after navigation
            if (window.innerWidth < 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');

                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }
        });
    </script>
    @livewireScripts
</body>

</html>
