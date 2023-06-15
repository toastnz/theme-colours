<?php

namespace Toast\ThemeColours\Models;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use Toast\Forms\IconOptionsetField;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\RequiredFields;
use Toast\ThemeColours\Helpers\Helper;
use SilverStripe\SiteConfig\SiteConfig;
use TractorCow\Colorpicker\Forms\ColorField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Toast\ThemeColours\Tasks\GenerateThemeCssFileTask;

class ThemeColour extends DataObject
{
    private static $table_name = 'ThemeColour';

    private static $db = [
        'SortOrder' => 'Int',
        'Title' => 'Varchar(255)',
        'CustomID' => 'Varchar(255)',
        'Colour' => 'Color',
    ];

    private static $belongs_many_many = [
        'SiteConfig' => SiteConfig::class
    ];

    private static $summary_fields = [
        'Title' => 'Title',
        'Colour.ColorCMS' => 'Color',
        'CustomID' => 'Colour ID',
        'ID' => 'ID',
    ];

    private static $default_sort = 'ID ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['SortOrder','SiteConfig','ColourClassName','CustomID']);

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title', 'Title')
                ->setReadOnly(!$this->canChangeColour())
                ->setDescription($this->canChangeColour() ? (($this->CustomID) ? 'e.g. "' . $this->CustomID . '" - ' : '') . 'Please limit to 30 characters' : 'This is the default theme colour "' . $this->CustomID . '" and cannot be changed.'),
        ]);

        if ($this->ID) {
            $fields->addFieldsToTab('Root.Main', [
                ColorField::create('Colour', 'Colour')
                    ->setReadOnly(!$this->canChangeColour())
                    ->setDescription($this->canChangeColour() ? 'Please select a colour' : 'This is the default theme colour "' . $this->CustomID . '" and cannot be changed.'),
            ]);
        } else {
            // Hide the CustomID field
            $fields->removeByName(['Colour']);
            $fields->insertAfter('Title', LiteralField::create('', '<div class="message notice">Colour field will become available after creating.</div>'));
        }

        return $fields;
    }

    public function getCMSValidator()
    {
        $required = new RequiredFields(['Title', 'Colour']);

        $this->extend('updateCMSValidator', $required);

        return $required;
    }

    public function canDelete($member = null)
    {
        // Get the restricted colours
        $restricted = $this->getColourRestrictions();

        // Check to see if there is a key in the restricted array that matches the CustomID
        if (array_key_exists($this->CustomID, $restricted)) {
            return false;
        }

        return true;
    }

    public function canChangeColour($member = null)
    {
        // Get the restricted colours
        $restricted = $this->getColourRestrictions();

        if (array_key_exists($this->CustomID, $restricted)) {
            if ($restricted[$this->CustomID]['Colour']) {
                return false;
            }
        }

        return true;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // If the title is empty, set it to the CustomID
        if (!$this->Title) {
            // If we have a CustomID, set the Title to that
            return $this->Title = $this->getColourCustomID();
        }

        // Convert the title to all lowercase
        $this->Title = strtolower($this->Title);
    }


    public function onAfterWrite()
    {
        parent::onAfterWrite();

         // if database and siteconfig is ready, run this
         if (Security::database_is_ready()) {
            if ($this->ID && Helper::getCurrentSiteConfig()) Helper::generateCSSFiles();
        }
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if($siteConfig = Helper::getCurrentSiteConfig()){
            foreach ($this->getDefaultColours() as $colour) {
                $key = key($colour);
                $value = $colour[$key];
       
                $existingRecord = $siteConfig->ThemeColours()->filter([
                    'CustomID' => $key,
                    'SiteConfig.ID' => $siteConfig->ID
                ])->first();
    
                if ($existingRecord) break;
    
                $colour = new ThemeColour();
                $colour->Title = $key;
                $colour->CustomID = $key;
                if ($value) $colour->Colour = $value;
                $colour->write();
                $siteConfig->ThemeColours()->add($colour->ID);
                DB::alteration_message("ThemeColour '$key' created", 'created');
            }
        }
    }

    // Method to return the ID or CustomID
    public function getColourCustomID()
    {
        return ($this->CustomID) ? $this->CustomID : $this->ID;
    }

    // Method to return the ColourPaletteID used with the ColourPaletteField
    public function getColourPaletteID()
    {
        return $this->getColourCustomID() . '/' . $this->Title;
    }

    // Method to return the ClassName
    public function getColourClassName()
    {
        // Prefix the class name with 'c-' in order to avoid numbers at the start of the class name
        $name = 'c-';
        // If we have a CustomID, use that, otherwise use the ID
        $name .= $this->CustomID ?: $this->ID;
        // Return the class name
        return $name;
    }

    // Method to return the ClassName and the Brightness
    public function getColourClasses()
    {
        return $this->getColourClassName() . ' ' . $this->getColourBrightness();
    }

    // Method to return the hex code
    public function getColourHexCode()
    {
        // Prefix the hex code with a hash
        $hex = '#';
        // If we have a Colour, use that, otherwise use 'ffffff'
        $hex .= $this->Colour ?: 'ffffff';
        // Return the hex code
        return $hex;
    }

    // Method to return Brightness
    public function getColourBrightness()
    {
        $hex = $this->Colour ?: 'ffffff';
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));

        $yiq = (($r*299)+($g*587)+($b*114))/1000;

        return ( $yiq >= 130 ) ? 'light' : 'dark';
    }

    // Method to get the restrictions for the colours
    public function getColourRestrictions()
    {
        $retrictions = [];
    
        foreach ($this->getDefaultColours() as $colour) {
            // We need to get the key, which is the name of the colour
            $name = key($colour);
            // We also need to get the value, which is the hex code
            $value = $colour[$name];

            // The colour cannot be deleted, if it is in the default colours
            // The colour's Colour value cannot be updated, if the $value is not null
            $retrictions[$name] = [
                'Colour' => ($value) ? true : false,
            ];
            
            // True means the field is read only
        }

        return $retrictions;
    }

    // Method to get the default colours
    protected function getDefaultColours()
    {
        return $this->config()->get('default_colours') ?: [];
    }
}