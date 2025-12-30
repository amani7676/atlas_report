<?php

namespace App\Traits;

use App\Events\DatabaseRecordChanged;
use App\Models\TableName;
use Illuminate\Support\Facades\Log;

trait TriggersAutoSms
{
    /**
     * Boot the trait
     */
    protected static function bootTriggersAutoSms()
    {
        // فقط برای جداول تعریف شده در table_names
        $tableName = (new static)->getTable();
        $tableNameRecord = TableName::where('table_name', $tableName)
            ->where('is_visible', true)
            ->first();

        if (!$tableNameRecord) {
            return; // این جدول در table_names تعریف نشده
        }

        // Event برای created
        static::created(function ($model) use ($tableName) {
            $residentId = static::getResidentIdFromModel($model);
            try {
                event(new DatabaseRecordChanged(
                    $model,
                    $tableName,
                    'created',
                    $residentId,
                    null,
                    $model->getAttributes()
                ));
            } catch (\Exception $e) {
                Log::error('Error triggering DatabaseRecordChanged event', [
                    'table' => $tableName,
                    'model_id' => $model->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        // Event برای updated
        static::updated(function ($model) use ($tableName) {
            $residentId = static::getResidentIdFromModel($model);
            try {
                event(new DatabaseRecordChanged(
                    $model,
                    $tableName,
                    'updated',
                    $residentId,
                    $model->getOriginal(),
                    $model->getChanges()
                ));
            } catch (\Exception $e) {
                Log::error('Error triggering DatabaseRecordChanged event', [
                    'table' => $tableName,
                    'model_id' => $model->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        // Event برای deleted
        static::deleted(function ($model) use ($tableName) {
            $residentId = static::getResidentIdFromModel($model);
            try {
                event(new DatabaseRecordChanged(
                    $model,
                    $tableName,
                    'deleted',
                    $residentId,
                    $model->getAttributes(),
                    null
                ));
            } catch (\Exception $e) {
                Log::error('Error triggering DatabaseRecordChanged event', [
                    'table' => $tableName,
                    'model_id' => $model->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * دریافت resident_id از model
     */
    protected static function getResidentIdFromModel($model)
    {
        // بررسی فیلدهای معمول برای resident_id
        if (isset($model->resident_id)) {
            return $model->resident_id;
        }

        // برای resident_reports
        if (method_exists($model, 'resident') && $model->resident) {
            return $model->resident->id ?? null;
        }

        // اگر model خودش Resident است
        if (isset($model->id) && get_class($model) === \App\Models\Resident::class) {
            return $model->id;
        }

        // برای سایر جداول، باید رابطه را بررسی کنیم
        // در حال حاضر null برمی‌گردانیم
        return null;
    }
}

