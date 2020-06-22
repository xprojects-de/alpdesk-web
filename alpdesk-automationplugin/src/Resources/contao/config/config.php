<?php

use Alpdesk\AlpdeskAutomationPlugin\Model\AlpdeskautomationitemsModel;
use Alpdesk\AlpdeskAutomationPlugin\Model\AlpdeskautomationchangesModel;

$GLOBALS['TL_MODELS']['tl_alpdeskautomationitems'] = AlpdeskautomationitemsModel::class;
$GLOBALS['TL_MODELS']['tl_alpdeskautomationchanges'] = AlpdeskautomationchangesModel::class;

$GLOBALS['TL_ADME']['automation'] = 'Alpdesk\\AlpdeskAutomationPlugin\\Elements\\AlpdeskElementAutomation';
$GLOBALS['BE_MOD']['alpdeskautomation']['alpdeskautomationitems'] = array
    (
    'tables' => array('tl_alpdeskautomationitems')
);
$GLOBALS['BE_MOD']['alpdeskautomation']['alpdeskautomationchanges'] = array
    (
    'tables' => array('tl_alpdeskautomationchanges')
);

