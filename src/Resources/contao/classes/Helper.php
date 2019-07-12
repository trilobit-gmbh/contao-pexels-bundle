<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-pexels-bundle
 */

namespace Trilobit\PexelsBundle;

use Contao\Controller;
use Contao\DC_File;
use Contao\File;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Helper.
 *
 * @author trilobit GmbH <https://github.com/trilobit-gmbh>
 */
class Helper
{
    /**
     * @return string
     */
    public static function getVendowDir()
    {
        return \dirname(__DIR__);
    }

    /**
     * @return mixed
     */
    public static function getConfigData()
    {
        $strYml = file_get_contents(self::getVendowDir().'/../config/config.yml');

        return Yaml::parse($strYml)['trilobit']['pexels'];
    }

    /**
     * @param $strCacheFile
     *
     * @throws \Exception
     *
     * @return array|mixed
     */
    public static function getCacheData($strCacheFile)
    {
        // prepare cache controll
        $strCachePath = StringUtil::stripRootDir(System::getContainer()->getParameter('kernel.cache_dir'));
        $strCacheFile = $strCachePath.'/contao/pexels/'.$strCacheFile.'.json';

        // Load the cached result
        if (file_exists(TL_ROOT.'/'.$strCacheFile)) {
            $objFile = new File($strCacheFile);

            return json_decode($objFile->getContent(), true);
        }

        return [];
    }

    /**
     * @return mixed
     */
    public static function generateFilterPalette()
    {
        return '123';
        /*
        Controller::loadLanguageFile('tl_pexels');
        Controller::loadDataContainer('tl_pexels');

        $objPexels = new DC_File('tl_pexels');

        return preg_replace('/^(.*?)<fieldset (.*)<\/fieldset>(.*)?$/si', '<fieldset $2</fieldset>', $objPexels->edit());
        */
    }
}
