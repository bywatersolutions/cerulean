<?php

namespace Marquine\Etl\Transformers;

use Marquine\Etl\Row;
use InvalidArgumentException;

use MathParser\StdMathParser;
use MathParser\Interpreting\Evaluator;

class Math extends Transformer
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
    protected $expression= "x";

    /**
     * Properties that can be set via the options method.
     *
     * @var array
     */
    protected $availableOptions = [
        'columns', 'expression'
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
            if (isset($row[$column])) {
                $parser = new StdMathParser();
                $AST = $parser->parse($this->expression);
                $evaluator = new Evaluator();
                $evaluator->setVariables(['x' => $column]);
                return $AST->accept($evaluator);
            }
        });
    }
}
