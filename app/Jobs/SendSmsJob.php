<?php

namespace App\Jobs;

use App\Models\SmsMessageResident;
use App\Services\MelipayamakService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $smsMessageResident;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsMessageResident $smsMessageResident)
    {
        $this->smsMessageResident = $smsMessageResident;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $smsMessageResident = $this->smsMessageResident->fresh();
        
        if ($smsMessageResident->status !== 'pending') {
            return;
        }

        if (empty($smsMessageResident->phone)) {
            $smsMessageResident->update([
                'status' => 'failed',
                'error_message' => 'شماره تلفن موجود نیست',
            ]);
            return;
        }

        $melipayamakService = new MelipayamakService();
        $from = config('services.melipayamak.from', '5000...');
        
        // ساخت متن پیام
        $messageText = $smsMessageResident->smsMessage->text;
        if ($smsMessageResident->smsMessage->link) {
            $messageText .= "\n" . $smsMessageResident->smsMessage->link;
        }

        try {
            $result = $melipayamakService->sendSms(
                $smsMessageResident->phone,
                $from,
                $messageText
            );

            if ($result['success']) {
                $smsMessageResident->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_code' => $result['response_code'] ?? null,
                    'error_message' => null,
                ]);
            } else {
                $smsMessageResident->update([
                    'status' => 'failed',
                    'error_message' => $result['message'],
                    'response_code' => $result['response_code'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in SendSmsJob', [
                'sms_message_resident_id' => $smsMessageResident->id,
                'error' => $e->getMessage(),
            ]);

            $smsMessageResident->update([
                'status' => 'failed',
                'error_message' => 'خطا: ' . $e->getMessage(),
            ]);
        }
    }
}
