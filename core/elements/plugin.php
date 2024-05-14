<?php
/** @var \MODX\Revolution\modX $modx */

if ($modx->services->has('mmxTwig')) {
    $modx->services->get('mmxTwig')->handleEvent($modx->event);
}