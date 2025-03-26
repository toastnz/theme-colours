<?php

namespace Toast\ThemeColours\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use Toast\ThemeColours\Helpers\Helper;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class GenerateThemeCssFileTask extends BuildTask
{

    protected static string $commandName = 'generate_theme_css_file';

    protected string $title= 'Regenerate Theme CSS file';

    protected static string $description = 'Regenerates Theme CSS file from configured colours in SiteConfig for main site only';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        Helper::generateCSSFiles();
        return Command::SUCCESS;
    }

    function colourBrightness($hex, $percent)
    {
        // Work out if hash given
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }
        /// HEX TO RGB
        $rgb = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        //// CALCULATE
        for ($i = 0; $i < 3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
            } else {
                // Darker
                $positivePercent = $percent - ($percent * 2);
                $rgb[$i] = round($rgb[$i] * (1 - $positivePercent)); // round($rgb[$i] * (1-$positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }
        //// RBG to Hex
        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if (strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }
        return $hash . $hex;
    }
}