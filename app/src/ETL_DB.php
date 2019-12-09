<?php

use SilverStripe\ORM\DataObject;
use ByWaterSolutions\JsonField\JsonField;

class ETL_DB extends DataObject {

	private static $singular_name = "Remote Database";

	private static $plural_name = "Remote Databases";

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

		$schema = file_get_contents("app/json/ETL_DB_Schema.json");

		$fields->addFieldToTab("Root.Main", new JsonField("Configuration", "Configuration", $this->Configuration, null, $schema));

		return $fields;
	}
}
