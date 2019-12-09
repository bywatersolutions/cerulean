<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use InvalidArgumentException;

class Map extends Transformer
{
    /**
     * Transformer columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Transformer columns.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'map'
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
        $row->transform($this->columns, function ($column) {
            if (isset($column)) {
                if (key_exists($column, $this->map)) {
                    return $this->map[$column];
                } else {
                    return null;
                }
            }
        });
    }
}
