<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'evaluator_id',
        'period',
        'evaluation_date',
        'evaluation_notes',
        'action_plan',
        'total_score',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function items()
    {
        return $this->hasMany(KpiEvaluationItem::class, 'kpi_evaluation_id');
    }
}
