<?php

$_tmp = [
    'elements-path' => 'Root directory of file templates',
    'elements-path_desc' => 'This directory should contain files with the extension *.tpl for Twig',
    'options' => 'Twig options',
    'options_desc' => 'JSON encoded string with Twig options, like {"strict_variables": true}',
    'use-modx' => 'Use MODX',
    'use-modx_desc' => 'You can enable the potentially dangerous use of MODX instance in templates',
];
/** @var array $_lang */
$_lang = array_merge($_lang, MMX\Twig\App::prepareLexicon($_tmp, 'setting_' . MMX\Twig\App::NAMESPACE));

unset($_tmp);