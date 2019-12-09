<?php

namespace Marquine\Etl\Loaders;

use Marquine\Etl\Row;
use InvalidArgumentException;

class File extends Loader
{

    /**
     * The columns to insert.
     *
     * @var array
     */
    protected $columns = [];

    protected $output = 'php://stdout';

    protected $format = 'csv';

    protected $mode = 'a';

    protected $availableOptions = ['columns', 'output', 'format', 'mode'];

    /**
     * Initialize the step.
     *
     * @return void
     */
    public function initialize()
    {
        if (! empty($this->columns) && array_keys($this->columns) === range(0, count($this->columns) - 1)) {
            $this->columns = array_combine($this->columns, $this->columns);
        }
        if ($this->format == 'csv') {
            $filepointer = fopen($this->output, $this->mode);
            fputcsv($filepointer, $this->columns);
            fclose($filepointer);
        }
    }

    public function load(Row $row)
    {
        $row = $row->toArray();
        if ($this->columns) {
            $result = [];
            foreach ($this->columns as $key => $column) {
                isset($row[$key]) ? $result[$column] = $row[$key] : $result[$column] = "";
            }
            $row = $result;
        }

        $filepointer = fopen($this->output, $this->mode);
        switch ($this->format) {
            case 'csv':
        fputcsv($filepointer, array_merge($this->columns, $row));
        break;
            case 'json':
        fwrite($filepointer, json_encode($row, JSON_PRETTY_PRINT) . ",\n");
        break;
        case 'yaml':
        case 'yml':
        fwrite($filepointer, \yaml_emit($row));
        break;
        default:
            throw new InvalidArgumentException("The write type [{$this->format}] is invalid.");
        }
        fclose($filepointer);
    }
}
