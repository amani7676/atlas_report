<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WelcomeMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'pattern_code',
        'pattern_text',
        'is_active',
        'send_delay_minutes',
        'send_once_per_resident',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'send_once_per_resident' => 'boolean',
        'send_delay_minutes' => 'integer',
    ];

    /**
     * ارتباط با فیلترها
     */
    public function filters()
    {
        return $this->hasMany(WelcomeMessageFilter::class)->orderBy('priority');
    }

    /**
     * ارتباط با لاگ‌های ارسال
     */
    public function logs()
    {
        return $this->hasMany(WelcomeMessageLog::class);
    }

    /**
     * دریافت پیام‌های فعال
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * ساخت کوئری فیلتر شده برای اقامت‌گران
     */
    public function buildResidentQuery()
    {
        $query = Resident::query();
        
        foreach ($this->filters as $filter) {
            $tableName = $filter->table_name;
            $fieldName = $filter->field_name;
            $operator = $filter->operator;
            $value = $filter->value;
            $logicalOperator = $filter->logical_operator;

            // اگر جدول residents نیست، باید join انجام دهیم
            if ($tableName !== 'residents') {
                $this->applyJoinFilter($query, $tableName, $fieldName, $operator, $value, $logicalOperator);
            } else {
                // فیلتر مستقیم روی residents
                $this->applyDirectFilter($query, $fieldName, $operator, $value, $logicalOperator);
            }
        }
        
        return $query;
    }

    /**
     * اعمال فیلتر مستقیم روی جدول residents
     */
    private function applyDirectFilter($query, $field, $operator, $value, $logicalOperator)
    {
        $processedValue = $this->processFilterValue($value, $operator);
        
        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '<':
            case '>=':
            case '<=':
                if ($logicalOperator === 'or') {
                    $query->orWhere($field, $operator, $processedValue);
                } else {
                    $query->where($field, $operator, $processedValue);
                }
                break;
                
            case 'like':
                $processedValue = '%' . $processedValue . '%';
                if ($logicalOperator === 'or') {
                    $query->orWhere($field, 'like', $processedValue);
                } else {
                    $query->where($field, 'like', $processedValue);
                }
                break;
                
            case 'in':
                $values = explode(',', $processedValue);
                $values = array_map('trim', $values);
                if ($logicalOperator === 'or') {
                    $query->orWhereIn($field, $values);
                } else {
                    $query->whereIn($field, $values);
                }
                break;
                
            case 'not_in':
                $values = explode(',', $processedValue);
                $values = array_map('trim', $values);
                if ($logicalOperator === 'or') {
                    $query->orWhereNotIn($field, $values);
                } else {
                    $query->whereNotIn($field, $values);
                }
                break;
                
            case 'is_null':
                if ($logicalOperator === 'or') {
                    $query->orWhereNull($field);
                } else {
                    $query->whereNull($field);
                }
                break;
                
            case 'is_not_null':
                if ($logicalOperator === 'or') {
                    $query->orWhereNotNull($field);
                } else {
                    $query->whereNotNull($field);
                }
                break;
        }
    }

    /**
     * اعمال فیلتر با join برای جداول دیگر
     */
    private function applyJoinFilter($query, $tableName, $fieldName, $operator, $value, $logicalOperator)
    {
        // تعریف روابط join
        $joinRelations = [
            'units' => ['residents.unit_id', '=', 'units.id'],
            'rooms' => ['residents.room_id', '=', 'rooms.id'],
            'beds' => ['residents.bed_id', '=', 'beds.id'],
            'contracts' => ['residents.contract_id', '=', 'contracts.id'],
        ];
        
        if (!isset($joinRelations[$tableName])) {
            return; // جدول پشتیبانی نشده
        }
        
        $joinCondition = $joinRelations[$tableName];
        
        // انجام join
        $query->leftJoin($tableName, function($join) use ($joinCondition) {
            $join->on($joinCondition[0], '=', $joinCondition[1]);
        });
        
        // اعمال فیلتر روی جدول join شده
        $this->applyDirectFilter($query, $tableName . '.' . $fieldName, $operator, $value, $logicalOperator);
    }

    /**
     * پردازش مقدار فیلتر بر اساس عملگر
     */
    private function processFilterValue($value, $operator)
    {
        if (in_array($operator, ['is_null', 'is_not_null'])) {
            return null;
        }

        if (in_array($operator, ['>', '<', '>=', '<='])) {
            return is_numeric($value) ? (float)$value : $value;
        }

        return $value;
    }

    /**
     * بررسی اینکه آیا اقامت‌گر قبلاً پیام دریافت کرده است
     */
    public function hasResidentReceivedMessage($residentId)
    {
        if (!$this->send_once_per_resident) {
            return false;
        }

        return $this->logs()
            ->where('resident_id', $residentId)
            ->where('status', 'sent')
            ->exists();
    }
}
