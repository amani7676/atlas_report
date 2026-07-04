<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
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
            flex-wrap: wrap;
            gap: 10px;
        }

        .navbar > * {
            flex-shrink: 0;
        }

        .navbar-title {
            display: none !important;
        }

        @media (min-width: 769px) {
            .navbar-title {
                display: block !important;
            }
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

        .sidebar-menu > li > a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            border-right: 3px solid transparent;
            cursor: pointer;
        }

        .sidebar-menu > li > a:hover,
        .sidebar-menu > li > a.active {
            background: rgba(255, 255, 255, 0.1);
            border-right-color: var(--success-color);
        }

        /* Submenu Styles */
        .menu-item {
            position: relative;
        }

        .menu-item.has-submenu > a::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: auto;
            transition: transform 0.3s;
            font-size: 12px;
        }

        .menu-item.has-submenu.open > a::after {
            transform: rotate(180deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background: rgba(0, 0, 0, 0.2);
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu-item.open > .submenu {
            max-height: 500px;
        }

        .submenu li {
            margin: 0;
        }

        .submenu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px 10px 40px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            border-right: 3px solid transparent;
            font-size: 14px;
        }

        .submenu a:hover,
        .submenu a.active {
            background: rgba(255, 255, 255, 0.15);
            border-right-color: var(--success-color);
            padding-right: 25px;
        }

        .menu-section {
            padding: 15px 20px 10px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .menu-section:first-child {
            margin-top: 0;
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

        /* Force all fixed widths to be responsive */
        @media (max-width: 768px) {
            [style*="width: 300px"],
            [style*="width:300px"],
            [style*="width: 250px"],
            [style*="width:250px"],
            [style*="width: 200px"],
            [style*="width:200px"],
            [style*="width: 150px"],
            [style*="width:150px"],
            [style*="width: 120px"],
            [style*="width:120px"] {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        /* Make all grid layouts responsive */
        @media (max-width: 768px) {
            [style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }
        }

        /* Make all flex containers responsive */
        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column !important;
                gap: 10px !important;
            }

            .d-flex.justify-content-between > * {
                width: 100% !important;
            }
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

        /* Responsive Utilities */
        .table-container,
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: -ms-autohiding-scrollbar;
            display: block;
        }

        /* Force all tables to be scrollable on mobile */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        /* Ensure all tables have minimum width for scrolling */
        @media (max-width: 768px) {
            .table {
                min-width: 600px;
            }
        }

        @media (max-width: 576px) {
            .table {
                min-width: 500px;
            }
        }

        @media (max-width: 480px) {
            .table {
                min-width: 450px;
            }

            /* Always show the operations column (last column) */
            .table th:last-child,
            .table td:last-child {
                display: table-cell !important;
                position: sticky;
                right: 0;
                background: white;
                z-index: 10;
                box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            }

            .table th:last-child {
                background: #f8f9fa;
                z-index: 11;
            }
        }

        /* Force all grid layouts to be responsive */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr !important;
                gap: 15px;
            }
        }

        @media (max-width: 576px) {
            .grid {
                grid-template-columns: 1fr !important;
                gap: 10px;
            }
        }

        /* Responsive Grid Helpers - Force full width on mobile */
        @media (max-width: 992px) {
            .row > [class*="col-lg-"],
            .row > [class*="col-xl-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }

        @media (max-width: 768px) {
            .row > [class*="col-md-"],
            .row > [class*="col-lg-"],
            .row > [class*="col-xl-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            /* Force all columns to stack */
            [class*="col-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 576px) {
            .row > [class*="col-sm-"],
            .row > [class*="col-md-"],
            .row > [class*="col-lg-"],
            .row > [class*="col-xl-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            /* Force all columns to stack on small screens */
            [class*="col-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
                margin-bottom: 8px;
            }
        }

        /* Global Responsive Styles for all pages */
        @media (max-width: 768px) {
            /* Cards */
            .card {
                padding: 12px !important;
                margin-bottom: 12px !important;
            }

            .card-body {
                padding: 12px !important;
            }

            /* Headings */
            h1 { font-size: 24px !important; }
            h2 { font-size: 20px !important; }
            h3 { font-size: 18px !important; }
            h4 { font-size: 16px !important; }
            h5 { font-size: 15px !important; }
            h6 { font-size: 14px !important; }

            /* Tables */
            .table {
                font-size: 12px !important;
                min-width: 600px;
            }

            .table th,
            .table td {
                padding: 8px 6px !important;
                font-size: 12px !important;
            }

            /* Forms */
            .form-control,
            .form-select,
            .form-input {
                font-size: 14px !important;
                padding: 8px 12px !important;
            }

            .form-label {
                font-size: 13px !important;
                margin-bottom: 6px !important;
            }

            /* Buttons */
            .btn {
                padding: 8px 14px !important;
                font-size: 13px !important;
            }

            .btn-sm {
                padding: 6px 12px !important;
                font-size: 12px !important;
            }

            /* Input Groups */
            .input-group {
                width: 100% !important;
            }

            .input-group-text {
                padding: 8px 12px !important;
                font-size: 14px !important;
            }

            /* Badges */
            .badge {
                font-size: 12px !important;
                padding: 5px 10px !important;
            }

            /* Pagination */
            .pagination {
                font-size: 12px !important;
            }

            .page-link {
                padding: 6px 10px !important;
                font-size: 12px !important;
            }

            /* Modals */
            .modal-dialog {
                margin: 10px !important;
                max-width: calc(100% - 20px) !important;
            }

            .modal-content {
                border-radius: 8px !important;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 12px 15px !important;
            }

            /* Alerts */
            .alert {
                font-size: 13px !important;
                padding: 10px 15px !important;
            }

            /* Remove fixed widths */
            [style*="width:"] {
                max-width: 100% !important;
            }

            /* Force full width for common elements */
            .w-100,
            .w-full {
                width: 100% !important;
            }
        }

        @media (max-width: 576px) {
            /* Cards */
            .card {
                padding: 10px !important;
                margin-bottom: 10px !important;
            }

            .card-body {
                padding: 10px !important;
            }

            /* Headings */
            h1 { font-size: 20px !important; }
            h2 { font-size: 18px !important; }
            h3 { font-size: 16px !important; }
            h4 { font-size: 15px !important; }
            h5 { font-size: 14px !important; }
            h6 { font-size: 13px !important; }

            /* Tables */
            .table {
                font-size: 11px !important;
                min-width: 500px;
            }

            .table th,
            .table td {
                padding: 6px 4px !important;
                font-size: 11px !important;
            }

            /* Forms */
            .form-control,
            .form-select,
            .form-input {
                font-size: 13px !important;
                padding: 6px 10px !important;
            }

            .form-label {
                font-size: 12px !important;
            }

            /* Buttons */
            .btn {
                padding: 6px 12px !important;
                font-size: 12px !important;
            }

            .btn-sm {
                padding: 5px 10px !important;
                font-size: 11px !important;
            }

            /* Input Groups */
            .input-group-text {
                padding: 6px 10px !important;
                font-size: 13px !important;
            }

            /* Badges */
            .badge {
                font-size: 11px !important;
                padding: 4px 8px !important;
            }

            /* Pagination */
            .page-link {
                padding: 5px 8px !important;
                font-size: 11px !important;
            }

            /* Modals */
            .modal-dialog {
                margin: 5px !important;
                max-width: calc(100% - 10px) !important;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 10px 12px !important;
            }
        }

        @media (max-width: 480px) {
            /* Cards */
            .card {
                padding: 8px !important;
                margin-bottom: 8px !important;
            }

            /* Headings */
            h1 { font-size: 18px !important; }
            h2 { font-size: 16px !important; }
            h3 { font-size: 15px !important; }
            h4 { font-size: 14px !important; }
            h5 { font-size: 13px !important; }
            h6 { font-size: 12px !important; }

            /* Tables */
            .table {
                font-size: 10px !important;
                min-width: 450px;
            }

            .table th,
            .table td {
                padding: 5px 3px !important;
                font-size: 10px !important;
            }

            /* Always show the operations column (last column) */
            .table th:last-child,
            .table td:last-child {
                display: table-cell !important;
                position: sticky;
                right: 0;
                background: white;
                z-index: 10;
                box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            }

            .table th:last-child {
                background: #f8f9fa;
                z-index: 11;
            }

            /* Forms */
            .form-control,
            .form-select {
                font-size: 12px !important;
                padding: 5px 8px !important;
            }

            /* Buttons */
            .btn {
                padding: 5px 10px !important;
                font-size: 11px !important;
            }

            /* Override all inline width styles */
            [style*="width:"] {
                width: 100% !important;
                max-width: 100% !important;
                min-width: auto !important;
            }

            /* Make flex containers stack */
            [style*="display: flex"] {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            /* Button groups should wrap */
            .btn-group {
                flex-wrap: wrap !important;
            }

            .btn-group .btn {
                margin-bottom: 5px !important;
            }
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            min-width: 300px;
            max-width: 450px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideInLeft 0.3s ease-out;
            direction: rtl;
            border-right: 4px solid;
        }

        .toast.success {
            border-color: #28a745;
        }

        .toast.error {
            border-color: #dc3545;
        }

        .toast.warning {
            border-color: #ffc107;
        }

        .toast.info {
            border-color: #17a2b8;
        }

        .toast-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .toast.success .toast-icon {
            color: #28a745;
        }

        .toast.error .toast-icon {
            color: #dc3545;
        }

        .toast.warning .toast-icon {
            color: #ffc107;
        }

        .toast.info .toast-icon {
            color: #17a2b8;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
            color: #212529;
        }

        .toast-message {
            font-size: 14px;
            color: #6c757d;
            line-height: 1.5;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: color 0.2s;
        }

        .toast-close:hover {
            color: #333;
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutLeft {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(-100%);
                opacity: 0;
            }
        }

        .toast.hiding {
            animation: slideOutLeft 0.3s ease-out forwards;
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

        /* Table Responsive */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 992px) {
            .main-content {
                padding: 15px;
            }

            .card {
                padding: 15px;
                margin-bottom: 15px;
            }

            .table th,
            .table td {
                padding: 10px 8px;
                font-size: 13px;
            }

            .btn {
                padding: 7px 14px;
                font-size: 13px;
            }

            /* Responsive Grid */
            .row > [class*="col-"] {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
                width: 280px;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            /* پیش‌فرض منو بسته باشد */
            .sidebar:not(.open) {
                transform: translateX(100%);
            }

            .content {
                margin-right: 0;
                width: 100%;
            }

            .mobile-menu-btn {
                display: block;
            }

            .navbar {
                padding: 0 15px;
                height: auto;
                min-height: 55px;
                flex-wrap: wrap;
            }

            .navbar h3 {
                font-size: 18px;
                margin-right: 10px;
                flex: 1;
                min-width: 200px;
            }

            .navbar > div {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            #refresh-timer {
                font-size: 12px;
                padding: 4px 10px;
            }

            #refresh-timer span {
                font-size: 12px;
            }

            .main-content {
                padding: 12px;
            }

            .card {
                padding: 12px;
                margin-bottom: 12px;
                border-radius: 8px;
            }

            .card h2,
            .card h3 {
                font-size: 18px;
            }

            .card h4 {
                font-size: 16px;
            }

            .card h5 {
                font-size: 15px;
            }

            .card h6 {
                font-size: 14px;
            }

            .sidebar-menu > li > a {
                padding: 12px 18px;
                font-size: 14px;
            }

            .submenu a {
                padding: 10px 18px 10px 40px;
                font-size: 13px;
            }

            .menu-section {
                padding: 10px 18px 6px;
                font-size: 11px;
            }

            .menu-item.has-submenu > a::after {
                font-size: 12px;
            }

            /* Table Responsive */
            .table-container {
                margin: 0 -12px;
            }

            .table {
                font-size: 12px;
                min-width: 600px;
            }

            .table thead th {
                padding: 8px 6px;
                font-size: 11px;
                white-space: nowrap;
            }

            .table tbody td {
                padding: 8px 6px;
                font-size: 11px;
            }

            /* Form Elements */
            .form-control,
            .form-select {
                font-size: 14px;
                padding: 8px 12px;
            }

            .form-label {
                font-size: 13px;
                margin-bottom: 6px;
            }

            /* Buttons */
            .btn {
                padding: 8px 14px;
                font-size: 13px;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 12px;
            }

            /* Grid System */
            .row {
                margin-left: -8px;
                margin-right: -8px;
            }

            .row > [class*="col-"] {
                padding-left: 8px;
                padding-right: 8px;
                margin-bottom: 12px;
            }

            /* Stats Cards */
            .stats-card {
                padding: 12px;
                margin-bottom: 12px;
            }

            .stats-number {
                font-size: 24px;
            }

            .stats-label {
                font-size: 12px;
            }

            /* Search and Filters */
            .search-box,
            .filter-box {
                margin-bottom: 12px;
            }

            /* Pagination */
            .pagination {
                font-size: 12px;
            }

            .page-link {
                padding: 6px 10px;
                font-size: 12px;
            }

            /* Badge */
            .badge {
                font-size: 11px;
                padding: 4px 8px;
            }

            /* Modal */
            .modal-dialog {
                margin: 10px;
            }

            .modal-content {
                border-radius: 8px;
            }

            .modal-header {
                padding: 12px 15px;
            }

            .modal-body {
                padding: 15px;
            }

            .modal-footer {
                padding: 10px 15px;
            }

            /* Override inline styles for better mobile experience */
            [style*="display: flex"] {
                flex-wrap: wrap !important;
            }

            [style*="width: 250px"],
            [style*="width:250px"],
            [style*="width: 200px"],
            [style*="width:200px"],
            [style*="width: 150px"],
            [style*="width:150px"],
            [style*="width: 120px"],
            [style*="width:120px"] {
                width: 100% !important;
                max-width: 100% !important;
            }

            /* Button groups should wrap */
            .btn-group {
                flex-wrap: wrap !important;
            }

            .btn-group .btn {
                margin-bottom: 5px !important;
            }

            /* Hide some table columns on tablets */
            .table th:nth-child(n+7):nth-child(-n+9),
            .table td:nth-child(n+7):nth-child(-n+9) {
                display: none;
            }

            /* Always show the operations column (last column) */
            .table th:last-child,
            .table td:last-child {
                display: table-cell !important;
                position: sticky;
                right: 0;
                background: white;
                z-index: 10;
                box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            }

            .table th:last-child {
                background: #f8f9fa;
                z-index: 11;
            }
        }

        @media (max-width: 576px) {
            .navbar {
                padding: 0 10px;
                min-height: 50px;
                height: auto;
                flex-wrap: wrap;
            }

            .navbar h3 {
                font-size: 16px;
                margin-right: 8px;
                flex: 1;
                min-width: 150px;
            }

            .navbar > div {
                gap: 8px;
                flex-wrap: wrap;
            }

            #refresh-timer {
                font-size: 11px;
                padding: 3px 8px;
            }

            #refresh-timer span {
                font-size: 11px;
            }

            .navbar a {
                font-size: 12px;
                padding: 4px 8px;
            }

            .navbar a span {
                font-size: 12px;
            }

            .main-content {
                padding: 10px;
            }

            .card {
                padding: 10px;
                margin-bottom: 10px;
            }

            .card h2,
            .card h3 {
                font-size: 16px;
            }

            .card h4 {
                font-size: 15px;
            }

            .card h5 {
                font-size: 14px;
            }

            .card h6 {
                font-size: 13px;
            }

            .table {
                font-size: 11px;
                min-width: 500px;
            }

            .table thead th {
                padding: 6px 4px;
                font-size: 10px;
            }

            .table tbody td {
                padding: 6px 4px;
                font-size: 10px;
            }

            /* Always show the operations column (last column) */
            .table th:last-child,
            .table td:last-child {
                display: table-cell !important;
                position: sticky;
                right: 0;
                background: white;
                z-index: 10;
                box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            }

            .table th:last-child {
                background: #f8f9fa;
                z-index: 11;
            }

            .btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 11px;
            }

            .form-control,
            .form-select {
                font-size: 13px;
                padding: 6px 10px;
            }

            .form-label {
                font-size: 12px;
            }

            .stats-number {
                font-size: 20px;
            }

            .stats-label {
                font-size: 11px;
            }

            /* Override all inline width styles */
            [style*="width:"] {
                width: 100% !important;
                max-width: 100% !important;
                min-width: auto !important;
            }

            /* Make flex containers stack vertically */
            [style*="display: flex"] {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            /* Button groups should wrap */
            .btn-group {
                flex-wrap: wrap !important;
            }

            .btn-group .btn {
                margin-bottom: 5px !important;
            }

            .page-link {
                padding: 5px 8px;
                font-size: 11px;
            }

            .badge {
                font-size: 10px;
                padding: 3px 6px;
            }

            /* Stack columns on very small screens */
            .row > [class*="col-md-"],
            .row > [class*="col-lg-"],
            .row > [class*="col-xl-"] {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }

            /* Hide less important table columns on very small screens */
            .table th:nth-child(n+5):nth-child(-n+8),
            .table td:nth-child(n+5):nth-child(-n+8) {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .sidebar-header h2 {
                font-size: 18px;
            }

            .main-content {
                padding: 8px;
            }

            .card {
                padding: 8px;
            }

            .table {
                font-size: 10px;
                min-width: 450px;
            }

            /* Always show the operations column (last column) */
            .table th:last-child,
            .table td:last-child {
                display: table-cell !important;
                position: sticky;
                right: 0;
                background: white;
                z-index: 10;
                box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            }

            .table th:last-child {
                background: #f8f9fa;
                z-index: 11;
            }

            .btn {
                padding: 5px 10px;
                font-size: 11px;
            }

            .form-control,
            .form-select {
                font-size: 12px;
                padding: 5px 8px;
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
            backdrop-filter: blur(2px);
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* در موبایل overlay فقط زمانی نمایش داده شود که منو باز است */
        @media (max-width: 768px) {
            .overlay {
                display: none;
            }
            
            .overlay.active {
                display: block;
            }
        }
        .rtl-popup {
            direction: rtl;
        }

        /* استایل‌های صفحه‌بندی سفارشی */
        .custom-pagination {
            display: flex !important;
            align-items: center;
            gap: 5px;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-direction: row;
        }

        .custom-pagination .page-item {
            list-style: none;
            display: inline-block;
        }

        .custom-pagination .page-link {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0;
            border: 1px solid #dee2e6;
            color: #0d6efd;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            text-decoration: none;
            background-color: white;
        }

        .custom-pagination .page-link:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-decoration: none;
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
            opacity: 0.5;
        }

        .custom-pagination .page-item.disabled .page-link:hover {
            transform: none;
            box-shadow: none;
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

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

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
                <!-- داشبورد -->
                <li>
                    <a href="/" class="<?php echo e(request()->is('/') ? 'active' : ''); ?>">
                        <i class="fas fa-home"></i>
                        <span>داشبورد</span>
                    </a>
                </li>

                <!-- گزارش‌ها -->
                <div class="menu-section">گزارش‌ها</div>
                <li class="menu-item has-submenu <?php echo e(request()->is('reports*') ? 'open' : ''); ?>">
                    <a href="#" onclick="event.preventDefault(); toggleSubmenu(this);">
                        <i class="fas fa-file-alt"></i>
                        <span>گزارش‌ها</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/reports" class="<?php echo e(request()->is('reports') && !request()->is('reports/create') && !request()->is('reports/edit*') ? 'active' : ''); ?>">
                                <i class="fas fa-list"></i>
                                <span>لیست گزارش‌ها</span>
                            </a>
                        </li>
                        <li>
                            <a href="/reports/create" class="<?php echo e(request()->is('reports/create') ? 'active' : ''); ?>">
                                <i class="fas fa-plus-circle"></i>
                                <span>ایجاد گزارش جدید</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- دسته‌بندی‌ها -->
                <div class="menu-section">دسته‌بندی‌ها</div>
                <li class="menu-item has-submenu <?php echo e(request()->is('categories*') ? 'open' : ''); ?>">
                    <a href="#" onclick="event.preventDefault(); toggleSubmenu(this);">
                        <i class="fas fa-list"></i>
                        <span>دسته‌بندی‌ها</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/categories" class="<?php echo e(request()->is('categories') && !request()->is('categories/create') && !request()->is('categories/edit*') ? 'active' : ''); ?>">
                                <i class="fas fa-list"></i>
                                <span>لیست دسته‌بندی‌ها</span>
                            </a>
                        </li>
                        <li>
                            <a href="/categories/create" class="<?php echo e(request()->is('categories/create') ? 'active' : ''); ?>">
                                <i class="fas fa-plus-circle"></i>
                                <span>ایجاد دسته‌بندی جدید</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- اقامت‌گران -->
                <div class="menu-section">اقامت‌گران</div>
                <li class="menu-item has-submenu <?php echo e(request()->is('residents*') || request()->is('resident-reports*') ? 'open' : ''); ?>">
                    <a href="#" onclick="event.preventDefault(); toggleSubmenu(this);">
                        <i class="fas fa-users"></i>
                        <span>اقامت‌گران</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/residents" class="<?php echo e(request()->is('residents') && !request()->is('residents/group-sms') && !request()->is('residents/expired-today') ? 'active' : ''); ?>">
                                <i class="fas fa-users"></i>
                                <span>مدیریت اقامت‌گران</span>
                            </a>
                        </li>
                        <li>
                            <a href="/resident-reports" class="<?php echo e(request()->is('resident-reports') && !request()->is('resident-reports/notifications') ? 'active' : ''); ?>">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>گزارش‌های تخلفی</span>
                            </a>
                        </li>
                        <li>
                            <a href="/resident-reports/notifications" class="<?php echo e(request()->is('resident-reports/notifications') ? 'active' : ''); ?>">
                                <i class="fas fa-bell"></i>
                                <span>گزارش‌های اطلاع‌رسانی</span>
                            </a>
                        </li>
                        <li>
                            <a href="/residents/group-sms" class="<?php echo e(request()->is('residents/group-sms') ? 'active' : ''); ?>">
                                <i class="fas fa-paper-plane"></i>
                                <span>ارسال گروهی پیامک</span>
                            </a>
                        </li>
                                            </ul>
                </li>

                
                <!-- سایر -->
                <div class="menu-section">سایر</div>
                <li class="menu-item has-submenu <?php echo e(request()->is('table-names*') || request()->is('constants*') || request()->is('sender-numbers*') || request()->is('settings*') || request()->is('patterns*') || request()->is('variables*') || request()->is('api-keys*') || request()->is('api-manager*') || request()->is('admin/card-thresholds*') || request()->is('sms/api-messages*') ? 'open' : ''); ?>">
                    <a href="#" onclick="event.preventDefault(); toggleSubmenu(this);">
                        <i class="fas fa-cogs"></i>
                        <span>سایر</span>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/settings" class="<?php echo e(request()->is('settings*') ? 'active' : ''); ?>">
                                <i class="fas fa-sliders-h"></i>
                                <span>تنظیمات</span>
                            </a>
                        </li>
                        <li>
                            <a href="/patterns" class="<?php echo e(request()->is('patterns*') ? 'active' : ''); ?>">
                                <i class="fas fa-eye"></i>
                                <span>مشاهده الگوها</span>
                            </a>
                        </li>
                        <li>
                            <a href="/api-keys" class="<?php echo e(request()->is('api-keys*') ? 'active' : ''); ?>">
                                <i class="fas fa-key"></i>
                                <span>مدیریت API Key</span>
                            </a>
                        </li>
                        <li>
                            <a href="/api-manager" class="<?php echo e(request()->is('api-manager*') ? 'active' : ''); ?>">
                                <i class="fas fa-server"></i>
                                <span>مدیریت API</span>
                            </a>
                        </li>
                        <li>
                            <a href="/variables" class="<?php echo e(request()->is('variables*') ? 'active' : ''); ?>">
                                <i class="fas fa-code"></i>
                                <span>مدیریت متغیرها</span>
                            </a>
                        </li>
                        <li>
                            <a href="/table-names" class="<?php echo e(request()->is('table-names*') ? 'active' : ''); ?>">
                                <i class="fas fa-table"></i>
                                <span>نام گذاری جداول</span>
                            </a>
                        </li>
                        <li>
                            <a href="/constants" class="<?php echo e(request()->is('constants*') ? 'active' : ''); ?>">
                                <i class="fas fa-cog"></i>
                                <span>ثابت‌ها</span>
                            </a>
                        </li>
                        <li>
                            <a href="/sender-numbers" class="<?php echo e(request()->is('sender-numbers*') ? 'active' : ''); ?>">
                                <i class="fas fa-phone-alt"></i>
                                <span>مدیریت شماره‌های فرستنده</span>
                            </a>
                        </li>
                        <li>
                            <a href="/sms/api-messages" class="<?php echo e(request()->is('sms/api-messages*') ? 'active' : ''); ?>">
                                <i class="fas fa-sms"></i>
                                <span>پیام‌های سامانه ملی پیامک</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- خروج -->
                <li>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin: 0;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" style="width: 100%; text-align: right; background: none; border: none; color: white; padding: 12px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 15px; transition: all 0.3s;" onmouseover="this.style.backgroundColor='rgba(255, 255, 255, 0.1)'" onmouseout="this.style.backgroundColor='transparent'">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>خروج</span>
                        </button>
                    </form>
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
                <div style="margin-right: auto;"></div>
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <!-- تایمر رفرش خودکار -->
                    <div id="refresh-timer" style="display: none; align-items: center; gap: 8px; padding: 5px 12px; background: rgba(67, 97, 238, 0.1); border-radius: 20px; font-size: 14px; color: var(--primary-color); font-weight: 500;">
                        <i class="fas fa-clock"></i>
                        <span id="timer-text">--:--</span>
                    </div>
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.sync-button');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-4149055777-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                    <a href="/residents/expired-today" style="color: var(--primary-color); text-decoration: none; display: flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 5px; transition: all 0.3s;" 
                       class="<?php echo e(request()->is('residents/expired-today') ? 'active' : ''); ?>"
                       onmouseover="this.style.backgroundColor='rgba(67, 97, 238, 0.1)'" 
                       onmouseout="this.style.backgroundColor='transparent'">
                        <i class="fas fa-calendar-times"></i>
                        <span>سررسید امروز</span>
                        <?php
                            try {
                                $todayJalali = class_exists(\Morilog\Jalali\Jalalian::class) ? \Morilog\Jalali\Jalalian::fromCarbon(now())->format('Y/m/d') : now()->format('Y/m/d');
                                $expiredCount = \App\Models\Resident::where('contract_payment_date_jalali', $todayJalali)->whereNotNull('contract_payment_date_jalali')->where('contract_payment_date_jalali', '!=', '')->count();
                            } catch (\Exception $e) {
                                $expiredCount = 0;
                            }
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expiredCount > 0): ?>
                            <span class="badge bg-danger" style="margin-right: 5px; animation: pulse 2s infinite;"><?php echo e($expiredCount); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                    <a href="/residents/group-sms" style="color: var(--primary-color); text-decoration: none; display: flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 5px; transition: all 0.3s;" 
                       class="<?php echo e(request()->is('residents/group-sms') ? 'active' : ''); ?>"
                       onmouseover="this.style.backgroundColor='rgba(67, 97, 238, 0.1)'" 
                       onmouseout="this.style.backgroundColor='transparent'">
                        <i class="fas fa-paper-plane"></i>
                        <span>ارسال گروهی</span>
                    </a>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="main-content">
                <?php echo e($slot); ?>

            </main>
        </div>
    </div>

    
    <div id="toast-container" class="toast-container"></div>

    <script>
        // Check if we're on mobile and close sidebar by default
        function checkMobileAndCloseSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            if (window.innerWidth <= 768) {
                // در موبایل منو پیش‌فرض بسته باشد
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            } else {
                // در دسکتاپ منو باز باشد (اختیاری)
                // sidebar.classList.add('open');
            }
        }

        // Initial check when page loads - منو پیش‌فرض بسته
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            checkMobileAndCloseSidebar();
        });

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

        // Submenu toggle functionality
        function toggleSubmenu(element) {
            const menuItem = element.closest('.menu-item');
            if (menuItem) {
                menuItem.classList.toggle('open');
            }
        }

        // Auto-open submenus based on current route
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            
            // Open submenu if current route matches
            document.querySelectorAll('.menu-item.has-submenu').forEach(item => {
                const submenuLinks = item.querySelectorAll('.submenu a');
                let shouldOpen = false;
                
                submenuLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && currentPath.startsWith(href)) {
                        shouldOpen = true;
                    }
                });
                
                if (shouldOpen) {
                    item.classList.add('open');
                }
            });
        });

        // Toast Notification System
        function showToast(type, title, message, duration = 5000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            toast.innerHTML = `
                <i class="fas ${icons[type] || icons.info} toast-icon"></i>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            container.appendChild(toast);

            // Auto remove after duration (اگر duration = 0 باشد، بسته نمی‌شود)
            if (duration > 0) {
                setTimeout(() => {
                    toast.classList.add('hiding');
                    setTimeout(() => {
                        if (toast.parentElement) {
                            toast.remove();
                        }
                    }, 300);
                }, duration);
            }
            // اگر duration = 0 باشد، toast بسته نمی‌شود و فقط با کلیک روی ضربدر بسته می‌شود
        }

        // Listen for toast events (from Livewire)
        Livewire.on('showToast', (data) => {
            showToast(
                data.type || 'info',
                data.title || 'اعلان',
                data.message || '',
                data.duration !== undefined ? data.duration : 5000
            );
        });
        
        // Listen for toast events (from window events)
        window.addEventListener('showToast', event => {
            const detail = event.detail;
            showToast(
                detail.type || 'info',
                detail.title || 'اعلان',
                detail.message || '',
                detail.duration !== undefined ? detail.duration : 5000
            );
        });

        // SweetAlert2 configuration - غیرفعال شده
        // window.addEventListener('showAlert', event => {
        //     const detail = event.detail;
        //     console.log('showAlert event received:', detail);
        //     
        //     const config = {
        //         icon: detail.type,
        //         title: detail.title,
        //         confirmButtonText: 'باشه',
        //         confirmButtonColor: '#4361ee',
        //         width: '600px',
        //         allowOutsideClick: true,
        //         allowEscapeKey: true,
        //     };
        //     
        //     // اگر HTML وجود داشت، از آن استفاده کن، در غیر این صورت از text
        //     if (detail.html) {
        //         console.log('Using HTML content, length:', detail.html.length);
        //         config.html = detail.html;
        //         // برای اطمینان از نمایش HTML، از html به جای text استفاده می‌کنیم
        //         delete config.text;
        //     } else if (detail.text) {
        //         console.log('Using text content');
        //         config.text = detail.text;
        //     }
        //     
        //     console.log('SweetAlert2 config:', config);
        //     Swal.fire(config);
        // });

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


        // رفرش خودکار صفحه بر اساس تنظیمات
        (function() {
            // جلوگیری از ایجاد interval تکراری
            if (window.timerInterval) {
                clearInterval(window.timerInterval);
                window.timerInterval = null;
            }
            if (window.refreshTimeout) {
                clearTimeout(window.refreshTimeout);
                window.refreshTimeout = null;
            }

                        
            // رفرش خودکار غیرفعال شده است
            
            // تابع برای راه‌اندازی مجدد تایمر (برای استفاده در event listener)
            window.restartTimer = function() {
                if (window.timerInterval) {
                    clearInterval(window.timerInterval);
                }
                localStorage.removeItem('timerStartTime');
                localStorage.removeItem('refreshInterval');
                location.reload();
            };
            
            // تابع برای فرمت کردن زمان (دقیقه:ثانیه)
            function formatTime(totalSeconds) {
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                const m = String(minutes).padStart(2, '0');
                const s = String(seconds).padStart(2, '0');
                return m + ':' + s;
            }

            // متغیر برای ذخیره زمان شروع تایمر
            let timerStartTime = null;
            
            // تابع برای شروع تایمر از مقدار تنظیمات
            function startTimer() {
                timerStartTime = Date.now();
                localStorage.setItem('timerStartTime', timerStartTime);
                localStorage.setItem('lastRefreshTime', timerStartTime);
                updateTimer();
            }
            
            // تابع برای به‌روزرسانی تایمر
            function updateTimer() {
                if (!timerElement || !timerContainer) {
                    return;
                }

                // دریافت زمان شروع از localStorage
                let startTime = parseInt(localStorage.getItem('timerStartTime'));
                
                // اگر زمان شروع وجود نداشت، الان را ثبت کن و از مقدار تنظیمات شروع کن
                if (!startTime) {
                    startTimer();
                    return;
                }

                const now = Date.now();
                const elapsed = now - startTime;
                const refreshIntervalMs = refreshInterval * 60 * 1000;
                const remaining = refreshIntervalMs - elapsed;
                const remainingSeconds = Math.max(0, Math.floor(remaining / 1000));
                
                // نمایش تایمر
                if (remainingSeconds > 0) {
                    timerElement.textContent = formatTime(remainingSeconds);
                    
                    // تغییر رنگ وقتی کمتر از 1 دقیقه باقی مانده
                    const remainingMinutes = Math.floor(remainingSeconds / 60);
                    if (remainingMinutes < 1) {
                        timerContainer.style.background = 'rgba(255, 158, 0, 0.1)';
                        timerContainer.style.color = 'var(--warning-color)';
                    } else {
                        timerContainer.style.background = 'rgba(67, 97, 238, 0.1)';
                        timerContainer.style.color = 'var(--primary-color)';
                    }
                } else {
                    timerElement.textContent = '00:00';
                    timerContainer.style.background = 'rgba(247, 37, 133, 0.1)';
                    timerContainer.style.color = 'var(--danger-color)';
                    
                    // اگر زمان تمام شد، sync کن و دوباره شروع کن
                    if (!window.syncInProgress) {
                        window.syncInProgress = true;
                        triggerRefresh();
                    }
                }
            }

            // تابع برای sync کردن داده‌ها (بدون رفرش صفحه)
            function triggerRefresh() {
                console.log(`⏰ زمان sync رسیده است. در حال فراخوانی API برای sync داده‌ها...`);
                
                // دریافت CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                if (!csrfToken) {
                    console.error('❌ CSRF token not found');
                    setTimeout(() => {
                        startTimer();
                        window.syncInProgress = false;
                    }, 2000);
                    return;
                }
                
                // فراخوانی API برای همگام‌سازی داده‌ها
                fetch('/api/residents/sync', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log('✅ دیتابیس به‌روزرسانی شد:', data.data);
                        
                        // Refresh همه Livewire component ها
                        if (typeof Livewire !== 'undefined') {
                            // Dispatch event برای refresh کردن component ها
                            Livewire.dispatch('residents-synced', {
                                synced_count: data.data.synced_count,
                                created_count: data.data.created_count,
                                updated_count: data.data.updated_count
                            });
                            
                            // کمی تاخیر برای اطمینان از اینکه event dispatch شده است
                            setTimeout(() => {
                                // Force refresh همه component های Livewire
                                if (Livewire.all) {
                                    Livewire.all().forEach(component => {
                                        if (component && typeof component.$refresh === 'function') {
                                            try {
                                                component.$refresh();
                                            } catch (e) {
                                                console.warn('Error refreshing component:', e);
                                            }
                                        }
                                    });
                                }
                            }, 500);
                        }
                        
                        // بعد از sync موفق، زمان شروع تایمر را ریست کن
                        startTimer();
                    } else {
                        console.error('❌ خطا در همگام‌سازی:', data.message);
                        // حتی در صورت خطا، زمان را به‌روزرسانی کن تا تایمر ادامه پیدا کند
                        startTimer();
                    }
                })
                .catch(error => {
                    console.error('❌ خطا در همگام‌سازی:', error);
                    // حتی در صورت خطا، زمان را به‌روزرسانی کن تا تایمر ادامه پیدا کند
                    startTimer();
                })
                .finally(() => {
                    window.syncInProgress = false;
                });
            }

            // نمایش تایمر در همه صفحات
            if (timerContainer) {
                timerContainer.style.display = 'flex';
            }
            
            if (refreshInterval && refreshInterval > 0) {
                console.log(`⏰ رفرش خودکار فعال: صفحه هر ${refreshInterval} دقیقه یکبار به‌روزرسانی می‌شود.`);
                
                // بررسی اینکه آیا زمان شروع تایمر وجود دارد
                const existingStartTime = parseInt(localStorage.getItem('timerStartTime'));
                const existingRefreshInterval = parseInt(localStorage.getItem('refreshInterval'));
                
                // اگر زمان شروع وجود ندارد یا مقدار تنظیمات تغییر کرده، تایمر را از اول شروع کن
                if (!existingStartTime || existingRefreshInterval !== refreshInterval) {
                    console.log('شروع تایمر از مقدار تنظیمات:', refreshInterval, 'دقیقه');
                    startTimer();
                    localStorage.setItem('refreshInterval', refreshInterval);
                } else {
                    // اگر زمان شروع وجود دارد، تایمر را ادامه بده
                    timerStartTime = existingStartTime;
                    updateTimer();
                }
                
                // به‌روزرسانی تایمر هر ثانیه
                if (window.timerInterval) {
                    clearInterval(window.timerInterval);
                }
                window.timerInterval = setInterval(updateTimer, 1000);
                
                // بررسی وضعیت sync هر 30 ثانیه (فقط برای به‌روزرسانی زمان sync در localStorage)
                setInterval(function() {
                    fetch('/api/residents/sync-status')
                        .then(response => response.json())
                        .then(data => {
                            if (data.synced && data.last_sync_time) {
                                // فقط برای اطلاعات، زمان sync را ذخیره کن
                                const serverTime = new Date(data.last_sync_time).getTime();
                                localStorage.setItem('lastRefreshTime', serverTime);
                            }
                        })
                        .catch(error => {
                            console.error('خطا در بررسی وضعیت sync:', error);
                        });
                }, 30000); // هر 30 ثانیه چک کن
            } else {
                // اگر رفرش غیرفعال است، تایمر را نمایش بده اما با پیام غیرفعال
                if (timerElement) {
                    timerElement.textContent = 'غیرفعال';
                }
                if (timerContainer) {
                    timerContainer.style.background = 'rgba(108, 117, 125, 0.1)';
                    timerContainer.style.color = '#6c757d';
                }
                console.log('⏰ رفرش خودکار غیرفعال است (مقدار تنظیمات: ' + refreshInterval + ' دقیقه)');
            }
        })();
    </script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    
    <!-- Persistent Alarm - Method 2: Simple HTML with AJAX -->
    <div id="sync-alarm" style="position: fixed; top: 20px; right: calc(var(--sidebar-width) + 20px); z-index: 10000; background: linear-gradient(135deg, #ff6b6b, #ee5a52); color: white; padding: 16px 20px; border-radius: 12px; box-shadow: 0 8px 25px rgba(238, 90, 82, 0.3); min-width: 320px; max-width: 400px; display: none;">
        <div style="display: flex; align-items: flex-start; gap: 15px;">
            <div style="flex-shrink: 0; font-size: 24px;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 700; margin-bottom: 6px; font-size: 16px;">
                    نیاز به همگام‌سازی
                </div>
                <div style="font-size: 13px; opacity: 0.95; margin-bottom: 12px;">
                    داده‌ها ممکن است به‌روز نباشند. برای دریافت آخرین اطلاعات روی دکمه همگام‌سازی کلیک کنید.
                </div>
                <button onclick="syncData()" id="sync-btn" style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid rgba(255, 255, 255, 0.3); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px;">
                    <i class="fas fa-sync-alt" id="sync-icon"></i>
                    <span id="sync-text">همگام‌سازی داده‌ها</span>
                </button>
            </div>
            <button onclick="closeAlarm()" style="flex-shrink: 0; background: rgba(255, 255, 255, 0.2); border: none; color: white; cursor: pointer; padding: 0; width: 28px; height: 28px; border-radius: 50%; font-size: 14px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
    // نمایش آلارم
    document.addEventListener('DOMContentLoaded', function() {
        const alarm = document.getElementById('sync-alarm');
        if (alarm) {
            alarm.style.display = 'block';
        }
    });

    // تابع همگام‌سازی
    function syncData() {
        const btn = document.getElementById('sync-btn');
        const icon = document.getElementById('sync-icon');
        const text = document.getElementById('sync-text');
        
        // نمایش لودینگ
        btn.disabled = true;
        icon.className = 'fas fa-sync-alt fa-spin';
        text.textContent = 'در حال همگام‌سازی...';
        
        // ارسال درخواست AJAX
        fetch('/sync-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // مخفی کردن آلارم
                document.getElementById('sync-alarm').style.display = 'none';
                // رفرش صفحه بعد از 1 ثانیه
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                // فقط در صورت خطا، پیام ساده نمایش بده
                alert('خطا: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در همگام‌سازی داده‌ها');
        })
        .finally(() => {
            // بازگشت به حالت اولیه
            btn.disabled = false;
            icon.className = 'fas fa-sync-alt';
            text.textContent = 'همگام‌سازی داده‌ها';
        });
    }

    // تابع بستن آلارم
    function closeAlarm() {
        document.getElementById('sync-alarm').style.display = 'none';
    }

    </script>
</body>

</html>

<?php /**PATH C:\laragon\www\atlas_report\resources\views\layouts\app.blade.php ENDPATH**/ ?>