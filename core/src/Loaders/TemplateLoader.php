<?php

namespace MMX\Twig\Loaders;

use MMX\Database\Models\Template;
use MMX\Twig\Models\TemplateTime;

class TemplateLoader extends ElementLoader
{
    protected string $model = Template::class;
    protected string $name = 'templatename';
    protected string $modelTime = TemplateTime::class;
    protected string $prefix = 'template:';
}