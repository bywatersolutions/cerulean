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
            if ( !isset($row[$key]) || is_null($row[$key]) || $row[$key] == '' || $this->force ) {
                // if the value is wrapped in {}, lookup that row if extant
                if (preg_match_all('/\{(?P<column>[^\}]+)\}/', $value, $matches) ) {
                   foreach($matches['column'] as $column) {
                        if (isset($row[$column]) ) {
                           $value = preg_replace('/\{'.$column.'\}/', $row[$column], $value);
                        } elseif (substr($column, 0, 3) == 'now') {
			   // cut off the 'now', make a datetime and output in 'c' format
			   $dt = new \DateTime(substr($column,3));
                           $value = $dt->format('c');
                        }
                   }
                }
                $row[$key] = $value;
            }
        }
    }
}
