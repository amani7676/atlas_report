<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoSms extends Model
{
    protected $table = 'auto_sms';

    protected $fillable = [
        'title',
        'text',
        'pattern_id',
        'send_type',
        'scheduled_at',
        'is_active',
        'last_checked_at',
        'last_sent_at',
        'total_sent',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'total_sent' => 'integer',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(AutoSmsCondition::class)->orderBy('order');
    }

    public function pattern()
    {
        return $this->belongsTo(Pattern::class);
    }

    /**
     * بررسی اینکه آیا شرط‌ها برقرار هستند یا نه
     */
    public function checkConditions($residentId = null)
    {
        $conditions = $this->conditions;
        
        if ($conditions->isEmpty()) {
            return true; // اگر شرطی نباشد، همیشه true
        }

        $result = null;
        $lastLogicalOperator = null;

        foreach ($conditions as $condition) {
            $conditionResult = $condition->evaluate($residentId);

            if ($result === null) {
                $result = $conditionResult;
            } else {
                if ($lastLogicalOperator === 'AND') {
                    $result = $result && $conditionResult;
                } else { // OR
                    $result = $result || $conditionResult;
                }
            }

            $lastLogicalOperator = $condition->logical_operator;
        }

        return $result;
    }

    /**
     * دریافت لیست اقامت‌گرانی که شرط‌ها را برآورده می‌کنند
     */
    public function getMatchingResidents()
    {
        $conditions = $this->conditions;
        
        if ($conditions->isEmpty()) {
            return Resident::all();
        }

        // ساخت query بر اساس شرط‌ها
        $query = Resident::query();

        foreach ($conditions as $index => $condition) {
            $conditionQuery = $condition->buildQuery();
            
            if ($index === 0) {
                $query->where($conditionQuery);
            } else {
                if ($condition->logical_operator === 'AND') {
                    $query->where($conditionQuery);
                } else {
                    $query->orWhere($conditionQuery);
                }
            }
        }

        return $query->get();
    }
}

