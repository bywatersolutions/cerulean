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
    public function getConnection($name)
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
		$rest_config = $this->config[$name];
                if (isset($rest_config['headers'])) {
                        $old_headers = $rest_config['headers'];
                        $new_headers = [];
                        foreach ($old_headers as $old) {
                                $new_headers[$old['key']] = $old['value'];
                        }
                        $rest_config['headers'] = $new_headers;
                }
                if (isset($rest_config['authentication']['url'])) {
                        // get the token
                        $auth_client = new Client($rest_config);
                        $auth_request = $auth_client->post($rest_config['authentication']['url'],
                                ['json' => ['username' => $rest_config['authentication']['username'],
                                            'password' => $rest_config['authentication']['password']],
                                'headers' => ['Accept' => 'application/json']
                                ]
                        );
                        $token = $auth_request->getHeader($rest_config['authentication']['header'])[0];
                        // plunk it into the set header
                        $rest_config['headers'][$rest_config['authentication']['header']] = $token;
                } elseif (isset($rest_config['authentication']['token'])) {
                        // insert token into Authentication header
                } elseif (isset($rest_config['authentication']['username']) && isset($rest_config['authentication']['password']) ) {
                        // add basic HTTP auth
                }

		$client = new Client($rest_config);
		return $client;
        }
        throw new InvalidArgumentException("RESTful connection [{$name}] not configured.");
    }
}

