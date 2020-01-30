<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\Gridfield\GridFieldAddExistingAutocompleter;

use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class ETL_RecordType extends DataObject {

	private static $allowed_actions = ['runprocesses'];

	private static $singular_name = "Record Type";

	private static $plural_name = "Record Types";

	private static $db = [
		'Title' => 'Varchar(128)',
		'Description' => 'Text',
		'RecordFields' => 'Text',
		'IsMap' => 'Boolean'
	];

	private static $has_many = [
		'Records' => ETL_Record::class,
		'Processes' => ETL_Process::class
	];

	private static $summary_fields = [
		'Title' => 'Record Type',
		'Records.Count' => 'Count'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', CheckboxField::create('IsMap', 'Is This a Map?', $this->IsMap)->setDescription("Maps should have columns 'Key' and 'Value'"));

		$fields->removeByName('Processes');
		if ($tihs->ID) {
			$etlprocesses = GridField::create('Processes', 'ETL Processes', $this->Processes());
			$config = GridFieldConfig_RelationEditor::create();
			$config->addComponent(new GridFieldOrderableRows());
			$config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
			$etlprocesses->setConfig($config);
			$fields->addFieldToTab('Root.Main',$etlprocesses);

//			$fields->removeByName('RecordFields');
			$records = GridField::create('Records', 'Records', $this->Records());
			$config = GridFieldConfig_Base::create();
			$records->setConfig($config);
			$fields->addFieldToTab('Root.Records',$records);
                } else {
                        $fields->addFieldToTab('Root.Main', LiteralField::create('NotSaved', "<p class='message warning'>You can add Processes once you save for the first time</p>"));
                }

		return $fields;
	}

	public function getBetterButtonsActions() {
        	$fields = parent::getBetterButtonsActions();
		if ($this->Processes()) {
			$button = BetterButtonLink::create('Run Processes', '/dev/tasks/runprocesses?PID=' . $this->ID);
			$button->newWindow();
			$button->addExtraClass("font-icon-right-dir");
			$button->addExtraClass("btn-info");
			$fields->push($button);
		}
	        return $fields;
	}

}
