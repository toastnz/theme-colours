<?php

namespace Toast\ThemeColours\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Config\Config;
use Toast\ThemeColours\Helpers\Helper;


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
        
        if(class_exists(Subsite::class)){
            $config = Config::inst()->get(Subsite::class, 'has_subsites_colours');
        }
        
        
        if ( !class_exists(Subsite::class) ){  
            $themeCssFilePath = '/app/client/styles/mainsite-theme.css';
        }

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
