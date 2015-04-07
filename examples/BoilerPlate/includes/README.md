#includes/
All extra classes needed by your extensions should be placed here. One class,
one file!

#includes/api
If you have any API modules in your extension place the source files here.
Don't forget to register them with the MediaWiki autoloader in the setup file.

```php
$wgAutoloadClasses['ApiQueryBoilerPlate'] = __DIR__ . '/includes/api/ApiQueryBoilerPlate.php';
$wgAPIListModules['bsboilerplate'] = 'ApiQueryBoilerPlate';
```

#includes/specials
If you have any SpecialPages in your extension place the source files here.
Don't forget to register them with the MediaWiki autoloader in the setup file.
You will also need an aliases file for proper i18n of your special page.

```php
$GLOBALS['wgAutoloadClasses']['BoilerPlate'] = __DIR__ . '/includes/SpecialBoilerPlate.php';
$wgSpecialPageGroups['BoilerPlate'] = 'bluespice';
$wgSpecialPages['BoilerPlate'] = 'SpecialBoilerPlate';
```

#includes/libs
If you have any third party libraries add them to this directory and include
them in your setup file. You may also use a 'composer.json' file in the root
directory of your extension.