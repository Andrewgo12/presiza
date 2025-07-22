<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceEvaluation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'evidence_id',
        'evaluator_id',
        'rating',
        'comment',
        'recommendation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the evidence that owns this evaluation.
     */
    public function evidence()
    {
        return $this->belongsTo(Evidence::class);
    }

    /**
     * Get the user who made this evaluation.
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Get the recommendation badge color.
     */
    public function getRecommendationBadgeColorAttribute(): string
    {
        return match ($this->recommendation) {
            'approve' => 'bg-green-100 text-green-800',
            'reject' => 'bg-red-100 text-red-800',
            'needs_revision' => 'bg-yellow-100 text-yellow-800',
            'escalate' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the recommendation display name.
     */
    public function getRecommendationDisplayNameAttribute(): string
    {
        return match ($this->recommendation) {
            'approve' => 'Aprobar',
            'reject' => 'Rechazar',
            'needs_revision' => 'Necesita Revisión',
            'escalate' => 'Escalar',
            default => ucfirst($this->recommendation),
        };
    }

    /**
     * Get the rating stars.
     */
    public function getRatingStarsAttribute(): string
    {
        if (!$this->rating) {
            return 'Sin calificación';
        }

        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Scope to get evaluations by recommendation.
     */
    public function scopeByRecommendation($query, $recommendation)
    {
        return $query->where('recommendation', $recommendation);
    }

    /**
     * Scope to get evaluations by rating.
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to get evaluations by evaluator.
     */
    public function scopeByEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }
}
