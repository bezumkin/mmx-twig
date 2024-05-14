<?php

namespace MMX\Twig\Loaders;

use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

class FileLoader extends FilesystemLoader
{
    protected string $path;

    public function __construct(string $path)
    {
        parent::__construct($path);
        $this->path = $path;
    }

    protected function findTemplate(string $name, bool $throw = true): ?string
    {
        if (!str_starts_with($name, 'file:')) {
            return null;
        }
        $name = substr($name, 5);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['tpl', 'html'])) {
            $name .= '.tpl';
        }

        try {
            return parent::findTemplate($name, $throw);
        } catch (LoaderError $e) {
            throw new LoaderError('File template "' . $name . '" not found in "' . $this->path . '"');
        }
    }

    public function exists(string $name): bool
    {
        return $this->findTemplate($name) !== null;
    }
}