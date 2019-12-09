<?php

use SilverStripe\ORM\DataObject;

class ETL_Record extends DataObject {

	private static $singular_name = "Record";

	private static $plural_name = "Records";

	private static $db = [
		'Hash' => 'Varchar(40)',
		'LegacyID' => 'Varchar(128)',
		'Data' => 'Text'
	];

	private static $has_one = [
		'Type' => ETL_RecordType::class
	];

	//public function summaryFields() {
	//	return explode(",", $this->Type()->RecordFields);
	//}
}
