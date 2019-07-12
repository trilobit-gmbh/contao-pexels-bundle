<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-pexels-bundle
 */

// Load language file(s)
System::loadLanguageFile('tl_pexels');

/*
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace(
    ';{proxy_legend',
    ';{pexels_legend:hide},pexelsApiKey,pexelsImageSource;{proxy_legend',
    $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']
);

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['pexelsApiKey'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_pexels']['pexelsApiKey'],
    'inputType' => 'text',
    'eval' => ['tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['pexelsHighResolution'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_pexels']['pexelsHighResolution'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['pexelsImageSource'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_pexels']['pexelsImageSource'],
    'inputType' => 'select',
    'options_callback' => ['tl_settings_pexels', 'getImageSource'],
    'reference' => &$GLOBALS['TL_LANG']['tl_pexels']['options']['image_source'],
    'eval' => ['chosen' => true, 'tl_class' => 'clr w50'],
];

/**
 * Class tl_settings_pexels.
 */
class tl_settings_pexels extends Backend
{
    /**
     * tl_settings_pexels constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getImageSource(DataContainer $dc)
    {
        return array_keys(\Trilobit\PexelsBundle\Helper::getConfigData()['imageSource']);
    }
}
