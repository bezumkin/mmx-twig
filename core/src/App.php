<?php

namespace MMX\Twig;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use MMX\Twig\Loaders\FileLoader;
use MMX\Twig\Models\ChunkTime;
use MMX\Twig\Models\TemplateTime;
use MODX\Revolution\modSystemEvent;
use MODX\Revolution\modX;
use Throwable;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\TwigFilter;

class App extends Environment
{
    public const NAME = 'mmxTwig';
    public const NAMESPACE = 'mmx-twig';

    protected modX $modx;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;

        $loaders = [new Loaders\ChunkLoader(), new Loaders\TemplateLoader()];
        $path = $modx->getOption(self::NAMESPACE . '.elements-path', null, MODX_CORE_PATH . 'elements/', true);
        if (is_readable($path)) {
            $loaders[] = new FileLoader($path);
        } else {
            $this->logEntry(modX::LOG_LEVEL_INFO, '"' . $path . '" is not readable, file loader disabled');
        }
        parent::__construct(new ChainLoader($loaders), $this->getDefaultOptions());

        $this->setDefaultFilters();
        $this->setGlobals();
    }

    public static function getCachePath(bool $create = true): string
    {
        $cache = MODX_CORE_PATH . 'cache/' . self::NAMESPACE;

        $fs = new Filesystem(new LocalFilesystemAdapter($cache));
        if ($create && !$fs->fileExists('/')) {
            $fs->createDirectory('/');
        }

        return $cache;
    }

    public static function clearCache(): void
    {
        $fs = new Filesystem(new LocalFilesystemAdapter(self::getCachePath(false)));
        $fs->deleteDirectory('/');
    }

    public function handleEvent(?modSystemEvent $event): void
    {
        if (!$event) {
            return;
        }

        if ($event->name === 'OnSiteRefresh') {
            $this::clearCache();
            $this->logEntry(modX::LOG_LEVEL_INFO, $this->modx->lexicon('refresh_default'));
        }

        if ($event->name === 'OnChunkSave' && $chunk = $event->params['chunk']) {
            ChunkTime::query()->updateOrCreate(['id' => $chunk->id], ['timestamp' => date('Y-m-d H:i:s')]);
        }

        if ($event->name === 'OnTemplateSave' && $template = $event->params['template']) {
            TemplateTime::query()->updateOrCreate(['id' => $template->id], ['timestamp' => date('Y-m-d H:i:s')]);
        }
    }

    public static function prepareLexicon(array $arr, string $prefix = ''): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $key = !$prefix ? $k : "$prefix.$k";
            if (is_array($v)) {
                $out += self::prepareLexicon($v, $key);
            } else {
                $out[$key] = $v;
            }
        }

        return $out;
    }

    protected function getDefaultOptions(): array
    {
        $options = [
            'auto_reload' => true,
            'strict_variables' => false,
            'autoescape' => false,
            'optimizations' => -1,
            'cache' => self::getCachePath(),
        ];
        try {
            $tmp = $this->modx->getOption(self::NAMESPACE . '.options', null, '{}', true);
            if ($tmp && ($tmp = json_decode($tmp, true, 512, JSON_THROW_ON_ERROR)) && is_array($tmp)) {
                if (!empty($tmp['cache'])) {
                    unset($tmp['cache']);
                } else {
                    $tmp['cache'] = false;
                }
                $options = array_merge($options, $tmp);
            }
        } catch (Throwable $e) {
            $this->logEntry(modX::LOG_LEVEL_ERROR, 'Could not read options from system setting');
        }

        return $options;
    }

    protected function setDefaultFilters(): void
    {
        $esc = static function ($string) {
            $string = preg_replace('/&amp;(#\d+|[a-z]+);/i', '&$1;', htmlspecialchars($string));

            return str_replace(['[', ']', '`', '{', '}'], ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'], $string);
        };
        $this->addFilter(new TwigFilter('tag', $esc));
        $this->addFilter(new TwigFilter('esc', $esc));

        $this->addFilter(new TwigFilter('print', static function ($var, $wrap = true) use ($esc) {
            $output = print_r($var, true);
            $output = $esc($output);
            if ($wrap) {
                $output = '<pre>' . $output . '</pre>';
            }

            return $output;
        }));

        $this->addFilter(new TwigFilter('dump', static function ($var, $wrap = true) use ($esc) {
            $output = var_export($var, true);
            $output = $esc($output);
            if ($wrap) {
                $output = '<pre>' . $output . '</pre>';
            }

            return $output;
        }));
    }

    protected function setGlobals(): void
    {
        $this->addGlobal('env', $_ENV);
        $this->addGlobal('get', $_GET);
        $this->addGlobal('post', $_POST);
        $this->addGlobal('files', $_FILES);
        $this->addGlobal('cookie', $_COOKIE);
        $this->addGlobal('server', $_SERVER);
        $this->addGlobal('session', $_SESSION);
        $this->addGlobal('request', $_REQUEST);

        if ($this->modx->getOption(self::NAMESPACE . '.use-modx')) {
            $this->addGlobal('modx', $this->modx);
        }
    }

    protected function logEntry(int $level, string $message): void
    {
        $this->modx->log($level, '[' . $this::NAME . '] ' . $message);
    }

    public function render($name, array $context = []): string
    {
        try {
            return parent::render($name, $context);
        } catch (Throwable $e) {
            $this->logEntry(modX::LOG_LEVEL_ERROR, $e->getMessage());
        }

        return '';
    }

    public function fetch(string $name, array $context = []): string
    {
        return $this->render($name, $context);
    }
}