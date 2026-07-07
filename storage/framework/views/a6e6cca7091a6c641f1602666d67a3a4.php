<div>
    <?php $__env->startSection('title', 'مدیریت API'); ?>

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

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .endpoint-name {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .api-table {
            width: 100%;
            border-collapse: collapse;
        }

        .api-table th,
        .api-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        .api-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .api-table tr:hover {
            background: #f9fafb;
        }
    </style>

    <div class="api-manager-container">
        <h1 style="color: #1f2937; margin-bottom: 24px;">مدیریت API</h1>

        <div class="section-card">
            <h2 style="color: #1f2937; margin-bottom: 16px;">APIهای موجود</h2>
            
            <table class="api-table">
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
                        <td><strong>تخلفات اقامتگران</strong></td>
                        <td>
                            <span class="endpoint-name">/api/admin/violations</span>
                        </td>
                        <td>دریافت لیست کاربران با تخلفات (گروه‌بندی شده)</td>
                        <td>
                            <a href="/api/admin/violations" target="_blank" class="btn btn-primary">تست</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\atlas_report\resources\views\livewire\admin\api-manager.blade.php ENDPATH**/ ?>