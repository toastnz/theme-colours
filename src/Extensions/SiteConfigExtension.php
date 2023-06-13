<?php

namespace Toast\ThemeColours\Extensions;

use SilverStripe\ORM\DB;
use Toast\ThemeColours\Helpers\Helper;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Security;
use Sheadawson\Linkable\Models\Link;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\LiteralField;
use Toast\Tasks\GenerateFontCssTask;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\OptionsetField;
use Sheadawson\Linkable\Forms\LinkField;
use Toast\ThemeColours\Models\ThemeColour;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use Heyday\ColorPalette\Fields\ColorPaletteField;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;

class SiteConfigExtension extends DataExtension
{

    private static $many_many = [
        'ThemeColours' => ThemeColour::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {

        /** -----------------------------------------
         * Theme
         * ----------------------------------------*/
        if (Security::database_is_ready() && Helper::isSuperAdmin()) {

            $coloursConfig = GridFieldConfig_RecordEditor::create(50);
            $coloursConfig->addComponent(GridFieldOrderableRows::create('SortOrder'));
            $coloursConfig->removeComponentsByType(GridFieldDeleteAction::class);

            $coloursField = GridField::create(
                'ThemeColours',
                'Theme Colours',
                $this->owner->ThemeColours(),
                $coloursConfig
            );

            // if Root.Customization doesn't exist, create it
            if (!$fields->fieldByName('Root.Customization')) {
                $fields->addFieldToTab('Root', TabSet::create('Customization'));
            }

            $fields->addFieldsToTab('Root.Customization.Colours', [
                $coloursField,
                LiteralField::create('ColourFieldsWarning', '<div class="message warning"><strong>Note:</strong> Only <strong>Default Admin</strong> can view these settings</div>')
                // LiteralField::create('ColourFieldsLink', '<div class="message notice">Please run this <a href="'.Director::absoluteBaseURL().'dev/tasks/generate_theme_css_file" target="_blank">task</a> to regenerate files after creating new colours.</div>'),
            ]); 

        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $Colour = new ThemeColour();
        $Colour->requireDefaultRecords();
    }
}
