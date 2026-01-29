<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PatternPatternVariable extends Pivot
{
    protected $table = 'pattern_pattern_variables';
    
    protected $fillable = [
        'pattern_id',
        'pattern_variable_id',
        'variable_code',
        'sort_order',
    ];
    
    protected $casts = [
        'sort_order' => 'integer',
    ];
    
    public function pattern()
    {
        return $this->belongsTo(Pattern::class);
    }
    
    public function patternVariable()
    {
        return $this->belongsTo(PatternVariable::class);
    }
}
