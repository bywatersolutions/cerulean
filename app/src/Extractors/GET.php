<?php

namespace Marquine\Etl\Extractors;

use Marquine\Etl\Row;
use Marquine\Etl\REST\Manager;

use Flow\JSONPath\JSONPath;
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
     * JSONPath to loop over.
     *
     * @var string
     */
    protected $loop = '';

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
        'columns', 'connection', 'config', 'loop'
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
	    $client = $this->rest->getConnection($this->connection);
	    $response = $client->get($this->input, $this->config);
            $response_complete = json_decode($response->getBody());
            $responseJSON = new JSONPath($response_complete);
            $response_array = $responseJSON->find($this->loop)->data();
            foreach ($response_array[0] as $data) {
                   $row = [];
		   $jsonPath = new JSONPath($data);
	           foreach ($this->columns as $key => $path) {
                	if ($path) {
				$row[$key] = $jsonPath->find($path)->data();
                	}
		   }
		   $row['record'] = $data;
                   yield new Row($row);
            }
    }
}
