<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiEvaluationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_evaluation_id',
        'kpi_indicator_id',
        'achievement_value',
        'hasil_value',
        'result_index',
        'final_score',
    ];

    public function evaluation()
    {
        return $this->belongsTo(KpiEvaluation::class, 'kpi_evaluation_id');
    }

    public function indicator()
    {
        return $this->belongsTo(KpiIndicator::class, 'kpi_indicator_id');
    }
}
