<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-pexels-bundle
 */

// global operations
$GLOBALS['TL_LANG']['tl_pexels']['operationAddFromPexels'][0] = 'Pexels';
$GLOBALS['TL_LANG']['tl_pexels']['operationAddFromPexels'][1] = 'Pexels';

// legends
$GLOBALS['TL_LANG']['tl_pexels']['pexels_search_legend'] = 'Suche';
$GLOBALS['TL_LANG']['tl_pexels']['pexels_result_legend'] = 'Suchergebnisse';

$GLOBALS['TL_LANG']['tl_settings']['pexels_legend'] = $GLOBALS['TL_LANG']['tl_pexels']['pexels_legend'];

// fields
$GLOBALS['TL_LANG']['tl_pexels']['pexelsApiKey'][0] = 'Pexels API-Key';
$GLOBALS['TL_LANG']['tl_pexels']['pexelsApiKey'][1] = 'Bitte geben Sie hier Ihren Pexels-API-Key ein. Weitere Informationen unter <a href="https://www.pexels.com/api/documentation/" target="_blank" rel="noopener noreferrer"><u>www.pexels.com/api/documentation/</u></a>.';
$GLOBALS['TL_LANG']['tl_pexels']['pexelsApiUrl'][0] = 'Pexels API-URL';
$GLOBALS['TL_LANG']['tl_pexels']['pexelsApiUrl'][1] = 'Bitte geben Sie hier Ihren Pexels-API-URL ein. Weitere Informationen unter <a href="https://www.pexels.com/api/documentation/" target="_blank" rel="noopener noreferrer"><u>www.pexels.com/api/documentation/</u></a>.';
$GLOBALS['TL_LANG']['tl_pexels']['pexelsImageSource'][0] = 'Quelle für hochaufgelöste Bilder';
$GLOBALS['TL_LANG']['tl_pexels']['pexelsImageSource'][1] = 'Bitte wählen Sie das Feld für die hochaufgelösten Bilder aus.';

$GLOBALS['TL_LANG']['tl_pexels']['fileupload'][0] = 'Datei-Upload Pexels';
$GLOBALS['TL_LANG']['tl_pexels']['fileupload'][1] = 'Datei-Upload Pexels';
$GLOBALS['TL_LANG']['tl_pexels']['poweredBy'][0] = 'Pexels für Contao &mdash; zur Verfügung gestellt von:';
$GLOBALS['TL_LANG']['tl_pexels']['poweredBy'][1] = 'Pexels für Contao &mdash; zur Verfügung gestellt von...';
$GLOBALS['TL_LANG']['tl_pexels']['searchTerm'][0] = 'Suche';
$GLOBALS['TL_LANG']['tl_pexels']['searchTerm'][1] = 'Bitte geben sie hier ihren Suchbegriff bzw. ihre Suchbegriffe ein.';
$GLOBALS['TL_LANG']['tl_pexels']['searchPexels'][0] = '';
$GLOBALS['TL_LANG']['tl_pexels']['searchPexels'][1] = 'Klicken sie auf \'Bildersuche starten\', um die Suche mit ihrem Suchbegriff zu starten.';

$GLOBALS['TL_LANG']['MSC']['pexels']['cachedResult'] = 'Cache-Ergebnis; Um die Pexels-API für alle schnell zu halten, werden Anfragen 24 Stunden zwischengespeichert.';
$GLOBALS['TL_LANG']['MSC']['pexels']['searchPexels'] = 'Bildersuche starten';
$GLOBALS['TL_LANG']['MSC']['pexels']['searchPexelsResult'] = 'Treffer';
$GLOBALS['TL_LANG']['MSC']['pexels']['photographer'] = 'Photograph';
$GLOBALS['TL_LANG']['MSC']['pexels']['photographerUrl'] = 'Profil';
$GLOBALS['TL_LANG']['MSC']['pexels']['hint'] = '<p>Fotos zur Verfügung gestellt von <a href="https://www.pexels.com/" target="_blank" rel="noopener noreferrer"><u>Pexels</u></a>.</p>'
    .'<p><strong>Richtlinien</strong></p>'
    .'<ul>'
    .'<li>Standardmäßig ist die API beschränkt auf 200 Abfragen pro Stunde und</li>'
    .'<li>20.000 Abfragen pro Monat.</li>'
    .'<li>Nennen Sie immer unseren Fotografen wenn möglich (z.B. "Foto von John Doe auf Pexels" mit einem Link zur Fotoseite auf Pexels).</li>'
    .'</ul>'
    .'<br>'
    .'<p>API-Dokumentation: <a href="https://www.pexels.com/api/documentation/" target="_blank" rel="noopener noreferrer"><u>www.pexels.com/api/documentation/</u></a></p>'
    .'<p>Beachten Sie bitte die <strong>Pexels <a href="https://www.pexels.com/photo-license/" target="_blank" rel="noopener noreferrer"><u>Nutzungsbedingungen</u></a></strong>!</p>'
;

$GLOBALS['TL_LANG']['ERR']['imageSourceNotAvailable'] = 'Die gewünschte Bildquelle "%s" ist nicht mit diesem API-Key verfügbar. Es wurde stattdessen "large2x" verwendet.<br>Weitere Informationen unter <a href="https://www.pexels.com/api/documentation/" target="_blank" rel="noopener noreferrer"><u>www.pexels.com/api/documentation/</u></a>.';
