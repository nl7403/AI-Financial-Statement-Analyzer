<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analysis extends Model
{
    protected $fillable = [
        'user_id',
        'statement_type',
        'input_json',
        'report_text',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}