<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Support\Eloquent\Model;

class ConsultationFormTemplate extends Model
{
    protected $fillable = [
        'name',
        'title',
        'intro',
        'consent_text',
        'questions',
        'version',
        'is_active',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(ConsultationSubmission::class, 'template_id');
    }
}
