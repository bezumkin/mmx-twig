<?php

namespace MMX\Twig\Console\Command;

use MMX\Database\Models\Namespaces;
use MMX\Database\Models\Plugin;
use MMX\Database\Models\SystemSetting;
use MMX\Twig\App;
use MODX\Revolution\modX;
use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Install mmxTwig for MODX 3';
    protected modX $modx;

    public function __construct(modX $modx, ?string $name = null)
    {
        parent::__construct($name);
        $this->modx = $modx;
    }

    public function run(InputInterface $input, OutputInterface $output): void
    {
        $srcPath = MODX_CORE_PATH . 'vendor/' . preg_replace('#-#', '/', App::NAMESPACE, 1);
        $corePath = MODX_CORE_PATH . 'components/' . App::NAMESPACE;

        if (!is_dir($corePath)) {
            symlink($srcPath . '/core', $corePath);
            $output->writeln('<info>Created symlink for "core"</info>');
        }

        if (!Namespaces::query()->find(App::NAMESPACE)) {
            $namespace = new Namespaces();
            $namespace->name = App::NAMESPACE;
            $namespace->path = '{core_path}components/' . App::NAMESPACE . '/';
            $namespace->assets_path = '';
            $namespace->save();
            $output->writeln('<info>Created namespace "' . $namespace->name . '"</info>');
        }

        $settings = [
            'elements-path' => '{core_path}elements/',
            'options' => '',
            'use-modx' => false,
        ];
        foreach ($settings as $key => $value) {
            $key = implode('.', [App::NAMESPACE, $key]);
            if (!SystemSetting::query()->find($key)) {
                $setting = new SystemSetting();
                $setting->key = $key;
                $setting->xtype = is_bool($value) ? 'combo-boolean' : 'textfield';
                $setting->value = $value;
                $setting->namespace = App::NAMESPACE;
                $setting->save();
                $output->writeln('<info>Created system setting "' . $setting->key . '"</info>');
            }
        }

        /** @var Plugin $plugin */
        if (!$plugin = Plugin::query()->where('name', App::NAME)->first()) {
            $plugin = new Plugin();
            $plugin->name = App::NAME;
            $plugin->plugincode = preg_replace('#^<\?php#', '', file_get_contents($corePath . '/elements/plugin.php'));
            $plugin->save();
            $output->writeln('<info>Created plugin "' . $plugin->name . '"</info>');
        }

        $pluginEvents = [
            'OnSiteRefresh',
            'OnChunkSave',
            'OnTemplateSave',
        ];
        foreach ($pluginEvents as $name) {
            if (!$plugin->Events()->where('event', $name)->count()) {
                $plugin->Events()->create(['event' => $name]);
                $output->writeln('<info>Added event "' . $name . '" to plugin "' . $plugin->name . '"</info>');
            }
        }

        $output->writeln('<info>Run Phinx migrations</info>');
        $phinx = new TextWrapper(new PhinxApplication(), ['configuration' => $srcPath . '/core/phinx.php']);
        if ($res = $phinx->getMigrate('local')) {
            $output->writeln(explode(PHP_EOL, $res));
        }

        $this->modx->getCacheManager()->refresh();
        $output->writeln('<info>Cleared MODX cache</info>');
    }
}
