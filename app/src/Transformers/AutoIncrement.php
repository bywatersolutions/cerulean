<?php
namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;

class AutoIncrement extends Transformer
{
    /**
     * Transformer columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The value to start incrementing with.
     *
     * @var int
     */
    protected $start = 1;

    /**
     * The current value.
     *
     * @var array
     */
    protected $current = 1;

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'start'
    ];
    /**
     * Initialize the step.
     *
     * @return void
     */
    public function initialize()
    {
        $this->current = $this->start;
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
            return $this->current;
        });
        $this->current++;
    }
}
