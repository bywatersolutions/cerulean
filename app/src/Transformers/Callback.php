<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use InvalidArgumentException;

class Callback extends Transformer
{
    /**
     * Transformer columns in.
     *
     * @var array
     */
    protected $in = [];

    /**
     * Transformer columns out.
     *
     * @var array
     */
    protected $out = [];

    /**
     * Callback function
     *
     * @callable callback
     */
    protected $callback = '';

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'in', 'out', 'callback'
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
        if ($this->callback) {
            $row = call_user_func($this->callback, $row);
        }
    }
}
