<?php

namespace Toast\ThemeColours\Extensions;

use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\Security\Security;
use Toast\ThemeColours\Helpers\Helper;

class DatabaseAdminExtension extends Extension
{
    public function onAfterBuild()
    {
         //generate all the required css files by theme colours
         if (Security::database_is_ready()) {
            // theme colours
            if (Helper::getCurrentSiteConfig()) Helper::generateCSSFiles();
        }
    }
}
