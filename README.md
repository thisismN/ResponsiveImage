Composer Installation
=====================

{
    "minimum-stability": "dev",
    "prefer-stable": true,
    "repositories": [
        {
            "type": "svn",
            "url": "https://code.mnatwork.com/svn/mn/ResponsiveImage/"
        }
    ],
    "require": {
        "mn/responsiveimage": "dev-trunk"
    }
}

Load the Module
===============

application.config.php
----------------------
// This should be an array of module namespaces used in the application.
'modules' => array(
    'ResponsiveImage',
    'Application',
),
...

Configuration
=============
Copy `ResponsiveImage/config/responsiveimage.global.php.dist` to `./config/autoload/responsiveimage.global.php`. 
Change any settings in it or module.config.php according to your needs.

Copy `ResponsiveImage/config/responsiveimage.local.php.dist` to `./config/autoload/responsiveimage.local.php`. 
Change any settings in it or module.config.php according to your local needs.

Install WURFL
=============

http://sourceforge.net/projects/wurfl/files/WURFL/

Download the latest wurfl.zip, extract it and place the wurfl.xml in the directory specified by the config variable:

'config' => array(
        'wurfl' => array(
            'wurflFile' => 'My directory'
        )
);

Example
-------
wget http://sourceforge.net/projects/wurfl/files/WURFL/2.3.5/wurfl-2.3.5.zip/download
unzip wurfl-2.3.5.zip
cp wurfl.xml data/resource/