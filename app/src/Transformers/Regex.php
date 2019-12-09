<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use InvalidArgumentException;

class Regex extends Transformer
{
    /**
     * Transformer columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Pattern to match
     *
     * @var string
     */
    protected $pattern;

    /**
     * What to replace with
     *
     * @var string
     */
    protected $replacement;

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'pattern', 'replacement'
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
        if ($this->pattern && $this->replacement) {
            $row->transform($this->columns, function ($column) {
                return preg_replace($this->pattern, $this->replacement, $column);
            });
        }
    }
}
