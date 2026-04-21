<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiPrompt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'prompt',
        'response',
        'promptable_id',
        'promptable_type',
    ];

    /**
     * Get the parent promptable model (Document, or others in the future).
     */
    public function promptable(): MorphTo
    {
        return $this->morphTo();
    }
}
