<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiKey;

class ApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // افزودن API Key های اولیه از .env
        $consoleApiKey = env('MELIPAYAMAK_CONSOLE_API_KEY');
        $apiKey = env('MELIPAYAMAK_API_KEY');

        if ($consoleApiKey) {
            ApiKey::updateOrCreate(
                ['key_name' => 'console_api_key'],
                [
                    'key_value' => $consoleApiKey,
                    'description' => 'API Key برای Console API (console.melipayamak.com)',
                    'is_active' => true,
                ]
            );
        }

        if ($apiKey) {
            ApiKey::updateOrCreate(
                ['key_name' => 'api_key'],
                [
                    'key_value' => $apiKey,
                    'description' => 'API Key برای REST API قدیمی (rest.payamak-panel.com)',
                    'is_active' => true,
                ]
            );
        }
    }
}
