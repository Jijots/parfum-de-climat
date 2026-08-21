<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FragranceSimilarity extends Model
{
    protected $fillable = [
        'fragrance_id',
        'similar_fragrance_id',
        'score',
        'rank',
    ];

    protected $casts = [
        'score' => 'float',
        'rank'  => 'integer',
    ];

    public function fragrance(): BelongsTo
    {
        return $this->belongsTo(Fragrance::class);
    }

    public function similar(): BelongsTo
    {
        return $this->belongsTo(Fragrance::class, 'similar_fragrance_id');
    }
}
