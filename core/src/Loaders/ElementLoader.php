<?php

namespace MMX\Twig\Loaders;

use Illuminate\Database\Eloquent\Model;
use MMX\Database\Models\Traits\StaticElement;
use Twig\Loader\LoaderInterface;
use Twig\Source;

abstract class ElementLoader implements LoaderInterface
{
    protected string $model;
    protected string $name;
    protected string $modelTime;
    protected array $cache = [];
    protected array $timestamps = [];

    protected function getElement($name): ?Model
    {
        if (array_key_exists($name, $this->cache)) {
            return $this->cache[$name];
        }

        $this->cache[$name] = (new $this->model())
            ->newQuery()
            ->where(is_numeric($name) ? ['id' => (int)$name] : [$this->name => (string)$name])
            ->first();

        return $this->cache[$name];
    }

    protected function getElementTime(int $id): int
    {
        if (!isset($this->timestamps[$id])) {
            $model = (new $this->modelTime())->newQuery()->select('timestamp')->find($id);
            $this->timestamps[$id] = $model ? (int)$model->timestamp : time();
        }

        return $this->timestamps[$id];
    }

    public function getSourceContext($name): Source
    {
        $content = $path = '';
        /** @var StaticElement $element */
        if ($element = $this->getElement($name)) {
            $content = $element->getContent();
            $path = $element->getStaticFile() ?? '';
        }

        return new Source($content, $name, $path);
    }

    public function getCacheKey($name): string
    {
        return sha1($name);
    }

    public function isFresh($name, $time): bool
    {
        /** @var StaticElement $element */
        if ($element = $this->getElement($name)) {
            $file = $element->getStaticFile();
            $elemTime = $file ? (int)filemtime($file) : $this->getElementTime($element->id);

            return $elemTime < $time;
        }

        return false;
    }

    public function exists($name): bool
    {
        return $this->getElement($name) !== null;
    }
}