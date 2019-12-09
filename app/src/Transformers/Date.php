<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use InvalidArgumentException;

class Date extends Transformer
{
    /**
     * Transformer columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Date format coming in
     *
     * @var string
     */
    protected $inputFormat = '';

    /**
     * Date format going out
     *
     * @var string
     */
    protected $outputFormat = 'Y-m-d';

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'inputFormat', 'outputFormat'
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
            //		return date($this->format, strtotime($column));
            $date_obj = date_create_from_format($this->inputFormat, $column);
            if ($date_obj) {
                return $date_obj->format($this->outputFormat);
            }
        });
    }
}
