<?php

namespace Segment\Controller\production;

/**
 * Description of ReturnLimitParameter
 *
 * @author michaelmosher
 */
class ReturnLimitParameter implements \Segment\Controller\Parameter
{
    private $tuple = [];
    
    public function __construct(string $field, $value, string $operator, array $options)
    {
        $this->tuple[\Segment\Model\production\ModelCaller::LIMITSTARTNUM] =
                $options[\Segment\Model\production\ModelCaller::LIMITSTARTNUM] ?? NULL;
        $this->tuple[\Segment\Model\production\ModelCaller::LIMITSTARTABS]
                = $options[\Segment\Model\production\ModelCaller::LIMITSTARTABS] ?? NULL;
        $this->tuple[self::operator] = $operator;
        $this->tuple[\Segment\Model\production\ModelCaller::LIMITAMTABS] =
                $options[\Segment\Model\production\ModelCaller::LIMITAMTABS] ?? NULL;
        $this->tuple[\Segment\Model\production\ModelCaller::LIMITAMTNUM] =
                $options[\Segment\Model\production\ModelCaller::LIMITAMTNUM] ?? NULL;
    }


    public function getField(): string
    {
        return "";
    }

    public function getOperator(): string
    {
        return $this->tuple[self::operator];
    }

    public function getValue()
    {
        return NULL;
    }
    
    public function getLimitStart(): float
    {
        return (float)$this->tuple[\Segment\Model\production\ModelCaller::LIMITSTARTNUM];
    }
    
    public function getLimitAmount(): float
    {
        return (float)$this->tuple[\Segment\Model\production\ModelCaller::LIMITAMTNUM];
    }
    
    public function isStartAbsolute(): bool
    {
        return (bool)$this->tuple[\Segment\Model\production\ModelCaller::LIMITSTARTABS];
    }
    
    public function isAmountAbsolute(): bool
    {
        return (bool)$this->tuple[\Segment\Model\production\ModelCaller::LIMITAMTABS];
    }

}
