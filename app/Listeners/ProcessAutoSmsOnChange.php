<?php

namespace App\Listeners;

use App\Events\DatabaseRecordChanged;
use App\Jobs\ProcessAutoSmsOnDatabaseChange;
use App\Models\AutoSms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessAutoSmsOnChange implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DatabaseRecordChanged $event): void
    {
        try {
            // دریافت تمام auto_sms هایی که این جدول را در related_tables دارند
            $autoSmsList = AutoSms::where('is_active', true)
                ->whereNotNull('pattern_id')
                ->get()
                ->filter(function ($autoSms) use ($event) {
                    $relatedTables = $autoSms->related_tables ?? [];
                    return in_array($event->tableName, $relatedTables);
                });

            if ($autoSmsList->isEmpty()) {
                return; // هیچ auto_sms فعالی برای این جدول وجود ندارد
            }

            // پردازش هر auto_sms
            foreach ($autoSmsList as $autoSms) {
                // ایجاد Job برای پردازش
                ProcessAutoSmsOnDatabaseChange::dispatch($autoSms, $event);
            }
        } catch (\Exception $e) {
            \Log::error('Error in ProcessAutoSmsOnChange listener', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
