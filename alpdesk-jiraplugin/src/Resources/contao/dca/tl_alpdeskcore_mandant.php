<?php

$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['palettes']['default'] . ';jirahost,jirauser,jirapassword,jiraproject,jiramoneyperhour';
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['jirahost'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['jirahost'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'maxlength' => 250, 'tl_class' => 'w50'),
    'sql' => "varchar(250) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['jirauser'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['jirauser'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'maxlength' => 250, 'tl_class' => 'w50'),
    'sql' => "varchar(250) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['jirapassword'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['jirapassword'],
    'exclude' => true,
    'search' => false,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'maxlength' => 250, 'tl_class' => 'w50', 'hideInput' => false),
    'save_callback' => array
        (
        array('Alpdesk\\AlpdeskJiraPlugin\\Backend\\AlpdeskJiraDcaUtils', 'generateEncryptPassword')
    ),
    'load_callback' => array
        (
        array('Alpdesk\\AlpdeskJiraPlugin\\Backend\\AlpdeskJiraDcaUtils', 'regenerateEncryptPassword')
    ),
    'sql' => "varchar(250) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['jiraproject'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['jiraproject'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'maxlength' => 250, 'tl_class' => 'w50'),
    'sql' => "varchar(250) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_alpdeskcore_mandant']['fields']['jiramoneyperhour'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_alpdeskcore_mandant']['jiramoneyperhour'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'maxlength' => 250, 'tl_class' => 'w50'),
    'sql' => "varchar(250) NOT NULL default ''"
);
