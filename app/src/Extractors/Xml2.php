<?php

namespace Marquine\Etl\Extractors;

use Marquine\Etl\Row;

class Xml2 extends Extractor
{
    /**
     * Extractor columns.
     *
     * @var array
     */
    protected $columns;

    /**
     * The loop path.
     *
     * @var string
     */
    protected $loop = '/';

    /**
     * The loop path.
     *
     * @var string
     */
    protected $separator = ' | ';

    /**
     * XML Reader.
     *
     * @var \XMLReader
     */
    protected $reader;

    /**
     * The current xml path
     *
     * @var string
     */
    protected $path;

    /**
     * Current row.
     *
     * @var array
     */
    protected $row = [];

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'loop', 'separator'
    ];

    /**
     * Extract data from the input.
     *
     * @return \Generator
     */
    public function extract()
    {

    // open the XML file with SimpleXML
        $this->reader = simplexml_load_file($this->input);

        // Use the XPath provided in loop option to get a baseline for each row
        foreach ($this->reader->xpath($this->loop) as $index => $record) {
            $ind = $index + 1;
            // for each column, get the values at the specified XPaths
            foreach ($this->columns as $key => $path) {
                // start with an empty value
                $results = '';
                if ($path) {
                    // if multiple results returned, implode to string with designated separator
                    $results = implode($this->separator, $record->xpath($path));
                }
                $this->row[$key] = $results;
            }
            yield new Row($this->row);
        }
    }
}
