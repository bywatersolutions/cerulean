<?php

namespace Marquine\Etl\Loaders;

use Marquine\Etl\Row;
use Marquine\Etl\REST\Manager;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class DELETE extends Loader
{
    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * The primary key.
     *
     * @var mixed
     */
    protected $key = [];

    /**
     * Configuration for the DELETE request
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
        'connection', 'key'
    ];

    /**
     * Create a new DELETE Loader instance.
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
        if ($this->key) {
           $record_id = "/" . $row[$this->key[0]];
        } else {
           $record_id = '';
        }

	$client = $this->rest->getConnection($this->connection);
	$payload = $this->config;
	$payload['json'] = $row;
	$payload['headers']['Accept'] = 'text/plain';
	try {
		$response = $client->delete($this->output . $record_id, $payload);
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

}
