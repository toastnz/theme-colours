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
        Requirements::javascript('toastnz/theme-colours: client/dist/scripts/index.js');
        Requirements::css('toastnz/theme-colours: client/dist/styles/index.css');

        return parent::Field($properties);
    }
}
