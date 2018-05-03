<?php

namespace Segment\Controller;

/**
 *
 * @author Michael-Mosher
 */
interface RestFunctionDecorator extends RestFunction
{
    public function __construct(RestRequest $rr, RestFunction $decoration = NULL);
}
