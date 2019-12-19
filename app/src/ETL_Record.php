<?php

use SilverStripe\ORM\DataObject;

class ETL_Record extends DataObject {

	private static $singular_name = "Record";

	private static $plural_name = "Records";

	private static $db = [
		'hash' => 'Varchar(40)',
		'legacyid' => 'Varchar(128)',
		'data' => 'Text',
		'typename' => 'Varchar(128)'
	];

	private static $has_one = [
		'Type' => ETL_RecordType::class
	];

	//public function summaryFields() {
	//	return explode(",", $this->Type()->RecordFields);
	//}
}
