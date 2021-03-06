<?php

namespace Marquine\Etl\Extractors;

use Marquine\Etl\Row;

use Scriptotek\Marc\Collection;
use Scriptotek\Marc\Record;

require 'File/MARC.php';

class MARC extends Extractor
{
    /**
     * Extractor columns.
     *
     * @var array
     */
    protected $columns;

    /**
     * The MARCspec path that defines where to pull the record's legacyid
     *
     * @var string
     */
    protected $idpath = '001';

    /**
     * The MARC tag that is used to store holdings/item records
     *
     * @var string
     */
    protected $holdingstag;

    /**
     * The separator to use when multiple values are returned in a column
     *
     * @var string
     */
    protected $separator = ' | ';

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
        'columns', 'idpath', 'holdingstag', 'separator'
    ];

    /**
     * Extract data from the input.
     *
     * @return \Generator
     */
    public function extract()
    {
	$collection = new \File_MARC($this->input);
	$counter = 1;
	while ($record = $collection->next()) {
		if ($this->holdingstag) {
			$items = $record->getFields($this->holdingstag);
		        $itemcount = 1;
			foreach ($items as $item) {
			    // if columns defined, then use, otherwise, fetch each subfield
			    $this->row['biblio_id'] = $this->getLegacyID($record, $counter);
			    if ($this->columns) {
			        foreach ($this->columns as $name => $tag) {
                                    $goal = implode($this->separator, $item->getSubFields($tag));
				    $goal = substr($goal, 5);
                                    if ($goal) {
					$this->row[$name] = $goal;
                                    }
			        }
                            } else {
                                foreach ($item->getSubFields() as $subfield) {
				     $this->row[$subfield->getCode()] = $subfield->getData();
				}
                            }
			    if (isset($this->row['barcode'])) {
				$this->row['legacyid'] = $this->row['barcode'];
			    } else {
				$this->row['legacyid'] = $this->row['biblio_id'] . ":" . $itemcount;
                            }
	            	    yield new Row($this->row);
			    $itemcount++;
			}
		} else {
		    $this->row['legacyid'] = $this->getLegacyID($record, $counter);
	  	    $this->row['record'] = $record->toRaw();
 		    foreach ($this->columns as $key => $path) {
			$results = '';
	                if ($path) {
        	            // if multiple results returned, implode to string with designated separator
 			    $tag_ref = new \File_MARC_Reference($path, $record);
	               	    $results = implode($this->separator, $tag_ref->content);
                	}
                	$this->row[$key] = $results;
            	    }

	            yield new Row($this->row);
		}
		$counter++;
		$this->row = [];
	}
    }

    private function getLegacyID($record, $count) {
	$reference = new \File_MARC_Reference($this->idpath,$record);
        $legacyid = implode($this->separator, $reference->content);
        if (!$legacyid) {
            $legacyid = $this->input . ":" . $count;
//	    echo $legacyid . "\n";
        }
	return $legacyid;
    }
}
