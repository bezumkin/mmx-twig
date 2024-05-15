<?php

namespace MMX\Twig\Loaders;

use Illuminate\Database\Eloquent\Model;
use MMX\Database\Models\Template;
use MMX\Twig\Models\TemplateTime;

class TemplateLoader extends ElementLoader
{
    protected string $model = Template::class;
    protected string $name = 'templatename';
    protected string $modelTime = TemplateTime::class;

    protected function getElement($name): ?Model
    {
        if (!str_starts_with($name, 'template:')) {
            return null;
        }

        return parent::getElement(substr($name, 9));
    }
}