<?php

namespace MMX\Twig\Loaders;

use MMX\Database\Models\Chunk;
use MMX\Twig\Models\ChunkTime;

class ChunkLoader extends ElementLoader
{
    protected string $model = Chunk::class;
    protected string $name = 'name';
    protected string $modelTime = ChunkTime::class;

    public function exists($name): bool
    {
        return !str_contains($name, ':') && $this->getElement($name) !== null;
    }
}