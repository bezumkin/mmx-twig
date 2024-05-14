<?php

$_tmp = [
    'elements-path' => 'Корневая директория файловых шаблонов',
    'elements-path_desc' => 'В этой директории должны лежать файлы с расширением *.tpl для Twig',
    'options' => 'Настройки Twig',
    'options_desc' => 'Закодированная в JSON строка с настройками, например {"strict_variables": true}',
    'use-modx' => 'Использовать MODX',
    'use-modx_desc' => 'Вы можете включить потенциально опасное использование объекта MODX в шаблонах',
];
/** @var array $_lang */
$_lang = array_merge($_lang, MMX\Twig\App::prepareLexicon($_tmp, 'setting_' . MMX\Twig\App::NAMESPACE));

unset($_tmp);