<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use InvalidArgumentException;

class Defaults extends Transformer
{
    /**
     * Transformer columns to set
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Whether to overwrite existing values
     *
     * @var boolean
     */
    protected $force = false;

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'force'
    ];

    /**
     * Initialize the step.
     *
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * Transform the given row.
     *
     * @param  \Marquine\Etl\Row  $row
     * @return void
     */
    public function transform(Row $row)
    {
	foreach ($this->columns as $key => $value) {
	    if ( !isset($row[$key]) || $this->force ) {
                $row[$key] = $value;
            }
        }
    }
}
