<?php

namespace Marquine\Etl\Loaders;

use Marquine\Etl\Row;
use Marquine\Etl\REST\Manager;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class PUT extends Loader
{
    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The columns to insert.
     *
     * @var array
     */
    protected $columns;

    /**
     * The primary key.
     *
     * @var mixed
     */
    protected $key = [];

    /**
     * Configuration for the POST request
     *
     * @var array
     */
    protected $config;

    /**
     * The restful connection manager.
     *
     * @var \Marquine\Etl\REST\Manager
     */
    protected $rest;

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'connection', 'key'
    ];

    /**
     * Create a new PUT Loader instance.
     *
     * @param  \Marquine\Etl\REST\Manager  $manager
     * @return void
     */
    public function __construct(Manager $manager)
    {
        $this->rest = $manager;
    }

    /**
     * Initialize the step.
     *
     * @return void
     */
    public function initialize()
    {
        if (! empty($this->columns) && array_keys($this->columns) === range(0, count($this->columns) - 1)) {
            $this->columns = array_combine($this->columns, $this->columns);
        }
    }

    /**
     * Load the given row.
     *
     * @param  \Marquine\Etl\Row  $row
     * @return void
     */
    public function load(Row $row)
    {
        if ($this->key) {
           $put_id = "/" . $row[$this->key[0]];
        } else {
           $put_id = '';
        }
        if ($this->columns) {
            $result = [];
            foreach ($this->columns as $key => $column) {
                if (isset($row[$key]) ) {
                   if ($row[$key] == 'true') {
                        $result[$column] = true;
                   } elseif ($row[$key] == 'false') {
                        $result[$column] = false;
                   } else {
                        $result[$column] = $row[$key];
                   }
		} else {
		   $result[$column] = null;
		}
            }
	    $interim = array();
            foreach($result as $path => $value) {
              $ancestors = explode('.', $path);
              $this->set_nested_value($interim, $ancestors, $value);
            }
            $row = $interim;
        } elseif (isset($row['record'])) {
                $row = $row['record'];
        }

	$client = $this->rest->getConnection($this->connection);
	$payload = $this->config;
	$payload['json'] = $row;
	$payload['headers']['Accept'] = 'text/plain';
	try {
		$response = $client->put($this->output . $put_id, $payload);
	} catch (RequestException $e) {
		if ($e->hasResponse()) {
		        echo Psr7\str($e->getResponse());
		}
	}
    }

    /**
     * Finalize the step.
     *
     * @return void
     */
    public function finalize()
    {
    }

    /**
     * Give it and array, and an array of parents, it will decent into the
     * nested arrays and set the value.
     */
    function set_nested_value(array &$arr, array $ancestors, $value) {
	$current = &$arr;
  	foreach ($ancestors as $key) {
	    // To handle the original input, if an item is not an array, 
    	    // replace it with an array with the value as the first item.
    	    if (!is_array($current)) {
      	 	$current = array( $current);
    	    }

	    if (!array_key_exists($key, $current)) {
  	        $current[$key] = array();
    	    }
	    $current = &$current[$key];
        }

	$current = $value;
    }
}

