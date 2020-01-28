<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use Marquine\Etl\Transformers\Transformer;
use InvalidArgumentException;

use Ramsey\Uuid\Uuid as RamseyUUID;

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
    protected $uuid_namespace;

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
        'columns', 'version', 'uuid_namespace'
    ];

    /**
     * Initialize the step.
     *
     * @return void
     */
    public function initialize()
    {
        $this->function = $this->getUUIDFunction();
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
		$args = array_merge([$this->uuid_namespace], $args);
	    }
            return call_user_func_array($this->function, $args);
        });
    }

    /**
     * Get the replace function name.
     *
     * @return string
     */
    protected function getReplaceFunction()
    {
        switch ($this->version) {
            case '1':
                return 'RamseyUUID::uuid1';
            case '3':
                return 'RamseyUUID::uuid3';
            case '4':
                return 'RamseyUUID::uuid4';
            case '5':
                return 'RamseyUUID::uuid5';
        }

        throw new InvalidArgumentException("The version [$this->version}] is invalid.");
    }
}
