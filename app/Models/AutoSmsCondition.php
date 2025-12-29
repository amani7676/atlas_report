<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AutoSmsCondition extends Model
{
    protected $table = 'auto_sms_conditions';

    protected $fillable = [
        'auto_sms_id',
        'field_type',
        'field_name',
        'data_type',
        'operator',
        'value',
        'logical_operator',
        'order',
    ];

    public function autoSms(): BelongsTo
    {
        return $this->belongsTo(AutoSms::class);
    }

    /**
     * ارزیابی شرط برای یک اقامت‌گر خاص
     */
    public function evaluate($residentId = null)
    {
        if ($this->field_type === 'resident') {
            return $this->evaluateResidentField($residentId);
        } elseif ($this->field_type === 'resident_report') {
            return $this->evaluateResidentReportField($residentId);
        } elseif ($this->field_type === 'report') {
            return $this->evaluateReportField($residentId);
        }

        return false;
    }

    /**
     * ارزیابی فیلد از جدول residents
     */
    protected function evaluateResidentField($residentId)
    {
        if (!$residentId) {
            return false;
        }

        $resident = \App\Models\Resident::find($residentId);
        if (!$resident) {
            return false;
        }

        // برای فیلدهای تاریخ، باید به درستی parse کنیم
        $fieldValue = $resident->{$this->field_name} ?? null;
        
        // اگر فیلد تاریخ است و مقدار هم تاریخ است
        if (in_array($this->field_name, ['contract_start_date', 'contract_end_date', 'contract_expiry_date'])) {
            if ($fieldValue instanceof \Carbon\Carbon) {
                $fieldValue = $fieldValue->format('Y-m-d');
            }
        }
        
        return $this->compare($fieldValue, $this->value, $this->operator);
    }

    /**
     * ارزیابی فیلد از جدول resident_reports (aggregate)
     */
    protected function evaluateResidentReportField($residentId)
    {
        if (!$residentId) {
            return false;
        }

        $query = \App\Models\ResidentReport::where('resident_id', $residentId);

        switch ($this->field_name) {
            case 'report_count':
                $fieldValue = $query->count();
                break;
            case 'total_score':
                $fieldValue = $query->join('reports', 'resident_reports.report_id', '=', 'reports.id')
                    ->sum('reports.negative_score') ?? 0;
                break;
            case 'last_report_date':
                $lastReport = $query->orderBy('created_at', 'desc')->first();
                $fieldValue = $lastReport ? $lastReport->created_at->format('Y-m-d') : null;
                break;
            default:
                return false;
        }

        return $this->compare($fieldValue, $this->value, $this->operator);
    }

    /**
     * ارزیابی فیلد از جدول reports
     */
    protected function evaluateReportField($residentId)
    {
        if (!$residentId) {
            return false;
        }

        // این نوع شرط نیاز به بررسی گزارش‌های اقامت‌گر دارد
        $reports = \App\Models\ResidentReport::where('resident_id', $residentId)
            ->with('report')
            ->get();

        foreach ($reports as $residentReport) {
            if ($residentReport->report) {
                $fieldValue = $residentReport->report->{$this->field_name} ?? null;
                if ($this->compare($fieldValue, $this->value, $this->operator)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ساخت query برای استفاده در getMatchingResidents
     */
    public function buildQuery()
    {
        if ($this->field_type === 'resident') {
            return function ($query) {
                $this->applyWhereClause($query, 'residents.' . $this->field_name);
            };
        }

        // برای سایر انواع، نیاز به subquery یا join داریم
        return function ($query) {
            // اینجا باید بر اساس field_type و field_name query مناسب بسازیم
            if ($this->field_type === 'resident_report' && $this->field_name === 'report_count') {
                $query->whereHas('residentReports', function ($q) {
                    // اینجا باید تعداد را بشماریم
                });
            }
        };
    }

    /**
     * اعمال where clause بر اساس operator
     */
    protected function applyWhereClause($query, $field)
    {
        switch ($this->operator) {
            case '>':
                $query->where($field, '>', $this->value);
                break;
            case '<':
                $query->where($field, '<', $this->value);
                break;
            case '=':
                $query->where($field, '=', $this->value);
                break;
            case '>=':
                $query->where($field, '>=', $this->value);
                break;
            case '<=':
                $query->where($field, '<=', $this->value);
                break;
            case '!=':
                $query->where($field, '!=', $this->value);
                break;
            case 'contains':
                $query->where($field, 'like', '%' . $this->value . '%');
                break;
            case 'not_contains':
                $query->where($field, 'not like', '%' . $this->value . '%');
                break;
        }
    }

    /**
     * مقایسه دو مقدار بر اساس operator و data_type
     */
    protected function compare($fieldValue, $compareValue, $operator)
    {
        if ($fieldValue === null) {
            return false;
        }

        $dataType = $this->data_type ?? 'string';

        // تبدیل مقادیر بر اساس نوع داده
        if ($dataType === 'number') {
            $fieldValue = is_numeric($fieldValue) ? (float)$fieldValue : 0;
            $compareValue = is_numeric($compareValue) ? (float)$compareValue : 0;
        } elseif ($dataType === 'date') {
            try {
                // تبدیل fieldValue به Carbon
                if ($fieldValue instanceof \Carbon\Carbon) {
                    $fieldDate = $fieldValue;
                } elseif (is_string($fieldValue)) {
                    $fieldDate = \Carbon\Carbon::parse($fieldValue);
                } else {
                    return false;
                }

                // برای عملگرهای days_after و days_before
                if ($operator === 'days_after' || $operator === 'days_before') {
                    $days = (int)$compareValue;
                    $today = \Carbon\Carbon::today();
                    
                    if ($operator === 'days_after') {
                        // بررسی می‌کند که آیا fieldDate + days >= today است
                        return $fieldDate->copy()->addDays($days)->lte($today);
                    } else {
                        // بررسی می‌کند که آیا fieldDate - days <= today است
                        return $fieldDate->copy()->subDays($days)->gte($today);
                    }
                }

                // برای سایر عملگرها، مقدار compareValue را به عنوان تعداد روز در نظر می‌گیریم
                // و با تاریخ امروز + تعداد روز مقایسه می‌کنیم
                $days = (int)$compareValue;
                $targetDate = \Carbon\Carbon::today()->addDays($days);
                $fieldValue = $fieldDate->format('Y-m-d');
                $compareValue = $targetDate->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }
        } elseif ($dataType === 'boolean') {
            $fieldValue = (bool)$fieldValue;
            $compareValue = filter_var($compareValue, FILTER_VALIDATE_BOOLEAN);
        } else {
            // string
            $fieldValue = (string)$fieldValue;
            $compareValue = (string)$compareValue;
        }

        switch ($operator) {
            case '>':
                if ($dataType === 'number') {
                    return $fieldValue > $compareValue;
                } elseif ($dataType === 'date') {
                    return $fieldValue && $compareValue && strtotime($fieldValue) > strtotime($compareValue);
                }
                return false;
            case '<':
                if ($dataType === 'number') {
                    return $fieldValue < $compareValue;
                } elseif ($dataType === 'date') {
                    return $fieldValue && $compareValue && strtotime($fieldValue) < strtotime($compareValue);
                }
                return false;
            case '=':
                if ($dataType === 'boolean') {
                    return $fieldValue === $compareValue;
                } elseif ($dataType === 'date') {
                    return $fieldValue && $compareValue && $fieldValue === $compareValue;
                }
                return $fieldValue === $compareValue;
            case '>=':
                if ($dataType === 'number') {
                    return $fieldValue >= $compareValue;
                } elseif ($dataType === 'date') {
                    return $fieldValue && $compareValue && strtotime($fieldValue) >= strtotime($compareValue);
                }
                return false;
            case '<=':
                if ($dataType === 'number') {
                    return $fieldValue <= $compareValue;
                } elseif ($dataType === 'date') {
                    return $fieldValue && $compareValue && strtotime($fieldValue) <= strtotime($compareValue);
                }
                return false;
            case '!=':
                if ($dataType === 'boolean') {
                    return $fieldValue !== $compareValue;
                } elseif ($dataType === 'date') {
                    return !$fieldValue || !$compareValue || $fieldValue !== $compareValue;
                }
                return $fieldValue !== $compareValue;
            case 'contains':
                return $dataType === 'string' && stripos((string)$fieldValue, (string)$compareValue) !== false;
            case 'not_contains':
                return $dataType === 'string' && stripos((string)$fieldValue, (string)$compareValue) === false;
            case 'days_after':
            case 'days_before':
                // این عملگرها در بخش date قبلاً پردازش شده‌اند
                return false;
            default:
                return false;
        }
    }
}

