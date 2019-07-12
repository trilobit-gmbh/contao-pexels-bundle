<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-pexels-bundle
 */

namespace Trilobit\PexelsBundle;

use Contao\Config;
use Contao\Controller;
use Contao\Dbafs;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\System;

/**
 * Class PexelsZone.
 *
 * @author trilobit GmbH <https://github.com/trilobit-gmbh>
 */
class PexelsZone extends \FileUpload
{
    /**
     * Check the uploaded files and move them to the target directory.
     *
     * @param string $strTarget
     *
     * @throws \Exception
     *
     * @return array
     */
    public function uploadTo($strTarget)
    {
        // Prepare file data
        $arrApiData = Helper::getCacheData(Input::post('tl_pexels_cache'));

        $strImageSource = (empty(Config::get('pexelsImageSource')) ? 'large2x' : Config::get('pexelsImageSource'));

        if (empty($arrApiData)) {
            Message::addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            $this->reload();
        }

        if ('' === $strTarget || \Validator::isInsecurePath($strTarget)) {
            throw new \InvalidArgumentException('Invalid target path '.$strTarget);
        }

        $blnImageSource = true;

        foreach ($arrApiData['photos'] as $value) {
            if (!\in_array((string) $value['id'], Input::post('tl_pexels_imageIds'), true)) {
                continue;
            }
            $arrPathParts = pathinfo(urldecode($value['src']['original']));
            $strFileNameTmp = $strTarget.'/'.$arrPathParts['basename'];

            $arrPathPartsNew = pathinfo(urldecode($value['url']));

            // Sanitize the filename
            try {
                $arrPathPartsNew['basename'] = \StringUtil::sanitizeFileName($arrPathPartsNew['basename']);
            } catch (\InvalidArgumentException $e) {
                \Message::addError($GLOBALS['TL_LANG']['ERR']['filename']);
                $this->blnHasError = true;

                continue;
            }

            $strFileNameNew = $strTarget.'/'.$arrPathPartsNew['basename'].'.'.$arrPathParts['extension'];

            $arrApiData['id'][$value['id']] = [
                'files' => [
                    'api' => $strFileNameTmp,
                    'contao' => $strFileNameNew,
                    'name' => trim(str_replace([$value['id'], '-'], ['', ' '], $arrPathPartsNew['basename'])),
                ],
                'values' => $value,
            ];

            $strDownload = $value['src'][$strImageSource];

            if (empty($value['src'][$strImageSource])) {
                $blnImageSource = false;
                $strDownload = $value['src']['large2x'];
            }

            $arrApiData['id'][$value['id']]['files']['download'] = $strDownload;
        }

        if (!$blnImageSource) {
            Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['imageSourceNotAvailable'], $strImageSource));
            System::log('Pexels image source "'.$strImageSource.'" not available; extended "webformatURL" used instead', __METHOD__, TL_FILES);
        }

        // Upload the files
        $maxlength_kb = $this->getMaximumUploadSize();
        $maxlength_kb_readable = $this->getReadableSize($maxlength_kb);
        $arrUploaded = [];

        $arrLanguages = \Contao\Database::getInstance()
            ->prepare("SELECT COUNT(language) AS language_count, language FROM tl_page WHERE type='root' AND published=1 GROUP BY language ORDER BY language_count DESC")
            ->limit(1)
            ->execute()
            ->fetchAllAssoc();

        if (empty($arrLanguages[0]['language'])) {
            $arrLanguages[0]['language'] = 'en';
        }

        foreach (Input::post('tl_pexels_imageIds') as $value) {
            $strFileTmp = 'system/tmp/'.md5(uniqid(mt_rand(), true));
            $strFileDownload = $arrApiData['id'][$value]['files']['download'];
            $strNewFile = $arrApiData['id'][$value]['files']['contao'];

            /*
            // get files
            $stream = file_get_contents($strFileDownload);

            $fileHandle = fopen(TL_ROOT.'/'.$strFileTmp, 'w');

            fwrite($fileHandle, $stream);
            fclose($fileHandle);
            */

            // file handle
            $fileHandle = fopen(TL_ROOT.'/'.$strFileTmp, 'w');

            // get file: curl
            $objCurl = curl_init($strFileDownload);

            curl_setopt($objCurl, CURLOPT_HEADER, false);
            curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($objCurl, CURLOPT_BINARYTRANSFER, true);

            curl_setopt($objCurl, CURLOPT_USERAGENT, 'Contao Pixabay API');
            curl_setopt($objCurl, CURLOPT_COOKIEJAR, TL_ROOT.'/system/tmp/curl.cookiejar.txt');
            curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($objCurl, CURLOPT_ENCODING, '');
            curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($objCurl, CURLOPT_AUTOREFERER, true);
            curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, false);    // required for https urls
            curl_setopt($objCurl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($objCurl, CURLOPT_TIMEOUT, 30);
            curl_setopt($objCurl, CURLOPT_MAXREDIRS, 10);

            $stream = curl_exec($objCurl);
            $returnCode = curl_getinfo($objCurl, CURLINFO_HTTP_CODE);

            // write
            fwrite($fileHandle, $stream);
            fclose($fileHandle);

            curl_close($objCurl);

            // move file to target
            $this->import('Files');

            // Set CHMOD and resize if neccessary
            if ($this->Files->rename($strFileTmp, $strNewFile)) {
                $this->Files->chmod($strNewFile, Config::get('defaultFileChmod'));

                $objFile = Dbafs::addResource($strNewFile);

                $objFile->meta = serialize([
                    $arrLanguages[0]['language'] => [
                        'title' => 'ID: '.$value
                            .' | '
                            .'Tags: '.$arrApiData['id'][$value]['files']['name']
                            .' | '
                            .'Photographer: '.$arrApiData['id'][$value]['values']['photographer'],
                        'alt' => 'Pexels: '.$arrApiData['id'][$value]['values']['url'],
                    ],
                ]);

                $objFile->save();

                // Notify the user
                Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['MSC']['fileUploaded'], $strNewFile));

                System::log('File "'.$strNewFile.'" has been uploaded', __METHOD__, TL_FILES);

                // Resize the uploaded image if necessary
                $this->resizeUploadedImage($strNewFile);

                $arrUploaded[] = $strNewFile;
            }
        }

        if (empty($arrUploaded)) {
            Message::addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
            $this->reload();
        }

        $this->blnHasError = false;

        return $arrUploaded;
    }

    public function generateMarkup()
    {
        Controller::loadLanguageFile('tl_pexels');

        $arrCache = Helper::getCacheData(Input::get('cache'));
        $arrApiParameter = Helper::getConfigData()['api'];

        $arrGlobalsConfig = $GLOBALS['TL_CONFIG'];

        $pexels_search = '';
        $blnPexelsCache = false;

        if (\count($arrCache)) {
            $blnPexelsCache = true;
            $pexels_search = $arrCache['__api__']['parameter']['query'];
        }

        $this->import('BackendUser', 'User');

        foreach ($arrApiParameter as $key => $value) {
            $GLOBALS['TL_CONFIG'][$key] = $this->User->{('order' === $key ? 'priority' : $key)};

            if ($blnPexelsCache
                && isset($arrCache['__api__']['parameter'][$key])
                && '' !== $arrCache['__api__']['parameter'][$key]
            ) {
                if ('bool' === strtolower($value)) {
                    $GLOBALS['TL_CONFIG'][$key] = 1;
                } elseif ('int' === strtolower($value)) {
                    $GLOBALS['TL_CONFIG'][$key] = \intval($arrCache['__api__']['parameter'][$key], 10);
                } else {
                    $GLOBALS['TL_CONFIG'][$key] = $arrCache['__api__']['parameter'][$key];
                }
            }
        }

        // Generate the markup
        $return = '
<input type="hidden" name="action" value="pexelsupload">

<div id="pexels_inform">
    <h2>'.$GLOBALS['TL_LANG']['tl_pexels']['poweredBy'][0].'</h2>
    <br>
    <!---<a href="https://pexels.com" target="_blank" rel="noopener noreferrer"><img src="/bundles/trilobitpexels/logo.svg" width="100" height="100" style="margin-right: 15px"></a>--->
    <a href="https://pexels.com" target="_blank" rel="noopener noreferrer"><span style="display: inline-block; background-color: #05A081; padding: 9px 12px; margin: 15px 15px 15px 0"><img src="/bundles/trilobitpexels/pexels2x-55493e5c9ae2025ef763e735064deee0a368f537950481d2d4e04e9f0ab02473.png" height="50"></span></a>
    <a href="https://www.trilobit.de" target="_blank" rel="noopener noreferrer"><img src="/bundles/trilobitpexels/trilobit_gmbh.svg" width="auto" height="50"></a><br>
    <div class="hint"><br><br><span>'.$GLOBALS['TL_LANG']['MSC']['pexels']['hint'].'</span></div>
</div>

</div></div>

<div id="pexels_form">
    <fieldset id="pal_pexels_search_legend" class="tl_box">
        <legend onclick="AjaxRequest.toggleFieldset(this,\'pexels_search_legend\',\'tl_pexels\')">'.$GLOBALS['TL_LANG']['tl_pexels']['pexels_search_legend'].'</legend>
        <div class="w50 widget">
            <h3>'.$GLOBALS['TL_LANG']['tl_pexels']['searchTerm'][0].'</h3>
            <input name="pexels_search" type="text" value="'.$pexels_search.'" class="tl_text search">
            <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_pexels']['searchTerm'][1].'</p>
        </div>

        <div class="w50 widget">
            <h3>'.$GLOBALS['TL_LANG']['tl_pexels']['pexels']['searchPexels'][0].'</h3>
            <button class="tl_submit">'.$GLOBALS['TL_LANG']['MSC']['pexels']['searchPexels'].'</button>
            <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_pexels']['searchPexels'][1].'</p>
        </div>
    </fieldset>
    
    <fieldset id="pal_pexels_result_legend" class="tl_box collapsed">
        <legend onclick="AjaxRequest.toggleFieldset(this,\'pexels_result_legend\',\'tl_pexels\')">'.$GLOBALS['TL_LANG']['tl_pexels']['pexels_result_legend'].'</legend>
        <div class="widget clr" id="pexels_images">
            <div class="widget"><p>'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p></div>
        </div>
        <div class="tl_box clr" id="pexels_pagination">
        </div>
    </fieldset>
</div>

<div><div>

<script>
    window.addEventListener("load", function(event) {
        //$$(\'div.tl_formbody_submit\').addClass(\'invisible\');
    });

    var pexelsImages           = $(\'pexels_images\');
    var pexelsPagination       = $(\'pexels_pagination\');
    var pexelsPages            = 1;
    var resultsPerPage         = \''.(floor(Config::get('resultsPerPage') / 4) * 4).'\';
    var strHtmlEmpty           = \'<div class="widget"><p>'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['noResult']).'<\/p><\/div>\';
    var strHtmlGoToPage        = \''.sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['goToPage']), '##PAGE##').'\';
    var strHtmlPhotographer    = \''.$GLOBALS['TL_LANG']['MSC']['pexels']['photographer'].'\';
    var strHtmlPhotographerUrl = \''.$GLOBALS['TL_LANG']['MSC']['pexels']['photographerUrl'].'\';
    var strHtmlCachedResult    = \''.$GLOBALS['TL_LANG']['MSC']['pexels']['cachedResult'].'\';
    var blnAuoSearch           = \''.($blnPexelsCache ? 'true' : 'false').'\';

    function pexelsGoToPage(page)
    {
        return strHtmlGoToPage.replace("##PAGE##", page);
    }

    function pexelsImagePagination(totalHits)
    {
        var paginationLinks = 7;
        var strHtmlPagination;
        var firstOffset;
        var lastOffset;
        var firstLink;
        var lastLink;

        // get pages
        pexelsPages = Math.ceil(totalHits / resultsPerPage);

        // get links
        paginationLinks = Math.floor(paginationLinks / 2);

        firstOffset = pexelsPage - paginationLinks - 1;

        if (firstOffset > 0) firstOffset = 0;

        lastOffset = pexelsPage + paginationLinks - pexelsPages;

        if (lastOffset < 0) lastOffset = 0;

        firstLink = pexelsPage - paginationLinks - lastOffset;

        if (firstLink < 1) firstLink = 1;

        lastLink = pexelsPage + paginationLinks - firstOffset;

        if (lastLink > pexelsPages) lastLink = pexelsPages;

        // html: open pagination container
        strHtmlPagination = \'<div class="pagination">\'
            + \'<p>'.preg_replace('/^(.*?)%s(.*?)%s(.*?)$/', '$1\' + pexelsPage + \'$2\' + pexelsPages + \'$3', $GLOBALS['TL_LANG']['MSC']['totalPages']).'<\/p>\'
            + \'<ul>\'
            ;

        // html: previous
        if (pexelsPage > 1)
        {
            strHtmlPagination += \'<li class="first">\'
                + \'<a href="#" onclick="return pexelsSearchUpdate(1);" class="first" title="\' + pexelsGoToPage(1) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['first']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                + \'<li class="previous">\'
                + \'<a href="#" onclick="return pexelsSearchUpdate(pexelsPage-1);" class="previous" title="\' + pexelsGoToPage(pexelsPage-1) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['previous']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                ;
        }

        // html: links
        if (pexelsPages > 1)
        {
            for (i=firstLink; i<=lastLink; i++)
            {
                if (i == pexelsPage)
                {
                    strHtmlPagination += \'<li><span class="active">\' + pexelsPage + \'<\/span><\/li>\'
                }
                else
                {
                    strHtmlPagination += \'<li><a href="#" onclick="return pexelsSearchUpdate(\' + i + \');" class="link" title="\' + pexelsGoToPage(i) + \'">\' + i + \'<\/a><\/li>\'
                }
            }
        }

        // html: next
        if (pexelsPage < pexelsPages)
        {
            strHtmlPagination += \'<li class="next">\'
                + \'<a href="#" onclick="return pexelsSearchUpdate(pexelsPage+1);" class="next" title="\' + pexelsGoToPage(pexelsPage+1) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['next']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                + \'<li class="last">\'
                + \'<a href="#" onclick="return pexelsSearchUpdate(\' + pexelsPages + \');" class="last" title="\' + pexelsGoToPage(pexelsPages) + \'">\'
                + \''.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['last']).'\'
                + \'<\/a>\'
                + \'<\/li>\'
                ;
        }

        // html: close pagination container
        strHtmlPagination += \'<\/ul>\'
            + \'<\/div>\'
            ;

        pexelsPagination.innerHTML = strHtmlPagination;
    }

    function pexelsImageList(pexelsJsonData)
    {
        var strHtmlImages;

        pexelsImages.innerHTML = strHtmlEmpty;
        
        if (pexelsJsonData.totalHits > 0)
        {
            //$$(\'div.tl_formbody_submit\').removeClass(\'invisible\');

            strHtmlImages = \'\'
                + \'<input type="hidden" name="tl_pexels_images" value="">\'
                + \'<input type="hidden" name="tl_pexels_imageIds" value="">\'
                + \'<input type="hidden" name="tl_pexels_cache" value="\' + pexelsJsonData.__api__.cache + \'">\'
                + \'<div class="widget">\'
                + \'<h3>\' + pexelsJsonData.totalHits + \' '.$GLOBALS['TL_LANG']['MSC']['pexels']['searchPexelsResult'].'<\/h3>\'
                + \'<\/div>\'
                + \'<div class="flex-container">\'
                ;

            for (var key in pexelsJsonData.photos)
            {
                if (pexelsJsonData.photos.hasOwnProperty(key))
                {
                    var value = pexelsJsonData.photos[key];
    
                    strHtmlImages += \'\'
                        + \'<div class="widget preview" id="pexels_preview_\' + key + \'">\'
                            + \'<label for="pexels_image_\' + key + \'">\'
                            + \'<div class="image-container" style="background-image:url(\' + value.src.large + \')">\'
                                + \'<a href="contao/popup?src=\' + value.url + \'" \'
                                    + \' title="\' + value.photographer + \'" \'
                                    + \' onclick="Backend.openModalIframe({title:\\\'\' + value.photographer + \'\\\', url:\\\'\' + value.url + \'\\\'});return false" \'
                                + \'>\'
                                    + \'<!---<img src="\' + value.src.large + \'">--->\'
                                + \'<\/a>\'
                            + \'<\/div>\'
                            + \'<br>\'
                            + \'<input type="checkbox" id="pexels_image_\' + key + \'" value="\' + value.id + \'" name="tl_pexels_imageIds[]" onclick="$$(\\\'#pexels_preview_\' + key + \'\\\').toggleClass(\\\'selected\\\')">\'
                                + \'ID: <strong>\' + value.id + \'<\/strong>\'
                            + \'<table class="tl_show">\'
                                + \'<tbody>\'
                                    + \'<tr>\'
                                        + \'<td class="tl_bg"><span class="tl_label">\' + strHtmlPhotographer + \': <\/span><\/td>\'
                                        + \'<td class="tl_bg">\'
                                                + value.photographer
                                        + \'<\/td>\'
                                    + \'<\/tr>\'
                                    + \'<tr>\'
                                        + \'<td><span class="tl_label">\' + strHtmlPhotographerUrl + \': <\/span><\/td>\'
                                        + \'<td>\'
                                            + \'<a href="contao/popup?src=\' + value.photographer_url + \'" \'
                                                + \' title="\' + value.photographer_url + \'" \'
                                                + \' onclick="Backend.openModalIframe({title:\\\'\' + value.photographer_url + \'\\\', url:\\\'\' + value.photographer_url + \'\\\'});return false" \'
                                            + \'>\'
                                                + value.photographer_url
                                            + \'<\/a>\'
                                        + \'<\/td>\'
                                    + \'<\/tr>\'
                                + \'<\/tbody>\'
                            + \'<\/table>\'
                            + \'<\/label>\'
                        + \'<\/div>\'
                        ;
                }
            }
            
            strHtmlImages += \'<\/div>\';

            strHtmlImages += (pexelsJsonData.__api__.cachedResult ? \'<br clear="all"><div class="widget"><p class="tl_help tl_tip">\' + strHtmlCachedResult + \'<\/p><\/div>\' : \'\');

            pexelsImages.innerHTML = strHtmlImages;
            pexelsImagePagination(pexelsJsonData.totalHits);

            new Fx.Scroll(window).toElement(\'pal_pexels_result_legend\');
        }
    }

    function pexelsException(pexelsJsonData)
    {
        pexelsImages.innerHTML = \'<br clear="all">\'
            + \'<div class="widget tl_error">\'
                + \'<p>\'
                    + \'<strong>#\' + pexelsJsonData.__api__.exceptionId + \'</strong>\'
                + \'<\/p>\'
                + \'<p>\'
                    + pexelsJsonData.__api__.exceptionMessage
                + \'<\/p>\'
            + \'<\/div>\'
            ;
    }

    function pexelsApi(search)
    {
        //$$(\'div.tl_formbody_submit\').addClass(\'invisible\');
        
        pexelsPagination.innerHTML = \'&nbsp;\';
        pexelsImages.innerHTML = \'<div class="spinner"><\/div>\';

        var xhr = new XMLHttpRequest();
        var url =\''.ampersand(Environment::get('script'), true).'/trilobit/pexels\'
            + \'?query=\'    + encodeURIComponent(search)
            + \'&page=\'     + pexelsPages
            + \'&per_page=\' + resultsPerPage
            ;
        
        xhr.open(\'GET\', url);
        xhr.onreadystatechange = function()
        {
            if (   this.status == 200
                && this.readyState == 4
            )
            {
                var pexelsJsonData = JSON.parse(this.responseText);

                if (   pexelsJsonData
                    && pexelsJsonData.__api__
                    && pexelsJsonData.__api__.exceptionId
                )
                {
                    pexelsException(pexelsJsonData);
                }
                else
                {
                    pexelsImageList(pexelsJsonData);
                }

                return false;
            }

            var pexelsJsonData = pexelsJsonData || {};
                pexelsJsonData.__api__ = pexelsJsonData.__api__ || {};

                pexelsJsonData.__api__.exceptionId = this.status;
                pexelsJsonData.__api__.exceptionMessage = \'[ERROR \' + this.status + \'] Please try again...\';

            pexelsException(pexelsJsonData);

        };
        xhr.send();

        return false;
    }

    function pexelsSearchUpdate(page)
    {
        if (page !== undefined)
        {
            pexelsPage = page;
        }

        var search = $$(\'input[name="pexels_search"]\').get(\'value\');

        $$(\'#pal_pexels_result_legend\').removeClass(\'collapsed\');
        $$(\'#pal_pexels_filter_legend\').addClass(\'collapsed\');

        if (   search === undefined
            || search === \'\'
        )
        {
            pexelsImages.innerHTML = \'\';
            pexelsImages.innerHTML = strHtmlEmpty;

            return false;
        }

        pexelsApi(search);
        

        return false;
    }

    function pexelsSearch()
    {
        $$(\'#pexels_form button.tl_submit\').addEvent(\'click\', function(e) {
            e.stop();

            return pexelsSearchUpdate(1);            
        });
    }

    pexelsSearch();
    
    if (blnAuoSearch) pexelsSearchUpdate('.$GLOBALS['TL_CONFIG']['page'].');
</script>';

        $GLOBALS['TL_CONFIG'] = $arrGlobalsConfig;

        return $return;
    }
}

class_alias(PexelsZone::class, 'PexelsZone');
