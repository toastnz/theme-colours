### Installation
------------

The easiest way is to use [composer](https://getcomposer.org/):

    composer require toastnz/theme-colours

Run `dev/build` afterwards.

### Configuration
-------------

Add the following to your `config.yml` (optional) to generate default colours on dev/build

```yaml
Toast\ThemeColours\Models:
  default_colours:
    - primary: null
    - secondary: null
    - black: '000000'
    - white: 'ffffff'
```

### Usage
-------------
### Helper functions 
```Helper::getThemeColourPalette()``` to loop through the $themeColours and add the Title and Value to the $array for ColorPaletteField to use.

```Helper::getThemeColourFromColourPaletteID``` to loop through the $themeColours and return the object that matches the $colourPaletteID
