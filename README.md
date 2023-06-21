### Installation
------------

The easiest way is to use [composer](https://getcomposer.org/):

    composer require toastnz/theme-colours

Run `dev/build` afterwards.

### Configuration
-------------

Add the following to your `config.yml` (optional) to generate default colours on dev/build
Colours with a hex value will be locked and not editable in the CMS
Colours with null value will be editable in the CMS

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
### Colour functions 
```getColourCustomID()``` returns either the ID set in the config.yml, or for additional colours, returns the ID

```getColourPaletteID()``` returns a combination of the getColourCustomID() and the Title, so the data object can be found from the selected colour palette field value.

```getColourClassName()``` returns `c-` + `getColourCustomID()` so the css class is unique. `c-` is there to represent `colour` and to ensure the class does not start with a number.

```getColourBrightness()``` returns either `dark` or `light` based on their luminocity of the colour value.

```getColourHexCode()``` returns the hex value of the colour

```getColourClasses()``` will return a combination of the `getColourClassName()` and `getColourBrightness()`
### Helper functions 
```Helper::getThemeColourPalette()``` to loop through the $themeColours and add the Title and Value to the $array for ColorPaletteField to use.

```Helper::getThemeColourFromColourPaletteID``` to loop through the $themeColours and return the object that matches the $colourPaletteID
