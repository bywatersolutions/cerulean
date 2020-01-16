<?php

use SilverStripe\ORM\DataObject;
use ByWaterSolutions\JsonEditorField\JsonEditorField;

class ETL_REST extends DataObject {

	private static $singular_name = "RESTful API Connection";

	private static $plural_name = "RESTful API Connections";

	private static $db = [
		'Title' => 'Varchar(255)',
		'Shortname' => 'Varchar(32)',
		'Configuration' => 'Text'
	];

	private static $summary_fields = [
		'Title',
		'Shortname'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$schema = file_get_contents($_SERVER['DOCUMENT_ROOT']. "/app/json/ETL_REST_Schema.json");

		$fields->addFieldToTab("Root.Main", new JsonEditorField("Configuration", "Configuration", $this->Configuration, null, $schema));

		return $fields;
	}
}
