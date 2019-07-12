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
 * Table tl_files
 */

if ('' !== \Config::get('pexelsApiKey')) {
    $GLOBALS['TL_DCA']['tl_files']['config']['onload_callback'][] = ['tl_files_pexels', 'setUploader'];

    $GLOBALS['TL_DCA']['tl_files']['list']['global_operations'] = array_merge(
        ['pexels' => [
            'label' => &$GLOBALS['TL_LANG']['tl_pexels']['operationAddFromPexels'],
            'href' => 'act=paste&mode=move&source=pexels',
            'class' => 'header_pexels',
            'icon' => '/bundles/trilobitpexels/logo.svg',
            'button_callback' => ['tl_files_pexels', 'pexels'],
        ]],
        $GLOBALS['TL_DCA']['tl_files']['list']['global_operations']
    );
}

/**
 * Class tl_files_pexels.
 */
class tl_files_pexels extends Backend
{
    /**
     * tl_files_pexels constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * @param $href
     * @param $label
     * @param $title
     * @param $class
     * @param $attributes
     *
     * @return string
     */
    public function pexels($href, $label, $title, $class, $attributes)
    {
        $canUpload = $this->User->hasAccess('f1', 'fop');
        $canPexels = $this->User->hasAccess('pexels', 'fop');

        return $canPexels && $canUpload ? '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'" class="'.$class.'"'.$attributes.'>'.$label.'</a> ' : '';
    }

    public function setUploader()
    {
        if ('move' === \Input::get('act') && 'pexels' === \Input::get('source')) {
            $this->import('BackendUser', 'User');
            $this->User->uploader = 'Trilobit\PexelsBundle\PexelsZone';
        }
    }
}
