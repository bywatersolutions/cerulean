<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use Marquine\Etl\Transformers\Transformer;
use InvalidArgumentException;

class UUID extends Transformer
{
    /**
     * Transformer columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The UUID version to use.
     *
     * @var int
     */
    protected $version = 4;

    /**
     * The namespace to use to generate v3 and v5 UUIDs.
     *
     * @var string
     */
    protected $uuidnamespace;

    /**
     * The root namespace to use to generate v3 and v5 UUIDs.
     *
     * @var string
     */
    protected $rootnamespace;

    /**
     * The UUID function to use, based on version
     *
     */
    protected $function;

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'version', 'uuidnamespace', 'rootnamespace'
    ];

    /**
     * Initialize the step.
     *
     * @return void
     */
    public function initialize()
    {
        $this->function = $this->getUUIDFunction();
        $root_ns = \Ramsey\Uuid\Uuid::uuid5(\Ramsey\Uuid\Uuid::NAMESPACE_DNS, $this->rootnamespace);
        $this->uuidnamespace = \Ramsey\Uuid\Uuid::uuid5($root_ns, $this->uuidnamespace);
    }

    /**
     * Transform the given row.
     *
     * @param  \Marquine\Etl\Row  $row
     * @return void
     */
    public function transform(Row $row)
    {
        $row->transform($this->columns, function ($column) {
	    $args = [$column];
	    if ($this->version == 3 or $this->version == 5) {
		$args = array_merge([$this->uuidnamespace], $args);
	    }
            $uuid = call_user_func_array($this->function, $args);
            return $uuid->toString();
        });
    }

    /**
     * Get the replace function name.
     *
     * @return string
     */
    protected function getUUIDFunction()
    {
        switch ($this->version) {
            case '1':
                return '\Ramsey\Uuid\Uuid::uuid1';
            case '3':
                return '\Ramsey\Uuid\Uuid::uuid3';
            case '4':
                return '\Ramsey\Uuid\Uuid::uuid4';
            case '5':
                return '\Ramsey\Uuid\Uuid::uuid5';
        }

        throw new InvalidArgumentException("The version [$this->version}] is invalid.");
    }
}
