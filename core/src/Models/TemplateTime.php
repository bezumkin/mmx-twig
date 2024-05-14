<?php

namespace MMX\Twig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MMX\Database\Models\Template;

/**
 * @property int $id
 * @property string $timestamp
 *
 * @property-read Template $template
 */
class TemplateTime extends Model
{
    protected $table = 'mmx_twig_templates_time';
    protected $guarded = [];
    public $timestamps = false;

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'id');
    }
}