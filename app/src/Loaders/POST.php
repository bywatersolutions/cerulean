<?php

namespace Marquine\Etl\Loaders;

use Marquine\Etl\Row;
use Marquine\Etl\REST\Manager;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class POST extends Loader
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
     * Configuration for the POST request
     *
     * @var array
     */
    protected $config;

    /**
     * Indicates if the loader will perform transactions.
     *
     * @var bool
     */
    protected $transaction = false;

    /**
     * Transaction commit size.
     *
     * @var int
     */
    protected $commitSize = 100;

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
        'columns', 'connection', 'transaction', 'commitSize'
    ];

    /**
     * Create a new POST Loader instance.
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
        if ($this->columns) {
            $result = [];

            foreach ($this->columns as $key => $column) {
                isset($row[$key]) ? $result[$column] = $row[$key] : $result[$column] = null;
            }

            $row = $result;
        }

	if ($this->transaction) {
		// requires a bulk API
	} else {
		$client = $this->rest->getConnection($this->connection);
		$payload = $this->config;
		$payload['json'] = $row;
		try {
			$response = $client->post($this->output, $payload);
		} catch (RequestException $e) {
			echo Psr7\str($e->getRequest());
			if ($e->hasResponse()) {
			        echo Psr7\str($e->getResponse());
    			}
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

}

