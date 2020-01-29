<?php

namespace Marquine\Etl\Loaders;

use Marquine\Etl\Row;
use Marquine\Etl\REST\Manager;

use GuzzleHttp\Client;

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
    }

    /**
     * Load the given row.
     *
     * @param  \Marquine\Etl\Row  $row
     * @return void
     */
    public function load(Row $row)
    {
	if ($this->transaction) {
		// requires a bulk API
	} else {
		$client = $this->rest->getConnection($this->connection);
		$payload = $this->config;
		$payload['json'] = json_encode($row);
		$response = $client->post($this->input, $payload);
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
