<?php
namespace Marquine\Etl\REST;

use InvalidArgumentException;
use GuzzleHttp\Client;

class Manager
{
    /**
     * The connections configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The connections instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Register a connection.
     *
     * @param  array  $config
     * @param  string  $name
     * @return void
     */
    public function addConnection($config, $name = 'default')
    {
        $this->config[$name] = $config;
    }

    /**
     * Get a connection instance.
     *
     * @param  string  $name
     * @return \GuzzleHttp\Client
     */
    protected function getConnection($name)
    {
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }
        return $this->connections[$name];
    }
    /**
     * Make a connection instance.
     *
     * @param  string  $name
     * @return \GuzzleHttp\Client
     *
     * @throws \InvalidArgumentException
     */
    protected function makeConnection($name)
    {
        if (isset($this->config[$name])) {
		$client = new Client($this->config[$name]);
        }
        throw new InvalidArgumentException("RESTful connection [{$name}] not configured.");
    }
}

