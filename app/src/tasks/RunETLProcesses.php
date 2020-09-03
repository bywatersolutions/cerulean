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
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DB;

use Marquine\Etl\Etl;
use Marquine\Etl\Container;

use GuzzleHttp\Client;

class RunETLProcesses extends BuildTask {

    protected $title = 'Run ETL Process';

    protected $description = 'Run an ETL Process';

    protected $enabled = true;

    protected $process = 0;

    function run($request) {
	$container = Container::getInstance();
	// Extractors
	$container->bind('simplexml_extractor', Marquine\Etl\Extractors\Xml2::class);
	$container->bind('marc_extractor', Marquine\Etl\Extractors\MARC::class);
	$container->bind('get_extractor', Marquine\Etl\Extractors\GET::class);

	// Transformers
	$container->bind('date_transformer', Marquine\Etl\Transformers\Date::class);
	$container->bind('math_transformer', Marquine\Etl\Transformers\Math::class);
	$container->bind('regex_transformer', Marquine\Etl\Transformers\Regex::class);
	$container->bind('callback_transformer', Marquine\Etl\Transformers\Callback::class);
	$container->bind('map_transformer', Marquine\Etl\Transformers\Map::class);
	$container->bind('defaults_transformer', Marquine\Etl\Transformers\Defaults::class);
	$container->bind('autoincrement_transformer', Marquine\Etl\Transformers\AutoIncrement::class);
	$container->bind('uuid_transformer', Marquine\Etl\Transformers\UUID::class);

	// Loaders
	$container->bind('file_loader', Marquine\Etl\Loaders\File::class);
	$container->bind('post_loader', Marquine\Etl\Loaders\POST::class);
	$container->bind('put_loader', Marquine\Etl\Loaders\PUT::class);

	// Bind Remote Databases
	$databases = ETL_DB::get();
	foreach ($databases as $database) {
		$db_config = json_decode($database->Configuration, true);
		Etl::service('db')->addConnection($db_config['config'], $database->Shortname);
	}

	// Bind local scratch space
	$cerulean_config = [
		'driver' => 'pgsql',
		'host' => Environment::getEnv('SS_DATABASE_SERVER'),
		'port' => Environment::getEnv('SS_DATABASE_PORT'),
		'database' => Environment::getEnv('SS_DATABASE_NAME'),
		'username' => Environment::getEnv('SS_DATABASE_USERNAME'),
		'password' => Environment::getEnv('SS_DATABASE_PASSWORD'),
		'charset' => 'utf8',
		'schema' => 'public'
	];
	Etl::service('db')->addConnection($cerulean_config, 'default');

	// enable RESTful API connections
        $container->singleton(Marquine\Etl\REST\Manager::class);
        $container->alias(Marquine\Etl\REST\Manager::class, 'rest');

	// Bind RESTful API connections
	$restfuls = ETL_REST::get();
	foreach ($restfuls as $restful) {
		$rest_config = json_decode($restful->Configuration, true);
		Etl::service('rest')->addConnection($rest_config['config'], $restful->Shortname);
	}

	$etl = new Etl($container);

	$vars = $request->getVars();
	if (isset($vars['process']) and is_numeric($vars['process'])) {
		$this->process = ETL_Process::get_by_id($vars['process']);
	}
	$debug = false;
	if (isset($vars['debug']) ) {
		$debug = true;
	}


	if ($this->process) {
		if ($debug) {
			echo "<h2>".$this->process->Title."</h2>";
			$config = json_decode($this->process->Configuration, true);
			echo "<pre>";
			var_dump($config);
			echo "</pre>";
		}

		// Set up Extractor, or use local Cerulean Record store
		if (isset($config['Extractor'])) {
			$extractor = array_keys($config['Extractor'])[0];

			//  if 'source' is set, it's a file; set relative to full assets path
			if (isset($config['Extractor'][$extractor]['source'])) {
				$source = $_SERVER['DOCUMENT_ROOT'] . "/public/assets/" . $config['Extractor'][$extractor]['source'];
			// if 'query' is set, it's a DB Query
			} elseif (isset($config['Extractor'][$extractor]['query'])) {
				$source = $config['Extractor'][$extractor]['query'];
                        // if 'endpoint' is set, it's a RESTful connection
                        } elseif (isset($config['Extractor'][$extractor]['endpoint'])) {
                                $source = $config['Extractor'][$extractor]['endpoint'];
			}

			$configuration = $config['Extractor'][$extractor]['config'];
			if (isset($configuration['columns'][0]) && isset($configuration['columns'][0]['key'])) {
				$configuration = $this->fixColumns($configuration);
			}

			$etl->extract($extractor, $source, $configuration);
		} else {
			$cerulean_query = "SELECT data FROM \"ETL_Record\" WHERE \"TypeID\" = " . $this->process->RecordTypeID;
			$etl->extract('query', $cerulean_query, array('connection' => 'default'));
			$etl->transform('callback', ['callback' => 'RunETLProcesses::denestJson']);
		}

		// Set up any Transformers defined
		if (isset($config['Transformers'])) {
			foreach($config['Transformers'] as $transformer) {
				$transformer_type = array_keys($transformer)[0];
				$configuration = $transformer[$transformer_type]['config'];
				if ( isset($configuration['columns'][0]) && isset($configuration['columns'][0]['key']) ) {
					$configuration = $this->fixColumns($transformer[$transformer_type]['config']);
				}
				$etl->transform($transformer_type, $configuration);
			}
		}

		// Set up Loader, or use Cerulean Record storage
		if (isset($config['Loader'])) {
			$loader = array_keys($config['Loader'])[0];
			$destination = $config['Loader'][$loader]['destination'];
			$configuration = $config['Loader'][$loader]['config'];
			if ( isset($configuration['columns'][0]) && isset($configuration['columns'][0]['key']) ) {
				$configuration = $this->fixColumns($configuration);
			}
			$etl->load($loader, $destination, $configuration);
		} else {
			$etl->transform('defaults', ['columns' => ['typename' => $this->process->RecordType()->Title], 'force' => true]);
			$etl->transform('callback', ['callback' => 'RunETLProcesses::nestJson']);
			$load_config = array();
			$load_config['columns'] = array('legacyid', 'data', 'typename');
			$load_config['connection'] = 'default';
			$load_config['key'] = array("legacyid", "typename");
			$load_config['commit_size'] = 500;
			$etl->load("insert_update", '"ETL_Record"', $load_config);
		}

		// Limit rows, if configured
		if (isset($vars['limit']) and is_numeric($vars['limit'])) {
			$etl->limit($vars['limit']);
		} elseif (isset($config['limit'])) {
			$etl->limit($config['limit']);
		}

		// Skip rows, if configured
		if (isset($vars['skip']) and is_numeric($vars['skip'])) {
			$etl->skip($vars['skip']);
		} elseif (isset($config['skip'])) {
			$etl->skip($config['skip']);
		}

		if ($debug) {
			echo "<h2>Results</h2>";
			echo "<pre>";
			var_dump($etl->toArray());
			echo "</pre>\n";
		} else {
			$etl->run();
		}

                $sync = DB::query('UPDATE "ETL_Record" SET "TypeID" = ' . $this->process->RecordTypeID . ' WHERE typename = \'' . $this->process->RecordType()->Title . '\'')->value();

	} else {
		echo "<h2>Process not found</h2>\n";
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

    static function nestJson($row) {
        $data = $row;
        unset($data['data']);
        $row['data'] = json_encode($data->toArray());
        return $row;
    }

    static function denestJson($row) {
       $data = json_decode($row['data'], true);
        unset($row['data']);
        foreach ($data as $key => $value) {
                $row[$key] = $value;
        }
    }

    static function setRecordTypeColumns($row) {
       $data = $row;
       unset($data['data']);
       $columns_string = implode(",", $data->toArray());
       $this->process->RecordType()->RecordFields = $columns_string;
       $this->process->RecordType()->write();
    }
}
