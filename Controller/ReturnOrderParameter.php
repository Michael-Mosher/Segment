<?php

namespace Segment\Controller\production;

/**
 * Description of ReturnOrderParameter
 *
 * @author michaelmosher
 */
class ReturnOrderParameter implements \Segment\Controller\Parameter{
    private $tuple = [];
    
    public function __construct(string $field, $value, string $operator, array $options)
    {
        $this->tuple[self::field] = $field;
        $this->tuple[\Segment\Model\production\ModelCaller::ORDERDIRECTION]
                = $options[\Segment\Model\production\ModelCaller::ORDERDIRECTION] ?? NULL;
        $this->tuple[self::operator] = $operator;
    }


    public function getField(): string
    {
        return $this->tuple[self::field];
    }

    public function getOperator(): string
    {
        return $this->tuple[self::operator];
    }

    public function getValue()
    {
        return NULL;
    }
    
    public function getAscDesc(): int
    {
        return $this->tuple[\Segment\Model\production\ModelCaller::ORDERDIRECTION];
    }

}
