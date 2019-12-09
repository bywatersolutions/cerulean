<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\Gridfield\GridFieldAddExistingAutocompleter;

use Unclecheese\BetterButtons\Actions\BetterButtonLink;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use ByWaterSolutions\JsonField\JsonField;

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
/**
	private static $many_many = [
		'ETLProcesses' => [
			'through' => ETL_RecordType_Config::class,
			'from' => 'ETL_RecordType',
			'to' => 'ETL_Process'
		]
	];
**/
	private static $summary_fields = [
		'Title' => 'Record Type',
		'Records.Count' => 'Count'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', CheckboxField::create('IsMap', 'Is This a Map?', $this->IsMap)->setDescription("Maps should have columns 'Key' and 'Value'"));

		$fields->removeByName('Processes');
		$etlprocesses = GridField::create('Processes', 'ETL Processes', $this->Processes());
		$config = GridFieldConfig_RelationEditor::create();
		$config->addComponent(new GridFieldOrderableRows());
		$config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
		$etlprocesses->setConfig($config);
		$fields->addFieldToTab('Root.Main',$etlprocesses);

//		$fields->removeByName('RecordFields');
		$records = GridField::create('Records', 'Records', $this->Records());
		$config = GridFieldConfig_Base::create();
		$records->setConfig($config);
		$fields->addFieldToTab('Root.Records',$records);

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


/**
class ETL_RecordType_Config extends DataObject {

	private static $db = [
		'Sort' => 'Int',
		'Configuration' => 'Text'
	];

	private static $has_one = [
		'ETL_RecordType' => ETL_RecordType::class,
		'ETL_Process' => ETL_Process::class
	];

	private static $default_sort = '"ETL_RecordType_Config"."Sort" ASC';

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$field->addFieldToTab('Root.Main', new HeaderField("Foo", "Foo"));
		return $fields;
	}
}

class ETL_Process_Instance extends DataObject {

        private static $singular_name = "ETL Process";

        private static $plural_name = "ETL Processes";

	private static $db = [
		'Sort' => 'Int',
		'Configuration' => 'Text'
	];

	private static $has_one = [
		'ETL_RecordType' => ETL_RecordType::class,
		'ETL_Process' => ETL_Process::class
	];

	private static $searchable_fields = [
                'Title',
                'Description'
	];

        private static $summary_fields = [
                'IconThumbnail' => 'Icon',
                'Title' => 'Name',
                'Description' => 'Description'
        ];

        public function getCMSFields() {
                $fields = parent::getCMSFields();

		$fields->removeByName('Sort');
		$fields->removeByName('ETL_RecordType');
		$fields->removeByName('Configuration');

		if ($this->ETL_ProcessID) {
			$starting_val = $this->Configuration ? $this->Configuration : $this->ETL_Process()->Configuration;
        	        $schema = file_get_contents($this->ETL_Process()->config()->get('schema'));
                	$fields->addFieldToTab('Root.Main', new JsonField('Configuration', 'Configuration', $starting_val, null, $schema));
		}
                return $fields;
        }

	public function getIconThumbnail() {
		return $this->ETL_Process()->getIconThumbnail();
	}

	public function getTitle() {
		return $this->ETL_Process()->Title;
	}

	public function getDescription() {
		return $this->ETL_Process()->Description;
	}
}
**/
