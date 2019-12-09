<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;

use SilverShop\HasOneField\HasOneButtonField;

use ByWaterSolutions\JsonField\JsonField;

class ETL_Process extends DataObject {

	private static $singular_name = "ETL Process";

	private static $plural_name = "ETL Processes";

	private static $schema = "app/json/ETL_Process_Schema_v5.json";

	private static $db = [
		'Title' => 'Varchar(128)',
		'Description' => 'Text',
		'Configuration' => 'Text',
		'Sort' => 'Int'
	];

	private static $has_one = [
		'Icon' => Image::class,
		'RecordType' => ETL_RecordType::class
	];
/**
	private static $belongs_many_many = [
		'RecordTypes' => ETL_RecordType::class . '.ETLProcesses'
	];
**/
	private static $summary_fields = [
		'IconThumbnail' => 'Icon',
		'Title' => 'Name',
		'Description' => 'Description'
	];

	private static $searchable_fields = [
		'Title',
		'Description'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName("RecordTypeID");
		$fields->removeByName("Sort");

	        $fields->addFieldToTab("Root.Main",
			HasOneButtonField::create($this, "RecordType")
		);

		$schema = file_get_contents($this->config()->get('schema'));
		$schema = $this->enumSchema($schema);
		$jsonfield = JsonField::create('Configuration', 'Configuration', $this->Configuration, null, $schema);

		$fields->addFieldToTab('Root.Main',
			$jsonfield
		);

		return $fields;
	}

	public function getIconThumbnail() {
		return $this->Icon()->CMSThumbnail();
	}

	public function enumSchema($schema) {
		$schema_array = json_decode($schema);
		if (!$schema_array) {
			print "Schema invalid";
			return "{}";
		}

		// Enumerate Files
		$files = File::get()->map('Title', 'Filename')->toArray();
		if ($files) {
			$file_titles = array_keys($files);
			$file_paths = [];
			foreach (array_values($files) as $file_path) {
				$file_paths[] = "/var/www/assets/" . $file_path;
			}
			$schema_array->{'definitions'}->{'sourceFiles'}->{'enum'} = $file_paths;
			$schema_array->{'definitions'}->{'sourceFiles'}->{'options'}->{'enum_titles'} = $file_titles;
		}

		// Enumerate DB Connections
		$dbs = ETL_DB::get()->map('Title', 'Shortname')->toArray();
		$dbs['default'] = 'Default (Cerulean)';
		$db_titles = array_keys($dbs);
		$db_paths = array_values($dbs);
		$schema_array->{'definitions'}->{'databases'}->{'enum'} = $db_paths;
		$schema_array->{'definitions'}->{'databases'}->{'options'}->{'enum_titles'} = $db_titles;

		// Enumerate Columns
		$columns = explode(',', $this->RecordType()->RecordFields);
		if ($columns) {
			$schema_array->{'definitions'}->{'fieldHeaders'}->{'items'}->{'enum'} = $columns;
		}

		// Enumerate Maps
		$maps = ETL_RecordType::get()->filter(array('IsMap' => true))->map('ID', 'Title')->toArray();;
		if ($maps) {
			$schema_array->{'definitions'}->{'mapTransformer'}->{'properties'}->{'map'}->{'properties'}->{'config'}->{'properties'}->{'map'}->{'enum'} = array_keys($maps);
			$schema_array->{'definitions'}->{'mapTransformer'}->{'properties'}->{'map'}->{'properties'}->{'config'}->{'properties'}->{'map'}->{'options'}->{'enum_titles'} = array_values($maps);
		}

		return json_encode($schema_array);
	}

	public function run_process() {
		return null;
	}
}

class E_Process extends ETL_Process {
	private static $singular_name = "Extract Process";

	private static $plural_name = "Extract Processes";

	public function enumSchema($schema) {
		$schema = parent::enumSchema($schema);
		$schema_array = json_decode($schema);

		$schema_array->{"properties"}->{"Transformers"}->{"required"} = false;
		$schema_array->{"properties"}->{"Loader"}->{"required"} = false;
		$schema_array->{"requiredProperties"} = ['Extractor'];

		return json_encode($schema_array);
	}
}

class T_Process extends ETL_Process {
	private static $singular_name = "Transform Process";

	private static $plural_name = "Transform Processes";

	public function enumSchema($schema) {
		$schema = parent::enumSchema($schema);
		$schema_array = json_decode($schema);

		$schema_array->{"properties"}->{"Extractor"}->{"required"} = false;
		$schema_array->{"properties"}->{"Loader"}->{"required"} = false;
		$schema_array->{"requiredProperties"} = ['Transformers'];

		return json_encode($schema_array);
	}


}

class L_Process extends ETL_Process {
	private static $singular_name = "Load Process";

	private static $plural_name = "Load Processes";

	public function enumSchema($schema) {
		$schema = parent::enumSchema($schema);
		$schema_array = json_decode($schema);

		$schema_array->{"properties"}->{"Extractor"}->{"required"} = false;
		$schema_array->{"properties"}->{"Transformers"}->{"required"} = false;
		$schema_array->{"requiredProperties"} = ['Loader'];

		return json_encode($schema_array);
	}
}
