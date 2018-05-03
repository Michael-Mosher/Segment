<?php

namespace Segment\Controller\production;

/**
 * Description of ReturnParameter
 *
 * @author michaelmosher
 */
class ReturnParameter implements \Segment\Controller\Parameter
{
    private $tuple = [];
    
    public function __construct(string $field, $value, string $operator, array $options)
    {
            $this->tuple[self::field] = $field;
            $this->tuple[self::operator] = $operator;
            $this->tuple[\Segment\Model\production\ModelCaller::ALIAS] =
                    $options[\Segment\Model\production\ModelCaller::ALIAS] ?? NULL;
    }
    
    public function getField(): string {
        return $this->tuple[self::field];
    }

    public function getOperator(): string {
        return $this->tuple[self::operator];
    }

    public function getValue() {
        return NULL;
    }
    
    public function getAlias(): string
    {
        return $this->tuple[\Segment\Model\production\ModelCaller::ALIAS];
    }

}
