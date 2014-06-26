# Installation
## Composer Installation
```
{
    "require": {
        "thisismn/responsiveimage": "dev-master"
    }
}
```

## Load the Module

config/application.config.php
```
'modules' => array(
    'ResponsiveImage',
    'Application',
),
```

## Configuration
Copy `ResponsiveImage/config/responsiveimage.global.php.dist` to `./config/autoload/responsiveimage.global.php`. 

Copy `ResponsiveImage/config/responsiveimage.local.php.dist` to `./config/autoload/responsiveimage.local.php`. 

Change any settings in these files according to your needs.  
Ensure that the cache and persistence directories are writable.

## Install WURFL
http://sourceforge.net/projects/wurfl/files/WURFL/

Download the latest wurfl.zip, extract it and place the wurfl.xml in the directory specified by the config variable:
```
'config' => array(
        'wurfl' => array(
            'wurflFile' => 'My directory'
        )
);
```

### Example
```
wget http://sourceforge.net/projects/wurfl/files/WURFL/2.3.5/wurfl-2.3.5.zip/download
unzip wurfl-2.3.5.zip
cp wurfl.xml data/resource/
```

# Usage


## View
In the view call the ResponsiveRoute helper passing in the recipe name and the image source.
```
<img src="<?= $this->responsiveRoute('hero', '/img/bananaman.jpg'); ?>" alt="Eric Wimp">
```

## Recipes
The JSON files in the recipes directory control how the image will appear on different devices.

Devices detection is either mobile, tablet or desktop. If detection fails a default configuration can be used.

```
{
    "mobile": {
         
    },
    "tablet": {
 
    },
    "desktop": {
 
    },
    "default": {
 
    }
}
```

## Scale
Specifying width will resize the image to that width keeping the correct ratio.  
Specifying height will resize the image to that height keeping the correct ratio.  
If width and height are both specified the image will be scaled to the lowest value keeping the correct ratio.
```
"mobile": {
    "width": 200,
    "height": 300
},
```

### Ignoring Ratio
If width and height are both specified and ratio is set to false the ratio will be ignored and the image zoom cropped from the centre to the specified size.
```
"mobile": {
    "width": 200,
    "height": 200,
    "ratio": false
}
```

## Art Direction
Sometimes a different crop or zoom of the image is desirable for narrow widths.

Along with a width and height specifying the artDirection property and an x and y position will crop the image using the X and Y as a centre point.  
This will be relative to the size of the original image.

Adding width and height properties in to the artDirection property allows the image to be zoomed before the crop is taken.
```
"mobile": {
    "width": 50,
    "height": 50,
    "artDirection": {
        "x": 202,
        "y": 109,
        "width": 400,
        "height": 300
    }
}
```

## Quality
The quality parameter ranges from 1 - worse to 95 - best and alters the JPEG compression.
```
"tablet": {
    "width": 500,
    "height": 700,
    "quality": 75
}
```

# Dependencies
* phpThumb - https://github.com/JamesHeinrich/phpThumb
* WURFL PHP API - https://github.com/mimmi20/Wurfl
* WURFL - http://sourceforge.net/projects/wurfl/files/WURFL

For performance it is recommended that the following are also installed:
* ImageMagick - http://www.imagemagick.org

