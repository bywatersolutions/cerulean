<?php
/**
require("../../src/Extractors/Xml2.php");

require("../../src/Transformers/Date.php");
require("../../src/Transformers/Math.php");
require("../../src/Transformers/Regex.php");
require("../../src/Transformers/Callback.php");
require("../../src/Transformers/Map.php");
require("../../src/Transformers/AutoIncrement.php");

require("../../src/Loaders/File.php");
require("../Spit_n_Polish.php");
**/
use SilverStripe\Dev\BuildTask;
use Marquine\Etl\Etl;
use Marquine\Etl\Container;


class RunETLProcesses extends BuildTask {

    protected $title = 'Run ETL Process';

    protected $description = 'Run an ETL Process';

    protected $enabled = true;

    function run($request) {
	$container = Container::getInstance();
	// Extractors
	$container->bind('simplexml_extractor', Marquine\Etl\Extractors\Xml2::class);

	// Transformers
	$container->bind('date_transformer', Marquine\Etl\Transformers\Date::class);
	$container->bind('math_transformer', Marquine\Etl\Transformers\Math::class);
	$container->bind('regex_transformer', Marquine\Etl\Transformers\Regex::class);
	$container->bind('callback_transformer', Marquine\Etl\Transformers\Callback::class);
	$container->bind('map_transformer', Marquine\Etl\Transformers\Map::class);
	$container->bind('autoincrement_transformer', Marquine\Etl\Transformers\AutoIncrement::class);

	// Loaders
	$container->bind('file_loader', Marquine\Etl\Loaders\File::class);

	// Bind Remote Databases
	$databases = ETL_DB::get();
	foreach ($databases as $database) {
		Etl::service('db')->addConnection(json_decode($database->Configuration), $database->Shortname);
	}

	$etl = new Etl($container);

	$vars = $request->getVars();
	$process = ETL_Process::get_by_id($vars['process']);

	if ($process) {
		echo "<h2>".$process->Title."</h2>";
		$config = json_decode($process->Configuration, true);
		echo "<pre>";
		var_dump($config);
		echo "</pre>";

		// Set up Extractor, or use local Cerulean Record store
		if (isset($config['Extractor'])) {
			$extractor = array_keys($config['Extractor'])[0];
			$source = $config['Extractor'][$extractor]['source'];
			$configuration = $config['Extractor'][$extractor]['config'];
			if ($configuration['columns'][0]['key']) {
				$configuration = $this->fixColumns($configuration);
			}
			$etl->extract($extractor, $source, $configuration);
		} else {
			$cerulean_query = "SELECT * FROM ETL_Record WHERE TypeID = " . $process->TypeID;
			//$etl->extract('query', "");
		}

		// Set up any Transformers defined
		if (isset($config['Transformers'])) {
			foreach($config['Transformers'] as $transformer) {
				$transformer_type = array_keys($transformer['Transformer'])[0];
				$configuration = $transformer['Transformer'][$transformer_type]['config'];
				$etl->transform($transformer_type, $configuration);
			}
		}

		// Set up Loader, or use Cerulean Record storage
		if (isset($config['Loader'])) {
			$loader = array_keys($config['Loader'])[0];
			$destination = $config['Loader'][$loader]['destination'];
			$configuration = $config['Loader'][$loader]['config'];
			$configuration = $this->fixColumns($configuration);
			$etl->load($extractor, $destination, $configuration);
		} else {
			//$etl->load("insert_update", "Record");
		}
		echo "<h2>Results</h2>";
		echo "<pre>";
		var_dump($etl->toArray());
		echo "</pre>";

		// Once we're ready....
		// $etl->run();

	} else {
		echo "<h2>Process not found</h2>";
	}
    }

    function fixColumns($configuration) {
		$old_columns = $configuration['columns'];
		$new_columns = [];
		foreach ($old_columns as $old) {
			$new_columns[$old['key']] = $old['value'];
		}
		$configuration['columns'] = $new_columns;
		return $configuration;
    }
}
