<?php

namespace Toast\ThemeColours\Fields;

use SilverStripe\View\Requirements;
use Toast\ThemeColours\Helpers\Helper;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\OptionsetField;

class ThemeColourPaletteField extends OptionsetField
{
    public function __construct($name, $title = null, $source = [], $value = null)
    {
        // Get the current site config ID
        $siteConfigID = SiteConfig::current_site_config()->ID;
        // Get the colour palette for the current site
        $colourPalette = Helper::getThemeColourPalette($siteConfigID);

        $source = $colourPalette;

        $this->setSource($source);

        if (!isset($title)) {
            $title = $name;
        }

        parent::__construct($name, $title, $source, $value);
    }

    public function Field($properties = [])
    {
        Requirements::javascript('toastnz/theme-colours: client/dist/scripts/colour-change.js');
        Requirements::css('toastnz/theme-colours: client/dist/styles/index.css');

        return parent::Field($properties);
    }

    public function getColour()
    {
        // Get the current value
        $value = $this->Value();
        // Get the ThemeColour
        $colour = Helper::getThemeColourFromColourPaletteID($value);
        // If there is no colour, return nothing
        if (!$colour) return '';
        // Once we have the colour, get the colour
        $colour = $colour->Colour;

        // Return the colour
        return $colour;
    }

    public function getColourBrightness($colourID)
    {
        // Make sure we have a colour ID
        if (!$colourID) return '';
        // Get the ThemeColour
        $colour = Helper::getThemeColourFromColourPaletteID($colourID);
        // If there is no colour, return nothing
        if (!$colour) return '';
        // Once we have the colour, get the brightness
        $brightness = $colour->getColourBrightness();

        // Return the brightness
        return $brightness;
    }

    public function getColourName($colourID)
    {
        // Make sure we have a colour ID
        if (!$colourID) return '';
        // Get the ThemeColour
        $colour = Helper::getThemeColourFromColourPaletteID($colourID);
        // If there is no colour, return nothing
        if (!$colour) return '';
        // Once we have the colour, get the name
        $name = $colour->Title;

        // Return the name
        return $name;
    }
}
