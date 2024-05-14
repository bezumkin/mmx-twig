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
    protected string $prefix;
    protected array $cache = [];

    protected function getElement($name): ?Model
    {
        if ($this->prefix) {
            if (!str_starts_with($name, $this->prefix)) {
                return null;
            }
            $name = substr($name, strlen($this->prefix));
        }

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $element = (new $this->model())
            ->newQuery()
            ->where(is_numeric($name) ? ['id' => (int)$name] : [$this->name => (string)$name])
            ->first();

        if ($element) {
            $this->cache[$name] = $element;
        }

        return $element;
    }

    protected function getElementTime(int $id): ?Model
    {
        return (new $this->modelTime())->newQuery()->find($id);
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
            if ($file = $element->getStaticFile()) {
                $elemTime = (int)filemtime($file);
            } elseif ($timestamp = $this->getElementTime($element->id)) {
                $elemTime = (int)strtotime($timestamp->timestamp);
            } else {
                $elemTime = time();
            }

            return $elemTime < $time;
        }

        return false;
    }

    public function exists($name): bool
    {
        return $this->getElement($name) !== null;
    }
}