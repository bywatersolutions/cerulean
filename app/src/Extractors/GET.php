<?php

namespace Marquine\Etl\Extractors;

use Marquine\Etl\Row;
use Marquine\Etl\REST\Manager;

use GuzzleHttp\Client;

class GET extends Extractor
{
    /**
     * Extractor columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The connection name.
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * RESTful GuzzleHttp\Client config
     *
     * @var array
     */
    protected $config = [];

    /**
     * The database manager.
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
        'columns', 'connection', 'config'
    ];

    /**
     * Create a new GET Extractor instance.
     *
     * @param  \Marquine\Etl\REST\Manager  $manager
     * @return void
     */
    public function __construct(Manager $manager)
    {
        $this->rest = $manager;
    }

    /**
     * Extract data from the input.
     *
     * @return \Generator
     */
    public function extract()
    {
	    $client = $manager->getConnection($this->connection);
	    $response = $client->get($this->input, $this->config);
            $response_array = json_decode($response->getBody());
	    foreach ($response_array as $data) {
		   $jsonPath = new JSONPath($data);
	           foreach ($this->columns as $key => $path) {
                	if ($path) {
				$this->row[$key] = $jsonPath->find($path)->data();
                	}
		   }
		   $row['record'] = $data;
                   yield new Row($row);
            }
    }
}
