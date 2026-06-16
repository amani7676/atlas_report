<?php

namespace App\Jobs;

use App\Models\SmsMessageResident;
use App\Models\SenderNumber;
use App\Services\MelipayamakService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPatternSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $smsMessageResidentId;
    private $phone;
    private $patternCode;
    private $variables;

    public function __construct($smsMessageResidentId, $phone, $patternCode, $variables)
    {
        $this->smsMessageResidentId = $smsMessageResidentId;
        $this->phone = $phone;
        $this->patternCode = $patternCode;
        $this->variables = $variables;
    }

    public function handle()
    {
        try {
            $smsMessageResident = SmsMessageResident::find($this->smsMessageResidentId);
            
            if (!$smsMessageResident) {
                Log::error('SendPatternSmsJob - SMS message resident not found', [
                    'sms_message_resident_id' => $this->smsMessageResidentId,
                ]);
                return;
            }

            // دریافت شماره فرستنده و API Key
            $senderNumber = SenderNumber::getActivePatternNumbers()->first();
            $senderNumberValue = $senderNumber ? $senderNumber->number : null;
            $apiKey = $senderNumber ? $senderNumber->api_key : null;

            // اگر API Key از sender number دریافت نشد، از جاهای دیگر استفاده می‌کنیم
            if (empty($apiKey)) {
                $dbConsoleKey = \App\Models\ApiKey::getKeyValue('console_api_key');
                $dbApiKey = \App\Models\ApiKey::getKeyValue('api key');
                $configConsoleKey = config('services.melipayamak.console_api_key');
                $configApiKey = config('services.melipayamak.api_key');

                $apiKey = $dbConsoleKey
                    ?: $dbApiKey
                    ?: $configConsoleKey
                    ?: $configApiKey;
            }

            // ارسال پیامک با متد SendByBaseNumber
            $melipayamakService = new MelipayamakService();
            $bodyId = (int)$this->patternCode;

            $result = $melipayamakService->sendByBaseNumber(
                $this->phone,
                $bodyId,
                $this->variables,
                $senderNumberValue,
                $apiKey
            );

            // به‌روزرسانی وضعیت
            if ($result['success']) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $result['response_code'] ?? null,
                    'rec_id' => $result['rec_id'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);

                Log::info('SendPatternSmsJob - SMS sent successfully', [
                    'sms_message_resident_id' => $smsMessageResident->id,
                    'rec_id' => $result['rec_id'],
                    'response_code' => $result['response_code'],
                ]);
            } else {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'خطا در ارسال',
                    'response_code' => $result['response_code'] ?? null,
                    'rec_id' => $result['rec_id'] ?? null,
                    'api_response' => $result['api_response'] ?? null,
                    'raw_response' => $result['raw_response'] ?? null,
                ]);

                Log::error('SendPatternSmsJob - SMS sending failed', [
                    'sms_message_resident_id' => $smsMessageResident->id,
                    'error_message' => $result['message'],
                    'response_code' => $result['response_code'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('SendPatternSmsJob - Exception', [
                'sms_message_resident_id' => $this->smsMessageResidentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // به‌روزرسانی وضعیت به failed در صورت خطا
            if (isset($smsMessageResident)) {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }
}
