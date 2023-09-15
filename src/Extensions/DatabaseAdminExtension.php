<?php

namespace Toast\ThemeColours\Extensions;

use SilverStripe\Control\Director;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Security;
use Toast\ThemeColours\Helpers\Helper;

class DatabaseAdminExtension extends DataExtension
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
