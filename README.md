TrilobitPexelsBundle
==============================================

Mit der Pexels Erweiterung können sie über die Dateiverwaltung von Contao Bilder oder Fotos von der freien Bilddatenbank Pexels herunterladen. Um Pexels benutzen zu können, benötigen sie eine API-Key, den sie nach der Registrierung bei Pexels anfordern können. Sie können außerdem Voreinstellungen für die Pexels-Suche in der Benutzerverwaltung festlegen.


With the Pexels extension you can download images or photos from the free image database Pexels via the file management of Contao. In order to use Pexels, you will need an API key that you can request after registering on the Pexels website. You can also set preferences for the Pexels search in the User Management.


Backend Ausschnitt
------------

![Backend Ausschnitt](docs/images/contao-pexels-bundle.png?raw=true "TrilobitPexelsBundle")


Installation
------------

Install the extension via composer: [trilobit-gmbh/contao-pexels-bundle](https://packagist.org/packages/trilobit-gmbh/contao-pexels-bundle).

And add the following code (with the API-Key from the Pexels Website) to the config.yml of your project.

    contao:
      localconfig:
        pexelsApiKey: 'Your API-Key'
        pexelsImageSource: 'large2x'


Compatibility
-------------

- Contao version ~4.4
