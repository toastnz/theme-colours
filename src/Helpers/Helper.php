<?php

namespace Toast\ThemeColours\Helpers;

use SilverStripe\Core\Environment;
use SilverStripe\Security\Security;
use SilverStripe\Security\Permission;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Config\Config;
use DirectoryIterator;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

class Helper
{
    static function isSuperAdmin()
    {
        if ($defaultUser = Environment::getEnv('SS_DEFAULT_ADMIN_USERNAME')) {
            if ($currentUser = Security::getCurrentUser()) {
                return $currentUser->Email == $defaultUser;
            }
        }
        return false;
    }
    
    static function getThemeColoursArray($id = null)
    {
        $array=[];

        $siteConfig = $id ? SiteConfig::get()->byID($id) : SiteConfig::current_site_config();

        if ($colours = $siteConfig->ThemeColours()){
            foreach($colours as $colour){
                // Add the colour to the array
                $array[$colour->getColourClassName()] = $colour;
            }
        }

        return $array;
    }

    static function getThemeColourPalette($id = null)
    {
        $themeColours = self::getThemeColoursArray($id);
        
        // We want a default 'None' option so add it to the $array
        $array = [
            'None' => '',
        ];

        // Loop through the $themeColours and add the Title and Value to the $array
        foreach($themeColours as $themeColour){
            $array[$themeColour->getColourPaletteID()] = $themeColour->getColourHexCode();
        }

        return $array;
    }

    static function getThemeColourFromColourPaletteID($colourPaletteID)
    {
        $themeColours = self::getThemeColoursArray();

        // Loop through the $themeColours and return the object that matches the $colourPaletteID
        foreach($themeColours as $themeColour){
            if ($colourPaletteID == $themeColour->getColourPaletteID()) {
                return $themeColour;
            }
        }
    }

    static function getColourFormatsForTinyMCE()
    {
        $colours = self::getThemeColoursArray();

        $formats = [];
        $colourFormats = [];

        // get current colours
        foreach ($colours as $colour) {
            $colourFormats[] = [
                'title'          => $colour->Title,
                'inline'         => 'span',
                'classes'        => 'colour--' . $colour->ColourClasses,
                'wrapper'        => true,
                'merge_siblings' => true,
            ];
        }

        $formats[] = [
            'title' => 'Colours',
            'items' => $colourFormats,
        ];

        return $formats;
    }

    static function generateCSSFiles()
    {
        // Get the current site's config
        $siteConfig = SiteConfig::current_site_config();

        // Get the site' ID and append to the css file name
        $styleID = ($siteConfig->ID == 1) ? 'mainsite' : 'subsite-' . $siteConfig->ID;

        // Get the site's colours
        $colours = $siteConfig->ThemeColours();

        // If we have colours
        if ($colours) {
            $CSSFilePath = Director::baseFolder() . '/app/client/styles/';
            $themeCSSFilePath = $CSSFilePath . $styleID . '-theme.css';
            $editorCSSFilePath = $CSSFilePath . $styleID . '-editor.css';

            // Remove files if they exist
            if (file_exists($themeCSSFilePath)) unlink($themeCSSFilePath);
            if (file_exists($editorCSSFilePath)) unlink($editorCSSFilePath);
            
            // Create a new file 
            $CSSVars = ':root {';

            // Loop through colours and add CSS vars
            foreach ($colours as $colour) {
                if ($colour->Colour) {
                    $CSSVars .= '--' . $colour->getColourClassName() . ': ' . $colour->getColourHexCode() . ';';
                }
            }

            // Close the file
            $CSSVars .= '}';

            // Create a new file for the theme
            $themeStyles = $CSSVars;
            // Create a new file for the editor
            $editorStyles = $CSSVars;

            // Loop through colours and add styles
            foreach ($colours as $colour) {
                if ($colour->Colour) {
                    $className = $colour->getColourClassName();
                    // Theme styles
                    $themeStyles .= '.colour--' . $className . '{';
                    $themeStyles .= 'color: var(--' . $className . ');';
                    $themeStyles .= '}';
                    $themeStyles .= '.background-colour--' . $className . '{';
                    $themeStyles .= 'background-color: var(--' . $className . ');';
                    $themeStyles .= '}';

                    // Editor styles
                    $editorStyles .= 'body.mce-content-body  .colour--' . $className . '{';
                    $editorStyles .= 'color: var(--' . $className . ');';
                    $editorStyles .= '}';
                }
            }

            // Write to file
            try { 
                file_put_contents($themeCSSFilePath, $themeStyles);
                file_put_contents($editorCSSFilePath, $editorStyles);
            } catch (\Exception $e) {
                // Do nothing
            }
        }
    }

}