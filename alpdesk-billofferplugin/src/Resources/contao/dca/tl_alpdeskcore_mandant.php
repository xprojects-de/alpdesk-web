<?php

$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['palettes']['default'] . ';billtemplate,offertemplate;billmount,offermount';
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['billtemplate'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['billtemplate'],
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_alpdeskcore_pdf_elements.name',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "int(10) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['offertemplate'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['offertemplate'],
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_alpdeskcore_pdf_elements.name',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "int(10) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['billmount'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['billmount'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => array('multiple' => false, 'fieldType' => 'radio'),
    'sql' => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['offermount'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['offermount'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => array('multiple' => false, 'fieldType' => 'radio'),
    'sql' => "blob NULL"
);

