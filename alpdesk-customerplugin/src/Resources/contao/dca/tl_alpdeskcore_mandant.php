<?php

$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['palettes']['default'] . ';customerdb';
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['customerdb'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['customerdb'],
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_alpdeskcore_databasemanager.title',
    'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
    'sql' => "int(10) unsigned NOT NULL default '0'"
);
