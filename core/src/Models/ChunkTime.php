<?php

namespace MMX\Twig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MMX\Database\Models\Chunk;

/**
 * @property int $id
 * @property string $timestamp
 *
 * @property-read Chunk $chunk
 */
class ChunkTime extends Model
{
    protected $table = 'mmx_twig_chunks_time';
    protected $guarded = [];
    public $timestamps = false;

    public function chunk(): BelongsTo
    {
        return $this->belongsTo(Chunk::class, 'id');
    }
}