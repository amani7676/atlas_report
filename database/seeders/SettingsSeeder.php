<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Settings;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // بررسی وجود تنظیمات
        $settings = Settings::first();
        
        if (!$settings) {
            Settings::create([
                'refresh_interval' => 5, // 5 دقیقه
                'api_url' => 'http://atlas2.test/api/residents',
            ]);
            
            $this->command->info('تنظیمات پیش‌فرض با موفقیت ایجاد شد.');
        } else {
            $this->command->info('تنظیمات از قبل وجود دارد.');
        }
    }
}
