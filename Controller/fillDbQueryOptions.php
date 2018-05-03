<?php

namespace Segment\Controller\Testing;

function fillDbQueryOptions(string $operator, \Segment\Controller\Parameter $tuple): array
{
    $answer = [];
    $temp_obj;
    switch (strtolower(trim($operator))) {
        case \Segment\Controller\RestReturn::FIELDAVG:
        case \Segment\Controller\RestReturn::FIELDCOUNT:
        case \Segment\Controller\RestReturn::FIELDFIRSTQ:
        case \Segment\Controller\RestReturn::FIELDMEDIAN:
        case \Segment\Controller\RestReturn::FIELDMODE:
        case \Segment\Controller\RestReturn::FIELDSET:
        case \Segment\Controller\RestReturn::FIELDTHIRDQ:
        case \Segment\Controller\RestReturn::NORMAL:
            $answer[\Segment\Model\production\ModelCaller::ALIAS] = $tuple->getAlias();
            break;
        case \Segment\Controller\RestReturn::LIMIT:
            $answer[\Segment\Model\production\ModelCaller::LIMITAMTABS] = $tuple->isAmountAbsolute();
            $answer[\Segment\Model\production\ModelCaller::LIMITAMTNUM] = $tuple->getLimitAmount();
            $answer[\Segment\Model\production\ModelCaller::LIMITSTARTABS] = $tuple->isStartAbsolute();
            $answer[\Segment\Model\production\ModelCaller::LIMITSTARTNUM] = $tuple->getLimitStart();
            break;
        case \Segment\Controller\RestReturn::ORDER:
            $answer[\Segment\Model\production\ModelCaller::ORDERDIRECTION] = $tuple->getAscDesc();
            break;
        case \Segment\Controller\RestSearch::BETWEEN:
        case \Segment\Controller\RestSearch::EQUAL:
        case \Segment\Controller\RestSearch::EQUALALL;
        case \Segment\Controller\RestSearch::EQUALANY:
        case \Segment\Controller\RestSearch::GREATALL:
        case \Segment\Controller\RestSearch::GREATANY:
        case \Segment\Controller\RestSearch::GREATEQ:
        case \Segment\Controller\RestSearch::GREATER:
        case \Segment\Controller\RestSearch::LESSALL:
        case \Segment\Controller\RestSearch::LESSANY:
        case \Segment\Controller\RestSearch::LESSEQ:
        case \Segment\Controller\RestSearch::LESSER:
        case \Segment\Controller\RestSearch::NEQUAL:
        case \Segment\Controller\RestSearch::NBETWEEN:
            $answer[\Segment\Model\production\ModelCaller::ANDORNOCONJ] = $tuple->getConjunctive();
            $answer[\Segment\Model\production\ModelCaller::EXCLUSIVERANGE] = $tuple->isExclusiveRange();
            if(is_a($temp_obj = $tuple->getValue(), "\Segment\Controller\Testing\SearchHttpRequest"))
                $answer[\Segment\Model\production\ModelCaller::STATEMENT1]  = $temp_obj;
            else if (is_array($temp_obj)){
                $first = TRUE;
                if(is_a($temp_obj[0], "\Segment\Controller\Testing\SearchHttpRequest"))
                    $answer[\Segment\Model\production\ModelCaller::STATEMENT1] = $temp_obj[0];
                foreach($value as $temp_obj):
                    if(!$first)
                        if(is_a($value, "\Segment\Controller\Testing\SearchHttpRequest")){
                            $answer[\Segment\Model\production\ModelCaller::STATEMENT2] = $value;
                            break;
                        }
                endforeach;
            }
            break;
        default:
            break;
    }
    return $answer;
}