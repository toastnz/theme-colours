<?php

namespace Toast\ThemeColours\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Config\Config;
use Toast\ThemeColours\Helpers\Helper;
use SilverStripe\SiteConfig\SiteConfig;


class PageExtension extends DataExtension
{
    public function getThemeColour($colour)
    {
        return Helper::getThemeColourFromColourPaletteID($colour);
    }
}

class PageControllerExtension extends Extension
{
    // onbeforeinit
    public function onBeforeInit()
    {
        $themeCssFilePath = null;

        // Grab the SiteConfig
        $siteConfig = SiteConfig::current_site_config();
        $siteID = $siteConfig->ID;

        // Get the theme ID / Name
        $theme = ($siteID === 1) ? 'mainsite' : 'subsite-' . $siteID;

        $themeCssFilePath = '/app/client/styles/' . $theme . '-theme.css';

        if ($themeCssFilePath){
            if (!file_exists(Director::baseFolder() .$themeCssFilePath)){
                $result = Helper::generateCSSFiles($themeCssFilePath);
            }
    
            if (file_exists(Director::baseFolder() .$themeCssFilePath)) {
                Requirements::customCSS(file_get_contents(Director::baseFolder() .$themeCssFilePath));
            }
        }
    }
}
