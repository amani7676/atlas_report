<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class WelcomeMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // ایجاد گزارش پیش‌فرض خوش‌آمدگویی اگر وجود ندارد
            $welcomeReport = Report::where('title', 'گزارش خوش‌آمدگویی')->first();
            
            if (!$welcomeReport) {
                // ابتدا یک دسته بندی برای خوش‌آمدگویی ایجاد می‌کنیم
                $category = \App\Models\Category::where('name', 'خوش‌آمدگویی')->first();
                if (!$category) {
                    $category = \App\Models\Category::create([
                        'name' => 'خوش‌آمدگویی',
                        'description' => 'گزارش‌های خوش‌آمدگویی اقامت‌گران',
                    ]);
                }

                $welcomeReport = Report::create([
                    'category_id' => $category->id,
                    'title' => 'گزارش خوش‌آمدگویی',
                    'description' => 'گزارش خودکار برای پیام‌های خوش‌آمدگویی ارسال شده به اقامت‌گران',
                    'negative_score' => 0,
                    'increase_coefficient' => 1.0,
                ]);
            }

            // به‌روزرسانی تنظیمات برای خوش‌آمدگویی
            $settings = \App\Models\Settings::first();
            
            if ($settings) {
                $settings->update([
                    'welcome_report_id' => $welcomeReport->id,
                    'welcome_start_date' => now()->subDays(7)->format('Y-m-d'), // 7 روز پیش
                    'welcome_check_interval_minutes' => 1, // هر 1 دقیقه
                    'welcome_system_active' => false, // به صورت پیش‌فرض غیرفعال
                ]);
            } else {
                \App\Models\Settings::create([
                    'welcome_report_id' => $welcomeReport->id,
                    'welcome_start_date' => now()->subDays(7)->format('Y-m-d'),
                    'welcome_check_interval_minutes' => 1,
                    'welcome_system_active' => false,
                ]);
            }

            $this->command->info('Welcome message settings seeded successfully.');

        } catch (\Exception $e) {
            $this->command->error('Error seeding welcome message settings: ' . $e->getMessage());
        }
    }
}
