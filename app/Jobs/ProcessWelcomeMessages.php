<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\WelcomeMessage;
use App\Models\Resident;
use App\Models\WelcomeMessageLog;
use App\Services\MelipayamakService;

class ProcessWelcomeMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting welcome messages processing');
            
            // دریافت تمام پیام‌های خوش‌آمدگویی فعال
            $welcomeMessages = WelcomeMessage::with('filters')
                ->active()
                ->get();
            
            if ($welcomeMessages->isEmpty()) {
                Log::info('No active welcome messages found');
                return;
            }
            
            $totalProcessed = 0;
            $totalSent = 0;
            $totalFailed = 0;
            
            foreach ($welcomeMessages as $welcomeMessage) {
                try {
                    $result = $this->processWelcomeMessage($welcomeMessage);
                    $totalProcessed += $result['processed'];
                    $totalSent += $result['sent'];
                    $totalFailed += $result['failed'];
                } catch (\Exception $e) {
                    Log::error('Error processing welcome message', [
                        'welcome_message_id' => $welcomeMessage->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            Log::info('Welcome messages processing completed', [
                'total_processed' => $totalProcessed,
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in welcome messages job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * پردازش یک پیام خوش‌آمدگویی خاص
     */
    private function processWelcomeMessage(WelcomeMessage $welcomeMessage): array
    {
        $processed = 0;
        $sent = 0;
        $failed = 0;
        
        try {
            // ساخت کوئری فیلتر شده
            $query = $welcomeMessage->buildResidentQuery();
            
            // دریافت اقامت‌گران واجد شرایط
            $residents = $query->get();
            
            Log::info('Processing welcome message', [
                'welcome_message_id' => $welcomeMessage->id,
                'title' => $welcomeMessage->title,
                'eligible_residents' => $residents->count(),
            ]);
            
            foreach ($residents as $resident) {
                $processed++;
                
                try {
                    // بررسی اینکه آیا اقامت‌گر قبلاً پیام دریافت کرده است
                    if ($welcomeMessage->hasResidentReceivedMessage($resident->resident_id)) {
                        Log::info('Resident already received welcome message', [
                            'resident_id' => $resident->resident_id,
                            'welcome_message_id' => $welcomeMessage->id,
                        ]);
                        continue;
                    }
                    
                    // بررسی تأخیر ارسال
                    if ($welcomeMessage->send_delay_minutes > 0) {
                        $createdAt = $resident->created_at ?? $resident->last_synced_at;
                        if ($createdAt && $createdAt->diffInMinutes(now()) < $welcomeMessage->send_delay_minutes) {
                            Log::info('Resident not eligible yet due to delay', [
                                'resident_id' => $resident->resident_id,
                                'delay_minutes' => $welcomeMessage->send_delay_minutes,
                                'created_minutes_ago' => $createdAt->diffInMinutes(now()),
                            ]);
                            continue;
                        }
                    }
                    
                    // بررسی گزارش‌های ارسال شده
                    if ($this->hasResidentSentReports($resident->resident_id)) {
                        Log::info('Resident has sent reports, skipping welcome message', [
                            'resident_id' => $resident->resident_id,
                        ]);
                        continue;
                    }
                    
                    // ایجاد لاگ ارسال
                    $log = WelcomeMessageLog::create([
                        'welcome_message_id' => $welcomeMessage->id,
                        'resident_id' => $resident->resident_id,
                        'resident_name' => $resident->resident_full_name,
                        'resident_phone' => $resident->resident_phone,
                        'status' => 'pending',
                    ]);
                    
                    // ارسال پیامک
                    if ($welcomeMessage->pattern_code && $resident->resident_phone) {
                        $smsResult = $this->sendWelcomeSms($welcomeMessage, $resident, $log);
                        
                        if ($smsResult['success']) {
                            $sent++;
                            $log->update([
                                'status' => 'sent',
                                'sent_at' => now(),
                                'rec_id' => $smsResult['rec_id'],
                                'response_code' => $smsResult['response_code'],
                                'api_response' => $smsResult['api_response'],
                                'raw_response' => $smsResult['raw_response'],
                            ]);
                            
                            Log::info('Welcome message sent successfully', [
                                'log_id' => $log->id,
                                'resident_id' => $resident->resident_id,
                                'rec_id' => $smsResult['rec_id'],
                            ]);
                        } else {
                            $failed++;
                            $log->update([
                                'status' => 'failed',
                                'error_message' => $smsResult['message'],
                                'response_code' => $smsResult['response_code'],
                                'api_response' => $smsResult['api_response'],
                                'raw_response' => $smsResult['raw_response'],
                            ]);
                            
                            Log::error('Failed to send welcome message', [
                                'log_id' => $log->id,
                                'resident_id' => $resident->resident_id,
                                'error' => $smsResult['message'],
                            ]);
                        }
                    } else {
                        $failed++;
                        $log->update([
                            'status' => 'failed',
                            'error_message' => $welcomeMessage->pattern_code ? 'شماره تلفن اقامت‌گر یافت نشد' : 'کد الگوی پیامک تنظیم نشده است',
                        ]);
                        
                        Log::warning('Cannot send welcome message - missing data', [
                            'log_id' => $log->id,
                            'resident_id' => $resident->resident_id,
                            'has_pattern_code' => !empty($welcomeMessage->pattern_code),
                            'has_phone' => !empty($resident->resident_phone),
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Error processing resident for welcome message', [
                        'welcome_message_id' => $welcomeMessage->id,
                        'resident_id' => $resident->resident_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error in processWelcomeMessage', [
                'welcome_message_id' => $welcomeMessage->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return [
            'processed' => $processed,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }
    
    /**
     * ارسال پیامک خوش‌آمدگویی
     */
    private function sendWelcomeSms(WelcomeMessage $welcomeMessage, Resident $resident, WelcomeMessageLog $log): array
    {
        try {
            // استخراج متغیرها از متن الگو
            $variables = $this->extractPatternVariables($welcomeMessage->pattern_text, $resident);
            
            // دریافت شماره فرستنده و API Key
            $senderNumber = \App\Models\SenderNumber::getActivePatternNumbers()->first();
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
            $bodyId = (int)$welcomeMessage->pattern_code;
            
            $result = $melipayamakService->sendByBaseNumber(
                $resident->resident_phone,
                $bodyId,
                $variables,
                $senderNumberValue,
                $apiKey
            );
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Error sending welcome SMS', [
                'welcome_message_id' => $welcomeMessage->id,
                'resident_id' => $resident->resident_id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'response_code' => null,
                'rec_id' => null,
                'api_response' => null,
                'raw_response' => null,
            ];
        }
    }
    
    /**
     * استخراج متغیرها از متن الگو
     */
    private function extractPatternVariables($patternText, Resident $resident): array
    {
        $variables = [];
        
        // لیست متغیرهای ممکن
        $variableMap = [
            'resident_id' => $resident->resident_id,
            'resident_full_name' => $resident->resident_full_name,
            'resident_phone' => $resident->resident_phone,
            'resident_age' => $resident->resident_age,
            'resident_job' => $resident->resident_job,
            'unit_id' => $resident->unit_id,
            'unit_name' => $resident->unit_name,
            'unit_code' => $resident->unit_code,
            'room_id' => $resident->room_id,
            'room_name' => $resident->room_name,
            'room_code' => $resident->room_code,
            'bed_id' => $resident->bed_id,
            'bed_name' => $resident->bed_name,
            'bed_code' => $resident->bed_code,
        ];
        
        // پیدا کردن تمام متغیرها در متن
        preg_match_all('/\{([^}]+)\}/', $patternText, $matches);
        
        foreach ($matches[1] as $variableName) {
            $variables[] = $variableMap[$variableName] ?? '';
        }
        
        return $variables;
    }
    
    /**
     * بررسی اینکه آیا اقامت‌گر گزارش ارسال کرده است
     */
    private function hasResidentSentReports($residentId): bool
    {
        return \App\Models\ResidentReport::where('resident_id', $residentId)->exists();
    }
}
